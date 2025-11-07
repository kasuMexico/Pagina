<?php
/***********************************************************************************************
 * maintenance_script.php
 * Muestra acumulados persistentes y snapshot en memoria. Guarda un resumen agregado.
 * Fecha: 2025-11-04
 * Revisado por: JCCM
 ***********************************************************************************************/

require_once __DIR__ . '/FunctionUsageTracker.php';

// Lee y agrega el histórico desde el archivo persistente
$hist = FunctionUsageTracker::readPersistent(__DIR__ . '/usage_log.txt');

// También muestra el snapshot de la petición actual, por si este script
// se incluye después de ejecutar lógica que llama trackUsage() en la MISMA petición.
$snapshot = FunctionUsageTracker::getUsage();

$isCli = (PHP_SAPI === 'cli');

$print = function(string $s) use ($isCli) {
    if ($isCli) { echo $s; }
    else { echo '<pre>' . htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>'; }
};

$buf  = "Reporte de uso de funciones (histórico acumulado):\n";
if (empty($hist)) {
    $buf .= "Sin datos persistentes aún. Verifica que se estén ejecutando métodos con trackUsage().\n";
} else {
    foreach ($hist as $funcion => $conteo) {
        $buf .= $funcion . " => " . (int)$conteo . "\n";
    }
}

$buf .= "\nSnapshot de esta petición:\n";
if (empty($snapshot)) {
    $buf .= "(vacío)\n";
} else {
    foreach ($snapshot as $funcion => $conteo) {
        $buf .= $funcion . " => " . (int)$conteo . "\n";
    }
}

$print($buf);

// Guarda un resumen agregado legible
@file_put_contents(__DIR__ . '/usage_report.txt', $buf);