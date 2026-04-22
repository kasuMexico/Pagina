<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/librerias_api.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($mysqli_api) || !($mysqli_api instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión API Market.');
}

api_access_schema($mysqli_api);

if (empty($_SESSION['api_access_csrf'])) {
    $_SESSION['api_access_csrf'] = bin2hex(random_bytes(32));
}

function api_access_redirect(string $msg): void
{
    header('Location: acceso.php?Msg=' . rawurlencode($msg), true, 303);
    exit;
}

function api_access_current_user(mysqli $db): ?array
{
    $id = (int)($_SESSION['api_access_user_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    $stmt = $db->prepare('SELECT * FROM api_access_users WHERE id = ? AND activo = 1 LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    return $row;
}

function api_access_user_requests(mysqli $db, int $userId): array
{
    $stmt = $db->prepare('SELECT * FROM api_access_requests WHERE user_id = ? ORDER BY id DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = [];
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = (string)($_POST['csrf'] ?? '');
        if (!hash_equals((string)$_SESSION['api_access_csrf'], $csrf)) {
            api_access_redirect('Sesión inválida. Recarga la página.');
        }

        $action = (string)($_POST['action'] ?? '');

        if ($action === 'logout') {
            unset($_SESSION['api_access_user_id']);
            api_access_redirect('Sesión cerrada.');
        }

        if ($action === 'register') {
            $nombre = trim((string)($_POST['nombre'] ?? ''));
            $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
            $empresa = trim((string)($_POST['empresa'] ?? ''));
            $telefono = trim((string)($_POST['telefono'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
                api_access_redirect('Datos inválidos. Usa un correo válido y contraseña de al menos 8 caracteres.');
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli_api->prepare('
                INSERT INTO api_access_users (nombre, correo, empresa, telefono, password_hash, activo)
                VALUES (?, ?, ?, ?, ?, 1)
            ');
            $stmt->bind_param('sssss', $nombre, $correo, $empresa, $telefono, $hash);
            $stmt->execute();
            $_SESSION['api_access_user_id'] = (int)$mysqli_api->insert_id;
            $stmt->close();

            api_access_redirect('Cuenta creada. Ahora solicita los accesos que necesitas.');
        }

        if ($action === 'login') {
            $correo = strtolower(trim((string)($_POST['correo'] ?? '')));
            $password = (string)($_POST['password'] ?? '');

            $stmt = $mysqli_api->prepare('SELECT * FROM api_access_users WHERE correo = ? AND activo = 1 LIMIT 1');
            $stmt->bind_param('s', $correo);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$user || !password_verify($password, (string)$user['password_hash'])) {
                api_access_redirect('Usuario o contraseña inválidos.');
            }

            $_SESSION['api_access_user_id'] = (int)$user['id'];
            $stmt = $mysqli_api->prepare('UPDATE api_access_users SET last_login = NOW() WHERE id = ?');
            $uid = (int)$user['id'];
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $stmt->close();

            api_access_redirect('Bienvenido a API Market.');
        }

        if ($action === 'request_access') {
            $user = api_access_current_user($mysqli_api);
            if (!$user) {
                api_access_redirect('Inicia sesión para solicitar accesos.');
            }

            $apiAccounts = isset($_POST['api_accounts']) ? 1 : 0;
            $apiCustomer = isset($_POST['api_customer']) ? 1 : 0;
            $apiPayments = isset($_POST['api_payments']) ? 1 : 0;
            $apiValidate = isset($_POST['api_validate_mexico']) ? 1 : 0;
            if (($apiAccounts + $apiCustomer + $apiPayments + $apiValidate) === 0) {
                api_access_redirect('Selecciona al menos una API.');
            }

            $website = trim((string)($_POST['website'] ?? ''));
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));
            $saldo = api_access_money_to_centavos($_POST['saldo_solicitado'] ?? '0');

            $stmt = $mysqli_api->prepare('
                INSERT INTO api_access_requests
                    (user_id, nombre, correo, empresa, telefono, website,
                     api_accounts, api_customer, api_payments, api_validate_mexico,
                     saldo_solicitado_centavos, mensaje, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "PENDIENTE")
            ');
            $uid = (int)$user['id'];
            $nombre = (string)$user['nombre'];
            $correo = (string)$user['correo'];
            $empresa = (string)($user['empresa'] ?? '');
            $telefono = (string)($user['telefono'] ?? '');
            $stmt->bind_param(
                'isssssiiiiis',
                $uid,
                $nombre,
                $correo,
                $empresa,
                $telefono,
                $website,
                $apiAccounts,
                $apiCustomer,
                $apiPayments,
                $apiValidate,
                $saldo,
                $mensaje
            );
            $stmt->execute();
            $stmt->close();

            api_access_redirect('Solicitud enviada. La revisaremos desde KASU.');
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log('[API_ACCESS_PORTAL] ' . $e->getMessage());
    api_access_redirect('No se pudo procesar la solicitud. Verifica si el correo ya existe.');
} catch (Throwable $e) {
    error_log('[API_ACCESS_PORTAL] ' . $e->getMessage());
    api_access_redirect('No se pudo procesar la solicitud.');
}

$currentUser = api_access_current_user($mysqli_api);
$requests = $currentUser ? api_access_user_requests($mysqli_api, (int)$currentUser['id']) : [];
$msg = isset($_GET['Msg']) ? (string)$_GET['Msg'] : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Acceso API Market KASU</title>
  <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css">
  <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
  <link rel="stylesheet" href="assets/index.css">
  <link rel="stylesheet" href="assets/codigo.css">
</head>
<body class="doc-page api-access-page">
  <header class="header-area header-sticky" role="banner">
    <div class="container">
      <nav class="main-nav" aria-label="API Market">
        <a href="index.php" class="logo" aria-label="API Market KASU">
          <img src="https://kasu.com.mx/assets/images/Index/florkasu.png" alt="KASU">
        </a>
        <ul class="nav">
          <li><a href="index.php">API Market</a></li>
          <li><a href="documentacion/doc_accounts.php">Documentación</a></li>
          <li><a href="index.php#contact-us">Contacto</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <section class="doc-hero">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7">
          <span class="api-kicker">Acceso API Market</span>
          <h1 class="doc-hero__title">Solicita credenciales y saldo para usar las APIs KASU.</h1>
          <p class="doc-hero__lead">Crea tu cuenta, selecciona las APIs que necesitas y pide saldo inicial si vas a consumir <strong>Validate_Mexico</strong>. KASU revisa y aprueba cada solicitud.</p>
        </div>
        <div class="col-lg-5">
          <div class="doc-hero__meta">
            <div class="doc-hero__meta-row">
              <span>APIs</span>
              <strong>Accounts, Customer, Payments, Validate_Mexico</strong>
            </div>
            <div class="doc-hero__meta-row">
              <span>Token</span>
              <strong>Token_Full + Bearer</strong>
            </div>
            <div class="doc-hero__meta-row">
              <span>Saldo</span>
              <strong>Wallet prepago para Validate_Mexico</strong>
            </div>
          </div>
        </div>
      </div>
      <?php if ($msg !== '') { ?>
        <div class="doc-note mt-4"><?php echo api_access_h($msg); ?></div>
      <?php } ?>
    </div>
  </section>

  <?php if (!$currentUser) { ?>
  <section class="doc-section">
    <div class="container">
      <div class="doc-grid">
        <div class="doc-panel">
          <span class="doc-pill">Nueva cuenta</span>
          <h3>Crear acceso</h3>
          <form method="post" action="acceso.php">
            <input type="hidden" name="csrf" value="<?php echo api_access_h($_SESSION['api_access_csrf']); ?>">
            <input type="hidden" name="action" value="register">
            <div class="form-group"><input class="form-control" name="nombre" placeholder="Nombre completo" required></div>
            <div class="form-group"><input class="form-control" name="empresa" placeholder="Empresa"></div>
            <div class="form-group"><input class="form-control" name="telefono" placeholder="Teléfono"></div>
            <div class="form-group"><input class="form-control" type="email" name="correo" placeholder="Correo electrónico" required></div>
            <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Contraseña, mínimo 8 caracteres" minlength="8" required></div>
            <button class="api-button" type="submit">Crear cuenta</button>
          </form>
        </div>
        <div class="doc-panel">
          <span class="doc-pill">Ya tengo cuenta</span>
          <h3>Iniciar sesión</h3>
          <form method="post" action="acceso.php">
            <input type="hidden" name="csrf" value="<?php echo api_access_h($_SESSION['api_access_csrf']); ?>">
            <input type="hidden" name="action" value="login">
            <div class="form-group"><input class="form-control" type="email" name="correo" placeholder="Correo electrónico" required></div>
            <div class="form-group"><input class="form-control" type="password" name="password" placeholder="Contraseña" required></div>
            <button class="api-button" type="submit">Entrar</button>
          </form>
        </div>
      </div>
    </div>
  </section>
  <?php } else { ?>
  <section class="doc-section">
    <div class="container">
      <div class="doc-heading d-flex flex-wrap justify-content-between align-items-start">
        <div>
          <span class="api-kicker">Panel del solicitante</span>
          <h2><?php echo api_access_h($currentUser['nombre']); ?></h2>
          <p><?php echo api_access_h($currentUser['correo']); ?></p>
        </div>
        <form method="post" action="acceso.php">
          <input type="hidden" name="csrf" value="<?php echo api_access_h($_SESSION['api_access_csrf']); ?>">
          <input type="hidden" name="action" value="logout">
          <button class="api-button api-button--secondary" type="submit">Cerrar sesión</button>
        </form>
      </div>

      <div class="doc-grid">
        <div class="doc-panel">
          <span class="doc-pill">Nueva solicitud</span>
          <h3>Selecciona accesos</h3>
          <form method="post" action="acceso.php">
            <input type="hidden" name="csrf" value="<?php echo api_access_h($_SESSION['api_access_csrf']); ?>">
            <input type="hidden" name="action" value="request_access">
            <label class="api-checkbox"><input type="checkbox" name="api_accounts"> API_ACCOUNTS</label>
            <label class="api-checkbox"><input type="checkbox" name="api_customer"> API_CUSTOMER</label>
            <label class="api-checkbox"><input type="checkbox" name="api_payments"> API_PAYMENTS</label>
            <label class="api-checkbox"><input type="checkbox" name="api_validate_mexico"> Validate_Mexico</label>
            <div class="form-group mt-3">
              <input class="form-control" type="url" name="website" placeholder="Sitio web o aplicación">
            </div>
            <div class="form-group">
              <input class="form-control" name="saldo_solicitado" placeholder="Saldo inicial para Validate_Mexico, ej. 500.00">
            </div>
            <div class="form-group">
              <textarea class="form-control" name="mensaje" rows="4" placeholder="Describe tu caso de uso, volumen esperado y ambiente de pruebas."></textarea>
            </div>
            <button class="api-button" type="submit">Enviar solicitud</button>
          </form>
        </div>
        <div class="doc-panel">
          <span class="doc-pill">Historial</span>
          <h3>Mis solicitudes</h3>
          <?php if (!$requests) { ?>
            <p>No tienes solicitudes registradas.</p>
          <?php } else { ?>
            <div class="api-request-list">
              <?php foreach ($requests as $request) { ?>
                <div class="api-request-item">
                  <strong>#<?php echo (int)$request['id']; ?> <?php echo api_access_h($request['estado']); ?></strong>
                  <span><?php echo api_access_h(implode(', ', api_access_request_apis($request))); ?></span>
                  <?php if ((string)($request['api_user'] ?? '') !== '') { ?>
                    <small>Usuario API: <code><?php echo api_access_h($request['api_user']); ?></code></small>
                  <?php } ?>
                  <?php if ((string)($request['secret_key_usuario'] ?? '') !== '' && (int)($request['secret_key_id'] ?? 0) > 0) { ?>
                    <small>User-Agent: <code><?php echo api_access_h($request['secret_key_usuario'] . '_' . $request['secret_key_id']); ?></code></small>
                  <?php } ?>
                  <?php if ((int)($request['saldo_solicitado_centavos'] ?? 0) > 0) { ?>
                    <small>Saldo solicitado: <?php echo api_access_h(api_access_centavos_to_money((int)$request['saldo_solicitado_centavos'])); ?></small>
                  <?php } ?>
                  <?php if ((string)($request['admin_notes'] ?? '') !== '') { ?>
                    <small>Notas KASU: <?php echo api_access_h($request['admin_notes']); ?></small>
                  <?php } ?>
                </div>
              <?php } ?>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </section>
  <?php } ?>

  <footer>
    <?php require __DIR__ . '/html/footer.php'; ?>
  </footer>
</body>
</html>
