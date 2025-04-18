<?php
// registro.php

// Iniciar sesión y cargar librerías
session_start();
require_once __DIR__ . '/eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

// Archivo donde se envían los registros
$archivoRegistro = '/eia/php/Registrar_Venta.php';

// Depuración (quítalo en producción)
echo "Librerías cargadas correctamente.<br>";
$basicas = new Basicas();
echo "Instancia de Basicas creada.<br>";

// Asegurarnos de tener conexión a BD
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die("Error: no se detectó la conexión a la base de datos.");
}

// Recuperar y sanear parámetros GET
$stat = filter_input(INPUT_GET, 'stat', FILTER_VALIDATE_INT);
$Dtpg = filter_input(INPUT_GET, 'Dtpg', FILTER_SANITIZE_STRING);
$Cte  = filter_input(INPUT_GET, 'Cte',  FILTER_SANITIZE_STRING);
$liga = filter_input(INPUT_GET, 'liga', FILTER_SANITIZE_URL);
$Name = filter_input(INPUT_GET, 'Name', FILTER_SANITIZE_STRING);
$curp = filter_input(INPUT_GET, 'curp', FILTER_SANITIZE_STRING);

// Obtener producto de la sesión (si existe)
$Producto = $_SESSION['Producto'] ?? null;

// Inicializar variables
$Valor     = 0;
$Descuento = 0.0;
$Img       = '';
$Costo     = floatval($_SESSION['Costo'] ?? 0);
$Ventana   = 'Ventana1';
$COntVtan  = '';

// Si hay un servicio seleccionado en sesión
if ($Producto) {
    if (!empty($_SESSION['tarjeta'])) {
        $IdProd   = intval($basicas->BuscarCampos($mysqli, "Id", "Productos", "Producto", $Producto));
        $Img       = $basicas->BuscarCampos($mysqli, "Img", "PostSociales", "Id", $_SESSION['tarjeta']);
        $Descuento = floatval($basicas->BuscarCampos($mysqli, "Descuento", "PostSociales", "Id", $_SESSION['tarjeta']));
        $Prod      = $basicas->BuscarCampos($mysqli, "Producto", "PostSociales", "Id", $_SESSION['tarjeta']);
        $IdPCup    = intval($basicas->BuscarCampos($mysqli, "Id", "Productos", "Producto", $Prod));

        if ($IdProd >= $IdPCup) {
            $Valor = 1;
            $Costo -= $Descuento;
        }
    }

    // Ventana predefinida en sesión
    if (!empty($_SESSION['Ventana'])) {
        $Ventana = $_SESSION['Ventana'];
    }
}
// Si no hay producto, validamos 'stat'
elseif ($stat === 1) {
    $pDtpg = htmlspecialchars($Dtpg, ENT_QUOTES);
    $COntVtan = "
        <h2>FELICIDADES!!!</h2>
        <h3>Estás a un paso de concluir tu registro</h3>
        <p>Te enviamos tu tarjeta, por favor liga tu CURP:</p>
        <form method='POST' action='{$archivoRegistro}'>
            <input type='hidden' name='stat' value='1'>
            <input type='hidden' name='Dtpg' value='{$pDtpg}'>
            <input name='CurBen' maxlength='18' placeholder='Clave CURP' required>
            <button type='submit' name='ActuPago' class='main-button'>VERIFICAR MI PAGO</button>
        </form>";
    $Ventana = 'Ventana3';

} elseif ($stat === 2) {
    $msg = htmlspecialchars($Cte, ENT_QUOTES);
    $COntVtan = "
        <h2>Parece que hubo un error!!</h2>
        <h3>{$msg}</h3>
        <p>No pudimos procesar tu tarjeta; te enviaremos fichas de pago por correo.</p>";
    $Ventana = 'Ventana3';

} elseif ($stat === 3) {
    $msg = htmlspecialchars($Cte, ENT_QUOTES);
    $url = htmlspecialchars($liga, ENT_QUOTES);
    $COntVtan = "
        <h2>FELICIDADES!!!</h2>
        <h3>{$msg}</h3>
        <p>Tu pago está en camino. Paga ahora:</p>
        <a href='{$url}' class='main-button'>Ir a pagar</a>";
    $Ventana = 'Ventana3';

} elseif (in_array($stat, [4,5], true)) {
    $title   = $stat === 4 ? 'TU YA ERES CLIENTE KASU' : 'LA CLAVE CURP NO EXISTE';
    $message = $stat === 4
        ? "El CURP {$curp} ya está registrado."
        : "El CURP {$curp} no existe o pertenece a persona fallecida.";
    $name = htmlspecialchars($Name, ENT_QUOTES);
    $COntVtan = "
        <h2>{$name}</h2>
        <h3>{$title}</h3>
        <p>{$message}</p>
        <a href='tel:+527125975763' class='main-button'>LLAMAR A KASU</a>";
    $Ventana = 'Ventana3';
}

// Construir select de plazos
if ($Producto === 'Universidad') {
    $sel18 = "<option value='36'>3 años</option>";
    $sel24 = "<option value='96'>8 años</option>";
} else {
    $sel18 = $sel24 = '';
}

