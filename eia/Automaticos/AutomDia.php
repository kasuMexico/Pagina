<?
//indicar que se inicia una sesion
session_start();
//inlcuir el archivo de funciones
require_once '../php/Funciones_kasu.php';
//Registramos los archivos automaticos
//Actualizamos las comisiones que han generado los prospectos
    Financieras::ActualComis($mysqli);
//Actualizamos las ventas
    Financieras::actualizaVts($mysqli);
//Realiamos el envio de los correos de lectura
//Realizamos el envio de correos de solicitud de citas
    $sql1 = "SELECT * FROM prospectos ";
    //Realiza consulta
    $res1 = $pros->query($sql1);
    //Si existe el registro se asocia en un fetch_assoc
    foreach ($res1 as $Reg1){
        //Validamos si el usuario esta solicitando cita
        if($Reg1['Automatico'] == 1 AND $Reg1['Servicio_Interes'] == "DISTRIBUIDOR"){
            //Se envia el correo electronico
            $asunto = "AGENDAR CITA";
            //COnvertimos el id del usuario en base64
            $UsrEncode = base64_encode('CITA');
            $dirUrl1 = base64_encode($Reg1['Id']);
            //Se crea el correo electronico para enviarlo segun los modelos
            $mensa = Correo::Mensaje($asunto,$Reg1['FullName'],$UsrEncode,$dirUrl1,'','','','','','','','','','','','','','','',$Reg1['Id']);
            //Se envia el correo electronico
            Correo::EnviarCorreo($Reg1['FullName'],$Reg1['Email'],$asunto,$mensa);
            //se registra el valor del correo enviado
            $ValMail = Basicas::BuscarCampos($pros,'Seguimiento','correos','Asunto',$asunto);
            //Se inserta el estado en la base de datos
            Basicas::ActCampo($pros,"prospectos","Estado",$ValMail,$Reg1['Id']);
        }
    }
    //Se cierra la conexion con la base de datos
    $pros->close();
    $mysqli->close();
