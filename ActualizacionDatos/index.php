<?php
/********************************************************************************************
 * Qué hace: Landing. Instrucciones, plan de referidos y flujo de consulta.
 *           Sin sesiones con ?value=BASE64(CURP).
 *           Con value: pide No. de Póliza y envía a php/datoscurp.php.
 *           Sin value: pide CURP y envía a php/datoscurp.php.
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

date_default_timezone_set('America/Mexico_City');
header_remove('X-Powered-By');

$rawValue = $_GET['value'] ?? '';
$curp     = ($rawValue !== '') ? (string)base64_decode($rawValue, true) : '';
$curp     = is_string($curp) ? strtoupper(trim($curp)) : '';

require_once '../eia/librerias.php';
// Se establecen el número de contacto
require_once  '../eia/php/Telcto.php';

$tel = isset($tel) ? (string)$tel : '712 261 2898';

function h(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>KASU | Actualizar datos de póliza</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Actualiza los datos de tu servicio de gastos funerarios de KASU">
  <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">

  <!-- CSS existentes -->
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/font-awesome.css">
  <link rel="stylesheet" href="../assets/css/templatemo-softy-pinko.css">

  <!-- Fix layout: evita encimados -->
  <style>
    /* Contenedor centrado y con ancho controlado */
    .auth-wrap { max-width: 520px; margin: 32px auto; }
    /* Card limpia y apilable */
    .features-small-item {
      display: block; position: relative; overflow: hidden;
      border-radius: 16px; box-shadow: 0 12px 28px rgba(0,0,0,.08);
      background: #fff; padding: 28px 24px;
    }
    .features-small-item .icon img { display:block; width:72px; height:auto; margin: 0 auto 8px; }
    .features-title { text-align:center; margin: 8px 0 0; font-weight:600; }
    .pricing-body { text-align:center; margin: 12px 0 18px; }
    .pricing-body hr { margin: 12px 0 18px; }
    .consulta label { font-weight:600; margin-top:10px; }
    .main-button-slider,
    .btn-primary {
      display:inline-block; border:0; border-radius: 22px; padding: 10px 20px;
      background:#012F91; color:#fff; text-transform:uppercase; font-size:14px; cursor:pointer;
    }
    .muted-link { color:#911F66; }
    /* Evita transformaciones de scrollreveal en estos paneles */
    .no-reveal { transform:none !important; opacity:1 !important; }
  </style>
</head>
<body>

  <!-- Modal: Instrucciones -->
  <div id="Instruccion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="padding:1em;">
        <h5 class="text-center mb-2">Ingreso a mi cuenta</h5>
        <p class="text-center mb-2">KASU</p>
        <ol style="padding-left:22px;">
          <li>Ten a la mano tu CURP y Póliza</li>
          <li>Captura los datos solicitados</li>
          <li>Haz clic en <b>Continuar</b></li>
        </ol>
        <p class="text-center" style="font-size:12px;">
          <b>NOTA:</b> Atención personalizada:
          <a href="tel:<?php echo h($tel); ?>" class="muted-link"><?php echo h($tel); ?></a>
        </p>
        <div class="text-center">
          <a class="main-button-slider" href="#" data-dismiss="modal">¡Entiendo!</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Reposición -->
  <div id="ActIndMod" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="padding:1em;">
        <h5 class="text-center mb-3">Reposición de la tarjeta</h5>
        <ol style="padding-left:22px;font-size:15px;">
          <li>Da clic en <b>PAGAR</b></li>
          <li>Ingresa tu CURP y actualiza tus datos</li>
          <li>Espera la tarjeta en tu domicilio</li>
        </ol>
        <p class="text-center" style="font-size:12px;">
          <b>NOTA:</b> Si no has recibido tu tarjeta, comunícate al
          <a href="tel:<?php echo h($tel); ?>" class="muted-link"><?php echo h($tel); ?></a>
        </p>
        <div class="text-center">
          <a class="main-button-slider" href="https://www.mercadopago.com.mx/checkout/v1/redirect?pref_id=292541305-e4f4df73-94a8-43ee-9f50-fc235cb29cf1">Pagar</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Sección principal -->
  <section class="section" id="Clientes">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 auth-wrap">

          <!-- Panel único: o captura CURP o CURP+Póliza -->
          <div class="features-small-item no-reveal">
            <div class="icon">
              <img src="../assets/images/Index/florkasu.png" alt="Kasu Logo">
            </div>
            <h1 class="features-title">Ingresar a mi cuenta</h1>
            
            <div class="pricing-body">
              </br>
              <hr>
              <h2 class="mb-1">Plan de Referidos</h2>
              <p class="mb-1">Recuerda que por cada cliente efectivo que refieras para cualquier producto KASU, puedes obtener</p>
              <h2>Hasta $300 MXN</h2>
              <hr>
            </div>

            <?php if ($curp === ''): ?>
              <!-- Form: solo CURP -->
              <form method="POST" id="FormCurp" action="php/datos.php" autocomplete="off" novalidate class="consulta">
                <label for="txtCurp_St1">Ingresa tu CURP</label>
                <input
                  type="text"
                  class="form-control"
                  id="txtCurp_St1"
                  name="txtCurp_St1"
                  maxlength="18"
                  inputmode="latin"
                  pattern="^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]{2}$"
                  placeholder="AAAA000000HAAAXX"
                  required
                >
                <!-- honeypot -->
                <input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;">
                <div class="text-center mt-3">
                  <button type="submit" id="btnVerCur" name="btnVerCur" class="main-button-slider">Continuar</button>
                </div>
              </form>

            <?php else: ?>
              <!-- Form: CURP fija + Póliza -->
              <form method="POST" id="FormCurpPoliza" action="php/datos.php" autocomplete="off" novalidate class="consulta">
                <label>CURP</label>
                <input type="text" class="form-control" value="<?php echo h($curp); ?>" disabled>
                <input type="hidden" name="txtCurp_ActIndCli" value="<?php echo h($curp); ?>">

                <label for="txtNumTarjeta_ActIndCli" class="mt-3">No. de Póliza</label>
                <input
                  type="text"
                  class="form-control"
                  id="txtNumTarjeta_ActIndCli"
                  name="txtNumTarjeta_ActIndCli"
                  maxlength="20"
                  placeholder="Ej. ABCD1234"
                  required
                >
                <!-- honeypot -->
                <input type="text" name="hp" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;">

                <div class="text-center mt-3">
                  <button type="submit" id="btnConsultar_ActIndCli" name="btnConsultar_ActIndCli" class="main-button-slider">Consultar</button>
                </div>

              </form>
            <?php endif; ?>

          </div><!-- /.features-small-item -->

        </div><!-- /.auth-wrap -->
      </div>
    </div>
  </section>

  <!-- JS -->
  <script src="../assets/js/jquery-2.1.0.min.js"></script>
  <script src="../assets/js/popper.js"></script>
  <script src="../assets/js/bootstrap.min.js"></script>
  <script src="../assets/js/scrollreveal.min.js"></script>
  <script src="../assets/js/waypoints.min.js"></script>
  <script src="../assets/js/jquery.counterup.min.js"></script>
  <script src="../assets/js/imgfix.min.js"></script>
  <script src="../assets/js/custom.js"></script>
  <script>
    // Modal de instrucciones al cargar
    if (window.jQuery) { jQuery(function($){ $("#Instruccion").modal("show"); }); }
    // Normaliza CURP a mayúsculas y sin caracteres extra
    (function(){
      var curp = document.getElementById('txtCurp_St1');
      if (!curp) return;
      curp.addEventListener('input', function () {
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'').slice(0,18);
      });
    })();
  </script>
</body>
</html>