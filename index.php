<?php

// Iniciar la sesión
session_start();

// Requerir el archivo de librerías
require_once 'eia/librerias.php';

// Para depuración: confirmar carga de librerías
//echo "Librerías cargadas correctamente.<br>";

// Instanciar la clase Basicas (asegúrate de que la clase esté definida en las librerías)
$basicas = new Basicas();
//echo "Instancia de Basicas creada.<br>";

// Si se recibe un mensaje en la URL, mostrar un alert en JavaScript y también imprimirlo en pantalla
if (isset($_GET['Msg'])) {
    $msg = addslashes($_GET['Msg']);
    echo "<script type='text/javascript'>alert('$msg');</script>";
    echo "Mensaje recibido: $msg<br>";
} else {
    echo "No se recibió mensaje (Msg) en la URL.<br>";
}

// Verificar que el parámetro 'Ml' exista y sea igual a 4
if (isset($_GET['Ml']) && $_GET['Ml'] == 4) {
    echo "Parámetro Ml es igual a 4.<br>";
    
    // Verificar si se recibió 'dat' para determinar en qué tabla actualizar
    if (empty($_GET['dat'])) {
        echo "No se recibió el parámetro 'dat'. Actualizando tabla 'Contacto'.<br>";
        $result = $basicas->ActCampo($mysqli, "Contacto", "Cancelacion", 1, $_GET['Id']);
        echo "Resultado de ActCampo en Contacto: " . var_export($result, true) . "<br>";
    } else {
        echo "Se recibió el parámetro 'dat'. Actualizando tabla 'prospectos'.<br>";
        // Asegúrate de que la variable $pros esté definida y sea una conexión válida
        $result = $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1, $_GET['Id']);
        echo "Resultado de ActCampo en prospectos: " . var_export($result, true) . "<br>";
    }
    
    echo "<script type='text/javascript'>alert('Se ha dado de baja tu email de nuestro News Letter');</script>";
    echo "Alerta de baja enviada.<br>";
} else {
    echo "El parámetro Ml no está definido o no es igual a 4.<br>";
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-MCR6T6W');
    </script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="description" content="KASU es una empresa brindar servicios a futuro, para asegurarte a ti y a los tuyos">
    <meta name="keywords" content="Funerario">
    <link rel="canonical" href="https://www.kasu.com.mx/index.php">
    <meta name="author" content="Jose Carlos Cabrera Monroy">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gastos funerarios a futuro | KASU</title>
    <!-- Additional CSS Files -->
    <link rel="icon" href="https://kasu.com.mx/assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/font-awesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="/assets/css/Index_Nvo.css">
    <!-- Javascript de la pagina -->
    <script type="text/javascript" src="eia/javascript/Registro.js"></script>
</head>

<body>
    <!-- Chat de Facebook -->
    <?
    require_once 'html/CodeFb.php';
    ?>
    <!-- Google Tag Manager (noscript)-->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!-- inicio ventanas emergentes de la letra pequeña de el contrato -->
    <? require_once 'html/Ventas_emer_Index.php'; ?>
    <!-- fin ventanas emergentes de la letra pequeña de el contrato -->
    <? require_once 'html/MenuPrincipal.php'; ?>
    <!-- Portada de pagina -->
    <!-- ***** Inicio Area de Welcome de Pagina con busqueda de cliente *****-->
    <div class="main-banner wow fadeIn" id="top" data-wow-duration="1s" data-wow-delay="0.5s">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-6 align-self-center">
                            <div class="left-content header-text wow fadeInLeft" data-wow-duration="1s" data-wow-delay="1s">
                                <h6><img src="../assets/images/flor_redonda.svg" style="width: 10vh;"></h6>
                                <h2> Servicios <em> de Gastos <span>Funerarios</span> y mucho </em>más</h2>
                                <p>La Visión de <strong>KASU</strong> es lograr una cobertura universal para las familias mexicanas en lo que se refiere a servicios funerarios.</p>
                                <div class="form">
                                    <input id="curp" type="text" class="text" placeholder="Ingresa tu CURP">
                                    <button type="submit" id="form-submit" class="main-button" data-toggle="modal" data-target=".bd-example-modal-sm" onclick="consultar()">CONSULTAR</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="right-image wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.5s">
                                <img src="/assets/images/Familia_Index.svg" alt="team meeting">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ***** Final Area de Welcome de Pagina con busqueda de cliente *****-->
    <div class="LlamadaKASU">
        <div class="row">
            <div class="col-md-4 col-md-12 col-sm-12 align-self-center">
                <h2>LÍNEA DE ATENCIÓN INMEDIATA</h2>
                <br>
                <a href="tel:<? echo $tel; ?>" class="btn btn-dark btn-lg"> EMERGENCIA FUNERARIA </a>
                <br>
                <br>
            </div>
        </div>
    </div>
    <!-- ***** Inicio Seccion de productos ***** -->
    <? require_once 'html/Section_Productos.php'; ?>
    <!-- ***** Inicio Seccion de Clientes ***** -->
    <section class="section colored padding-top-70" id="Clientes">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                    <div class="center-heading">
                        <p><strong>La Visión</strong> de <strong>KASU</strong> es lograr una cobertura universal para las familias mexicanas en lo que se refiere a servicios funerarios.</p>
                        <br>
                        <div class="count-item decoration-bottom">
                            <h2 class="section-title">
                                <strong>1<?PHP echo number_format($basicas->MaxDat($mysqli, "Id", "Venta"), 0, ".", ","); ?></strong><span> Clientes Activos</span>
                            </h2>
                        </div>
                        <p><strong>KASU</strong> es una empresa que ofrece Servicios funerarios a bajo costo en México, los cuales <strong>se pagan una sola vez en la vida</strong> y no requiere renovación o pagos adicionales, lo cual es una <strong>característica única</strong> y diferenciadora en comparación con otros Servicios funerarios en el mercado.</p>
                        <br>
                        <p>
                            Este enfoque en ayudar a las personas es el factor mas importante a promocionar,
                            destacando la importancia de apoyar a las comunidades locales y brindar una solución eficaz a un problema común.
                            Además, el hecho de que <STRONG>KASU</STRONG> se haya concretado en un <STRONG>fideicomiso</STRONG> permite brindar un <STRONG>servicio funerario digno</STRONG> en el momento que mas lo necesitas.
                        </p>
                        <br><br>
                    </div>
                </div>
                <div class="col-lg-2 col-md-12 col-sm-12 align-self-center"></div>
                <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
                    <div class="features-small-item">
                        <div class="descri">
                            <div class="icon">
                                <i><img src="assets/images/Index/florkasu.png" name="Logo" alt="Kasu Logo"></i>
                            </div>
                        </div>
                        <h2 class="features-title"><strong>Cotiza</strong></h2>
                        <p>Cotiza tu Servicio, tan solo requieres tu clave CURP</p>
                        <div class="consulta">
                            <div class="form-group">
                                <form method="POST" id="Cotizar" action="/login/php/Registro_Prospectos.php">
                                    <div id="FingerPrint" style="display: none;"></div>
                                    <input name="CURP" id="CURP" class="form-control" placeholder="Ingresar CURP">
                                    <br>
                                    <input type="email" name="Email" id="Email" class="form-control" placeholder="Correo Electornico">
                                    <br>
                                    <button type="submit" name="FormCotizar" id="FormCotizar" class="main-button" data-toggle="modal">Cotizar Servicio</button>
                                    <br>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ***** Porque contratar Ventajas***** -->
    <section class="mini" id="Ventajas">
        <div class="mini-content">
            <div class="container">
                <div class="col-lg-12">
                    <div class="center-heading">
                        <h2 class="section-title" style="color: #F9EBF9;">Los principales beneficios de contratar con <strong>KASU</strong></h2>
                    </div>
                </div>
                <br>
                <!-- ***** Mini Box Start ***** -->
                <div class="row">
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box">
                            <i><img src="assets/images/infinito.png" alt="Icono" style="height: 30px; width: 50px;"></i>
                            <h2>Adquiere tú servicio una sola vez en la vida.</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box">
                            <i><img src="assets/images/republica.png" alt="Icono" style="height: 40px; width: 40px;"></i>
                            <h2>Cobertura en toda la republica Mexicana</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box">
                            <i><img src="assets/images/usuario.png" alt="Icono" style="height: 40px; width: 50px;"></i>
                            <h2>Esta ligado a tu edad mediante el CURP</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box">
                            <i><img src="assets/images/relog.png" alt="Icono" style="height: 35px; width: 35px;"></i>
                            <h2>mientras mas joven, mas bajo es el costo</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box">
                            <i><img src="assets/images/tarjeta.png" alt="Icono" style="height: 35px; width: 35px;"></i>
                            <h2>Puedes pagar a crédito (3,6 y 9 meses)</h2>
                        </a>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                        <a href="#" class="mini-box">
                            <i><img src="assets/images/ok.png" alt="Icono" style="height: 35px; width: 35px;"></i>
                            <h2>Sin Renovaciones, o Cargos ocultos.</h2>
                        </a>
                    </div>
                </div>
                <!-- ***** Mini Box End ***** -->
            </div>
        </div>
    </section>
    <!-- ***** Opiniones de los clientes ***** -->
    <section class="section" id="testimonials" display="none">
        <div class="container">
            <br><br>
            <div class="row">
                <div class="offset-lg-3 col-lg-6">
                    <div class="center-heading">
                        <p>Conoce las <strong><a target="_blank" href="/testimonios.php">Opiniones</a></strong> de nuestros clientes. </p>
                        <br>
                    </div>
                </div>
            </div>
            <!-- ***** Section Title End ***** -->
            <div class="row">
                <!-- ***** Testimonials Item Start ***** -->
                <?php
                //Creamos la variables pricipales
                $cont = 1;
                //Contamos el no de  Articulos
                $Max = $basicas->MaxDat($mysqli, "id", "opiniones");
                //Buscamos un numero random
                $Arts = rand($cont, $Max);
                $ks = $Max - 1;
                if ($Arts >= $ks) {
                    $Arts = 1;
                }
                $Arts2 = $Arts + 2;
                //Se imprimen los comentarios
                while ($Arts <= $Arts2) {
                    //Consulta para los artiulos
                    $SqlArti = "SELECT * FROM opiniones WHERE id =" . $Arts;
                    //Si la consulta es verdadera imprime el articulo
                    if ($ResArti = $mysqli->query($SqlArti)) {
                        $art = $ResArti->fetch_assoc();
                        //print_r($art);
                        echo "
																			<div class='col-lg-4 col-md-6 col-sm-12'>
																					<div class='team-item'>
																							<div class='team-content'>
																									<div class='team-info'>
																										 <br>
																										 <img src='" . $art['foto'] . "' alt='" . $art['Nombre'] . "'>
																										 <p>" . $art['Opinion'] . "</p>
																										 <h3 class='user-name'>" . $art['Nombre'] . "</h3>
																										 <span>" . $art['Servicio'] . "</span>
																									</div>
																							</div>
																					</div>
																			</div>
																	";
                    }
                    $Arts++;
                }
                ?>
                <!-- ***** Testimonials Item End ***** -->
            </div>
        </div>
    </section>
    <!-- Contacto -->
    <footer>
        <? require_once 'html/footer.php'; ?>
    </footer>
    <script type="text/javascript" src="eia/javascript/finger.js"></script>
    <script src="assets/js/jquery-2.1.0.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/imgfix.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/scrollreveal.min.js"></script>
    <script src="assets/js/custom.js"></script>
    <!-- CONSULTA CURP -->
    <script type="text/javascript">
        //codigo de lanzador de ventana emergente y consulta
        function consultar() {
            //Aterrizaje de valor
            var value = document.getElementById("curp").value;
            //Encode en base 64
            var valueA = btoa(value);
            if (value == null) {
                // console.log("La CURP esta vacia");
            } else {
                // console.log(value);
                var xhttps = new XMLHttpRequest();
                xhttps.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("datos").innerHTML = this.responseText;
                    }
                };
                xhttps.open("GET", "https://kasu.com.mx/php/Consulta.php?value=" + valueA, true);
                xhttps.send();
            }
        }
    </script>
</body>

</html>