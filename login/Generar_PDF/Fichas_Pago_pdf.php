<?php
try {
    // Evita salida antes de enviar el PDF
    ob_start();

    require_once '../../eia/librerias.php';                      // Debe exponer $mysqli y tus clases
    require_once 'dompdfMaster/dompdf_config.inc.php';          // DOMPDF (legacy)
    require_once 'dompdfMaster/include/autoload.inc.php';       // Autoload DOMPDF

    date_default_timezone_set('America/Mexico_City');

    // ------------------------------------------------------------------
    // Resolver parámetros de entrada
    //  - GET busqueda  : base64(IdContact)  (mismo flujo que Póliza)
    //  - POST IdVenta  : Id de Venta directo
    //  - GET  Cte      : base64(IdVenta) (compatibilidad)
    //  - POST data     : fecha de promesa (opcional)
    // ------------------------------------------------------------------
    $idVenta   = null;
    $idContact = null;
    $fec       = $_POST['data'] ?? null;

    if (isset($_GET['busqueda'])) {
        $idContact = (int) $mysqli->real_escape_string(base64_decode($_GET['busqueda']));
    } elseif (isset($_POST['IdVenta'])) {
        $idVenta = (int) $mysqli->real_escape_string($_POST['IdVenta']);
    } elseif (isset($_GET['Cte'])) {
        $idVenta = (int) $mysqli->real_escape_string(base64_decode($_GET['Cte']));
    }

    if (!$idContact && !$idVenta) {
        throw new Exception('Parámetros inválidos.');
    }

    // ------------------------------------------------------------------
    // Cargar Venta (y derivar IdContact si entró por IdVenta)
    // ------------------------------------------------------------------
    if ($idVenta) {
        $stmtV = $mysqli->prepare("SELECT * FROM Venta WHERE Id = ? LIMIT 1");
        $stmtV->bind_param('i', $idVenta);
    } else {
        $stmtV = $mysqli->prepare("SELECT * FROM Venta WHERE IdContact = ? LIMIT 1");
        $stmtV->bind_param('i', $idContact);
    }
    $stmtV->execute();
    $resV = $stmtV->get_result();
    if (!($venta = $resV->fetch_assoc())) {
        throw new Exception('Venta no encontrada.');
    }
    $stmtV->close();

    // Mantén el nombre esperado por el template
    $row = $venta;

    // Si se entró por contact, fija IdContact desde la venta
    $idContact = (int) $venta['IdContact'];

    // ------------------------------------------------------------------
    // (Opcional) cargar más datos si tu template los requiere
    // ------------------------------------------------------------------
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

    // ------------------------------------------------------------------
    // Instancias que tu template usa directamente
    // ------------------------------------------------------------------
    $basicas      = new Basicas();
    $financieras  = new Financieras();

    // ------------------------------------------------------------------
    // Renderizar HTML del template (usa $row, $fec, $basicas, $financieras)
    // ------------------------------------------------------------------
    ob_start();
    require __DIR__ . '/html/Fichas_Pago.php';
    $html = ob_get_clean();

    // ------------------------------------------------------------------
    // Generar PDF con DOMPDF (legacy)
    // ------------------------------------------------------------------
    $dompdf = new DOMPDF();
    if (method_exists($dompdf, 'set_option')) {
        $dompdf->set_option('enable_html5_parser', true);
        $dompdf->set_option('enable_remote', true);
    }
    $dompdf->set_paper('A4', 'portrait');
    $dompdf->load_html($html);
    $dompdf->render();

    // Guardar y enviar al navegador
    $NomFichas = preg_replace('/\s+/', '', ($row['Nombre'] ?? 'Cliente'));
    $nombrePdf = "FICHAS_{$NomFichas}.pdf";

    $rutaCarpeta = __DIR__ . '/DATES';
    if (!is_dir($rutaCarpeta)) { @mkdir($rutaCarpeta, 0775, true); }
    @file_put_contents($rutaCarpeta . '/' . $nombrePdf, $dompdf->output());

    // Limpia cualquier salida previa antes de mandar el PDF
    if (ob_get_length()) { ob_end_clean(); }
    $dompdf->stream($nombrePdf); // Cambia a ['Attachment' => 0] si quieres inline
    exit;

} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    echo "Error generando PDF: " . $e->getMessage() . " (línea " . $e->getLine() . ")";
}
