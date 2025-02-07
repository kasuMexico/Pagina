<?php
    class Basicas{
/**************************** CODE AHH *************************
Esta funcion busca en las tablas datos relacionados
    $ah  => Dato principal 100%
    $sr => Dato a comparar
****************************************************************/
        public function ColorPor($ah,$sr){
          //Calculamos en donde esta
          $a1s = $sr/$ah;
          $as = $a1s*100;
          if($as >= 90){
              return "#04B404";
          }elseif($as <= 50){
              return "#B40404";
          }else{
              return "#FFBF00";
          }
        }
/**************************** CODE AHH *************************
Esta funcion busca en las tablas datos relacionados
    $c0  => Conexión de la BD
    $tab => Nombre de la tabla
    $col => Nombre de la columna a buscar
    $na  => Palabra o frace  a buscar
****************************************************************/
        public function BLikes($c0,$tab,$col,$na){
            $sql = "SELECT * FROM $tab WHERE $col LIKE '%".$na."%'";
            //realizar consulta
            return $c0->query($sql);
            //return $sql;
        }
/**************************** CODE AHH *************************
        Esta funcion busca en las tablas datos relacionados
            $c0  => Conexión de la BD
            $tab => Nombre de la tabla
            $col => Nombre de la columna a buscar
            $na  => Palabra o frace  a buscar
****************************************************************/
        public function BLikesCan($c0,$tab,$col,$na,$hy,$dj){
            $sql = "SELECT * FROM $tab WHERE $hy = '$dj' AND $col LIKE '%".$na."%' ";
            //realizar consulta
            return $c0->query($sql);
            //return $sql;
        }
/**************************** CODE AHH ***********************************************************
        Esta funcion busca en las tablas datos relacionados
            $c0  => Conexión de la BD
            $tab => Nombre de la tabla
            $col => Nombre de la columna a buscar
            $na  => Palabra o frace  a buscar
**************************************************************************************************/
        public function BLikesD2($c0,$tab,$col,$na,$hy,$dj,$hSy,$dSj){
            $sql = "SELECT * FROM $tab WHERE $hy = '$dj' AND $hSy = '$dSj' AND $col LIKE '%".$na."%' ";
            //realizar consulta
            return $c0->query($sql);
            //return $sql;
        }
        public function BLikes2($c0,$tab,$col,$na,$hy,$dj,$hSy,$dSj){
            $sql = "SELECT * FROM $tab WHERE $hy = '$dj' || $hSy = '$dSj' AND $col LIKE '%".$na."%' ";
            //realizar consulta
            return $c0->query($sql);
            //return $sql;
        }
        public function BLikes3($c0,$tab,$col,$na,$hy,$dj,$hSy,$dSj,$h1Sy,$d1Sj){
            $sql = "SELECT * FROM $tab WHERE $hy = '$dj' || $hSy = '$dSj' AND $h1Sy = '$d1Sj' AND $col LIKE '%".$na."%' ";
            //realizar consulta
            return $c0->query($sql);
            //return $sql;
        }
/*********************************************************************************
 Esta funcion recibe un array y realiza la insecion del mismo en la bases de datos y tablas dadas, recibe;
 1.- $c0 => Recibe la variable de la conexion a la base de datos
 2.- $n1 => Recibe la tabla donde se insertaran los datos
 3.- $d0 => Recibe un array con los datos a insertar y campos de la tabla en el sig formato;
       $variable = array (Nombre_del_Campo => $valor_del_campo);
*********************************************************************************/
        public function InsertCampo($c0,$n1,$d0){
        //separar los campos de las tablas de los datos a insertar
        $n2 = implode(", ",array_keys($d0));
        $n3 = implode("', '", array_values($d0));
        //crear consulta
            $sql = "INSERT INTO ".$n1." (".$n2.") VALUES ('".$n3."')";
        //realizar consulta
                if ($c0->query($sql) === true) {
        //almacena el Id retornado del insert realizado
                    return $c0->insert_id;
                } else {
        //Almacena el error de la consulta
                    return $c0->error;
                }
        }
/*********************************************************************************
Esta funcion realiza una busqueda en la base de datos y devuelve el valor
 1.- $c0 => Recibe la variable de la conexion a la base de datos
 2.- $d1 => Recibe el campo a buscar en la tabla
 2.- $n1 => Recibe la tabla donde se buscaran los datos
*********************************************************************************/
        public function MaxDat($c0,$d1,$n1){
        //Crear consulta
            $sql = "SELECT MAX($d1) FROM ".$n1;
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            $Reg=mysqli_fetch_assoc($res);
        //Si existe el campo a buscar
            return $Reg['MAX('.$d1.')'];
        }
/*********************************************************************************
Esta funcion realiza una busqueda en la base de datos y devuelve el valor
 1.- $c0 => Recibe la variable de la conexion a la base de datos
 2.- $d1 => Recibe el campo a buscar en la tabla
 2.- $n1 => Recibe la tabla donde se buscaran los datos
 3.- $d2 => Recibe el Nombre de la columna a validar
 4.- $d3 => Recibe la variable con la que se valida la consulta
*********************************************************************************/
        public function Max1Dat($c0,$d1,$n1,$d2,$n3){
        //Crear consulta
            $sql = "SELECT MAX($d1) FROM ".$n1." WHERE ".$d2." = '".$n3."'";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            $Reg=mysqli_fetch_assoc($res);
        //Si existe el campo a buscar
            return $Reg['MAX('.$d1.')'];
        }
/*********************************************************************************
      Esta funcion busca el maximo valor de una tabla donde es diferente un valor
/*********************************************************************************/
      public function Max1DifDat($c0,$d1,$n1,$d2,$n3){
      //Crear consulta
          $sql = "SELECT MAX($d1) FROM ".$n1." WHERE ".$d2." != '".$n3."'";
      //Realiza consulta
          $res = mysqli_query($c0, $sql);
      //Si existe el registro se asocia en un fetch_assoc
          $Reg=mysqli_fetch_assoc($res);
      //Si existe el campo a buscar
          return $Reg['MAX('.$d1.')'];
      }
/*********************************************************************************
Esta funcion realiza una busqueda en la base de datos y devuelve el valor
 1.- $c0 => Recibe la variable de la conexion a la base de datos
 2.- $d1 => Recibe el campo a buscar en la tabla
 2.- $n1 => Recibe la tabla donde se buscaran los datos
 3.- $d2 => Recibe el Nombre de la columna a validar
 4.- $d3 => Recibe la variable con la que se valida la consulta
*********************************************************************************/
        public function Max2Dat($c0,$d1,$n1,$d2,$n3,$d4,$n5){
        //Crear consulta
            $sql = "SELECT MAX($d1) FROM ".$n1." WHERE ".$d2." = '".$n3."' AND ".$d4." = '".$n5."'";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            $Reg=mysqli_fetch_assoc($res);
        //Si existe el campo a buscar
            return $Reg['MAX('.$d1.')'];
        }
/*********************************************************************************
Esta funcion realiza una busqueda en la base de datos y devuelve el valor
 1.- $c0 => Recibe la variable de la conexion a la base de datos
 2.- $d1 => Recibe el campo a buscar en la tabla
 2.- $n1 => Recibe la tabla donde se buscaran los datos
 3.- $d2 => Recibe el Nombre de la columna a validar
 4.- $d3 => Recibe la variable con la que se valida la consulta
*********************************************************************************/
        public function Min2Dat($c0,$d1,$n1,$d2,$n3,$d4,$n5){
        //Crear consulta
            $sql = "SELECT MIN($d1) FROM ".$n1." WHERE ".$d2." = '".$n3."' AND ".$d4." = '".$n5."'";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            $Reg=mysqli_fetch_assoc($res);
        //Si existe el campo a buscar
            return $Reg['MIN('.$d1.')'];
        }
/*********************************************************************************
        Esta funcion realiza la busqueda de un valor minimoen la base de datos y devuelve el valor
         1.- $c0 => Recibe la variable de la conexion a la base de datos
         2.- $d1 => Recibe el campo a buscar en la tabla
         2.- $n1 => Recibe la tabla donde se buscaran los datos
         3.- $d2 => Recibe el Nombre de la columna a validar
         4.- $n3 => Recibe la variable con la que se valida la consulta
*********************************************************************************/
        public function Min1Dat($c0,$d1,$n1,$d2,$n3){
            //Crear consulta
            $sql = "SELECT MIN($d1) FROM ".$n1." WHERE ".$d2." = '".$n3."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg=mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['MIN('.$d1.')'];
        }
/*********************************************************************************
                Busca el minimo de una tabla
*********************************************************************************/
        public function MinDat($c0,$d1,$n1){
        //Crear consulta
            $sql = "SELECT MIN($d1) FROM ".$n1;
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            $Reg=mysqli_fetch_assoc($res);
        //Si existe el campo a buscar
            return $Reg['MIN('.$d1.')'];
        }
/*********************************************************************************
Esta funcion realiza una busqueda en la base de datos y devuelve el valor
 1.- $c0 => Recibe la variable de la conexion a la base de datos
 2.- $d1 => Recibe el campo a buscar en la tabla
 2.- $n1 => Recibe la tabla donde se buscaran los datos
 3.- $d2 => Recibe el Nombre de la columna a validar
 4.- $d3 => Recibe la variable con la que se valida la consulta
*********************************************************************************/
        public function BuscarCampos($c0,$d1,$n1,$d2,$d3){
        //Crear consulta
            $sql = "SELECT ".$d1." FROM ".$n1." WHERE ".$d2." = '".$d3."'";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
        //Si existe el campo a buscar
                return $Reg[$d1];
            }
        }
