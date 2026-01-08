<?php
/********************************************************************************************
 * Valida CURP + No. de Póliza contra Venta⇄Usuario. Muestra:
 *   - Izquierda: mini estado de cuenta (cobros, comisión, status).
 *   - Derecha: formulario de contacto.
 * Entradas POST:
 *   txtCurp_ActIndCli        => CURP
 *   txtNumTarjeta_ActIndCli  => Póliza (Venta.IdFIrma)
 * Rev: 04/11/2025 — JCCM
 ********************************************************************************************/
declare(strict_types=1);
date_default_timezone_set('America/Mexico_City');
header_remove('X-Powered-By');

session_start();
require_once __DIR__ . '/../../eia/analytics_bootstrap.php';
require_once '../../eia/librerias.php';
if (!isset($mysqli) || !($mysqli instanceof mysqli)) { http_response_code(500); exit('Error de conexión.'); }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function curp_ok(string $s): bool {
  return (bool)preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{2}[A-Z]{3}[A-Z0-9]{2}$/', $s);
}

/* ===== 1) Entradas ===== */
$curp   = strtoupper(trim((string)($_POST['txtCurp_ActIndCli'] ?? '')));
$poliza = trim((string)($_POST['txtNumTarjeta_ActIndCli'] ?? ''));
if ($curp === '' && isset($_SESSION['txtCurp_ActIndCli'])) { $curp = strtoupper(trim((string)$_SESSION['txtCurp_ActIndCli'])); }
if ($curp === '' || $poliza === '' || !curp_ok($curp)) {
  echo '<script>alert("Datos insuficientes o CURP inválida.");location.href="../index.php?stat=1";</script>'; exit;
}

/* ===== 2) Venta + Usuario ===== */
$venta = [
  'Id'=>0,'IdContact'=>0,'Nombre'=>'','TipoServicio'=>'',
  'IdFIrma'=>'','Producto'=>'','Status'=>'','Referencia_KASU'=>'','FechaRegistro'=>''
];
$contact = [];

try {
  $sql = 'SELECT v.`Id`, v.`IdContact`, v.`Nombre`, v.`TipoServicio`, v.`IdFIrma`,
                 v.`Producto`, v.`Status`, v.`Referencia_KASU`, v.`FechaRegistro`
          FROM `Venta` v
          INNER JOIN `Usuario` u ON u.`IdContact` = v.`IdContact`
          WHERE TRIM(v.`IdFIrma`) = ? AND u.`ClaveCurp` = ?
          LIMIT 1';
  $st = $mysqli->prepare($sql);
  $st->bind_param('ss', $poliza, $curp);
  $st->execute();
  $rs = $st->get_result();
  if ($rs && $rs->num_rows) { $venta = $rs->fetch_assoc(); }
  $st->close();

  if (empty($venta['Id']) || (int)$venta['IdContact'] <= 0) {
    echo '<script>alert("La póliza no pertenece a la CURP indicada o no existe.");history.back();</script>'; exit;
  }

  /* 2.1 Contacto por IdContact */
  $sqlC = 'SELECT * FROM `Contacto` WHERE `id` = ? LIMIT 1';
  $stC = $mysqli->prepare($sqlC);
  $idContact = (int)$venta['IdContact'];
  $stC->bind_param('i', $idContact);
  $stC->execute();
  $rsC = $stC->get_result();
  if ($rsC && $rsC->num_rows) { $contact = $rsC->fetch_assoc(); }
  $stC->close();

  /* 2.2 Fallback por CURP */
  if (!$contact) {
    $sqlU = 'SELECT `IdContact` FROM `Usuario` WHERE `ClaveCurp` = ? ORDER BY `id` DESC LIMIT 1';
    $stU = $mysqli->prepare($sqlU);
    $stU->bind_param('s', $curp);
    $stU->execute();
    $rsU = $stU->get_result();
    if ($rsU && $rsU->num_rows) {
      $rowU = $rsU->fetch_assoc();
      $altId = (int)($rowU['IdContact'] ?? 0);
      if ($altId > 0) {
        $sqlC2 = 'SELECT * FROM `Contacto` WHERE `id` = ? LIMIT 1';
        $stC2 = $mysqli->prepare($sqlC2);
        $stC2->bind_param('i', $altId);
        $stC2->execute();
        $rsC2 = $stC2->get_result();
        if ($rsC2 && $rsC2->num_rows) { $contact = $rsC2->fetch_assoc(); }
        $stC2->close();
      }
    }
    $stU->close();
  }

} catch (Throwable $e) {
  error_log('datos.php SQL: '.$e->getMessage().' | state: '.$mysqli->sqlstate);
  echo '<script>alert("Error al consultar la información.");location.href="../index.php?stat=1";</script>'; exit;
}

