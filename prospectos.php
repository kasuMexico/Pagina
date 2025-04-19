<?php
// prospectos.php

// Requerir el archivo de librerías
require_once 'eia/librerias.php';

// —————————————————————————————————————————————————————————————
// 1) Sanear y decodificar parámetros de entrada
// —————————————————————————————————————————————————————————————
$data_enc  = filter_input(INPUT_GET, 'data',   FILTER_SANITIZE_STRING);
$usr_enc   = filter_input(INPUT_GET, 'Usr',    FILTER_SANITIZE_STRING);
$serfb_enc = filter_input(INPUT_GET, 'SerFb',  FILTER_SANITIZE_STRING);
$ori_enc   = filter_input(INPUT_GET, 'Ori',    FILTER_SANITIZE_STRING);

$data   = $data_enc   ? base64_decode($data_enc)   : '';
$usr    = $usr_enc    ? base64_decode($usr_enc)    : '';
$SerFb  = $serfb_enc  ? base64_decode($serfb_enc)  : '';
$Origen = $ori_enc    ? base64_decode($ori_enc)    : '';

// —————————————————————————————————————————————————————————————
// 2) Definir servicios y configuración de cada uno
// —————————————————————————————————————————————————————————————
$serviceMap = [
    'FUNERARIO'     => 'Gastos Funerarios',
    'POLICIAS'      => 'Seguridad Pública',
    'UNIVERSITARIO' => 'Inversión Universitaria',
    'RETIRO'        => 'Ahorro para el Retiro',
    'DISTRIBUIDOR'  => 'Agente Externo',
    'CITREG'        => 'Registrar Cita',
    'CITA'          => 'Registrar Cita',
];

//if (!array_key_exists($data, $serviceMap)) {
//    header('HTTP/1.1 400 Bad Request');
//    echo "Servicio desconocido.";
//    exit;
//}

$config = [
    'FUNERARIO' => [
        'title'   => '¡Estás un paso más cerca!',
        'message' => 'El equipo KASU está preparando tu cotización, déjanos tus datos',
        'btn'     => 'Registrarme y Continuar',
        'imgSide' => 'https://kasu.com.mx/assets/images/gasto_funerario.svg',
        'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/funerario.png',
    ],
    'UNIVERSITARIO' => [
        'title'   => 'Inversión Universitaria',
        'message' => 'Estás cerca de asegurar la educación universitaria de tu hijo',
        'btn'     => 'Registrarme',
        'imgSide' => 'https://kasu.com.mx/assets/images/gasto_universitario.svg',
        'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/universidad.png',
    ],
    'RETIRO' => [
        'title'   => 'Servicio de Retiro',
        'message' => 'Regístrate y en un momento te contactará alguien de nuestro equipo',
        'btn'     => 'Registrarme',
        'imgSide' => 'https://kasu.com.mx/assets/images/gasto_retiro.svg',
        'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/retiro.png',
    ],
    'POLICIAS' => [
        'title'   => 'Contacta con un agente',
        'message' => 'El personal de seguridad merece el mejor respaldo en los momentos más difíciles',
        'btn'     => 'Contactar',
        'imgSide' => 'https://kasu.com.mx/assets/images/gasto_policias.svg',
        'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/policias.png',
    ],
    'DISTRIBUIDOR' => [
        'title'   => 'Agente Externo',
        'message' => 'Felicidades, estás a un paso de generar ingresos desde tu celular',
        'btn'     => 'Recibir más info',
        'imgSide' => 'https://kasu.com.mx/assets/images/padres_con_hijos.jpeg',
        'imgSeo'  => '',
    ],
    'CITREG' => [
        'title'   => 'Registrar Cita',
        'message' => 'Registra el día que puedas recibir una llamada de uno de nuestros agentes',
        'btn'     => 'Registrar Cita',
        'imgSide' => '',
        'imgSeo'  => '',
    ],
    'CITA' => [
        'title'   => 'Cita Telefónica',
        'message' => 'Elige día y hora para tu llamada con un ejecutivo',
        'btn'     => 'Registrar Cita',
        'imgSide' => '',
        'imgSeo'  => '',
    ],
];

