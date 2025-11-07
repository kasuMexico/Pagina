<?php
/**
 * Fichas_Pago → Generación de PDF
 * Qué hace:
 *  - Acepta una de tres entradas:
 *      • GET ?busqueda  = base64(IdContact)
 *      • POST IdVenta   = Id directo de Venta
 *      • GET  ?Cte      = base64(IdVenta)  (compatibilidad)
 *  - Carga Venta, Usuario y Contacto con consultas preparadas.
 *  - Inyecta $row, $fec, $basicas, $financieras en el template html/Fichas_Pago.php.
 *  - Renderiza PDF con DOMPDF (legacy si existe clase DOMPDF, si no usa namespaced Dompdf).
 *  - Guarda el PDF en /DATES y lo envía al navegador.
 *
 * Notas de seguridad:
 *  - Sanitiza entradas y usa mysqli prepare.
 *  - Evita salida previa con ob_* antes del stream PDF.
 *
 * Dependencias:
 *  - ../../eia/librerias.php  (expone $mysqli y clases auxiliares)
 *  - dompdfMaster/…           (DOMPDF legacy y autoload)
 *  - html/Fichas_Pago.php     (template HTML)
 *
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

try {
    /* ===================== BLOQUE: Control de salida ===================== */
    // Evita salida antes de enviar el PDF
    if (!ob_get_level()) { ob_start(); }

    /* ===================== BLOQUE: Cargas base ===================== */
    require_once '../../eia/librerias.php';                      // $mysqli y tus clases
    require_once 'dompdfMaster/dompdf_config.inc.php';          // DOMPDF (legacy)
    require_once 'dompdfMaster/include/autoload.inc.php';       // Autoload DOMPDF

    // Si usas la versión namespaced, estos "use" no rompen con legacy
    if (class_exists('\\Dompdf\\Dompdf')) {
        /** @noinspection PhpUnusedAliasInspection */
        use Dompdf\Dompdf;
        /** @noinspection PhpUnusedAliasInspection */
        use Dompdf\Options;
    }

    date_default_timezone_set('America/Mexico_City');

    /* ===================== BLOQUE: Resolver parámetros de entrada ===================== */
    //  - GET busqueda  : base64(IdContact)
    //  - POST IdVenta  : Id de Venta directo
    //  - GET  Cte      : base64(IdVenta) (compatibilidad)
    //  - POST data     : fecha de promesa (opcional)
    $idVenta   = null;
    $idContact = null;
    $fec       = $_POST['data'] ?? null;

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

    /* ===================== BLOQUE: Cargar Venta ===================== */
    if ($idVenta) {
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

    // Mantén el nombre esperado por el template
    $row       = $venta;
    $idContact = (int)$venta['IdContact'];

    /* ===================== BLOQUE: Cargar Usuario y Contacto ===================== */
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

    /* ===================== BLOQUE: Instancias auxiliares para el template ===================== */
    // Si librerias.php ya los instancia, estos "new" no estorban; mantenemos para acoplamiento débil
    $basicas     = new Basicas();
    $financieras = new Financieras();

    /* ===================== BLOQUE: Render HTML del template ===================== */
    // Variables que el template espera disponibles: $row, $fec, $basicas, $financieras, $usuario, $contacto
    ob_start();
    require __DIR__ . '/html/Fichas_Pago.php';
    $html = ob_get_clean();

    if ($html === '' || $html === false) {
        throw new Exception('El template Fichas_Pago.php no produjo salida.');
    }

    /* ===================== BLOQUE: Construcción de DOMPDF ===================== */
    // Soporta legacy DOMPDF y también la versión namespaced moderna.
    if (class_exists('DOMPDF')) {
        // Legacy
        $dompdf = new DOMPDF();
        if (method_exists($dompdf, 'set_option')) {
            $dompdf->set_option('enable_html5_parser', true);
            $dompdf->set_option('enable_remote', true);
        }
    } else {
        // Namespaced
        $opts = new Options();
        $opts->set('enable_html5_parser', true);
        $opts->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($opts);
    }

    $dompdf->set_paper('A4', 'portrait');
    $dompdf->load_html($html);
    $dompdf->render();

    /* ===================== BLOQUE: Persistencia y entrega ===================== */
    $NomFichas = preg_replace('/\s+/', '', (string)($row['Nombre'] ?? 'Cliente'));
    $nombrePdf = "FICHAS_{$NomFichas}.pdf";

    $rutaCarpeta = __DIR__ . '/DATES';
    if (!is_dir($rutaCarpeta)) { @mkdir($rutaCarpeta, 0775, true); }
    @file_put_contents($rutaCarpeta . '/' . $nombrePdf, $dompdf->output());

    // Limpia cualquier salida previa antes de mandar el PDF
    if (ob_get_length()) { ob_end_clean(); }
    // Para inline en navegador: stream($nombrePdf, ['Attachment' => 0])
    $dompdf->stream($nombrePdf);
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    http_response_code(500);
    echo "Error generando PDF: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
       . " (línea " . (int)$e->getLine() . ")";
}
