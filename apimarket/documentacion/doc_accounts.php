<?php
declare(strict_types=1);

// Errores y seguridad
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
ini_set('display_errors', '0');
header_remove('X-Powered-By');

session_start();

require_once __DIR__ . '/../librerias_api.php'; // Debe definir $mysqli y charset UTF-8

// Valida conexión
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error de conexión.');
}

// Deriva la "word" desde el nombre del archivo actual
// Ej.: doc_accounts.php -> "accounts"
$scriptPath = (string)($_SERVER['PHP_SELF'] ?? '');
$filename   = pathinfo($scriptPath, PATHINFO_BASENAME);
$extension  = pathinfo($filename, PATHINFO_EXTENSION);
$basename   = $extension !== '' ? basename($filename, '.' . $extension) : $filename;

$posUnderscore = strpos($basename, '_');
$word = $posUnderscore !== false ? substr($basename, $posUnderscore + 1) : $basename;
$word = trim((string)$word);

// Consulta segura
$Reg = [];
try {
    $stmt = $mysqli->prepare('SELECT * FROM ContApiMarket WHERE Nombre = ?');
    $stmt->bind_param('s', $word);
    $stmt->execute();
    $res = $stmt->get_result();
    $Reg = $res->fetch_assoc() ?? [];
    $stmt->close();
} catch (Throwable $e) {
    // Silencia el detalle al usuario final
    $Reg = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="Cobros">
    <link rel="canonical" href="https://kasu.com.mx">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Registra, modifica y borra registros de clientes">
    <meta name="author" content="Jose Carlos Cabrera Monroy">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU | API_ACCOUNTS</title>

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/templatemo-softy-pinko.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
    <link rel="stylesheet" href="../assets/codigo.css">
</head>
<body>
<?php
    require_once __DIR__ . '/../html/menu.php';        // Menú principal
    require_once __DIR__ . '/../html/Inf_general.php'; // Información general
    require_once __DIR__ . '/../html/versiones.php';   // Información de versiones
    require_once __DIR__ . '/../html/Sandbox.php';     // Sandbox
?>
<!-- ***** CODIGOS GENERALES ***** -->
<section class="section padding-top-70 " id="">
    <div class="container">
        <div class="Consulta">
            <h2 class="titulos"><strong>CÓDIGOS GENERALES</strong></h2>
            <br>
            <p>Estos son los códigos generales generados por <strong>API_REGISTRO</strong> y las claves para envío de datos.</p>
            <br>
        </div>
        <div class="row">
            <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>CÓDIGOS</strong></td>
                            <td><strong>DESCRIPCIÓN</strong></td>
                        </tr>
                        <tr><td>200</td><td style="text-align: justify;">Petición exitosa. Respuesta en JSON.</td></tr>
                        <tr><td>400</td><td style="text-align: justify;">Falta algún dato requerido por la solicitud.</td></tr>
                        <tr><td>401</td><td style="text-align: justify;">Comunicación corrupta. Datos modificados.</td></tr>
                        <tr><td>404</td><td style="text-align: justify;">Petición desconocida. Solo se admiten claves documentadas.</td></tr>
                        <tr><td>405</td><td style="text-align: justify;">El método HTTP es distinto de <strong>POST</strong>.</td></tr>
                        <tr><td>412</td><td style="text-align: justify;">El cliente ya está registrado con el producto seleccionado.</td></tr>
                        <tr><td>417</td><td style="text-align: justify;">CURP de persona fallecida o inexistente.</td></tr>
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
                        <tr><td>token_full</td><td style="text-align: justify;">Genera un token de autorización de uso con vigencia de 10 minutos.</td></tr>
                        <tr><td>new_service</td><td style="text-align: justify;">Registra un cliente <strong>KASU</strong>.</td></tr>
                        <tr><td>modify_record</td><td style="text-align: justify;">Obtiene el precio de un producto <strong>KASU</strong>.</td></tr>
                    </table>
                </div>

                <div><br></div>

                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>PRODUCTOS</strong></td>
                            <td><strong>DESCRIPCIÓN</strong></td>
                        </tr>
                        <tr><td>Funerario</td><td style="text-align: justify;">Servicio de <strong>Gastos Funerarios</strong> ligado a la edad.</td></tr>
                        <tr><td>Retiro</td><td style="text-align: justify;">Plan Privado de Retiro para adultos menores de <strong>65 años</strong>.</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ***** REGISTRAR EL SERVICIO ***** -->
<section class="section padding-top-70" id="">
    <div class="container">
        <div class="Consulta">
            <h2 class="titulos"><strong>REGISTRAR SERVICIO</strong></h2>
            <br>
            <p>Reemplaza los valores de ejemplo por datos reales del cliente y producto. Algunos parámetros son opcionales según el caso.</p>
            <br>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>Parámetro</strong></td>
                            <td><strong>Descripción</strong></td>
                        </tr>
                        <tr><td>API_KEY_AQUI</td><td style="text-align: justify;">Token recibido de <strong>AUTENTICACIÓN</strong>.</td></tr>
                        <tr><td>tipo_peticion</td><td style="text-align: justify;">Tipo de petición según tablas de acceso.</td></tr>
                        <tr><td>YOUR_APPUSER</td><td style="text-align: justify;">Usuario registrado en KASU.</td></tr>
                        <tr><td>CURP_CODE</td><td style="text-align: justify;">CURP del cliente.</td></tr>
                        <tr><td>Mail</td><td style="text-align: justify;">Correo del cliente.</td></tr>
                        <tr><td>Telefono</td><td style="text-align: justify;">Teléfono del cliente.</td></tr>
                        <tr><td>Producto</td><td style="text-align: justify;">Producto permitido según acceso.</td></tr>
                        <tr><td>NumeroPagos</td><td style="text-align: justify;">Número de pagos elegidos.</td></tr>
                        <tr><td>Terminos</td><td style="text-align: justify;">Aceptación de <a href="https://kasu.com.mx/terminos-y-condiciones.php"><strong>Términos y Condiciones</strong></a>.</td></tr>
                        <tr><td>Aviso</td><td style="text-align: justify;">Aceptación del <a href="https://kasu.com.mx/terminos-y-condiciones.php"><strong>Aviso de Privacidad</strong></a>.</td></tr>
                        <tr><td>Fideicomiso</td><td style="text-align: justify;">Ingreso al <a href="https://kasu.com.mx/Fideicomiso_F0003.pdf"><strong>Fideicomiso F/0003</strong></a>.</td></tr>
                        <tr><td>Calle</td><td style="text-align: justify;">Calle del cliente.</td></tr>
                        <tr><td>Numero</td><td style="text-align: justify;">Número de casa.</td></tr>
                        <tr><td>Colonia</td><td style="text-align: justify;">Colonia.</td></tr>
                        <tr><td>Municipio</td><td style="text-align: justify;">Municipio.</td></tr>
                        <tr><td>Codigo_Postal</td><td style="text-align: justify;">Código Postal.</td></tr>
                        <tr><td>Estado</td><td style="text-align: justify;">Estado.</td></tr>
                        <tr><td>TIMESTAMP</td><td style="text-align: justify;">Tiempo de generación del token de acceso.</td></tr>
                        <tr><td>EXPIRE_IN</td><td style="text-align: justify;">Segundos de vigencia del token.</td></tr>
                    </table>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
                <div class="code-window">
					<pre id="codecopi" class="userContent" style="white-space: pre-wrap;">
						<code>
							POST https://apimarket.kasu.com.mx/api/Registro_V1

							Headers:
							Authorization: Bearer API_KEY_AQUI

							Content-Type: application/json
							User-Agent: Your-Application-Name/1.0

							{
							"tipo_peticion": "new_service",
							"nombre_de_usuario": "YOUR_APPUSER",
							"curp_en_uso": "CURP_CODE",
							"mail": "CORREO_ELECTRONICO",
							"telefono": TELEFONO,
							"producto": "PRODUCTO",
							"numero_pagos": NUMERO_PAGOS,
							"terminos": "ACCEPT",
							"aviso": "ACCEPT",
							"fideicomiso": "ACCEPT",
							"direccion": {
								"calle": "CALLE",
								"numero": NUMERO,
								"colonia": "COLONIA",
								"municipio": "MUNICIPIO",
								"codigo_postal": CODIGO_POSTAL,
								"estado": "ESTADO"
							},

							"token_data": {
								"timestamp": TIMESTAMP,
								"expires_in": EXPIRE_IN
							}

							}
						</code>
					</pre>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ***** REGISTRO DE DATOS DE REGISTRAR EL SERVICIO ***** -->
<section class="section padding-top-70 colored" id="">
    <div class="container">
        <div class="Consulta">
            <h2 class="titulos"><strong>REGISTRO DE DATOS DE REGISTRAR EL SERVICIO</strong></h2>
            <br>
            <p>La API retorna códigos de error cuando no resuelve correctamente la solicitud. Usa estas guías para decidir la función a ejecutar.</p>
            <br>
        </div>
        <div class="row">
            <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>CLAVE</strong></td>
                            <td><strong>DESCRIPCIÓN DE CLAVES DE FUNCIONES</strong></td>
                        </tr>
                        <tr><td>registro_servicio</td><td style="text-align: justify;">Registra un cliente <strong>KASU</strong>.</td></tr>
                    </table>
                </div>

                <div><br><br></div>

                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>CÓDIGO</strong></td>
                            <td><strong>ERRORES DE PETICIÓN</strong></td>
                        </tr>
                        <tr><td>201</td><td style="text-align: justify;">Registro exitoso con estatus PREVENTA.</td></tr>
                        <tr><td>406</td><td style="text-align: justify;">Edad fuera de rango o producto inexistente.</td></tr>
                        <tr><td>409</td><td style="text-align: justify;">No se aceptó fideicomiso, privacidad o términos.</td></tr>
                    </table>
                </div>
            </div>

            <div class="col-lg-1 col-md-12 col-sm-12 align-self-center"></div>

            <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>LLAVE</strong></td>
                            <td><strong>RESPUESTA POSITIVA</strong></td>
                        </tr>
                        <tr><td>mensaje</td><td style="text-align: justify;">Mensaje de éxito con el <strong>SERVICIO</strong>.</td></tr>
                        <tr><td>nombre</td><td style="text-align: justify;">Nombre del cliente según <strong>RENAPO</strong>.</td></tr>
                        <tr><td>CURP</td><td style="text-align: justify;">CURP ligada al servicio <strong>KASU</strong>.</td></tr>
                        <tr><td>mail</td><td style="text-align: justify;">Correo para <strong>API_COBROS</strong>.</td></tr>
                        <tr><td>poliza</td><td style="text-align: justify;"><strong>TOKEN</strong> único del servicio.</td></tr>
                        <tr><td>Status</td><td style="text-align: justify;">Estatus del servicio para <strong>API_COBROS</strong>.</td></tr>
                        <tr><td>Costo</td><td style="text-align: justify;">Costo del servicio para <strong>API_COBROS</strong>.</td></tr>
                    </table>

                    <div class="Consulta">
                        <br>
                        <p>Si no tienes acceso a <strong>API_COBROS</strong>, no se retornan datos de cobro y el sistema enviará un correo automático al cliente.</p>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ***** Features Small Start ***** -->
<section class="section padding-top-70" id="otros">
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
                    <?php require_once __DIR__ . '/../html/select_api.php'; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ***** Features Small End ***** -->

<footer>
    <?php require_once __DIR__ . '/../html/footer.php'; ?>
</footer>

<!-- Copiar -->
<script>
(function () {
    function copyFromElement(id) {
        var el = document.getElementById(id);
        if (!el) return;

        var text = el.textContent || el.innerText || '';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).catch(function(){ fallbackCopy(text); });
        } else {
            fallbackCopy(text);
        }
    }
    function fallbackCopy(text) {
        var aux = document.createElement('textarea');
        aux.value = text;
        document.body.appendChild(aux);
        aux.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(aux);
    }
    // Expone función global compatible con tu HTML
    window.copiarAlPortapapeles = copyFromElement;
})();
</script>

<!-- JS -->
<script src="https://kasu.com.mx/assets/js/jquery-2.1.0.min.js"></script>
<script src="https://kasu.com.mx/assets/js/bootstrap.min.js"></script>
<script src="https://kasu.com.mx/assets/js/scrollreveal.min.js"></script>
<script src="https://kasu.com.mx/assets/js/custom.js"></script>
</body>
</html>
