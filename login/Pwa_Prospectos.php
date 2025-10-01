<?php
session_start();
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// sesión
if (!isset($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit();
}

// ids / nivel (el nivel se usa en el menú)
$IdAsignacion = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);

// lanzadores de modales
$Lanzar = '';
if (!empty($_POST['CreaProsp'])) {                           // crear prospecto
    //Lanzador de ventanas
    $Lanzar = "#Ventana2";
} elseif (!empty($_POST['ArmaPres'])) {                      // armar presupuesto
    $venta = "SELECT * FROM prospectos WHERE Id='".intval($_POST['IdPros'])."' LIMIT 1";
    $res   = $pros->query($venta);
    if ($Reg = $res->fetch_assoc()) {}
    //Lanzador de ventanas
    $Lanzar = "#Ventana3";
} elseif (!empty($_POST['SelPros'])) {                       // ver prospecto
    $venta = "SELECT * FROM prospectos WHERE Id='".intval($_POST['IdProspecto'])."' LIMIT 1";
    $res   = $pros->query($venta);
    if ($Reg = $res->fetch_assoc()) {
      $Lanzar = "#Ventana1";
      $nomVd  = $basicas->BuscarCampos($mysqli,"Nombre","Empleados","IdUsuario",$_SESSION["Vendedor"]);
    }
} elseif(!empty($_POST['Cancelar'])){
    $venta = "SELECT * FROM prospectos WHERE Id='".intval($_POST['IdPros'])."' LIMIT 1";
    $res   = $pros->query($venta);
    if ($Reg = $res->fetch_assoc()) {}
    //Lanzador de ventanas
    $Lanzar = "#Ventana4";
} elseif(!empty($_POST['ConvDist'])){
    $venta = "SELECT * FROM prospectos WHERE Id='".intval($_POST['IdPros'])."' LIMIT 1";
    $res   = $pros->query($venta);
    if ($Reg = $res->fetch_assoc()) {}
    //Lanzador de ventanas
    $Lanzar = "#Ventana5";
} elseif (!empty($_POST['CancelaCte'])) { //Codigo que cancela el prospecto y registra el evento
    // Auditoría (GPS + fingerprint)
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Cancelar_Venta',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );
    //Mensaje de cancelacion
    $_GET['Msg'] = "Se ha cancelado el prospecto";
    //Cambio de Status de Propspecto
    $basicas->ActCampo($pros,"prospectos","Cancelacion",1,intval($_POST['IdVenta']));
}

// alert opcional
if (isset($_GET['Msg'])) {
  echo "<script>alert('".htmlspecialchars($_GET['Msg'])."');</script>";
}

