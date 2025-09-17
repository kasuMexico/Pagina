<?php
//Contador de veces que se usa cada funcion para seguimiento *JCCM
require_once 'FunctionUsageTracker.php';

class Basicas {
    // Usa el trait para poder registrar el uso de los métodos.
    use UsageTrackerTrait;

    /*********************************************************************************
     * Retorna un color basado en el porcentaje que representa $sr respecto a $ah.
     * Si el porcentaje es mayor o igual a 90% retorna verde, si es menor o igual a 50%
     * retorna rojo, de lo contrario amarillo.
     *********************************************************************************/
    public function ColorPor($ah, $sr) {
        $this->trackUsage();  // Registra el uso de este método.
        if ($ah == 0) {
            // Evita división entre cero; se retorna rojo como valor por defecto.
            return "#B40404";
        }
        $percentage = ($sr / $ah) * 100;
        if ($percentage >= 90) {
            return "#04B404";
        } elseif ($percentage <= 50) {
            return "#B40404";
        } else {
            return "#FFBF00";
        }
    }

    /*********************************************************************************
     * Realiza una búsqueda usando LIKE en la columna $col de la tabla $tab.
     * $na es el término a buscar.
     *********************************************************************************/
    public function BLikes($c0, $tab, $col, $na) {
        $this->trackUsage();  // Registra el uso de este método.
        $na = $c0->real_escape_string($na);
        $sql = "SELECT * FROM `$tab` WHERE `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda en la tabla $tab con la condición de que la columna $hy 
     * sea igual a $dj y además que la columna $col contenga el término $na.
     *********************************************************************************/
    public function BLikesCan($c0, $tab, $col, $na, $hy, $dj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na = $c0->real_escape_string($na);
        $dj = $c0->real_escape_string($dj);
        $sql = "SELECT * FROM `$tab` WHERE `$hy` = '$dj' AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda en la tabla $tab con dos condiciones fijas y una búsqueda 
     * en $col usando LIKE.
     *********************************************************************************/
    public function BLikesD2($c0, $tab, $col, $na, $hy, $dj, $hSy, $dSj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na   = $c0->real_escape_string($na);
        $dj   = $c0->real_escape_string($dj);
        $dSj  = $c0->real_escape_string($dSj);
        $sql  = "SELECT * FROM `$tab` WHERE `$hy` = '$dj' AND `$hSy` = '$dSj' AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda con condiciones OR y una cláusula LIKE.
     *********************************************************************************/
    public function BLikes2($c0, $tab, $col, $na, $hy, $dj, $hSy, $dSj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na   = $c0->real_escape_string($na);
        $dj   = $c0->real_escape_string($dj);
        $dSj  = $c0->real_escape_string($dSj);
        $sql  = "SELECT * FROM `$tab` WHERE (`$hy` = '$dj' OR `$hSy` = '$dSj') AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda con múltiples condiciones (combinación de OR y AND) y LIKE.
     *********************************************************************************/
    public function BLikes3($c0, $tab, $col, $na, $hy, $dj, $hSy, $dSj, $h1Sy, $d1Sj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na    = $c0->real_escape_string($na);
        $dj    = $c0->real_escape_string($dj);
        $dSj   = $c0->real_escape_string($dSj);
        $d1Sj  = $c0->real_escape_string($d1Sj);
        $sql   = "SELECT * FROM `$tab` WHERE (`$hy` = '$dj' OR `$hSy` = '$dSj') AND `$h1Sy` = '$d1Sj' AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Inserta datos en la tabla $n1 utilizando un array asociativo $d0 (campo => valor).
     *********************************************************************************/
    public function InsertCampo($c0, $n1, $d0) {
        $this->trackUsage();  // Registra el uso de este método.
        $campos = array_keys($d0);
        $valores = array_map(function($valor) use ($c0) {
            return $c0->real_escape_string($valor);
        }, array_values($d0));
        $campos_str = implode(", ", $campos);
        $valores_str = implode("', '", $valores);
        $sql = "INSERT INTO `$n1` ($campos_str) VALUES ('$valores_str')";
        if ($c0->query($sql) === true) {
            return $c0->insert_id;
        } else {
            return $c0->error;
        }
    }

    /*********************************************************************************
     * Retorna el valor máximo de la columna $d1 de la tabla $n1.
     *********************************************************************************/
    public function MaxDat($c0, $d1, $n1) {
        $this->trackUsage();  // Registra el uso de este método.
        $sql = "SELECT MAX(`$d1`) AS max_value FROM `$n1`";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['max_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor máximo de $d1 de la tabla $n1 donde $d2 = $n3.
     *********************************************************************************/
    public function Max1Dat($c0, $d1, $n1, $d2, $n3) {
        $this->trackUsage();  // Registra el uso de este método.
        $n3 = $c0->real_escape_string($n3);
        $sql = "SELECT MAX(`$d1`) AS max_value FROM `$n1` WHERE `$d2` = '$n3'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['max_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor máximo de $d1 de la tabla $n1 donde $d2 != $n3.
     *********************************************************************************/
    public function Max1DifDat($c0, $d1, $n1, $d2, $n3) {
        $this->trackUsage();  // Registra el uso de este método.
        $n3 = $c0->real_escape_string($n3);
        $sql = "SELECT MAX(`$d1`) AS max_value FROM `$n1` WHERE `$d2` != '$n3'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['max_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor máximo de $d1 de la tabla $n1 donde $d2 = $n3 y $d4 = $n5.
     *********************************************************************************/
    public function Max2Dat($c0, $d1, $n1, $d2, $n3, $d4, $n5) {
        $this->trackUsage();  // Registra el uso de este método.
        $n3 = $c0->real_escape_string($n3);
        $n5 = $c0->real_escape_string($n5);
        $sql = "SELECT MAX(`$d1`) AS max_value FROM `$n1` WHERE `$d2` = '$n3' AND `$d4` = '$n5'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['max_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor mínimo de $d1 de la tabla $n1 donde $d2 = $n3 y $d4 = $n5.
     *********************************************************************************/
    public function Min2Dat($c0, $d1, $n1, $d2, $n3, $d4, $n5) {
        $this->trackUsage();  // Registra el uso de este método.
        $n3 = $c0->real_escape_string($n3);
        $n5 = $c0->real_escape_string($n5);
        $sql = "SELECT MIN(`$d1`) AS min_value FROM `$n1` WHERE `$d2` = '$n3' AND `$d4` = '$n5'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['min_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor mínimo de $d1 de la tabla $n1 donde $d2 = $n3.
     *********************************************************************************/
    public function Min1Dat($c0, $d1, $n1, $d2, $n3) {
        $this->trackUsage();  // Registra el uso de este método.
        $n3 = $c0->real_escape_string($n3);
        $sql = "SELECT MIN(`$d1`) AS min_value FROM `$n1` WHERE `$d2` = '$n3'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['min_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor mínimo de $d1 de la tabla $n1.
     *********************************************************************************/
    public function MinDat($c0, $d1, $n1) {
        $this->trackUsage();  // Registra el uso de este método.
        $sql = "SELECT MIN(`$d1`) AS min_value FROM `$n1`";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['min_value'];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor del campo $d1 de la tabla $n1 donde $d2 = $d3.
     *********************************************************************************/
    public function BuscarCampos($c0, $d1, $n1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $sql = "SELECT `$d1` FROM `$n1` WHERE `$d2` = '$d3'";
        $res = $c0->query($sql);
        if ($res && $Reg = $res->fetch_assoc()) {
            return $Reg[$d1];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor del campo $d1 de la tabla $n1 donde $d2 = $d3 y $d4 = $d5.
     *********************************************************************************/
    public function Buscar2Campos($c0, $d1, $n1, $d2, $d3, $d4, $d5) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d5 = $c0->real_escape_string($d5);
        $sql = "SELECT `$d1` FROM `$n1` WHERE `$d2` = '$d3' AND `$d4` = '$d5'";
        $res = $c0->query($sql);
        if ($res && $Reg = $res->fetch_assoc()) {
            return $Reg[$d1];
        }
        return null;
    }

    /*********************************************************************************
     * Retorna el valor del campo $d1 de la tabla $n1 donde $d2 = $d3, $d4 = $d5 y $d6 = $d7.
     *********************************************************************************/
    public function Buscar3Campos($c0, $d1, $n1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d5 = $c0->real_escape_string($d5);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT `$d1` FROM `$n1` WHERE `$d2` = '$d3' AND `$d4` = '$d5' AND `$d6` = '$d7'";
        $res = $c0->query($sql);
        if ($res && $Reg = $res->fetch_assoc()) {
            return $Reg[$d1];
        }
        return null;
    }

    /*********************************************************************************
     * Calcula la edad a partir de una CURP.
     * Se extraen los dígitos correspondientes a la fecha de nacimiento y se calcula la diferencia.
     *********************************************************************************/
    public function ObtenerEdad($curp) {
        $this->trackUsage();  // Registra el uso de este método.
        if (strlen($curp) !== 18) {
            return "CURP invalid";
        }
        $yearPart  = substr($curp, 4, 2);
        $monthPart = substr($curp, 6, 2);
        $dayPart   = substr($curp, 8, 2);
        $currentYearTwoDigits = date("y");
        if ((int)$yearPart <= (int)$currentYearTwoDigits) {
            $birthYear = 2000 + (int)$yearPart;
        } else {
            $birthYear = 1900 + (int)$yearPart;
        }
        $birthDate = DateTime::createFromFormat('Y-m-d', sprintf("%04d-%02d-%02d", $birthYear, (int)$monthPart, (int)$dayPart));
        if (!$birthDate) {
            return "CURP invalid";
        }
        $now = new DateTime();
        $age = $now->diff($birthDate)->y;
        return $age;
    }

    /*********************************************************************************
     * Retorna el código de producto para Fune basado en el rango de edad.
     *********************************************************************************/
    public function ProdFune($d16) {
        $this->trackUsage();  // Registra el uso de este método.
        if ($d16 >= 2 && $d16 <= 29) {
            return "02a29";
        } elseif ($d16 >= 30 && $d16 <= 49) {
            return "30a49";
        } elseif ($d16 >= 50 && $d16 <= 54) {
            return "50a54";
        } elseif ($d16 >= 55 && $d16 <= 59) {
            return "55a59";
        } elseif ($d16 >= 60 && $d16 <= 64) {
            return "60a64";
        } elseif ($d16 >= 65 && $d16 <= 69) {
            return "65a69";
        } else {
            return "<70";
        }
    }

    /*********************************************************************************
     * Retorna el código de producto para Policiaco basado en el rango de edad.
     *********************************************************************************/
    public function ProdPli($d16) {
        $this->trackUsage();  // Registra el uso de este método.
        if ($d16 >= 2 && $d16 <= 29) {
            return "P02a29";
        } elseif ($d16 >= 30 && $d16 <= 49) {
            return "P30a49";
        } elseif ($d16 >= 50 && $d16 <= 54) {
            return "P50a54";
        } elseif ($d16 >= 55 && $d16 <= 59) {
            return "P55a59";
        } elseif ($d16 >= 60 && $d16 <= 64) {
            return "P60a64";
        } elseif ($d16 >= 65 && $d16 <= 69) {
            return "P65a69";
        }
        return null;
    }

    /*********************************************************************************
     * Actualiza el campo $Val de la tabla $n1 con el valor $act para el registro identificado 
     * por Id = $IdD.
     *********************************************************************************/
    public function ActCampo($c0, $n1, $Val, $act, $IdD) {
        $this->trackUsage();  // Registra el uso de este método.
        $act = $c0->real_escape_string($act);
        $IdD = $c0->real_escape_string($IdD);
        $sql = "UPDATE `$n1` SET `$Val` = '$act' WHERE `Id` = '$IdD'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Actualiza el campo $Val de la tabla $n1 con el valor $act donde la columna $Cam 
     * coincide con $IdD.
     *********************************************************************************/
    public function ActTab($c0, $n1, $Val, $act, $Cam, $IdD) {
        $this->trackUsage();  // Registra el uso de este método.
        $act = $c0->real_escape_string($act);
        $IdD = $c0->real_escape_string($IdD);
        $sql = "UPDATE `$n1` SET `$Val` = '$act' WHERE `$Cam` = '$IdD'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Actualiza el campo $Val de la tabla $n1 con el valor $act donde se cumple la condición 
     * en $Cam = $IdD y $Uno = $Dos.
     *********************************************************************************/
    public function ActDosCampo($c0, $n1, $Val, $act, $Cam, $IdD, $Uno, $Dos) {
        $this->trackUsage();  // Registra el uso de este método.
        $act = $c0->real_escape_string($act);
        $IdD = $c0->real_escape_string($IdD);
        $Dos = $c0->real_escape_string($Dos);
        $sql = "UPDATE `$n1` SET `$Val` = '$act' WHERE `$Cam` = '$IdD' AND `$Uno` = '$Dos'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Actualiza dos campos ($Val1 y $Val2) en la tabla $n1 para el registro con Id = $IdD 
     * y donde se cumplan las condiciones en $cam1 = $dat1, $cam2 = $dat2 y $cam3 = $dat3.
     *********************************************************************************/
    public function ActCampoCon($c0, $n1, $act1, $act2, $IdD, $Val1, $Val2, $cam1, $cam2, $cam3, $dat1, $dat2, $dat3) {
        $this->trackUsage();  // Registra el uso de este método.
        $act1 = $c0->real_escape_string($act1);
        $act2 = $c0->real_escape_string($act2);
        $IdD  = $c0->real_escape_string($IdD);
        $dat1 = $c0->real_escape_string($dat1);
        $dat2 = $c0->real_escape_string($dat2);
        $dat3 = $c0->real_escape_string($dat3);
        $sql = "UPDATE `$n1` SET `$Val1` = '$act1', `$Val2` = '$act2' WHERE `Id` = '$IdD' AND `$cam1` = '$dat1' AND `$cam2` = '$dat2' AND `$cam3` = '$dat3'";
        $c0->query($sql);
        return $sql;
    }

    /*********************************************************************************
     * Valida el usuario y la contraseña comparando los hashes SHA256.
     *********************************************************************************/
    public function ValidarUsr($c0, $usr, $pass) {
        $this->trackUsage();  // Registra el uso de este método.
        $TCrip = hash("sha256", $usr);
        $FCrip = hash("sha256", $pass);
        $sql = "SELECT `IdUsuarioModificador` FROM `RegistroUsuarios` WHERE `Usuario` = '$TCrip' AND `Contrasena` = '$FCrip'";
        $res = $c0->query($sql);
        if ($res && $Reg = $res->fetch_assoc()) {
            return $Reg['IdUsuarioModificador'];
        }
        return null;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen las dos condiciones:
     * $d2 = $d3 y $d4 = $d5.
     *********************************************************************************/
    public function ConUnCon($c0, $d1, $d2, $d3, $d4, $d5) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d5 = $c0->real_escape_string($d5);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` = '$d5'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d4 = $d5 y $d6 != $d7.
     *********************************************************************************/
    public function ConDosCon($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d5 = $c0->real_escape_string($d5);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` = '$d5' AND `$d6` != '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 donde $d2 = $d3.
     *********************************************************************************/
    public function ConUno($c0, $d1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d8 != $d9, y dentro del rango de fechas definido por $d4 <= $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function CuentaFechas($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7, $d8, $d9) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d9 = $c0->real_escape_string($d9);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d8` != '$d9' AND `$d4` <= '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d4 = $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function Cuenta1Fec1Cond($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` = '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3 y $d6 >= $d7.
     *********************************************************************************/
    public function Cuenta1Fec($c0, $d1, $d2, $d3, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d6 >= $d7.
     *********************************************************************************/
    public function Cuenta0Fec($c0, $d1, $d6, $d7) {
        //echo "<br>imprime lo que trae el la variable mysqli -> dentro de la Funcion Cuenta0Fec<br>";
        //var_dump($c0);
        $this->trackUsage();  // Registra el uso de este método.
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen: $d6 >= $d7.
     *********************************************************************************/
    /*public function Cuenta0Fec($c0, $d1, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        
        // Validar el objeto de conexión
        if (!($c0 instanceof mysqli)) {
            error_log("Cuenta0Fec: conexión inválida");
            return 0;
        }

        // Sanitizar valores: tabla y campo
        // (Idealmente solo usarlos hardcodeados, si vienen de usuario usar una lista blanca)
        $tabla = $c0->real_escape_string($d1);
        $campo = $c0->real_escape_string($d6);
        $valor = $c0->real_escape_string($d7);

        // Armar la consulta
        $sql = "SELECT COUNT(*) AS total FROM `$tabla` WHERE `$campo` >= '$valor'";

        $res = $c0->query($sql);

        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        } else {
            // Opcional: log del error para debug
            error_log("Cuenta0Fec: error en consulta [$sql] : ".$c0->error);
        }
        return 0;
    }*/


    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d4 <= $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function CuentaFechasLim($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` <= '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d8 != $d9, $d10 = $d11, y dentro del rango de fechas ($d4 <= $d5 y $d6 >= $d7).
     *********************************************************************************/
    public function ContarFechas4($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7, $d8, $d9, $d10, $d11) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3  = $c0->real_escape_string($d3);
        $d9  = $c0->real_escape_string($d9);
        $d11 = $c0->real_escape_string($d11);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d8` != '$d9' AND `$d10` = '$d11' AND `$d4` <= '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 que cumplen:
     * $d2 = $d3 y $d4 = $d5.
     *********************************************************************************/
    public function Sumar2cond($c0, $c1, $d1, $d2, $d3, $d4, $d5) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d5 = $c0->real_escape_string($d5);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` = '$d5'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 que cumplen:
     * $d2 = $d3.
     *********************************************************************************/
    public function Sumar1cond($c0, $c1, $d1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` = '$d3'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 para registros donde 
     * `$d2` >= $d3.
     *********************************************************************************/
    public function Sumar0Fecha($c0, $c1, $d1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` >= '$d3'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 de la tabla $d1.
     *********************************************************************************/
    public function Sumar($c0, $c1, $d1) {
        $this->trackUsage();  // Registra el uso de este método.
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1`";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 para registros que cumplen:
     * $d2 = $d3, $d4 <= $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function SumarFechas($c0, $c1, $d1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` <= '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 para registros que cumplen:
     * $d2 = $d3 y $d6 >= $d7.
     *********************************************************************************/
    public function Sumar1Fechas($c0, $c1, $d1, $d2, $d3, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 para registros que cumplen:
     * $d2 = $d3, $d6 >= $d7 y $d8 = $d9.
     *********************************************************************************/
    public function Sumar1Fec1Cond($c0, $c1, $d1, $d2, $d3, $d6, $d7, $d8, $d9) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $d9 = $c0->real_escape_string($d9);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d6` >= '$d7' AND `$d8` = '$d9'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Suma los valores de la columna $c1 en la tabla $d1 para registros que cumplen:
     * $d2 = $d3, $d8 != $d9, $d4 <= $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function SumarFechasIndis($c0, $c1, $d1, $d2, $d3, $d4, $d5, $d6, $d7, $d8, $d9) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $d9 = $c0->real_escape_string($d9);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d8` != '$d9' AND `$d4` <= '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Retorna el valor de la columna $c1 de la tabla $d1 para registros que cumplen:
     * $d2 = $d3 y $d6 >= $d7.
     *********************************************************************************/
    public function Buscar1Fechas($c0, $c1, $d1, $d2, $d3, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string($d3);
        $d7 = $c0->real_escape_string($d7);
        $sql = "SELECT `$c1` FROM `$d1` WHERE `$d2` = '$d3' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res && $Reg = $res->fetch_assoc()) {
            return $Reg[$c1];
        }
        return null;
    }

    /**************************************************************************************************************************
    Esta funcion valida un usuario y una contraseña  para el modod sandbox;
    1.- $c0   => Recibe la Conexion a la base de datos
    2.- $usr  => Recibe el usuario
    2.- $pass => Recibe la contraseña
    ****************************************************************************************************************************/
    public function ValidarUsrAPI_sandbox($c0,$usr){
        $usrA  = "Api_KASU_Sandbox";
        if($usrA === $usr){
            return true;
        }
    }
}
    //echo "<br>imprime lo que trae el la variable mysqli -> en Finciones Basicas <br>";
    //var_dump($mysqli);
?>
