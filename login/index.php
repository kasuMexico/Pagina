<?php
/********************************************************************************************
 * Qué hace: Renderiza la pantalla de acceso KASU (iniciar sesión / activar contraseña / cambiar contraseña)
 *           con CSRF, cookies seguras y rutas a Funcionalidad_Empleados.php.
 * Fecha: 06/12/2025
 * Revisado por: JCCM
 * Archivo: login/index.php
 ********************************************************************************************/

declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
try {
    kasu_session_start();
} catch (Throwable $e) {
    error_log('[KASU][Index][SessionError] ' . $e->getMessage());
}

$sessionDebug = [
    'session_id'      => session_id(),
    'session_name'    => session_name(),
    'session_status'  => session_status(),
    'cookie_params'   => session_get_cookie_params(),
    'user_agent'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
];
error_log('[KASU][Index][SessionDebug] ' . json_encode($sessionDebug, JSON_UNESCAPED_UNICODE));

/* ==========================================================================================
 * Dependencias
 * ========================================================================================== */
require_once __DIR__ . '/../eia/librerias.php';

/* ==========================================================================================
 * Acceso directo si ya hay sesión
 * ========================================================================================== */
$hasSession = !empty($_SESSION['Vendedor']) && !empty($_SESSION['IdEmpleado']);
if ($hasSession) {
    error_log('[KASU][Index] Sesión válida detectada, redirigiendo a Pwa_Principal.php');
    header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
    exit;
}

/* ==========================================================================================
 * Token CSRF
 * ========================================================================================== */
if (empty($_SESSION['csrf_auth'])) {
    $_SESSION['csrf_auth'] = bin2hex(random_bytes(32));
}

/* ==========================================================================================
 * Selector de vista
 * - action: '' = login, 'cp' = cambiar contraseña
 * - data:   int opcional; si es 8, mostrar registro de contraseña por enlace
 * - Usr:    usuario pasado por enlace
 * ========================================================================================== */
$action   = $_GET['action'] ?? '';                               // '', 'cp'
$dataRaw  = $_GET['data'] ?? null;
$data     = filter_input(INPUT_GET, 'data', FILTER_VALIDATE_INT);
$usr      = filter_input(INPUT_GET, 'Usr', FILTER_SANITIZE_SPECIAL_CHARS);

/* ==========================================================================================
 * Detección de modo "registro de contraseña por token"
 * ========================================================================================== */
$isTokenReset = ($dataRaw !== null && !ctype_digit((string)$dataRaw) && !empty($usr));

/* ==========================================================================================
 * Mensajes de estado
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
 * Título y subtítulo según vista (para UI tipo app)
 * ========================================================================================== */
$viewTitle    = 'Acceso';
$viewSubtitle = 'Ingresa con tu usuario y contraseña.';

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
<meta name="theme-color" content="#F1F7FC">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<title>KASU | Ventas</title>

<link rel="icon" href="/assets/images/Index/florkasu.png">
<link rel="apple-touch-icon" href="/login/assets/img/icon-152x152.png?v=2">
<link rel="manifest" href="/login/manifest.webmanifest?v=2">

<!-- Bootstrap (para .btn, .form-control, grid) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">

