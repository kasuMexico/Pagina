<?php
/**************************************************************************************************************************
 * Archivo: Basicas.php
 * Qué hace: Provee funciones utilitarias de consulta, inserción y reglas de negocio para KASU.
 * Compatibilidad: PHP 8.2
 * Fecha: 04/11/2025
 * Revisado por: JCCM
 **************************************************************************************************************************/
//Funicones de Trackeo
require_once __DIR__ . '/FunctionUsageTracker.php';

// Instancia global para funciones básicas
$basicas = $basicas ?? new Basicas();

class Basicas{

  // Telemetría de uso
  use UsageTrackerTrait;

  /**************************************************************************************************************************
    Esta funcion valida un usuario y una contraseña  para el modod sandbox;
    1.- $c0   => Recibe la Conexion a la base de datos
    2.- $usr  => Recibe el usuario
    2.- $pass => Recibe la contraseña
  ****************************************************************************************************************************/
  public function ValidarUsrAPI_sandbox($c0,$usr){
    $usrA  = "Api_KASU_Sandbox";
    if ($usrA === $usr) {
      return true;
    }
  }

  /**************************************************************************************************************************
    Esta funcion realiza una busqueda en la base de datos y devuelve el valor
     1.- $c0 => Recibe la variable de la conexion a la base de datos
     2.- $d1 => Recibe el campo a buscar en la tabla
     2.- $n1 => Recibe la tabla donde se buscaran los datos
     3.- $d2 => Recibe el Nombre de la columna a validar
     4.- $d3 => Recibe la variable con la que se valida la consulta
  ****************************************************************************************************************************/
  public function BuscarCampos($c0,$d1,$n1,$d2,$d3){
    // Crear consulta
    $sql = "SELECT ".$d1." FROM ".$n1." WHERE ".$d2." = '".$this->esc($c0, $d3)."'";
    // Realiza consulta
    $res = mysqli_query($c0, $sql);
    // Si existe el registro se asocia en un fetch_assoc
    if ($Reg = mysqli_fetch_assoc($res)) {
      // Si existe el campo a buscar
      return $Reg[$d1];
    }
  }

  /********************************************************************************/
  public function Buscar2Campos($c0,$d1,$n1,$d2,$d3,$d4,$d5){
    // Crear consulta
    $sql = "SELECT $d1 FROM $n1 WHERE $d2 = '".$this->esc($c0,$d3)."' AND $d4 = '".$this->esc($c0,$d5)."' ";
    // Realiza consulta
    $res = mysqli_query($c0, $sql);
    // Si existe el registro se asocia en un fetch_assoc
    if ($Reg = mysqli_fetch_assoc($res)) {
      // Si existe el campo a buscar
      return $Reg[$d1];
    }
  }

  /********************************************************************************/
  public function Buscar3Campos($c0,$d1,$n1,$d2,$d3,$d4,$d5,$d6,$d7){
    // Crear consulta
    $sql = "SELECT $d1 FROM $n1 WHERE $d2 = '".$this->esc($c0,$d3)."' AND $d4 = '".$this->esc($c0,$d5)."' AND $d6 = '".$this->esc($c0,$d7)."'";
    // Realiza consulta
    $res = mysqli_query($c0, $sql);
    // Si existe el registro se asocia en un fetch_assoc
    if ($Reg = mysqli_fetch_assoc($res)) {
      // Si existe el campo a buscar
      return $Reg[$d1];
    }
  }

  /**************************************************************************************************************************
    Esta funcion retorna la edad de una clave curp;
    1.- $d4 => Recibe la clave CURP a obtener edad
  ****************************************************************************************************************************/
  public function ObtenerEdad($d4){
    // Validar la curp por longitud de bits
    if (!(strlen($d4)===18)) {
      return "CURP invalid";
    } else {
      // Extraer la fecha de nacimiento de la clave CURP
      $Ano = mb_strcut($d4, 4, 2, "UTF-8");
      $Mes = mb_strcut($d4, 6, 2, "UTF-8");
      $Dia = mb_strcut($d4, 8, 2, "UTF-8");

      // Nacimiento / 2000 y el año actual y asignarle los dos digitos correspondientes
      if ($Ano >= 00 and $Ano <= date("y")) {
        $AnoFin = "20" . $Ano;
      } else {
        $AnoFin = "19" . $Ano;
      }

      // Calculando edad por año actual contra año de nacimiento
      $AnoEdad = date("Y") - $AnoFin;
      // Calculando edad por mes de nacimiento
      $MesEdad = date("m") - $Mes;

      // Calculadora de edad según la fecha de nacimiento
      if ($MesEdad < 0) {
        $Edad = $AnoEdad - 1;
      } elseif ($MesEdad == 0) {
        $DiaEdad = date("j") - $Dia;
        if ($DiaEdad <= 0) {
          $Edad = $AnoEdad - 1;
        } else {
          $Edad = $AnoEdad;
        }
      } elseif ($MesEdad > 0) {
        $Edad = $AnoEdad;
      }
      return $Edad;
    }
  }

