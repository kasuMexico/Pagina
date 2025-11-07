<?php
/**************************************************************************************************************
 * Archivo: Seguridad.php
 * Qué hace: Funciones de seguridad para API KASU: consulta CURP vía REST, verificación de token,
 *           validación de usuario API y generación de firma de venta.
 * Compatibilidad: PHP 8.2
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 *************************************************************************************************************/

require_once __DIR__ . '/FunctionUsageTracker.php';
require_once __DIR__ . '/Funciones_Basicas.php';

// Instancia global para funciones básicas
$basicas = $basicas ?? new Basicas();

class Seguridad {

  // Telemetría de uso
  use UsageTrackerTrait;

  /**************************************************************************************************************
    Esta funcion valida que una curp sea correcta y retorna los valores contenidos en la misma en forma de array
            $Curp => es la clave curp a consultar
  *************************************************************************************************************/
  public function peticion_get($Curp){
    // URL del servicio externo
    $url = "https://conectame.ddns.net/rest/api.php?m=curp&user=Kasu&pass=]Q*[4Jt7eBw5!aY5&val=".$Curp;

    // Inicializa cURL
    $conexion = curl_init();
    curl_setopt_array($conexion, [
      CURLOPT_URL            => $url,
      CURLOPT_HTTPGET        => true,
      CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS      => 3,
      CURLOPT_CONNECTTIMEOUT => 6,
      CURLOPT_TIMEOUT        => 10,
      CURLOPT_USERAGENT      => 'KASU-API/1.0 (+https://kasu.com.mx)'
    ]);

    // Ejecuta
    $respuesta = curl_exec($conexion);
    if ($respuesta === false) {
      // Cierra y retorna "error" como en la versión previa
      curl_close($conexion);
      return "error";
    }

    // Cierra conexión
    curl_close($conexion);

    // Decodifica JSON a arreglo asociativo; mantiene contrato de retorno (array|string "error")
    $data = json_decode($respuesta, true);
    // Si falla el decode, devuelve "error" para no romper flujos existentes
    return (is_array($data)) ? $data : "error";
  }

  /**************************************************************************************************************
        Esta funcion verifica el valor de un token generado por la api para validarlo
                $Curp => es la clave curp a consultar
                Las funciones quie quieran validar el token deberan contener los datos de tiempo con que se generaron para poder obtenerlos y estas siempre iran con el prefijo  token_data
  *************************************************************************************************************/
  // Funcion que Verifica que el los Token sean correctos y validos
  public function verificarToken($Token_Auth,$data,$Secret_KEY) {
    // Aterrizamos los datos a utilizar para generar los tokens
    // En PHP 8.2 evitamos avisos por índices inexistentes
    $timestamp    = isset($data['token_data']['timestamp']) ? (int)$data['token_data']['timestamp'] : 0;
    $expires_in   = isset($data['token_data']['expires_in']) ? (int)$data['token_data']['expires_in'] : 0;
    $curp_en_uso  = isset($data['curp_en_uso']) ? (string)$data['curp_en_uso'] : '';

    // Si faltan datos mínimos, invalida
    if ($timestamp <= 0 || $expires_in <= 0 || $curp_en_uso === '' || $Secret_KEY === '' || $Token_Auth === '') {
      return false;
    }

    // Generamos la Primer firma con el payload
    $Firma_A = hash_hmac('sha256', $curp_en_uso, $Secret_KEY);

    // Armamos el array para la generacion de la firma
    $token_data = array(
      "timestamp"   => $timestamp,
      "expires_in"  => $expires_in
    );

    // Convertir los datos en formato JSON.
    $token_data_json = json_encode($token_data, JSON_UNESCAPED_UNICODE);

    // Generamos la Segunda firma con el payload
    $Firma_B = hash_hmac('sha256', $token_data_json, $Firma_A);

    if ($Firma_B != $Token_Auth){
      return false; // token inválido
      //return $Firma_B."|".$Token_Auth;
    }

    // Actualizamos el tiempo en que se genero al tiempo
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
    $partes = explode("_", (string)$Agent, 2);
    // Protección por si el formato no es el esperado
    $Usuario = $partes[0] ?? '';
    $Unico   = $partes[1] ?? '';

    // Escapar valores para la consulta
    $UsuarioEsc = mysqli_real_escape_string($c0, $Usuario);
    $UnicoEsc   = mysqli_real_escape_string($c0, $Unico);
    $UsrEsc     = mysqli_real_escape_string($c0, (string)$usr);

    // Crear consulta
    $sql = "SELECT * FROM Secret_KEY WHERE Usuario = '".$UsuarioEsc."' AND Id = '".$UnicoEsc."' AND IdUsuario = '".$UsrEsc."'";

    // Realiza consulta
    $res = mysqli_query($c0, $sql);
    if ($res && ($Reg = mysqli_fetch_assoc($res))) {
      // Validamos que las claves esten vigentes
      // Nota: la lógica original devuelve false si 'Status' está definido (cualquier valor), y devuelve "Usuario_Id" si es NULL.
      if (array_key_exists('Status', $Reg) && $Reg['Status'] !== null) {
        return false; // Si está definido el Status distinto de NULL, se ha dado de baja
      } else {
        // El campo Status está definido y en valor de NULL o no existe
        return $Reg['Usuario']."_".$Reg['Id'];
      }
    }
    // Si no hay coincidencia, no retorna nada como en la versión previa
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
    // Escape de parámetros para compatibilidad 8.2 y seguridad
    $PrEsc  = mysqli_real_escape_string($c0, (string)$Pr);
    $subStr = (string)$sub;

    /* busqueda de los datos
       SELECT Contacto.id, Contacto.Usuario, Contacto.Producto, Usuario.ClaveCurp, Contacto.FechaRegistro
       FROM Contacto, Usuario
       WHERE (Contacto.id = '$Pr') and ( Usuario.IdContact= '$Pr')
    */
    $aguja = "SELECT Contacto.id, Contacto.Usuario, Contacto.Producto, Usuario.ClaveCurp, Contacto.FechaRegistro
              FROM Contacto, Usuario
              WHERE (Contacto.id = '".$PrEsc."') AND (Usuario.IdContact = '".$PrEsc."')";

    // si se realiza la consulta
    if ($hilos = $c0->query($aguja)) {
      if ($tela = $hilos->fetch_assoc()) {
        $costura = (string)$tela['Usuario'] . (string)$tela['Producto'] . (string)$tela['ClaveCurp'] . $subStr . (string)$tela['FechaRegistro'];
        /* Generar el hash */
        $firma = hash("adler32", $costura, false);
        return $firma;
      }
    }
    // Sin cambios: no retorna nada si falla, como el original
  }
}