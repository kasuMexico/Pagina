<?PHP
class Seguridad{
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
/*********************************************************************************
    Esta funcion retorna la Clave Publica de un certificado;
     1.- $c0 => Recibe la Conexion a la base de datos
     1.- $d8 => Recibe el id del Usuario
*********************************************************************************/
    public function ObtenerClave($c0,$d8){
        $Dir = Basicas::BuscarCampos($c0," DireccionClaves","Usuario","Id",$d8);
    //Consulta para el certificado
        $Cons = "SELECT X509 FROM ClavesUnicas WHERE Direccion  = '$Dir'";
    //se ejecuta la consulta
        $resp = mysqli_query($c0, $Cons);
    //se aloja el resultado en un array
        $Cert = mysqli_fetch_array($resp, MYSQLI_ASSOC);
    //Se igualan las variables y se guarda el CERTificado en una variablevariable
        foreach($Cert as $val){
    //Almacena el Certificado en la variable $X509
        $X509 = $val;
    //Obtener la clave publica del certificado digital
        $pub_key = openssl_pkey_get_public($X509);
        $keyData = openssl_pkey_get_details($pub_key);
          if(!$keyData['key']){
              return "Error en el certificado";
          } else {
              return $keyData['key'];
          }
      }
    }
}
