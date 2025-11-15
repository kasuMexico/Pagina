<?php
/**
 * Fichas_Pago → Generación de PDF (Composer / vendor)
 * Entradas:
 *   - GET ?busqueda = base64(IdContact)
 *   - POST IdVenta  = Id directo de Venta
 *   - GET  ?Cte     = base64(IdVenta)  (compat)
 *   - POST data     = fecha promesa (opcional)
 * Dependencias:
 *   - ../../eia/librerias.php  ($mysqli, clases)
 *   - vendor/autoload.php      (dompdf/dompdf)
 *   - html/Fichas_Pago.php     (template)
 * Fecha: 07/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

try {
    /* ===== Salida controlada ===== */
    if (!ob_get_level()) { ob_start(); }
    date_default_timezone_set('America/Mexico_City');
    error_reporting(E_ALL);

    /* ===== Autoload Composer ===== */
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

    /* ===== App deps ===== */
    require_once '../../eia/librerias.php'; // Debe definir $mysqli, Basicas, Financieras

    /* ===== Resolver parámetros ===== */
    $idVenta   = null;
    $idContact = null;
    $fecRaw    = $_POST['data'] ?? null;

    if (isset($_GET['busqueda'])) {
        $dec = base64_decode((string)$_GET['busqueda'], true);
        if ($dec === false) { throw new Exception('Parámetro "busqueda" inválido.'); }
        $idContact = (int)$dec;
    } elseif (isset($_POST['IdVenta'])) {
        $idVenta = (int)$_POST['IdVenta'];
    } elseif (isset($_GET['Cte'])) {
        $dec = base64_decode((string)$_GET['Cte'], true);
        if ($dec === false) { throw new Exception('Parámetro "Cte" inválido.'); }
        $idVenta = (int)$dec;
    }

    if ((!$idContact || $idContact <= 0) && (!$idVenta || $idVenta <= 0)) {
        throw new Exception('Parámetros de entrada insuficientes.');
    }

    // Fecha promesa amigable al template
    $fec = null;
    if (is_string($fecRaw)) {
        $fecRaw = trim($fecRaw);
        if ($fecRaw !== '') {
            $t = strtotime($fecRaw);
            $fec = $t ? date('Y-m-d', $t) : $fecRaw; // si no parsea, pasa literal
        }
    }

    /* ===== Cargar Venta ===== */
    if ($idVenta && $idVenta > 0) {
        $stmtV = $mysqli->prepare("SELECT * FROM Venta WHERE Id = ? LIMIT 1");
        $stmtV->bind_param('i', $idVenta);
    } else {
        $stmtV = $mysqli->prepare("SELECT * FROM Venta WHERE IdContact = ? ORDER BY FechaRegistro DESC LIMIT 1");
        $stmtV->bind_param('i', $idContact);
    }
    $stmtV->execute();
    $resV  = $stmtV->get_result();
    $venta = $resV->fetch_assoc();
    $stmtV->close();
    if (!$venta) { throw new Exception('Venta no encontrada.'); }

    // Nombre esperado por el template
    $row       = $venta;
    $idContact = (int)$venta['IdContact'];

    /* ===== Cargar Usuario y Contacto ===== */
    $stmtU = $mysqli->prepare("SELECT * FROM Usuario WHERE IdContact = ? LIMIT 1");
    $stmtU->bind_param('i', $idContact);
    $stmtU->execute();
    $usuario = $stmtU->get_result()->fetch_assoc();
    $stmtU->close();

    $stmtC = $mysqli->prepare("SELECT * FROM Contacto WHERE id = ? LIMIT 1");
    $stmtC->bind_param('i', $idContact);
    $stmtC->execute();
    $contacto = $stmtC->get_result()->fetch_assoc();
    $stmtC->close();

    /* ===== Instancias auxiliares ===== */
    // Si ya existen globales en librerias.php, estos new no afectan
    $basicas     = new Basicas();
    $financieras = new Financieras();

    /* ===== Render HTML ===== */
    // Variables disponibles en el template: $row, $fec, $basicas, $financieras, $usuario, $contacto
    ob_start();
    require __DIR__ . '/html/Fichas_Pago.php';
    $html = ob_get_clean();
    if ($html === '' || $html === false) {
        throw new Exception('El template Fichas_Pago.php no produjo salida.');
    }

    /* ===== Dompdf (vendor) ===== */
    $opts = new \Dompdf\Options();
    $opts->set('enable_html5_parser', true);
    $opts->set('isRemoteEnabled', true);
    $opts->set('defaultFont', 'DejaVu Sans'); // UTF-8 safe
    // Limita accesos de archivos al directorio actual
    $opts->setChroot(__DIR__);

    $dompdf = new \Dompdf\Dompdf($opts);
    if (method_exists($dompdf, 'setBasePath')) {
        $dompdf->setBasePath(__DIR__ . '/html/'); // assets relativos del template
    }

    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    /* ===== Persistencia y entrega ===== */
    $nombreCliente = '';
    if (!empty($usuario['Nombre']))         $nombreCliente = (string)$usuario['Nombre'];
    elseif (!empty($contacto['Nombre']))    $nombreCliente = (string)$contacto['Nombre'];
    elseif (!empty($row['Nombre']))         $nombreCliente = (string)$row['Nombre'];
    else                                    $nombreCliente = 'Cliente';

    $NomFichas = preg_replace('/[^A-Za-z0-9_-]+/', '', preg_replace('/\s+/', '', $nombreCliente));
    if ($NomFichas === '') $NomFichas = 'FICHAS';
    $nombrePdf = "FICHAS_{$NomFichas}.pdf";

    $rutaCarpeta = __DIR__ . '/DATES';
    if (!is_dir($rutaCarpeta) && !@mkdir($rutaCarpeta, 0775, true) && !is_dir($rutaCarpeta)) {
        throw new Exception('No se pudo crear el directorio DATES.');
    }
    @file_put_contents($rutaCarpeta . '/' . $nombrePdf, $dompdf->output());

    if (ob_get_length()) { ob_end_clean(); }
    // Mostrar inline en el navegador
    $dompdf->stream($nombrePdf, ['Attachment' => 0]);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    http_response_code(500);
    echo "Error generando PDF: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
       . " (línea " . (int)$e->getLine() . ")";
}