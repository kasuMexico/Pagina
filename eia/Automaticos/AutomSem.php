<?php
session_start();

// Incluir el archivo de funciones (se asume que este archivo carga las clases Basicas, Correo, Financieras, Seguridad y FunctionUsageTracker)
require_once '../php/Funciones_kasu.php';

// Registrar la variable empresa
$empresa = 'https://www.kasu.com.mx/';

// Registrar la variable Asunto para los artículos sugeridos
$asunto = "ARTÍCULOS SUGERIDOS";

// --- CONSULTA PARA OBTENER ARTÍCULOS DEL BLOG ---
// Se construye la consulta para obtener los artículos publicados de wp_posts
$consulta = "SELECT * FROM wp_posts WHERE post_status = 'publish' AND ping_status = 'open'";
$resultado = mysqli_query($cnp, $consulta);

if (!$resultado) {
    die("Error en la consulta de artículos: " . mysqli_error($cnp));
}

$cont = 0;
$id = array();
$dir = array();
$tit = array();

while ($fila = mysqli_fetch_array($resultado)) {
    $id[$cont]  = $fila['ID'];
    $dir[$cont] = $fila['post_name'];
    $tit[$cont] = $fila['post_title'];
    $cont++;
}

// Seleccionar 4 artículos aleatorios
$claves_aleatorias = array_rand($dir, 4);

// Para cada uno, se obtiene el target_post_id; si es 0 se usa el ID original
$temp = Basicas::BuscarCampos($cnp, "target_post_id", "wp_yoast_seo_links", "post_id", $id[$claves_aleatorias[0]]);
$pst_parA = ($temp == 0) ? $id[$claves_aleatorias[0]] : $temp;

$temp = Basicas::BuscarCampos($cnp, "target_post_id", "wp_yoast_seo_links", "post_id", $id[$claves_aleatorias[1]]);
$pst_parB = ($temp == 0) ? $id[$claves_aleatorias[1]] : $temp;

$temp = Basicas::BuscarCampos($cnp, "target_post_id", "wp_yoast_seo_links", "post_id", $id[$claves_aleatorias[2]]);
$pst_parC = ($temp == 0) ? $id[$claves_aleatorias[2]] : $temp;

$temp = Basicas::BuscarCampos($cnp, "target_post_id", "wp_yoast_seo_links", "post_id", $id[$claves_aleatorias[3]]);
$pst_parD = ($temp == 0) ? $id[$claves_aleatorias[3]] : $temp;

// Obtener el ID mínimo de la imagen (post) para cada artículo
$IdImgA = Basicas::Min1Dat($cnp, "ID", "wp_posts", "post_parent", $pst_parA);
$IdImgB = Basicas::Min1Dat($cnp, "ID", "wp_posts", "post_parent", $pst_parB);
$IdImgC = Basicas::Min1Dat($cnp, "ID", "wp_posts", "post_parent", $pst_parC);
$IdImgD = Basicas::Min1Dat($cnp, "ID", "wp_posts", "post_parent", $pst_parD);

// Obtener la primera imagen (guid) de cada artículo
$imag1 = Basicas::BuscarCampos($cnp, "guid", "wp_posts", "ID", $IdImgA);
$imag2 = Basicas::BuscarCampos($cnp, "guid", "wp_posts", "ID", $IdImgB);
$imag3 = Basicas::BuscarCampos($cnp, "guid", "wp_posts", "ID", $IdImgC);
$imag4 = Basicas::BuscarCampos($cnp, "guid", "wp_posts", "ID", $IdImgD);

// Titulos aleatorios
$Titulo1 = $tit[$claves_aleatorias[0]];
$Titulo2 = $tit[$claves_aleatorias[1]];
$Titulo3 = $tit[$claves_aleatorias[2]];
$Titulo4 = $tit[$claves_aleatorias[3]];

// Construir las URLs de los artículos
$dirUrl1 = $empresa . "blog/" . $dir[$claves_aleatorias[0]];
$dirUrl2 = $empresa . "blog/" . $dir[$claves_aleatorias[1]];
$dirUrl3 = $empresa . "blog/" . $dir[$claves_aleatorias[2]];
$dirUrl4 = $empresa . "blog/" . $dir[$claves_aleatorias[3]];

/*************************************************************************************************************
                                      ENVÍO DE CORREOS A PROSPECTOS
*************************************************************************************************************/
$MxArt = Basicas::MaxDat($pros, "Id", "prospectos");
$i = 1;