// Selector de tipo de servicio
if ($Producto !== 'Universidad') {
    $SelTipServ = "
    <p>Selecciona el tipo de servicio</p>
    <select name='TipoServicio'>
      <option value='Tradicional'>Tradicional</option>
      <option value='Ecologico' selected>Ecológico</option>
      <option value='Cremacion'>Cremación</option>
    </select>";
} else {
    $SelTipServ = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro | KASU</title>
    <link rel="icon" href="assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="assets/css/Compra.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="eia/javascript/Registro.js"></script>
    <script src="eia/javascript/validarcurp.js"></script>
    <!-- jQuery + Bootstrap JS para modales -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
</head>
<body onload="$('#<?= $Ventana ?>').modal('toggle')">

<!-- Modal Ventana1 -->
<div class="modal fade" id="Ventana1" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"><div class="modal-body">
      <div class="Formulario">
        <h3>¿El servicio es para ti?</h3>
        <select id="RegSelCur" onchange="OcuForCurp(this)">
          <option value="RegCurBen">Sí</option>
          <option value="RegCurCli">No</option>
        </select>
      </div>
    </div></div>
  </div>
</div>

<!-- Modal Ventana2 (cupón / forma de pago) -->
<div class="modal fade" id="Ventana2" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"><div class="modal-body">
      <form method="POST" action="<?= $archivoRegistro ?>">
        <input type="hidden" name="Cupon" value="<?= htmlspecialchars($_SESSION['tarjeta'] ?? '', ENT_QUOTES) ?>">
        <h2><?= htmlspecialchars($_SESSION['NombreCOm'] ?? '', ENT_QUOTES) ?></h2>
        <?php if ($Valor === 1): ?>
          <img src="assets/images/cupones/<?= htmlspecialchars($Img, ENT_QUOTES) ?>" class="img-thumbnail" style="width:15em">
          <p>Precio inicial: <?= money_format('%.2n', $_SESSION['Costo'] ?? 0) ?></p>
          <p>Descuento: <?= money_format('%.2n', $Descuento) ?></p>
        <?php else: ?>
          <p>No aplicable</p>
        <?php endif; ?>
        <p>Edad: <?= htmlspecialchars($_SESSION['Edad'] ?? '', ENT_QUOTES) ?></p>
        <p>Producto: <?= htmlspecialchars($Producto, ENT_QUOTES) ?></p>
        <p>Selecciona tiempo de pago:</p>
        <select name="Meses" onchange="CalPre(this)">
          <option value="0">Pago único</option>
          <?php if ($Valor !== 1 && $Producto !== 'Universidad'): ?>
            <option value="3">3 meses</option>
            <option value="6">6 meses</option>
            <option value="9">9 meses</option>
          <?php endif; ?>
          <?= $sel18 . $sel24 ?>
        </select>
        <?= $SelTipServ ?>
        <div class="Formulario">
          <button type="submit" name="BtnMetPago" class="main-button">Pagar</button>
        </div>
      </form>
    </div></div>
  </div>
</div>

<!-- Modal Ventana3 (resultado) -->
<div class="modal fade" id="Ventana3" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content"><div class="modal-body">
      <?= $COntVtan ?>
    </div></div>
  </div>
</div>

<!-- Sección de formulario principal, con imagen a la izquierda y formulario a la derecha -->
<section id="Formulario" class="container-fluid">
  <div class="row mh-100vh">
    <div class="col-md-6">
      <img src="assets/images/registro/familiaformulario.png" class="img-responsive" alt="Imagen formulario">
    </div>
    <div class="col-md-6 AreaTrabajo">
      <form method="POST" action="<?= $archivoRegistro ?>" <?php if (!isset($_GET['pro'])) echo 'onsubmit="validate(event,this)"'; ?>>
        <div class="logo"><img src="assets/images/kasu_logo.jpeg"></div>
        <h1 class="text-center">REGISTRO DE TU SERVICIO</h1>
        <div class="Formulario">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="Cupon" value="<?= htmlspecialchars($_SESSION['data'] ?? '', ENT_QUOTES) ?>">
          <input type="email" name="Mail" placeholder="Correo electrónico" required>
          <input type="tel"   name="Telefono" placeholder="Teléfono" required>
          <input type="text"  name="Direccion" placeholder="Dirección" required>
        </div>
        <div class="Botones">
          <?php if (isset($_GET['pro']) && $_GET['pro'] == 1): ?>
            <img src="assets/images/Index/funer.png" alt="Funerario">
            <div class="Formulario">
              <input type="text" disabled value="Funerario">
              <input type="hidden" name="Producto" value="Funerario">
            </div>
          <?php elseif (isset($_GET['pro']) && $_GET['pro'] == 2): ?>
            <img src="assets/images/Index/universitario.png" alt="Universidad">
            <div class="Formulario">
              <input type="text" disabled value="Universidad">
              <input type="hidden" name="Producto" value="Universidad">
            </div>
          <?php else: ?>
            <label class="only-one">
              <input type="checkbox" name="Producto" value="Funerario" onclick="selectServ('Funerario')">
              <img src="assets/images/Index/funer.png" alt="Funerario">
            </label>
            <label class="only-one">
              <input type="checkbox" name="Producto" value="Universidad" onclick="selectServ('Universidad')">
              <img src="assets/images/Index/universitario.png" alt="Universidad">
            </label>
            <div id="servicio" class="Formulario"></div>
          <?php endif; ?>
        </div>
        <div class="Formulario">
          <button type="submit" name="Registro" class="main-button">Continuar mi compra</button>
        </div>
        <div class="Ligas text-center">
          <a href="/">Regresar a KASU</a> |
          <a href="https://kasu.com.mx/terminos-y-condiciones.php">Términos y condiciones</a>
        </div>
      </form>
    </div>
  </div>
</section>

<script src="eia/javascript/AlPie.js"></script>
<script src="eia/javascript/finger.js"></script>
<script src="eia/javascript/localize.js"></script>
<script>
function selectServ(e) {
  if (e === "Funerario") {
    document.getElementById("servicio").innerHTML =
      '<input type="hidden" name="Producto" value="Funerario">';
  } else if (e === "Universidad") {
    document.getElementById("servicio").innerHTML =
      '<input type="hidden" name="Producto" value="Universidad">';
  } else {
    document.getElementById("servicio").innerHTML = '';
  }
}
</script>
</body>
</html>