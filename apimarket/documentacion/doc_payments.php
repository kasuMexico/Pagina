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
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="keywords" content="Cobros">
  <link rel="canonical" href="kasu.com.mx">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Interactua con las finanzas de nuestros Servicios KASU">
  <meta name="author" content="Jose Carlos cabrera Monroy">
  <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
  <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
  <title>KASU| API_PAYMENTS</title>

  <!-- Additional CSS Files -->
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
  <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
  <link rel="stylesheet" href="../assets/codigo.css">
</head>
<body>
  <!-- La venta emergente debe de estar fuera del div que lo lanza *JCCM -->
  <?php
    require_once '../html/menu.php';        // Menu principal
    require_once '../html/Inf_general.php'; // informacion general
    require_once '../html/versiones.php';   // Informacion de versiones
  ?>

  <!-- ***** CODIGOS GENERALES ***** -->
  <section class="section padding-top-70" id="">
    <div class="container">
      <div class="Consulta">
        <h2 class="titulos"><strong>CODIGOS GENERALES</strong></h2>
        <br>
        <p>Estos son los codigos generales generados por <strong>API_REGISTRO</strong>, y las claves para envio de datos.</p>
        <br>
      </div>
      <div class="row">
        <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
          <div class="row">
            <table class="table table-responsive justify">
              <tr>
                <td><strong>CODIGOS</strong></td>
                <td><strong>DESCRIPCION</strong></td>
              </tr>
              <tr><td>200</td><td style="text-align: justify;">Peticion exitosa, retorna en formato JSON.</td></tr>
              <tr><td>400</td><td style="text-align: justify;">Falta algun dato necesario de los que requiere la solicitud.</td></tr>
              <tr><td>401</td><td style="text-align: justify;">La comunicacion entre el cliente y el servidor fue corrupta.</td></tr>
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
                <td><strong>DESCRIPCION</strong></td>
              </tr>
              <tr>
                <td>token_full</td>
                <td style="text-align: justify;">
                  Acceso único a todas las API KASU. Cada token se emite con permisos finos y
                  <strong>activamos de forma selectiva</strong> las funcionalidades asignadas a ese token.
                </td>
              </tr>
              <tr><td>new_service</td><td style="text-align: justify;">Registra un cliente <strong>KASU</strong>.</td></tr>
              <tr><td>modify_record</td><td style="text-align: justify;">Obtiene el precio de un producto <strong>KASU</strong>.</td></tr>
            </table>
          </div>

          <!-- Alcance del token y funcionalidades -->
          <div class="row" style="margin-top:18px;">
            <div class="col-12">
              <h3 class="titulos"><strong>ALCANCE DEL TOKEN Y PERMISOS</strong></h3>
              <p style="text-align: justify;">
                Al generar <code>token_full</code> se crea una credencial única. Sobre esa credencial
                habilitamos las capacidades listadas abajo. Si una capacidad no está asociada a tu token,
                la API responderá con error de autorización o de alcance insuficiente.
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
  
  <!-- ***** Features Small Start ***** -->
  <?php require_once '../html/Sandbox.php'; ?>


  <!-- ***** Features Small Start ***** -->
  <section class="section padding-top-70" id="otros">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="row">
            <div class="col-lg-12">
              <div class="center-heading">
                <h2 class="section-title">Otras APIS que te pueren interesar</h2>
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
