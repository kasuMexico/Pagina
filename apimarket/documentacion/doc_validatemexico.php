<?php
/**
 * ============================================================================
 * Archivo : doc_validatemexico.php
 * Carpeta : /apimarket/documentacion
 *
 * Qué hace:
 * ----------
 * Documentación pública (interna) para el producto:
 *   Validate_Mexico (CURP / RFC) - Conéctame (SOAP) con caché y prepago.
 *
 * Reglas comerciales:
 * - Cobro por consulta EXITOSA.
 * - Precio IGUAL aunque la respuesta venga de caché (margen máximo).
 * - Caché: si ya existe en BD y está vigente, no se consulta upstream.
 * ============================================================================
 */

declare(strict_types=1);
date_default_timezone_set('America/Mexico_City');

$BASE_URL  = 'https://apimarket.kasu.com.mx';
$API_URL   = $BASE_URL . '/api/ValidateMexico_V1.php';
$TOKEN_URL = $BASE_URL . '/Token_Full.php'; // ajusta si tu ruta real es distinta
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>API Market KASU - Validate_Mexico</title>
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;margin:0;background:#f6f8fb;color:#0f172a}
    .wrap{max-width:1100px;margin:0 auto;padding:24px}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px 18px;margin:12px 0;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    h1{font-size:22px;margin:0 0 10px}
    h2{font-size:16px;margin:0 0 10px}
    p,li{line-height:1.45}
    code,pre{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,Liberation Mono,monospace}
    pre{background:#0b1020;color:#e5e7eb;padding:14px;border-radius:12px;overflow:auto}
    .pill{display:inline-block;background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;padding:3px 10px;border-radius:999px;font-size:12px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:900px){.grid{grid-template-columns:1fr}}
    table{width:100%;border-collapse:collapse}
    th,td{border-bottom:1px solid #e5e7eb;text-align:left;padding:10px}
    th{background:#f8fafc;font-weight:600}
    .muted{color:#475569;font-size:13px}
  </style>
</head>
<body>
<div class="wrap">

  <div class="card">
    <h1>Validate_Mexico <span class="pill">CURP / RFC</span></h1>
    <p class="muted">
      Servicio de validación para obtener datos asociados a CURP y RFC mediante Conéctame (upstream SOAP),
      con caché para optimización y modelo prepago para subdistribuidores.
    </p>
  </div>

  <div class="card">
    <h2>Endpoints</h2>
    <div class="grid">
      <div>
        <p><b>Token (Bearer)</b></p>
        <pre><?php echo htmlspecialchars($TOKEN_URL, ENT_QUOTES, 'UTF-8'); ?></pre>
        <p class="muted">Genera un token de acceso (Authorization: Bearer) para consumir la API.</p>
      </div>
      <div>
        <p><b>API Validate_Mexico</b></p>
        <pre><?php echo htmlspecialchars($API_URL, ENT_QUOTES, 'UTF-8'); ?></pre>
        <p class="muted">Recibe solicitudes JSON por POST.</p>
      </div>
    </div>
  </div>

  <div class="card">
    <h2>Modelo de cobro y caché</h2>
    <ul>
      <li><b>Cobro por consulta exitosa</b>: solo se descuenta saldo cuando la respuesta es exitosa.</li>
      <li><b>Precio igual aunque sea caché</b>: el costo por éxito es el mismo venga de BD (caché) o upstream.</li>
      <li><b>Caché</b>: si la CURP/RFC ya existe y está vigente, el sistema responde sin llamar al proveedor.</li>
      <li><b>TTL sugerido</b>: 30 días (configurable a nivel de implementación).</li>
    </ul>
  </div>

  <div class="card">
    <h2>Métodos disponibles</h2>
    <table>
      <thead>
        <tr>
          <th>Método</th>
          <th>Descripción</th>
          <th>¿Cobrable?</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>curp_validate</code></td>
          <td>Valida y obtiene datos por CURP.</td>
          <td>Sí (solo si éxito)</td>
        </tr>
        <tr>
          <td><code>rfc_validate</code></td>
          <td>Obtiene datos por RFC.</td>
          <td>Sí (solo si éxito)</td>
        </tr>
        <tr>
          <td><code>upstream_saldo</code></td>
          <td>Consulta saldo del proveedor upstream (solo admin/soporte).</td>
          <td>No</td>
        </tr>
        <tr>
          <td><code>upstream_peticiones</code></td>
          <td>Consulta historial upstream por página (solo admin/soporte).</td>
          <td>No</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Autenticación</h2>
    <p>Debe enviarse el token en la cabecera:</p>
    <pre>Authorization: Bearer &lt;TOKEN&gt;
Content-Type: application/json</pre>

    <p class="muted">
      La generación/validación del token depende del módulo actual de API Market (Token_Full.php / Validador_Token.php).
    </p>
  </div>

  <div class="card">
    <h2>Ejemplo 1: Validar CURP</h2>
    <pre>{
  "tipo_peticion": "request",
  "nombre_de_usuario": "Api_telecom_bienestar",
  "metodo": "curp_validate",
  "valor": "AAAA000000HDFXXX00"
}</pre>

    <p><b>Respuesta (ejemplo)</b></p>
    <pre>{
  "ok": true,
  "success": true,
  "origen": "cache",
  "costo_centavos": 200,
  "saldo_centavos": 499800,
  "data": {
    "Response": "correct",
    "Curp": "AAAA000000HDFXXX00",
    "Nombre": "NOMBRE",
    "Paterno": "APELLIDO",
    "Materno": "APELLIDO",
    "Sexo": "H",
    "FechaNacimiento": "2000-01-01",
    "Nacionalidad": "MEX",
    "StatusCurp": "OK"
  },
  "ms": 34
}</pre>
  </div>

  <div class="card">
    <h2>Ejemplo 2: Validar RFC</h2>
    <pre>{
  "tipo_peticion": "request",
  "nombre_de_usuario": "Api_telecom_bienestar",
  "metodo": "rfc_validate",
  "valor": "ABC123456T89"
}</pre>
  </div>

  <div class="card">
    <h2>Precios</h2>
    <p class="muted">Los precios se gestionan desde la tabla <code>api_pricing</code> por método. Valores típicos:</p>
    <table>
      <thead>
        <tr>
          <th>Método</th>
          <th>Precio éxito</th>
          <th>Precio fallo</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><code>curp_validate</code></td>
          <td>$2.00 MXN (200 centavos)</td>
          <td>$0.00 MXN</td>
        </tr>
        <tr>
          <td><code>rfc_validate</code></td>
          <td>$2.00 MXN (200 centavos)</td>
          <td>$0.00 MXN</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Códigos HTTP</h2>
    <table>
      <thead>
        <tr><th>Código</th><th>Significado</th></tr>
      </thead>
      <tbody>
        <tr><td><code>200</code></td><td>Solicitud procesada (revisar <code>success</code>).</td></tr>
        <tr><td><code>400</code></td><td>Solicitud inválida (JSON inválido, método faltante, valor inválido).</td></tr>
        <tr><td><code>401</code></td><td>No autorizado (token faltante o inválido).</td></tr>
        <tr><td><code>402</code></td><td>Saldo insuficiente (prepago).</td></tr>
        <tr><td><code>403</code></td><td>Prohibido (usuario inactivo o sin permisos).</td></tr>
        <tr><td><code>405</code></td><td>Método HTTP no permitido (solo POST).</td></tr>
        <tr><td><code>429</code></td><td>Límite de solicitudes (rate limit, si aplica).</td></tr>
        <tr><td><code>502</code></td><td>Error del proveedor upstream (SOAP).</td></tr>
        <tr><td><code>500</code></td><td>Error interno.</td></tr>
      </tbody>
    </table>
  </div>

  <div class="card">
    <h2>Notas de privacidad</h2>
    <ul>
      <li>Para auditoría se recomienda registrar <b>hash</b> del valor consultado (CURP/RFC) en lugar del valor en claro.</li>
      <li>La respuesta completa puede almacenarse como JSON en caché (según política interna).</li>
    </ul>
  </div>

</div>
</body>
</html>
