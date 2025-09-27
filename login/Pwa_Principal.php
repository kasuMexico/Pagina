<?php
// Iniciar la sesión y cargar las librerías necesarias
session_start();
require_once '../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

// Verificar que la sesión del vendedor existe; de lo contrario, redirigir al login
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/');
    exit();
}

// Crear una instancia de la clase Basicas (si las funciones no son estáticas)
$basicas = new Basicas();

// Incluir el archivo que realiza el análisis de metas
require_once 'php/Analisis_Metas.php';

// --------------------- INSERCIÓN DE LA IMAGEN DE PERFIL DEL USUARIO ------------------------
$Vend     = $_SESSION["Vendedor"];
$ruta     = "assets/img/perfil/";
$file_ext = 'default.jpg';

// Buscar archivo de perfil por patrón {IdVendedor}.*
$pattern = $ruta . $Vend . ".*";
$files   = glob($pattern);
if ($files && count($files) > 0) {
    $profileFile = $files[0];
    $file_ext    = pathinfo($profileFile, PATHINFO_EXTENSION);
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#F2F2F2">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <title>KASU</title>

    <!-- Manifest / iOS -->
    <link rel="manifest" href="/login/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- CSS -->
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?echo $VerCache;?>">
    <link rel="stylesheet" href="assets/css/Grafica.css">

    <!-- JS externos -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

    <!-- Gráfica -->
    <script src="Javascript/GenGrafica.js"></script>
</head>
<body>
    <!-- Menú principal fijo -->
    <section id="Menu">
        <?php require_once 'html/Menuprinc.php'; ?>
    </section>

    <!-- Contenedor principal -->
    <div class="principal">
        <div class="d-flex align-items-center py-2 pe-3">
            <h4 class="flex-grow-1 text-center mb-0">
                <img alt="logo" class="img-fluid" style="padding-left:10px;" src="/login/assets/img/logoKasu.png">
            </h4>
        </div>
        <hr>
    </div>

    <!-- Información del perfil -->
    <div class="dpersonales">
        <div class="imgPerfil">
            <img
                class="img-thumbnail"
                alt="perfil"
                src="/login/assets/img/perfil/<?php echo htmlspecialchars($Vend . "." . $file_ext); ?>"
            >
        </div>
        <div class="Nombre">
            <?php
            $SL1         = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
            $nivel       = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
            $suc         = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
            $su2         = $basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $suc);
            $nombreNivel = $basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $nivel);
            ?>
            <p><?php echo htmlspecialchars($SL1); ?></p>
            <p><?php echo htmlspecialchars($nombreNivel . " - " . $su2); ?></p>
        </div>
        <div class="flex-grow-1 text-center mb-0">
            <button class="btn btn-success" id="btnInstall">Instalar KASU</button>
        </div>
    </div>

    <!-- Gráfica y metas -->
    <div class="container">
        <div class="row">

            <!-- Gráfica -->
            <div class="col-md-6">
                <div class="Grafica" id="chart_container"></div>
            </div>

            <!-- Metas -->
            <div class="col-md-6">
                <?php
                if ($Niv == 7) {
                    echo '
                    <div class="col-md-12">
                        <p>Comisiones Acumuladas</p>
                        <h3 style="color:' . htmlspecialchars($spv) . ';">$' . number_format($ComGenHoy, 2) . '</h3>
                    </div>';
                } else {
                    echo '
                    <div class="col-md-12">
                        <p>Normalidad Mensual</p>
                        <a href="Pwa_Clientes.php">
                            <h3 style="color:' . htmlspecialchars($spv) . ';">' . round($AvCob) . ' %</h3>
                        </a>
                    </div>';
                }

                echo '<div class="row">';

                if ($Niv != 7) {
                    echo '
                    <div class="col-md-6">
                        <hr>
                        <p><strong>Meta de Cobranza</strong></p>
                        <h3>$' . number_format($MetaCob, 2) . '</h3>
                        <p>Avance de Cobranza</p>
                        <a href="Pwa_Registro_Pagos.php">
                            <h3 style="color:' . htmlspecialchars($spv) . ';">$' . number_format($CobHoy, 2) . '</h3>
                        </a>
                    </div>';
                }

                if ($Niv != 5) {
                    echo '
                    <div class="col-md-6">
                        <hr>
                        <p><strong>Meta de Venta</strong></p>
                        <h3>$' . number_format($MetaVta, 2) . '</h3>
                        <p>Avance de Venta</p>
                        <a href="registro.php">
                            <h3 style="color:' . htmlspecialchars($bxo) . ';">' . round($AvVtas) . ' %</h3>
                        </a>
                    </div>';
                }
                ?>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts PWA -->
    <script defer src="Javascript/finger.js" async></script>
    <script defer src="Javascript/localize.js"></script>
    <script defer src="Javascript/install.js"></script>

    <!-- Registro SW -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/login/service-worker.js', { scope: '/login/' });
        }
    </script>

</body>
</html>
