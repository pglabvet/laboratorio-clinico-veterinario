<?php
// 1. Fix controller: change "Kardex PEPS" to "Inventario" in titles
$controller = __DIR__ . '/app/Http/Controllers/KardexExportController.php';
$c = file_get_contents($controller);
$c = str_replace("'Kardex PEPS: '", "'Inventario: '", $c);
$c = str_replace("'Kardex PEPS - Categoria: '", "'Inventario - Categoria: '", $c);
$c = str_replace("'Kardex PEPS'", "'Inventario'", $c);
file_put_contents($controller, $c);
echo "[Controller] Replaced 'Kardex PEPS' -> 'Inventario'\n";

// Also fix KardexPeps.php livewire component titles
$peps = __DIR__ . '/app/Livewire/Inventario/KardexPeps.php';
$p = file_get_contents($peps);
$p = str_replace("'Kardex: '", "'Inventario: '", $p);
$p = str_replace("'Kardex Categoría: '", "'Inventario - Categoría: '", $p);
$p = str_replace("'Kardex PEPS'", "'Inventario'", $p);
file_put_contents($peps, $p);
echo "[KardexPeps] Replaced Kardex titles -> Inventario\n";

// 2. Fix PDF blade: use body padding instead of @page margin, and remove "Kardex PEPS" from footer
$pdf = __DIR__ . '/resources/views/exports/kardex-pdf.blade.php';
$blade = file_get_contents($pdf);

// Replace @page margin with smaller top/bottom but large sides
$blade = str_replace(
    "@page {\n            margin: 25px 60px;\n        }",
    "@page {\n            margin: 20px 50px 20px 50px;\n        }",
    $blade
);

// Also try with \r\n
$blade = str_replace(
    "@page {\r\n            margin: 25px 60px;\r\n        }",
    "@page {\r\n            margin: 20px 50px 20px 50px;\r\n        }",
    $blade
);

// Change footer text
$blade = str_replace('Kardex PEPS &middot; ', '', $blade);

// Change title tag
$blade = str_replace('<title>Kardex PEPS</title>', '<title>Inventario</title>', $blade);

file_put_contents($pdf, $blade);
echo "[PDF Blade] Updated margins and removed 'Kardex PEPS' references\n";

// 3. Also update the controller to set margins via dompdf options
$c2 = file_get_contents($controller);
$c2 = str_replace(
    "->setPaper('a4', 'landscape');",
    "->setPaper('a4', 'landscape')\n        ->setOption(['margin-left' => 50, 'margin-right' => 50]);",
    $c2
);
file_put_contents($controller, $c2);
echo "[Controller] Added dompdf margin options\n";

echo "\nDone!\n";
