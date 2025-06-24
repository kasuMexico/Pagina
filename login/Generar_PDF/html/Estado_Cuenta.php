<?php
// Asegura que las variables estén definidas
$tel = isset($tel) ? $tel : '';
$saldo = isset($saldo) ? $saldo : '0.00';

// Usar number_format para montos
$costoVenta = isset($venta['CostoVenta']) ? number_format($venta['CostoVenta'], 2) : '0.00';

// Comienza el buffer para la impresión del HTML
echo '
<html lang="es">
<head>
    <title>Estado de Cuenta</title>
    <link rel="stylesheet" href="css/EstadoCta.css">
</head>
<body>
    <table class="t-h">
        <tr>
            <td>
                <h1 class="ha-text"><strong>KASU, Servicios a Futuro S.A de C.V.</strong></h1>
                <p class="hb-text">Julian Gonzalez 10 2do piso, Fermin J. Villaloz</p>
                <p class="hb-text">Atlacomulco, Estado de Mexico, Mexico C.P. 50450</p>
                <p class="hb-text"> Teléfono: '.htmlspecialchars($tel).'</p>
            </td>
        </tr>
    </table>
    <img src="https://kasu.com.mx/assets/poliza/img2/transp.jpg" class="header">
    <div class="container">
        <div class="cardheader">Datos del Cliente</div>
        <div class="cardbody">
            Nombre : '.htmlspecialchars($persona['Nombre']).'<br>
            CURP : '.htmlspecialchars($persona['ClaveCurp']).'<br>
            Fecha Registro : '.htmlspecialchars(substr($venta['FechaRegistro'], 0, 10)).'<br>
            Fecha Última Modificación : '.htmlspecialchars(substr($persona['FechaRegistro'], 0, 10)).'<br>
        </div>
        <div class="cardheader"></div>
        <div class="cardbody">
            Dirección : ' . (isset($datos['Direccion']) && !empty($datos['Direccion']) ? htmlspecialchars($datos['Direccion']) : "<span class='text-danger'>No disponible</span>") . '<br>
            Teléfono : '.htmlspecialchars($datos['Telefono']).'<br>
            Email : '.htmlspecialchars($datos['Mail']).'<br>
            Producto : '.htmlspecialchars($venta['Producto']).'<br>
            N. Activador : '.htmlspecialchars($venta['IdFIrma']).'<br>
            Status : '.htmlspecialchars($venta['Status']).'<br>
            '.htmlspecialchars($Credito).'<br>
        </div>
        <div class="card">
            <div class="cardheader">Historial de transacciones</div>
            <div class="cardbody">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Saldo</th>
                            <th>Pagos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>'.htmlspecialchars(substr($venta['FechaRegistro'], 0, 10)).'</td>
                            <td>Compra de servicio '.htmlspecialchars($datos['Producto']).'</td>
                            <td>'.$costoVenta.'</td>
                            <td> - </td>
                        </tr>';
                        // Realiza consulta
                        $Ct4 = "SELECT * FROM Pagos WHERE IdVenta = '".htmlspecialchars($busqueda)."'";
                        if ($resultado = $mysqli->query($Ct4)) {
                            while ($pago = $resultado->fetch_assoc()) {
                                echo '
                                <tr>
                                    <td>'.htmlspecialchars(substr($pago['FechaRegistro'], 0, 10)).'</td>
                                    <td>'.htmlspecialchars($pago['status']).' de Servicio '.htmlspecialchars($venta['Producto']).'</td>
                                    <td> - </td>
                                    <td>'.number_format($pago['Cantidad'], 2).'</td>
                                </tr>
                                ';
                            }
                        }
                        echo '
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Saldo de la cuenta</td>
                            <td>'.htmlspecialchars($saldo).'</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <img src="https://kasu.com.mx/assets/poliza/img2/LINE7.jpg" class="h-line">
    <h2 class="url">CONSULTA NUESTRO AVISO DE PRIVACIDAD EN : WWW.KASU.COM.MX/AVISOPRIVACIDAD.HTML</h2>
    <img src="https://kasu.com.mx/assets/poliza/img2/img.jpg" class="fin2">
</body>
</html>';
?>
