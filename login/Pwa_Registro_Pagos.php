<?php
session_start();
setlocale(LC_ALL, 'es_ES');
require_once '../eia/librerias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Sesión
if (!isset($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit();
}

// Datos de nivel/empleado
$Niv   = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
$IdVen = $basicas->BuscarCampos($mysqli,"Id","Empleados","IdUsuario",$_SESSION["Vendedor"]);

// Lógica de selección (abre modal de pago)
$Ventana = '';
if (!empty($_POST['SelCte'])) {
  $venta = "SELECT * FROM Venta WHERE Id='".intval($_POST['IdVenta'])."'";
  $res   = $mysqli->query($venta);
  if ($Reg = $res->fetch_assoc()) {
    $Pago      = $financieras->Pago($mysqli, (int)$_POST['IdVenta']);
    $PagoPend  = $financieras->PagosPend($mysqli, (int)$_POST['IdVenta']);
    $FecProm   = $basicas->BuscarCampos($mysqli,"FechaReg","PromesaPago","id",intval($_POST["Referencia"]));
    $CantProm  = $basicas->BuscarCampos($mysqli,"Pago","PromesaPago","id",intval($_POST["Referencia"]));
    $GpsSql    = "SELECT * FROM gps WHERE Id='".intval($Reg['Idgps'])."'";
    if ($resGps = $mysqli->query($GpsSql)) {
      if ($RegGps = $resGps->fetch_assoc()) {
        $Gps = "geo:".$RegGps['Latitud'].",".$RegGps['Longitud'].";u=".$RegGps['Presicion'];
      }
    }
  }
  $Ventana = '#Ventana2';
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Registros de Pagos</title>

  <!-- PWA / iOS -->
  <link rel="manifest" href="/login/manifest.webmanifest">
  <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Material+Icons">
  <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?php echo $VerCache; ?>">
  <link rel="stylesheet" href="assets/css/Grafica.css">
  <link rel="stylesheet" href="/login/assets/css/cupones.css">

  <!-- JS base (evitar duplicados) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

  <!-- Otros -->
  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>
<body onload="localize()">

  <!-- Topbar fija -->
  <div class="topbar">
    <div class="d-flex align-items-center">
      <h4 class="title">Pagos y Promesas de Pago</h4>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- Modal registrar pago -->
  <div class="modal fade" id="Ventana2" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <?php require 'html/EmPago.php'; ?>
      </div>
    </div>
  </div>

  <!-- Contenido que hace scroll entre barras -->
  <main class="page-content">
    <section class="container" style="width:99%;">
      <form method="post">
        <div class="form-group">
          <div class="table-responsive">
            <?php
            if ($Niv >= 5) {
              // Periodos (2 semanas + futuros del periodo)
              $df = 1; $Ca = 3;
              while ($df <= $Ca) {
                if ($df == 1) { $cd = '+ 7';  $Ya = '- 1'; }
                elseif ($df == 2){ $cd = '+ 15'; $Ya = '+ 8'; }
                else {
                  echo '
                  <section class="container" style="width:99%;">
                    <div class="form-group">
                      <div class="row align-items-center">
                        <div class="col text-center">
                          <h4 class="mb-0">Pagos del Periodo</h4>
                          <hr style="margin:auto;">
                        </div>
                      </div>
                    </div>
                  </section>';
                  $cd = '+ 2'; $Ya = '- 1';
                }
                $limiSup = date("Y-m-d", strtotime("$Ya days"));
                $limiInf = date("Y-m-d", strtotime("$cd days"));
                echo "<p>Semana del ".strftime("%d", strtotime($limiSup))." al ".strftime("%d de %B", strtotime($limiInf))."</p>";

                if ($df == 3) {
                  $limA   = date("Y-m-d", strtotime($limiSup."-14 days"));
                  $limB   = date("Y-m-d", strtotime($limiInf."-14 days"));
                  $Ventas = "SELECT * FROM Pagos WHERE Usuario='".$_SESSION["Vendedor"]."' AND FechaRegistro>='{$limA}' AND FechaRegistro<='{$limB}'";
                } else {
                  $Ventas = "SELECT * FROM PromesaPago WHERE User='".$_SESSION["Vendedor"]."' AND FechaPago>='{$limiSup}' AND FechaPago<='{$limiInf}'";
                }

                if ($resultado = $mysqli->query($Ventas)) {
                  while ($fila = $resultado->fetch_row()) {
                    $RefPag = $basicas->BuscarCampos($mysqli,"Id","Pagos","Referencia",$fila[0]);
                    if (empty($RefPag)) {
                      $Venta = "SELECT * FROM Venta WHERE Id='".$fila[1]."' AND Usuario='".$_SESSION["Vendedor"]."'";
                      $Fdp   = strftime("%d de %B", strtotime($fila[2]));
                      if ($df == 3) {
                        $Fdp    = strftime("%d de %B", strtotime($fila[13]."+14 days"));
                        $SuPagT = $basicas->SumarFechas($mysqli,"Cantidad","Pagos","IdVenta",$fila[1],'FechaRegistro',$limiInf,'FechaRegistro',$limiSup);
                        $pago   = $financieras->Pago($mysqli, $fila[1]);
                        $dj     = $pago - $SuPagT;
                        if ($dj <= 0) { $Venta = NULL; }
                      }
                      if ($Venta) {
                        $S62 = $mysqli->query($Venta);
                        $S63 = $S62->fetch_row();
                        printf("
                          <form method='POST' action='%s'>
                            <input type='number' name='IdVenta'     value='%s' hidden>
                            <input type='number' name='Referencia'  value='%s' hidden>
                            <input type='number' name='Promesa'     value='%s' hidden>
                            <span class='new badge blue %s' data-badge-caption='' style='position:relative;padding:0;width:100px;top:10px;'>%s</span>
                            <input type='submit' name='SelCte' class='%s' value='%s - %s'>
                          </form>
                        ",
                        htmlspecialchars($_SERVER['PHP_SELF']),
                        $S63[0], $fila[0], $fila[3],
                        $S63[10], $S63[10], $S63[10],
                        $Fdp, htmlspecialchars($S63[3]));
                      }
                    }
                  }
                }
                $df++;
              }
            } elseif ($Niv <= 4) {
              $IdSuc  = $basicas->BuscarCampos($mysqli,"Sucursal","Empleados","IdUsuario",$_SESSION["Vendedor"]);
              $NomSuc = $basicas->BuscarCampos($mysqli,"NombreSucursal","Sucursal","Id",$IdSuc);
              $sqal   = "SELECT * FROM Empleados WHERE Nombre!='Vacante' AND Nivel>='{$Niv}' AND Sucursal=".$mysqli->real_escape_string($IdSuc);
              if ($r4e9s = $mysqli->query($sqal)) {
                foreach ($r4e9s as $Resd5){
                  $df = 1; $Ca = 3;
                  while ($df <= $Ca) {
                    if ($df == 1) { $cd = '+ 7';  $Ya = '- 1'; }
                    elseif ($df == 2){ $cd = '+ 15'; $Ya = '+ 8'; }
                    else { $cd = '+ 2'; $Ya = '- 1'; }
                    $limiSup = date("Y-m-d", strtotime("$Ya days"));
                    $limiInf = date("Y-m-d", strtotime("$cd days"));

                    if ($df == 3) {
                      $limA   = date("Y-m-d", strtotime($limiSup."-14 days"));
                      $limB   = date("Y-m-d", strtotime($limiInf."-14 days"));
                      $Ventas = "SELECT * FROM Pagos WHERE Usuario='".$Resd5["IdUsuario"]."' AND FechaRegistro>='{$limA}' AND FechaRegistro<='{$limB}'";
                    } else {
                      $Ventas = "SELECT * FROM PromesaPago WHERE User='".$Resd5["IdUsuario"]."' AND FechaPago>='{$limiSup}' AND FechaPago<='{$limiInf}'";
                    }

                    if ($resultado = $mysqli->query($Ventas)) {
                      while ($fila = $resultado->fetch_row()) {
                        $RefPag = $basicas->BuscarCampos($mysqli,"Id","Pagos","Referencia",$fila[0]);
                        if (empty($RefPag)) {
                          $Venta = "SELECT * FROM Venta WHERE Id='".$fila[1]."' AND Usuario='".$Resd5["IdUsuario"]."'";
                          $Fdp   = strftime("%d de %B", strtotime($fila[2]));
                          if ($df == 3) {
                            $Fdp    = strftime("%d de %B", strtotime($fila[13]."+14 days"));
                            $SuPagT = $basicas->SumarFechas($mysqli,"Cantidad","Pagos","IdVenta",$fila[1],'FechaRegistro',$limiInf,'FechaRegistro',$limiSup);
                            $pago   = $financieras->Pago($mysqli, $fila[1]);
                            $dj     = $pago - $SuPagT;
                            if ($dj <= 0) { $Venta = NULL; }
                          }
                          if ($Venta) {
                            $S62 = $mysqli->query($Venta);
                            $S63 = $S62->fetch_row();
                            printf("
                              <form method='POST' action='%s'>
                                <input type='number' name='IdVenta'     value='%s' hidden>
                                <input type='number' name='Referencia'  value='%s' hidden>
                                <input type='number' name='Promesa'     value='%s' hidden>
                                <input type='text'   name='IdVendedor'  value='%s' hidden>
                                <span class='new badge blue %s' data-badge-caption='' style='position:relative;padding:0;width:100px;top:10px;'>%s</span>
                                <input type='submit' name='SelCte' class='%s' value='%s - %s - %s'>
                              </form>
                            ",
                            htmlspecialchars($_SERVER['PHP_SELF']),
                            $S63[0], $fila[0], $fila[3], htmlspecialchars($Resd5["IdUsuario"]),
                            $S63[10], $S63[10], $S63[10],
                            $Fdp, htmlspecialchars($Resd5["IdUsuario"]), htmlspecialchars($NomSuc));
                          }
                        }
                      }
                    }
                    $df++;
                  }
                }
              }
            }
            ?>
          </div>
        </div>
      </form>
      <br><br><br><br>
    </section>
  </main>

  <!-- Helpers de tu app -->
  <script src="Javascript/fingerprint-core-y-utils.js"></script>
  <script src="Javascript/finger.js"></script>
  <script src="Javascript/localize.js"></script>

  <!-- Abrir modal si corresponde -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if (!empty($Ventana)) : ?>
      $('<?php echo $Ventana; ?>').modal('show');
      <?php endif; ?>
    });
  </script>
</body>
</html>