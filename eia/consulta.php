<?php
require_once '../eia/librerias.php';

// Instancias de las clases
$basicas    = new Basicas();
$financieras = new Financieras();

// Si vienen datos
if (isset($_GET['value'])) {
    // Desencriptar y sanear
    $dat = base64_decode($_GET['value']);
    $dat = $mysqli->real_escape_string($dat);

    // Buscar contacto por CURP
    $cont = $basicas->BuscarCampos($mysqli, "IdContact", "Usuario", "ClaveCurp", $dat);

    if ($cont >= 1) {
        // Obtener Id de la venta y estatus
        $idvta  = $basicas->BuscarCampos($mysqli, "Id",       "Venta",   "IdContact", $cont);
        $status = $basicas->BuscarCampos($mysqli, "Status",   "Venta",   "Id",        $idvta);

        // Producto y su presentación
        $producto = $basicas->BuscarCampos($mysqli, "Producto", "Venta", "IdContact", $cont);
        switch ($producto) {
            case "Universidad": $prodMost = "Inversión Universitaria"; break;
            case "Retiro":      $prodMost = "Retiro Privado";        break;
            default:            $prodMost = "Gastos Funerarios";     break;
        }

        // Registrar evento de consulta CURP
        $DatEventos = [
            "Contacto"      => $cont,
            "Host"          => $_SERVER['PHP_SELF'],
            "Evento"        => "ConsultaCURP",
            "Usuario"       => "PLATAFORMA",
            "IdVta"         => $idvta,
            "FechaRegistro" => date('Y-m-d H:i:s')
        ];
        $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);

        // Obtener nombre completo
        $Nombre   = $basicas->BuscarCampos($mysqli, "Nombre",  "Venta", "IdContact", $cont);
        $Paterno  = $basicas->BuscarCampos($mysqli, "Paterno", "Venta", "IdContact", $cont);
        $Materno  = $basicas->BuscarCampos($mysqli, "Materno", "Venta", "IdContact", $cont);
        $Nombre   = trim("$Nombre $Paterno $Materno");

        // Mostrar datos del cliente
        echo "
        <div id='FingerPrint' style='display: ;'></div><br>
        <div>
            <p>Cliente:</p><h4><strong>" . htmlspecialchars($Nombre) . "</strong></h4><br>
            <p>CURP:</p><h5>" . htmlspecialchars($dat) . "</h5>
            <p>Producto:</p><h4>" . htmlspecialchars($prodMost) . "</h4>
            <p>Tipo Servicio:</p><h4>" . htmlspecialchars($basicas->BuscarCampos($mysqli, "TipoServicio", "Venta", "IdContact", $cont)) . "</h4><br>
            <p>Estatus:</p><h4>" . htmlspecialchars($status) . "</h4>
        </div>";

        // Si está en COBRANZA o PREVENTA
        if ($status === 'COBRANZA' || $status === 'PREVENTA') {
            $pagosRealizados = $financieras->SumarPagos($mysqli, "Cantidad", "Pagos", "IdVenta", $idvta);
            $pendiente       = $financieras->SaldoCredito($mysqli, $idvta);

            echo "
            <div>
                <p>Pagos Realizados</p><h4>{$pagosRealizados}</h4>
                <p>Pendiente de pagar</p><h4>{$pendiente}</h4>
            </div>";

            if ($status === 'PREVENTA') {
                // Contactar ejecutivo
                $waText = rawurlencode("Buen día, estoy interesado en retomar mi proceso de venta de mi servicio $producto, mi nombre es $Nombre");
                echo "
                <a class='btn btn-primary' style='margin-top:1.5em; width:80%;'
                   target='_blank' rel='noopener'
                   href='https://api.whatsapp.com/send?phone=527121000245&text={$waText}'>
                   Contactar un Ejecutivo
                </a><br><br>";
            } else {
                // Descargar estado de cuenta
                $encIdVta = base64_encode($idvta);
                echo "
                <a href='https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda={$encIdVta}'
                   class='btn btn-secondary' style='margin-top:1.5em; width:80%;'>
                   Descargar estado de cuenta
                </a><br><br>";
            }

        } else {
            // Descargar póliza o ingresar a cuenta
            $encCont = base64_encode($cont);
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
            "Host"          => $_SERVER['PHP_SELF'],
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
