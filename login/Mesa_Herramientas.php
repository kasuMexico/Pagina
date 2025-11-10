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
session_start();
require_once '../eia/librerias.php';
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
if (!empty($_POST['RepDat'])) { $Ventana = "Ventana1"; }
elseif (!empty($_POST['ActDatos'])) { $Ventana = "Ventana2"; }

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
<body>

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
      <form method="POST" action="php/Funcionalidad_Empleados.php">
        <div class="modal-header">
          <h5 class="modal-title">Actualizar mis Datos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="number" name="IdContact" value="<?php echo (int)($RegCt['id'] ?? 0); ?>" hidden>
          <input type="text"   name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>" hidden>
          <input type="text"   name="nombre" value="<?php echo h($Reg['Nombre'] ?? ''); ?>" hidden>

          <label>Nombre</label>
          <input class="form-control" disabled type="text" value="<?php echo h($Reg['Nombre'] ?? ''); ?>">

          <label class="mt-2">Puesto</label>
          <input class="form-control" disabled type="text" value="<?php
            echo h($basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$Reg['Nivel'] ?? 0));
          ?>">

          <label class="mt-2">Clabe Bancaria</label>
          <input class="form-control" disabled type="text" value="<?php echo h($Reg['Cuenta'] ?? ''); ?>">

          <label class="mt-2">Dirección</label>
          <input class="form-control" type="text" name="Direccion" value="<?php echo h($RegCt['Direccion'] ?? ''); ?>">

          <label class="mt-2">Teléfono</label>
          <input class="form-control" type="text" name="Telefono" value="<?php echo h($RegCt['Telefono'] ?? ''); ?>">

          <label class="mt-2">Email</label>
          <input class="form-control" type="text" name="Mail" value="<?php echo h($RegCt['Mail'] ?? ''); ?>">
        </div>
        <div class="modal-footer">
          <input type="submit" name="CamDat" class="btn btn-primary" value="Modificar Datos">
        </div>
      </form>
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
    <div class="mw-100">
      <?php if ((int)$Vende <= 2): ?>
        <label>Buscar colaborador por nombre</label>
        <form method="POST" action="Mesa_Empleados.php">
          <div class="input-group mb-3">
            <input type="text" class="form-control" name="nombre" placeholder="Nombre del colaborador">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" name="action" value="buscar">Buscar</button>
            </div>
          </div>
        </form>

        <label>Buscar clientes por Status</label>
        <form method="POST" action="Mesa_Clientes.php">
          <div class="input-group mb-3">
            <select class="form-control" name="Status">
              <option value="0">Buscar cliente por Status</option>
              <option value="COBRANZA">COBRANZA</option>
              <option value="ACTIVO">ACTIVO</option>
              <option value="ACTIVACION">ACTIVACION</option>
              <option value="CANCELADO">CANCELADO</option>
              <option value="PREVENTA">PREVENTA</option>
              <option value="FALLECIDO">FALLECIDO</option>
            </select>
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" name="action" value="buscar">Buscar</button>
            </div>
          </div>
        </form>
      <?php endif; ?>

      <?php if ((int)$Vende <= 2): ?>
        <label>Buscar clientes por nombre</label>
        <form method="POST" action="Mesa_Clientes.php">
          <div class="input-group mb-3">
            <input type="text" name="nombre" class="form-control" placeholder="Buscar cliente por nombre">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" name="action" value="buscar">Buscar</button>
            </div>
          </div>
        </form>
      <?php endif; ?>

      <?php if ((int)$Vende <= 3): ?>
        <label>Buscar prospecto por nombre</label>
        <form method="POST" action="Mesa_Prospectos.php">
          <div class="input-group mb-3">
            <input type="text" name="nombre" class="form-control" placeholder="Buscar prospecto por nombre">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" name="action" value="buscar">Buscar</button>
            </div>
          </div>
        </form>
        <hr>

        <label>Carga masiva de clientes</label>
        <small class="form-text text-muted">Descarga la plantilla, llénala y súbela.</small>
        <form method="POST" action="Lote_Clientes.php" enctype="multipart/form-data">
          <div class="input-group mb-3">
            <input type="file" name="archivo_csv" class="form-control" accept=".csv,text/csv">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" name="action" value="buscar">Subir</button>
              <a class="btn btn-outline-secondary" href="https://kasu.com.mx/login/assets/Plantilla_Ctes_Masivos_KASU.csv" download>Descargar</a>
            </div>
          </div>
        </form>
        <hr>
      <?php endif; ?>

      <form method="POST" action="php/Funcionalidad_Pwa.php">
        <?php if ((int)$Vende <= 2): ?>
          <label>Metas de Colocación y Cobranza (mes corriente)</label>
          <div class="form-group">
            <input class="form-control form-control-sm" type="number" name="MetaMes" placeholder="Meta de colocación del mes de <?php echo h($meses[(int)date('n')]); ?>">
            <small class="form-text text-muted">No agregues símbolos ni decimales.</small>
            <input class="form-control form-control-sm mt-2" type="number" name="Normalidad" placeholder="% de normalidad del mes de <?php echo h($meses[(int)date('n')]); ?>">
            <small class="form-text text-muted">No agregues símbolos ni decimales.</small>
          </div>
          <input class="btn btn-secondary btn-sm btn-block" type="submit" name="Asignar" value="Asignar Metas de Venta">
          <hr>
        <?php endif; ?>
      </form>

      <form method="POST" action="<?php echo h($_SERVER['PHP_SELF']); ?>">
        <div class="form-group">
          <input class="btn btn-secondary btn-sm btn-block" name="RepDat" type="submit" value="Reportar un problema">
          <input class="btn btn-secondary btn-sm btn-block" name="ActDatos" type="submit" value="Actualizar mis Datos">
        </div>
      </form>

      <!-- =================== Foto de perfil ===================
           Qué hace: Sube nueva foto de perfil por POST multipart
           Fecha: 05/11/2025 | Revisado por: JCCM -->
      <form id="perfilForm" method="POST" enctype="multipart/form-data" action="php/Funcionalidad_Pwa.php">
        <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="btnEnviar" value="1">

        <input type="file" id="subirImg" name="subirImg" accept="image/*" class="d-none">
        <button type="button" id="btnFoto" class="btn btn-secondary btn-sm btn-block">Nueva foto de perfil</button>

        <div id="info" class="small text-muted"></div>
      </form>
      <hr>

      <!-- =================== Salir ===================
           Qué hace: Cierra sesión con token CSRF y marca de tiempo
           Fecha: 05/11/2025 | Revisado por: JCCM -->
      <form method="POST" action="/login/logout.php">
        <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="Evento" value="LogOut">
        <input type="hidden" name="checkdia" value="<?php echo h(date('Y-m-d')); ?>">
        <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf_logout']); ?>">
        <div class="Botones">
          <button class="btn btn-success btn-sm btn-block" type="submit" name="Salir" value="1">Salir</button>
        </div>
      </form>
    </div>

    <br><br><br>
  </div>
