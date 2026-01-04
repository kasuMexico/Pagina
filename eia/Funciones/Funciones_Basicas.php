<?php
// Contador de veces que se usa cada funcion para seguimiento *JCCM
// Actualizado a compatibilidad PHP 8.2. Sin cambiar firmas ni retornos.
// Fecha: 2025-11-03 — Revisado por JCCM
require_once __DIR__ . '/FunctionUsageTracker.php';

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
        $na = $c0->real_escape_string((string)$na);
        $sql = "SELECT * FROM `$tab` WHERE `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda en la tabla $tab con la condición de que la columna $hy 
     * sea igual a $dj y además que la columna $col contenga el término $na.
     *********************************************************************************/
    public function BLikesCan($c0, $tab, $col, $na, $hy, $dj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na = $c0->real_escape_string((string)$na);
        $dj = $c0->real_escape_string((string)$dj);
        $sql = "SELECT * FROM `$tab` WHERE `$hy` = '$dj' AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda en la tabla $tab con dos condiciones fijas y una búsqueda 
     * en $col usando LIKE.
     *********************************************************************************/
    public function BLikesD2($c0, $tab, $col, $na, $hy, $dj, $hSy, $dSj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na   = $c0->real_escape_string((string)$na);
        $dj   = $c0->real_escape_string((string)$dj);
        $dSj  = $c0->real_escape_string((string)$dSj);
        $sql  = "SELECT * FROM `$tab` WHERE `$hy` = '$dj' AND `$hSy` = '$dSj' AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda con condiciones OR y una cláusula LIKE.
     *********************************************************************************/
    public function BLikes2($c0, $tab, $col, $na, $hy, $dj, $hSy, $dSj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na   = $c0->real_escape_string((string)$na);
        $dj   = $c0->real_escape_string((string)$dj);
        $dSj  = $c0->real_escape_string((string)$dSj);
        $sql  = "SELECT * FROM `$tab` WHERE (`$hy` = '$dj' OR `$hSy` = '$dSj') AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Realiza una búsqueda con múltiples condiciones (combinación de OR y AND) y LIKE.
     *********************************************************************************/
    public function BLikes3($c0, $tab, $col, $na, $hy, $dj, $hSy, $dSj, $h1Sy, $d1Sj) {
        $this->trackUsage();  // Registra el uso de este método.
        $na    = $c0->real_escape_string((string)$na);
        $dj    = $c0->real_escape_string((string)$dj);
        $dSj   = $c0->real_escape_string((string)$dSj);
        $d1Sj  = $c0->real_escape_string((string)$d1Sj);
        $sql   = "SELECT * FROM `$tab` WHERE (`$hy` = '$dj' OR `$hSy` = '$dSj') AND `$h1Sy` = '$d1Sj' AND `$col` LIKE '%$na%'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Inserta datos en la tabla $n1 utilizando un array asociativo $d0 (campo => valor).
     *********************************************************************************/
    public function InsertCampo($c0, $n1, $d0) {
        $this->trackUsage();  // Registra el uso de este método.
        $campos  = array_keys($d0);
        $valores = array_map(function($valor) use ($c0) {
            return $c0->real_escape_string((string)$valor);
        }, array_values($d0));
        $campos_str  = implode(", ", $campos);
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
        $n3 = $c0->real_escape_string((string)$n3);
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
        $n3 = $c0->real_escape_string((string)$n3);
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
        $n3 = $c0->real_escape_string((string)$n3);
        $n5 = $c0->real_escape_string((string)$n5);
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
        $n3 = $c0->real_escape_string((string)$n3);
        $n5 = $c0->real_escape_string((string)$n5);
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
        $n3 = $c0->real_escape_string((string)$n3);
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
     * Fecha: 05/11/2025
     * Revisado por: JCCM
     *********************************************************************************/
    public function BuscarCampos(mysqli $c0, string $d1, string $n1, string $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.

        // 1) Validar identificadores (tabla/columnas) para evitar inyección en nombres
        foreach ([$d1, $n1, $d2] as $id) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', $id)) {
                return null;
            }
        }

        // 2) Si el valor a buscar es null, no consultar
        if ($d3 === null) {
            return null;
        }

        // 3) Preparar consulta con parámetro (no se puede param. nombres de tabla/campo)
        $sql = "SELECT `$d1` FROM `$n1` WHERE `$d2` = ? LIMIT 1";

        try {
            $stmt = $c0->prepare($sql);
            if ($stmt === false) {
                return null; // p.ej. columna inexistente o SQL inválido
            }

            // Tipar el parámetro
            if (is_int($d3) || (is_string($d3) && ctype_digit($d3))) {
                $val  = (int)$d3;
                $type = 'i';
            } else {
                $val  = (string)$d3;
                $type = 's';
            }

            $stmt->bind_param($type, $val);
            $stmt->execute();
            $stmt->bind_result($out);

            if ($stmt->fetch()) {
                $stmt->close();
                return $out;
            }

            $stmt->close();
            return null;
        } catch (\mysqli_sql_exception $e) {
            // Esquema inválido (p.ej. columna que no existe) u otro error. Silenciar y retornar null.
            return null;
        }
    }

    /**
     * Retorna el valor del campo $d1 de la tabla $n1 donde $d2 = $d3 y $d4 = $d5.
     *
     * @param mysqli $c0   Conexión MySQLi activa.
     * @param string $d1   Columna a devolver (p.ej. "Email").
     * @param string $n1   Tabla a consultar (p.ej. "Usuarios").
     * @param string $d2   Columna del primer filtro WHERE (p.ej. "Id").
     * @param mixed  $d3   Valor para $d2 (se envía como parámetro preparado).
     * @param string $d4   Columna del segundo filtro AND (p.ej. "Activo").
     * @param mixed  $d5   Valor para $d4 (se envía como parámetro preparado).
     * @return mixed|null  Valor de $d1 o null si no hay coincidencia/error.
     */
    public function Buscar2Campos($c0, $d1, $n1, $d2, $d3, $d4, $d5) {
        $this->trackUsage(); // Telemetría interna de uso del método.

        // Validación básica de identificadores (tabla/columnas). Evita inyección en nombres.
        $isId = static function($s){ return is_string($s) && preg_match('/^[A-Za-z0-9_]+$/', $s); };
        if (!$isId($d1) || !$isId($n1) || !$isId($d2) || !$isId($d4)) {
            return null;
        }

        // SQL con identificadores escapados mediante backticks. Valores por parámetros.
        $sql = "SELECT `{$d1}` FROM `{$n1}` WHERE `{$d2}` = ? AND `{$d4}` = ? LIMIT 1";
        $stmt = $c0->prepare($sql);
        if (!$stmt) return null;

        // Inferencia simple de tipos para bind_param: i=int, d=float, s=string.
        $t1 = is_int($d3) ? 'i' : (is_float($d3) ? 'd' : 's');
        $t2 = is_int($d5) ? 'i' : (is_float($d5) ? 'd' : 's');
        $stmt->bind_param($t1.$t2, $d3, $d5);

        // Ejecuta y obtiene primera fila.
        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) { $stmt->close(); return null; }
        $row = $res->fetch_assoc();
        $stmt->close();

        // Retorna el valor de la columna solicitada o null.
        return $row[$d1] ?? null;
    }

    /*********************************************************************************
     * Retorna el valor del campo $d1 de la tabla $n1 donde $d2 = $d3, $d4 = $d5 y $d6 = $d7.
     *********************************************************************************/
    public function Buscar3Campos($c0, $d1, $n1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string((string)$d3);
        $d5 = $c0->real_escape_string((string)$d5);
        $d7 = $c0->real_escape_string((string)$d7);
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
        if (strlen((string)$curp) !== 18) {
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
     * Retorna el código de producto para Transporte basado en el rango de edad.
     *********************************************************************************/
    public function ProdTrans($d16) {
        $this->trackUsage();  // Registra el uso de este método.
        if ($d16 >= 2 && $d16 <= 29) {
            return "T02a29";
        } elseif ($d16 >= 30 && $d16 <= 49) {
            return "T30a49";
        } elseif ($d16 >= 50 && $d16 <= 54) {
            return "T50a54";
        } elseif ($d16 >= 55 && $d16 <= 59) {
            return "T55a59";
        } elseif ($d16 >= 60 && $d16 <= 64) {
            return "T60a64";
        } elseif ($d16 >= 65 && $d16 <= 69) {
            return "T65a69";
        }
        return null;
    }

    /*********************************************************************************
     * Actualiza el campo $Val de la tabla $n1 con el valor $act para el registro identificado 
        $c0  =   conexion usada a base de datos
        $Val = nombre de la columna a actualizar.
        $act = nuevo valor que se asignará a esa columna.
        $IdD = Id de la fila objetivo.
        $n1 = nombre de la tabla.
     *********************************************************************************/
    public function ActCampo($c0, $n1, $Val, $act, $IdD) {
        $this->trackUsage();  // Registra el uso de este método.
        $act = $c0->real_escape_string((string)$act);
        $IdD = $c0->real_escape_string((string)$IdD);
        $sql = "UPDATE `$n1` SET `$Val` = '$act' WHERE `Id` = '$IdD'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Actualiza el campo $Val de la tabla $n1 con el valor $act donde la columna $Cam 
     * coincide con $IdD.
     *********************************************************************************/
    public function ActTab($c0, $n1, $Val, $act, $Cam, $IdD) {
        $this->trackUsage();  // Registra el uso de este método.
        $act = $c0->real_escape_string((string)$act);
        $IdD = $c0->real_escape_string((string)$IdD);
        $sql = "UPDATE `$n1` SET `$Val` = '$act' WHERE `$Cam` = '$IdD'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Actualiza el campo $Val de la tabla $n1 con el valor $act donde se cumple la condición 
     * en $Cam = $IdD y $Uno = $Dos.
     *********************************************************************************/
    public function ActDosCampo($c0, $n1, $Val, $act, $Cam, $IdD, $Uno, $Dos) {
        $this->trackUsage();  // Registra el uso de este método.
        $act = $c0->real_escape_string((string)$act);
        $IdD = $c0->real_escape_string((string)$IdD);
        $Dos = $c0->real_escape_string((string)$Dos);
        $sql = "UPDATE `$n1` SET `$Val` = '$act' WHERE `$Cam` = '$IdD' AND `$Uno` = '$Dos'";
        return $c0->query($sql);
    }

    /*********************************************************************************
     * Actualiza dos campos ($Val1 y $Val2) en la tabla $n1 para el registro con Id = $IdD 
     * y donde se cumplan las condiciones en $cam1 = $dat1, $cam2 = $dat2 y $cam3 = $dat3.
     *********************************************************************************/
    public function ActCampoCon($c0, $n1, $act1, $act2, $IdD, $Val1, $Val2, $cam1, $cam2, $cam3, $dat1, $dat2, $dat3) {
        $this->trackUsage();  // Registra el uso de este método.
        $act1 = $c0->real_escape_string((string)$act1);
        $act2 = $c0->real_escape_string((string)$act2);
        $IdD  = $c0->real_escape_string((string)$IdD);
        $dat1 = $c0->real_escape_string((string)$dat1);
        $dat2 = $c0->real_escape_string((string)$dat2);
        $dat3 = $c0->real_escape_string((string)$dat3);
        $sql = "UPDATE `$n1` SET `$Val1` = '$act1', `$Val2` = '$act2' WHERE `Id` = '$IdD' AND `$cam1` = '$dat1' AND `$cam2` = '$dat2' AND `$cam3` = '$dat3'";
        $c0->query($sql);
        return $sql;
    }

    /*********************************************************************************
     * Valida el usuario y la contraseña comparando los hashes SHA256.
     *********************************************************************************/
    public function ValidarUsr($c0, $usr, $pass) {
        $this->trackUsage();  // Registra el uso de este método.
        $TCrip = hash("sha256", (string)$usr);
        $FCrip = hash("sha256", (string)$pass);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d5 = $c0->real_escape_string((string)$d5);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d5 = $c0->real_escape_string((string)$d5);
        $d7 = $c0->real_escape_string((string)$d7);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d4` = '$d5' AND `$d6` != '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros de una Tabla.
     *********************************************************************************/
    public function ContarTabla($c0, $d1) {
        $this->trackUsage();  // Registra el uso de este método.
        $sql = "SELECT COUNT(*) AS total FROM `$d1`";
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
    public function ConUno($c0, $d1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string((string)$d3);
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
     * $d2 = $d3, $d4 <= $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function CuentaFechas($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7, $d8, $d9) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string((string)$d3);
        $d9 = $c0->real_escape_string((string)$d9);
        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d8` != '$d9' AND `$d4` <= '$d5' AND `$d6` >= '$d7'";
        $res = $c0->query($sql);
        if ($res) {
            $Reg = $res->fetch_assoc();
            return $Reg['total'];
        }
        return 0;
    }

    /*********************************************************************************
     * BLOQUE: CuentaFechas0 — 2025-11-05 — Revisado por: JCCM
     * Qué hace:
     *   Cuenta los registros en la tabla $t que cumplen:
     *     `$colLE` <= $fin  Y  `$colGE` >= $ini
     *   Útil para conteos por rango de fechas sin condición de igualdad adicional.
     *
     * Parámetros:
     *   $c0     : mysqli conectado
     *   $t      : nombre de tabla (ej. 'Contacto' o 'Venta')
     *   $colLE  : columna fecha para límite superior (ej. 'FechaRegistro')
     *   $fin    : fecha fin inclusive (ej. '2025-12-31')
     *   $colGE  : columna fecha para límite inferior (normalmente la misma que $colLE)
     *   $ini    : fecha inicio inclusive (ej. '2025-01-01')
     *
     * Retorna: int con el total
     *********************************************************************************/
    public function CuentaFechas0($c0, $t, $colLE, $fin, $colGE, $ini) {
        $this->trackUsage(); // si existe en tu clase

        // Saneamos identificadores (tabla/columnas) a [A-Za-z0-9_]
        $t     = preg_replace('/[^A-Za-z0-9_]/', '', (string)$t);
        $colLE = preg_replace('/[^A-Za-z0-9_]/', '', (string)$colLE);
        $colGE = preg_replace('/[^A-Za-z0-9_]/', '', (string)$colGE);

        // Escapar valores
        $fin = $c0->real_escape_string((string)$fin);
        $ini = $c0->real_escape_string((string)$ini);

        // Query
        $sql = "SELECT COUNT(*) AS total
                FROM `{$t}`
                WHERE `{$colLE}` <= '{$fin}'
                AND `{$colGE}` >= '{$ini}'";

        if ($res = $c0->query($sql)) {
            $row = $res->fetch_assoc();
            return (int)($row['total'] ?? 0);
        }
        return 0;
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d4 = $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function Cuenta1Fec1Cond($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
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
        $this->trackUsage();

        // Guardia sin warnings
        $conn = ($c0 instanceof mysqli && @ $c0->ping()) ? $c0 : null;
        if (!$conn) return 0;

        // Validar identificadores
        foreach ([$d1, $d2, $d6] as $id) {
            if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$id)) return 0;
        }

        // Escapar valores
        $d3 = $conn->real_escape_string((string)$d3);
        $d7 = $conn->real_escape_string((string)$d7);

        $sql = "SELECT COUNT(*) AS total FROM `$d1` WHERE `$d2` = '$d3' AND `$d6` >= '$d7'";
        $res = $conn->query($sql);
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['total'] ?? 0);
    }

    // Funcion que cuenta las fechas 
    function Cuenta0Fec($db, $tabla, $campoFec, $fecIni){
        // Guardia sin warnings
        $conn = ($db instanceof mysqli && @ $db->ping()) ? $db : null;
        if (!$conn) return 0;

        // Validar identificadores (evita inyección en nombres de tabla/campo)
        if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$tabla))     return 0;
        if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$campoFec))  return 0;

        // Escapar valores
        $fecIni = $conn->real_escape_string((string)$fecIni);

        $sql = "SELECT COUNT(*) AS c FROM `$tabla` WHERE `$campoFec` >= '$fecIni'";
        $res = $conn->query($sql);
        $row = $res ? $res->fetch_assoc() : null;
        return (int)($row['c'] ?? 0);
    }

    /*********************************************************************************
     * Cuenta los registros en la tabla $d1 que cumplen:
     * $d2 = $d3, $d4 <= $d5 y $d6 >= $d7.
     *********************************************************************************/
    public function CuentaFechasLim($c0, $d1, $d2, $d3, $d4, $d5, $d6, $d7) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
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
        $d3  = $c0->real_escape_string((string)$d3);
        $d9  = $c0->real_escape_string((string)$d9);
        $d11 = $c0->real_escape_string((string)$d11);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d5 = $c0->real_escape_string((string)$d5);
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
        $d3 = $c0->real_escape_string((string)$d3);
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
        $d3 = $c0->real_escape_string((string)$d3);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
        $d9 = $c0->real_escape_string((string)$d9);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
        $d9 = $c0->real_escape_string((string)$d9);
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
        $d3 = $c0->real_escape_string((string)$d3);
        $d7 = $c0->real_escape_string((string)$d7);
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
    /*
    Convierte un nombre de una persona en mayusculas a minusculas
    // Ejemplos
    // echo Minusculas_Nombre('JUAN PÉREZ GARCÍA');            // Juan Pérez García
    // echo Minusculas_Nombre('MARÍA DEL CARMEN DE LA CRUZ');  // María del Carmen de la Cruz
    // echo Minusculas_Nombre("O'CONNOR MC GREGOR IV");        // O'Connor Mc Gregor IV
    */
    public function Minusculas_Nombre(string $s): string {
        $s = trim(preg_replace('/\s+/', ' ', $s));
        if ($s === '') return '';

        // todo a minúsculas
        $s = mb_strtolower($s, 'UTF-8');

        // partículas que van en minúsculas (si no son la primera palabra)
        $particles = ['de','del','la','las','los','y','e','da','das','do','dos','van','von','di','du','le','lo'];

        $ucfirst = function(string $w): string {
            return mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8') .
                mb_substr($w, 1, null, 'UTF-8');
        };

        $roman = function(string $w): bool {
            return (bool)preg_match('/^(?=[ivxlcdm]+$).{1,4}$/i', $w);
        };

        $words  = explode(' ', $s);
        foreach ($words as $i => $w) {
            if ($w === '') continue;

            // compuesto con guion o apóstrofo
            if (preg_match("/[-’']/u", $w)) {
                $parts = preg_split("/([-’'])/u", $w, -1, PREG_SPLIT_DELIM_CAPTURE);
                foreach ($parts as $k => $p) {
                    if ($p === '-' || $p === "'" || $p === '’') continue;
                    $parts[$k] = $ucfirst($p);
                }
                $words[$i] = implode('', $parts);
                continue;
            }

            // números romanos en mayúsculas
            if ($roman($w)) { $words[$i] = mb_strtoupper($w, 'UTF-8'); continue; }

            // partículas en minúsculas salvo si es la primera palabra
            if ($i > 0 && in_array($w, $particles, true)) {
                $words[$i] = $w;
                continue;
            }

            $words[$i] = $ucfirst($w);
        }

        return implode(' ', $words);
    }

}
?>