/********************************************************************************/
        public function Buscar2Campos($c0,$d1,$n1,$d2,$d3,$d4,$d5){
        //Crear consulta
            $sql = "SELECT $d1 FROM $n1 WHERE $d2 = '$d3' AND $d4 = '$d5' ";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
        //Si existe el campo a buscar
                return $Reg[$d1];
            }
        }
/********************************************************************************/
        public function Buscar3Campos($c0,$d1,$n1,$d2,$d3,$d4,$d5,$d6,$d7){
        //Crear consulta
            $sql = "SELECT $d1 FROM $n1 WHERE $d2 = '$d3' AND $d4 = '$d5' AND $d6 = '$d7' ";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
        //Si existe el campo a buscar
                return $Reg[$d1];
            }
        }
/*********************************************************************************
Esta funcion retorna la edad de una clave curp;
 1.- $d4 => Recibe la clave CURP a obtener edad
*********************************************************************************/
        public function ObtenerEdad($d4){
        //Validar la curp por longitud de bits
            if(!(strlen($d4)===18)){
                return "CURP invalid";
            } else {
        //Extraer la fecha de nacimeinto de al clave CURP
                $Ano = mb_strcut($d4, 4, 2, "UTF-8");
                $Mes = mb_strcut($d4, 6, 2, "UTF-8");
                $Dia = mb_strcut($d4, 8, 2, "UTF-8");
        //Nacimiento / 2000 y el año actual y asignarle los dos digitos correspondientes
                if($Ano >= 00 and $Ano <= date("y")){
                    $AnoFin = "20" . $Ano;
                } else {
                    $AnoFin = "19" . $Ano;
                }
        //Calculando edad por año actual contra año de nacimiento
                $AnoEdad = date("Y") - $AnoFin;
        //Calculando edad por mes de nacimiento
                $MesEdad = date("m") - $Mes;
        //Calculandora de edad degu la fecha de nacimiento
                if($MesEdad < 0){
                    $Edad = $AnoEdad - 1;
                } elseif ($MesEdad == 0){
                $DiaEdad = date("j") - $Dia;
                    if ($DiaEdad <= 0){
                        $Edad = $AnoEdad - 1;
                    }else{
                        $Edad = $AnoEdad;
                    }
                } elseif ($MesEdad > 0){
                $Edad = $AnoEdad;
                }
                return $Edad;
            }
        }
