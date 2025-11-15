<?php
/**
 * Contrato de Ejecutivo → Generación de PDF
 * Qué hace:
 *  - Recibe GET 'Add' = Id de Contacto.
 *  - Consulta datos de Contacto y Empleado.
 *  - Renderiza html/Contrato_Ejecutivo.php y genera PDF con DOMPDF.
 *  - Guarda el PDF en /DATES y lo entrega al navegador.
 *
 * Seguridad:
 *  - PHP 8.2 compatible, consultas preparadas y saneo de entrada.
 *
 * Dependencias:
 *  - ../../eia/Conexiones/cn_vtas.php  (debe exponer $mysqli)
 *  - dompdfMaster/dompdf_config.inc.php
 *  - dompdfMaster/include/autoload.inc.php
 *  - html/Contrato_Ejecutivo.php
 *
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

try {
    // Control de salida para evitar "headers already sent"
    if (!ob_get_level()) { ob_start(); }

    // Core
    require_once '../../eia/Conexiones/cn_vtas.php';
    require_once 'dompdfMaster/dompdf_config.inc.php';
    @require_once 'dompdfMaster/include/autoload.inc.php'; // autoload DOMPDF si existe

    date_default_timezone_set('America/Mexico_City');
    $fecha = date('d-m-Y');

    // -------- Entrada segura --------
    if (!isset($_GET['Add'])) {
        http_response_code(400);
        throw new Exception("Falta parámetro 'Add'.");
    }
    $idContacto = (int)$_GET['Add'];
    if ($idContacto <= 0) {
        http_response_code(400);
        throw new Exception("Parámetro 'Add' inválido.");
    }

    // -------- Defaults para el template --------
    $Direccion = '';
    $EMail     = '';
    $Nombre    = '';
    $RFC       = '';
    $CLABE     = '';

    // -------- Contacto --------
    $sqlC = "SELECT Direccion, Mail FROM Contacto WHERE id = ? LIMIT 1";
    $stC  = $mysqli->prepare($sqlC);
    $stC->bind_param('i', $idContacto);
    $stC->execute();
    $rsC = $stC->get_result();
    if ($row2 = $rsC->fetch_assoc()) {
        $Direccion = (string)$row2['Direccion'];
        $EMail     = (string)$row2['Mail'];
    } else {
        http_response_code(404);
        throw new Exception('Contacto no encontrado.');
    }
    $stC->close();

    // -------- Empleado (por IdContacto) --------
    $sqlE = "SELECT Nombre, RFC, Cuenta FROM Empleados WHERE IdContacto = ? LIMIT 1";
    $stE  = $mysqli->prepare($sqlE);
    $stE->bind_param('i', $idContacto);
    $stE->execute();
    $rsE = $stE->get_result();
    if ($row3 = $rsE->fetch_assoc()) {
        $Nombre = (string)$row3['Nombre'];
        $RFC    = (string)$row3['RFC'];
        $CLABE  = (string)$row3['Cuenta'];
    } else {
        http_response_code(404);
        throw new Exception('Empleado no encontrado para este contacto.');
    }
    $stE->close();

    // -------- HTML del contrato --------
    ob_start();
    // El template usará variables: $fecha, $Direccion, $EMail, $Nombre, $RFC, $CLABE
    require __DIR__ . '/html/Contrato_Ejecutivo.php';
    $html = ob_get_clean();
    if ($html === '' || $html === false) {
        throw new Exception('El template no generó contenido.');
    }

    // -------- DOMPDF (legacy o namespaced) --------
    if (class_exists('DOMPDF')) {
        $pdf = new DOMPDF();
        if (method_exists($pdf, 'set_option')) {
            $pdf->set_option('enable_html5_parser', true);
            $pdf->set_option('enable_remote', true);
        }
    } else {
        $opts = new \Dompdf\Options();
        $opts->set('enable_html5_parser', true);
        $opts->set('isRemoteEnabled', true);
        $pdf = new \Dompdf\Dompdf($opts);
    }

    $pdf->set_paper('A4', 'portrait');
    $pdf->load_html($html);
    $pdf->render();
    $output = $pdf->output();

    // -------- Guardado en servidor --------
    $NomFichas = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace(' ', '', $Nombre ?: 'Ejecutivo'));
    $nombrePdf = "Contrato_{$NomFichas}.pdf";
    $dirOut    = __DIR__ . '/DATES';
    if (!is_dir($dirOut)) { @mkdir($dirOut, 0775, true); }
    @file_put_contents($dirOut . '/' . $nombrePdf, $output);

    // -------- Entrega al navegador --------
    if (ob_get_length()) { ob_end_clean(); }
    $pdf->stream($nombrePdf); // descarga directa; usar stream($nombrePdf, ['Attachment'=>0]) para inline
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    echo $e->getMessage();
    echo ' @Línea ' . (int)$e->getLine();
    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";
}