</main>

<!-- =================== JS (única versión) ===================
     Qué hace: Dependencias y scripts de interacción
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

<!-- =================== Mostrar modal si aplica ===================
     Qué hace: Abre modal #Ventana1 o #Ventana2 según $Ventana
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var v = <?php echo $Ventana ? json_encode('#'.$Ventana, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) : 'null'; ?>;
  if (v) { $(v).modal('show'); }
});

function cambiar(){
  var inp = document.getElementById('subirImg');
  if (inp && inp.files && inp.files[0]) {
    document.getElementById('info').textContent = inp.files[0].name;
  }
}

function OcuForCurp(el){
  // toggle botón "Cargar Foto" si aplica en vistas relacionadas
  var btnUp = document.getElementById("RegCurCli");
  if (btnUp) btnUp.style.display = "";
}
</script>

<!-- =================== Auto-subir foto ===================
     Qué hace: Dispara input file y envía el formulario al seleccionar imagen
     Fecha: 05/11/2025 | Revisado por: JCCM -->
<script>
(function () {
  var form = document.getElementById('perfilForm');
  var file = document.getElementById('subirImg');
  var btn  = document.getElementById('btnFoto');
  var info = document.getElementById('info');
  var locked = false;

  if (btn && file && form) {
    btn.addEventListener('click', function () { file.click(); });
    file.addEventListener('change', function () {
      if (!file.files || !file.files[0] || locked) return;
      locked = true;
      if (info) info.textContent = 'Cargando foto...';
      form.submit();
    });
  }
})();
</script>

</body>
</html>

