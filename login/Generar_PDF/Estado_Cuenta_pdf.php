<?php
/**
 * Estado de Cuenta → Generación de PDF (Composer / vendor)
 * - Carga Dompdf desde vendor/autoload.php
 * - Consulta Venta/Contacto/Usuario
 * - Calcula saldo con Financieras->SaldoCredito
 * - Renderiza html/Estado_Cuenta.php
 * - Guarda en /DATES y sirve al navegador
 *
 * Fecha: 07/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

try {
    // ===== Salida controlada
    if (!ob_get_level()) { ob_start(); }

    // ===== Entorno
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
    date_default_timezone_set('America/Mexico_City');

    // ===== Autoload Composer (buscar vendor en rutas típicas)
    $candidates = [
        dirname(__DIR__, 2) . '/vendor/autoload.php', // ../../vendor
        dirname(__DIR__)     . '/vendor/autoload.php', // ../vendor
        __DIR__              . '/vendor/autoload.php', // ./vendor
        $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    ];
    $autoload = null;
    foreach ($candidates as $cand) {
        if (is_file($cand)) { $autoload = $cand; break; }
    }
    if ($autoload === null) {
        throw new Exception('No se encontró vendor/autoload.php. Ejecuta "composer install".');
    }
    require_once $autoload;

    // ===== Dependencias de tu app
    require_once '../../eia/librerias.php'; // Debe exponer $mysqli y clases Basicas/Financieras

    // ===== Entrada
    if (!isset($_GET['busqueda'])) {
        throw new Exception("Falta el parámetro 'busqueda'.");
    }
    $dec = base64_decode((string)$_GET['busqueda'], true);
    if ($dec === false) {
        throw new Exception("Parámetro 'busqueda' inválido.");
    }
    $idVenta = (int)$dec;
    if ($idVenta <= 0) {
        throw new Exception("Id de venta inválido.");
    }

    // ===== Consultas
    $stmtV = $mysqli->prepare("SELECT * FROM Venta WHERE Id = ? LIMIT 1");
    $stmtV->bind_param('i', $idVenta);
    $stmtV->execute();
    $venta = $stmtV->get_result()->fetch_assoc();
    $stmtV->close();
    if (!$venta) { throw new Exception("No se encontró la venta."); }

    $idContact = (int)$venta['IdContact'];

    $stmtC = $mysqli->prepare("SELECT * FROM Contacto WHERE id = ? LIMIT 1");
    $stmtC->bind_param('i', $idContact);
    $stmtC->execute();
    $datos = $stmtC->get_result()->fetch_assoc();
    $stmtC->close();
    if (!$datos) { throw new Exception("No se encontró el contacto."); }

    $stmtU = $mysqli->prepare("SELECT * FROM Usuario WHERE IdContact = ? LIMIT 1");
    $stmtU->bind_param('i', $idContact);
    $stmtU->execute();
    $persona = $stmtU->get_result()->fetch_assoc();
    $stmtU->close();
    if (!$persona) { throw new Exception("No se encontró el usuario."); }

    // ===== Cálculos
    $financieras = new Financieras();

    $nombre   = (string)($persona['Nombre'] ?? 'Desconocido');
    $numPagos = (int)($venta['NumeroPagos'] ?? 0);
    $status   = (string)($venta['Status'] ?? '');

    if ($status === "ACTIVO" || $status === "ACTIVACION") {
        $saldo = number_format(0, 2);
    } else {
        $saldo = $financieras->SaldoCredito($mysqli, $idVenta);
    }
    $Credito = ($numPagos >= 2) ? "Compra a crédito; {$numPagos} Meses" : "Compra de contado";

    // ===== Render del template
    // El template puede usar: $venta, $datos, $persona, $saldo, $Credito, $nombre
    ob_start();
    require __DIR__ . '/html/Estado_Cuenta.php';
    $html = ob_get_clean();
    if ($html === '' || $html === false) {
        throw new Exception('El template html/Estado_Cuenta.php no produjo salida.');
    }

    // ===== Dompdf desde vendor
    $opts = new \Dompdf\Options();
    $opts->set('enable_html5_parser', true);
    $opts->set('isRemoteEnabled', true);             // permitir http/https para assets
    $opts->set('defaultFont', 'DejaVu Sans');        // cobertura UTF-8
    // Limita acceso de archivos al directorio actual (resuelve assets relativos del template)
    $opts->setChroot(__DIR__);

    $dompdf = new \Dompdf\Dompdf($opts);

    // BasePath ayuda a resolver enlaces relativos de CSS/IMG dentro del HTML
    if (method_exists($dompdf, 'setBasePath')) {
        $dompdf->setBasePath(__DIR__ . '/html/');
    }

    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $output = $dompdf->output();

    // ===== Persistencia y entrega
    $NomFichas = preg_replace('/[^A-Za-z0-9_-]+/', '', preg_replace('/\s+/', '', $nombre));
    $NomFichas = $NomFichas !== '' ? $NomFichas : 'EDOCTA';
    $nombrePdf = "EDOCTA_{$NomFichas}.pdf";

    $dirOut = __DIR__ . '/DATES';
    if (!is_dir($dirOut) && !@mkdir($dirOut, 0775, true) && !is_dir($dirOut)) {
        throw new Exception('No se pudo crear el directorio de salida DATES.');
    }
    @file_put_contents($dirOut . '/' . $nombrePdf, $output);

    if (ob_get_length()) { ob_end_clean(); }
    // Inline en navegador; usa Attachment=>1 si quieres forzar descarga
    $dompdf->stream($nombrePdf, ['Attachment' => 0]);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    http_response_code(500);
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "<br>";
    echo "<strong>Línea:</strong> " . (int)$e->getLine() . "<br>";
}
