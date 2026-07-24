<?php
/**
 * Migración: Poblar tabla Amortizacion para ventas a crédito existentes.
 * Ejecutar: php populate_amortizacion.php [--dry-run]
 */
declare(strict_types=1);

require_once __DIR__ . '/../librerias.php';

$dryRun = in_array('--dry-run', $argv ?? [], true);

// Solo ventas a crédito (NumeroPagos > 1)
$sql = "SELECT v.Id, v.Producto, v.CostoVenta, v.NumeroPagos, v.FechaRegistro,
               p.Perido, p.TasaAnual
        FROM Venta v
        JOIN Productos p ON p.Producto = v.Producto
        WHERE v.NumeroPagos > 1
        ORDER BY v.Id";

$res = $mysqli->query($sql);
if (!$res) {
    die("Error consultando ventas: " . $mysqli->error);
}

$creadas  = 0;
$errores  = 0;

while ($venta = $res->fetch_assoc()) {
    $idVenta    = (int)$venta['Id'];
    $costoVenta = (float)$venta['CostoVenta'];
    $numMeses   = (int)$venta['NumeroPagos'];
    $tasaAnual  = (float)$venta['TasaAnual'];
    $perido     = max(1, (int)$venta['Perido']);
    $fechaAlta  = new DateTime($venta['FechaRegistro']);

    $totalPeriodos = $numMeses * $perido;

    // Tasa por período
    $periodosPorAno = 12 * $perido;
    $tasaPeriodo    = ($tasaAnual / 100) / $periodosPorAno;

    // Cuota del sistema francés (=PAGO de Excel)
    if ($tasaPeriodo == 0) {
        $cuota = round($costoVenta / $totalPeriodos, 2);
    } else {
        $factor = pow(1 + $tasaPeriodo, $totalPeriodos);
        $cuota  = round(($costoVenta * $tasaPeriodo * $factor) / ($factor - 1), 2);
    }

    // Primer vencimiento
    $stepDias = max(1, (int)floor(30 / $perido));
    $y = (int)$fechaAlta->format('Y');
    $m = (int)$fechaAlta->format('m');
    $d = (int)$fechaAlta->format('d');

    if ($perido === 1) {
        $venc = new DateTime(date('Y-m-t', $fechaAlta->getTimestamp()));
        if ($venc <= $fechaAlta) {
            $venc = (new DateTime("{$y}-{$m}-01"))->modify('last day of next month');
        }
    } elseif ($perido === 2) {
        if ($d <= 15) {
            $venc = new DateTime("{$y}-{$m}-15");
        } else {
            $venc = new DateTime(date('Y-m-t', $fechaAlta->getTimestamp()));
        }
        if ($venc <= $fechaAlta) {
            $venc->modify("+{$stepDias} days");
        }
    } else {
        $venc = new DateTime("{$y}-{$m}-01");
        while ($venc <= $fechaAlta) {
            $venc->modify("+{$stepDias} days");
        }
    }

    // Generar plan de amortización
    $saldoInsoluto = $costoVenta;

    for ($num = 1; $num <= $totalPeriodos; $num++) {
        $interes  = round($saldoInsoluto * $tasaPeriodo, 2);
        $capital  = round($cuota - $interes, 2);
        $saldoInsoluto = round($saldoInsoluto - $capital, 2);

        // Corregir última cuota por redondeo
        if ($num === $totalPeriodos && abs($saldoInsoluto) > 0.01) {
            $capital        += $saldoInsoluto;
            $saldoInsoluto   = 0.0;
        }

        $fechaVenc = $venc->format('Y-m-d');

        if (!$dryRun) {
            $stmt = $mysqli->prepare("
                INSERT IGNORE INTO Amortizacion
                    (IdVenta, NumeroCuota, FechaVencimiento, MontoCuota, Capital, Interes, SaldoInsoluto, Estatus)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDIENTE')
            ");
            $stmt->bind_param('iisdddd', $idVenta, $num, $fechaVenc, $cuota, $capital, $interes, $saldoInsoluto);
            if (!$stmt->execute()) {
                echo "  ERROR Venta {$idVenta} cuota {$num}: " . $stmt->error . "\n";
                $errores++;
            }
            $stmt->close();
        }

        $venc->modify("+{$stepDias} days");
        $creadas++;
    }
}

$res->close();

$modo = $dryRun ? 'DRY-RUN' : 'REAL';
echo "\n[$modo] Amortizacion: {$creadas} cuotas generadas, {$errores} errores.\n";
