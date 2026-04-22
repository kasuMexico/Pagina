<?php
/***********************************************************************************************
 * Archivo: doc_payments.php
 * Qué hace: Página de documentación de la API_PAYMENTS. Muestra parámetros, códigos de respuesta,
 *           alcance de token_full y funcionalidades habilitables por token, sandbox y ejemplos.
 * Fecha: 2025-11-04 — Revisado por JCCM
 ***********************************************************************************************/

// indicar que se inicia una sesion *JCCM
session_start();

// se insertan las funciones
require_once '../librerias_api.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="keywords" content="Cobros">
  <link rel="canonical" href="kasu.com.mx">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Documentación API_PAYMENTS V1 para consultar estado de cuenta y registrar pagos PSD2.">
  <meta name="author" content="Jose Carlos cabrera Monroy">
  <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css">
  <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
  <title>KASU | API_PAYMENTS</title>

  <!-- Additional CSS Files -->
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
  <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
  <link rel="stylesheet" href="../assets/index.css">
  <link rel="stylesheet" href="../assets/codigo.css">
</head>
<body class="doc-page">
  <!-- La venta emergente debe de estar fuera del div que lo lanza *JCCM -->
  <?php
    require_once '../html/menu.php';        // Menu principal
    require_once '../html/Inf_general.php'; // informacion general
    require_once '../html/versiones.php';   // Informacion de versiones
  ?>

  <!-- ***** CODIGOS GENERALES ***** -->
  <section class="doc-section" id="codigos">
    <div class="container">
      <div class="doc-heading">
        <span class="api-kicker">API_PAYMENTS</span>
        <h2>Códigos, funciones y permisos</h2>
        <p>Estos son los códigos generales generados por <strong>API_PAYMENTS</strong> y las funciones admitidas por <strong>/api/Payments_V1</strong>.</p>
      </div>
      <div class="row">
        <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
          <div class="row">
            <table class="table table-responsive justify">
              <tr>
                <td><strong>CÓDIGOS</strong></td>
                <td><strong>DESCRIPCIÓN</strong></td>
              </tr>
              <tr><td>200</td><td style="text-align: justify;">Petición exitosa, retorna en formato JSON.</td></tr>
              <tr><td>400</td><td style="text-align: justify;">Falta algún dato necesario de los que requiere la solicitud.</td></tr>
              <tr><td>401</td><td style="text-align: justify;">Token faltante, inválido o comunicación corrupta.</td></tr>
              <tr><td>404</td><td style="text-align: justify;">Petición desconocida. Solo se admiten las claves documentadas.</td></tr>
              <tr><td>405</td><td style="text-align: justify;">El método HTTP es distinto a <strong>POST</strong>.</td></tr>
              <tr><td>412</td><td style="text-align: justify;">El cliente ya está registrado con el producto seleccionado.</td></tr>
              <tr><td>417</td><td style="text-align: justify;">La CURP pertenece a persona fallecida o no existe.</td></tr>
              <tr><td>418</td><td style="text-align: justify;">Tiempo de operación excedido para este TOKEN.</td></tr>
            </table>
          </div>
        </div>

        <div class="col-lg-1 col-md-12 col-sm-12 align-self-center"></div>

        <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
          <div class="row">
            <table class="table table-responsive justify">
              <tr>
                <td><strong>CLAVES DE FUNCIONES</strong></td>
                <td><strong>DESCRIPCIÓN</strong></td>
              </tr>
              <tr>
                <td>token_full</td>
                <td style="text-align: justify;">
                  Acceso único a todas las API KASU. Cada token se emite con permisos finos y
                  <strong>activamos de forma selectiva</strong> las funcionalidades asignadas a ese token.
                </td>
              </tr>
              <tr><td>account_status</td><td style="text-align: justify;">Consulta saldo, pago del periodo, mora y estado de cobranza.</td></tr>
              <tr><td>pagos_psd2</td><td style="text-align: justify;">Registra un pago y aplica primero mora cuando corresponde.</td></tr>
            </table>
          </div>

          <!-- Alcance del token y funcionalidades -->
          <div class="row" style="margin-top:18px;">
            <div class="col-12">
              <h3 class="titulos"><strong>ALCANCE DEL TOKEN Y PERMISOS</strong></h3>
              <p style="text-align: justify;">
                Al generar <code>token_full</code> se crea una credencial única. Sobre esa credencial
                habilitamos las capacidades listadas abajo. Si una capacidad no está asociada a tu token,
                la API responderá con error de autorización o alcance insuficiente.
              </p>
            </div>

            <div class="col-12">
              <h4 class="titulos" style="margin-top:8px;"><strong>Funcionalidades habilitables — Lado Usuario</strong></h4>
              <table class="table table-responsive justify">
                <tr><td><strong>Ref Func</strong></td><td><strong>Funcionalidad</strong></td></tr>
                <tr><td>1</td><td>Permite consultar el <strong>saldo total</strong> de la póliza.</td></tr>
                <tr><td>2</td><td>Permite consultar el <strong>pago del periodo</strong>.</td></tr>
                <tr><td>3</td><td>Permite consultar la <strong>comisión del pago del periodo</strong>.</td></tr>
                <tr><td>4</td><td>Permite <strong>registrar el pago</strong> de un cliente.</td></tr>
              </table>
            </div>

            <div class="col-12">
              <h4 class="titulos" style="margin-top:8px;"><strong>Resolución interna — Lado KASU</strong></h4>
              <table class="table table-responsive justify">
                <tr><td><strong>Ref Func</strong></td><td><strong>Operación interna</strong></td></tr>
                <tr><td>4</td><td>Registra el pago del cliente en el sistema transaccional.</td></tr>
                <tr><td>4</td><td>Asigna la comisión generada al prospecto según la <strong>tabla de productos</strong>.</td></tr>
                <tr><td>1</td><td>Consulta la póliza por <strong>Número de Póliza</strong> y calcula el saldo total.</td></tr>
                <tr><td>2</td><td>Consulta la póliza por <strong>Número de Póliza</strong> y determina el pago del periodo.</td></tr>
                <tr><td>3</td><td>Consulta en <strong>Productos</strong> la comisión aplicable y la devuelve desglosada.</td></tr>
              </table>
              <p style="text-align: justify;">
                Nota: La activación de cada Ref Func se liga al token emitido. Podrás verificar tus permisos
                activos en la respuesta del endpoint de autenticación o con tu ejecutivo de integración.
              </p>
            </div>
          </div>
          <!-- /Alcance del token -->
        </div>
      </div>
    </div>
  </section>
  
  <section class="doc-section doc-section--muted" id="ejemplos">
    <div class="container">
      <div class="doc-heading">
        <h2>Ejemplos de consumo</h2>
        <p>Ambas operaciones requieren <strong>curp_en_uso</strong>, <strong>poliza_en_uso</strong>, Bearer token y <strong>token_data</strong>.</p>
      </div>
      <div class="doc-grid">
        <div class="doc-panel">
          <span class="doc-pill">account_status</span>
          <p>Consulta saldo, pago de periodo, mora, pagos realizados, pagos pendientes, comisión y liga de pago.</p>
          <div class="code-window">
            <pre id="codecopi" class="userContent" style="white-space: pre-wrap;"><code>POST https://apimarket.kasu.com.mx/api/Payments_V1
