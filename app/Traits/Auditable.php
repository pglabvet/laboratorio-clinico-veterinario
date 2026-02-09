<?php

namespace App\Traits;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Trait Auditable.
 *
 * Al incluir este trait en un modelo Eloquent, se registran automáticamente
 * las acciones de crear, actualizar y eliminar en la tabla de auditorías.
 *
 * Uso:
 *     class Muestra extends Model
 *     {
 *         use Auditable;
 *
 *         // Opcional: campos que NO quieres auditar (contraseñas, tokens, etc.)
 *         protected array $camposExcluidosAuditoria = ['password', 'remember_token'];
 *
 *         // Opcional: nombre legible para la auditoría
 *         protected string $nombreAuditable = 'Muestra';
 *     }
 *
 * ¿Cómo funciona?
 * Laravel dispara eventos cuando un modelo es creado, actualizado o eliminado.
 * Este trait escucha esos eventos con el método estático "booted()" y genera
 * automáticamente un registro de auditoría con los datos relevantes.
 */
trait Auditable
{
    /**
     * Se ejecuta cuando el modelo es "booted" (inicializado por Laravel).
     * Aquí registramos los listeners de eventos del modelo.
     */
    public static function bootAuditable(): void
    {
        // Cuando se CREA un nuevo registro
        static::created(function (Model $model) {
            $model->registrarAuditoria('crear', [], $model->getAttributes());
        });

        // Cuando se ACTUALIZA un registro existente
        static::updated(function (Model $model) {
            // getOriginal() = valores ANTES del cambio
            // getChanges() = valores que CAMBIARON
            $camposModificados = $model->getChanges();

            // Si lo único que cambió es updated_at, no registramos
            if (count($camposModificados) === 1 && isset($camposModificados['updated_at'])) {
                return;
            }

            $valoresAnteriores = [];
            foreach (array_keys($camposModificados) as $campo) {
                $valoresAnteriores[$campo] = $model->getOriginal($campo);
            }

            $model->registrarAuditoria('actualizar', $valoresAnteriores, $camposModificados);
        });

        // Cuando se ELIMINA un registro
        static::deleted(function (Model $model) {
            $model->registrarAuditoria('eliminar', $model->getOriginal(), []);
        });
    }

    /**
     * Crea el registro de auditoría en la base de datos.
     *
     * @param  string  $accion  La acción realizada (crear, actualizar, eliminar)
     * @param  array  $valoresAnteriores  Valores antes del cambio
     * @param  array  $valoresNuevos  Valores después del cambio
     */
    protected function registrarAuditoria(string $accion, array $valoresAnteriores, array $valoresNuevos): void
    {
        // Filtra campos sensibles que no deben guardarse en auditoría
        $excluidos = $this->getCamposExcluidos();
        $valoresAnteriores = $this->filtrarCampos($valoresAnteriores, $excluidos);
        $valoresNuevos = $this->filtrarCampos($valoresNuevos, $excluidos);

        // Genera una descripción legible de la acción
        $descripcion = $this->generarDescripcion($accion);

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => $accion,
            'entidad' => $this->getNombreAuditable(),
            'entidad_id' => $this->getKey(),
            'descripcion' => $descripcion,
            'valores_anteriores' => ! empty($valoresAnteriores) ? $valoresAnteriores : null,
            'valores_nuevos' => ! empty($valoresNuevos) ? $valoresNuevos : null,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Retorna los campos que deben excluirse de la auditoría.
     * Siempre se excluyen timestamps y campos sensibles comunes.
     * El modelo puede definir campos adicionales con $camposExcluidosAuditoria.
     *
     * @return array<string>
     */
    protected function getCamposExcluidos(): array
    {
        $excluidos = [
            'created_at',
            'updated_at',
            'deleted_at',
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'email_verified_at',
        ];

        // Si el modelo define campos adicionales a excluir
        if (property_exists($this, 'camposExcluidosAuditoria')) {
            $excluidos = array_merge($excluidos, $this->camposExcluidosAuditoria);
        }

        return $excluidos;
    }

    /**
     * Elimina los campos excluidos de un array de valores.
     *
     * @param  array  $valores  Valores originales
     * @param  array  $excluidos  Campos a excluir
     * @return array Valores filtrados
     */
    protected function filtrarCampos(array $valores, array $excluidos): array
    {
        return array_diff_key($valores, array_flip($excluidos));
    }

    /**
     * Genera una descripción legible de la acción.
     * Ejemplo: "Creó la Muestra #15" o "Eliminó el Insumo 'Jeringas 5ml'"
     */
    protected function generarDescripcion(string $accion): string
    {
        $nombre = $this->getNombreAuditable();
        $identificador = $this->getIdentificadorAuditable();

        $verbo = match ($accion) {
            'crear' => 'Creó',
            'actualizar' => 'Actualizó',
            'eliminar' => 'Eliminó',
            default => 'Modificó',
        };

        return "{$verbo} {$nombre}: {$identificador}";
    }

    /**
     * Retorna el nombre legible del modelo para la auditoría.
     * Si el modelo define $nombreAuditable, usa ese. Sino, usa el nombre de la clase.
     */
    protected function getNombreAuditable(): string
    {
        if (property_exists($this, 'nombreAuditable')) {
            return $this->nombreAuditable;
        }

        return class_basename($this);
    }

    /**
     * Retorna un identificador legible del registro.
     * Intenta usar campos comunes como nombre, codigo, etc.
     * Si no encuentra ninguno, usa el ID.
     */
    protected function getIdentificadorAuditable(): string
    {
        // Intenta encontrar un campo representativo
        $camposIdentificadores = ['codigo_muestra', 'nombre', 'name', 'email', 'codigo', 'titulo'];

        foreach ($camposIdentificadores as $campo) {
            if (isset($this->attributes[$campo]) && ! empty($this->attributes[$campo])) {
                return (string) $this->attributes[$campo];
            }
        }

        return "#{$this->getKey()}";
    }
}
