<?php
/********************************************************************************************
 * Qué hace: Renderiza la pantalla de acceso KASU (iniciar sesión / activar contraseña / cambiar contraseña)
 *           con CSRF, cookies seguras y rutas a Funcionalidad_Empleados.php.
 * Fecha: 06/12/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();

/* ==========================================================================================
 * BLOQUE: Dependencias
 * ========================================================================================== */
require_once __DIR__ . '/../eia/librerias.php';

/* ==========================================================================================
 * BLOQUE: Acceso directo si ya hay sesión
 * ========================================================================================== */
if (!empty($_SESSION['Vendedor'])) {
    header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
    exit;
}

/* ==========================================================================================
 * BLOQUE: Token CSRF
 * ========================================================================================== */
if (empty($_SESSION['csrf_auth'])) {
    $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}

/* ==========================================================================================
 * BLOQUE: Selector de vista
 * - action: '' = login, 'cp' = cambiar contraseña
 * - data:   int opcional; si es 8, mostrar registro de contraseña por enlace
 * - Usr:    usuario pasado por enlace
 * ========================================================================================== */
$action   = $_GET['action'] ?? '';                               // '', 'cp'
$dataRaw  = $_GET['data'] ?? null;
$data     = filter_input(INPUT_GET, 'data', FILTER_VALIDATE_INT);
$usr      = filter_input(INPUT_GET, 'Usr', FILTER_SANITIZE_SPECIAL_CHARS);

/* ==========================================================================================
 * BLOQUE: Detección de modo "registro de contraseña por token"
 * ========================================================================================== */
$isTokenReset = ($dataRaw !== null && !ctype_digit((string)$dataRaw) && !empty($usr));

/* ==========================================================================================
 * BLOQUE: Mensajes de estado
 * ========================================================================================== */
$messages = [
    1 => "Este correo ya registró contraseña. Solicita otro enlace a tu supervisor.",
    2 => "Las contraseñas no coinciden.",
    3 => "Contraseña registrada correctamente.",
    4 => "Usuario o contraseña incorrectos.",
    5 => "Tu contraseña actual es incorrecta.",
    6 => "Contraseña actualizada correctamente.",
];

/* ==========================================================================================
 * BLOQUE: Título y subtítulo según vista (para UI tipo app)
 * ========================================================================================== */
$viewTitle    = 'Plataforma Colaboradores';
$viewSubtitle = 'Inicia sesión para continuar.';

if ($isTokenReset || (!empty($data) && $data === 8)) {
    $viewTitle    = 'Crear contraseña';
    $viewSubtitle = 'Elige una contraseña segura para tu cuenta.';
} elseif ($action === 'cp') {
    $viewTitle    = 'Cambiar contraseña';
    $viewSubtitle = 'Actualiza tu contraseña de acceso.';
}

