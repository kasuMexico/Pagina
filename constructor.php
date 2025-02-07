<?php
//indicar que se inicia una sesion
    session_start();
//Requerimos el archivo de librerias *JCCM
    require_once 'eia/librerias.php';
//Muestra de Ligas para Distribuidores
//https://kasu.com.mx/constructor.php?datafb=NDc=
//Cosntuimos el archivo
    $IdCupon = base64_decode($_GET['datafb']);
//Deconstruimos el archivo
    $ext = explode('|',$IdCupon);
//Cupon usado
    $_SESSION["tarjeta"] = $ext[0];
    $_SESSION["IdUsr"] = $ext[1];
//realizamos la consulta
    $venta = "SELECT * FROM PostSociales WHERE Status = 1 AND Id = ".$ext[0];
//Realiza consulta
    $res = mysqli_query($mysqli, $venta);
//Si existe el registro se asocia en un fetch_assoc
    if($Reg=mysqli_fetch_assoc($res)){
      //select imagen
      if($Reg['Tipo'] == "Art"){
        $img = $Reg['Img'];
      }else{
        $img = "https://kasu.com.mx/assets/images/cupones/".$Reg['Img'];
      }
?>
<!DOCTYPE html>
<html lang="es">
		<head>
      <meta charset="utf-8">
      <title><? echo $Reg['TitA'];?></title>
      <meta name="description" content="<? echo $Reg['DesA'];?>">
      <script async src="eia/javascript/recargar.js" type="text/javascript"></script>
      <meta http-equiv="Refresh" content="1;url=<? echo $Reg['Dire'];?>" />
      <!-- Meta drescripciones de facebook  -->
      <meta property="og:url" content="https://kasu.com.mx/constructor.php?datafb=<? echo $_GET['datafb'];?>" />
      <meta property="og:type" content="article"/>
      <meta property="og:title" content="<? echo $Reg['TitA'];?>" />
      <meta property="og:description" content="<? echo $Reg['DesA'];?>" />
      <meta property="og:image" content="<? echo $img;?>" />
      <meta property="fb:app_id" content="206687981468176" />
    </head>
		<body onload="enviarDatos(); return false">
        <img src="<? echo $img;?>" style="display: none;">
        <div style="text-align: center; margin-top: 40em;">
          <img src="/assets/images/kasu_logo.jpeg" style="width: 20%;" alt="logo kasu servicios a futuro">
        </div>
        <!-- Registro de eventos -->
        <form name="formulario" action="">
             <!-- <p>Event</p>  -->
            <input type="text" name="Event" id="Event" value="Tarjeta" style="display: none;"/>
             <!-- <p>Cupon</p>  -->
            <input type="text" name="Cupon" id="Cupon" value="<?PHP echo $ext[0]; ?>" style="display: none;"/>
            <!-- <p>Usuario</p>  -->
            <input type="text" name="Usuario" id="Usuario" value="<?PHP echo $ext[1]; ?>" style="display: none;"/>
            <!-- Retorna el valor del evento registrado -->
            <input type="texto" name="RegAct" id="RegAct" style="display: none;" />
        </form>
    </body>
</html>
<?
}
?>
