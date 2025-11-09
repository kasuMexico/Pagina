<?php
/*******************************************************************************************
 * Constructor de tarjetas sociales y redirección con Open Graph
 * 03/11/2025 – Revisado por JCCM
 * kasu.com.mx/constructor.php?datafb=NDc=
 * Qué hace:
 * - Decodifica el parámetro GET base64 `datafb` con formato "<IdCupon>|<IdUsr>".
 * - Guarda en sesión la tarjeta y el usuario.
 * - Busca el registro activo en PostSociales y construye metadatos Open Graph.
 * - Muestra una página puente y redirige a la URL del post.
 *
 * Adecuaciones PHP 8.2:
 * - Validación estricta de entrada (base64 strict, existencia de índices).
 * - Prevención de “Undefined index/variable”.
 * - Uso de mysqli preparado para evitar inyección.
 * - Reemplazo de short open tags por <?php / <?=.
 *******************************************************************************************/

//indicar que se inicia una sesion
session_start();

//Requerimos el archivo de librerias *JCCM
require_once 'eia/librerias.php';

//Muestra de Ligas para Distribuidores
//https://kasu.com.mx/constructor.php?datafb=NDc=

//Cosntuimos el archivo
$datafb = filter_input(INPUT_GET, 'datafb', FILTER_UNSAFE_RAW) ?? '';

if ($datafb === '') {
    http_response_code(400);
    exit('Parámetro datafb faltante.');
}

// Decodificar base64 en modo estricto
$IdCupon = base64_decode($datafb, true);
if ($IdCupon === false) {
    http_response_code(400);
    exit('Contenido datafb inválido.');
}

//Deconstruimos el archivo
$ext = explode('|', $IdCupon);

// Validar que existan al menos 2 segmentos: [0]=IdCupon/IdPost, [1]=IdUsr
if (!isset($ext[0], $ext[1])) {
    http_response_code(400);
    exit('Estructura de datafb inválida.');
}

// Normalizar tipos
$IdPost = (int)$ext[0];
$IdUsr  = (string)$ext[1];

//Cupon usado
$_SESSION["tarjeta"] = $IdPost;
$_SESSION["IdUsr"]   = $IdUsr;

//realizamos la consulta
// 03/11/2025 – Revisado por JCCM: preparar sentencia para seguridad
$Reg = null;
if ($stmt = $mysqli->prepare("SELECT * FROM PostSociales WHERE Status = 1 AND Id = ?")) {
    $stmt->bind_param('i', $IdPost);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $Reg = $res->fetch_assoc();
    }
    $stmt->close();
}

if (!$Reg) {
    http_response_code(404);
    exit('No se encontró la tarjeta solicitada o no está activa.');
}

//select imagen
if (($Reg['Tipo'] ?? '') === "Art") {
    $img = $Reg['Img'] ?? '';
} else {
    $imgBase = $Reg['Img'] ?? '';
    // Añadir prefijo absoluto solo si no viene ya con http(s)
    if ($imgBase !== '' && !preg_match('~^https?://~i', $imgBase)) {
        $img = "https://kasu.com.mx/assets/images/cupones/" . ltrim($imgBase, '/');
    } else {
        $img = $imgBase;
    }
}

// Sanitizar salidas HTML
$titulo = htmlspecialchars((string)($Reg['TitA'] ?? 'KASU'), ENT_QUOTES, 'UTF-8');
$descr  = htmlspecialchars((string)($Reg['DesA'] ?? ''), ENT_QUOTES, 'UTF-8');
$dest   = htmlspecialchars((string)($Reg['Dire'] ?? 'https://www.kasu.com.mx'), ENT_QUOTES, 'UTF-8');
// Imagen puede ir cruda en OG si es URL absoluta; para atributo alt sí sanitizamos
$imgAlt = 'imagen de tarjeta kasu';

// URL canónica de este constructor con el mismo datafb
$selfUrl = 'https://kasu.com.mx/constructor.php?datafb=' . rawurlencode($datafb);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="/assets/images/kasu_logo.jpeg">
    <title><?= $titulo; ?></title>
    <meta name="description" content="<?= $descr; ?>">
    <meta name="robots" content="noindex,nofollow">
    <script async src="eia/javascript/recargar.js" type="text/javascript"></script>
    <meta http-equiv="Refresh" content="1;url=<?= $dest; ?>" />
    <!-- Meta descripciones de Facebook / Open Graph -->
    <meta property="og:url" content="<?= htmlspecialchars($selfUrl, ENT_QUOTES, 'UTF-8'); ?>" />
    <meta property="og:type" content="article"/>
    <meta property="og:title" content="<?= $titulo; ?>" />
    <meta property="og:description" content="<?= $descr; ?>" />
    <?php if (!empty($img)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" />
    <?php endif; ?>
    <meta property="fb:app_id" content="206687981468176" />
</head>
<body onload="enviarDatos(); return false" style="text-align: center;">
    <?php if (!empty($img)): ?>
    <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= $imgAlt; ?>" style="display:none;">
    <?php endif; ?>
    <div style="text-align: center; margin-top: 40em;">
        <img src="/assets/images/kasu_logo.jpeg" style="width: 20%;" alt="logo kasu servicios a futuro">
    </div>

    <!-- Registro de eventos -->
    <form name="formulario" action="">
        <!-- <p>Event</p>  -->
        <input type="text" name="Event" id="Event" value="Tarjeta" style="display: none;"/>
        <!-- <p>Cupon</p>  -->
        <input type="text" name="Cupon" id="Cupon" value="<?= $IdPost; ?>" style="display: none;"/>
        <!-- <p>Usuario</p>  -->
        <input type="text" name="Usuario" id="Usuario" value="<?= htmlspecialchars($IdUsr, ENT_QUOTES, 'UTF-8'); ?>" style="display: none;"/>
        <!-- Retorna el valor del evento registrado -->
        <input type="text" name="RegAct" id="RegAct" style="display: none;" />
    </form>
</body>
</html>