/* Cache-busting seguro para assets si $VerCache no está definido */
$VerCacheSafe = isset($VerCache) ? (string)$VerCache : '1';
?>
<!doctype html>
<html lang="es-MX">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="Acceso seguro a KASU Ventas">
<meta name="theme-color" content="#01579b">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<title>KASU | Ventas</title>
<link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
<link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png">
<link rel="manifest" href="/login/manifest.webmanifest">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>">
</head>
<body class="auth-body">
  <main class="login-clean auth-shell">
    <section class="login-card auth-card" aria-label="Acceso a KASU Ventas">
      <header class="auth-header">
        <div class="auth-logo">
          <img alt="KASU" src="assets/img/logoKasu.png" loading="lazy" decoding="async">
        </div>
        <div class="auth-header-text">
          <h1 class="auth-title"><?= htmlspecialchars($viewTitle, ENT_QUOTES) ?></h1>
          <p class="auth-subtitle"><?= htmlspecialchars($viewSubtitle, ENT_QUOTES) ?></p>
        </div>
      </header>

      <div class="auth-tabs" role="tablist" aria-label="Modo de acceso">
        <a
          href="/login/index.php"
          class="auth-tab<?= ($action === '' && !$isTokenReset && ($data !== 8)) ? ' is-active' : '' ?>"
          role="tab"
          aria-selected="<?= ($action === '' && !$isTokenReset && ($data !== 8)) ? 'true' : 'false' ?>"
        >
          Ingresar
        </a>

        <a
          href="/login/index.php?action=cp"
          class="auth-tab<?= ($action === 'cp') ? ' is-active' : '' ?>"
          role="tab"
          aria-selected="<?= ($action === 'cp') ? 'true' : 'false' ?>"
        >
          Cambiar contraseña
        </a>
      </div>

      <?php if ($isTokenReset): ?>
        <!-- ==================================================================================
             FORMULARIO: ACTIVAR/REGISTRAR CONTRASEÑA vía enlace con token (data no numérico)
             ================================================================================== -->
        <form class="auth-form" method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="data" value="<?= htmlspecialchars($dataRaw, ENT_QUOTES) ?>">
          <input type="hidden" name="User" value="<?= htmlspecialchars($usr ?? '', ENT_QUOTES) ?>">

          <div class="form-group">
            <label class="auth-label" for="pass1-token">Nueva contraseña</label>
            <input
              id="pass1-token"
              class="form-control auth-control"
              type="password"
              name="PassWord1"
              placeholder="••••••••"
              required
              autocomplete="new-password">
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass2-token">Confirmar contraseña</label>
            <input
              id="pass2-token"
              class="form-control auth-control"
              type="password"
              name="PassWord2"
              placeholder="Repite tu contraseña"
              required
              autocomplete="new-password">
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="GenCont" value="1" type="submit">
            Guardar contraseña
          </button>

          <button type="button" class="btn btn-link btn-sm auth-link" onclick="window.location.href='/login/index.php'">
            Volver a iniciar sesión
          </button>
        </form>

      <?php elseif (!empty($data) && $data === 8): ?>
        <!-- ==================================================================================
             FORMULARIO: ACTIVAR/REGISTRAR CONTRASEÑA vía enlace (data = 8)
             ================================================================================== -->
        <form class="auth-form" method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="data" value="<?= (int)$data ?>">
          <input type="hidden" name="User" value="<?= htmlspecialchars($usr ?? '', ENT_QUOTES) ?>">

          <div class="form-group">
            <label class="auth-label" for="pass1-link">Nueva contraseña</label>
            <input
              id="pass1-link"
              class="form-control auth-control"
              type="password"
              name="PassWord1"
              placeholder="••••••••"
              required
              autocomplete="new-password">
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass2-link">Confirmar contraseña</label>
            <input
              id="pass2-link"
              class="form-control auth-control"
              type="password"
              name="PassWord2"
              placeholder="Repite tu contraseña"
              required
              autocomplete="new-password">
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="GenCont" value="1" type="submit">
            Guardar contraseña
          </button>

          <button type="button" class="btn btn-link btn-sm auth-link" onclick="window.location.href='/login/index.php'">
            Volver a iniciar sesión
          </button>
        </form>

      <?php elseif ($action === 'cp'): ?>
        <!-- ==================================================================================
             FORMULARIO: CAMBIAR CONTRASEÑA
             ================================================================================== -->
        <form class="auth-form" method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">

          <div class="form-group">
            <label class="auth-label" for="usuario-cp">Usuario</label>
            <input
              id="usuario-cp"
              class="form-control auth-control"
              type="text"
              name="Usuario"
              placeholder="Ej. JCARLOS"
              required
              autocomplete="username">
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass-act">Contraseña actual</label>
            <input
              id="pass-act"
              class="form-control auth-control"
              type="password"
              name="PassAct"
              placeholder="••••••••"
              required
              autocomplete="current-password">
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass1-cp">Nueva contraseña</label>
            <input
              id="pass1-cp"
              class="form-control auth-control"
              type="password"
              name="PassWord1"
              placeholder="Nueva contraseña"
              required
              autocomplete="new-password">
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass2-cp">Confirmar nueva contraseña</label>
            <input
              id="pass2-cp"
              class="form-control auth-control"
              type="password"
              name="PassWord2"
              placeholder="Repite tu contraseña"
              required
              autocomplete="new-password">
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="CambiarPass" value="1" type="submit">
            Cambiar contraseña
          </button>

          <button type="button" class="btn btn-link btn-sm auth-link" onclick="window.location.href='/login/index.php'">
            Volver a iniciar sesión
          </button>
        </form>

      <?php else: ?>
        <!-- ==================================================================================
             FORMULARIO: LOGIN
             ================================================================================== -->
        <form class="auth-form" method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="on">
          <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">

          <div class="form-group">
            <label class="auth-label" for="usuario-login">Usuario</label>
            <input
              id="usuario-login"
              class="form-control auth-control"
              type="text"
              name="Usuario"
              placeholder="Ej. JCARLOS"
              required
              autocomplete="username">
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass-login">Contraseña</label>
            <input
              id="pass-login"
              class="form-control auth-control"
              type="password"
              name="PassWord"
              placeholder="••••••••"
              required
              autocomplete="current-password">
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="Login" value="1" type="submit">
            Ingresar
          </button>

          <button
            type="button"
            class="btn btn-link btn-sm auth-link"
            onclick="window.location.href='<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>?action=cp'">
            Cambiar mi contraseña
          </button>
        </form>
      <?php endif; ?>

      <?php if (!empty($data) && isset($messages[$data])): ?>
        <div class="auth-toast" role="status">
          <span class="auth-toast-dot"></span>
          <p class="auth-toast-text"><?= htmlspecialchars($messages[$data], ENT_QUOTES) ?></p>
        </div>
      <?php endif; ?>

      <footer class="auth-footer">
        <small>Versión PWA · KASU Ventas</small>
      </footer>
    </section>
  </main>

  <script defer src="Javascript/finger.js?v=3"></script>
  <script defer src="Javascript/localize.js?v=3"></script>
  <script defer src="Javascript/Inyectar_gps_form.js"></script>
  <script defer src="/login/Javascript/install.js"></script>
</body>
</html>
