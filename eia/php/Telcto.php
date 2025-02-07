<?php
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
/*/ Se asume que $mysqli es una conexión válida a la base de datos.
$cont = 0;
$queryDF = "SELECT diaFest FROM DiasFestivos WHERE diaFest = '$datFecha'";
$resQueDF = $mysqli->query($queryDF);
if ($resQueDF) {
    // Cuenta el número de registros que coinciden con la fecha actual
    $cont = mysqli_num_rows($resQueDF);
} else {
    error_log("Error en la consulta de días festivos: " . $mysqli->error);
}
*/
// -------------------------------------------------------------------------
// Determinar el teléfono a utilizar en función de la fecha y hora actual

$tel = ""; // Variable para almacenar el teléfono a asignar

if ($cont === 0) {
    // No es día festivo
    // Verificar que el día actual no sea sábado (6) ni domingo (0)
    if (date('w', strtotime($datFecha)) != 0 && date('w', strtotime($datFecha)) != 6) {
        // Si la hora actual está dentro del horario de oficina (entre 09:00 y 17:00)
        if ($datHActual >= $datHEntrada && $datHActual <= $datHSalida) {
            // Consulta para obtener un empleado con Nivel 2 (ej. atención al cliente)
            $venta = "SELECT * FROM Empleados WHERE Nivel = '2' LIMIT 1";
            $res = mysqli_query($mysqli, $venta);
            if ($res && $Reg = mysqli_fetch_assoc($res)) {
                // Si el empleado tiene 'Telefono' igual a 0, se asigna un teléfono de oficina predeterminado;
                // de lo contrario se usa el teléfono registrado.
                $tel = ($Reg['Telefono'] == 0) ? "7122612898" : $Reg['Telefono'];
            } else {
                // Si no se encuentra ningún empleado o hay error en la consulta, se asigna un valor por defecto.
                $tel = "7122612898";
            }
        } else {
            // Fuera del horario de oficina
            $tel = "7121977370";
        }
    } else {
        // Es sábado o domingo
        $tel = "7122612898";
    }
} else {
    // Es día festivo
    $tel = "7121977370";
}

// Para efectos de prueba, se muestra el teléfono asignado.
echo "Teléfono asignado: " . $tel;
?>
