<?php
/********************************************************************************************
 * Qué hace: Página "Estado de Cuenta". Muestra datos de la venta, cliente y movimientos,
 *           y permite enviar por correo o descargar el PDF. Adaptada a PHP 8.2:
 *           - mysqli en modo excepciones
 *           - consultas preparadas
 *           - sanitización de salidas
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, fija zona horaria, carga librerías, endurece cabeceras
// Fecha: 05/11/2025 | Revisado por: JCCM
session_start();
require_once '../eia/librerias.php';
require_once __DIR__ . '/php/mesa_helpers.php';
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header_remove('X-Powered-By');

// =================== Utilidades ===================
// Qué hace: Escape seguro para HTML
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Guardia de sesión ===================
// Qué hace: Valida autenticación
// Fecha: 05/11/2025 | Revisado por: JCCM
if (empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login');
    exit();
}

// =================== Entrada y constantes ===================
// Qué hace: Obtiene parámetro de búsqueda y datos base
// Fecha: 05/11/2025 | Revisado por: JCCM
$busqueda = $_POST['busqueda'] ?? ($_GET['busqueda'] ?? '');
$tel      = '7208177632'; // Teléfono de la empresa

// =================== Validación de conexión ===================
// Qué hace: Asegura que $mysqli existe y es válido
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión.');
}

// =================== Cargar Venta ===================
// Qué hace: Consulta Venta por Id usando statement preparado
// Fecha: 05/11/2025 | Revisado por: JCCM
$venta = null;
if ($busqueda === '' || !ctype_digit((string)$busqueda)) {
    // Si no es numérico, normaliza a 0 para evitar inyección y errores
    $busqueda = '0';
}
$stmt = $mysqli->prepare('SELECT * FROM Venta WHERE Id = ? LIMIT 1');
$idVenta = (int)$busqueda;
$stmt->bind_param('i', $idVenta);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $venta = $res->fetch_assoc() ?: null;
}
$stmt->close();

// =================== Si no hay venta, salida temprana ===================
// Qué hace: Muestra mensaje mínimo si no se encuentra la venta
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!$venta) {
    ?>
    <!DOCTYPE html>
    <html lang="es" dir="ltr">
    <head>
      <meta charset="utf-8">
      <title>Estado de Cuenta</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" crossorigin="anonymous">
    </head>
    <body class="p-3" onload="localize()">
      <div class="alert alert-warning">No se encontró la venta solicitada.</div>
      <a class="btn btn-secondary" href="Mesa_Clientes.php">Regresar</a>
    </body>
    </html>
    <?php
    exit;
}

// =================== Cargar Contacto ===================
// Qué hace: Consulta Contacto con el id asociado a la venta
// Fecha: 05/11/2025 | Revisado por: JCCM
$datos = null;
$idContactoVenta = (int)($venta['IdContact'] ?? 0);
$stmt = $mysqli->prepare('SELECT * FROM Contacto WHERE id = ? LIMIT 1'); // respeta nombre de columna original "id"
$stmt->bind_param('i', $idContactoVenta);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $datos = $res->fetch_assoc() ?: null;
}
$stmt->close();

// =================== Cargar Usuario ===================
// Qué hace: Consulta Usuario por IdContact
// Fecha: 05/11/2025 | Revisado por: JCCM
$persona = null;
$stmt = $mysqli->prepare('SELECT * FROM Usuario WHERE IdContact = ? LIMIT 1');
$stmt->bind_param('i', $idContactoVenta);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $persona = $res->fetch_assoc() ?: null;
}
$stmt->close();

// =================== Cálculos de saldo y tipo de compra ===================
// Qué hace: Determina saldo y leyenda de crédito/contado
// Fecha: 05/11/2025 | Revisado por: JCCM
$saldo = '0.00';
if ($venta['Status'] === "ACTIVO" || $venta['Status'] === "ACTIVACION") {
    $saldo = number_format(0, 2);
} else {
    // Usa clase $financieras del sistema
    $saldo_val = $financieras->SaldoCredito($mysqli, (int)$venta['Id']);
    $saldo = number_format((float)$saldo_val, 2);
}
if ((int)$venta['NumeroPagos'] >= 2) {
    $Credito = "Compra a crédito; " . (int)$venta['NumeroPagos'] . " Meses";
} else {
    $Credito = "Compra de contado";
}

// =================== Token para no duplicar correos ===================
// Qué hace: Genera token y lo guarda en sesión
// Fecha: 05/11/2025 | Revisado por: JCCM
$_SESSION['mail_token'] = bin2hex(random_bytes(16));

// =================== Parámetro nombre para regresar ===================
// Qué hace: Recupera nombre de búsqueda para navegación
// Fecha: 05/11/2025 | Revisado por: JCCM
$name = $_POST['nombre'] ?? ($_GET['name'] ?? "");

// =================== Alertas por GET ===================
// Qué hace: Lanza alert en carga si hay aviso de actualización
// Fecha: 05/11/2025 | Revisado por: JCCM
if (isset($_GET['Vt']) && (int)$_GET['Vt'] === 1) {
    $msg = isset($_GET['Msg']) ? (string)$_GET['Msg'] : '';
    echo "<script>window.addEventListener('load',()=>alert(".json_encode($msg, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."));</script>";
}

// =================== IDs auxiliares para envío ===================
// Qué hace: Normaliza IDs usados en inputs ocultos
// Fecha: 05/11/2025 | Revisado por: JCCM
$personaId  = $persona['Id']  ?? ($persona['id']  ?? '');
$contactoId = $datos['id']    ?? ($datos['Id']    ?? '');
$ventaId    = $venta['Id']    ?? '';
$producto   = $venta['Producto'] ?? '';
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
   <meta charset="utf-8">
   <title>Estado de Cuenta</title>
   <link rel="shortcut icon" href="../assets/images/logokasu.ico">
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" crossorigin="anonymous">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons'>
   <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
</head>
<body onload="localize()">
    <!-- =================== Barra superior ===================
         Qué hace: Navegación y acciones (regresar, enviar correo, descargar PDF)
         Fecha: 05/11/2025 | Revisado por: JCCM -->
    <nav class="navbar navbar-expand-lg text-white justify-content-between" style="background-color:#8D70E3;">
        <a class="navbar-brand">Estado Cuenta</a>
        <div class="form-inline">
            <!-- Retorna a la ventana anterior -->
            <form action="Mesa_Clientes.php" method="post" style="padding-right: 5px;">
                <input type="text" name="nombre" value="<?php echo h($name); ?>" hidden>
                <label for="Regresar" title="Regresar a cliente" class="btn" style="background:#F5B041;color:#F8F9F9;"><i class="material-icons">undo</i></label>
                <input id="Regresar" type="submit" value="Regresar" name="Accion" hidden>
            </form>

            <!-- Enviar Datos para correo del estado de cuenta -->
            <form action="../eia/EnviarCorreo.php" method="post" style="padding-right: 5px;">
                <!-- ===== Bloque de registro de Eventos (GPS + Fingerprint) ===== -->
                <div id="Gps" style="display:none;"></div>
                <div data-fingerprint-slot></div>
                <input type="text" name="nombre" value="<?php echo h($name); ?>" hidden>
                <input type="text" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>" hidden>
                <input type="number" name="IdVenta" value="<?php echo h((string)$ventaId); ?>" hidden>
                <input type="number" name="IdContact" value="<?php echo h((string)$contactoId); ?>" hidden>
                <input type="number" name="IdUsuario" value="<?php echo h((string)$personaId); ?>" hidden>
                <input type="text"   name="Producto" value="<?php echo h($producto); ?>" hidden>
                <!-- ===== Datos del correo ===== -->
                <input type="text" name="IdVenta"   value="<?php echo h((string)$busqueda); ?>" hidden>
                <input type="text" name="FullName"  value="<?php echo h($persona['Nombre'] ?? ''); ?>" hidden>
                <input type="text" name="Email"     value="<?php echo h($datos['Mail'] ?? ''); ?>" hidden>
                <input type="text" name="Asunto"    value="ENVIO ARCHIVO" hidden>
                <input type="text" name="Descripcion" value="Estado de Cuenta" hidden>
                <input type="hidden" name="mail_token" value="<?php echo h($_SESSION['mail_token']); ?>">
                <label for="Enviar" title="Enviar estado de cuenta" class="btn" style="background:#2980B9;color:#F8F9F9;"><i class="material-icons">email</i></label>
                <input id="Enviar" type="submit" value="Enviar" name="EnviarEdoCta" hidden>
            </form>

            <!-- Descargar el estado de cuenta -->
            <a class="btn" style="background:#58D68D;color:#F8F9F9;" href="https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=<?php echo base64_encode((string)$busqueda); ?>">
              <i class="material-icons">download</i>
            </a>
        </div>
    </nav>

    <br>

    <div class="container">
        <div class="card">
            <div class="card-header bg-secondary text-light"><?php echo h($persona['Nombre'] ?? ''); ?></div>
            <div class="card-body">
                <!-- =================== Encabezados de datos =================== -->
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light">Datos de la empresa</div>
                            <div class="card-body">
                                 KASU, Servicios a Futuro S.A. de C.V. <br>
                                 Fideicomiso F/0003 Gastos Funerarios<br>
                                 Atlacomulco, Estado de México, México C.P. 50450<br>
                                 Teléfono: <?php echo h($tel); ?><br>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light">Datos del Cliente</div>
                            <div class="card-body">
                                Nombre: <?php echo h($persona['Nombre'] ?? ''); ?> <br>
                                CURP: <?php echo h($persona['ClaveCurp'] ?? ''); ?><br>
                                Fecha Registro: <?php echo h(substr((string)($venta['FechaRegistro'] ?? ''), 0, 10)); ?><br>
                                Fecha Última Modificación: <?php echo h(substr((string)($persona['FechaRegistro'] ?? ''), 0, 10)); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <!-- =================== Datos de servicio =================== -->
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light"></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        Dirección: <?php
                                          echo isset($datos['calle']) && $datos['calle'] !== ''
                                            ? h($datos['calle'])
                                            : '<span class="text-danger">No disponible</span>';
                                        ?><br>
                                        Teléfono: <?php echo h($datos['Telefono'] ?? ''); ?> <br>
                                        Email: <?php echo h($datos['Mail'] ?? ''); ?> <br>
                                        Producto: <?php echo h($venta['Producto'] ?? ''); ?><br>
                                        N. Activador: <?php echo h($venta['IdFIrma'] ?? ''); ?> <br>
                                        Status: <?php echo h($venta['Status'] ?? ''); ?><br>
                                        <?php echo h($Credito); ?><br>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <br>

                <!-- =================== Historial de transacciones =================== -->
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header bg-secondary text-light">Historial de transacciones</div>
                            <div class="card-body">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Concepto</th>
                                            <th>Saldo</th>
                                            <th>Debe</th>
                                            <th>Haber</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo h(substr((string)$venta['FechaRegistro'], 0, 10)); ?></td>
                                            <td>Compra de servicio <?php echo h($venta['Producto'] ?? ''); ?></td>
                                            <td><?php echo number_format((float)($venta['CostoVenta'] ?? 0), 2); ?></td>
                                            <td> - </td>
                                            <td> - </td>
                                        </tr>
                                        <?php
                                        // Pagos relacionados a la venta
                                        $stmt = $mysqli->prepare('SELECT * FROM Pagos WHERE IdVenta = ? ORDER BY FechaRegistro ASC, Id ASC');
                                        $stmt->bind_param('i', $idVenta);
                                        $stmt->execute();
                                        if ($resultado = $stmt->get_result()) {
                                            while ($pago = $resultado->fetch_assoc()) {
                                                $fec = h(substr((string)$pago['FechaRegistro'], 0, 10));
                                                $sts = h($pago['status'] ?? '');
                                                $prd = h($venta['Producto'] ?? '');
                                                $cant = number_format((float)($pago['Cantidad'] ?? 0), 2);
                                                echo <<<HTML
                                                    <tr>
                                                        <td>{$fec}</td>
                                                        <td>{$sts} de Servicio {$prd}</td>
                                                        <td> - </td>
                                                        <td>{$cant}</td>
                                                        <td>$ - Mxn</td>
                                                    </tr>
HTML;
                                            }
                                        }
                                        $stmt->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- =================== Resumen de saldo =================== -->
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                            <td>Saldo de la cuenta</td>
                                            <td><?php echo h($saldo); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </div>

</body>
<!-- =================== JS ===================
     Qué hace: Dependencias de UI y scripts de localización/huella
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type="text/javascript" src="Javascript/localize.js"></script>
<script src="Javascript/fingerprint-core-y-utils.js"></script>
<script src="Javascript/finger.js" defer></script>
</html>
