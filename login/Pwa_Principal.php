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

// Incluir el archivo que realiza el análisis de metas (se supone que define variables globales usadas más adelante)
require_once 'php/Analisis_Metas.php';

// --------------------- INSERCIÓN DE LA IMAGEN DE PERFIL DEL USUARIO ------------------------

// Se obtiene el identificador (nombre) del vendedor
$Vend = $_SESSION["Vendedor"];
//echo "DEBUG: ID del vendedor = " . htmlspecialchars($Vend) . "<br>";

// Definir la ruta donde se encuentran las imágenes de perfil
$ruta = "assets/img/perfil/";
// Establecer un valor por defecto para la extensión, en caso de no encontrar imagen
$file_ext = 'default.jpg';

// Usar glob() para buscar archivos cuyo nombre comience con el ID del vendedor
$pattern = $ruta . $Vend . ".*";
$files = glob($pattern);
if ($files && count($files) > 0) {
    // Se toma el primer archivo encontrado
    $profileFile = $files[0];
    $file_ext = pathinfo($profileFile, PATHINFO_EXTENSION);
    //echo "DEBUG: Se encontró imagen de perfil: " . htmlspecialchars($profileFile) . "<br>";
} else {
    //echo "DEBUG: No se encontró imagen para el vendedor. Se usará imagen por defecto.<br>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <!-- Evitar el escalado para mantener el diseño -->
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <title>Inicio</title>
    <meta name="theme-color" content="#2F3BA2" />
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <!-- CSS desde CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Librerías para gráficos y jQuery -->
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <!-- CSS locales -->
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="assets/css/Grafica.css">
    <!-- Script para generar la gráfica de cartera -->
    <script src="Javascript/GenGrafica.js"></script>
</head>
<body>
    <!-- Menú principal fijo -->
    <section id="Menu">
        <?php require_once 'html/Menuprinc.php'; ?>
    </section>
    
    <!-- Contenedor principal -->
    <div class="principal">
        <!-- Encabezado: logo y eslogan -->
        <div class="row" style="display:flex; align-items: center;">
            <img alt="logo" class="img-fluid" style="padding-left: 10px;" src="/login/assets/img/logoKasu.png">
            <div style="transform: translate(0, 25px);">
                <p style="transform: scaleY(2);">
                    <strong>Protege a Quien Amas</strong>
                </p>
            </div>
        </div>
        <hr>

        <!-- Sección de información del perfil del usuario -->
        <div class="dpersonales">
            <div class="imgPerfil">
                <!-- Mostrar la imagen de perfil del vendedor -->
                <img alt="perfil" class="img-thumbnail" src="/login/assets/img/perfil/<?php echo htmlspecialchars($Vend . "." . $file_ext); ?>" alt="Carga tu foto de perfil">
            </div>
            <div class="Nombre">
                <?php
                // Obtener datos del vendedor usando el objeto $basicas
                $SL1 = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                $nivel = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                $suc = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                $su2 = $basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $suc);
                $nombreNivel = $basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $nivel);

                // Mostrar información para depuración
                //echo "DEBUG: Nombre del vendedor = " . htmlspecialchars($SL1) . "<br>";
                //echo "DEBUG: Nivel = " . htmlspecialchars($nombreNivel) . " - Sucursal = " . htmlspecialchars($su2) . "<br>";
                ?>
                <p><?php echo htmlspecialchars($SL1); ?></p>
                <p><?php echo htmlspecialchars($nombreNivel . " - " . $su2); ?></p>
            </div>
        </div>

        <!-- Sección de contenido adicional: gráfica y datos de metas -->
        <div class="container">
            <div class="row">
                <!-- Columna para la gráfica -->
                <div class="col-md-6">
                    <div class="Grafica" id="chart_container"></div>
                </div>
                <!-- Columna para la información de metas y avances -->
                <div class="col-md-6">
                    <?php
                    // Se asume que las variables de análisis de metas ($Niv, $spv, $ComGenHoy, $MetaCob, $CobHoy, $MetaVta, $AvCob, $AvVtas) se definieron en 'Analisis_Metas.php'
                    if ($Niv == 7) {
                        echo '
                        <div class="col-md-12">
                            <p>Comisiones Acumuladas</p>
                            <h3 style="color: ' . htmlspecialchars($spv) . ';">$' . number_format($ComGenHoy, 2) . '</h3>
                        </div>
                        ';
                    } else {
                        echo '
                        <div class="col-md-12">
                            <p>Normalidad Mensual</p>
                            <a href="Pwa_Clientes.php"><h3 style="color: ' . htmlspecialchars($spv) . ';">' . round($AvCob) . ' %</h3></a>
                        </div>
                        ';
                    }
                    echo '<div class="row">';
                    if ($Niv != 7) {
                        // Bloque de información de cobranza
                        echo '
                        <div class="col-md-6">
                            <hr>
                            <p><strong>Meta de Cobranza</strong></p>
                            <h3>$' . number_format($MetaCob, 2) . '</h3>
                            <p>Avance de Cobranza</p>
                            <a href="Pwa_Registro_Pagos.php"><h3 style="color: ' . htmlspecialchars($spv) . ';">$' . number_format($CobHoy, 2) . '</h3></a>
                        </div>
                        ';
                    }
                    if ($Niv != 5) {
                        // Bloque de información de ventas
                        echo '
                        <div class="col-md-6">
                            <hr>
                            <p><strong>Meta de Venta</strong></p>
                            <h3>$' . number_format($MetaVta, 2) . '</h3>
                            <p>Avance de Venta</p>
                            <a href="registro.php"><h3 style="color: ' . htmlspecialchars($bxo) . ';">' . round($AvVtas) . ' %</h3></a>
                        </div>
                        ';
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inclusión de scripts: fingerprint y localización -->
    <script defer src="Javascript/finger.js" async></script>
    <script defer src="Javascript/localize.js"></script>
</body>
</html>