/*********************************************************************************
Esta funcion retorna el precio del producto segun la edad;
 1.- $d16 => Recibe la edad del precio a validar
*********************************************************************************/
        public function ProdFune($d16){
        //recepcion de datos de edad
            if($d16 >= 02 && $d16 <=29) {
                $b = "02a29";
            } elseif($d16 >= 30 && $d16 <=49) {
                $b = "30a49";
            } elseif($d16 >= 50 && $d16 <=54) {
                $b = "50a54";
            } elseif($d16 >= 55 && $d16 <=59) {
                $b = "55a59";
            } elseif($d16 >= 60 && $d16 <=64) {
                $b = "60a64";
            } elseif($d16 >= 65 && $d16 <=69) {
                $b = "65a69";
            } else {
                $b = "<70";
            }
        //Retorna el producto por la edad del cliente
            return $b;
        }
/*********************************************************************************
Esta funcion retorna el precio del producto policiaco segun la edad;
 1.- $d16 => Recibe la edad del precio a validar
*********************************************************************************/
        public function ProdPli($d16){
        //recepcion de datos de edad
            if($d16 >= 02 && $d16 <=29) {
                $b = "P02a29";
            } elseif($d16 >= 30 && $d16 <=49) {
                $b = "P30a49";
            } elseif($d16 >= 50 && $d16 <=54) {
                $b = "P50a54";
            } elseif($d16 >= 55 && $d16 <=59) {
                $b = "P55a59";
            } elseif($d16 >= 60 && $d16 <=64) {
                $b = "P60a64";
            } elseif($d16 >= 65 && $d16 <=69) {
                $b = "P65a69";
            }
        //Retorna el producto por la edad del cliente
            return $b;
        }
