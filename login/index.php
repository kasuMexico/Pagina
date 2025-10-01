<?php
    // cookies seguras antes de session_start
    session_set_cookie_params([
    'lifetime'=>0,
    'path'=>'/',
    'domain'=>'kasu.com.mx',
    'secure'=>true,
    'httponly'=>true,
    'samesite'=>'Lax'
    ]);

    session_start();
    require_once __DIR__.'/../eia/librerias.php';

    // si ya hay sesión, entra directo
    if (!empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login/Pwa_Principal.php'); exit;
    }

    // token CSRF para formularios
    $_SESSION['csrf_auth'] = $_SESSION['csrf_auth'] ?? bin2hex(random_bytes(32));

    // selector de vista
    $action = $_GET['action'] ?? '';          // '', 'cp' (cambiar pass)
    $data   = filter_input(INPUT_GET,'data',FILTER_VALIDATE_INT);
    $usr    = filter_input(INPUT_GET,'Usr',FILTER_SANITIZE_SPECIAL_CHARS);

    // mensajes
    $messages = [
    1=>"Este correo ya registró contraseña. Solicita otro enlace a tu supervisor.",
    2=>"Las contraseñas no coinciden.",
    3=>"Contraseña registrada correctamente.",
    4=>"Usuario o contraseña incorrectos.",
    5=>"Tu contraseña actual es incorrecta.",
    6=>"Contraseña actualizada correctamente."
    ];
?>
<!doctype html><html lang="es-MX"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>KASU | Ventas</title>
<link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?echo $VerCache;?>">
<style>
html,body{ height:100%; background:#fff; }
body, .main{ min-height:100dvh; background:#fff !important; }
.login-clean{ background:#fff !important; }
.login-clean form{
  background:#fff !important;
  box-shadow:none !important; /* quita “tarjeta gris” */
  border:0 !important;
}
</style>
<meta name="theme-color" content="#ffffff">

</head>
<body>
    <div class="login-clean">
    <div class="illustration"><img alt="KASU" src="/login/assets/img/logoKasu.png"></div>

    <?php if (!empty($data) && $messages === 8): ?>
    <!-- ACTIVAR/REGISTRAR CONTRASEÑA vía enlace -->
    <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
        <input type="hidden" name="csrf"  value="<?= $_SESSION['csrf_auth'] ?>">
        <input type="hidden" name="Host"  value="<?= htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES) ?>">
        <input type="hidden" name="data"  value="<?= (int)$data ?>">
        <input type="hidden" name="User"  value="<?= htmlspecialchars($usr ?? '',ENT_QUOTES) ?>">
        <div class="form-group"><input class="form-control" type="password" name="PassWord1" placeholder="Nueva contraseña" required autocomplete="new-password"></div>
        <div class="form-group"><input class="form-control" type="password" name="PassWord2" placeholder="Confirmar contraseña" required autocomplete="new-password"></div>
        <div class="form-group"><button class="btn btn-primary btn-block" name="GenCont" value="1">Guardar contraseña</button></div>
        <div class="text-center"><a href="/login/index.php">Volver a iniciar sesión</a></div>
    </form>

    <?php elseif ($action === 'cp'): ?>
    <!-- CAMBIAR CONTRASEÑA con usuario + pass actual -->
    <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
        <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES) ?>">
        <div class="form-group"><input class="form-control" type="text" name="Usuario" placeholder="Usuario" required autocomplete="username"></div>
        <div class="form-group"><input class="form-control" type="password" name="PassAct" placeholder="Contraseña actual" required autocomplete="current-password"></div>
        <div class="form-group"><input class="form-control" type="password" name="PassWord1" placeholder="Nueva contraseña" required autocomplete="new-password"></div>
        <div class="form-group"><input class="form-control" type="password" name="PassWord2" placeholder="Confirmar nueva contraseña" required autocomplete="new-password"></div>
        <div class="form-group"><button class="btn btn-primary btn-block" name="CambiarPass" value="1">Cambiar contraseña</button></div>
        <div class="text-center"><a href="/login">Volver a iniciar sesión</a></div>
    </form>

    <?php else: ?>
    <!-- INGRESAR AL SISTEMA -->
    <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
        <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES) ?>">
        <div class="form-group"><input class="form-control" type="text" name="Usuario" placeholder="Usuario" required autocomplete="username"></div>
        <div class="form-group"><input class="form-control" type="password" name="PassWord" placeholder="Contraseña" required autocomplete="current-password"></div>
        <div class="form-group"><button class="btn btn-primary btn-block" name="Login" value="1">Ingresar</button></div>
        <div class="text-center">
            <a href="<?= htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES) ?>?action=cp">Cambiar mi contraseña</a>
        </div>
    </form>
    <?php endif; ?>

    <?php if (!empty($data) && isset($messages[$data])): ?>
        <div class="alert alert-info mt-3" role="alert"><?= htmlspecialchars($messages[$data],ENT_QUOTES) ?></div>
    <?php endif; ?>
</div>
</body>
</html>
