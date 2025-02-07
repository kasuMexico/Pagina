<?php
class Seguridad{
/**************************************************************************************************************
  Esta funcion valida que una curp sea correcta y retorna los valores contenidos en la misma en forma de array
          $Curp => es la clave curp a consultar
*************************************************************************************************************/
    public function peticion_get($Curp){
            //$url = "https://conectame.ddns.net/rest/api.php?m=curp&user=prueba&pass=sC%7D9pW1Q%5Dc&val=".$Curp;
            $url = "https://conectame.ddns.net/rest/api.php?m=curp&user=Kasu&pass=]Q*[4Jt7eBw5!aY5&val=".$Curp;
            $conexion = curl_init();
            // --- Url
            curl_setopt($conexion, CURLOPT_URL,$url);
            // --- Petición GET.
            curl_setopt($conexion, CURLOPT_HTTPGET, TRUE);
            // --- Cabecera HTTP.
            curl_setopt($conexion, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
            // --- Para recibir respuesta de la conexión.
            curl_setopt($conexion, CURLOPT_RETURNTRANSFER, 1);
            // --- Respuesta
            $respuesta=curl_exec($conexion);
            //Si contiene error retorna el error
            if($respuesta===false){
                return "error";
            }
            //Cerramos la conexion
            curl_close($conexion);
            //Comvertimos en array
            return json_decode($respuesta, true);
    }
/**************************************************************************************************************
      Esta funcion verifica el valor de un token generado por la api para validarlo
              $Curp => es la clave curp a consultar
              Las funciones quie quieran validar el token deberan contener los datos de tiempo con que se generaron para poder obtenerlos y estas siempre iran con el prefijo  token_data
*************************************************************************************************************/
    //Funcion que Verifica que el los Token sean correctos y validos
    public function verificarToken($Token_Auth,$data,$Secret_KEY) {
        //Aterrizamos los datos a utilizar para generar los tokens
        $timestamp    = $data['token_data']['timestamp'];
        $expires_in   = $data['token_data']['expires_in'];
        $curp_en_uso  = $data['curp_en_uso'];
        //Generamos la Primer  firma con el payload
        $Firma_A = hash_hmac('sha256',$curp_en_uso,$Secret_KEY);
        //Armamos el array para la generacion de la firma
        $token_data = array(
            "timestamp"   => $timestamp,
            "expires_in"  => $expires_in
        );
        //Convertir los datos en formato JSON.
        $token_data_json = json_encode($token_data);
        //Generamos la Segunda  firma con el payload
        $Firma_B = hash_hmac('sha256',$token_data_json,$Firma_A);

        if($Firma_B != $Token_Auth){
          return false; // token inválido
          //return $Firma_B."|".$Token_Auth;
        }
        //Actualizamos el tiempo en que se genero al tiempo
        $Token_expir = $timestamp + $expires_in;
        // verificar si el token ha expirado
        if ($Token_expir < time()) {
            return "exced_time"; // token expirado
        }
        // si se han pasado todas las verificaciones, el token es válido
        return true;
    }
/**************************************************************************************************************************
          Esta funcion valida un usuario y una contraseña;
          1.- $c0   => Recibe la Conexion a la base de datos
          2.- $usr  => Recibe el usuario
          2.- $pass => Recibe la contraseña
****************************************************************************************************************************/
        public function ValidarUsrAPI($c0,$usr,$Agent){
          // Separamos el string en dos partes usando la función explode
          $partes = explode("_", $Agent);
          // La primera parte del string es el texto "Api_kasu_"
          $Usuario = $partes[0];
          // La segunda parte del string es el número
          $Unico = $partes[1];
          //Crear consulta
          $sql = "SELECT * FROM Secret_KEY WHERE Usuario = '".$Usuario."' AND Id = '".$Unico."' AND IdUsuario = '".$usr."'";
          //Realiza consulta
          $res = mysqli_query($c0, $sql);
          //Si existe el registro se asocia en un fetch_assoc
          if($Reg=mysqli_fetch_assoc($res)){
            //Validamos que las claves esten vigentes
            if(isset($Reg['Status'])){
              return false; //Si esta definido el Secret_KEY se ha dado de Baja
            } else {
              //El campo Status esta Definido y en Valor de NULL
              return $Reg['Usuario']."_".$Reg['Id'];
            }
          }
        }
/***********************************************************************************
Esta funcion debe generar un hash con todos los datos de el cliente registrados en la venta;
* Usuario que vendio
* Producto
* Precio Vta
* Clave curp
* Fecha de registro

$c0 => Conexion
$pr => Id del contacto=767
***********************************************************************************/
    public function Firma($c0,$Pr,$sub){
    /*busqueda de los datos*/
    //crea consulta SELECT Contacto.id, Contacto.Usuario, Contacto.Producto, Usuario.ClaveCurp, Venta.Subtotal, Venta.FechaRegistro FROM Contacto, Usuario, Venta WHERE (Contacto.id = '$Pr') and (Contacto.id = Usuario.IdContact ) and (Contacto.id = Venta.IdContact) and (Venta.IdFIrma = 0)
    $aguja = "SELECT Contacto.id, Contacto.Usuario, Contacto.Producto, Usuario.ClaveCurp, Contacto.FechaRegistro FROM Contacto, Usuario WHERE (Contacto.id = '$Pr') and ( Usuario.IdContact= '$Pr')";
    //si se realiza la consulta
    if($hilos = $c0->query($aguja)){
      $tela = $hilos ->fetch_assoc();
      $costura = $tela['Usuario'].$tela['Producto'].$tela['ClaveCurp'].$sub.$tela['FechaRegistro'];
      /*Generar el hash*/
      $firma = hash("adler32", $costura, FALSE);
      //echo "$firma<br>";
      return $firma;
    }
  }
}
