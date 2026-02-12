<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo de Auditoría.
 *
 * Representa un registro de auditoría del sistema. Cada vez que un usuario
 * crea, actualiza o elimina una entidad, se genera automáticamente un registro
 * en esta tabla con los detalles de la acción.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $accion
 * @property string $entidad
 * @property int|null $entidad_id
 * @property string $descripcion
 * @property array|null $valores_anteriores
 * @property array|null $valores_nuevos
 * @property string|null $ip
 * @property string|null $user_agent
 */
class Auditoria extends Model
{
    /**
     * Nombre de la tabla (por convención Laravel buscaría "auditorias",
     * pero lo dejamos explícito para claridad).
     */
    protected $table = 'auditorias';

    /**
     * Campos que se pueden asignar masivamente.
     * Todos los campos son necesarios para crear un registro de auditoría.
     */
    protected $fillable = [
        'user_id',
        'accion',
        'entidad',
        'entidad_id',
        'descripcion',
        'valores_anteriores',
        'valores_nuevos',
        'ip',
        'user_agent',
    ];

    /**
     * Casting de columnas JSON a arrays de PHP automáticamente.
     * Esto permite acceder a valores_anteriores y valores_nuevos como arrays
     * en lugar de strings JSON crudos.
     */
    protected function casts(): array
    {
        return [
            'valores_anteriores' => 'array',
            'valores_nuevos' => 'array',
            'entidad_id' => 'integer',
        ];
    }

    /**
     * Relación: ¿Quién realizó esta acción?
     * Retorna el usuario responsable de la auditoría.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene los campos que cambiaron en una actualización.
     *
     * Compara valores_anteriores con valores_nuevos y retorna solo
     * los campos que efectivamente cambiaron, con su valor anterior y nuevo.
     *
     * Ejemplo de retorno:
     * [
     *     'precio' => ['anterior' => 15.00, 'nuevo' => 20.00],
     *     'nombre' => ['anterior' => 'Jeringa', 'nuevo' => 'Jeringa 5ml'],
     * ]
     *
     * @return array<string, array{anterior: mixed, nuevo: mixed}>
     */
    public function getCambios(): array
    {
        if ($this->accion !== 'actualizar' || ! $this->valores_anteriores || ! $this->valores_nuevos) {
            return [];
        }

        $cambios = [];

        foreach ($this->valores_nuevos as $campo => $valorNuevo) {
            $valorAnterior = $this->valores_anteriores[$campo] ?? null;

            if ($valorAnterior !== $valorNuevo) {
                $cambios[$campo] = [
                    'anterior' => $valorAnterior,
                    'nuevo' => $valorNuevo,
                ];
            }
        }

        return $cambios;
    }

    /**
     * Retorna un ícono representativo según la acción.
     * Se usa en la vista para identificar visualmente el tipo de acción.
     */
    public function getIcono(): string
    {
        return match ($this->accion) {
            'crear' => 'plus-circle',
            'actualizar' => 'pencil-square',
            'eliminar' => 'trash',
            default => 'information-circle',
        };
    }

    /**
     * Retorna el color del badge según la acción.
     * Verde para crear, amarillo para actualizar, rojo para eliminar.
     */
    public function getColor(): string
    {
        return match ($this->accion) {
            'crear' => 'green',
            'actualizar' => 'amber',
            'eliminar' => 'red',
            default => 'zinc',
        };
    }

    /**
     * Mapa de nombres legibles para las entidades del sistema.
     * Traduce el nombre del modelo a un nombre que el usuario entienda.
     */
    public static function nombresEntidades(): array
    {
        return [
            'Muestra' => 'Muestra',
            'Analisis' => 'Análisis',
            'Veterinaria' => 'Veterinaria',
            'Sucursal' => 'Sucursal',
            'Insumo' => 'Insumo',
            'Especie' => 'Especie',
            'User' => 'Usuario',
            'TipoAnalisis' => 'Tipo de Análisis',
            'PlantillaFormulario' => 'Plantilla',
            'UnidadMedida' => 'Unidad de Medida',
            'CategoriaInsumo' => 'Categoría de Insumo',
            'MovimientoInventario' => 'Movimiento de Inventario',
            'InventarioSucursal' => 'Inventario',
            'Resultado' => 'Resultado',
        ];
    }

    /**
     * Retorna el nombre legible de la entidad.
     */
    public function getNombreEntidad(): string
    {
        return self::nombresEntidades()[$this->entidad] ?? $this->entidad;
    }
}
