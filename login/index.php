<?php
/********************************************************************************************
 * Qué hace: Renderiza la pantalla de acceso KASU (iniciar sesión / activar contraseña / cambiar contraseña)
 *           con CSRF, cookies seguras y rutas a Funcionalidad_Empleados.php.
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Cookies seguras de sesión
 * Qué hace: Configura parámetros de cookie para la sesión (HTTPS, HttpOnly, SameSite=Lax)
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => 'kasu.com.mx',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

/* ==========================================================================================
 * BLOQUE: Inicio de sesión y dependencias
 * Qué hace: Inicia la sesión y carga librerías del proyecto
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
session_start();
require_once __DIR__ . '/../eia/librerias.php';

/* ==========================================================================================
 * BLOQUE: Acceso directo si ya hay sesión
 * Qué hace: Si existe $_SESSION['Vendedor'], redirige al panel principal PWA
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
if (!empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
    exit;
}

/* ==========================================================================================
 * BLOQUE: Token CSRF
 * Qué hace: Genera y persiste token CSRF para formularios de autenticación
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
if (empty($_SESSION['csrf_auth'])) {
    $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}

/* ==========================================================================================
 * BLOQUE: Selector de vista
 * Qué hace: Lee parámetros GET para decidir qué formulario mostrar
 * - action: '' = login, 'cp' = cambiar contraseña
 * - data:   int opcional; si es 8, mostrar registro de contraseña por enlace
 * - Usr:    usuario pasado por enlace
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$action   = $_GET['action'] ?? '';                               // '', 'cp'
$dataRaw  = $_GET['data'] ?? null;
$data     = filter_input(INPUT_GET, 'data', FILTER_VALIDATE_INT);
$usr      = filter_input(INPUT_GET, 'Usr', FILTER_SANITIZE_SPECIAL_CHARS);

/* ==========================================================================================
 * BLOQUE: Mensajes de estado
 * Qué hace: Mapea códigos de mensaje a texto informativo
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
$messages = [
    1 => "Este correo ya registró contraseña. Solicita otro enlace a tu supervisor.",
    2 => "Las contraseñas no coinciden.",
    3 => "Contraseña registrada correctamente.",
    4 => "Usuario o contraseña incorrectos.",
    5 => "Tu contraseña actual es incorrecta.",
    6 => "Contraseña actualizada correctamente.",
];

// Cache-busting seguro para assets si $VerCache no está definido
$VerCacheSafe = isset($VerCache) ? (string)$VerCache : '1';
?>
<!doctype html>
<html lang="es-MX">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Acceso seguro a KASU Ventas">
<meta name="theme-color" content="#F2F2F2">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>KASU | Ventas</title>
<link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
<link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
<link rel="manifest" href="/login/manifest.webmanifest">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>">
</head>
<body>
    <div class="login-clean">
      <div class="login-card">
        <div class="illustration"><img alt="KASU" src="assets/img/logoKasu.png"></div>

<?php
  $isTokenReset = ($dataRaw !== null && !ctype_digit((string)$dataRaw) && !empty($usr));
?>

<?php if ($isTokenReset): ?>
        <!-- ==================================================================================
             FORMULARIO: ACTIVAR/REGISTRAR CONTRASEÑA vía enlace con token
             ================================================================================== -->
        <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
            <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
            <input type="hidden" name="data" value="<?= htmlspecialchars($dataRaw, ENT_QUOTES) ?>">
            <input type="hidden" name="User" value="<?= htmlspecialchars($usr ?? '', ENT_QUOTES) ?>">

            <div class="form-group">
                <input class="form-control" type="password" name="PassWord1" placeholder="Nueva contraseña" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" name="PassWord2" placeholder="Confirmar contraseña" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" name="GenCont" value="1" type="submit">Guardar contraseña</button>
            </div>
            <div class="text-center"><a href="/login/index.php">Volver a iniciar sesión</a></div>
        </form>

    <?php elseif (!empty($data) && $data === 8): ?>
        <!-- ==================================================================================
             FORMULARIO: ACTIVAR/REGISTRAR CONTRASEÑA vía enlace
             Qué hace: Permite establecer una nueva contraseña desde un link de activación
             Fecha: 05/11/2025 — Revisado por: JCCM
             ================================================================================== -->
        <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
            <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
            <input type="hidden" name="data" value="<?= (int)$data ?>">
            <input type="hidden" name="User" value="<?= htmlspecialchars($usr ?? '', ENT_QUOTES) ?>">

            <div class="form-group">
                <input class="form-control" type="password" name="PassWord1" placeholder="Nueva contraseña" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" name="PassWord2" placeholder="Confirmar contraseña" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" name="GenCont" value="1" type="submit">Guardar contraseña</button>
            </div>
            <div class="text-center"><a href="/login/index.php">Volver a iniciar sesión</a></div>
        </form>

    <?php elseif ($action === 'cp'): ?>
        <!-- ==================================================================================
             FORMULARIO: CAMBIAR CONTRASEÑA con usuario + contraseña actual
             Qué hace: Solicita credenciales actuales y nueva contraseña
             Fecha: 05/11/2025 — Revisado por: JCCM
             ================================================================================== -->
        <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
            <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">

            <div class="form-group">
                <input class="form-control" type="text" name="Usuario" placeholder="Usuario (ej. JCARLOS)" required autocomplete="username">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" name="PassAct" placeholder="Contraseña actual" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" name="PassWord1" placeholder="Nueva contraseña" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" name="PassWord2" placeholder="Confirmar nueva contraseña" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" name="CambiarPass" value="1" type="submit">Cambiar contraseña</button>
            </div>
            <div class="text-center"><a href="/login">Volver a iniciar sesión</a></div>
        </form>

    <?php else: ?>
        <!-- ==================================================================================
             FORMULARIO: INGRESAR AL SISTEMA
             Qué hace: Autenticación estándar con usuario y contraseña
             Fecha: 05/11/2025 — Revisado por: JCCM
             ================================================================================== -->
        <form method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="on">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
            <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">

            <div class="form-group">
                <input class="form-control" type="text" name="Usuario" placeholder="Usuario" required autocomplete="username">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" name="PassWord" placeholder="Contraseña" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" name="Login" value="1" type="submit">Ingresar</button>
            </div>
            <div class="text-center">
                <a href="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>?action=cp">Cambiar mi contraseña</a>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($data) && isset($messages[$data])): ?>
        <div class="alert alert-info" role="alert"><?= htmlspecialchars($messages[$data], ENT_QUOTES) ?></div>
    <?php endif; ?>
      </div>
    </div>
  <script defer src="Javascript/finger.js?v=3"></script>
  <script defer src="Javascript/localize.js?v=3"></script>
  <script defer src="Javascript/Inyectar_gps_form.js"></script>
  <script defer src="/login/Javascript/install.js"></script>
</body>
</html>
