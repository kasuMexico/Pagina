<?php
// Sesión
session_start();
// Google Tag Manager
require_once __DIR__ . '/eia/analytics_bootstrap.php';

// Normaliza parámetro
$ser = isset($_GET['Ser']) ? (string)$_GET['Ser'] : '';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="KASU es una empresa dedicada a prestar servicios a futuro. Con plataformas tecnológicas acercamos servicios a comunidades alejadas.">
  <meta name="author" content="Erendida Itzel Castro Márquez">
  <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
  <link rel="icon" href="assets/images/logo.png">
  <title>KASU | Preguntas frecuentes</title>

  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
  <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
  <!-- Modal -->
  <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content" style="height:auto; padding:1em;">
        <div id="datos"></div>
      </div>
    </div>
  </div>

  <!-- Header -->
  <?php require_once 'html/MenuPrincipal.php'; ?>

  <br><br><br><br>

  <section class="section colored" id="pricing-plans">
    <div class="container">
      <div class="row">
        <?php if ($ser === 'UNIVERSITARIO BASE'): ?>
          <!-- ***** UNIVERSITARIO BASE ***** -->
          <div class="pricing-item">
            <div class="pricing-header"></div>
            <div class="pricing-body">
              <i><img style="height:80px;" src="assets/images/Index/funer.png" alt="Servicio funerario"></i>
              <br><br>
              <h6><strong>Servicio de gastos funerarios</strong></h6>
              <br><br>

              <div class="dudasfun">
                <div class="container">
                  <h1 class="hd-tit">CONTRATO DE PRESTACIÓN DE SERVICIOS</h1>
                  <h2 class="hd-sub">ANTECEDENTES</h2>

                  <p style="text-align:justify;">
                    Mediante el contrato de fideicomiso protocolizado en fecha veinte (20) de mayo de dos mil dieciséis (2016) denominado, a partir de este y para el presente, como <strong>FIDEICOMISO F/0009</strong>, se nombró a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> como fideicomitente y fideicomisario en tercer lugar y a <strong>CAPITAL &amp; FONDEO MÉXICO S.A. DE C.V. SOFOM ENR</strong> como fiduciaria y fideicomisaria en primer lugar. Mediante contrato de cesión de aportaciones firmado por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> y <strong>CAPITAL &amp; FONDEO MÉXICO S.A. DE C.V. SOFOM ENR</strong>, se protocolizan las solicitudes de acceso al <strong>FIDEICOMISO F/0009</strong> y, con su aceptación, se nombra a toda persona descrita en el contrato de aportación como fideicomisario en segundo lugar.
                  </p>

                  <p style="text-align:justify;">Por lo que, al momento de firmarse el contrato, <strong>EL CLIENTE</strong> será:</p>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;">
                      <strong>A)</strong> Acreedor a los beneficios que el fideicomiso señala, tomando en consideración los servicios específicos en la solicitud de <strong>SERVICIO A FUTURO</strong> de cada <strong>CLIENTE</strong>.
                    </li>
                    <li style="text-align:justify;">
                      <strong>B)</strong> Las aportaciones de cada <strong>CLIENTE</strong> al mencionado <strong>FIDEICOMISO F/0009</strong> son documentadas por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> mediante un recibo impreso o digital en el que se especificará:
                      <ul class="hd-entrada">
                        <li style="text-align:justify;">Contrato de cesión al cual pertenece <strong>EL CLIENTE</strong>.</li>
                        <li style="text-align:justify;">Nombre de <strong>EL CLIENTE</strong>.</li>
                        <li style="text-align:justify;">Clave Única de Registro de Población de <strong>EL CLIENTE</strong>.</li>
                        <li style="text-align:justify;">Contrato de servicio a futuro de <strong>EL CLIENTE</strong>.</li>
                        <li style="text-align:justify;">Recibo de depósito de valor unitario de servicio a futuro de <strong>EL CLIENTE</strong>.</li>
                      </ul>
                    </li>
                    <li style="text-align:justify;">
                      <strong>C)</strong> El contrato <strong>FIDEICOMISO F/0009</strong> especifica que <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> será el único distribuidor y comercializador autorizado para realizar los contratos de aportación descritos.
                    </li>
                  </ul>

                  <h2 class="hd-sub"><strong>DECLARACIONES</strong></h2>

                  <p style="text-align:justify;">
                    Declara la sociedad denominada <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>, a través de su presentante:
                  </p>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;">
                      Que es una sociedad constituida al amparo de las leyes mexicanas a partir del día veintidós (22) de octubre de dos mil veinte (2020), otorgada ante la fe de la Lic. <strong>Norma Vélez Bautista</strong>, titular de la Notaría Pública número <strong>83</strong> del Estado de México, con residencia en Atlacomulco, mediante escritura pública número <strong>38,169</strong>.
                    </li>
                    <li style="text-align:justify;">
                      Cuenta con Registro Federal de Contribuyentes: <strong>KSF201022441</strong> (ajuste si aplica).
                    </li>
                    <li style="text-align:justify;">
                      Su representante cuenta con facultades suficientes para celebrar el presente contrato en su nombre y representación, según consta en la escritura pública número <strong>38,160</strong> de fecha veinte (20) de octubre de dos mil veinte (2020), ante la misma notaría.
                    </li>
                    <li style="text-align:justify;">En este acto se constituye como <strong>PRESTADOR DE SERVICIOS</strong>.</li>
                    <li style="text-align:justify;">No ha iniciado, ni se tiene conocimiento de que se haya iniciado, procedimiento alguno tendiente a declararla en concurso mercantil, estado de insolvencia o liquidación.</li>
                    <li style="text-align:justify;">
                      No tiene conocimiento de acción o procedimiento ante autoridad que:
                      <ul class="hd-entrada">
                        <li style="text-align:justify;">Afecte o pueda afectar la validez o exigibilidad del presente contrato o documentos relacionados.</li>
                        <li style="text-align:justify;">Pueda anular o impedir la transmisión de los derechos de cobro cedidos al patrimonio del fideicomiso.</li>
                        <li style="text-align:justify;">Pueda impugnar o impedir emisiones o reaperturas subsecuentes.</li>
                        <li style="text-align:justify;">Impida conducir su negocio conforme a las leyes aplicables y permisos correspondientes.</li>
                      </ul>
                    </li>
                  </ul>

                  <p style="text-align:justify;"><strong>EL CLIENTE</strong> declara por su cuenta:</p>
                  <ul class="hd-entrada">
                    <li style="text-align:justify;">
                      Que es de su interés firmar la solicitud de ingreso al <strong>FIDEICOMISO F/0009</strong> firmado entre <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> y <strong>CAPITAL &amp; FONDEO MÉXICO S.A. DE C.V. SOFOM ENR</strong>.
                    </li>
                    <li style="text-align:justify;">Que conoce los alcances del fideicomiso y las responsabilidades correspondientes.</li>
                    <li style="text-align:justify;">Que sus datos personales serán registrados de forma digital en <strong>www.kasu.com.mx</strong>, conforme al aviso de privacidad.</li>
                    <li style="text-align:justify;">Autoriza compartir su información con empresas relacionadas para operación y calidad del servicio, conforme a la regulación aplicable.</li>
                  </ul>

                  <h2 class="hd-sub"><strong>CLÁUSULAS</strong></h2>

                  <p style="text-align:justify;">
                    <strong>PRIMERA. OBJETO.</strong> El presente contrato especifica los medios por los cuales <strong>EL CLIENTE</strong> accede a los beneficios amparados con el patrimonio del <strong>FIDEICOMISO F/0009</strong>, ya sea por cuenta propia, por empresas controladas o por terceros subcontratados, según lo establezcan <strong>EL CLIENTE</strong> y/o el <strong>BENEFICIARIO</strong> en la base de datos de <strong>www.kasu.com.mx</strong>.
                  </p>

                  <p style="text-align:justify;">
                    <strong>SEGUNDA. SERVICIOS.</strong> La prestación está sujeta a condiciones y medios de ejecución que se detallan en los anexos del servicio contratado.
                  </p>

                  <p style="text-align:justify;">
                    <strong>TERCERA. DURACIÓN.</strong> Indefinida. La vigencia se activa una vez cubierto el pago total del servicio y transcurridos diez (10) años naturales posteriores a la liquidación del servicio.
                  </p>

                  <p style="text-align:justify;">
                    <strong>CUARTA. MONEDA.</strong> Pesos mexicanos, conforme a la Ley Monetaria vigente. Las obligaciones se cumplirán al tipo de cambio publicado por Banco de México en el DOF en la fecha de pago.
                  </p>
                </div>

                <br>
                <div class="container">
                  <p style="text-align:justify;">
                    <strong>QUINTA. RESCISIÓN.</strong> En caso de incumplimiento, cualquiera de las partes podrá rescindir de pleno derecho mediante aviso por escrito. En rescisión, <strong>EL CLIENTE</strong> pagará comisiones pendientes a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>. No habrá reembolsos de cantidades abonadas, salvo lo indicado para servicio universitario respecto de comisiones del grupo. El contrato terminará automáticamente por: <strong>a)</strong> fallecimiento de <strong>EL CLIENTE</strong>; <strong>b)</strong> omisión de pago mayor a 60 días naturales o tres atrasos; <strong>c)</strong> incumplimiento; <strong>d)</strong> acuerdo de las partes.
                  </p>

                  <p style="text-align:justify;">
                    <strong>SEXTA. CESIÓN DE DERECHOS.</strong> Los derechos de cobro podrán ser cedidos por <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong>. La obligación de prestación podrá ejecutarse por empresas integradas o terceros autorizados.
                  </p>

                  <p style="text-align:justify;">
                    <strong>SÉPTIMA. REGULACIÓN.</strong> Para interpretación se estará a la Ley General de Títulos y Operaciones de Crédito. En caso de controversia, tribunales competentes, renunciando a cualquier otro fuero.
                  </p>

                  <p style="text-align:justify;">
                    <strong>OCTAVA. LUGAR DE PAGO.</strong> En oficinas de <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> o mediante cargo automático a cuenta/tarjeta autorizada. El estado de cuenta hará prueba de pago mientras exista saldo suficiente.
                  </p>

                  <p style="text-align:justify;">
                    <strong>NOVENA. NOTIFICACIONES.</strong> Cualquier comunicación será por escrito al domicilio de la institución. <strong>KASU</strong> notificará cambios de domicilio dentro de diez días naturales. <strong>EL CLIENTE</strong> notificará actos jurídicos relevantes con veinte días hábiles de anticipación. La omisión podrá afectar la vigencia del contrato.
                  </p>

                  <p style="text-align:justify;">
                    <strong>DÉCIMA. ACEPTACIÓN.</strong> Al recibir la tarjeta, <strong>EL CLIENTE</strong> reconoce conocer la operativa del servicio y sus responsabilidades.
                  </p>

                  <p style="text-align:justify;">
                    <strong>DÉCIMA PRIMERA. AUTONOMÍA.</strong> La invalidez de alguna disposición no afectará la validez del resto.
                  </p>

                  <p style="text-align:justify;">
                    <strong>DÉCIMA SEGUNDA. JURISDICCIÓN Y COMPETENCIA.</strong> Las partes se someten a los tribunales competentes del lugar de suscripción o de la Ciudad de México, renunciando a cualquier otro fuero.
                  </p>
                </div>
              </div>
            </div>
          </div>

        <?php elseif ($ser === 'UNIVERSITARIO'): ?>
          <!-- ***** UNIVERSITARIO ***** -->
          <div class="pricing-item">
            <div class="pricing-body">
              <i><img style="height:80px;" src="/assets/images/Index/universitario.png" alt="Servicio universitario"></i>
              <br><br>
              <h6><strong>Servicio de Inversión universitaria</strong></h6>
              <br><br>

              <div class="dudasuni">
                <div class="container">
                  <h1 class="hd-tit">CONTRATO DE PRESTACIÓN DE SERVICIOS FUNERARIOS A FUTURO</h1>
                  <h2 class="hd-sub">ANTECEDENTES</h2>

                  <p style="text-align:justify;">
                    Mediante el contrato de fideicomiso protocolizado en fecha veinte (20) de mayo de dos mil dieciséis (2016) denominado <strong>FIDEICOMISO F/0003</strong>, se nombró a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> como fideicomitente y fideicomisario en tercer lugar y a <strong>CAPITAL &amp; FONDEO MÉXICO S.A. DE C.V. SOFOM ENR</strong> como fiduciaria y fideicomisaria en primer lugar. Por contrato de cesión de aportaciones entre ambas, se protocolizan solicitudes de acceso al <strong>FIDEICOMISO F/0003</strong>, nombrando como fideicomisario en segundo lugar a la persona descrita en el contrato de aportación.
                  </p>

                  <p style="text-align:justify;">Por lo que, al momento de firmarse el contrato, <strong>EL CLIENTE</strong> será:</p>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;"><strong>A)</strong> Acreedor a los beneficios del fideicomiso conforme a la solicitud de <strong>SERVICIO A FUTURO</strong>.</li>
                    <li style="text-align:justify;">
                      <strong>B)</strong> Las aportaciones se documentan mediante recibo impreso o digital con:
                      <ul class="hd-entrada">
                        <li>Contrato de cesión correspondiente.</li>
                        <li>Nombre de <strong>EL CLIENTE</strong>.</li>
                        <li>CURP de <strong>EL CLIENTE</strong>.</li>
                        <li>Contrato de servicio a futuro.</li>
                        <li>Recibo de depósito del valor unitario del servicio a futuro.</li>
                      </ul>
                    </li>
                    <li style="text-align:justify;"><strong>C)</strong> <strong>KASU</strong> es el distribuidor y comercializador autorizado.</li>
                  </ul>

                  <h2 class="hd-sub"><strong>DECLARACIONES</strong></h2>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;">
                      Sociedad constituida el 22/10/2020 ante la Notaría 83 del Edo. de México, Titular: Lic. Norma Vélez Bautista. Escritura 38,169.
                    </li>
                    <li style="text-align:justify;">RFC: <strong>KSF201022441</strong> (ajuste si aplica).</li>
                    <li style="text-align:justify;">
                      Facultades del representante: escritura 38,160 del 20/10/2020, Notaría 83.
                    </li>
                    <li style="text-align:justify;">Se constituye como <strong>PRESTADOR DE SERVICIOS</strong>.</li>
                    <li style="text-align:justify;">Sin procedimientos de insolvencia o liquidación.</li>
                    <li style="text-align:justify;">
                      Sin acciones que afecten validez o exigibilidad del contrato, transmisión de derechos, o emisiones subsecuentes.
                    </li>
                  </ul>

                  <h2 class="hd-sub"><strong>CLÁUSULAS</strong></h2>

                  <p style="text-align:justify;">
                    <strong>PRIMERA. OBJETO.</strong> Acceso a beneficios del <strong>FIDEICOMISO F/0003</strong> por cuenta propia, empresas relacionadas o terceros.
                  </p>

                  <p style="text-align:justify;"><strong>SEGUNDA. SERVICIOS.</strong></p>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;"><strong>SERVICIO FUNERARIO.</strong> Incluye:</li>
                    <li style="text-align:justify;">
                      <strong>Traslado:</strong> Radio máximo de 60 km desde la agencia funeraria o el domicilio del cliente. Kilómetros excedentes a cargo de la familia.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Sala de velación:</strong> Capacidad mínima de 50 personas, con área de cafetería y cinco cajones de estacionamiento. Equipo de velación con porta ataúd.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Cafetería:</strong> 50 lonches que incluyan café o té, galletas, agua y sándwiches.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Equipo de velación:</strong> Floreros y equipo en comodato. Recuperación de insumos por parte de la agencia.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Acondicionamiento del cuerpo:</strong> Embalsamado (si procede), maquillaje y mortaja.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Cremación:</strong> Por cuenta propia o terceros subcontratados.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Ataúd:</strong> Para inhumación se entrega; para cremación se proporciona en comodato y se recupera.
                    </li>
                    <li style="text-align:justify;">
                      <strong>Trámites:</strong> Gestión ante autoridad sanitaria, MP u hospital según aplique.
                    </li>
                  </ul>

                  <p style="text-align:justify;">
                    <strong>TERCERA. DURACIÓN.</strong> Indefinida. Vigente 30 días naturales posteriores a la liquidación del servicio.
                  </p>

                  <p style="text-align:justify;">
                    <strong>CUARTA. MONEDA.</strong> Pesos mexicanos; tipo de cambio DOF de Banco de México en fecha de pago.
                  </p>

                  <p style="text-align:justify;">
                    <strong>QUINTA. RESCISIÓN.</strong> Incumplimiento rescinde de pleno derecho con aviso escrito. No hay reembolso de cantidades abonadas, salvo lo relativo a comisiones del servicio universitario. Causas automáticas: fallecimiento, mora >60 días o 3 atrasos, incumplimiento, acuerdo de partes.
                  </p>

                  <p style="text-align:justify;">
                    <strong>SEXTA. CESIÓN.</strong> Derechos de cobro podrán cederse; la prestación puede ejecutarse por empresas integradas o terceros.
                  </p>

                  <p style="text-align:justify;">
                    <strong>SÉPTIMA. REGULACIÓN.</strong> Ley General de Títulos y Operaciones de Crédito. Tribunales competentes; renuncia a otros fueros.
                  </p>

                  <p style="text-align:justify;">
                    <strong>OCTAVA. LUGAR DE PAGO.</strong> Oficinas de KASU o cargo automático autorizado. Estado de cuenta como prueba de pago.
                  </p>

                  <p style="text-align:justify;">
                    <strong>NOVENA. NOTIFICACIONES.</strong> Por escrito al domicilio señalado. Obligación de informar cambios y actos jurídicos relevantes.
                  </p>

                  <p style="text-align:justify;">
                    <strong>DÉCIMA. ACEPTACIÓN.</strong> Al recibir la tarjeta, <strong>EL CLIENTE</strong> reconoce procesos y responsabilidades.
                  </p>

                  <p style="text-align:justify;">
                    <strong>DÉCIMA PRIMERA. AUTONOMÍA.</strong> La nulidad parcial no afecta el resto.
                  </p>

                  <p style="text-align:justify;">
                    <strong>DÉCIMA SEGUNDA. JURISDICCIÓN.</strong> Tribunales del lugar de suscripción o de la Ciudad de México.
                  </p>
                </div>
              </div>
            </div>
          </div>

        <?php else: ?>
          <!-- ***** POR DEFECTO: GASTOS FUNERARIOS A FUTURO ***** -->
          <div class="pricing-item">
            <div class="pricing-header"></div>
            <div class="pricing-body">
              <i><img style="height:80px;" src="assets/images/Index/funer.png" alt="Servicio funerario"></i>
              <br><br>
              <h6><strong>Servicio de gastos funerarios a futuro</strong></h6>
              <br><br>

              <div class="dudasfun">
                <div class="container">
                  <h1 class="hd-tit">CONTRATO DE PRESTACIÓN DE SERVICIOS</h1>
                  <br>
                  <h2 class="hd-sub"><strong>ANTECEDENTES</strong></h2>

                  <p style="text-align:justify;">
                    Mediante el contrato de fideicomiso protocolizado en fecha veinte (20) de mayo de dos mil dieciséis (2016) denominado <strong>FIDEICOMISO F/0003</strong>, se nombró a <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> como fideicomitente y fideicomisario en tercer lugar y a <strong>CAPITAL &amp; FONDEO MÉXICO S.A. DE C.V. SOFOM ENR</strong> como fiduciaria y fideicomisaria en primer lugar. Mediante contrato de cesión de aportaciones entre ambas, se protocolizan las solicitudes de acceso al <strong>FIDEICOMISO F/0003</strong> y, con su aceptación, se nombra a la persona descrita en el contrato de aportación como fideicomisario en segundo lugar.
                  </p>

                  <p style="text-align:justify;">Por lo que, al momento de firmarse el contrato, <strong>EL CLIENTE</strong> será:</p>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;">
                      <strong>A)</strong> Acreedor a los beneficios del fideicomiso, conforme a la solicitud de <strong>SERVICIO A FUTURO</strong>.
                    </li>
                    <li style="text-align:justify;">
                      <strong>B)</strong> Sus aportaciones se documentarán mediante recibo impreso o digital con:
                      <ul class="hd-entrada">
                        <li>Contrato de cesión correspondiente.</li>
                        <li>Nombre de <strong>EL CLIENTE</strong>.</li>
                        <li>CURP de <strong>EL CLIENTE</strong>.</li>
                        <li>Contrato de servicio a futuro.</li>
                        <li>Recibo de depósito del valor unitario del servicio a futuro.</li>
                      </ul>
                    </li>
                    <li style="text-align:justify;">
                      <strong>C)</strong> <strong>KASU SERVICIOS A FUTURO, S.A. DE C.V.</strong> será el distribuidor y comercializador autorizado.
                    </li>
                  </ul>

                  <h2 class="hd-sub"><strong>DECLARACIONES</strong></h2>

                  <ul class="hd-entrada">
                    <li style="text-align:justify;">
                      Sociedad constituida el 22/10/2020. Notaría 83 del Estado de México. Escritura 38,169.
                    </li>
                    <li style="text-align:justify;">
                      RFC: <strong>KSF201022441</strong> (ajuste si aplica).
                    </li>
                    <li style="text-align:justify;">
                      Facultades del representante conforme a escritura 38,160 del 20/10/2020.
                    </li>
                    <li style="text-align:justify;">Se constituye como <strong>PRESTADOR DE SERVICIOS</strong>.</li>
                    <li style="text-align:justify;">Sin procedimientos de insolvencia.</li>
                    <li style="text-align:justify;">
                      Sin acciones que afecten la validez o exigibilidad del contrato ni la transmisión de derechos.
                    </li>
                  </ul>

                  <p style="text-align:justify;"><strong>EL CLIENTE</strong> declara por su cuenta:</p>
                  <ul class="hd-entrada">
                    <li style="text-align:justify;">Interés en ingresar al <strong>FIDEICOMISO F/0003</strong>.</li>
                    <li style="text-align:justify;">Conoce alcances y responsabilidades.</li>
                    <li style="text-align:justify;">Autoriza tratamiento de datos personales conforme al aviso de privacidad.</li>
                  </ul>

                  <h2 class="hd-sub"><strong>CLÁUSULAS</strong></h2>

                  <p style="text-align:justify;">
                    <strong>PRIMERA. OBJETO.</strong> Acceso a beneficios del <strong>FIDEICOMISO F/0003</strong>.
                  </p>
                  <p style="text-align:justify;">
                    <strong>SEGUNDA. SERVICIOS.</strong> Sujeto a condiciones y medios de ejecución según anexos del servicio.
                  </p>
                  <p style="text-align:justify;">
                    <strong>TERCERA. DURACIÓN.</strong> Indefinida. Vigente tras el pago total y conforme se establezca.
                  </p>
                  <p style="text-align:justify;">
                    <strong>CUARTA. MONEDA.</strong> Pesos mexicanos; tipo de cambio DOF vigente.
                  </p>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <?php require_once 'html/footer.php'; ?>
  </footer>

  <!-- JS -->
  <script src="assets/js/jquery-2.1.0.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  <script src="assets/js/scrollreveal.min.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>