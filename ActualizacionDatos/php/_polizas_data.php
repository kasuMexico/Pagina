<?php
/********************************************************************************************
 * _polizas_data.php – Funciones y consulta de pólizas del cliente.
 * require_once TEMPRANO (antes de ModifDatos.php y MisPolizas.php).
 * Expone: $todasLasPolizas, clasificarProducto(), imagenProducto(), formatoMX()
 ********************************************************************************************/
declare(strict_types=1);

/* ===== Consultar todas las pólizas del cliente por CURP ===== */
$todasLasPolizas = [];
try {
    $sqlPolizas = 'SELECT v.`Id`, v.`IdContact`, v.`Nombre`, v.`TipoServicio`, v.`IdFIrma`,
                          v.`Producto`, v.`Status`, v.`Referencia_KASU`, v.`FechaRegistro`, v.`CostoVenta`
                   FROM `Venta` v
                   INNER JOIN `Usuario` u ON u.`IdContact` = v.`IdContact`
                   WHERE u.`ClaveCurp` = ?
                   ORDER BY v.`FechaRegistro` DESC';
    $stP = $mysqli->prepare($sqlPolizas);
    $stP->bind_param('s', $curp);
    $stP->execute();
    $rsP = $stP->get_result();
    while ($rsP && $row = $rsP->fetch_assoc()) {
        $todasLasPolizas[] = $row;
    }
    $stP->close();
} catch (Throwable $e) {
    error_log('_polizas_data SQL: ' . $e->getMessage());
    $todasLasPolizas = [];
}

/* ===== Helpers ===== */
function clasificarProducto(string $producto): string {
    $checkers = [
        'FUNERARIO'           => 'ProdFune',
        'OFICIAL DE SEGURIDAD'=> 'ProdPli',
        'TRANSPORTISTA'       => 'ProdTrans',
    ];
    foreach ($checkers as $label => $method) {
        if (method_exists($GLOBALS['basicas'], $method) && $GLOBALS['basicas']->$method($producto)) {
            return $label;
        }
    }
    return 'RETIRO';
}

function imagenProducto(string $tipo): string {
    $map = [
        'FUNERARIO'            => '/assets/images/Funerario_princ.png',
        'OFICIAL DE SEGURIDAD' => '/assets/images/registro/Oficiales-Seguridad.png',
        'TRANSPORTISTA'        => '/assets/images/registro/Registro-Servicio.png',
        'RETIRO'               => '/assets/images/registro/Plan-Retiro-Privado.png',
    ];
    return $map[$tipo] ?? '/assets/images/registro/Registro-Servicio.png';
}

function formatoMX(float $monto): string {
    return '$' . number_format($monto, 2, '.', ',');
}