// métrica (para mesa de control)
$Metodo = "Vtas";
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Cartera Prospectos</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-180x180.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo $VerCache; ?>">

  <!-- JS base -->
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>
<body onload="localize()">

  <!-- Top bar fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title">Prospectos Asignados</h4>

      <!-- botón crear prospecto -->
      <form class="BtnSocial m-0 ml-auto" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="Host" value="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="btnCrear" class="btn mb-0" title="Crear nuevo prospecto" style="background:#F7DC6F;color:#000;">
          <i class="material-icons">person_add</i>
        </label>
        <input id="btnCrear" type="submit" name="CreaProsp" hidden>
      </form>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- Modales -->
  <section class="VentanasEMergentes">
    <!-- Info prospecto -->
    <div class="modal fade" id="Ventana1" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?php echo isset($Reg['FullName'])?htmlspecialchars($Reg['FullName']):'Prospecto'; ?></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
          </div>
          <?php if (!empty($Reg)) : ?>
          <div class="modal-body">
            <p>Captado en</p>
            <h2><strong><?php echo htmlspecialchars($Reg['Origen']); ?></strong></h2>
            <p>Fecha Alta</p>
            <h2><strong><?php echo date("d-M-Y", strtotime($Reg['Alta'])); ?></strong></h2>
            <p>Producto</p>
            <h2><strong><?php echo htmlspecialchars($Reg['Servicio_Interes']); ?></strong></h2>
            <?php
              $Papeline = $basicas->Buscar2Campos($pros,"Nombre","Papeline","Pipeline",$Reg['Papeline'],"Nivel",$Reg['PosPapeline']);
              $MaxPape = $basicas->BuscarCampos($pros,"Maximo","Papeline","Pipeline",$Reg['Papeline']);
            ?>
            <p>Estatus en el proceso de venta</p>
            <h2><strong><?php echo htmlspecialchars($Reg['Papeline']." - ".$Papeline); ?></strong></h2>
            <p>Avance de la venta</p>
            <h2><strong><?php echo intval($Reg['PosPapeline'])." de ".intval($MaxPape); ?></strong></h2>
          </div>
          <div class="modal-footer">
            <a target="_blank" rel="noopener" class="btn btn-primary mr-2"
               href="https://api.whatsapp.com/send?phone=+52<?php echo urlencode($Reg['NoTel']); ?>&text=<?php echo urlencode('Hola mi nombre es '.$nomVd.' te contacto debido a que te interesaron nuestros productos de KASU'); ?>">
              Whatsapp
            </a>
            <? if($Reg['Servicio_Interes'] == "DISTRIBUIDOR"):?>
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0">
                <input type="hidden" name="IdVendedor" value="<?php echo isset($_POST['IdVendedor'])?htmlspecialchars($_POST['IdVendedor']):''; ?>">
                <input type="hidden" name="IdPros" value="<?php echo intval($Reg['Id']); ?>">
                <input type="submit" name="ConvDist" class="btn btn-success" value="Autorizar">
                <input type="submit" name="Cancelar" class="btn btn-danger" value="Cancelar">
              </form>
            <? else:?>
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0">
                <input type="hidden" name="IdVendedor" value="<?php echo isset($_POST['IdVendedor'])?htmlspecialchars($_POST['IdVendedor']):''; ?>">
                <input type="hidden" name="IdPros" value="<?php echo intval($Reg['Id']); ?>">
                <input type="submit" name="ArmaPres" class="btn btn-primary mr-2" value="Presupuesto">
                <input type="submit" name="Cancelar" class="btn btn-danger" value="Cancelar">
              </form>
            <? endif;?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Presupuesto -->
    <div class="modal fade" id="Ventana3" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Presupuesto de Venta</h5>
            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
          </div>
          <?php require_once 'html/Presupuesto.php'; ?>
        </div>
      </div>
    </div>

    <!-- Nuevo prospecto -->
    <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <?php require_once 'html/NvoProspecto.php'; ?>
        </div>
      </div>
    </div>
    
    <!-- Ventana4: Cancelar venta -->
    <div class="modal fade" id="Ventana4" tabindex="-1" role="dialog" aria-labelledby="modalV6" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php require 'html/CancelUsr.php'; ?>
        </div>
      </div>
    </div>
    
    <!-- Ventana5: Confirmar alta de Distribuidor -->
    <div class="modal fade" id="Ventana5" tabindex="-1" role="dialog" aria-labelledby="modalV6" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <?php if ($Lanzar === '#Ventana5') { require_once 'html/ActualizarDatos.php'; } ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Contenido -->
  <main class="page-content">
    <section class="container" style="width:99%;">
      <div class="form-group">
        <div class="table-responsive">
          <?php
          if (($Niv = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"])) >= 5) {
            $Vende = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);
            $Ventas = "SELECT * FROM prospectos WHERE Asignado='".$mysqli->real_escape_string($Vende)."' AND Cancelacion=0";
            if ($resultado = $pros->query($Ventas)) {
              while ($fila = $resultado->fetch_row()) {
                printf("
                  <form method='POST' action='%s'>
                    <input type='number' name='IdProspecto' value='%s' hidden>
                    <input type='text'   name='StatusVta'  value='%s' hidden>
                    <span class='new badge blue %s' style='position:relative;padding:0;width:100px;top:20px;'>%s</span>
                    <input type='submit' name='SelPros' class='%s' value='%s'>
                  </form>
                ",
                htmlspecialchars($_SERVER['PHP_SELF']),
                $fila[0], $fila[9], $fila[9], $fila[9], $fila[9], htmlspecialchars($fila[4]));
              }
            }
          } elseif ($Niv <= 4 && $Niv >= 2) {
            $IdSuc  = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
            $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
            $sqal   = "SELECT * FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}' AND Sucursal=".$mysqli->real_escape_string($IdSuc);
            if ($r = $mysqli->query($sqal)) {
              foreach ($r as $emp) {
                $Ventas = "SELECT * FROM prospectos WHERE Asignado='".$mysqli->real_escape_string($emp["Id"])."' AND Cancelacion=0";
                if ($resultado = $pros->query($Ventas)) {
                  while ($fila = $resultado->fetch_row()) {
                    printf("
                      <form method='POST' action='%s'>
                        <input type='number' name='IdProspecto' value='%s' hidden>
                        <input type='text'   name='StatusVta'  value='%s' hidden>
                        <input type='text'   name='IdVendedor' value='%s' hidden>
                        <span class='new badge blue %s' style='position:relative;padding:0;width:100px;top:20px;'>%s</span>
                        <input type='submit' name='SelPros' class='%s' value='%s - %s - %s'>
                      </form>
                    ",
                    htmlspecialchars($_SERVER['PHP_SELF']),
                    $fila[0], $fila[9], htmlspecialchars($emp["IdUsuario"]),
                    $fila[9], $fila[9], $fila[9],
                    htmlspecialchars($fila[4]), htmlspecialchars($emp["IdUsuario"]), htmlspecialchars($NomSuc));
                  }
                }
              }
            }
          } elseif ($Niv == 1) {
            $IdSuc  = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
            $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
            $sqal   = "SELECT Id,IdUsuario FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}'";
            if ($r = $mysqli->query($sqal)) {
              foreach ($r as $emp) {
                $Ventas = "SELECT * FROM prospectos WHERE Asignado='".$mysqli->real_escape_string($emp["Id"])."' AND Cancelacion=0";
                if ($resultado = $pros->query($Ventas)) {
                  while ($fila = $resultado->fetch_row()) {
                    printf("
                      <form method='POST' action='%s'>
                        <input type='number' name='IdProspecto' value='%s' hidden>
                        <input type='text'   name='StatusVta'  value='%s' hidden>
                        <input type='text'   name='IdVendedor' value='%s' hidden>
                        <span class='new badge blue %s' style='position:relative;padding:0;width:100px;top:20px;'>%s</span>
                        <input type='submit' name='SelPros' class='%s' value='%s - %s - %s'>
                      </form>
                    ",
                    htmlspecialchars($_SERVER['PHP_SELF']),
                    $fila[0], $fila[9], htmlspecialchars($emp["IdUsuario"]),
                    $fila[9], $fila[9], $fila[9],
                    htmlspecialchars($fila[4]), htmlspecialchars($emp["IdUsuario"]), htmlspecialchars($NomSuc));
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

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/fingerprint-core-y-utils.js"></script>
  <script src="Javascript/finger.js"></script>
  <script src="Javascript/localize.js"></script>

  <!-- abrir modal si toca -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Lanzar)) : ?>
      $('<?php echo $Lanzar; ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>