/* ===== 2.3 Dirección normalizada ===== */
$addr = [
  'codigo_postal' => (string)($contact['codigo_postal'] ?? ''),
  'calle'         => (string)($contact['calle'] ?? ($contact['Direccion'] ?? '')),
  'numero'        => (string)($contact['numero'] ?? ''),
  'colonia'       => (string)($contact['colonia'] ?? ''),
  'municipio'     => (string)($contact['municipio'] ?? ''),
  'estado'        => (string)($contact['estado'] ?? ''),
  'Referencia'    => (string)($contact['Referencia'] ?? ''),
  'Telefono'      => (string)($contact['Telefono'] ?? ''),
  'Mail'          => (string)($contact['Mail'] ?? ''),
];

/* ===== Producto comprado ===== */
$ProductoComprado = 'RETIRO';
$checkers = ['FUNERARIO'=>'ProdFune','OFICIAL DE SEGURIDAD'=>'ProdPli','TRANSPORTISTA'=>'ProdTrans'];
foreach ($checkers as $label => $method) {
  if (method_exists($basicas, $method) && $basicas->$method($venta['Producto'])) { $ProductoComprado = $label; break; }
}

/* ===== 3) Estado de cuenta ===== */

/* 3.1 Mes filtrado */
$mesSel = $_POST['fmes'] ?? '';
if (!preg_match('/^\d{4}-\d{2}$/', $mesSel)) { $mesSel = date('Y-m'); }
$iniMes = $mesSel . '-01 00:00:00'; //Fecha Inicio de Mes
$finMes = date('Y-m-t 23:59:59', strtotime($mesSel.'-01')); //Fecha final de Mes

/* 3.2 Porcentaje de cobro por producto y año */

/* 3.2.1) Determina el año a usar, para calculo de los montos de referencia segun esten validos en el año
      - Preferente: año de FechaRegistro de la venta
      - Alterno: año de $iniMes si no hay FechaRegistro */
$anioVenta = !empty($venta['FechaRegistro'])
  ? (int)date('Y', strtotime($venta['FechaRegistro']))
  : (int)date('Y', strtotime($iniMes));

/* 3.2.2) Inicializa el porcentaje como null para saber si hubo match en BD */
$porcCobr = null;

/* 3.2.3) Intenta obtener Porc_Cobr para el producto en el año de la venta */
$qPC = 'SELECT `Porc_Cobr` FROM `Productos` WHERE `Producto`=? AND `Validez`=? LIMIT 1';
if ($st = $mysqli->prepare($qPC)) {
  // 'si' = string (Producto), int (Validez)
  $st->bind_param('si', $venta['Producto'], $anioVenta);
  $st->execute();
  $r = $st->get_result();
  if ($r && $r->num_rows) {
    $porcCobr = (float)$r->fetch_assoc()['Porc_Cobr'];
  }
  $st->close();
}

/* 3.2.4) Fallback: si no hay registro para ese año, intenta con el año actual */
if ($porcCobr === null) {
  $anioHoy = (int)date('Y');
  if ($st = $mysqli->prepare($qPC)) {
    $st->bind_param('si', $venta['Producto'], $anioHoy);
    $st->execute();
    $r = $st->get_result();
    if ($r && $r->num_rows) {
      $porcCobr = (float)$r->fetch_assoc()['Porc_Cobr'];
    }
    $st->close();
  }
}

/* 3.2.5) Fallback final: toma el último porcentaje disponible por Validez descendente */
if ($porcCobr === null) {
  $qUlt = 'SELECT `Porc_Cobr` FROM `Productos` WHERE `Producto`=? ORDER BY `Validez` DESC LIMIT 1';
  if ($st = $mysqli->prepare($qUlt)) {
    $st->bind_param('s', $venta['Producto']);
    $st->execute();
    $r = $st->get_result();
    if ($r && $r->num_rows) {
      $porcCobr = (float)$r->fetch_assoc()['Porc_Cobr'];
    }
    $st->close();
  }
}

/* 3.2.6) Si no se encontró nada en BD, usa 0.0 por defecto */
if ($porcCobr === null) { $porcCobr = 0.0; }

/* 3.2.7) Normaliza a proporción [0..1] y acota:
      - Ejemplo: 14 => 0.14
      - Protege contra valores negativos o >100 */
$porc = max(0.0, min(1.0, $porcCobr / 100.0));

/* 3.3 Pagos del periodo */