  /**************************************************************************************************************************
    Esta funcion retorna el precio del producto segun la edad;
    1.- $c0 => Recibe la Conexion a la base de datos
    2.- $d10 => Recibe la forma de pago (credito = 1 , Contado = 2)
    1.- $d16 => Recibe la edad del precio a validar
  ****************************************************************************************************************************/
  public function ProdFune($d16){
    // recepcion de datos de edad
    if ($d16 >= 02 && $d16 <=29) {
      $b = "02a29";
    } elseif ($d16 >= 30 && $d16 <=49) {
      $b = "30a49";
    } elseif ($d16 >= 50 && $d16 <=54) {
      $b = "50a54";
    } elseif ($d16 >= 55 && $d16 <=59) {
      $b = "55a59";
    } elseif ($d16 >= 60 && $d16 <=64) {
      $b = "60a64";
    } elseif ($d16 >= 65 && $d16 <=69) {
      $b = "65a69";
    } else {
      return false;
    }
    // Retorna el producto por la edad del cliente
    return $b;
  }

  /**************************************************************************************************************************
    Esta funcion recibe un array y realiza la insecion del mismo en la bases de datos y tablas dadas, recibe;
    1.- $c0 => Recibe la variable de la conexion a la base de datos
    2.- $n1 => Recibe la tabla donde se insertaran los datos
    3.- $d0 => Recibe un array con los datos a insertar y campos de la tabla en el sig formato;
    $variable = array (Nombre_del_Campo => $valor_del_campo);
  ****************************************************************************************************************************/
  public function InsertCampo($c0,$n1,$d0){
    // separar los campos de las tablas de los datos a insertar
    $campos = array_keys($d0);
    $valores = array_values($d0);

    // Escapar valores para prevenir errores en 8.2 sin cambiar lógica de retorno
    $escapados = array_map(function($v) use ($c0){
      return mysqli_real_escape_string($c0, (string)$v);
    }, $valores);

    $n2 = implode(", ", $campos);
    $n3 = implode("', '", $escapados);

    // crear consulta
    $sql = "INSERT INTO ".$n1." (".$n2.") VALUES ('".$n3."')";
    // realizar consulta
    if ($c0->query($sql) === true) {
      // almacena el Id retornado del insert realizado
      return $c0->insert_id;
    } else {
      // Almacena el error de la consulta
      return $c0->error;
    }
  }

  /**************************************************************************************************************************
    Esta funcion recibe un array y realiza la insecion del mismo en la bases de datos y tablas dadas, recibe;
    1.- $curp_en_uso => Recibe la variable de la conexion a la base de datos
    2.- $producto => Recibe la tabla donde se insertaran los datos
  ****************************************************************************************************************************/
  public function VerificarProducto($curp_en_uso,$producto){
    // Obtenemos la edad de el cliente
    $EdadCte = $this->ObtenerEdad($curp_en_uso);

    // si el producto es Funerario obtenemos el bloque del producto
    if ($producto == "Funerario") {
      // Validamos si el cliente esta fuera de la edad
      if ($this->ProdFune($EdadCte)) {
        return true;
      } else {
        return false;
      }
    } elseif ($producto == "Universidad") {
      // Validamos si el cliente esta fuera de la edad
      if ($EdadCte > 9) {
        // Si el cliente tiene mas de la edad aceptable del producto
        return false;
      }
    } elseif ($producto == "Retiro") {
      // Validamos si el cliente esta fuera de la edad
      if ($EdadCte > 65) {
        // Si el cliente tiene mas de la edad aceptable del producto
        return false;
      }
    } else {
      // Si el producto no existe retorna este valor
      return false;
    }
    return true;
  }

  /*********************************************************************************
    Esta funcion realiza una busqueda en la base de datos y devuelve el valor
    1.- $c0 => Recibe la variable de la conexion a la base de datos
    2.- $d1 => Recibe el campo a buscar en la tabla
    3.- $n1 => Recibe la tabla donde se buscaran los datos
  *********************************************************************************/
  public function MaxDat($c0,$d1,$n1){
    // Crear consulta
    $sql = "SELECT MAX($d1) FROM ".$n1;
    // Realiza consulta
    $res = mysqli_query($c0, $sql);
    // Si existe el registro se asocia en un fetch_assoc
    $Reg = mysqli_fetch_assoc($res);
    // Si existe el campo a buscar
    return $Reg['MAX('.$d1.')'];
  }

  /*********************************************************************************
    cuenta los valores en una tabla dada Una condicion
    c0 => Conexion a la base de datos
    d1 => Tabla a consultar
    d2 => Valor de la columna
    d3 => valor a consultar
  *********************************************************************************/
  public function ConUno($c0,$d1,$d2,$d3){
    // Crear consulta
    $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '".$this->esc($c0,$d3)."'";
    // Realiza consulta
    $res = mysqli_query($c0, $sql);
    // Si existe el registro se asocia en un fetch_assoc
    $Reg = mysqli_fetch_assoc($res);
    // Si existe el campo a buscar
    return $Reg['COUNT(*)'];
  }

  /*********************************************************************************
    Helper interno: escape seguro para strings en consultas simples.
    Mantiene comportamiento de concatenación existente sin cambiar firmas.
  *********************************************************************************/
  private function esc($c0, $v){
    return mysqli_real_escape_string($c0, (string)$v);
  }
}