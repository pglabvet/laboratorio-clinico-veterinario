<?php

namespace App\Services;

use App\Models\Analisis;
use App\Models\Auditoria;
use App\Models\CategoriaInsumo;
use App\Models\Especie;
use App\Models\Insumo;
use App\Models\Muestra;
use App\Models\PlantillaFormulario;
use App\Models\Resultado;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;
use App\Models\UnidadMedida;
use App\Models\User;
use App\Models\Veterinaria;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Traduce los registros de auditoría a frases que entiende el usuario final:
 * quién hizo qué, dónde y cuándo, con nombres de campos en español y
 * nombres en lugar de IDs. No modifica la auditoría, solo la presenta.
 */
class AuditoriaLegibleService
{
    /** Minutos para agrupar varios valores de resultados guardados de una vez */
    private const MINUTOS_AGRUPAR_RESULTADOS = 10;

    /** Campos que no aportan al usuario final */
    private const CAMPOS_OCULTOS = [
        'id', 'muestra_id', 'analisis_id', 'fecha_inicio', 'fecha_finalizacion', 'fecha_aprobacion',
        'created_at', 'updated_at', 'deleted_at', 'indice', 'repeticiones_procesadas',
    ];

    /** Nombres legibles de campos */
    private const ETIQUETAS = [
        'codigo_muestra' => 'Código',
        'paciente_nombre' => 'Paciente',
        'propietario_nombre' => 'Propietario',
        'tipo_muestra' => 'Tipo de muestra',
        'fecha_recepcion' => 'Fecha de recepción',
        'diagnostico' => 'Diagnóstico',
        'observaciones' => 'Observaciones',
        'observaciones_bioquimico' => 'Observaciones del bioquímico',
        'observaciones_aprobador' => 'Observaciones del revisor',
        'bioquimico_id' => 'Bioquímico',
        'aprobador_id' => 'Revisado por',
        'plantilla_formulario_id' => 'Plantilla',
        'tipo_analisis_id' => 'Tipo de análisis',
        'stock_minimo' => 'Stock mínimo',
        'costo_unitario' => 'Costo unitario',
        'tipo_movimiento' => 'Tipo de movimiento',
        'observacion' => 'Observación',
        'fuera_rango' => 'Fuera de rango',
        'name' => 'Nombre',
        'email' => 'Correo',
        'telefono' => 'Teléfono',
        'direccion' => 'Dirección',
    ];

    /** Campos que guardan un ID y el modelo para mostrar su nombre */
    private const RELACIONES = [
        'especie_id' => [Especie::class, 'nombre'],
        'veterinaria_id' => [Veterinaria::class, 'nombre'],
        'sucursal_id' => [Sucursal::class, 'nombre'],
        'tipo_analisis_id' => [TipoAnalisis::class, 'nombre'],
        'plantilla_formulario_id' => [PlantillaFormulario::class, 'nombre'],
        'bioquimico_id' => [User::class, 'name'],
        'aprobador_id' => [User::class, 'name'],
        'user_id' => [User::class, 'name'],
        'registrado_por' => [User::class, 'name'],
        'creado_por' => [User::class, 'name'],
        'insumo_id' => [Insumo::class, 'nombre'],
        'categoria_insumo_id' => [CategoriaInsumo::class, 'nombre'],
        'unidad_medida_id' => [UnidadMedida::class, 'nombre'],
    ];

    /** Caché de nombres resueltos durante la petición */
    private array $nombres = [];

    /** Caché de análisis (con tipo y muestra) por ID */
    private array $analisis = [];

    /** analisis_id de cada resultado por ID de resultado */
    private array $analisisDeResultado = [];

