<?php
/**
 * Estado de Cuenta → Generación de PDF
 * Qué hace:
 *  - Recibe GET ?busqueda = base64(Id de Venta).
 *  - Carga Venta, Contacto y Usuario con consultas preparadas (mysqli).
 *  - Calcula saldo con Financieras->SaldoCredito si la póliza no está ACTIVO/ACTIVACION.
 *  - Inyecta datos en html/Estado_Cuenta.php y genera PDF con DOMPDF (legacy o namespaced).
 *  - Guarda el PDF en /DATES y lo envía al navegador.
 *
 * Seguridad:
 *  - Sanitiza parámetros.
 *  - Evita salida previa antes del stream del PDF.
 *
 * Dependencias:
 *  - ../../eia/librerias.php  (debe exponer $mysqli y clases Basicas/Financieras)
 *  - dompdfMaster/…           (DOMPDF legacy + autoload)
 *  - html/Estado_Cuenta.php   (template HTML)
 *
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

try {
    /* ===== Salida controlada ===== */
    if (!ob_get_level()) { ob_start(); }

    /* ===== Cargas base ===== */
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    require_once '../../eia/librerias.php';
    require_once 'dompdfMaster/dompdf_config.inc.php';
    require_once 'dompdfMaster/include/autoload.inc.php';

    // Soporte namespaced (si aplica)
    if (class_exists('\\Dompdf\\Dompdf')) {
        /** @noinspection PhpUnusedAliasInspection */
        use Dompdf\Dompdf;
        /** @noinspection PhpUnusedAliasInspection */
        use Dompdf\Options;
    }

    date_default_timezone_set('America/Mexico_City');

    /* ===== Parámetro de entrada ===== */
    if (!isset($_GET['busqueda'])) {
        throw new Exception("No se proporcionó el parámetro 'busqueda'.");
    }
    $dec = base64_decode((string)$_GET['busqueda'], true);
    if ($dec === false) {
        throw new Exception("Parámetro 'busqueda' inválido.");
    }
    $idVenta = (int)$dec;
    if ($idVenta <= 0) {
        throw new Exception("Id de venta inválido.");
    }

    /* ===== Consultas preparadas ===== */
    // Venta
    $stmtV = $mysqli->prepare("SELECT * FROM Venta WHERE Id = ? LIMIT 1");
    $stmtV->bind_param('i', $idVenta);
    $stmtV->execute();
    $ventaRes = $stmtV->get_result();
    $venta    = $ventaRes->fetch_assoc();
    $stmtV->close();
    if (!$venta) { throw new Exception("No se encontró la venta."); }

    $idContact = (int)$venta['IdContact'];

    // Contacto
    $stmtC = $mysqli->prepare("SELECT * FROM Contacto WHERE id = ? LIMIT 1");
    $stmtC->bind_param('i', $idContact);
    $stmtC->execute();
    $datosRes = $stmtC->get_result();
    $datos    = $datosRes->fetch_assoc();
    $stmtC->close();
    if (!$datos) { throw new Exception("No se encontró el contacto."); }

    // Usuario
    $stmtU = $mysqli->prepare("SELECT * FROM Usuario WHERE IdContact = ? LIMIT 1");
    $stmtU->bind_param('i', $idContact);
    $stmtU->execute();
    $personaRes = $stmtU->get_result();
    $persona    = $personaRes->fetch_assoc();
    $stmtU->close();
    if (!$persona) { throw new Exception("No se encontró el usuario."); }

    /* ===== Cálculos financieros ===== */
    $financieras = new Financieras();

    $nombre   = (string)($persona['Nombre'] ?? 'Desconocido');
    $numPagos = (int)($venta['NumeroPagos'] ?? 0);
    $status   = (string)($venta['Status'] ?? '');

    if ($status === "ACTIVO" || $status === "ACTIVACION") {
        $saldo = number_format(0, 2);
    } else {
        $saldo = number_format((float)$financieras->SaldoCredito($mysqli, $idVenta), 2);
    }
    $Credito = ($numPagos >= 2) ? "Compra a crédito; {$numPagos} Meses" : "Compra de contado";

    /* ===== Render del template ===== */
    // El template puede usar: $venta, $datos, $persona, $saldo, $Credito, $nombre
    ob_start();
    require __DIR__ . '/html/Estado_Cuenta.php';
    $html = ob_get_clean();
    if ($html === '' || $html === false) {
        throw new Exception('El template Estado_Cuenta.php no produjo salida.');
    }

    /* ===== DOMPDF ===== */
    if (class_exists('DOMPDF')) {
        // Legacy
        $pdf = new DOMPDF();
        if (method_exists($pdf, 'set_option')) {
            $pdf->set_option('enable_html5_parser', true);
            $pdf->set_option('enable_remote', true);
        }
    } else {
        // Namespaced
        $opts = new Options();
        $opts->set('enable_html5_parser', true);
        $opts->set('isRemoteEnabled', true);
        $pdf = new Dompdf($opts);
    }

    $pdf->set_paper("A4", "portrait");
    $pdf->load_html($html);
    $pdf->render();
    $output = $pdf->output();

    /* ===== Persistencia y entrega ===== */
    $NomFichas = preg_replace('/\s+/', '', $nombre);
    $nombrePdf = "EDOCTA_{$NomFichas}.pdf";

    $dirOut = __DIR__ . '/DATES';
    if (!is_dir($dirOut)) { @mkdir($dirOut, 0775, true); }
    @file_put_contents($dirOut . '/' . $nombrePdf, $output);

    if (ob_get_length()) { ob_end_clean(); }
    // Para inline usa: $pdf->stream($nombrePdf, ['Attachment' => 0]);
    $pdf->stream($nombrePdf);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    http_response_code(500);
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "<br>";
    echo "<strong>Línea:</strong> " . (int)$e->getLine() . "<br>";
}
