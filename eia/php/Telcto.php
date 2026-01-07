<?php
/**
 * Archivo: Telcto.php
 */

date_default_timezone_set('America/Mexico_City');

$telOficinaDefault = '7208177632';
$telFueraHorario = '3123091366';

function kasu_parse_client_time(): ?DateTimeImmutable {
    $clientTs = filter_input(INPUT_COOKIE, 'kasu_client_ts', FILTER_VALIDATE_INT);
    if (!$clientTs) {
        $clientTs = filter_input(INPUT_GET, 'client_ts', FILTER_VALIDATE_INT);
    }

    if (!$clientTs) {
        return null;
    }

    $offsetMinutes = filter_input(INPUT_COOKIE, 'kasu_tz_offset', FILTER_VALIDATE_INT);
    $tz = null;
    if (is_int($offsetMinutes)) {
        $offsetSeconds = -$offsetMinutes * 60;
        $hours = intdiv($offsetSeconds, 3600);
        $minutes = abs(intdiv($offsetSeconds % 3600, 60));
        $tz = new DateTimeZone(sprintf('%+03d:%02d', $hours, $minutes));
    }

    $time = new DateTimeImmutable('@' . $clientTs);
    return $tz ? $time->setTimezone($tz) : $time;
}

$now = kasu_parse_client_time();
if (!$now) {
    $now = new DateTimeImmutable('now', new DateTimeZone('America/Mexico_City'));
}

$datHora = $now->format('H:i:00');
$datFecha = $now->format('Y-m-d');

$datHActual = strtotime($datHora);
$datHEntrada = strtotime('09:00:00');
$datHSalida = strtotime('17:00:00');

// -------------------------------------------------------------------------
// Consulta de los días festivos
// Se asume que $mysqli es una conexión válida a la base de datos.
$cont = 0;
if (isset($mysqli) && $mysqli instanceof mysqli) {
    $stmt = $mysqli->prepare("SELECT 1 FROM DiasFestivos WHERE diaFest = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $datFecha);
        if ($stmt->execute()) {
            $stmt->store_result();
            $cont = (int) $stmt->num_rows;
        }
        $stmt->close();
    }
}

// -------------------------------------------------------------------------
// Determinar el teléfono a utilizar en función de la fecha y hora actual

$tel = '';
$telOficina = $telOficinaDefault;

if ($cont === 0) {
    // No es día festivo
    // Verificar que el día actual no sea sábado (6) ni domingo (0)
    $diaSemana = date('w', strtotime($datFecha));
    //echo "DEBUG: Día de la semana (0=Domingo, 6=Sábado): " . $diaSemana . "<br>";
    if ($diaSemana != 0 && $diaSemana != 6) {
        // Dentro de días laborales
        if ($datHActual >= $datHEntrada && $datHActual <= $datHSalida) {
            // Dentro del horario de oficina (09:00 - 17:00)
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $resEmp = $mysqli->query("SELECT * FROM Empleados WHERE Nivel = '2' LIMIT 1");
                if ($resEmp && ($row = $resEmp->fetch_assoc())) {
                    foreach (['Telefono', 'NoTel', 'Celular', 'Telefono1', 'Tel', 'Tel1'] as $field) {
                        if (!empty($row[$field]) && $row[$field] !== '0') {
                            $telOficina = (string) $row[$field];
                            break;
                        }
                    }
                }
            }
            $tel = $telOficina;
        } else {
            $tel = $telFueraHorario;
        }
    } else {
        // Fin de semana (sábado o domingo)
        $tel = $telFueraHorario;
    }
} else {
    // Es día festivo
    $tel = $telFueraHorario;
}
?>
