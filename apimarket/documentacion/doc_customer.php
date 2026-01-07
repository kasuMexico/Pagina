<?php
declare(strict_types=1);

// === Arranque de sesión seguro (PHP 8.2) ===
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// === Carga de dependencias ===
require_once __DIR__ . '/../librerias_api.php'; // Debe definir $mysqli conectado y UTF-8

// Manejo de errores de mysqli como excepciones
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// === Normalización de entrada y utilidades ===
/**
 * Devuelve la parte de palabra después del primer guion bajo en el nombre del archivo actual.
 * ej. doc_accounts.php => accounts
 */
function obtenerPalabraDesdeArchivoActual(): string {
    $self = $_SERVER['PHP_SELF'] ?? '';
    $filename = basename($self);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $basename = $extension !== '' ? basename($filename, '.' . $extension) : $filename;

    // Si no hay guion bajo, usa el basename completo
    if (strpos($basename, '_') === false) {
        return $basename;
    }
    // Toma lo que va después del primer "_"
    [$prefix, $rest] = explode('_', $basename, 2);
    return $rest ?: $basename;
}

$word = obtenerPalabraDesdeArchivoActual();

// === Consulta segura ===
$Reg = null;
if (isset($mysqli) && ($mysqli instanceof mysqli)) {
    $stmt = $mysqli->prepare('SELECT * FROM ContApiMarket WHERE Nombre = ?');
    $stmt->bind_param('s', $word);
    $stmt->execute();
    $res = $stmt->get_result();
    $Reg = $res->fetch_assoc() ?: null;
    $stmt->close();
}
// Nota: $Reg queda disponible si luego decides imprimir datos.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="Cobros">
    <link rel="canonical" href="https://kasu.com.mx<?php echo htmlspecialchars(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Consulta los datos de clientes y productos">
    <meta name="author" content="Jose Carlos Cabrera Monroy">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,300,400,500,700,900" rel="stylesheet">
    <link rel="icon" href="https://kasu.com.mx/assets/images/Index/florkasu.png">
    <title>KASU | API_CUSTOMER</title>

    <!-- Additional CSS Files -->
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://kasu.com.mx/assets/css/font-awesome.css">
    <link rel="stylesheet" href="https://kasu.com.mx/assets/css/index.css">
    <link rel="stylesheet" href="../assets/codigo.css">