Authorization: Bearer API_KEY_AQUI
Content-Type: application/json
User-Agent: SECRET_KEY_USUARIO_SECRET_KEY_ID

{
  "tipo_peticion": "account_status",
  "nombre_de_usuario": "YOUR_APPUSER",
  "curp_en_uso": "CURP_CODE",
  "poliza_en_uso": "POLIZA",
  "token_data": {
    "timestamp": TIMESTAMP,
    "expires_in": EXPIRE_IN
  }
}</code></pre>
          </div>
        </div>
        <div class="doc-panel">
          <span class="doc-pill">pagos_psd2</span>
          <p>Registra un pago. Si existe mora, la API aplica primero la mora y después el abono principal.</p>
          <div class="code-window">
            <pre id="codecopindex" class="userContent" style="white-space: pre-wrap;"><code>POST https://apimarket.kasu.com.mx/api/Payments_V1
Authorization: Bearer API_KEY_AQUI
Content-Type: application/json
User-Agent: SECRET_KEY_USUARIO_SECRET_KEY_ID

{
  "tipo_peticion": "pagos_psd2",
  "nombre_de_usuario": "YOUR_APPUSER",
  "curp_en_uso": "CURP_CODE",
  "poliza_en_uso": "POLIZA",
  "cantidad": 850.00,
  "metodo": "API_PAYMENTS",
  "referencia": 123,
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

  <!-- ***** Features Small Start ***** -->
  <?php require_once '../html/Sandbox.php'; ?>


  <!-- ***** Features Small Start ***** -->
  <section class="doc-section" id="otros">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-lg-12">
              <div class="center-heading">
                <h2 class="section-title">Otras APIs que te pueden interesar</h2>
                <br>
              </div>
            </div>
            <?php require_once '../html/select_api.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ***** Features Small End ***** -->

  <footer>
    <?php require_once '../html/footer.php'; ?>
  </footer>

  <!-- Copiar -->
  <script>
  function copiarAlPortapapeles(codecopi) {
    var aux = document.createElement("input");
    aux.setAttribute("value", document.getElementById(codecopi).innerHTML);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);
  }
  </script>

  <!-- jQuery -->
  <script src="https://kasu.com.mx/assets/js/jquery-2.1.0.min.js"></script>
  <!-- Bootstrap -->
  <script src="https://kasu.com.mx/assets/js/bootstrap.min.js"></script>
  <!-- Plugins -->
  <script src="https://kasu.com.mx/assets/js/scrollreveal.min.js"></script>
  <!-- Global Init -->
  <script src="https://kasu.com.mx/assets/js/custom.js"></script>
</body>
</html>
