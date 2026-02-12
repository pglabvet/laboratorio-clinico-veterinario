<?php

namespace App\Services;

use App\Models\InventarioSucursal;
use App\Models\Muestra;
use App\Models\PlantillaFormulario;
use App\Models\Sucursal;
use App\Models\TipoAnalisis;

class MuestraService
{
    /**
     * Generar código único para la muestra por sucursal.
     * Formato: {PREFIJO}AA0000 (Prefijo de sucursal + 2 letras + 4 dígitos)
     * Ejemplo: SAA0001 (Sucursal Sur), NAA0002 (Sucursal Norte)
     * Rango por sucursal: AA0000 - ZZ9999 (676 * 10,000 = 6,760,000 combinaciones)
     */
    public function generarCodigoMuestra(int $sucursalId): string
    {
        $sucursal = Sucursal::find($sucursalId);
        if (!$sucursal) {
            throw new \Exception('Sucursal no encontrada');
        }
        $prefijo = $sucursal->getPrefijo();

        // Obtener el último código de muestra de esta sucursal
        // lockForUpdate() previene race conditions cuando dos usuarios crean muestras simultáneamente
        $ultimaMuestra = Muestra::where('sucursal_id', $sucursalId)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if (!$ultimaMuestra) {
            return $prefijo . 'AA0001';
        }

        $ultimoCodigo = $ultimaMuestra->codigo_muestra;

        // Si no sigue el formato PREFIJOAA0000, empezar desde AA0001
        if (!preg_match('/^[A-Z]{1,2}([A-Z]{2})(\d{4})$/', $ultimoCodigo, $matches)) {
            return $prefijo . 'AA0001';
        }

        $letras = $matches[1];
        $numero = (int)$matches[2];

        $numero++;

        if ($numero > 9999) {
            $numero = 1;
            $letras = $this->incrementarLetras($letras);
        }

        return $prefijo . $letras . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Incrementar las letras del código (AA -> AB -> AC ... -> AZ -> BA -> BB ... -> ZZ)
     */
    private function incrementarLetras(string $letras): string
    {
        $letra1 = $letras[0];
        $letra2 = $letras[1];

        if ($letra2 === 'Z') {
            $letra2 = 'A';
            if ($letra1 === 'Z') {
                return 'AA';
            }
            else {
                $letra1 = chr(ord($letra1) + 1);
            }
        }
        else {
            $letra2 = chr(ord($letra2) + 1);
        }

        return $letra1 . $letra2;
    }

    /**
     * Validar stock disponible antes de crear análisis.
     * Acepta un array de plantilla IDs directamente.
     *
     * @param  array  $plantillaIds  IDs de plantillas a validar
     * @param  int  $sucursalId  ID de la sucursal
     * @return array{warnings: string[]}  Warnings de stock bajo (los errores lanzan excepción)
     *
     * @throws \Exception Si hay stock insuficiente
     */
    public function validarStockDisponible(array $plantillaIds, int $sucursalId): array
    {
        $insumosInsuficientes = [];
        $insumosStockBajo = [];

        foreach ($plantillaIds as $plantillaId) {
            $plantilla = PlantillaFormulario::with('insumos')->find($plantillaId);

            if (!$plantilla || $plantilla->insumos->isEmpty()) {
                continue;
            }

            foreach ($plantilla->insumos as $insumo) {
                $cantidadRequerida = $insumo->pivot->cantidad_requerida;

                $inventario = InventarioSucursal::where('insumo_id', $insumo->id)
                    ->where('sucursal_id', $sucursalId)
                    ->first();

                if (!$inventario || $inventario->stock_actual <= 0) {
                    $insumosInsuficientes[] = "{$insumo->nombre} (sin stock)";
                }
                elseif ($inventario->stock_actual < $cantidadRequerida) {
                    $insumosInsuficientes[] = "{$insumo->nombre} (Disponible: {$inventario->stock_actual}, Requerido: {$cantidadRequerida})";
                }
                elseif ($inventario->stock_actual <= $inventario->stock_minimo) {
                    $insumosStockBajo[] = $insumo->nombre;
                }
            }
        }

        if (!empty($insumosInsuficientes)) {
            throw new \Exception(
                '❌ No se puede crear el análisis. Los siguientes insumos tienen stock insuficiente: ' .
                implode(', ', $insumosInsuficientes) .
                '. Por favor, registre una entrada de inventario antes de continuar.'
                );
        }

        return ['warnings' => $insumosStockBajo];
    }

    /**
     * Validar stock por tipos de análisis (busca la plantilla activa de cada tipo).
     *
     * @param  array  $tipoAnalisisIds  IDs de tipos de análisis
     * @param  int  $sucursalId  ID de la sucursal
     * @return array{warnings: string[]}
     *
     * @throws \Exception Si hay stock insuficiente
     */
    public function validarStockPorTiposAnalisis(array $tipoAnalisisIds, int $sucursalId): array
    {
        $plantillaIds = [];

        $tiposAnalisis = TipoAnalisis::with('plantillas')
            ->whereIn('id', $tipoAnalisisIds)
            ->get();

        foreach ($tiposAnalisis as $tipoAnalisis) {
            $plantilla = $tipoAnalisis->plantillas()->where('activo', true)->first();
            if ($plantilla) {
                $plantillaIds[] = $plantilla->id;
            }
        }

        return $this->validarStockDisponible($plantillaIds, $sucursalId);
    }
}
