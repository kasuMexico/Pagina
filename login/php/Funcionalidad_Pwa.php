<?php
// Iniciar la sesión
session_start();

// Incluir el archivo de librerías que contiene las definiciones de las clases (Basicas, Seguridad, Financieras, etc.) y las conexiones a la base de datos
require_once '../eia/librerias.php';

// Si no existe la variable de sesión "Vendedor", redirigir a la página de login
if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/');
    exit();
}
// Crear una instancia de la clase Basicas (para usar sus métodos no estáticos, por ejemplo, para trackUsage)
$basicas = new Basicas();

// Incluir el archivo que realiza el análisis de las metas de venta y colocación
require_once 'php/Analisis_Metas.php';

// Obtener el identificador del vendedor de la sesión
$Vend = $_SESSION["Vendedor"];

// Definir la ruta de la carpeta de imágenes de perfil
$ruta = "assets/img/perfil";
// Valor predeterminado para la extensión (en caso de no encontrarse)
$file_ext = "jpg";

// Verificar si la ruta es un directorio y, de ser así, buscar el archivo que corresponda al vendedor
if (is_dir($ruta)) {
    if ($gestor = opendir($ruta)) {
        while (($archivo = readdir($gestor)) !== false) {
            // Solo se consideran archivos y no directorios
            if (is_file($ruta . "/" . $archivo)) {
                $partes = explode('.', $archivo);
                // Si el nombre (la parte antes del punto) coincide con el identificador del vendedor
                if (count($partes) >= 2 && $partes[0] === $Vend) {
                    $file_ext = end($partes);
                    break;
                }
            }
        }
        closedir($gestor);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <title>Inicio</title>
    <meta name="theme-color" content="#2F3BA2" />
    <link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <link rel="stylesheet" href="assets/css/styles.min.css">
    <link rel="stylesheet" href="assets/css/Grafica.css">
    <!-- Función que genera la gráfica de cartera -->
    <script src="Javascript/GenGrafica.js"></script>
</head>
<body>
    <!-- Inicio del menú principal fijo -->
    <section id="Menu">
        <?php require_once 'html/Menuprinc.php'; ?>
    </section>

    <!-- Sección de datos del usuario -->
    <div class="principal">
        <div class="row" style="display: flex;">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <img alt="logo" class="img-fluid" style="padding-left: 10px;" src="/login/assets/img/logoKasu.png">
            <div style="transform: translate(0, 25px);">
                <p style="transform: scaleY(2);"><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Protege a Quien Amas</strong></p>
            </div>
        </div>
        <hr>
        <div class="dpersonales">
            <div class="imgPerfil">
                <!-- Se muestra la imagen de perfil del vendedor -->
                <img alt="perfil" class="img-thumbnail" src="/login/assets/img/perfil/<?php echo htmlspecialchars($Vend) . '.' . htmlspecialchars($file_ext); ?>" alt="Carga tu foto de perfil">
            </div>
            <div class="Nombre">
                <?php
                // Recuperar el nombre, el nivel y la sucursal del vendedor usando los métodos de la instancia $basicas
                $SL1     = $basicas->BuscarCampos($mysqli, "Nombre", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                $nivelId = $basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                $suc     = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
                $su2     = $basicas->BuscarCampos($mysqli, "NombreSucursal", "Sucursal", "Id", $suc);
                $SL2     = $basicas->BuscarCampos($mysqli, "NombreNivel", "Nivel", "Id", $nivelId);

                // Imprimir la información de forma segura
                echo "<p>" . htmlspecialchars($SL1) . "</p>";
                echo "<p>" . htmlspecialchars($SL2) . " - " . htmlspecialchars($su2) . "</p>";
                ?>
            </div>
        </div>

        <!-- Contenido adicional debajo de la información personal -->
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="Grafica" id="chart_container"></div>
                </div>
                <div class="col-md-6">
                    <?php
                    // Se muestra información en función del nivel del usuario (por ejemplo, información de comisiones o metas)
                    if ($Niv == 7) {
                        echo '
                        <div class="col-md-12">
                            <p>Comisiones Acumuladas</p>
                            <h3 style="color:' . htmlspecialchars($spv) . ';">' . money_format('%.2n', $ComGenHoy) . '</h3>
                        </div>
                        ';
                    } else {
                        echo '
                        <div class="col-md-12">
                            <p>Normalidad Mensual</p>
                            <a href="Pwa_Clientes.php"><h3 style="color:' . htmlspecialchars($spv) . ';">' . round($AvCob) . ' %</h3></a>
                        </div>
                        ';
                    }
                    echo '<div class="row">';
                    if ($Niv != 7) {
                        echo '
                        <div class="col-md-6">
                            <hr>
                            <p><strong>Meta de Cobranza</strong></p>
                            <h3>' . money_format('%.2n', $MetaCob) . '</h3>
                            <p>Avance de Cobranza</p>
                            <a href="Pwa_Registro_Pagos.php"><h3 style="color:' . htmlspecialchars($spv) . ';">' . money_format('%.2n', $CobHoy) . '</h3></a>
                        </div>
                        ';
                    }
                    if ($Niv != 5) {
                        echo '
                        <div class="col-md-6">
                            <hr>
                            <p><strong>Meta de Venta</strong></p>
                            <h3>' . money_format('%.2n', $MetaVta) . '</h3>
                            <p>Avance de Venta</p>
                            <a href="registro.php"><h3 style="color:' . htmlspecialchars($bxo) . ';">' . round($AvVtas) . ' %</h3></a>
                        </div>
                        ';
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br><br><br>
    <!-- Scripts -->
    <script defer src="Javascript/finger.js"></script>
    <script defer src="Javascript/localize.js"></script>
</body>
</html>
