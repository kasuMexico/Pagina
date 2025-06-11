<?php
// Iniciar sesión
session_start();

// Incluir el archivo de funciones
require_once '../eia/librerias.php';
$_SESSION["Vendedor"] = 'Jcarlos';
// Redirigir si el usuario ya tiene sesión activa
if (isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
    exit();
}

// Validar mensajes de error o confirmación
$messages = [
    1 => "Este correo ya ha registrado la contraseña, si requieres otra contraseña por favor ponte en contacto con tu supervisor.",
    2 => "Las contraseñas que registraste no coinciden.",
    3 => "Haz registrado exitosamente tu contraseña, si la olvidas solicita un nuevo correo a tu supervisor."
];

$alertMessage = "";
if (isset($_GET['Data']) && array_key_exists($_GET['Data'], $messages)) {
    $alertMessage = "<script>alert('" . htmlspecialchars($messages[$_GET['Data']], ENT_QUOTES, 'UTF-8') . "');</script>";
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KASU</title>
    
    <!-- Meta tags -->
    <meta name="theme-color" content="#2F3BA2">
    <meta name="description" content="Una aplicación para Vendedores">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="KASU Vendedores">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" href="/login/assets/img/kasu_logo.jpeg">
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    
    <!-- Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="/login/assets/css/styles.min.css">

    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/21478023ef.js" crossorigin="anonymous"></script>

    <?php echo $alertMessage; ?>
</head>

<body class="main">
    <header class="header">
        <div style="text-align:right; width:100%;">
            <button id="buttonAdd" aria-label="Install"><i class="fas fa-download" style="color:#01579b;"></i></button>
            <button onclick="window.location.reload()" id="butRefresh" aria-label="Refresh"><i class="fas fa-redo-alt" style="color:#01579b;"></i></button>
        </div>
    </header>

    <div class="login-clean">
        <?php if (!empty($_GET['data'])) : ?>
            <form method="POST" action="php/Funcionalidad_Empleados.php">
                <h1 class="sr-only">Login Form</h1>
                <div class="illustration">
                    <img alt="KASU Logo" src="/login/assets/img/logoKasu.png">
                </div>
                <!-- Registros de Gps y fingerprint -->
                <div id="Gps" style="display:none;"></div>
                <div id="FingerPrint" style="display:none;"></div>
                
                <!-- Inputs ocultos con sanitización -->
                <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="data" value="<?= htmlspecialchars($_GET['data'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="User" value="<?= htmlspecialchars($_GET['Usr'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <input class="form-control" type="password" name="PassWord1" placeholder="Contraseña" required>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="PassWord2" placeholder="Confirmar Contraseña" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="GenCont" value="Generar Contraseña" class="btn btn-primary btn-block">
                </div>
            </form>
        <?php else : ?>
            <form method="POST" action="php/Funcionalidad_Pwa.php">
                <h1 class="sr-only">Login Form</h1>
                <div class="illustration">
                    <img alt="KASU Logo" src="/login/assets/img/logoKasu.png">
                </div>
                
                <!-- Registros de Gps y fingerprint -->
                <div id="Gps" style="display:none;"></div>
                <div id="FingerPrint" style="display:none;"></div>

                <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <input class="form-control" type="text" name="Usuario" placeholder="Usuario" required>
                </div>
                <div class="form-group">
                    <input class="form-control" type="password" name="PassWord" placeholder="Contraseña" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="Login" value="Ingresar" class="btn btn-primary btn-block">
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('service-worker.js')
                    .then(reg => console.log('Service worker registrado.', reg))
                    .catch(err => console.error('Error al registrar el Service Worker:', err));
            });
        }
    </script>

    <!-- Scripts -->
    <script defer src="Javascript/install.js"></script>
    <script defer src="Javascript/refresh.js"></script>
    <script defer src="Javascript/finger.js"></script>
    <script defer src="Javascript/localize.js"></script>
</body>

</html>