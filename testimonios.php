<?php
// Iniciar la sesión
session_start();
// Requerir el archivo de librerías
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
    <!-- Chat de Facebook -->
    <?php require_once 'html/CodeFb.php'; ?>

    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!-- ***** Header Area Start ***** -->
    <?php require_once 'html/MenuPrincipal.php'; ?>
    <br><br><br><br><br>
    <section class="section" id="testimonials">
        <div class="container">
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
                         <p>
                            En KASU, creemos en la transparencia y la confianza. Por eso, aquí solo encontrarás opiniones reales de clientes que han vivido la experiencia de nuestro servicio.
                        </p>
                        <p>
                            A diferencia de otras empresas, en KASU no simulamos ni inventamos testimonios: cada comentario aquí es auténtico y representa la voz de quienes nos eligieron para proteger a su familia.
                        </p>
                        <h2>
                            ¡Conoce sus historias y descubre por qué cada vez más personas confían en KASU!
                        </h2>
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
                                        <div class='team-info'>
                                            <br>
                                            <img src='" . htmlspecialchars($art['foto']) . "' alt='" . htmlspecialchars($art['Nombre']) . "'>
                                            <p>" . htmlspecialchars($art['Opinion']) . "</p>
                                            <h3 class='user-name'>" . htmlspecialchars($art['Nombre']) . "</h3>
                                            <span>" . htmlspecialchars($art['Servicio']) . "</span>
                                        </div>
                                    </div>
                                </div>
                            </div>"
                        );
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