/* 3.3.1 IdFirma e identificación de ventas ligadas
   - Toma el IdFIrma/Referencia del vendedor desde la venta actual.
   - Busca TODAS las ventas cuya TRIM(Usuario) = IdFIrma.
   - Llena $ventasMeta con Id, Producto y FechaRegistro.
   - Si no hay resultados, al menos agrega la venta actual para no romper el flujo. 
   - Debemos buscar las ventas que t
   */
$Vendedor = $miIdFirma = trim((string)($venta['IdFIrma'] ?? ''));


$ventasMeta = []; // [IdVenta] => ['Producto'=>..., 'FechaRegistro'=>...]
$qVL = 'SELECT v.`Id`, v.`Producto`, v.`FechaRegistro`
        FROM `Venta` v
        WHERE TRIM(v.`Referencia_KASU`) = ?';
$stVL = $mysqli->prepare($qVL);
$stVL->bind_param('s', $miIdFirma);
$stVL->execute();
$rsVL = $stVL->get_result();
while ($rsVL && $rowVL = $rsVL->fetch_assoc()) {
  $ventasMeta[(int)$rowVL['Id']] = [
    'Producto'      => (string)$rowVL['Producto'],
    'FechaRegistro' => (string)$rowVL['FechaRegistro'],
  ];
}
$stVL->close();

/* 3.3.2 Pagos del MES para esas ventas
   - Si hay ventas relacionadas, arma un IN dinámico con placeholders.
   - Recupera pagos dentro del rango [iniMes, finMes].
   - Ordena por FechaRegistro e Id para aplicar FIFO después. */
$pagos = [];
if ($ventasMeta) {
  $ids = array_keys($ventasMeta);
  $ph  = implode(',', array_fill(0, count($ids), '?')); // ?,?,?... tantos como ids
  $tp  = str_repeat('i', count($ids)) . 'ss';           // tipos: i...i + s + s (fechas)

  $qPag = "SELECT `Id`,`Cantidad`,`IdVenta`,`FechaRegistro`,`Referencia`
           FROM `Pagos`
           WHERE `IdVenta` IN ($ph)
             AND `FechaRegistro` BETWEEN ? AND ?
           ORDER BY `FechaRegistro` ASC, `Id` ASC";
  $stP = $mysqli->prepare($qPag);
  $bind = $ids;        // primeros N = IdVenta (int)
  $bind[] = $iniMes;   // penúltimo = inicio rango (string)
  $bind[] = $finMes;   // último   = fin rango (string)
  $stP->bind_param($tp, ...$bind);
  $stP->execute();
  $rP = $stP->get_result();

  /* 3.3.3 Total de comisiones pagadas del MES a este IdVendedor
     - Suma lo que YA fue pagado al vendedor en el mes.
     - Se usará para cubrir comisiones esperadas (FIFO). */
  $totPagadoMes = 0.0;
  $qCom = "SELECT COALESCE(SUM(`Cantidad`),0) AS s
           FROM `Comisiones_pagos`
           WHERE `IdVendedor`= ? AND `fechaRegistro` BETWEEN ? AND ?";
  $stC = $mysqli->prepare($qCom);
  $stC->bind_param('sss', $miIdFirma, $iniMes, $finMes);
  $stC->execute();
  if ($rc = $stC->get_result()) { $totPagadoMes = (float)$rc->fetch_assoc()['s']; }
  $stC->close();

  /* 3.3.4 FIFO: aplica lo pagado del MES contra comisiones del MES
     - Para cada pago de cliente calcula comisión esperada = monto * $porc.
     - Resta del “saldoPago” lo ya cubierto en el mes.
     - Marca cada renglón como Pagado si la comisión quedó en 0, si no Pendiente. */
  $saldoPago = $totPagadoMes;
  while ($rP && $row = $rP->fetch_assoc()) {
    $monto  = (float)$row['Cantidad'];
    $comEsp = round($monto * $porc, 2);

    $cubierto   = min($comEsp, max(0.0, $saldoPago)); // lo que alcanza a cubrirse
    $saldoPago -= $cubierto;                          // baja el saldo de pago del mes
    $pendiente  = max(0.0, $comEsp - $cubierto);      // lo que aún falta

    $row['Concepto']       = 'Cobro';
    $row['Comision']       = $comEsp;
    $row['Cubierto']       = $cubierto;
    $row['Pendiente']      = $pendiente;
    $row['StatusComision'] = ($pendiente <= 0.01) ? 'Pagado' : 'Pendiente';

    $pagos[] = $row;
  }
  $stP->close();
}

