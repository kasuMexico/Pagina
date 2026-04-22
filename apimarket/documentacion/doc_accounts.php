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
    <meta name="description" content="Documentación API_ACCOUNTS V1 para registrar servicios KASU, generar póliza y liga de pago.">
    <meta name="author" content="Jose Carlos Cabrera Monroy">
    <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css">
    <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU | API_ACCOUNTS</title>

    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="../assets/codigo.css">
</head>
<body class="doc-page">
<?php
    require_once __DIR__ . '/../html/menu.php';        // Menú principal
    require_once __DIR__ . '/../html/Inf_general.php'; // Información general
    require_once __DIR__ . '/../html/versiones.php';   // Información de versiones
    require_once __DIR__ . '/../html/Sandbox.php';     // Sandbox
?>
<!-- ***** CODIGOS GENERALES ***** -->
<section class="doc-section" id="codigos">
    <div class="container">
        <div class="doc-heading">
            <span class="api-kicker">API_ACCOUNTS</span>
            <h2>Códigos, funciones y productos</h2>
            <p>Estos son los códigos generales generados por <strong>API_ACCOUNTS</strong>, la función <strong>new_service</strong> y los productos habilitados para alta desde <strong>/api/Accounts_V1</strong>.</p>
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
                        <tr><td>account_status</td><td style="text-align: justify;">Consulta el estado de cuenta desde <strong>API_PAYMENTS</strong>.</td></tr>
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
<section class="doc-section doc-section--muted" id="new-service">
    <div class="container">
        <div class="doc-heading">
            <h2>Registrar servicio</h2>
            <p>Reemplaza los valores de ejemplo por datos reales del cliente y producto. Las aceptaciones legales admiten <strong>acepto</strong> o <strong>accept</strong>.</p>
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
                        <tr><td>mail</td><td style="text-align: justify;">Correo del cliente.</td></tr>
                        <tr><td>telefono</td><td style="text-align: justify;">Teléfono del cliente, 10 dígitos MX.</td></tr>
                        <tr><td>producto</td><td style="text-align: justify;">Producto solicitado: <strong>Funerario</strong> o <strong>Retiro</strong>, sujeto a edad y catálogo.</td></tr>
                        <tr><td>numero_pagos</td><td style="text-align: justify;">Número de pagos elegidos. Si es mayor a 1 se calcula crédito.</td></tr>
                        <tr><td>dia_pago</td><td style="text-align: justify;">Día de pago mensual permitido: <strong>1</strong> o <strong>15</strong>. En contado se registra como 0.</td></tr>
                        <tr><td>tipo_servicio</td><td style="text-align: justify;">Tipo de servicio KASU. Si no se envía, se usa <strong>Ecologico</strong>.</td></tr>
                        <tr><td>terminos</td><td style="text-align: justify;">Aceptación de <a href="https://kasu.com.mx/terminos-y-condiciones.php"><strong>Términos y Condiciones</strong></a>. Valores: <strong>acepto</strong> o <strong>accept</strong>.</td></tr>
                        <tr><td>aviso</td><td style="text-align: justify;">Aceptación del <a href="https://kasu.com.mx/terminos-y-condiciones.php"><strong>Aviso de Privacidad</strong></a>. Valores: <strong>acepto</strong> o <strong>accept</strong>.</td></tr>
                        <tr><td>fideicomiso</td><td style="text-align: justify;">Ingreso al <a href="https://kasu.com.mx/Fideicomiso_F0003.pdf"><strong>Fideicomiso F/0003</strong></a>. Valores: <strong>acepto</strong> o <strong>accept</strong>.</td></tr>
                        <tr><td>direccion.calle</td><td style="text-align: justify;">Calle del cliente.</td></tr>
                        <tr><td>direccion.numero</td><td style="text-align: justify;">Número de casa.</td></tr>
                        <tr><td>direccion.colonia</td><td style="text-align: justify;">Colonia.</td></tr>
                        <tr><td>direccion.municipio</td><td style="text-align: justify;">Municipio.</td></tr>
                        <tr><td>direccion.codigo_postal</td><td style="text-align: justify;">Código Postal.</td></tr>
                        <tr><td>direccion.estado</td><td style="text-align: justify;">Estado.</td></tr>
                        <tr><td>TIMESTAMP</td><td style="text-align: justify;">Tiempo de generación del token de acceso.</td></tr>
                        <tr><td>EXPIRE_IN</td><td style="text-align: justify;">Segundos de vigencia del token.</td></tr>
                    </table>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
                <div class="code-window">
					<pre id="codecopi" class="userContent" style="white-space: pre-wrap;">
						<code>
							POST https://apimarket.kasu.com.mx/api/Accounts_V1

							Headers:
							Authorization: Bearer API_KEY_AQUI

							Content-Type: application/json
							User-Agent: SECRET_KEY_USUARIO_SECRET_KEY_ID

							{
							"tipo_peticion": "new_service",
							"nombre_de_usuario": "YOUR_APPUSER",
							"curp_en_uso": "CURP_CODE",
							"mail": "CORREO_ELECTRONICO",
							"telefono": TELEFONO,
							"producto": "PRODUCTO",
							"numero_pagos": NUMERO_PAGOS,
							"dia_pago": 1,
							"tipo_servicio": "Ecologico",
							"terminos": "acepto",
							"aviso": "acepto",
							"fideicomiso": "acepto",
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
<section class="doc-section" id="respuesta">
    <div class="container">
        <div class="doc-heading">
            <h2>Respuesta de new_service</h2>
            <p>La API retorna códigos de error cuando no resuelve correctamente la solicitud y una respuesta <strong>201</strong> cuando crea la venta en estatus <strong>PREVENTA</strong>.</p>
        </div>
        <div class="row">
            <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                <div class="row">
                    <table class="table table-responsive justify">
                        <tr>
                            <td><strong>CLAVE</strong></td>
                            <td><strong>DESCRIPCIÓN DE CLAVES DE FUNCIONES</strong></td>
                        </tr>
                        <tr><td>new_service</td><td style="text-align: justify;">Registra un cliente <strong>KASU</strong>.</td></tr>
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
                        <tr><td>datos_compra.id_venta</td><td style="text-align: justify;">Identificador de la venta creada.</td></tr>
                        <tr><td>datos_compra.id_contacto</td><td style="text-align: justify;">Identificador del contacto creado.</td></tr>
                        <tr><td>datos_compra.nombre</td><td style="text-align: justify;">Nombre del cliente según validación CURP.</td></tr>
                        <tr><td>datos_compra.CURP</td><td style="text-align: justify;">CURP ligada al servicio <strong>KASU</strong>.</td></tr>
                        <tr><td>datos_compra.mail</td><td style="text-align: justify;">Correo ligado al servicio.</td></tr>
                        <tr><td>datos_compra.poliza</td><td style="text-align: justify;">Póliza única del servicio.</td></tr>
                        <tr><td>datos_compra.status</td><td style="text-align: justify;">Estatus inicial del servicio: <strong>PREVENTA</strong>.</td></tr>
                        <tr><td>datos_compra.subtotal</td><td style="text-align: justify;">Total de la venta o crédito.</td></tr>
                        <tr><td>datos_compra.amount</td><td style="text-align: justify;">Monto inicial a cobrar.</td></tr>
                        <tr><td>datos_compra.pago_link</td><td style="text-align: justify;">Liga para generar o continuar el pago.</td></tr>
                    </table>

                    <div class="Consulta">
                        <br>
                        <p>La respuesta incluye los datos mínimos para continuar cobranza con <strong>API_PAYMENTS</strong> o con la liga de pago.</p>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ***** Features Small Start ***** -->
<section class="doc-section doc-section--muted" id="otros">
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
