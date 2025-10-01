<?php
session_start();
require_once '../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

// Validar sesión
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login');
    exit;
} else {
  // o rotar cada render
  $_SESSION['csrf_logout'] = $_SESSION['csrf_logout'] ?? bin2hex(random_bytes(32)); 
}

// Datos de usuario
$venta = "SELECT * FROM Empleados WHERE IdUsuario = '".$mysqli->real_escape_string($_SESSION["Vendedor"])."' LIMIT 1";
$res = $mysqli->query($venta);
$Reg = $res ? $res->fetch_assoc() : null;

$Vende = $Reg['Nivel'] ?? null;
$RegCt = null;
if (!empty($Reg['IdContacto'])) {
    $ContC = "SELECT * FROM Contacto WHERE Id = '".(int)$Reg["IdContacto"]."' LIMIT 1";
    $ResCt = $mysqli->query($ContC);
    $RegCt = $ResCt ? $ResCt->fetch_assoc() : null;
}

// Alerts
if(isset($_GET['Msg'])){
    $Mens = base64_decode($_GET['Msg']);
    echo "<script>alert('".htmlspecialchars($Mens, ENT_QUOTES)."');</script>";
}

// Lanzar modales
$Ventana = null;
if(!empty($_POST['RepDat'])){ $Ventana = "Ventana1"; }
elseif(!empty($_POST['ActDatos'])){ $Ventana = "Ventana2"; }

// Selector (si lo usas internamente)
require_once 'php/Selector_Emergentes_Ml.php';

// Meses para metas
$meses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
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

<!-- PWA / iOS -->
<link rel="manifest" href="/login/manifest.webmanifest?v=<?php echo $VerCache ?? 1; ?>">
<link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

<!-- CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo $VerCache ?>">

</head>
<body>

<!-- TOP BAR fija -->
<div class="topbar">
  <h4 class="title mb-0">Herramientas</h4>
</div>

<!-- Menú inferior -->
<section id="Menu">
  <?php require_once 'html/Menuprinc.php'; ?>
</section>

<!-- MODALES -->
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
          <input type="text"   name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
          <input type="text"   name="nombre" value="<?php echo htmlspecialchars($Reg['Nombre'] ?? ''); ?>" hidden>

          <label>Nombre</label>
          <input class="form-control" disabled type="text" value="<?php echo htmlspecialchars($Reg['Nombre'] ?? ''); ?>">

          <label class="mt-2">Puesto</label>
          <input class="form-control" disabled type="text" value="<?php
            echo htmlspecialchars($basicas->BuscarCampos($mysqli,"NombreNivel","Nivel","Id",$Reg['Nivel'] ?? 0));
          ?>">

          <label class="mt-2">Clabe Bancaria</label>
          <input class="form-control" disabled type="text" value="<?php echo htmlspecialchars($Reg['Cuenta'] ?? ''); ?>">

          <label class="mt-2">Dirección</label>
          <input class="form-control" type="text" name="Direccion" value="<?php echo htmlspecialchars($RegCt['Direccion'] ?? ''); ?>">

          <label class="mt-2">Teléfono</label>
          <input class="form-control" type="text" name="Telefono" value="<?php echo htmlspecialchars($RegCt['Telefono'] ?? ''); ?>">

          <label class="mt-2">Email</label>
          <input class="form-control" type="text" name="Mail" value="<?php echo htmlspecialchars($RegCt['Mail'] ?? ''); ?>">
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
          <input type="text" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" hidden>
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

<!-- CONTENIDO -->
<main class="page-content">
  <div class="container" style="width:99%;">
    <div class="mw-100">
      <?php if((int)$Vende <= 2): ?>
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

      <?php if((int)$Vende <= 2): ?>
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

      <?php if((int)$Vende <= 3): ?>
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
            <input type="file" name="archivo_csv" class="form-control">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="submit" name="action" value="buscar">Subir</button>
              <a class="btn btn-outline-secondary" href="https://kasu.com.mx/login/assets/Plantilla_Ctes_Masivos_KASU.csv" download>Descargar</a>
            </div>
          </div>
        </form>
        <hr>
      <?php endif; ?>

      <form method="POST" action="php/Funcionalidad_Pwa.php">
        <?php if((int)$Vende <= 2): ?>
          <label>Metas de Colocación y Cobranza (mes corriente)</label>
          <div class="form-group">
            <input class="form-control form-control-sm" type="number" name="MetaMes" placeholder="Meta de colocación del mes de <?php echo $meses[(int)date('n')]; ?>">
            <small class="form-text text-muted">No agregues símbolos ni decimales.</small>
            <input class="form-control form-control-sm mt-2" type="number" name="Normalidad" placeholder="% de normalidad del mes de <?php echo $meses[(int)date('n')]; ?>">
            <small class="form-text text-muted">No agregues símbolos ni decimales.</small>
          </div>
          <input class="btn btn-secondary btn-sm btn-block" type="submit" name="Asignar" value="Asignar Metas de Venta">
          <hr>
        <?php endif; ?>
      </form>

      <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div class="form-group">
          <input class="btn btn-secondary btn-sm btn-block" name="RepDat" type="submit" value="Reportar un problema">
          <input class="btn btn-secondary btn-sm btn-block" name="ActDatos" type="submit" value="Actualizar mis Datos">
        </div>
      </form>

      <form method="POST" enctype="multipart/form-data" action="php/Funcionalidad_Pwa.php">
        <label for="subirImg" id="RegCurBen" class="btn btn-secondary btn-sm btn-block">Nueva Foto de Perfil</label>
        <input type="file" id="subirImg" name="subirImg" onchange="cambiar()" onclick="OcuForCurp(this)" style="display:none">
        <div id="info" class="small text-muted"></div>
        <input type="submit" id="RegCurCli" class="btn btn-secondary btn-sm btn-block" name="btnEnviar" value="Cargar Foto" style="display:none;">
      </form>
      <hr>

      <!-- SALIR -->
      <form method="POST" action="/login/logout.php">
        <input type="hidden" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES); ?>">
        <input type="hidden" name="Evento" value="LogOut">
        <input type="hidden" name="checkdia" value="<?php echo date('Y-m-d'); ?>">
        <input type="hidden" name="csrf" value="<?php echo $_SESSION['csrf_logout']; ?>">
        <div class="Botones">
          <button class="btn btn-success btn-sm btn-block" type="submit" name="Salir" value="1">Salir</button>
        </div>
      </form>
    </div>

    <br><br><br>
  </div>
</main>

<!-- JS (única versión) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

<!-- Mostrar modal si aplica -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var v = <?php echo $Ventana ? json_encode('#'.$Ventana) : 'null'; ?>;
  if (v) { $(v).modal('show'); }
});
function cambiar(){
  var inp = document.getElementById('subirImg');
  if (inp.files && inp.files[0]) document.getElementById('info').textContent = inp.files[0].name;
}
function OcuForCurp(el){
  // toggle botón "Cargar Foto"
  var btnUp = document.getElementById("RegCurCli");
  btnUp.style.display = "";
}
</script>

</body>
</html>