    /**
     * Historial completo de una muestra, en orden cronológico y agrupado:
     * registro, correcciones, análisis agregados o quitados, resultados,
     * envíos a revisión, aprobaciones y rechazos.
     *
     * @return Collection<int, array>
     */
    public function historialMuestra(Muestra $muestra): Collection
    {
        $desde = $muestra->created_at ?? Carbon::create(2000);

        // Análisis actuales y los que se quitaron de la muestra
        $analisisIds = $muestra->analisis()->pluck('id');
        $analisisQuitados = Auditoria::where('entidad', 'Analisis')
            ->where('accion', 'eliminar')
            ->where('created_at', '>=', $desde)
            ->get()
            ->filter(fn ($a) => (int) ($a->valores_anteriores['muestra_id'] ?? 0) === $muestra->id)
            ->pluck('entidad_id');
        $analisisIds = $analisisIds->merge($analisisQuitados)->unique()->values();

        $resultadoIds = Resultado::whereIn('analisis_id', $analisisIds)->pluck('id');

        $auditorias = Auditoria::with('user')
            ->where(function ($q) use ($muestra, $analisisIds, $resultadoIds, $desde) {
                $q->where(fn ($q) => $q->where('entidad', 'Muestra')->where('entidad_id', $muestra->id))
                    ->orWhere(fn ($q) => $q->where('entidad', 'Analisis')->whereIn('entidad_id', $analisisIds))
                    ->orWhere(fn ($q) => $q->where('entidad', 'Resultado')->whereIn('entidad_id', $resultadoIds))
                    ->orWhere(fn ($q) => $q->where('entidad', 'Resultado')->where('accion', 'eliminar')->where('created_at', '>=', $desde));
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            // Resultados eliminados: solo los de los análisis de esta muestra
            ->filter(fn ($a) => $a->entidad !== 'Resultado' || $a->accion !== 'eliminar'
                || $analisisIds->contains((int) ($a->valores_anteriores['analisis_id'] ?? 0)));

        $this->precargar($auditorias);

        $eventos = collect();

        foreach ($auditorias as $auditoria) {
            $evento = $this->describir($auditoria);

            // Los cambios de estado automáticos de la muestra ya se explican con los eventos de sus análisis
            if ($evento['automatico']) {
                continue;
            }

            $ultimo = $eventos->last();

            // Agrupar varios valores de resultados guardados juntos por el mismo usuario
            if ($auditoria->entidad === 'Resultado' && $ultimo && $ultimo['grupo'] === 'resultados'
                && $ultimo['user_id'] === $auditoria->user_id
                && $ultimo['analisis_id'] === $evento['analisis_id']
                && $ultimo['fecha']->diffInMinutes($auditoria->created_at) <= self::MINUTOS_AGRUPAR_RESULTADOS) {
                $ultimo['fecha'] = $auditoria->created_at;
                $eventos->put($eventos->keys()->last(), $ultimo);

                continue;
            }

            // "Guardó resultados" seguido de "envió a revisión" del mismo usuario: una sola línea
            if ($evento['grupo'] === 'envio-revision' && $ultimo && $ultimo['grupo'] === 'resultados'
                && $ultimo['user_id'] === $auditoria->user_id
                && $ultimo['analisis_id'] === $evento['analisis_id']
                && $ultimo['fecha']->diffInMinutes($auditoria->created_at) <= self::MINUTOS_AGRUPAR_RESULTADOS) {
                $eventos->pop();
            }

            if ($auditoria->entidad === 'Resultado') {
                $evento['texto'] = 'Guardó resultados de '.$this->nombreAnalisis($evento['analisis_id']);
                $evento['grupo'] = 'resultados';
                $evento['detalles'] = [];
            }

            $eventos->push($evento);
        }

        return $eventos->values();
    }

    /**
     * Convierte un registro de auditoría en un evento legible:
     * texto, dónde, detalles campo por campo, icono y color.
     */
    public function describir(Auditoria $auditoria): array
    {
        $evento = [
            'id' => $auditoria->id,
            'fecha' => $auditoria->created_at,
            'user_id' => $auditoria->user_id,
            'usuario' => $auditoria->user->name ?? 'Sistema',
            'texto' => '',
            'donde' => '',
            'motivo' => null,
            'detalles' => [],
            'icono' => 'pencil-square',
            'color' => 'amber',
            'grupo' => null,
            'analisis_id' => null,
            'automatico' => false,
        ];

        return match ($auditoria->entidad) {
            'Muestra' => $this->describirMuestra($auditoria, $evento),
            'Analisis' => $this->describirAnalisis($auditoria, $evento),
            'Resultado' => $this->describirResultado($auditoria, $evento),
            default => $this->describirGenerico($auditoria, $evento),
        };
    }

    /**
     * Precarga en bloque los análisis y resultados referenciados para evitar consultas por fila.
     */
    public function precargar(iterable $auditorias): void
    {
        $auditorias = collect($auditorias);

        $resultadoIds = $auditorias->where('entidad', 'Resultado')
            ->filter(fn ($a) => ! isset($a->valores_nuevos['analisis_id']) && ! isset($a->valores_anteriores['analisis_id']))
            ->pluck('entidad_id')
            ->diff(array_keys($this->analisisDeResultado));
        if ($resultadoIds->isNotEmpty()) {
            $this->analisisDeResultado += Resultado::whereIn('id', $resultadoIds)->pluck('analisis_id', 'id')->all();
        }

        $analisisIds = $auditorias->where('entidad', 'Analisis')->pluck('entidad_id')
            ->merge($auditorias->where('entidad', 'Resultado')->map(fn ($a) => $this->analisisIdDeResultado($a)))
            ->filter()
            ->unique()
            ->diff(array_keys($this->analisis));
        if ($analisisIds->isNotEmpty()) {
            foreach (Analisis::with(['tipoAnalisis', 'muestra'])->whereIn('id', $analisisIds)->get() as $analisis) {
                $this->analisis[$analisis->id] = $analisis;
            }
        }
    }

    private function describirMuestra(Auditoria $auditoria, array $evento): array
    {
        $codigo = $auditoria->valores_nuevos['codigo_muestra']
            ?? $auditoria->valores_anteriores['codigo_muestra']
            ?? Muestra::whereKey($auditoria->entidad_id)->value('codigo_muestra')
            ?? '#'.$auditoria->entidad_id;
        $evento['donde'] = "Muestra {$codigo}";

        switch ($auditoria->accion) {
            case 'crear':
                $evento['texto'] = "Registró la muestra {$codigo}";
                $evento['icono'] = 'plus-circle';
                $evento['color'] = 'green';
                break;
            case 'eliminar':
                $evento['texto'] = "Eliminó la muestra {$codigo}";
                $evento['icono'] = 'trash';
                $evento['color'] = 'red';
                break;
            default:
                $detalles = $this->detalles($auditoria);
                if (array_keys($auditoria->getCambios()) === ['estado']) {
                    $evento['texto'] = 'La muestra pasó a estado '.$auditoria->valores_nuevos['estado'];
                    $evento['icono'] = 'arrow-path';
                    $evento['color'] = 'zinc';
                    $evento['automatico'] = true;
                } else {
                    $evento['texto'] = "Corrigió los datos de la muestra {$codigo}";
                    $evento['detalles'] = $detalles;
                }
        }

        return $evento;
    }

    private function describirAnalisis(Auditoria $auditoria, array $evento): array
    {
        $analisisId = $auditoria->entidad_id;
        $evento['analisis_id'] = $analisisId;
        $nombre = $this->nombreAnalisis($analisisId, $auditoria->valores_anteriores ?? $auditoria->valores_nuevos);
        $evento['donde'] = $this->dondeAnalisis($analisisId, $auditoria->valores_anteriores ?? $auditoria->valores_nuevos);

        if ($auditoria->accion === 'crear') {
            $evento['texto'] = "Agregó el análisis {$nombre}";
            $evento['icono'] = 'plus-circle';
            $evento['color'] = 'green';

            return $evento;
        }

        if ($auditoria->accion === 'eliminar') {
            $evento['texto'] = "Quitó el análisis {$nombre}";
            $evento['icono'] = 'trash';
            $evento['color'] = 'red';

            return $evento;
        }

        $cambios = $auditoria->getCambios();
        $estadoAntes = $cambios['estado']['anterior'] ?? null;
        $estadoDespues = $cambios['estado']['nuevo'] ?? null;

        if ($estadoDespues === Analisis::ESTADO_EN_REVISION) {
            $evento['texto'] = "Llenó los resultados de {$nombre} y los envió a revisión";
            $evento['icono'] = 'paper-airplane';
            $evento['color'] = 'blue';
            $evento['grupo'] = 'envio-revision';
        } elseif ($estadoDespues === Analisis::ESTADO_APROBADO) {
            $evento['texto'] = "Aprobó el análisis {$nombre}";
            $evento['icono'] = 'check-circle';
            $evento['color'] = 'green';
        } elseif ($estadoAntes === Analisis::ESTADO_EN_REVISION && $estadoDespues === Analisis::ESTADO_PENDIENTE) {
            $evento['texto'] = "Rechazó el análisis {$nombre} para que lo corrijan";
            $evento['icono'] = 'x-circle';
            $evento['color'] = 'red';
            $evento['motivo'] = $auditoria->valores_nuevos['observaciones_aprobador'] ?? null;
        } elseif ($estadoDespues === Analisis::ESTADO_ENVIADO) {
            $evento['texto'] = "Envió los resultados de {$nombre} al cliente";
            $evento['icono'] = 'envelope';
            $evento['color'] = 'violet';
        } else {
            $evento['texto'] = "Modificó el análisis {$nombre}";
            $evento['detalles'] = $this->detalles($auditoria);
        }

        return $evento;
    }

    private function describirResultado(Auditoria $auditoria, array $evento): array
    {
        $analisisId = $this->analisisIdDeResultado($auditoria);
        $evento['analisis_id'] = $analisisId;
        $evento['grupo'] = 'resultados';
        $evento['icono'] = 'beaker';
        $evento['color'] = 'blue';
        $nombre = $this->nombreAnalisis($analisisId);
        $evento['donde'] = $this->dondeAnalisis($analisisId);

        $evento['texto'] = match ($auditoria->accion) {
            'crear' => "Guardó un valor en los resultados de {$nombre}",
            'eliminar' => "Eliminó un valor de los resultados de {$nombre}",
            default => "Modificó un valor de los resultados de {$nombre}",
        };

        return $evento;
    }

    private function describirGenerico(Auditoria $auditoria, array $evento): array
    {
        $entidad = $auditoria->getNombreEntidad();
        $identificador = str_contains($auditoria->descripcion, ': ')
            ? substr($auditoria->descripcion, strpos($auditoria->descripcion, ': ') + 2)
            : '#'.$auditoria->entidad_id;
        $evento['donde'] = "{$entidad} {$identificador}";

        [$verbo, $icono, $color] = match ($auditoria->accion) {
            'crear' => ['Creó', 'plus-circle', 'green'],
            'eliminar' => ['Eliminó', 'trash', 'red'],
            default => ['Modificó', 'pencil-square', 'amber'],
        };

        $evento['texto'] = "{$verbo} {$entidad} {$identificador}";
        $evento['icono'] = $icono;
        $evento['color'] = $color;
        $evento['detalles'] = $auditoria->accion === 'actualizar' ? $this->detalles($auditoria) : [];

        return $evento;
    }

    /**
     * Cambios campo por campo en formato legible: [campo, antes, después]
     *
     * @return array<int, array{campo: string, antes: string, despues: string}>
     */
    public function detalles(Auditoria $auditoria): array
    {
        $detalles = [];

        foreach ($auditoria->getCambios() as $campo => $cambio) {
            if (in_array($campo, self::CAMPOS_OCULTOS, true) || is_array($cambio['nuevo']) || is_array($cambio['anterior'])) {
                continue;
            }

            $detalles[] = [
                'campo' => $this->etiqueta($campo),
                'antes' => $this->valor($campo, $cambio['anterior']),
                'despues' => $this->valor($campo, $cambio['nuevo']),
            ];
        }

        return $detalles;
    }

    /**
     * Datos de un registro creado o eliminado en formato legible: [campo, valor]
     *
     * @return array<int, array{campo: string, valor: string}>
     */
    public function datosRegistro(Auditoria $auditoria): array
    {
        $valores = $auditoria->accion === 'eliminar' ? $auditoria->valores_anteriores : $auditoria->valores_nuevos;
        $datos = [];

        foreach ($valores ?? [] as $campo => $valor) {
            if (in_array($campo, self::CAMPOS_OCULTOS, true) || is_array($valor)) {
                continue;
            }

            $datos[] = ['campo' => $this->etiqueta($campo), 'valor' => $this->valor($campo, $valor)];
        }

        return $datos;
    }

    private function etiqueta(string $campo): string
    {
        if (isset(self::ETIQUETAS[$campo])) {
            return self::ETIQUETAS[$campo];
        }

        return ucfirst(str_replace('_', ' ', preg_replace('/_id$/', '', $campo)));
    }

    private function valor(string $campo, mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '(vacío)';
        }

        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if (isset(self::RELACIONES[$campo])) {
            return $this->nombreRelacion($campo, $valor);
        }

        if ($campo === 'sexo') {
            return match ($valor) {
                'M' => 'Macho',
                'H' => 'Hembra',
                default => (string) $valor,
            };
        }

        if (in_array($campo, ['estado', 'activo'], true) && in_array($valor, [0, 1, '0', '1', true, false], true)) {
            return $valor ? 'Activo' : 'Inactivo';
        }

        // Fechas guardadas como texto ISO
        if (is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $valor)) {
            try {
                return Carbon::parse($valor)->timezone(config('app.timezone'))->format('d/m/Y H:i');
            } catch (\Throwable) {
                return $valor;
            }
        }

        return (string) $valor;
    }

