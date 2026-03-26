<?php

namespace App\Helpers;

class FormatoHelper
{
    /**
     * Formatea un número decimal dinámicamente:
     * - Muestra hasta 6 decimales según lo necesite el número original.
     * - No corta ceros necesarios para llegar a un mínimo de 2 decimales (ej. 1 -> 1.00).
     * - Evita imprimir ceros fútiles a la derecha más allá de los 2 decimales (ej. 1.250000 -> 1.25, 0.000300 -> 0.0003).
     *
     * @param float|null $valor
     * @return string
     */
    public static function dinamico(?float $valor): string
    {
        if ($valor === null) {
            return '';
        }

        // 1. Mostrar hasta 6 decimales exactos.
        $formateado = number_format($valor, 6, '.', ',');

        // $formateado ahora tiene siempre 6 decimales literales. Ejemplo: "1.000000", "0.390000", "0.000001"
        // 2. Remover los ceros finales DESPUÉS del separador decimal
        $sinCerosSobrantes = rtrim($formateado, '0');
        
        // 3. Remover un punto solitario por si acabara en punto (e.g., "1.");
        $sinPuntoFinal = rtrim($sinCerosSobrantes, '.');
        
        // 4. Asegurar que al menos tenga 2 decimales para mantener consistencia visual.
        // Verificamos cuántos decimales restaron después de la limpieza.
        if (strpos($sinPuntoFinal, '.') === false) {
            // No tiene decimales, le agregamos ".00"
            return $sinPuntoFinal . '.00';
        }

        $partes = explode('.', $sinPuntoFinal);
        if (isset($partes[1]) && strlen($partes[1]) < 2) {
            // Tiene solo 1 decimal (ej. "1.5"), agregamos un "0"
            return $sinPuntoFinal . '0';
        }

        return $sinPuntoFinal;
    }
}
