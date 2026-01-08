<?php
/**
 * Consulta de cliente por CURP y muestra de acciones según estatus.
 * Compatibilizado con PHP 8.2. Conserva la lógica existente.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 * Archivo: consulta.php
 */

require_once '../eia/librerias.php';

// Helper de escape HTML
if (!function_exists('e')) {
    function e($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// Si vienen datos
if (isset($_GET['value'])) {
    // Desencriptar y sanear (base64 estricto)
    $raw = base64_decode((string)$_GET['value'], true);
    if ($raw === false) {
        http_response_code(400);
        exit('Parámetro inválido.');
    }
    $dat = $mysqli->real_escape_string($raw);

    // Buscar contacto por CURP
    $cont = (int)$basicas->BuscarCampos($mysqli, "IdContact", "Usuario", "ClaveCurp", $dat);

    if ($cont >= 1) {
        // Obtener Id de la venta y estatus
        $idvta  = (int)$basicas->BuscarCampos($mysqli, "Id",     "Venta", "IdContact", $cont);
        $status = (string)$basicas->BuscarCampos($mysqli, "Status", "Venta", "Id", $idvta);

        // Producto y su presentación
        $producto = (string)$basicas->BuscarCampos($mysqli, "Producto", "Venta", "IdContact", $cont);
        switch ($producto) {
            case "Universidad": $prodMost = "Inversión Universitaria"; break;
            case "Retiro":      $prodMost = "Retiro Privado";          break;
            default:            $prodMost = "Gastos Funerarios";       break;
        }

        // Registrar evento de consulta CURP
        $DatEventos = [
            "Contacto"      => $cont,
            "Host"          => $_SERVER['PHP_SELF'] ?? '',
            "Evento"        => "ConsultaCURP",
            "Usuario"       => "PLATAFORMA",
            "IdVta"         => $idvta,
            "FechaRegistro" => date('Y-m-d H:i:s')
        ];
        $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);

        // Obtener nombre completo
        $Nombre   = (string)$basicas->BuscarCampos($mysqli, "Nombre",  "Venta", "IdContact", $cont);
        $Paterno  = (string)$basicas->BuscarCampos($mysqli, "Paterno", "Venta", "IdContact", $cont);
        $Materno  = (string)$basicas->BuscarCampos($mysqli, "Materno", "Venta", "IdContact", $cont);
        $NombreC  = trim("$Nombre $Paterno $Materno");

        // Mostrar datos del cliente
        echo "
        <div id='FingerPrint' style='display: ;'></div><br>
        <div>
            <p>Cliente:</p><h4><strong>" . e($NombreC) . "</strong></h4><br>
            <p>CURP:</p><h5>" . e($dat) . "</h5>
            <p>Producto:</p><h4>" . e($prodMost) . "</h4>
            <p>Tipo Servicio:</p><h4>" . e((string)$basicas->BuscarCampos($mysqli, 'TipoServicio', 'Venta', 'IdContact', $cont)) . "</h4><br>
            <p>Estatus:</p><h4>" . e($status) . "</h4>
        </div>";

        // Si está en COBRANZA o PREVENTA
        if ($status === 'COBRANZA' || $status === 'PREVENTA') {
            $pagosRealizados = (float)$financieras->SumarPagos($mysqli, "Cantidad", "Pagos", "IdVenta", $idvta);
            $pendiente       = (float)$financieras->SaldoCredito($mysqli, $idvta);

            echo "
            <div>
                <p>Pagos Realizados</p><h4>" . e((string)$pagosRealizados) . "</h4>
                <p>Pendiente de pagar</p><h4>" . e((string)$pendiente) . "</h4>
            </div>";

            if ($status === 'PREVENTA') {
                // Contactar ejecutivo
                $waText = rawurlencode("Buen día, estoy interesado en retomar mi proceso de venta de mi servicio $producto, mi nombre es $NombreC");
                echo "
                <a class='btn btn-primary' style='margin-top:1.5em; width:80%;'
                   target='_blank' rel='noopener'
                   href='https://api.whatsapp.com/send?phone=527121000245&text={$waText}'>
                   Contactar un Ejecutivo
                </a><br><br>";
            } else {
                // Descargar estado de cuenta
                $encIdVta = base64_encode((string)$idvta);
                echo "
                <a href='https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda={$encIdVta}'
                   class='btn btn-secondary' style='margin-top:1.5em; width:80%;'>
                   Descargar estado de cuenta
                </a><br><br>";
            }

        } else {
            // Descargar póliza o ingresar a cuenta
            $encCont = base64_encode((string)$cont);
            echo "
            <div style='margin-top:1em;'>
                <a href='https://kasu.com.mx/login/Generar_PDF/Poliza_pdf.php?busqueda={$encCont}'
                   class='btn btn-secondary btn-sm' download>
                   Descargar mi Póliza
                </a>
            </div>
            <div style='margin-top:1em;'>
                <a href='https://kasu.com.mx/ActualizacionDatos/index.php?value={$encCont}'
                   class='btn btn-secondary btn-sm' style='background:#ec7c26;'>
                   Ingresar a mi Cuenta
                </a>
            </div><br>";
        }

    } else {
        // Registrar evento de error en consulta
        $DatEventos = [
            "Contacto"      => $dat,
            "Host"          => $_SERVER['PHP_SELF'] ?? '',
            "Evento"        => "ErrorConsulta",
            "Usuario"       => "PLATAFORMA",
            "FechaRegistro" => date('Y-m-d H:i:s')
        ];
        $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);

        // Mostrar mensaje de CURP no registrado
        echo "
        <div style='padding:2rem; height:250px;'>
            <h6>No se tiene registro de esta CURP, verifique si es correcta.</h6><br>
            <p>Si no se ha registrado o le interesa el servicio le invitamos a registrarse en este<br>
            <a href='https://kasu.com.mx/registro.php' target='_blank' style='color:#911F66; font-size:1.5rem;'>link</a></p>
        </div>";
    }
}
?>