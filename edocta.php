<?php
/*******************************************************************************************
 * Estado de cuenta del cliente (detalle de venta y pagos)
 * 03/11/2025 – Revisado por JCCM
 *
 * Objetivo:
 * - Cargar la venta por Id y mostrar: Nombre, Producto, Status y Subtotal.
 * - Listar los pagos asociados a la venta.
 * - Calcular el total pagado con SUM(Cantidad).
 *
 * Cambios para PHP 8.2:
 * - Saneado de $_GET['Id'] y uso de sentencias preparadas (mysqli_stmt) para evitar inyección.
 * - Validaciones de resultados para evitar warnings por índices no definidos.
 * - Escapado de salida con htmlspecialchars.
 * - Eliminación de short open tags en echos.
 *******************************************************************************************/

// Requerimos el archivo de librerías *JCCM
require_once __DIR__ . '/eia/librerias.php';

// Validar conexión
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión.');
}

// === Entrada: Id de la venta — saneo y normalización ===
// 03/11/2025 – Revisado por JCCM
$ventaId = $_GET['Id'] ?? '';
// Aceptar solo dígitos. Si la clave no es válida, forzar 0 para no devolver resultados
if (!preg_match('/^\d+$/', (string)$ventaId)) {
    $ventaId = '0';
}

// === Consulta: Venta por Id ===
// 03/11/2025 – Revisado por JCCM
$Reg = null;
if ($stmt = $mysqli->prepare('SELECT * FROM Venta WHERE Id = ?')) {
    $stmt->bind_param('i', $ventaId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $Reg = $res->fetch_assoc(); // Se usa asociativo porque el template usa nombres de campo
    }
    $stmt->close();
}

// Si no existe registro, mostrar mensaje simple y terminar
if (!$Reg) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <title>Estado de cuenta | No encontrado</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    </head>
    <body class="p-4">
        <h2>Estado de cuenta</h2>
        <div class="alert alert-warning" role="alert">
            La venta solicitada no existe o el identificador es inválido.
        </div>
        <a class="btn btn-secondary" href="index.php">Regresar</a>
    </body>
    </html>
    <?php
    exit;
}

// === Precalcular total de pagos con SUM ===
// 03/11/2025 – Revisado por JCCM
$totalPagos = '0.00';
if ($stmtTot = $mysqli->prepare('SELECT COALESCE(SUM(Cantidad),0) AS total FROM Pagos WHERE IdVenta = ?')) {
    $stmtTot->bind_param('i', $ventaId);
    $stmtTot->execute();
    $resTot = $stmtTot->get_result();
    if ($resTot && ($rowTot = $resTot->fetch_assoc())) {
        $totalPagos = (string)$rowTot['total'];
    }
    $stmtTot->close();
}

// Helper de salida segura
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Estado de cuenta</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="login/assets/css/styles.min.css">
    <style>
        /* Bloque: estilos mínimos de página — 03/11/2025 – Revisado por JCCM */
        .cabecera { padding: 8px 16px; }
        .CabeceraCuenta { display: flex; gap: 12px; align-items: center; }
        .CabeceraCuenta .caja { padding: 6px 10px; background:#f5f5f5; border-radius:8px; }
        .historial { padding: 0 16px; }
        .pie { padding: 0 16px 24px; }
        table.table td { vertical-align: middle; }
    </style>
</head>
<body>
    <!--Inicio de menu principal fijo-->
    <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- ***** Logo Start ***** -->
                        <a href="index.php" class="logo">
                            <img src="assets/images/kasu_logo.jpeg" alt="Logo Kasu"/>
                        </a>
                        <!-- ***** Logo End ***** -->
                        <!-- ***** Menu Start ***** -->
                        <ul class="nav">
                            <li><a href="index.php">KASU</a></li>
                        </ul>
                        <a class='menu-trigger'>
                            <span>Menú</span>
                        </a>
                        <!-- ***** Menu End ***** -->
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!--Final de menu principal fijo-->

    <br><br><br><br><br>
    <h2 class="px-3">Estado de cuenta</h2>

    <div class="cabecera">
        <h2><?php echo e($Reg['Nombre'] ?? ''); ?></h2>
        <div class="CabeceraCuenta">
            <div class="caja">
                <h2 class="m-0"><?php echo e($Reg['Producto'] ?? ''); ?></h2>
            </div>
            <h2 class="m-0"><?php echo e($Reg['Status'] ?? ''); ?></h2>
        </div>
    </div>

    <div class="historial">
        <table class="table">
            <tbody>
                <tr>
                    <td><strong>Fecha</strong></td>
                    <td><strong>Pagos</strong></td>
                </tr>
            </tbody>
            <tbody>
            <?php
            /*****************************************************************
             * Listado de pagos de la venta
             * Se mantiene la lógica original con índices numéricos del fetch
             * (se preserva compatibilidad con el orden de columnas actual).
             * 03/11/2025 – Revisado por JCCM
             *****************************************************************/
            if ($stmtPag = $mysqli->prepare('SELECT * FROM Pagos WHERE IdVenta = ?')) {
                $stmtPag->bind_param('i', $ventaId);
                $stmtPag->execute();
                $resPag = $stmtPag->get_result();
                if ($resPag) {
                    while ($Pago = $resPag->fetch_row()) {
                        // Original: $Pago[6], $Pago[7], $Pago[8] como componentes de fecha; $Pago[5] como cantidad
                        $d = isset($Pago[6]) ? e($Pago[6]) : '';
                        $m = isset($Pago[7]) ? e($Pago[7]) : '';
                        $y = isset($Pago[8]) ? e($Pago[8]) : '';
                        $amt = isset($Pago[5]) ? e($Pago[5]) : '0.00';
                        printf(
                            "<tr><td>%s/%s/%s</td><td>$ %s</td></tr>\n",
                            $d, $m, $y, $amt
                        );
                    }
                }
                $stmtPag->close();
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="pie">
        <div class="tablaCuenta">
            <table class="table">
                <tbody>
                    <tr>
                        <td><strong>Pagos</strong></td>
                        <td>$ <?php echo e($totalPagos); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Deuda</strong></td>
                        <td>$ <?php echo e($Reg['Subtotal'] ?? '0.00'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- jQuery y Bootstrap -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js" integrity="sha512-b94ZBnuJQ6k6+..." crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha512-5o..." crossorigin="anonymous"></script>
    <script src="assets/js/jquery-2.1.0.min.js"></script>
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script>

    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
