<?php
// Activar la visualización de errores para depuración (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establece la zona horaria predeterminada
date_default_timezone_set('America/Mexico_City');

// Se obtiene la hora y la fecha actuales
$datHora  = date("H:i:00");
$datFecha = date("Y-m-d");

// Convierte la hora actual y los límites de horario a timestamps para facilitar la comparación
$datHActual  = strtotime($datHora);
$datHEntrada = strtotime("09:00:00");
$datHSalida  = strtotime("17:00:00");

// -------------------------------------------------------------------------
// Consulta de los días festivos
// Se asume que $mysqli es una conexión válida a la base de datos.
$cont = 0;
if (isset($mysqli)) {
    $queryDF = "SELECT diaFest FROM DiasFestivos WHERE diaFest = '$datFecha'";
    $resQueDF = $mysqli->query($queryDF);
    if ($resQueDF) {
        // Cuenta el número de registros que coinciden con la fecha actual
        $cont = mysqli_num_rows($resQueDF);
        //echo "DEBUG: Número de días festivos encontrados: " . $cont . "<br>";
    } else {
        error_log("Error en la consulta de días festivos: " . $mysqli->error);
        //echo "DEBUG: Error en consulta de días festivos.<br>";
    }
} else {
    //echo "DEBUG: Conexión a la base de datos no definida.<br>";
}

// -------------------------------------------------------------------------
// Determinar el teléfono a utilizar en función de la fecha y hora actual

$tel = ""; // Variable para almacenar el teléfono a asignar

if ($cont === 0) {
    // No es día festivo
    // Verificar que el día actual no sea sábado (6) ni domingo (0)
    $diaSemana = date('w', strtotime($datFecha));
    //echo "DEBUG: Día de la semana (0=Domingo, 6=Sábado): " . $diaSemana . "<br>";
    if ($diaSemana != 0 && $diaSemana != 6) {
        // Dentro de días laborales
        if ($datHActual >= $datHEntrada && $datHActual <= $datHSalida) {
            // Dentro del horario de oficina (09:00 - 17:00)
            $venta = "SELECT * FROM Empleados WHERE Nivel = '2' LIMIT 1";
            $res = mysqli_query($mysqli, $venta);
            if ($res && $Reg = mysqli_fetch_assoc($res)) {
                // Si el empleado tiene 'Telefono' igual a 0, se asigna un teléfono de oficina predeterminado
                $tel = ($Reg['Telefono'] == 0) ? "7208177632" : $Reg['Telefono'];
                //echo "DEBUG: Empleado encontrado: " . htmlspecialchars($Reg['Nombre']) . ", teléfono asignado: " . $tel . "<br>";
            } else {
                // Si no se encuentra empleado o hay error, asignar teléfono por defecto
                $tel = "3123091366";
                //echo "DEBUG: No se encontró empleado o error en la consulta; se asigna teléfono por defecto: " . $tel . "<br>";
            }
        } else {
            // Fuera del horario de oficina
            $tel = "3123091366";
            //echo "DEBUG: Fuera del horario de oficina; teléfono asignado: " . $tel . "<br>";
        }
    } else {
        // Fin de semana (sábado o domingo)
        $tel = "3123091366";
        //echo "DEBUG: Es fin de semana; teléfono asignado: " . $tel . "<br>";
    }
} else {
    // Es día festivo
    $tel = "3123091366";
    //echo "DEBUG: Es día festivo; teléfono asignado: " . $tel . "<br>";
}

// Mostrar el teléfono asignado (para depuración)
//echo "Teléfono asignado: " . $tel;
?>