/*********************************************************************************
        Esta funcion actualiza un campo de una tabla;
 1.- $c0  => Recibe la Conexion a la base de datos
 2.- $n1  => Recibe la tabla con en la cual se actualizara
 2.- $act => Recibe el valor actualizado que se insertara en la tabla
 3.- $IdD => Recibe el id donde se actualizara el valor
 4.- $Val => Recibe el campo del valor a actualizar
*********************************************************************************/
        public function ActCampo($c0,$n1,$Val,$act,$IdD){
        //Insertar a un numero al numero de usos de los codigos de descuento
            $SqlUp = "UPDATE ".$n1." SET ".$Val."='".$act."' WHERE Id='".$IdD."'";
            mysqli_query($c0, $SqlUp);
        }
/*********************************************************************************
                Esta funcion actualiza un campo de una tabla;
         1.- $c0  => Recibe la Conexion a la base de datos
         2.- $n1  => Recibe la tabla con en la cual se actualizara
         2.- $act => Recibe el valor actualizado que se insertara en la tabla
         3.- $IdD => Recibe el id donde se actualizara el valor
         4.- $Val => Recibe el campo del valor a actualizar
         5.- $Cam => REcibe la columan a validar
*********************************************************************************/
        public function ActTab($c0,$n1,$Val,$act,$Cam,$IdD){
          //Insertar a un numero al numero de usos de los codigos de descuento
              $SqlUp = "UPDATE ".$n1." SET ".$Val."='".$act."' WHERE ".$Cam."='".$IdD."'";
              mysqli_query($c0, $SqlUp);
          }
/*********************************************************************************/
        public function ActDosCampo($c0,$n1,$Val,$act,$Cam,$IdD,$Uno,$Dos){
          //Insertar a un numero al numero de usos de los codigos de descuento
              $SqlUp = "UPDATE ".$n1." SET ".$Val."='".$act."' WHERE ".$Cam."='".$IdD."' AND ".$Uno."='".$Dos."'";
              mysqli_query($c0, $SqlUp);
        }
/*********************************************************************************
Esta funcion actualiza los campos de una tabla;
 1.- $c0  => Recibe la Conexion a la base de datos
 2.- $n1  => Recibe la tabla con en la cual se actualizara
 2.- $act1 => Recibe el valor actualizado 1 que se insertara en la tabla
 3.- $act2 => Recibe el valor actualizado 2 que se insertara en la tabla
 4.- $IdD => Recibe el id donde se actualizara el valor
 5.- $Val1 => Recibe el campo del 1 valor a actualizar
 6.- $Val2 => Recibe el campo del 2 valor a actualizar
 7.- $cam1 => Recibe el campo 1 a comparar
 8.- $cam2 => Recibe el campo 2 a comparar
 9.- $cam3 => Recibe el campo 2 a comparar
 10.- $dat1 => Recibe el dato 1 con el cual se va a comparar
 11.- $dat1 => Recibe el dato 2 con el cual se va a comparar
 12.- $dat1 => Recibe el dato 3 con el cual se va a comparar
*********************************************************************************/
        public function ActCampoCon($c0,$n1,$act1,$act2,$IdD,$Val1,$Val2,$cam1,$cam2,$cam3,$dat1,$dat2,$dat3){
            $SqlUp = "UPDATE ".$n1." SET ".$Val1."='".$act1."',".$Val2."='".$act2."' WHERE Id='".$IdD."' AND ".$cam1."='".$dat1."' AND ".$cam2."='".$dat2."' AND ".$cam3."='".$dat3."'";
            //actualiza el los campos
            mysqli_query($c0, $SqlUp);
					return $SqlUp;
        }
