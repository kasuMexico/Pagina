<?php
/***********************************************************************************************
 * Archivo : doc_validatemexico.php
 * Carpeta : /apimarket/documentacion
 *
 * Documentación pública para Validate_Mexico V1.
 **********************************************************************************************/

declare(strict_types=1);
date_default_timezone_set('America/Mexico_City');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../librerias_api.php';

$BASE_URL  = 'https://apimarket.kasu.com.mx';
$API_URL   = $BASE_URL . '/api/ValidateMexico_V1';
$TOKEN_URL = $BASE_URL . '/api/Token_Full';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <link rel="canonical" href="https://kasu.com.mx/apimarket/documentacion/doc_validatemexico.php">
  <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Documentación Validate_Mexico V1 para validar CURP y RFC con caché, wallet prepago y upstream controlado.">
  <meta name="author" content="Jose Carlos Cabrera Monroy">
  <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css">
  <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
  <title>KASU | Validate_Mexico</title>

  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
  <link rel="stylesheet" href="../assets/index.css">
  <link rel="stylesheet" href="../assets/codigo.css">
</head>
<body class="doc-page">
<?php
  require_once __DIR__ . '/../html/menu.php';
  require_once __DIR__ . '/../html/Inf_general.php';
  require_once __DIR__ . '/../html/versiones.php';
?>

<section class="doc-section" id="endpoints">
  <div class="container">
    <div class="doc-heading">
      <span class="api-kicker">Validate_Mexico</span>
      <h2>Endpoints y autenticación</h2>
      <p>El endpoint V1 recibe solicitudes JSON por <strong>POST</strong>. Todas las operaciones requieren <strong>Authorization: Bearer</strong>, <strong>nombre_de_usuario</strong>, <strong>curp_en_uso</strong> y <strong>token_data</strong>.</p>
    </div>
    <div class="doc-grid">
      <div class="doc-panel">
        <span class="doc-pill">Token</span>
        <h3>Token_Full</h3>
        <pre class="doc-code"><code><?php echo htmlspecialchars($TOKEN_URL, ENT_QUOTES, 'UTF-8'); ?></code></pre>
        <p>Genera la credencial Bearer con el flujo HMAC vigente.</p>
      </div>
      <div class="doc-panel">
        <span class="doc-pill">API</span>
        <h3>ValidateMexico_V1</h3>
        <pre class="doc-code"><code><?php echo htmlspecialchars($API_URL, ENT_QUOTES, 'UTF-8'); ?></code></pre>
        <p>Valida CURP/RFC y retorna origen de respuesta, costo, saldo y datos del proveedor.</p>
      </div>
    </div>
  </div>
</section>

<section class="doc-section doc-section--muted" id="metodos">
  <div class="container">
    <div class="doc-heading">
      <h2>Métodos disponibles</h2>
      <p>Los métodos públicos usan wallet/prepago y caché. Las consultas upstream operativas deben restringirse a usuarios autorizados.</p>
    </div>
    <div class="doc-table">
      <table class="table table-responsive justify">
        <thead>
          <tr>
            <th>Método</th>
            <th>Descripción</th>
            <th>Modelo</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>curp_validate</code></td>
            <td>Valida y obtiene datos por CURP.</td>
            <td>Prepago; cobra si la consulta es exitosa.</td>
          </tr>
          <tr>
            <td><code>rfc_validate</code></td>
            <td>Valida y obtiene datos por RFC.</td>
            <td>Prepago; cobra si la consulta es exitosa.</td>
          </tr>
          <tr>
            <td><code>upstream_saldo</code></td>
            <td>Consulta saldo del proveedor upstream.</td>
            <td>Operativo; no descuenta wallet.</td>
          </tr>
          <tr>
            <td><code>upstream_peticiones</code></td>
            <td>Consulta historial upstream por página.</td>
            <td>Operativo; no descuenta wallet.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="doc-section" id="ejemplos">
  <div class="container">
    <div class="doc-heading">
      <h2>Ejemplos de consumo</h2>
      <p>También se acepta <strong>request</strong> como alias de <strong>metodo</strong>. El valor a validar viaja en <strong>valor</strong>.</p>
    </div>
    <div class="doc-grid">
      <div class="doc-panel">
        <span class="doc-pill">CURP</span>
        <h3>curp_validate</h3>
        <div class="code-window">
          <pre id="codecopi" class="userContent" style="white-space: pre-wrap;"><code>POST https://apimarket.kasu.com.mx/api/ValidateMexico_V1