</head>
<body>
    <!-- La ventana emergente debe de estar fuera del div que lo lanza -->
    <?php
    require_once __DIR__ . '/../html/menu.php';        // Menú principal
    require_once __DIR__ . '/../html/Inf_general.php'; // Información general
    require_once __DIR__ . '/../html/versiones.php';   // Información de versiones
    ?>

    <!-- *****          SANDBOX      ***** -->
    <?php require_once __DIR__ . '/../html/Sandbox.php'; ?>

    <!-- *****     CÓDIGOS GENERALES     ***** -->
    <section class="section padding-top-70 colored" id="">
        <div class="container">
            <div class="Consulta">
                <h2 class="titulos"><strong>CODIGOS GENERALES</strong></h2>
                <br>
                <p>Estos son los códigos generales generados por <strong>API_CUSTOMER</strong>, y las claves para envío de datos.</p>
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
                            <tr><td>202</td><td style="text-align: justify;">Consulta de datos exitosa.</td></tr>
                            <tr><td>400</td><td style="text-align: justify;">Falta algún dato necesario de los que requiere la solicitud.</td></tr>
                            <tr><td>401</td><td style="text-align: justify;">La comunicación entre el cliente y el servidor fue corrupta. Los datos fueron modificados.</td></tr>
                            <tr><td>404</td><td style="text-align: justify;">Petición desconocida. Solo se admiten las claves de funciones de la documentación.</td></tr>
                            <tr><td>405</td><td style="text-align: justify;">El método HTTP utilizado en la solicitud es distinto a <strong>POST</strong>.</td></tr>
                            <tr><td>406</td><td style="text-align: justify;">El producto excede los límites de edad para el producto seleccionado o el producto no existe.</td></tr>
                            <tr><td>409</td><td style="text-align: justify;">El cliente no autorizó la consulta de sus datos o la clave de aceptación es incorrecta.</td></tr>
                            <tr><td>412</td><td style="text-align: justify;">La condición que buscas no es correcta o no es apta para ser consultada.</td></tr>
                            <tr><td>418</td><td style="text-align: justify;">Has excedido el tiempo de operación para este TOKEN.</td></tr>
                        </table>
                    </div>
                </div>

                <div class="col-lg-1 col-md-12 col-sm-12 align-self-center"></div>

                <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
                    <div class="row">
                        <table class="table table-responsive justify">
                            <tr>
                                <td><strong>tipo_peticion</strong></td>
                                <td><strong>DESCRIPCIÓN DE CLAVES DE FUNCIONES</strong></td>
                            </tr>
                            <tr>
                                <td>request</td>
                                <td style="text-align: justify;">Retorna las claves y datos consultables.</td>
                            </tr>
                            <tr>
                                <td>individual_request</td>
                                <td style="text-align: justify;">Búsqueda puntual: <strong>Contacto</strong> cliente, datos <strong>personales</strong> o una <strong>venta</strong> específica.</td>
                            </tr>
                            <tr>
                                <td>request_block</td>
                                <td style="text-align: justify;">Búsqueda por conjunto de datos específico.</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div> <!-- row -->
        </div>
    </section>

    <!-- *****  CONSULTA BASE ***** -->
    <section class="section padding-top-70" id="">
        <div class="container">
            <div class="row">
                <div class="col-lg-5 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter left move 30px over 0.6s after 0.4s">
                    <div class="row">
                        <table class="table table-responsive justify">
                            <tr>
                                <td><strong>Parámetro</strong></td>
                                <td><strong>DESCRIPCIÓN</strong></td>
                            </tr>
                            <tr>
                                <td>API_KEY_AQUI</td>
                                <td style="text-align: justify;">Reemplaza por el <strong>TOKEN</strong> recibido en <strong>AUTENTICACIÓN</strong>.</td>
                            </tr>
                            <tr>
                                <td>tipo_peticion</td>
                                <td style="text-align: justify;">General, individual o por bloque según la clave.</td>
                            </tr>
                            <tr>
                                <td>YOUR_APPUSER</td>
                                <td style="text-align: justify;">Tu usuario registrado en KASU.</td>
                            </tr>
                            <tr>
                                <td>CLAVE_CONSULTA</td>
                                <td style="text-align: justify;">Clave de búsqueda requerida.</td>
                            </tr>
                            <tr>
                                <td>CURP_CODE</td>
                                <td style="text-align: justify;">CURP con la que generaste el <strong>API_KEY</strong>.</td>
                            </tr>
                            <tr>
                                <td>TIMESTAMP</td>
                                <td style="text-align: justify;">Instante de generación del token de <strong>ACCESO</strong>.</td>
                            </tr>
                            <tr>
                                <td>EXPIRE_IN</td>
                                <td style="text-align: justify;">Vigencia del token devuelto por <strong>ACCESO</strong>.</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="col-lg-1 col-md-12 col-sm-12 align-self-center"></div>

                <div class="col-lg-6 col-md-12 col-sm-12 align-self-center" data-scroll-reveal="enter right move 30px over 0.6s after 0.4s">
                    <div class="code-window">
						<pre id="codecopi" class="userContent" style="white-space: pre-wrap;">
							<code>
								POST https://apimarket.kasu.com.mx/api/Customer_V1

								Headers:
								Authorization: Bearer API_KEY_AQUI
								Content-Type: application/json
								User-Agent: YourApplicationName/1.0

								{
									"tipo_peticion": "request",
									"nombre_de_usuario": "YOUR_APPUSER",
									"request": "CLAVE_CONSULTA",
									"curp_en_uso": "CURP_CODE",
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

    <!-- ***** Otras APIs ***** -->
    <section class="section padding-top-70 colored" id="otros">
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

    <footer>
        <?php require_once __DIR__ . '/../html/footer.php'; ?>
    </footer>

    <!-- Copiar -->
    <script>
    function copiarAlPortapapeles(id) {
        var el = document.getElementById(id);
        if (!el) return;

        var texto = el.innerText || el.textContent || '';
        if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
            navigator.clipboard.writeText(texto).catch(function(){ fallbackCopy(texto); });
        } else {
            fallbackCopy(texto);
        }

        function fallbackCopy(t) {
            var aux = document.createElement('textarea');
            aux.value = t;
            aux.setAttribute('readonly', '');
            aux.style.position = 'absolute';
            aux.style.left = '-9999px';
            document.body.appendChild(aux);
            aux.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(aux);
        }
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