/***************************************************************************
        Esta funcion valida un usuario y una contraseña;
 1.- $c0   => Recibe la Conexion a la base de datos
 2.- $usr  => Recibe el usuario
 2.- $pass => Recibe la contraseña
                    Revisar esta formula
*********************************************************************************/
        public function ValidarUsr($c0,$usr,$pass){
        //Convertir en hash y validarlo
            $TCrip = hash("sha256", $usr);
            $FCrip = hash("sha256", $pass);
        //Crear consulta
            $sql = "SELECT IdUsuarioModificador FROM RegistroUsuarios WHERE Usuario  = '$TCrip' AND Contrasena  = '$FCrip'";
        //Realiza consulta
            $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
            if($Reg=mysqli_fetch_assoc($res)){
        //Si existe el campo a buscar
                $Id = $Reg['IdUsuarioModificador'];
                return $Id;
            } else {
                return NULL;
            }
        }
/*********************************************************************************
        cuenta los valores en una tabla dada DOS condiciones
c0 => Conexion a la base de datos
d1 => Tabla a consultar
d2 => Valor de la columna
d3 => valor a consultar
d4 => Valor de la columna
d5 => valor a consultar
*********************************************************************************/
        public function ConUnCon($c0,$d1,$d2,$d3,$d4,$d5){
    //Busca en la tabla de fingerprint una condicion que determina el total de lectores delimitado por una condicion ·X·
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '$d3' AND $d4 = '$d5'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
                return $Reg['COUNT(*)'];
        }
/*********************************************************************************
        cuenta los valores en una tabla dada DOS condiciones
c0 => Conexion a la base de datos
d1 => Tabla a consultar
d2 => Valor de la columna
d3 => valor a consultar
d4 => Valor de la columna
d5 => valor a consultar
*********************************************************************************/
    public function ConDosCon($c0,$d1,$d2,$d3,$d4,$d5,$d6,$d7){
    //Busca en la tabla de fingerprint una condicion que determina el total de lectores delimitado por una condicion ·X·
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '$d3' AND $d4 = '$d5' AND $d6 != '$d7'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
                return $Reg['COUNT(*)'];
        }
/*********************************************************************************
        cuenta los valores en una tabla dada Una condicion
c0 => Conexion a la base de datos
d1 => Tabla a consultar
d2 => Valor de la columna
d3 => valor a consultar
*********************************************************************************/
        public function ConUno($c0,$d1,$d2,$d3){
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '".$d3."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
                return $Reg['COUNT(*)'];
        }
/*********************************************************************************
          Cuenta los valores dentro de un rango de fechas exceptuando un ultimo valor
********************************************************************************/
        public function CuentaFechas($c0,$d1,$d2,$d3,$d4,$d5,$d6,$d7,$d8,$d9){
    //Busca en la tabla de fingerprint una condicion que determina el total de lectores delimitado por una condicion ·X·
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '".$d3."' AND $d8 != '".$d9."' AND $d4 <= '".$d5."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
                return $Reg['COUNT(*)'];
        }
/*************************************  Cuenta con una fecha  *************************************************/
        public function Cuenta1Fec1Cond($c0,$d1,$d2,$d3,$d4,$d5,$d6,$d7){
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '".$d3."' AND $d4 = '".$d5."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['COUNT(*)'];
        }
/*************************************  Cuenta con una fecha  *************************************************/
        public function Cuenta1Fec($c0,$d1,$d2,$d3,$d6,$d7){
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '".$d3."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['COUNT(*)'];
        }
/*************************************  Cuenta con una fecha  *************************************************/
        public function Cuenta0Fec($c0,$d1,$d6,$d7){
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['COUNT(*)'];
        }
/********************************************************************************
                Cuenta los valores dentro de un rango de fechas
********************************************************************************/
        public function CuentaFechasLim($c0,$d1,$d2,$d3,$d4,$d5,$d6,$d7){
    //Busca en la tabla de fingerprint una condicion que determina el total de lectores delimitado por una condicion ·X·
            //Crear consulta
            $sql = "SELECT COUNT(*) FROM $d1 WHERE $d2 = '".$d3."' AND $d4 <= '".$d5."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
                return $Reg['COUNT(*)'];
        }
/*********************************************************************************
        Cuenta los valores dentro de un rango de fechas exceptuando un ultimo valor
********************************************************************************/
      public function ContarFechas4($c0,$d1,$d2,$d3,$d4,$d5,$d6,$d7,$d8,$d9,$d10,$d11){
  //Busca en la tabla de fingerprint una condicion que determina el total de lectores delimitado por una condicion ·X·
          //Crear consulta
          $sql = "SELECT COUNT(*)  FROM $d1 WHERE $d2 = '".$d3."' AND $d8 != '".$d9."' AND $d10 = '".$d11."' AND $d4 <= '".$d5."' AND $d6 >= '".$d7."'";
          //Realiza consulta
          $res = mysqli_query($c0, $sql);
          //Si existe el registro se asocia en un fetch_assoc
          $Reg = mysqli_fetch_assoc($res);
          //Si existe el campo a buscar
          return $Reg['COUNT(*)'];
      }