// Enviar un correo de artículos a cada prospecto
while ($i <= $MxArt) {
    // Se verifica si el prospecto tiene el servicio cancelado
    $Fa = Basicas::BuscarCampos($pros, "Cancelacion", "prospectos", "Id", $i);
    if (empty($Fa)) {
        // Se obtienen los datos del prospecto
        $FullName  = Basicas::BuscarCampos($pros, "FullName", "prospectos", "Id", $i);
        $RegEmail  = Basicas::BuscarCampos($pros, "Email", "prospectos", "Id", $i);
        $Rl        = Basicas::BuscarCampos($pros, "Sugeridos", "prospectos", "Id", $i);

        // Se crea el correo utilizando la plantilla "ARTÍCULOS SUGERIDOS"
        $mensa = Correo::Mensaje(
            $asunto,
            $FullName,
            $empresa,
            $dirUrl1,
            $imag1,
            $Titulo1,
            '',       // No se especifica descripción para este caso
            $dirUrl2,
            $imag2,
            $Titulo2,
            '',
            $dirUrl3,
            $imag3,
            $Titulo3,
            '',
            $dirUrl4,
            $imag4,
            $Titulo4,
            '',
            $i
        );
        // Se envía el correo (se asume que EnviarCorreo está correctamente implementado en la clase Correo)
        Correo::EnviarCorreo($FullName, $RegEmail, $asunto, $mensa);

        // Se incrementa el valor de "Sugeridos" y se actualiza el registro
        $Rl++;
        Basicas::ActCampo($pros, "prospectos", "Sugeridos", $Rl, $i);
    }
    $i++;
}

/*************************************************************************************************************
                                      ENVÍO DE CORREOS A CLIENTES
*************************************************************************************************************/
$sql9 = "SELECT * FROM Venta WHERE Status != 'CANCELADO'";
$S629 = $mysqli->query($sql9);
if (!$S629) {
    die("Error en la consulta de ventas: " . $mysqli->error);
}
while ($S659 = mysqli_fetch_array($S629)) {
    // Obtener el Id de contacto de la venta
    $i = Basicas::BuscarCampos($mysqli, "IdContact", "Venta", "Id", $S659[0]);
    // Verificar si el servicio está cancelado en la tabla Contacto
    $Fa = Basicas::BuscarCampos($mysqli, "Cancelacion", "Contacto", "id", $i);
    if (empty($Fa)) {
        $FullName = Basicas::BuscarCampos($mysqli, "Nombre", "Usuario", "IdContact", $i);
        $RegEmail = Basicas::BuscarCampos($mysqli, "Mail", "Contacto", "id", $i);
        if (!empty($RegEmail)) {
            $Rl = Basicas::BuscarCampos($mysqli, "Sugeridos", "Contacto", "id", $i);
            // Se utiliza la plantilla "ARTÍCULOS SUGERIDOS" con un indicador 'Baja' en el sexto parámetro (según lo definido en Funciones_kasu.php)
            $mensa = Correo::Mensaje(
                $asunto,
                $FullName,
                $empresa,
                $dirUrl1,
                $imag1,
                $Titulo1,
                'Baja',
                $dirUrl2,
                $imag2,
                $Titulo2,
                '',
                $dirUrl3,
                $imag3,
                $Titulo3,
                '',
                $dirUrl4,
                $imag4,
                $Titulo4,
                '',
                $i
            );
            Correo::EnviarCorreo($FullName, $RegEmail, $asunto, $mensa);
            $Rl++;
            Basicas::ActCampo($mysqli, "Contacto", "Sugeridos", $Rl, $i);
        }
    }
}