Authorization: Bearer API_KEY_AQUI
Content-Type: application/json
User-Agent: SECRET_KEY_USUARIO_SECRET_KEY_ID

{
  "tipo_peticion": "request",
  "nombre_de_usuario": "YOUR_APPUSER",
  "curp_en_uso": "CURP_CODE",
  "metodo": "curp_validate",
  "valor": "AAAA000000HDFXXX00",
  "token_data": {
    "timestamp": TIMESTAMP,
    "expires_in": EXPIRE_IN
  }
}</code></pre>
        </div>
      </div>
      <div class="doc-panel">
        <span class="doc-pill">RFC</span>
        <h3>rfc_validate</h3>
        <div class="code-window">
          <pre id="codecopindex" class="userContent" style="white-space: pre-wrap;"><code>POST https://apimarket.kasu.com.mx/api/ValidateMexico_V1
Authorization: Bearer API_KEY_AQUI
Content-Type: application/json
User-Agent: SECRET_KEY_USUARIO_SECRET_KEY_ID

{
  "tipo_peticion": "request",
  "nombre_de_usuario": "YOUR_APPUSER",
  "curp_en_uso": "CURP_CODE",
  "metodo": "rfc_validate",
  "valor": "ABC123456T89",
  "token_data": {
    "timestamp": TIMESTAMP,
    "expires_in": EXPIRE_IN
  }
}</code></pre>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="doc-section doc-section--muted" id="respuestas">
  <div class="container">
    <div class="doc-heading">
      <h2>Respuesta y errores</h2>
      <p>La respuesta exitosa incluye el origen de datos (<strong>cache</strong> o <strong>upstream</strong>), costo en centavos, saldo restante, payload y tiempo de procesamiento.</p>
    </div>
    <div class="doc-grid">
      <div class="doc-panel">
        <h3>Respuesta positiva</h3>
        <pre class="doc-code"><code>{
  "ok": true,
  "success": true,
  "origen": "cache",
  "costo_centavos": 200,
  "saldo_centavos": 499800,
  "data": {
    "Response": "correct",
    "Curp": "AAAA000000HDFXXX00"
  },
  "ms": 34
}</code></pre>
      </div>
      <div class="doc-panel">
        <h3>Códigos HTTP</h3>
        <div class="doc-table">
          <table class="table table-responsive justify">
            <tbody>
              <tr><td><code>200</code></td><td>Solicitud procesada.</td></tr>
              <tr><td><code>400</code></td><td>JSON inválido, método faltante o valor inválido.</td></tr>
              <tr><td><code>401</code></td><td>Bearer token faltante o inválido.</td></tr>
              <tr><td><code>402</code></td><td>Saldo insuficiente.</td></tr>
              <tr><td><code>403</code></td><td>Usuario inactivo o sin permisos.</td></tr>
              <tr><td><code>405</code></td><td>Método HTTP no permitido; solo POST.</td></tr>
              <tr><td><code>502</code></td><td>Error del proveedor upstream.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../html/Sandbox.php'; ?>

<section class="doc-section" id="otros">
  <div class="container">
    <div class="center-heading">
      <h2 class="section-title">Otras APIs que te pueden interesar</h2>
      <br>
    </div>
    <?php require_once __DIR__ . '/../html/select_api.php'; ?>
  </div>
</section>

<footer>
  <?php require_once __DIR__ . '/../html/footer.php'; ?>
</footer>

<script>
function copiarAlPortapapeles(id) {
  var el = document.getElementById(id);
  if (!el) return;
  var texto = el.innerText || el.textContent || '';
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(texto).catch(function(){ fallbackCopy(texto); });
  } else {
    fallbackCopy(texto);
  }
  function fallbackCopy(t) {
    var aux = document.createElement('textarea');
    aux.value = t;
    aux.setAttribute('readonly', '');
    aux.style.position = 'absolute';
    aux.style.left = '-9999px';
    document.body.appendChild(aux);
    aux.select();
    try { document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(aux);
  }
}
</script>
<script src="https://kasu.com.mx/assets/js/jquery-2.1.0.min.js"></script>
<script src="https://kasu.com.mx/assets/js/bootstrap.min.js"></script>
<script src="https://kasu.com.mx/assets/js/scrollreveal.min.js"></script>
<script src="https://kasu.com.mx/assets/js/custom.js"></script>
</body>
</html>
