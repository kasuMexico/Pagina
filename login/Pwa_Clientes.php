<?php
session_start();
require_once '../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

// Redirección si no hay sesión
if (empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login');
    exit();
}

/* ================== Defaults y utilidades ================== */
$Niv     = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
$IdVen   = (int)$basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
$Ventana = null;   // Ventana a cargar (Ventana1..4)
$Lanzar  = null;   // '#Ventana' si hay que abrir modal
$BtnPago = null;
$saldoNum = null;
$nombre   = null;
$selCte   = $_POST['SelCte'] ?? '';

// Variables posibles para includes
$Reg = $Pago1 = $Pago = $Saldo = $PagoPend = $Status = null;

/* ================== Selección de cliente (abre modal) ================== */
if (!empty($selCte)) {
    $idVentaPost = (int)($_POST['IdVenta'] ?? 0);
    if ($idVentaPost > 0) {
        $ventaSql = "SELECT * FROM Venta WHERE Id = {$idVentaPost} LIMIT 1";
        if ($res = $mysqli->query($ventaSql)) {
            if ($Reg = $res->fetch_assoc()) {

                // Status de referencia: POST -> venta
                $statusVta = $_POST['StatusVta'] ?? ($Reg['Status'] ?? '');

                if (!in_array($statusVta, ['ACTIVO', 'ACTIVACION'], true)) {
                    // Cálculos financieros
                    $Pago1    = $financieras->Pago($mysqli, (int)$Reg['Id']);
                    $Pago     = number_format($Pago1, 2);
                    $Saldo    = '$' . number_format($financieras->SaldoCredito($mysqli, (int)$Reg['Id']), 2);
                    $PagoPend = $financieras->PagosPend($mysqli, (int)$Reg['Id']);

                    // Estado de mora/corriente
                    if (method_exists($financieras, 'estado_mora_corriente')) {
                        $StatVtas = $financieras->estado_mora_corriente((int)$Reg['Id']);
                        $Status   = (!empty($StatVtas['estado']) && $StatVtas['estado'] === 'AL CORRIENTE') ? 'Pago' : 'Mora';
                    } else {
                        $Status = 'Pago';
                    }
                }

                // Configurar modal
                $Ventana = 'Ventana1';
                $Lanzar  = '#Ventana';
            }
        }
    }
}

/* ================== Lanzadores directos de ventana ================== */
if ($selCte == "Enviar") {
    $Ventana = "Ventana4";
    $Lanzar  = "#Ventana";
} elseif ($selCte == "Agregar Pago") {
    $Ventana = "Ventana2";
    $Lanzar  = "#Ventana";
}

// Alerts de mensajes recibidos
if(isset($_GET['Msg'])){
    echo "<script>alert('".htmlspecialchars($_GET['Msg'], ENT_QUOTES)."');</script>";
}

// Registro de metodo para pagos / mesa de control /
$Metodo = "Vtas";
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#F2F2F2">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <title>Clientes</title>

    <!-- Manifest / iOS -->
    <link rel="manifest" href="/login/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo $VerCache; ?>">

    <!-- JS externos -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>