    private function nombreRelacion(string $campo, mixed $id): string
    {
        [$modelo, $columna] = self::RELACIONES[$campo];
        $clave = "{$modelo}:{$id}";

        if (! array_key_exists($clave, $this->nombres)) {
            $this->nombres[$clave] = $modelo::whereKey($id)->value($columna);
        }

        return $this->nombres[$clave] ?? "#{$id} (eliminado)";
    }

    private function analisisIdDeResultado(Auditoria $auditoria): ?int
    {
        $id = $auditoria->valores_nuevos['analisis_id']
            ?? $auditoria->valores_anteriores['analisis_id']
            ?? ($this->analisisDeResultado[$auditoria->entidad_id] ??= Resultado::whereKey($auditoria->entidad_id)->value('analisis_id'));

        return $id ? (int) $id : null;
    }

    private function obtenerAnalisis(?int $analisisId): ?Analisis
    {
        if (! $analisisId) {
            return null;
        }

        if (! array_key_exists($analisisId, $this->analisis)) {
            $this->analisis[$analisisId] = Analisis::with(['tipoAnalisis', 'muestra'])->find($analisisId);
        }

        return $this->analisis[$analisisId];
    }

    /**
     * Nombre del tipo de análisis; si el análisis fue eliminado se usa el valor guardado en la auditoría.
     */
    private function nombreAnalisis(?int $analisisId, ?array $valores = null): string
    {
        $analisis = $this->obtenerAnalisis($analisisId);

        if ($analisis?->tipoAnalisis) {
            return $analisis->tipoAnalisis->nombre;
        }

        if (isset($valores['tipo_analisis_id'])) {
            return $this->nombreRelacion('tipo_analisis_id', $valores['tipo_analisis_id']);
        }

        return 'análisis #'.$analisisId;
    }

    private function dondeAnalisis(?int $analisisId, ?array $valores = null): string
    {
        $analisis = $this->obtenerAnalisis($analisisId);
        $codigo = $analisis?->muestra?->codigo_muestra
            ?? (isset($valores['muestra_id']) ? Muestra::whereKey($valores['muestra_id'])->value('codigo_muestra') : null);

        return $this->nombreAnalisis($analisisId, $valores).($codigo ? " · Muestra {$codigo}" : '');
    }
}