/********************************************************************************
                suma los valores en una tabla dada DOS condiciones
        c0 => Conexion a la base de datos
        $c1=> Columna a sumar
        d1 => Tabla a consultar
        d2 => Nombre de la columna
        d3 => valor a consultar
        d4 => Nombre de la columna
        d5 => valor a consultar
*********************************************************************************/
        public function Sumar2cond($c0,$c1,$d1,$d2,$d3,$d4,$d5){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '$d3' AND $d4 = '$d5'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
/********************************************************************************/
        public function Sumar1cond($c0,$c1,$d1,$d2,$d3){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '$d3'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
/********************************************************************************/
        public function Sumar0Fecha($c0,$c1,$d1,$d2,$d3){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 >=  '$d3'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
/********************************************************************************/
        public function Sumar($c0,$c1,$d1){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 ";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
/*********************************************************************************
        Suma los valores dentro de un rango de Fechas
            c0 => Conexion a la base de datos
            $c1=> Columna a sumar
            $d1 => Tabla a consultar
            $d2 => Nombre de la columna
            $d3 => valor a consultar
            $d4 => Nombre de la columna
            $d5 => valor a consultar
*********************************************************************************/
        public function SumarFechas($c0,$c1,$d1,$d2,$d3,$d4,$d5,$d6,$d7){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '".$d3."' AND $d4 <= '".$d5."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
/*********************************************************************************
        Suma los valores dentro de un rango de Fechas
            c0 => Conexion a la base de datos
            $c1=> Columna a sumar
            $d1 => Tabla a consultar
            $d2 => Nombre de la columna
            $d3 => valor a consultar
            $d4 => Nombre de la columna
            $d5 => valor a consultar
*********************************************************************************/
        public function Sumar1Fechas($c0,$c1,$d1,$d2,$d3,$d6,$d7){
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '".$d3."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }
/*********************************************************************************/
          public function Sumar1Fec1Cond($c0,$c1,$d1,$d2,$d3,$d6,$d7,$d8,$d9){
              //Crear consulta
              $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '".$d3."' AND $d6 >= '".$d7."' AND $d8 = '".$d9."'";
              //Realiza consulta
              $res = mysqli_query($c0, $sql);
              //Si existe el registro se asocia en un fetch_assoc
              $Reg = mysqli_fetch_assoc($res);
              //Si existe el campo a buscar
              return $Reg['SUM('.$c1.')'];
          }
/*********************************************************************************
          Cuenta los valores dentro de un rango de fechas exceptuando un ultimo valor
********************************************************************************/
    public function SumarFechasIndis($c0,$c1,$d1,$d2,$d3,$d4,$d5,$d6,$d7,$d8,$d9){
    //Busca en la tabla de fingerprint una condicion que determina el total de lectores delimitado por una condicion ·X·
            //Crear consulta
            $sql = "SELECT SUM($c1) FROM $d1 WHERE $d2 = '".$d3."' AND $d8 != '".$d9."' AND $d4 <= '".$d5."' AND $d6 >= '".$d7."'";
            //Realiza consulta
            $res = mysqli_query($c0, $sql);
            //Si existe el registro se asocia en un fetch_assoc
            $Reg = mysqli_fetch_assoc($res);
            //Si existe el campo a buscar
            return $Reg['SUM('.$c1.')'];
        }

/*********************************************************************************
      Suma los valores dentro de un rango de Fechas
            c0 => Conexion a la base de datos
            $c1=> Columna a buscar
            $d1 => Tabla a consultar
            $d2 => Nombre de la columna
            $d3 => valor a consultar
            $d4 => Nombre de la columna
            $d5 => valor a consultar
*********************************************************************************/
  public function Buscar1Fechas($c0,$c1,$d1,$d2,$d3,$d6,$d7){
        //Crear consulta
        $sql = "SELECT ".$c1." FROM $d1 WHERE $d2 = '".$d3."' AND $d6 >= '".$d7."'";
        //Realiza consulta
        $res = mysqli_query($c0, $sql);
        //Si existe el registro se asocia en un fetch_assoc
        if($Reg=mysqli_fetch_assoc($res)){
        //Si existe el campo a buscar
            return $Reg[$c1];
        }
    }
}