<body onload="localize()">

    <!-- Top bar fija -->
    <div class="topbar">
        <div class="d-flex align-items-center w-100">
        <h4 class="title">Cartera de Clientes</h4>

        <!-- botón crear prospecto -->
        <form class="BtnSocial m-0 ml-auto" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
                <label for="btnCrearCte" title="Crear nuevo prospecto" class="btn" style="background:#58D68D;color:#F8F9F9;">
                    <i class="material-icons">person_add</i>
                </label>
                <input id="btnCrearCte" type="submit" name="SelCte" hidden>
        </form>
        </div>
    </div>

    <!-- Menú inferior fijo -->
    <section id="Menu">
        <?php require_once 'html/Menuprinc.php'; ?>
    </section>

    <!-- Modal contenedor -->
    <div class="modal fade" id="Ventana" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <?php
                if ($Ventana === "Ventana1") {
                    require 'html/EmEdoCte.php';
                } elseif ($Ventana === "Ventana2") {
                    require 'html/Emergente_Registrar_Pago.php';
                } elseif ($Ventana === "Ventana4") {
                    require 'html/NvoCliente.php';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Contenido que sí hace scroll entre top y bottom -->
    <main class="page-content">
        <section class="container" style="width:99%;">
            <div class="form-group">
                <div class="table-responsive">
                    <?php
                    // Listado de ventas según nivel
                    if ($Niv >= 5) {
                        $Ventas = "SELECT * FROM Venta WHERE Usuario = '" . $mysqli->real_escape_string($_SESSION["Vendedor"]) . "'";
                        if ($resultado = $mysqli->query($Ventas)) {
                            while ($fila = $resultado->fetch_assoc()) {
                                ?>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                    <input type="number" name="IdVenta"   value="<?php echo (int)$fila['Id']; ?>" hidden>
                                    <input type="text"   name="StatusVta" value="<?php echo htmlspecialchars($fila['Status']); ?>" hidden>
                                    <span class="new badge blue <?php echo htmlspecialchars($fila['Status']); ?>" style="position:relative;padding:0;width:100px;top:20px;">
                                        <?php echo htmlspecialchars($fila['Status']); ?>
                                    </span>
                                    <input type="submit" name="SelCte" class="<?php echo htmlspecialchars($fila['Status']); ?>"
                                           value="<?php echo htmlspecialchars($fila['Nombre']); ?>">
                                </form>
                                <?php
                            }
                        }
                    } elseif ($Niv <= 4 && $Niv >= 2) {
                        $IdSuc  = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                        $NomSuc = $basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $IdSuc);
                        $sqal   = "SELECT * FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}' AND Sucursal={$IdSuc}";
                        if ($r = $mysqli->query($sqal)) {
                            foreach ($r as $emp) {
                                $Ventas = "SELECT * FROM Venta WHERE Usuario = '" . $mysqli->real_escape_string($emp["IdUsuario"]) . "'";
                                if ($resultado = $mysqli->query($Ventas)) {
                                    while ($fila = $resultado->fetch_assoc()) {
                                        ?>
                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                            <input type="number" name="IdVenta"    value="<?php echo (int)$fila['Id']; ?>" hidden>
                                            <input type="text"   name="StatusVta"  value="<?php echo htmlspecialchars($fila['Status']); ?>" hidden>
                                            <input type="text"   name="IdVendedor" value="<?php echo htmlspecialchars($emp["IdUsuario"]); ?>" hidden>
                                            <span class="new badge blue <?php echo htmlspecialchars($fila['Status']); ?>" style="position:relative;padding:0;width:100px;top:20px;">
                                                <?php echo htmlspecialchars($fila['Status']); ?>
                                            </span>
                                            <input type="submit" name="SelCte" class="<?php echo htmlspecialchars($fila['Status']); ?>"
                                                   value="<?php echo htmlspecialchars($fila['Nombre'] . ' - ' . $emp["IdUsuario"] . ' - ' . $NomSuc); ?>">
                                        </form>
                                        <?php
                                    }
                                }
                            }
                        }
                    } elseif ($Niv == 1) {
                        $IdSuc  = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                        $NomSuc = $basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $IdSuc);
                        $sqal   = "SELECT * FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}'";
                        if ($r = $mysqli->query($sqal)) {
                            foreach ($r as $emp) {
                                $Ventas = "SELECT * FROM Venta WHERE Usuario = '" . $mysqli->real_escape_string($emp["IdUsuario"]) . "'";
                                if ($resultado = $mysqli->query($Ventas)) {
                                    while ($fila = $resultado->fetch_assoc()) {
                                        ?>
                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                            <input type="number" name="IdVenta"    value="<?php echo (int)$fila['Id']; ?>" hidden>
                                            <input type="text"   name="StatusVta"  value="<?php echo htmlspecialchars($fila['Status']); ?>" hidden>
                                            <input type="text"   name="IdVendedor" value="<?php echo htmlspecialchars($emp["IdUsuario"]); ?>" hidden>
                                            <span class="new badge blue <?php echo htmlspecialchars($fila['Status']); ?>" style="position:relative;padding:0;width:100px;top:20px;">
                                                <?php echo htmlspecialchars($fila['Status']); ?>
                                            </span>
                                            <input type="submit" name="SelCte" class="<?php echo htmlspecialchars($fila['Status']); ?>"
                                                   value="<?php echo htmlspecialchars($fila['Nombre'] . ' - ' . $emp["IdUsuario"] . ' - ' . $NomSuc); ?>">
                                        </form>
                                        <?php
                                    }
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            <br><br><br><br>
        </section>
    </main>

    <!-- JS (una sola versión) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="Javascript/fingerprint-core-y-utils.js"></script>
    <script src="Javascript/finger.js"></script>
    <script src="Javascript/Seleccionar.js"></script>
    <script src="Javascript/localize.js"></script>

    <!-- Abrir modal solo si corresponde -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (!empty($Lanzar)) : ?>
            $('<?php echo $Lanzar; ?>').modal('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>