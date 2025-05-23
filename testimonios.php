<?php
// Requerimos el archivo de librerías *JCCM
require_once 'eia/librerias.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Google Tag Manager -->
    <script>
    (function(w,d,s,l,i){
        w[l]=w[l]||[];
        w[l].push({'gtm.start': new Date().getTime(), event:'gtm.js'});
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s),
            dl = l!='dataLayer' ? '&l='+l : '';
        j.async = true;
        j.src = 'https://www.googletagmanager.com/gtm.js?id='+i+dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-MCR6T6W');
    </script>
    <!-- End Google Tag Manager -->
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Las opiniones de los clientes respaldan el trabajo que tenemos">
    <meta name="author" content="Erendida Itzel Castro Marquez">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="icon" href="../assets/images/kasu_logo.jpeg">
    <title>KASU | Testimoniales clientes</title>
    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
</head>
<body>
    <!-- Facebook Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s){
        if(f.fbq)return;
        n=f.fbq=function(){
            n.callMethod? n.callMethod.apply(n,arguments):n.queue.push(arguments);
        };
        if(!f._fbq) f._fbq=n;
        n.push=n;
        n.loaded=!0;
        n.version='2.0';
        n.queue=[];
        t=b.createElement(e);
        t.async=!0;
        t.src=v;
        s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s);
    }(window, document, 'script', 'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '186990709965368');
    fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=186990709965368&ev=PageView&noscript=1"/>
    </noscript>
    <!-- End Facebook Pixel Code -->

    <!-- Chat de Facebook -->
    <?php require_once 'html/CodeFb.php'; ?>

    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- La venta emergente debe de estar fuera del div que lo lanza *JCCM -->
    <div class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content" style="height:auto; padding:1em;">
                <div id="datos">
                    <!-- Aquí se cargarán los datos dinámicos -->
                </div>
            </div>
        </div>
    </div>

    <!-- ***** Header Area Start ***** -->
    <?php require_once 'html/MenuPrincipal.php'; ?>

    <br><br><br><br><br>
    <section class="section" id="testimonials" style="display: none;">
        <div class="container" style="display: none;">
            <!-- ***** Section Title Start ***** -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="center-heading">
                        <h1 class="section-title">Opinión de nuestros clientes</h1>
                    </div>
                </div>
                <div class="offset-lg-3 col-lg-6">
                    <div class="center-text">
                        <!-- Puedes agregar una descripción adicional aquí -->
                    </div>
                </div>
            </div>
            <!-- ***** Section Title End ***** -->
            <div class="row">
                <!-- ***** Testimonials Item Start ***** -->
                <?php
                // Realizamos una única consulta para obtener todos los testimonios
                $result = $mysqli->query("SELECT * FROM opiniones");
                if ($result) {
                    while ($art = $result->fetch_assoc()) {
                        // Asegúrate de que los índices coincidan con los nombres de columna en tu tabla
                        printf("
                        <div class='col-lg-4 col-md-6 col-sm-12'>
                            <div class='team-item'>
                                <div class='team-content'>
                                    <i><img src='assets/images/testimonial-icon.png' alt='Imagen de comentario'></i>
                                    <p>%s</p>
                                    <div class='user-image'>
                                        <img src='%s' alt='Imagen del usuario'>
                                    </div>
                                    <div class='team-info'>
                                        <h3 class='user-name'>%s</h3>
                                        <span>%s</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ", $art['comentario'], $art['ruta_imagen'], $art['nombre'], $art['cargo']);
                    }
                }
                ?>
                <!-- ***** Testimonials Item End ***** -->
            </div>
        </div>
    </section>

    <!-- ***** Footer Start ***** -->
    <footer>
        <?php require_once 'html/footer.php'; ?>
    </footer>

    <!-- jQuery -->
    <script src="assets/js/jquery-2.1.0.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/js/popper.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Plugins -->
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script>
    <!-- Global Init -->
    <script src="assets/js/custom.js"></script>
</body>
</html>
