<?php
// maintenance_script.php

// Incluir el archivo que contiene la clase FunctionUsageTracker y el trait UsageTrackerTrait.
require_once 'FunctionUsageTracker.php';

// Opcional: Si tus clases ya fueron utilizadas en la ejecución de tu aplicación,
// el tracker tendrá registros de uso. De lo contrario, este script imprimirá un reporte vacío.

// Opción 1: Imprimir el reporte de uso en pantalla.
$uso = FunctionUsageTracker::getUsage();
echo "Reporte de uso de funciones:\n";
if (empty($uso)) {
    echo "No se ha registrado el uso de ninguna función.\n";
} else {
    foreach ($uso as $funcion => $conteo) {
        echo "$funcion se ha llamado $conteo veces.\n";
    }
}

// Opción 2: Guardar el reporte en un archivo (por ejemplo, usage_log.txt).
FunctionUsageTracker::outputUsage('usage_log.txt');
echo "\nEl reporte de uso se ha guardado en 'usage_log.txt'.\n";
?>
