<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Umbral de Clasificación de Resultados (%)
    |--------------------------------------------------------------------------
    |
    | Porcentaje del rango que se usa como margen para clasificar resultados
    | como "alerta" (azul) antes de considerarlos "crítico" (rojo).
    |
    | Ejemplo: Con un rango de 7-12 y umbral de 15%:
    |   - Amplitud = 5, Umbral = 0.75
    |   - Alerta: 6.25–6.99 (bajo) o 12.01–12.75 (alto)
    |   - Crítico: < 6.25 o > 12.75
    |
    */
    'umbral_resultado' => (float) env('RESULTADO_UMBRAL_PORCENTAJE', 15) / 100,

];
