<?php
/**
 * Generar PDF de póliza del cliente (DOMPDF 3.x via Composer)
 * - Lee IdContact en base64 desde ?busqueda
 * - Consulta Usuario, Contacto y Venta
 * - Verifica estatus ACTIVO/ACTIVACION
 * - Renderiza html/Poliza_Servicio.php a PDF
 * - Guarda en /DATES y hace stream
 * PHP 8.2
 */

declare(strict_types=1);

header_remove('X-Powered-By');
date_default_timezone_set('America/Mexico_City');

$WEBROOT = dirname(__DIR__, 2); // /public_html

// Dependencias
require $WEBROOT . '/vendor/autoload.php';           // Composer (dompdf/dompdf)
require $WEBROOT . '/eia/Conexiones/cn_vtas.php';    // Debe exponer $mysqli (mysqli)

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    if (!ob_get_level()) { ob_start(); }

    // Entrada
    $busqueda = $_GET['busqueda'] ?? '';
    $dat = base64_decode($busqueda, true);
    if ($dat === false || $dat === '') {
        throw new RuntimeException('Parámetro "busqueda" inválido.');
    }
    $idContact = (int)$dat;
    if ($idContact <= 0) {
        throw new RuntimeException('IdContact inválido.');
    }

    // ===== Consultas =====
    $curp = $cont = $email = $phone = $direccion = $calle = $numero = $colonia = $municipio = $codigo_postal = $estado = '';
    $name = $Producto = $TipoServicio = $Status = $IdFIrma = $FechaR = '';
    $Costo = 0.0;

    // Usuario
    $sql = "SELECT `ClaveCurp`, `IdContact` FROM `Usuario` WHERE `IdContact`=? LIMIT 1";
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $idContact);
    $st->execute();
    $st->bind_result($curp, $cont);
    $st->fetch();
    $st->close();

    if ((int)$cont <= 0) {
        throw new RuntimeException('Usuario no encontrado.');
    }

    // Contacto
    $sql = "SELECT `Mail`, `Telefono`, `calle`, `numero`, `colonia`, `municipio`, `codigo_postal`, `estado` FROM `Contacto` WHERE `id`=? LIMIT 1";
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $cont);
    $st->execute();
    $st->bind_result($Mail, $Telefono, $calle, $numero , $colonia , $municipio , $codigo_postal , $estado);
    $st->fetch();
    $st->close();

    //Armamos la direccion
    $direccion = $calle.' '.$numero.' '.$colonia.' '.$municipio.' '.$codigo_postal.' '.$estado;
    // Venta (última por fecha)
    $sql = "SELECT `Nombre`,`Producto`,`TipoServicio`,`CostoVenta`,`Status`,`IdFIrma`,`FechaRegistro`
            FROM `Venta`
            WHERE `IdContact`=?
            ORDER BY `FechaRegistro` DESC
            LIMIT 1";
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $cont);
    $st->execute();
    $st->bind_result($name, $Producto, $TipoServicio, $Costo, $Status, $IdFIrma, $FechaR);
    $st->fetch();
    $st->close();

    if ($name === '') {
        throw new RuntimeException('Venta no encontrada para el contacto.');
    }

    // Formato moneda
    $Costo = (float)$Costo;
    if (class_exists('NumberFormatter')) {
        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
        $CostoFormateado = $fmt->formatCurrency($Costo, 'MXN');
    } else {
        $CostoFormateado = '$' . number_format($Costo, 2, '.', ',') . ' MXN';
    }

    // Descripción producto
    if ($Producto === "Universidad") {
        $productoA = "Servicio Universitario";
    } else {
        $productoA = substr((string)$Producto, 0, 3) . " a " . substr((string)$Producto, 3, 2) . " años";
    }

    // Valida estatus
    if ($Status !== "ACTIVO" && $Status !== "ACTIVACION") {
        echo '<script type="text/javascript">
                alert("Esta póliza no se encuentra liquidada en su totalidad");
                window.location="https://kasu.com.mx/";
              </script>';
        if (ob_get_level()) { ob_end_flush(); }
        exit;
    }

    // HTML de la póliza
    ob_start();
    require __DIR__ . '/html/Poliza_Servicio.php';
    $html = (string)ob_get_clean();
    if ($html === '') {
        throw new RuntimeException('El template Poliza_Servicio.php no produjo salida.');
    }

    // Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);         // permitir imágenes/CSS por https
    $options->set('chroot', $WEBROOT);              // restringe a /public_html
    $options->set('defaultFont', 'DejaVu Sans');    // Unicode seguro

    $pdf = new Dompdf($options);
    $pdf->setPaper('A4', 'portrait');
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();
    $output = $pdf->output();

    // Guardado
    $NomFichas = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '', $name));
    $nombrePdf = "POLIZA_{$NomFichas}.pdf";
    $dirOut = __DIR__ . '/DATES';
    if (!is_dir($dirOut) && !@mkdir($dirOut, 0775, true) && !is_dir($dirOut)) {
        throw new RuntimeException('No se pudo crear el directorio DATES.');
    }
    file_put_contents($dirOut . '/' . $nombrePdf, $output);

    // Entrega (descarga por defecto). Para ver inline usa Attachment=false.
    $pdf->stream($nombrePdf /* , ['Attachment' => false] */);

    if (ob_get_level()) { ob_end_flush(); }

} catch (Throwable $e) {
    http_response_code(500);
    if (ob_get_level()) { ob_end_clean(); }
    echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "<br>";
    echo "Línea: " . (int)$e->getLine() . "<br>";
    echo "<pre>"; var_dump($e->getTrace()); echo "</pre>";
}