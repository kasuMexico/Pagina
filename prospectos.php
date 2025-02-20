<?
//Requerimos el archivo de librerias *JCCM
  require_once 'eia/librerias.php';
//Convertios las variables desde get 64
  $data = base64_decode($_GET['data']);
  $usr = base64_decode($_GET['Usr']);
  $SerFb = base64_decode($_GET['SerFb']);
  $Origen = base64_decode($_GET['Ori']);
  //Imprimimos el array para ver q datos se estan enviando
  //ingresos base 64
  //FUNERARIO
  //https://kasu.com.mx/prospectos.php?data=RlVORVJBUklP
  //SEGURIDAD PUBLICA
  //https://kasu.com.mx/prospectos.php?data=UE9MSUNJQVM=
  //UNIVERSITARIO
  //https://kasu.com.mx/prospectos.php?data=VU5JVkVSU0lUQVJJTw==
  //RETIRO
  //https://kasu.com.mx/prospectos.php?data=UkVUSVJP
  //DISTRIBUIDOR
  //https://kasu.com.mx/prospectos.php?data=RElTVFJJQlVJRE9S
  //GENERAR CITAS
  //https://kasu.com.mx/prospectos.php?data=Q0lUUkVH
  //GENERAR CITAS SERVICIO RETIRO
  //https://kasu.com.mx/prospectos.php?data=Q0lUUkVH&SerFb=UkVUSVJP&Ori=ZmI=
  //GENERAR CITAS SERVICIO FUNERARIO
  //https://kasu.com.mx/prospectos.php?data=Q0lUUkVH&SerFb=RlVORVJBUklP&Ori=ZmI=
  //GENERAR CITAS INVERSION UNIVERSITARIA
  //https://kasu.com.mx/prospectos.php?data=Q0lUUkVH&SerFb=VU5JVkVSU0lUQVJJTw==&Ori=ZmI=
  //GENERAR CITAS DISTRIBUIDOR
  //https://kasu.com.mx/prospectos.php?data=Q0lUUkVH&SerFb=RElTVFJJQlVJRE9S&Ori=ZmI=
  //GENERAR CITAS CORREOS
  //https://kasu.com.mx/prospectos.php?data=Q0lUQQ==&Usr=NDU=
  //valor de Boton de registros
  $Btn = "Registro";
  //Imagen lateral de el formulario de registro
  $ImgDer = "https://kasu.com.mx/assets/images/registro/familiaformulario.png";
  //Registo de archivo constructor
  if($data == "FUNERARIO"){
    $Btn = "Registro";
    $Titu = "¡Estas un paso más cerca!";
    $text = "El equipo KASU esta preparando tu cotizacion, déjanos tus datos";
    $TxtBtn = "Registrarme y Continuar";
    $ImgDer = "https://kasu.com.mx/assets/images/gasto_funerario.svg";
    $ImgSeo = "https://kasu.com.mx/assets/images/registro/funerario.png";
  }elseif($data == "UNIVERSITARIO"){
    $Btn = "Registro";
    $Titu = "Inversion Universitaria";
    $text = "Estás cerca de asegurar la educación Universitaria de tu hijo";
    $TxtBtn = "Registrarme";
    $ImgDer = "https://kasu.com.mx/assets/images/gasto_universitario.svg";
    $ImgSeo = "https://kasu.com.mx/assets/images/registro/universidad.png";
  }elseif($data == "RETIRO"){
    $Btn = "Registro";
    $Titu = "Servicio de Retiro";
    $text = "Registrate y en un momento te contactara alguien de nuestro equipo";
    $TxtBtn = "Registrarme";
    $ImgDer = "https://kasu.com.mx/assets/images/gasto_retiro.svg";
    $ImgSeo = "https://kasu.com.mx/assets/images/registro/retiro.png";
  }elseif($data == "POLICIAS"){
    $Btn = "Registro";
    $Titu = "Contacta con un agente";
    $text = "El personal de seguridad se merece el mejor respaldo en los momentos mas difíciles de su vida";
    $TxtBtn = "Contactar";
    $ImgDer = "https://kasu.com.mx/assets/images/gasto_policias.svg";
    $ImgSeo = "https://kasu.com.mx/assets/images/registro/retiro.png";
  }elseif($data == "DISTRIBUIDOR"){
    $Titu = "Agente Externo";
    $text = "Felicidades estas a un paso de generar ingresos desde tu celular";
    $TxtBtn = "Recibir mas Info";
    $ImgDer = "https://kasu.com.mx/assets/images/padres_con_hijos.jpeg";
  }elseif($data == "CITREG"){
    $text = "Registra el dia que puedas recibir una llamada de uno de nuestros agentes";
    $Titu = "Registrar Cita";
    $TxtBtn = "Registrar Cita";
    $Btn = "Cita";
  }else{
    $text = "te enviaremos por correo electronico la informacion necesaria para que conozcas todo sobre KASU";
    $Titu = "Registrate";
    $TxtBtn = "Registrarme";
    $Btn = "Cita";
  }
  //Registros
  if(!empty($_GET['Usr'])){
      //Buscamos si ya se ha registrado previamente
      $Reg2 = $basicas->BuscarCampos($pros,'Id','Distribuidores','IdProspecto',$usr);
      //Reenvios segun el resultado
        if(empty($Reg2) or $data == "CITREG" or  $data == "CITA"){
      //Busqueda de prospectos
        $venta = "SELECT * FROM prospectos WHERE Id = ".$_GET['Usr'];
      //Realiza consulta
            $res = mysqli_query($pros, $venta);
      //Si existe el registro se asocia en un fetch_assoc
            if($Reg = mysqli_fetch_assoc($res)){}
      //constructor de archivo
            if($data == "CITA"){
              $Titu = "Cita Telefonica";
              $text = "Registra el dia que puedas recibir una llamada de uno de nuestros ejecutivos, !solo selecciona el dia y la hora¡ ";
              $Btn = "Cita";
              $TxtBtn = "Registrar Cita";
            }else{
              $text = "Concluye tu Registro";
              $Btn = "PerDIstri";
              $TxtBtn = "Concluir Registro";
            }
      }else{
      //Creamos la respuesta
          $Msg = "lo sentimos este correo electronico ya se ha usado, solicita uno nuevo llamando al Centro de atencion al cliente";
      //echo "Se ha enviado un email al correo registrado.";
          header('Location: https://kasu.com.mx/index.php?Msg='.$Msg);
      }
  }