/*************************************************************************************************************
                                      ENVÍO DE CORREOS DE VENTA
*************************************************************************************************************/
$sql1 = "SELECT * FROM prospectos";
$res1 = $pros->query($sql1);
if ($res1) {
    foreach ($res1 as $Reg1) {
        // Si se solicita cita y el servicio no es DISTRIBUIDOR
        if ($Reg1['Automatico'] == 1 && $Reg1['Servicio_Interes'] != "DISTRIBUIDOR") {
            // Definir asunto en base al estado del prospecto
            if ($Reg1['Estado'] == 0) {
                $asunto = 'CONOCENOS UN POCO MÁS';
            } else {
                $asunto = Basicas::Buscar2Campos($pros, 'Asunto', 'correos', 'Seguimiento', $Reg1['Estado'], 'Tipo', 'VENTA');
            }
            // Se definen parámetros según el asunto
            if ($asunto == "CONOCENOS UN POCO MÁS") {
                if ($Reg1['Servicio_Interes'] == "FUNERARIO") {
                    $servicio = "KASU Gastos Funerarios: Es un servicio que te permite pagar los gastos funerarios de un ser querido mediante una aportación mínima hoy.";
                    $DirServicio = "https://www.kasu.com.mx/productos.php?Art=1";
                    $Titulo1 = "https://kasu.com.mx/blog/category/tanatologia/";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                } elseif ($Reg1['Servicio_Interes'] == "UNIVERSITARIO") {
                    $servicio = "KASU Inversión universitaria: Permite invertir en un fideicomiso para pagar estudios universitarios en el futuro.";
                    $DirServicio = "https://www.kasu.com.mx/productos.php?Art=2";
                    $Titulo1 = "https://kasu.com.mx/blog/category/educacion/";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                } elseif ($Reg1['Servicio_Interes'] == "RETIRO") {
                    $servicio = "KASU Retiro: Invierte en un fideicomiso para contar con respaldo financiero al cumplir 65 años.";
                    $DirServicio = "https://www.kasu.com.mx/productos.php?Art=3";
                    $Titulo1 = "https://kasu.com.mx/blog/category/retiro/";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                }
                // Se crea el correo usando la plantilla correspondiente
                $mensa = Correo::Mensaje(
                    $asunto,
                    $Reg1['FullName'],
                    $empresa,
                    $servicio,
                    $DirServicio,
                    $Titulo1,
                    $Desc1,
                    $empresa,
                    '', '', '', '', '', '', '', '', '', '', '',
                    $Reg1['Id']
                );
            } elseif ($asunto == "¿AUN TIENES DUDAS?") {
                if ($Reg1['Servicio_Interes'] == "FUNERARIO") {
                    $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=1";
                    $Titulo1 = "https://kasu.com.mx/blog/category/tanatologia/";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                } elseif ($Reg1['Servicio_Interes'] == "UNIVERSITARIO") {
                    $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=2";
                    $Titulo1 = "https://kasu.com.mx/blog/category/educacion/";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                } elseif ($Reg1['Servicio_Interes'] == "RETIRO") {
                    $dirUrl1 = "https://www.kasu.com.mx/productos.php?Art=3";
                    $Titulo1 = "https://kasu.com.mx/blog/category/retiro/";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                }
                $mensa = Correo::Mensaje(
                    $asunto,
                    $Reg1['FullName'],
                    $empresa,
                    $dirUrl1,
                    '',
                    $Titulo1,
                    $Desc1,
                    '', '', '', '', '', '', '', '', '', '', '', '',
                    $Reg1['Id']
                );
            } elseif ($asunto == "CONOCENOS UN POCO MÁS") {
                if ($Reg1['Servicio_Interes'] == "FUNERARIO") {
                    $dirUrl2 = "https://www.kasu.com.mx/productos.php?Art=1";
                    $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0003.pdf";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                } elseif ($Reg1['Servicio_Interes'] == "UNIVERSITARIO") {
                    $dirUrl2 = "https://www.kasu.com.mx/productos.php?Art=2";
                    $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0009.pdf";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                } elseif ($Reg1['Servicio_Interes'] == "RETIRO") {
                    $dirUrl2 = "https://www.kasu.com.mx/productos.php?Art=3";
                    $dirUrl1 = "https://kasu.com.mx/Fideicomiso_F0010.pdf";
                    $Desc1 = "https://wa.me/525575016531?texto=Hola%20ingrese%20en%20la%20pagina%20de%20kasu...";
                }
                $Titulo1 = $Reg1['Servicio_Interes'];
                $mensa = Correo::Mensaje(
                    $asunto,
                    $Reg1['FullName'],
                    $empresa,
                    $dirUrl1,
                    'https://kasu.com.mx/letraspeq.php',
                    $Titulo1,
                    $Desc1,
                    $dirUrl2,
                    '', '', '', '', '', '', '', '', '', '', '',
                    $Reg1['Id']
                );
            }
            // Enviar el correo
            Correo::EnviarCorreo($Reg1['FullName'], $Reg1['Email'], $asunto, $mensa);
            // Registrar el estado del correo enviado
            $ValMail = Basicas::BuscarCampos($pros, 'Seguimiento', 'correos', 'Asunto', $asunto);
            Basicas::ActCampo($pros, "prospectos", "Estado", $ValMail, $Reg1['Id']);
        }
    }
}

// Cerrar conexiones a la base de datos
$pros->close();
$mysqli->close();
?>