$defaults = [
    'title'   => 'Regístrate',
    'message' => 'Te enviaremos por correo la información necesaria para conocer todo sobre KASU',
    'btn'     => 'Registrarme',
    'imgSide' => 'https://kasu.com.mx/assets/images/registro/familiaformulario.png',
    'imgSeo'  => 'https://kasu.com.mx/assets/images/registro/default.png',
];

$settings = array_merge($defaults, $config[$data]);

// —————————————————————————————————————————————————————————————
// 3) Verificar duplicados si viene Usr
// —————————————————————————————————————————————————————————————
if ($usr) {
    $basicas = new Basicas();
    $prosId = $basicas->BuscarCampos($pros, 'Id', 'Distribuidores', 'IdProspecto', $usr);
    if (!empty($prosId) && !in_array($data, ['CITREG','CITA'], true)) {
        $msg = rawurlencode('Lo sentimos, este correo ya se ha usado.');
        header("Location: https://kasu.com.mx/index.php?Msg={$msg}");
        exit;
    }
    // Si es CITA o CITREG, puedes cargar datos existentes...
}

// —————————————————————————————————————————————————————————————
// 4) Incluir emergentes según Ml
// —————————————————————————————————————————————————————————————
require_once 'login/php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Prospecto | <?= htmlspecialchars($serviceMap[$data]) ?></title>
    <meta name="description" content="<?= htmlspecialchars($settings['message']) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title"       content="<?= htmlspecialchars($settings['title']) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($settings['message']) ?>" />
    <meta property="og:image"       content="<?= htmlspecialchars($settings['imgSeo']) ?>" />
    <link rel="canonical" href="https://kasu.com.mx/prospectos.php?data=<?= urlencode($data_enc) ?>">
    <link rel="icon" href="assets/images/kasu_logo.jpeg">
    <link rel="stylesheet" href="assets/css/Compra.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
</head>
<body>
    <section id="Formulario" class="row">
        <!-- Formulario (izquierda) -->
        <div class="col-md-6 AreaTrabajo">
            <form method="POST"
                  id="<?= $SerFb ? "cita-$SerFb" : "Prospecto-$data" ?>"
                  action="login/php/Registro_Prospectos.php">
                <div class="logo text-center">
                    <a href="/"><img src="assets/images/kasu_logo.jpeg" alt="KASU"></a>
                </div>
                <h1 class="text-center"><?= htmlspecialchars($settings['title']) ?></h1>
                <p class="text-center"><?= htmlspecialchars($settings['message']) ?></p>
                <div class="Formulario">
                    <input type="hidden" name="Host"  value="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="Cupon" value="<?= htmlspecialchars($data_enc) ?>">
                    <input type="hidden" name="Origen" value="<?= htmlspecialchars($ori_enc) ?>">

                    <!-- Aquí tus campos de nombre/email/telefono, etc. -->
                    <input type="text"  name="name"     placeholder="Nombre" required>
                    <input type="email" name="Mail"     placeholder="Correo electrónico" required>
                    <input type="tel"   name="Telefono" placeholder="Teléfono" required>

                    <?php if (in_array($data, ['CITA','CITREG'], true)): ?>
                        <label>Fecha de cita</label>
                        <input type="date" name="FechaCita" required>
                        <label>Hora de cita</label>
                        <input type="time" name="HoraCita" required>
                    <?php endif; ?>
                </div>

                <div class="Formulario text-center" style="margin-top:1em">
                    <button type="submit" name="<?= htmlspecialchars($settings['btn']) ?>"
                            class="btn btn-primary">
                        <?= htmlspecialchars($settings['btn']) ?>
                    </button>
                </div>
                <div class="Ligas text-center" style="margin-top:1em">
                    <a href="/" style="color:#911F66">Regresar a KASU</a> |
                    <a href="https://kasu.com.mx/terminos-y-condiciones.php" style="color:#012F91">
                        Términos y Condiciones
                    </a>
                </div>
            </form>
        </div>
        <!-- Imagen lateral (derecha) -->
        <div class="col-md-6 text-center">
            <img src="<?= htmlspecialchars($settings['imgSide']) ?>"
                 alt="Imagen <?= htmlspecialchars($serviceMap[$data]) ?>"
                 style="max-width:100%; height:auto;">
        </div>
    </section>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="eia/javascript/Registro.js"></script>
    <script src="eia/javascript/finger.js"></script>
</body>
</html>
