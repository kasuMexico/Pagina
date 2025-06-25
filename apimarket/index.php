<?php
// Incluye la librería de funciones y conexiones (asegúrate de que la ruta es correcta)
require_once 'librerias_api.php';
// Mostrar alerta si hay mensaje GET

if (isset($_GET['Msg'])) {
    echo "<script type='text/javascript'>alert('".htmlspecialchars($_GET['Msg'])."');</script>";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-MH6TN5Z');
    </script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="KASU Te permite conectar tu plataforma Web o Movil a nuestras bases de Datos">
    <meta name="author" content="Erendida Itzel Castro Marquez y Jose Carlos Cabrera Monroy">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU | Apimarket</title>
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/codigo.css">
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MH6TN5Z"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="height:auto; padding:1em;">
                <div id="datos"></div>
            </div>
        </div>
    </div>
    <!-- ***** Header Area Start ***** -->
    <header class="header-area header-sticky">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav class="main-nav">
                        <!-- Logo -->
                        <a href="#" class="logo">
                            <img src="https://kasu.com.mx/assets/images/kasu_logo.jpeg" alt="Logo Kasu"/>
                        </a>
                        <!-- Menu -->
                        <ul class="nav" >
                            <li><a style="color: black;" href="https://kasu.com.mx/" class="comprar">KASU</a></li>
                            <li><a style="color: black;" href="#apikasu">Documentación</a></li>
                            <li><a style="color: black;" href="#contact-us">Contactános</a></li>
                        </ul>
                        <a class='menu-trigger'>
                            <span>Menú</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <div class="welcome-area">
        <div class="header-text">
            <div class="container">
                <div class="row">
                    <div class="offset-xl-3 col-xl-6 offset-lg-2 col-lg-8 col-md-12 col-sm-12">
                        <h1 style="color:white">Todas las oportunidades del open banking a tu alcance <strong>Apimarket_KASU</strong></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Descripción General de la API -->
    <section class="section padding-top-70" id="">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
            <div class="center-heading">
              <div class="count-item decoration-bottom">
                  <h2 class="section-title">
                    <strong>
                        <?php
                        // Contador de clientes activos
                        echo number_format($basicas->MaxDat($mysqli, "Id", "Venta"), 0, ".", ",");
                        ?>
                    </strong>
                    <span> Clientes Activos</span>
                  </h2>
              </div>
              <p style="text-align: justify;"><strong>KASU</strong> es una plataforma que cuenta con un entorno de gestión robusto que permite a los usuarios realizar la compra de pequeñas partes de fideicomisos que sirven como ahorro para afrontar situaciones difíciles en su vida, tales como gastos funerarios, enviar a sus hijos a la universidad, crear fondos para el retiro y envío y recepción de remesas.</p>
            </div>
          </div>
          <div class="col-lg-2 col-md-12 col-sm-12 align-self-center" ></div>
          <div class="col-lg-4 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
            <div class="row">
              <div class="features-small-item">
                <div class="center-heading">
                  <h2 class="section-title">Usa el entorno KASU</h2>
                </div>
                <div class="center-text" id="apikasu">
                  <p style="text-align: justify;"><strong>1.- </strong>Comercializa nuestros servicios y recibe interesantes comisiones por ello.</p>
                  <p style="text-align: justify;"><strong>2.- </strong>Recibe los pagos que nuestros clientes tienen que hacer sobre los servicios de KASU y obtén una comisión por cada peso que cobres.</p>
                  <p style="text-align: justify;"><strong>3.- </strong>Realiza validaciones de datos de clientes con los datos de los clientes de KASU.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- Selecciona la API -->
    <section class="section colored padding-top-70" id="Ventajas">
      <div class="col-lg-12">
          <div class="center-heading">
              <br><br>
              <h2 class="section-title">Selecciona la <strong>API's</strong> que mejor se adapte a tus necesidades</h2>
          </div>
      </div>
      <br>
      <?php require_once 'html/select_api.php'; ?>
    </section>
    <!-- Sección de usabilidad general -->
    <section class="section padding-top-70">
      <div class="container">
        <div class="Consulta">
            <h2 class="titulos"><strong>USABILIDAD GENERAL</strong></h2>
            <br>
            <p>Las <strong>API</strong> que hemos desarrollado para ti cuentan con una usabilidad formada por bloques que pueden comunicarse entre sí o intercambiar información generada en un bloque para interactuar en cualquier otro.</p>
            <br>
            <p>Solo recuerda que debes tener permisos para cada una de nuestras <strong>API</strong> verticales.</p>
            <br>
        </div>
        <div class="table-container">
            <table class="table">
                <tbody>
                    <tr>
                        <td class="blue" style="padding: 25px;"><h2><strong>API_CUSTOMER</strong></h2></td>
                        <td class="red" style="padding: 25px;"><h2><strong>API_PAYMENTS</strong></h2></td>
                        <td class="purple" style="padding: 25px;"><h2><strong>API_ACCOUNTS</strong></h2></td>
                    </tr>
                    <tr>
                        <td colspan="3" class="green">
                            <h2><strong>token_full</strong></h2>
                            <p>Consulta que retorna un token de acceso para todas las API_KASU...</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="blue">
                            <h2><strong>request</strong></h2>
                            <br><p>Te muestra los códigos para las búsquedas por bloques o individuales de datos...</p>
                        </td>
                        <td class="red">
                            <h2><strong>account_status</strong></h2>
                            <br><p>Consulta el estado de cuenta de un cliente, pagos, pendiente y moras...</p>
                        </td>
                        <td class="purple">
                            <h2><strong>new_service</strong></h2>
                            <br><p>Te permite realizar el registro de un servicio <strong>KASU</strong>...</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="blue">
                            <h2><strong>individual_request</strong></h2>
                            <br><p>Te muestra los datos individuales de clientes, productos o pólizas...</p>
                        </td>
                        <td class="red">
                            <h2><strong>pagos_psd2</strong></h2>
                            <br><p>Realiza el cobro de un servicio <strong>KASU</strong> y genera una comisión...</p>
                        </td>
                        <td class="purple">
                            <h2><strong>modify_record</strong></h2>
                            <br><p>Te permite realizar modificaciones al registro de un cliente...</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="blue">
                            <h2><strong>request_block</strong></h2>
                            <br><p>Te muestra por bloques los datos de los usuarios...</p>
                        </td>
                        <td class="red">
                            <h2><strong>envios_swift</strong></h2>
                            <br><p>Realiza la entrega de envíos que reciban tus clientes desde <strong>Estados Unidos</strong>...</p>
                        </td>
                        <td class="purple"></td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
    </section>
    <!-- Autenticación -->
    <?php require_once 'html/Autenticacion.php'; ?>
    <!-- Opiniones de clientes y productos -->
    <section class="section">
        <div class="col-lg-12">
            <div class="center-heading">
                <br><br>
                <h2 class="section-title">Conoce los productos <strong>KASU</strong> y ofrécelos a tus clientes</h2>
            </div>
        </div>
        <br>
        <div class="container">
            <div class="row">
                <!-- Aquí puedes insertar tu código para mostrar productos u opiniones -->

                
            </div>
        </div>
    </section>
    <!-- Contacto -->
    <section class="section colored" id="contact-us">
        <div class="container">
            <br><br>
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <h2 class="margin-bottom-30">Contáctanos y obtén tu Acceso</h2>
                    <br>
                    <div class="contact-text">
                        <p style="text-align: justify;">Puedes enviar un correo electrónico registrándote en este formulario y proporcionar información sobre tu caso de uso...</p>
                    </div>
                </div>
                <div class="col-lg-8 col-md-6 col-sm-12">
                    <div class="contact-form">
                        <form id="ContactoApiMarket" action="contacto.php" method="post">
                            <div class="row">
                                <div class="col-lg-6 col-md-12 col-sm-12">
                                    <fieldset>
                                        <input type="text" name="name" id="name"  class="form-control" placeholder="Nombre" required>
                                    </fieldset>
                                </div>
                                <div class="col-lg-6 col-md-12 col-sm-12">
                                    <fieldset>
                                        <input type="email" name="email" id="email" class="form-control"  placeholder="Correo" required>
                                    </fieldset>
                                </div>
                                <div class="col-lg-12">
                                    <fieldset>
                                        <textarea name="message" id="message" rows="6" class="form-control" placeholder="Coméntanos brevemente en qué usarás las API's de KASU" required></textarea>
                                    </fieldset>
                                </div>
                                <div class="col-lg-12">
                                    <fieldset>
                                        <button type="submit" id="Enviar" class="main-button">Enviar</button>
                                    </fieldset>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Footer -->
    <footer>
        <?php require_once('assets/footer.php'); ?>
    </footer>
    <!-- Scripts -->
    <script src="https://kasu.com.mx/assets/js/jquery-2.1.0.min.js"></script>
    <script src="https://kasu.com.mx/assets/js/bootstrap.min.js"></script>
    <script src="https://kasu.com.mx/assets/js/scrollreveal.min.js"></script>
    <script src="https://kasu.com.mx/assets/js/custom.js"></script>
</body>
</html>