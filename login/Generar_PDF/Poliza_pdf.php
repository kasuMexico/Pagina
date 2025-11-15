<?php
declare(strict_types=1);

header_remove('X-Powered-By');
date_default_timezone_set('America/Mexico_City');

$WEBROOT = dirname(__DIR__, 2);

require $WEBROOT . '/vendor/autoload.php';
require $WEBROOT . '/eia/Conexiones/cn_vtas.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $busqueda = $_GET['busqueda'] ?? '';
    $dat = base64_decode($busqueda, true);
    if ($dat === false || $dat === '') {
        throw new RuntimeException('Parámetro "busqueda" inválido.');
    }
    $idContact = (int)$dat;
    if ($idContact <= 0) {
        throw new RuntimeException('IdContact inválido.');
    }

    // ===== Usuario =====
    $st = $mysqli->prepare("SELECT `ClaveCurp`, `IdContact` FROM `Usuario` WHERE `IdContact`=? LIMIT 1");
    $st->bind_param('i', $idContact);
    $st->execute();
    $st->bind_result($curp, $cont);
    $st->fetch();
    $st->close();
    if ((int)$cont <= 0) {
        throw new RuntimeException('Usuario no encontrado.');
    }

    // ===== Contacto =====
    $Mail = $Telefono = $calle = $numero = $colonia = $municipio = $codigo_postal = $estado = '';
    $st = $mysqli->prepare("SELECT `Mail`,`Telefono`,`calle`,`numero`,`colonia`,`municipio`,`codigo_postal`,`estado` FROM `Contacto` WHERE `id`=? LIMIT 1");
    $st->bind_param('i', $cont);
    $st->execute();
    $st->bind_result($Mail,$Telefono,$calle,$numero,$colonia,$municipio,$codigo_postal,$estado);
    $st->fetch();
    $st->close();

    $email = $Mail;
    $phone = $Telefono;
    $direccion = trim("$calle $numero $colonia $municipio $codigo_postal $estado");

    // ===== Venta (última) =====
    $name = $Producto = $TipoServicio = $Status = $IdFIrma = $FechaR = '';
    $Costo = 0.0;

    $st = $mysqli->prepare(
        "SELECT `Nombre`,`Producto`,`TipoServicio`,`CostoVenta`,`Status`,`IdFIrma`,`FechaRegistro`
         FROM `Venta`
         WHERE `IdContact`=?
         ORDER BY `FechaRegistro` DESC
         LIMIT 1"
    );
    $st->bind_param('i', $cont);
    $st->execute();
    $st->bind_result($name,$Producto,$TipoServicio,$Costo,$Status,$IdFIrma,$FechaR);
    $st->fetch();
    $st->close();

    if ($name === '') {
        throw new RuntimeException('Venta no encontrada para el contacto.');
    }

    if ($Status !== 'ACTIVO' && $Status !== 'ACTIVACION') {
        $msg = 'Esta póliza no se encuentra liquidada en su totalidad';
        header('Location: https://kasu.com.mx/?Msg=' . rawurlencode($msg), true, 303);
        exit;
    }

    // ===== Monto formateado =====
    $Costo = (float)$Costo;
    if (class_exists('NumberFormatter')) {
        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
        $CostoFormateado = $fmt->formatCurrency($Costo, 'MXN');
    } else {
        $CostoFormateado = '$' . number_format($Costo, 2, '.', ',') . ' MXN';
    }
    $Costo = $CostoFormateado;

    // ===== Descripción de producto (solo funerario) =====
    // Convierte "30a49" -> "30 a 49 años". Si no matchea, deja el valor tal cual.
    if (preg_match('/^(\d{1,2})a(\d{1,2})$/', (string)$Producto, $m)) {
        $productoA = "{$m[1]} a {$m[2]} años";
    } else {
        $productoA = (string)$Producto;
    }

    // ===== Render HTML =====
    ob_start();
    require __DIR__ . '/html/Poliza_Servicio.php';
    $html = (string)ob_get_clean();
    if ($html === '') {
        throw new RuntimeException('El template Poliza_Servicio.php no produjo salida.');
    }

    // ===== PDF =====
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', $WEBROOT);
    $options->set('defaultFont', 'DejaVu Sans');

    $pdf = new Dompdf($options);
    $pdf->setPaper('letter', 'portrait');
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();
    $output = $pdf->output();

    // ===== Guardar y descargar =====
    $NomFichas = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '', $name));
    $nombrePdf = "POLIZA_{$NomFichas}.pdf";
    $dirOut = __DIR__ . '/DATES';
    if (!is_dir($dirOut) && !@mkdir($dirOut, 0775, true) && !is_dir($dirOut)) {
        throw new RuntimeException('No se pudo crear el directorio DATES.');
    }
    file_put_contents($dirOut . '/' . $nombrePdf, $output);

    $pdf->stream($nombrePdf);
    exit;

} catch (Throwable $e) {
    if (!headers_sent()) {
        header('Location: https://kasu.com.mx/?Msg=' . rawurlencode('Error: ' . $e->getMessage()), true, 303);
        exit;
    }
    echo 'Error al generar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