//alertas de correo electronico
  require_once 'login/php/Selector_Emergentes_Ml.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    		<!-- Google Tag Manager -->
    		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    		})(window,document,'script','dataLayer','GTM-MCR6T6W');</script>
    		<!-- End Google Tag Manager -->
    		<meta charset="utf-8">
        <title>Prospecto | <?if(isset($_GET['data'])){echo $data;}else{echo "KASU";}?></title>
    		<meta name="description" content="<?echo $text;?>">
    		<meta name="keywords" content="Prospecto">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta property="og:url" content="https://kasu.com.mx/prospectos.php?data=<? echo $_GET['data'];?>" />
        <meta property="og:type" content="Registro" />
        <meta property="og:title" content="Registro | <?if(isset($_GET['data'])){echo $data;}else{echo "KASU";}?>" />
        <meta property="og:description" content="<?echo $Titu.$text;?>" />
        <meta property="og:image" content="<?echo $ImgSeo;?>" />
    		<link rel="canonical" href="https://kasu.com.mx/prospectos.php?data=<? echo $_GET['data'];?>">
    		<link rel="icon" href="assets/images/kasu_logo.jpeg">
    		<link rel="stylesheet" href="assets/css/Compra.css">
    		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" media="screen">
    		<script type="text/javascript" src="eia/javascript/Registro.js"></script>
    </head>
    <body><!--onload="localize()"-->
        <!-- Chat de Facebook -->
        <?
        //require_once 'html/CodeFb.php';
        ?>
    		<!-- Google Tag Manager (noscript) -->
    		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MCR6T6W"
    		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    		<!-- End Google Tag Manager (noscript) -->
    		<section id="Formulario">
        		<div class="container-fluid">
            		<div class="row mh-100vh">
                		<div class="img_familia" class="Contenedor">
                		      <img src="<?echo $ImgDer;?>" style=" width: 500px;" align="left" alt="Imagen Formulario Registro">
                		</div>
                		<div class="AreaTrabajo">
                				<div class="Contenedor">
                				    <!--form method="POST" id="form" action="login/php/Registro_Prospectos.php"-->
                            <form method="POST" id="<? if(!empty($SerFb)){echo "cita-".$SerFb;}else{echo "Prospecto-".$data;} ?>" action="login/php/Registro_Prospectos.php">
                    						<div class="logo">
                    							<a href="/"><img src="assets/images/kasu_logo.jpeg"></a>
                    						</div>
                    						<h1 style="text-align: center;"><?echo $Titu;?></h1>
                                <h3 style="text-align: center;"><?echo $text;?></h3>
                                <br>
                    						<div class="Formulario">
                        							<!--Insercion de registros de Gps y fingerprint-->
                        							<!-- <div id="Gps" style="display: none;"></div> -->
                        							<div id="FingerPrint" style="display: none;"></div>
                        							<input type="text" name="Host" value="<?PHP echo $_SERVER['PHP_SELF'];?>" style="display: none;">
                        							<input type="text" name="Cupon" id="Cupon" value="<?PHP echo $_SESSION["data"];?>" style="display: none;">
                                      <input type="text" name="Origen" id="Cupon" value="<?PHP echo $Origen;?>" style="display: none;">
                        							<!--Insercion de registros de Gps y fingerprint-->
                                      <?
                                      if(!empty($_GET['Usr'])){
                                        echo '
                                        <input type="text" name="na" disabled value="'.$Reg['FullName'].'">
                          							<input type="email" name="Ma" disabled  value="'.$Reg['Email'].'" >
                          							<input type="tel" name="Telefo" disabled value="'.$Reg['NoTel'].'" >
                                        <input type="text" name="name" style="display: none;" value="'.$Reg['FullName'].'">
                                        <input type="email" name="Mail" style="display: none;" value="'.$Reg['Email'].'" >
                                        <input type="tel" name="Telefono" style="display: none;" value="'.$Reg['NoTel'].'" >
                                        ';
                                      }else{
                                        //Registro de Datos Generales de formulario de registro sin Usuario
                                        echo '
                                        <input type="text" name="name" placeholder="Nombre" value="" required >
                          							<input type="email" name="Mail" placeholder="Correo electronico" value="" required >
                          							<input type="tel" name="Telefono" placeholder="Telefono" value="" required >
                                        ';
                                        //Registro de fecha de Nacimiento segun Producto
                                        if($data != "DISTRIBUIDOR" and $data != "CITREG" and $data != "POLICIAS"){
                                          //Label que arroja el registro segun el producto seleccionado
                                          if($data == "UNIVERSITARIO"){
                                            echo '
                                                <label for="exampleFormControlSelect1">Fecha de nacimiento del afiliado</label>
                                            ';
                                          }else{
                                            echo '
                                                <label for="exampleFormControlSelect1">Ingresa tu fecha de nacimiento</label>
                                            ';
                                          }
                                          //Imprimimos el imputa de registro de fecha de Nacimiento
                                            echo '
                                                <input type="date" name="FechaNac" placeholder="Fecha Nacimiento" required>
                                            ';
                                        }elseif ($data == "POLICIAS" ) {
                                          echo '
                                                <label for="exampleFormControlSelect1">Cuantos Oficiales quieres Asegurar</label>
                                                <input type="number" name="Oficiales" placeholder="Numero de Oficiales" required>
                                          ';
                                        }
                                      }
                                      if(isset($_GET['data'])){
                                        //Aterrizar el servicio registrado
                                        if(empty($_GET['SerFb'])){
                                          //SI no esta lleno el servicio de facebook lanza los registros
                                          echo '<input type="text" name="Servicio" id="Cupon" value="'.$data.'" style="display: none;">';
                                        }else{
                                          //SI esta lleno captura las variables
                                          echo '<input type="text" name="Servicio" value="'.$SerFb.'" style="display: none;">';
                                        }
                                      }elseif(!empty($_GET['Usr'])){
                                        echo '
                                        <input type="number" name="Clabe" placeholder="Clabe Interbancaria (18 Digitos)"required>
                                        <input type="text" name="Direccion" placeholder="Domicilio actual" required>
                                        <input type="number" name="Usr" value="'.$usr.'" style="display: none;">
                                        ';
                                      }if($data == "CITA"){
                                        //SI es una cita desde correos electronicos imprime los datos pre llenos del Usuario
                                        echo '
                                              <input type="number" name="IdProspecto" value="'.$usr.'" style="display: none;">
                                              <label for="exampleFormControlSelect1">Fecha de cita</label>
                                              <input type="date" name="FechaCita" placeholder="Fecha de la llamada"required>
                                              <label for="exampleFormControlSelect1">Hora de cita</label>
                                              <input type="time" name="HoraCita" placeholder="Hora de la cita" required>
                                              ';
                                      }elseif($data == "CITREG"){
                                        //SI es registro de cita pide los datos del prospecto
                                        echo '
                                              <label for="exampleFormControlSelect1">Fecha de cita</label>
                                              <input type="date" name="FechaCita" placeholder="Fecha de la llamada"required>
                                              <label for="exampleFormControlSelect1">Hora de cita</label>
                                              <input type="time" name="HoraCita" placeholder="Hora de la cita" required>
                                              ';
                                        if(empty($_GET['SerFb'])){
                                          echo '
                                                <label for="exampleFormControlSelect1">Servicio de Interes</label>
                                                <select class="" name="Servicio">
                                                    <option value="FUNERARIO">GASTOS FUNERARIOS</option>
                                                    <option value="UNIVERSITARIO">INVERSION UNIVERSITARIA</option>
                                                    <option value="RETIRO">AHORRO PARA EL RETIRO</option>
                                                </select>
                                                ';
                                        }
                                      }
                                      ?>
                    						</div>
                                <br>
                  							<div class="Formulario">
                  								  <input type="submit" style="background-color:#012F91; color:white;" name="<?echo $Btn;?>" value="<?echo $TxtBtn;?>" id="BtnnContactoVta">
                  							</div>
                                <br><br>
                  							<div class="Ligas">
                    								<a style="color: #911F66" href="/">Regresar a KASU</a>
                    								<a style="color: #012F91" href="https://kasu.com.mx/terminos-y-condiciones.php">Términos y condiciones</a>
                  							</div>
                					   </form>
                				 </div>
                			</div>
            			</div>
        			</div>
    		</section>
    		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    		<script type="text/javascript" src="eia/javascript/AlPie.js"></script>
    		<script type="text/javascript" src="eia/javascript/finger.js"></script>
    		<!-- <script type="text/javascript" src="eia/javascript/localize.js"></script> -->
    		<script type="text/javascript">
    			function selectServ(e) {
    				if (e == "Funerario") {
    					document.getElementById("servicio").innerHTML = '<input type="text" name="Producto" style="border:none;text-align: center;center;pointer-events: none;" value="Funerario">';
    				} else if (e == "Universidad") {
    					document.getElementById("servicio").innerHTML = '<input type="text" name="Producto" style="border:none;text-align: center;center;pointer-events: none;" value="Universidad"	>';
    				} else {
    					document.getElementById("servicio").innerHTML = "";
    				}
    			}

    		</script>
    	  <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js" ></script>
    </body>
</html>
