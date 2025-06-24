<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Clase financiera
try {
    ob_start(); // Iniciar buffer para el PDF
    require_once '../../eia/librerias.php';
    require_once 'dompdfMaster/dompdf_config.inc.php';//Bloqeuado para impresion
		require_once 'dompdfMaster/include/autoload.inc.php'; //se carga el autoload para poder tener disponible la case DOMPDF

    $financieras = new Financieras();

    date_default_timezone_set('America/Mexico_City');
    $fecha = date("Y-m-d-H-i-s");

    // Validar parámetro GET
    if (!isset($_GET['busqueda'])) {
        throw new Exception("No se proporcionó el parámetro 'busqueda'.");
    }
    $busqueda = base64_decode($_GET['busqueda']);

    // Consultar venta
    $Ct3a = mysqli_query($mysqli, "SELECT * FROM Venta WHERE Id = '" . $mysqli->real_escape_string($busqueda) . "'");
    if (!$Ct3a) throw new Exception("Error al consultar Venta.");

    if ($venta = mysqli_fetch_assoc($Ct3a)) {
        // Consultar contacto
        $Ct1a = mysqli_query($mysqli, "SELECT * FROM Contacto WHERE id = '" . $mysqli->real_escape_string($venta['IdContact']) . "'");
        if (!$Ct1a) throw new Exception("Error al consultar Contacto.");

        if ($datos = mysqli_fetch_assoc($Ct1a)) {
            // Consultar usuario
            $Ct2a = mysqli_query($mysqli, "SELECT * FROM Usuario WHERE IdContact = '" . $mysqli->real_escape_string($venta['IdContact']) . "'");
            if (!$Ct2a) throw new Exception("Error al consultar Usuario.");

            if ($persona = mysqli_fetch_assoc($Ct2a)) {
                // Variables seguras
                $nombre   = isset($persona['Nombre']) ? $persona['Nombre'] : 'Desconocido';
                $numPagos = isset($venta['NumeroPagos']) ? intval($venta['NumeroPagos']) : 0;
                $status   = isset($venta['Status']) ? $venta['Status'] : '';
                $idVenta  = isset($venta['Id']) ? $venta['Id'] : '';

                // Calcular saldo y tipo de compra
                if ($status == "ACTIVO" || $status == "ACTIVACION") {
                    $saldo = number_format(0, 2);
                } else {
                    $saldo = number_format($financieras->SaldoCredito($mysqli, $idVenta), 2);
                }
                $Credito = ($numPagos >= 2) ? "Compra a crédito; $numPagos Meses" : "Compra de contado";

                // Generar HTML
                ob_start();
                include 'html/Estado_Cuenta.php';
                $html = ob_get_clean();

                // Generar PDF
                $datospdf = new DOMPDF();
                $datospdf->set_option('enable_html5_parser', TRUE);
                $datospdf->set_paper("A4", "portrait");
                $datospdf->load_html($html);
                $datospdf->render();
                $output = $datospdf->output();

                // Guardar archivo PDF
                $NomFichas = str_replace(' ', '', $nombre);
                $nombrePdf = "EDOCTA_" . $NomFichas . ".pdf";
                @file_put_contents("DATES/" . $nombrePdf, $output);

                // Descargar PDF
                $datospdf->stream($nombrePdf);
                exit;
            } else {
                throw new Exception("No se encontró el usuario.");
            }
        } else {
            throw new Exception("No se encontró el contacto.");
        }
    } else {
        throw new Exception("No se encontró la venta.");
    }
} catch (Throwable $e) {
    echo "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";
}
?>