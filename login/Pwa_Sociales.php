<?php
// Inicia sesión y librerías
session_start();
require_once '../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

// Validar sesión
if (!isset($_SESSION["Vendedor"])) {
  header('Location: https://kasu.com.mx/login');
  exit();
}

// Datos de usuario / comisiones
$Niv    = $basicas->BuscarCampos($mysqli,"Nivel","Empleados","IdUsuario",$_SESSION["Vendedor"]);
$PorCom = $basicas->BuscarCampos($mysqli,"N".$Niv,"Comision","Id",2);
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#F2F2F2">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
  <title>Post Sociales</title>

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
  <link rel="stylesheet" href="/login/assets/css/cupones.css">
</head>
<body onload="localize()">

  <!-- Barra superior fija -->
  <div class="topbar">
    <div class="d-flex align-items-center w-100">
      <h4 class="title m-0">Post Sociales</h4>
    </div>
  </div>

  <!-- Menú inferior fijo -->
  <section id="Menu">
    <?php require_once 'html/Menuprinc.php'; ?>
  </section>

  <!-- Contenido entre barras -->
  <main class="page-content">
    <div class="container">
      <!-- Bootstrap 4: sin g-4. Espaciado con mb-4 en columnas -->
      <div class="row">

        <?php
        /* ======= Cupones de Venta (6 items) ======= */
        $b = (int)$basicas->Max1Dat($mysqli,"Id","PostSociales","Tipo","Vta");
        for ($a=1; $a<=6; $a++) {
          $c = rand(1, max(1,$b));
          $sql = "SELECT * FROM PostSociales WHERE Id = '".$mysqli->real_escape_string($c)."' AND Status=1 AND Tipo='Vta' LIMIT 1";
          if ($res = $mysqli->query($sql)) {
            if ($Reg = $res->fetch_assoc()) {
              $ClArch  = $Reg['Id'].'|'.$_SESSION["Vendedor"];
              $DirPrin = ($Reg['Red']==="facebook")
                ? "http://www.facebook.com/sharer.php?u=https://kasu.com.mx/constructor.php?datafb="
                : "https://twitter.com/intent/tweet?text=".urlencode($Reg['DesA'])."&url=".urlencode('https://kasu.com.mx/constructor.php?datafb=');

              $ComGen = (float)$basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$Reg['Producto']);
              $Comis  = $ComGen * ((float)$PorCom/100.0);
              ?>
              <div class="col-12 col-md-6 mb-4"><!-- 1 por fila en móvil, 2 en >=md -->
                <div class="card h-100">
                  <div class="card-body">
                    <a class="ContCupon d-block mb-2" href="javascript:void(0);"
                       onclick="window.open('<?php echo $DirPrin.base64_encode($ClArch); ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                      <img class="img-fluid w-100" src="https://kasu.com.mx/assets/images/cupones/<?php echo htmlspecialchars($Reg['Img']); ?>" alt="">
                    </a>

                    <a class="BtnSocial d-inline-block mb-2" href="javascript:void(0);"
                       onclick="window.open('<?php echo $DirPrin.base64_encode($ClArch); ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                      <img src="/login/assets/img/sociales/<?php echo htmlspecialchars($Reg['Red']); ?>.png" alt="">
                    </a>

                    <div class="ContCupon">
                      <h2 class="h5">Com/Vta $<?php echo number_format($Comis, 2); ?></h2>
                      <h3 class="h6 mb-2"><?php echo htmlspecialchars($Reg['TitA']); ?></h3>
                      <p class="mb-0"><?php echo htmlspecialchars($Reg['DesA']); ?></p>
                    </div>
                  </div>
                </div>
              </div>
              <?php
            }
          }
        }

        /* ======= Artículos (4 items) ======= */
        $f = (int)$basicas->Max1Dat($mysqli,"Id","PostSociales","Tipo","Art");
        $desde = max($b+1, 1);
        for ($g=1; $g<=4; $g++) {
          $d = rand($desde, max($f,$desde));
          $sql = "SELECT * FROM PostSociales WHERE Id = '".$mysqli->real_escape_string($d)."' AND Status=1 AND Tipo='Art' LIMIT 1";
          if ($res = $mysqli->query($sql)) {
            if ($Reg = $res->fetch_assoc()) {
              $ClArch  = $Reg['Id'].'|'.$_SESSION["Vendedor"];
              $DirPrin = ($Reg['Red']==="facebook")
                ? "http://www.facebook.com/sharer.php?u=https://kasu.com.mx/constructor.php?datafb="
                : "https://twitter.com/intent/tweet?text=".urlencode($Reg['DesA'])."&url=".urlencode('https://kasu.com.mx/constructor.php?datafb=');

              $ComGen = (float)$basicas->BuscarCampos($mysqli,"comision","Productos","Producto",$Reg['Producto']);
              $Comis  = $ComGen * ((float)$PorCom/100.0);
              if ($Reg['Producto'] === "Universidad") {
                $Comis /= 2500;
              } elseif ($Reg['Producto'] === "Retiro") {
                $Comis /= 1000;
              } else {
                $Comis /= 100;
              }
              ?>
              <div class="col-12 col-md-6 mb-4">
                <div class="card h-100">
                  <div class="card-body">
                    <a class="ContCupon d-block mb-2" href="javascript:void(0);"
                       onclick="window.open('<?php echo $DirPrin.base64_encode($ClArch); ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                      <img class="img-fluid w-100" src="<?php echo htmlspecialchars($Reg['Img']); ?>" alt="">
                    </a>

                    <a class="BtnSocial d-inline-block mb-2" href="javascript:void(0);"
                       onclick="window.open('<?php echo $DirPrin.base64_encode($ClArch); ?>','ventanacompartir','toolbar=0,status=0,width=650,height=500');">
                      <img src="/login/assets/img/sociales/<?php echo htmlspecialchars($Reg['Red']); ?>.png" alt="">
                    </a>

                    <div class="ContCupon">
                      <h2 class="h5">Lectura $<?php echo number_format($Comis, 2); ?></h2>
                      <h3 class="h6 mb-2"><?php echo htmlspecialchars($Reg['TitA']); ?></h3>
                      <p class="mb-1"><?php echo htmlspecialchars($Reg['DesA']); ?></p>
                      <small class="text-muted">*Comisión por usuario único, por día de lectura</small>
                    </div>
                  </div>
                </div>
              </div>
              <?php
            }
          }
        }
        ?>

      </div><!-- /.row -->
      <br><br><br><br>
    </div><!-- /.container -->
  </main>

  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
  <script src="Javascript/finger.js"></script>
  <script src="Javascript/localize.js"></script>
</body>
</html>