/* 3.3.5 Total pendiente del MES para el botón "Solicitar pago"
   - Suma todos los pendientes de comisión calculados.
   - Este total alimenta la UI para solicitar pago de comisiones. */
$totalPendiente = 0.0;
foreach ($pagos as $p) {
  $totalPendiente += (float)($p['Pendiente'] ?? 0);
}


/* 3.6 Selector de meses (últimos 6) sin strftime */
$opcMeses = [];
$fmt = class_exists('IntlDateFormatter')
  ? new IntlDateFormatter('es_MX', IntlDateFormatter::NONE, IntlDateFormatter::NONE, date_default_timezone_get(), IntlDateFormatter::GREGORIAN, 'LLLL y')
  : null;
$mesesES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
for ($i=0; $i<6; $i++) {
  $t = strtotime("-$i month");
  $val = date('Y-m', $t);
  $lab = $fmt ? $fmt->format($t) : ($mesesES[(int)date('n',$t)-1].' '.date('Y',$t));
  $lab = mb_convert_case($lab, MB_CASE_TITLE, 'UTF-8');
  $opcMeses[] = [$val,$lab];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>KASU | Mi cuenta KASU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../../assets/css/font-awesome.css">
  <link rel="stylesheet" href="../assets/estadocta.css?v=1">
  <link rel="stylesheet" href="../../login/assets/css/cupones.css">
</head>
<body>
  <!-- Botón hamburguesa (no afecta tu menú global) -->
  <button class="toggle-btn" id="btnAside" type="button" aria-label="Abrir menú">
    <span class="fa fa-bars"></span> Menú
  </button>

  <div class="app-wrap">
    <?php
      if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
      if (empty($_SESSION['csrf_logout'])) {
          $_SESSION['csrf_logout'] = bin2hex(random_bytes(16));
      }
    ?>
    <!-- ASIDE -->
      <aside class="aside" id="aside">
        <h5 class="d-flex align-items-center" style="margin:6px 10px 14px;">
        <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg"
            alt="KASU"
            width="24" height="24"
            loading="lazy" decoding="async"
            style="border-radius:4px;margin-right:8px;">
        Mi cuenta KASU
      </h5>
      <nav class="nav flex-column">
        <a href="#" class="nav-link" data-target="#sec-datos">
          <i class="fa fa-user"></i> &nbsp; Mis Datos
        </a>
        <a href="#" class="nav-link active" data-target="#sec-estado">
          <i class="fa fa-list-alt"></i> &nbsp; Mis Referidos
        </a>
        <a href="#" class="nav-link" data-target="#sec-sociales">
          <i class="fa fa-share-square"></i> &nbsp; Compartir KASU
        </a>
        <form id="logoutForm" action="logout.php" method="post" style="display:none">
          <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_logout']); ?>">
        </form>
        <a href="#" onclick="document.getElementById('logoutForm').submit(); return false;" class="nav-link">
          <i class="fa fa-sign-out"></i> Salir
        </a>
      </nav>
    </aside>

    <!-- CONTENIDO -->
    <main class="main">
      <div class="container-fluid">
        <?php
        // Mostrar errores mientras desarrollas
        error_reporting(E_ALL);
        ini_set('display_errors','1');

        // Base = directorio del archivo actual
        $base = __DIR__;

        // Sección: Estado de cuenta
        require_once $base . '/EstadoCta.php';

        // Sección: Modificar mis datos
        require_once $base . '/ModifDatos.php';
        
        // Sección: Modificar mis datos
        require_once $base . '/CompartirRedes.php';
        ?>
      </div>
    </main>

  <script src="../../assets/js/jquery-2.1.0.min.js"></script>
  <script src="../../assets/js/popper.js"></script>
  <script src="../../assets/js/bootstrap.min.js"></script>
  <script>
    // Toggle aside en móviles
    (function(){
      var btn = document.getElementById('btnAside');
      var aside = document.getElementById('aside');
      btn && btn.addEventListener('click', function(){ aside.classList.toggle('open'); });

      // Navegación entre secciones
      document.querySelectorAll('.aside .nav-link').forEach(function(a){
        a.addEventListener('click', function(e){
          e.preventDefault();
          document.querySelectorAll('.aside .nav-link').forEach(x=>x.classList.remove('active'));
          a.classList.add('active');
          var tgt = a.getAttribute('data-target');
          document.querySelectorAll('.section').forEach(function(s){
            s.classList.toggle('active', s.id === tgt.substring(1));
          });
          // Auto-cerrar el menú en móvil
          if (window.matchMedia('(max-width: 992px)').matches) { aside.classList.remove('open'); }
        });
      });
    })();
  </script>
</body>
</html>
