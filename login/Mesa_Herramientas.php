<?php
/********************************************************************************************
 * Qué hace: Página "Mesa Herramientas" de la PWA. Muestra utilidades para empleados:
 *           búsqueda, carga masiva, metas del mes, actualización de datos y reporte de fallas.
 *           Adaptada a PHP 8.2 con mysqli en modo excepciones y consultas preparadas.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

// =================== Sesión y dependencias ===================
// Qué hace: Inicia sesión, fija zona horaria, carga librerías y activa excepciones mysqli
// Fecha: 05/11/2025 | Revisado por: JCCM
require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
header_remove('X-Powered-By');

// =================== Utilidades ===================
// Qué hace: Función de escape segura para HTML
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!function_exists('h')) {
  function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

// =================== Validar sesión ===================
// Qué hace: Exige autenticación y genera token CSRF de salida
// Fecha: 05/11/2025 | Revisado por: JCCM
if (!isset($_SESSION["Vendedor"]) || $_SESSION["Vendedor"] === '') {
    header('Location: https://kasu.com.mx/login');
    exit;
} else {
    $_SESSION['csrf_logout'] = $_SESSION['csrf_logout'] ?? bin2hex(random_bytes(32));
}

// =================== Datos de usuario (consultas preparadas) ===================
// Qué hace: Obtiene registro del empleado por IdUsuario y su contacto asociado
// Fecha: 05/11/2025 | Revisado por: JCCM
$Reg = null;
$Vende = null;
$RegCt = null;

// Validación mínima de conexión
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión.');
}

// Empleado
$stmt = $mysqli->prepare('SELECT * FROM Empleados WHERE IdUsuario = ? LIMIT 1');
$stmt->bind_param('s', $_SESSION["Vendedor"]);
$stmt->execute();
if ($res = $stmt->get_result()) {
    $Reg = $res->fetch_assoc() ?: null;
}
$stmt->close();
$Vende = $Reg['Nivel'] ?? null;

// Contacto del empleado
if (!empty($Reg['IdContacto'])) {
    $idc = (int)$Reg['IdContacto'];
    $stmt = $mysqli->prepare('SELECT * FROM Contacto WHERE Id = ? LIMIT 1');
    $stmt->bind_param('i', $idc);
    $stmt->execute();
    if ($rc = $stmt->get_result()) {
        $RegCt = $rc->fetch_assoc() ?: null;
    }
    $stmt->close();
}

// =================== Alerts ===================
// Qué hace: Muestra alerta si viene ?Msg= codificado en base64
// Fecha: 05/11/2025 | Revisado por: JCCM
if (isset($_GET['Msg'])) {
    $Mens = base64_decode((string)$_GET['Msg'], true);
    if ($Mens !== false && $Mens !== null) {
        echo "<script>alert('".h($Mens)."');</script>";
    }
}

// =================== Lanzar modales ===================
// Qué hace: Define qué modal abrir en el render actual
// Fecha: 05/11/2025 | Revisado por: JCCM
$Ventana = null;
if (isset($_POST['RepDat'])) { $Ventana = "Ventana1"; }
elseif (isset($_POST['ActDatos'])) { $Ventana = "Ventana2"; }

// =================== Catálogo de meses ===================
// Qué hace: Arreglo para mostrar nombre del mes en metas
// Fecha: 05/11/2025 | Revisado por: JCCM
$meses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

// =================== Cache bust ===================
// Qué hace: Versión de recursos estáticos
// Fecha: 05/11/2025 | Revisado por: JCCM
$VerCache = time();
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="theme-color" content="#F2F2F2">
<link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
<title>Mesa Herramientas</title>

<!-- =================== PWA / iOS ===================
     Qué hace: Manifiesto y recursos para instalación
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<link rel="manifest" href="/login/manifest.webmanifest?v=<?php echo (int)$VerCache; ?>">
<link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

<!-- =================== CSS ===================
     Qué hace: Estilos base del sitio
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo (int)$VerCache; ?>">

</head>
<body onload="localize()">
<!-- =================== TOP BAR fija ===================
     Qué hace: Encabezado de la sección
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<div class="topbar">
  <h4 class="title mb-0">Herramientas</h4>
</div>

<!-- =================== Menú inferior ===================
  Qué hace: Carga menú principal de la PWA
  Fecha: 05/11/2025 | Revisado por: JCCM 
-->
<section id="Menu">
  <?php require_once 'html/Menuprinc.php'; ?>
</section>

<!-- =================== MODALES ===================
     Qué hace: Formularios para actualizar datos y reportar problema
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<section class="VentanasEmergentes">
  <!-- Modal Actualizar Datos -->
  <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <?php
        $nombre = $Reg['Nombre'] ?? '';
        $Reg_backup   = $Reg;
        $Recg_backup  = $Recg ?? null;
        $Recg1_backup = $Recg1 ?? null;
        $Reg = [
          'Id'       => (int)($RegCt['id'] ?? 0),
          'Nombre'   => $Reg_backup['Nombre'] ?? '',
          'Producto' => 'Empleado',
          'Sucursal' => $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$Reg_backup['Sucursal'] ?? 0)
        ];
        $Recg = [
          'id'       => (int)($RegCt['id'] ?? 0),
          'calle'    => $RegCt['Direccion'] ?? ($RegCt['calle'] ?? ''),
          'Telefono' => $RegCt['Telefono'] ?? '',
          'Mail'     => $RegCt['Mail'] ?? ''
        ];
        $Recg1 = [
          'id' => (int)($Reg_backup['IdUsuario'] ?? 0)
        ];
        require __DIR__ . '/html/ActualizarDatos.php';
        $Reg  = $Reg_backup;
        $Recg = $Recg_backup;
        $Recg1 = $Recg1_backup;
      ?>
    </div></div>
  </div>

  <!-- Modal Reporte de Problema -->
  <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
      <form method="POST" action="php/Funcionalidad_Empleados.php">
        <div class="modal-header">
          <h5 class="modal-title">Reportar un problema</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="text" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>" hidden>
          <div class="form-group">
            <label>¿Qué problema tuviste?</label>
            <textarea class="form-control" name="problema" rows="3" placeholder="Describe el problema con detalle"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="submit" name="Reporte" class="btn btn-primary" value="Enviar Reporte">
        </div>
      </form>
    </div></div>
  </div>
</section>

<!-- =================== CONTENIDO ===================
     Qué hace: Formularios y utilidades según nivel del empleado
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<main class="page-content">
  <div class="container" style="width:99%;">
    <div class="tool-sections">

      <?php if ($Reg): ?>
      <section class="tool-section">
        <header>
          <strong>Mi información</strong>
          <small><?php echo h($basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$Reg['Nivel'] ?? 0)); ?></small>
        </header>
        <div class="tool-section-body small">
          <p class="mb-1"><strong>Nombre:</strong> <?php echo h($Reg['Nombre'] ?? ''); ?></p>
          <p class="mb-1"><strong>Usuario:</strong> <?php echo h($_SESSION['Vendedor']); ?></p>
          <p class="mb-1"><strong>Sucursal:</strong> <?php echo h($basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$Reg['Sucursal'] ?? 0)); ?></p>
          <p class="mb-1"><strong>Teléfono:</strong> <?php echo h($RegCt['Telefono'] ?? 'Sin registro'); ?></p>
          <p class="mb-0"><strong>Correo:</strong> <?php echo h($RegCt['Mail'] ?? 'Sin registro'); ?></p>
        </div>
      </section>
      <?php endif; ?>

      <?php if ((int)$Vende <= 3): ?>
      <section class="tool-section">
        <header><strong>Buscadores rápidos</strong></header>
        <div class="tool-section-body">
          <?php if ((int)$Vende <= 2): ?>
          <form method="POST" action="Mesa_Empleados.php" class="integrated-search mb-2">
            <input type="text" name="nombre" placeholder="Nombre del colaborador">
            <button type="submit">Buscar</button>
          </form>
          <?php endif; ?>

          <form method="POST" action="Mesa_Clientes.php" class="integrated-search mb-2">
            <input type="text" name="nombre" placeholder="Cliente por nombre">
            <button type="submit">Buscar</button>
          </form>

          <form method="POST" action="Mesa_Clientes.php" class="integrated-search mb-2">
            <select name="Status">
              <option value="">Status de cobranza</option>
              <option value="COBRANZA">COBRANZA</option>
              <option value="ATRASADO">ATRASADO</option>
              <option value="PREVENTA">PREVENTA</option>
            </select>
            <button type="submit">Filtrar</button>
          </form>

          <?php if ((int)$Vende <= 3): ?>
          <form method="POST" action="Mesa_Prospectos.php" class="integrated-search mb-0">
            <input type="text" name="nombre" placeholder="Prospecto por nombre">
            <button type="submit">Buscar</button>
          </form>
          <?php endif; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if ((int)$Vende <= 2): ?>
      <section class="tool-section">
        <header><strong>Carga masiva de clientes</strong></header>
        <div class="tool-section-body">
          <form method="POST" action="Lote_Clientes.php" enctype="multipart/form-data">
            <div class="file-input mb-2">
              <input type="file" id="archivoCsv" name="archivo_csv" accept=".csv,text/csv">
              <label for="archivoCsv">
                <span>Selecciona archivo CSV</span>
                <em>Browse</em>
              </label>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button class="btn btn-secondary btn-sm mr-2" type="submit">Subir archivo</button>
              <a class="btn btn-outline-secondary btn-sm" href="https://kasu.com.mx/login/assets/Plantilla_Ctes_Masivos_KASU.csv" download>Descargar plantilla</a>
            </div>
          </form>
        </div>
      </section>
      <?php endif; ?>

      <?php if ((int)$Vende <= 2): ?>
      <section class="tool-section">
        <header><strong>Metas y normalidad</strong></header>
        <div class="tool-section-body">
          <form method="POST" action="php/Funcionalidad_Pwa.php">
            <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="Evento" value="LogOut">
            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_logout']); ?>">
            <div id="Gps" style="display:none;"></div>
            <div data-fingerprint-slot></div>
            <div class="form-group">
              <label class="small">Meta de colocación (<?php echo h($meses[(int)date('n')]); ?>)</label>
              <input class="form-control form-control-sm" type="number" name="MetaMes" placeholder="Ej. 120000">
            </div>
            <div class="form-group">
              <label class="small">% Normalidad de Cobranza</label>
              <input class="form-control form-control-sm" type="number" name="Normalidad" placeholder="Ej. 92">
            </div>
            <button class="btn btn-secondary btn-sm btn-block" type="submit" name="Asignar" value="1">Guardar metas</button>
          </form>
        </div>
      </section>
      <?php endif; ?>

      <section class="tool-section">
        <header><strong>Acciones rápidas</strong></header>
        <div class="tool-section-body tool-actions-body">
          <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>" class="mb-2">
            <div class="action-buttons">
              <button class="btn btn-outline-primary btn-sm w-100" name="ActDatos" type="submit">Actualizar mis datos</button>
              <button class="btn btn-outline-warning btn-sm w-100" name="RepDat" type="submit">Reportar un problema</button>
            </div>
          </form>
          <form method="POST" action="/login/logout.php">
            <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
            <input type="hidden" name="Evento" value="LogOut">
            <input type="hidden" name="checkdia" value="<?php echo h(date('Y-m-d')); ?>">
            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_logout']); ?>">
            <button class="btn btn-success btn-sm w-100" type="submit" name="Salir" value="1">Cerrar sesión</button>
          </form>
        </div>
      </section>

    </div>
    <br><br><br>
  </div>
</main>

<!-- =================== JS (única versión) ===================
     Qué hace: Dependencias y scripts de interacción
     Fecha: 05/11/2025 | Revisado por: JCCM -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/finger.js?v=3"></script>
  <script src="Javascript/localize.js?v=3"></script>
  <script src="Javascript/Inyectar_gps_form.js"></script>
<!-- =================== Mostrar modal si aplica ===================
     Qué hace: Abre modal #Ventana1 o #Ventana2 según $Ventana
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var v = <?php echo $Ventana ? json_encode('#'.$Ventana, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : 'null'; ?>;
  if (v) { $(v).modal('show'); }
});
</script>
</body>
</html>
