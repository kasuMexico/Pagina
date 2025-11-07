<?php
/**
 * Cotización → PDF (PHP 8.2 + dompdf 3.x)
 * Ubicación: /public_html/login/Generar_PDF/Cotizacion_pdf.php
 */

declare(strict_types=1);

header_remove('X-Powered-By');
date_default_timezone_set('America/Mexico_City');

// Rutas base
$WEBROOT = dirname(__DIR__, 2);      // -> /public_html
$HERE    = __DIR__;                   // -> /public_html/login/Generar_PDF

// Dependencias (nota: carpeta composer es "vendor" en minúsculas)
require $WEBROOT . '/vendor/autoload.php';
require $WEBROOT . '/eia/librerias.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    if (!ob_get_level()) { ob_start(); }

    // Conexión DB ($pros o $mysqli)
    $DB = null;
    if (isset($pros) && $pros instanceof mysqli)       { $DB = $pros; }
    elseif (isset($mysqli) && $mysqli instanceof mysqli){ $DB = $mysqli; }
    if (!$DB) { throw new RuntimeException('Conexión MySQL no disponible.'); }

    // ===== Param: id de propuesta =====
    $idPropuesta = 0;
    if (isset($_GET['idp']) && $_GET['idp'] !== '') {
        $idPropuesta = (int)$_GET['idp'];
    } elseif (isset($_POST['busqueda']) && $_POST['busqueda'] !== '') {
        $idPropuesta = (int)$_POST['busqueda'];
    } elseif (isset($_GET['busqueda']) && $_GET['busqueda'] !== '') {
        $dec = base64_decode((string)$_GET['busqueda'], true);
        if ($dec !== false) { $idPropuesta = (int)$dec; }
    }
    if ($idPropuesta <= 0) {
        http_response_code(400);
        throw new InvalidArgumentException('Parámetro de búsqueda inválido.');
    }

    // ===== PrespEnviado =====
    $stmtP = $DB->prepare('SELECT * FROM PrespEnviado WHERE Id=? LIMIT 1');
    $stmtP->bind_param('i', $idPropuesta);
    $stmtP->execute();
    $Propuest = $stmtP->get_result()->fetch_assoc();
    $stmtP->close();
    if (!$Propuest) { http_response_code(404); exit('Registro no encontrado'); }

    // ===== Prospecto =====
    $idProspecto = (int)$Propuest['IdProspecto'];
    $stmtS = $DB->prepare('SELECT * FROM prospectos WHERE Id=? LIMIT 1');
    $stmtS->bind_param('i', $idProspecto);
    $stmtS->execute();
    $Prospecto = $stmtS->get_result()->fetch_assoc();
    $stmtS->close();
    if (!$Prospecto) { http_response_code(404); exit('Prospecto no encontrado'); }

    // ===== HTML (usa $Propuest y $Prospecto) =====
    ob_start();
    require $HERE . '/html/Cotizacion.php';
    $html = (string)ob_get_clean();
    if ($html === '') { throw new RuntimeException('El template no produjo salida.'); }

    // ===== DOMPDF =====
    $opts = new Options();
    $opts->set('isRemoteEnabled', true);
    $opts->set('defaultFont', 'DejaVu Sans');
    $opts->set('chroot', $WEBROOT); // restringe a /public_html

    $pdf = new Dompdf($opts);
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->setPaper('A4', 'portrait');
    $pdf->render();
    $pdfBytes = $pdf->output();

    // ===== Guardado =====
    $NomFichas = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '', (string)($Prospecto['FullName'] ?? 'Prospecto')));
    $nombrePdf = "Propuesta_{$NomFichas}.pdf";
    $dirOut = $HERE . '/DATES';
    if (!is_dir($dirOut) && !@mkdir($dirOut, 0775, true) && !is_dir($dirOut)) {
        throw new RuntimeException('No se pudo crear el directorio DATES.');
    }
    @file_put_contents($dirOut . '/' . $nombrePdf, $pdfBytes);

    // ===== Descarga =====
    if (ob_get_length()) { ob_end_clean(); }
    header('Content-Type: application/pdf');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="' . $nombrePdf . '"');
    header('Content-Length: ' . strlen($pdfBytes));
    header('Cache-Control: private, max-age=0, must-revalidate');
    echo $pdfBytes;
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    http_response_code(500);
    echo 'Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . ' @' . (int)$e->getLine();
}