<!-- Hoja de estilos principal de la PWA (incluye los estilos auth-* que pegaste) -->
<link rel="stylesheet" href="/login/assets/css/styles.min.css?v=<?= htmlspecialchars($VerCacheSafe, ENT_QUOTES) ?>">
</head>
<body class="auth-body">
  <main class="login-clean auth-shell">
    <section class="login-card auth-card" aria-label="Acceso a KASU Ventas">
      <header class="auth-header">
        <div class="auth-logo">
          <img
            alt="KASU"
            src="assets/img/logoKasu.png"
            loading="lazy"
            decoding="async">
        </div>
        <div class="auth-header-text">
          <h1 class="auth-title"><?= htmlspecialchars($viewTitle, ENT_QUOTES) ?></h1>
          <p class="auth-subtitle"><?= htmlspecialchars($viewSubtitle, ENT_QUOTES) ?></p>
        </div>
      </header>

      <!-- Tabs modo de acceso (login / cambiar contraseña) -->
      <nav class="auth-tabs" role="tablist" aria-label="Modo de acceso">
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
      </nav>

      <?php if ($isTokenReset): ?>
        <!-- ==========================================================================
             FORMULARIO: ACTIVAR/REGISTRAR CONTRASEÑA vía enlace con token (data no numérico)
             ========================================================================== -->
        <form class="auth-form" method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="data" value="<?= htmlspecialchars($dataRaw, ENT_QUOTES) ?>">
          <input type="hidden" name="User" value="<?= htmlspecialchars($usr ?? '', ENT_QUOTES) ?>">

          <div class="form-group">
            <label class="auth-label" for="pass1-token">Nueva contraseña</label>
            <div class="auth-password">
              <input
                id="pass1-token"
                class="form-control auth-control"
                type="password"
                name="PassWord1"
                placeholder="••••••••"
                required
                autocomplete="new-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass2-token">Confirmar contraseña</label>
            <div class="auth-password">
              <input
                id="pass2-token"
                class="form-control auth-control"
                type="password"
                name="PassWord2"
                placeholder="Repite tu contraseña"
                required
                autocomplete="new-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="GenCont" value="1" type="submit">
            Guardar contraseña
          </button>

          <button
            type="button"
            class="btn btn-link btn-sm auth-link"
            onclick="window.location.href='/login/index.php'">
            Volver a iniciar sesión
          </button>
        </form>

      <?php elseif (!empty($data) && $data === 8): ?>
        <!-- ==========================================================================
             FORMULARIO: ACTIVAR/REGISTRAR CONTRASEÑA vía enlace (data = 8)
             ========================================================================== -->
        <form class="auth-form" method="POST" action="php/Funcionalidad_Empleados.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_auth'] ?>">
          <input type="hidden" name="Host" value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
          <input type="hidden" name="data" value="<?= (int)$data ?>">
          <input type="hidden" name="User" value="<?= htmlspecialchars($usr ?? '', ENT_QUOTES) ?>">

          <div class="form-group">
            <label class="auth-label" for="pass1-link">Nueva contraseña</label>
            <div class="auth-password">
              <input
                id="pass1-link"
                class="form-control auth-control"
                type="password"
                name="PassWord1"
                placeholder="••••••••"
                required
                autocomplete="new-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass2-link">Confirmar contraseña</label>
            <div class="auth-password">
              <input
                id="pass2-link"
                class="form-control auth-control"
                type="password"
                name="PassWord2"
                placeholder="Repite tu contraseña"
                required
                autocomplete="new-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="GenCont" value="1" type="submit">
            Guardar contraseña
          </button>

          <button
            type="button"
            class="btn btn-link btn-sm auth-link"
            onclick="window.location.href='/login/index.php'">
            Volver a iniciar sesión
          </button>
        </form>

      <?php elseif ($action === 'cp'): ?>
        <!-- ==========================================================================
             FORMULARIO: CAMBIAR CONTRASEÑA
             ========================================================================== -->
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
            <div class="auth-password">
              <input
                id="pass-act"
                class="form-control auth-control"
                type="password"
                name="PassAct"
                placeholder="••••••••"
                required
                autocomplete="current-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass1-cp">Nueva contraseña</label>
            <div class="auth-password">
              <input
                id="pass1-cp"
                class="form-control auth-control"
                type="password"
                name="PassWord1"
                placeholder="Nueva contraseña"
                required
                autocomplete="new-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label class="auth-label" for="pass2-cp">Confirmar nueva contraseña</label>
            <div class="auth-password">
              <input
                id="pass2-cp"
                class="form-control auth-control"
                type="password"
                name="PassWord2"
                placeholder="Repite tu contraseña"
                required
                autocomplete="new-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
          </div>

          <button class="btn btn-primary btn-block auth-btn" name="CambiarPass" value="1" type="submit">
            Cambiar contraseña
          </button>

          <button
            type="button"
            class="btn btn-link btn-sm auth-link"
            onclick="window.location.href='/login/index.php'">
            Volver a iniciar sesión
          </button>
        </form>

      <?php else: ?>
        <!-- ==========================================================================
             FORMULARIO: LOGIN
             ========================================================================== -->
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
            <div class="auth-password">
              <input
                id="pass-login"
                class="form-control auth-control"
                type="password"
                name="PassWord"
                placeholder="••••••••"
                required
                autocomplete="current-password">
              <button type="button" class="auth-eye" aria-label="Mostrar contraseña" aria-pressed="false">
                <svg class="icon-eye" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/><path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/><path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z"/></svg>
              </button>
            </div>
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
        <?php $isErrorToast = in_array($data, [1, 2, 4, 5], true); ?>
        <div class="auth-toast <?= $isErrorToast ? 'auth-toast-error' : 'auth-toast-success' ?>" role="<?= $isErrorToast ? 'alert' : 'status' ?>">
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
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.auth-eye').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var input = btn.parentElement.querySelector('input');
          var mostrar = (input.type === 'password');
          input.type = mostrar ? 'text' : 'password';
          btn.classList.toggle('is-visible', mostrar);
          btn.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
          btn.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
      });
    });
  </script>
</body>
</html>
