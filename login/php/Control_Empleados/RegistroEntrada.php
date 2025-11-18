<?php
/**
 * Archivo: asistencia.php
 * Qué hace: Control de asistencia para empleados (entrada, descansos, regreso a oficina y salida)
 * - Exige GPS para registrar eventos clave
 * - Calcula antelación, retardo, extra y totales de descansos
 * Compatibilidad: PHP 8.2
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/eia/session.php';
kasu_session_start();
date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/../../eia/librerias.php'; // Debe exponer $mysqli, $basicas, clase Correo, etc.

// ===== Helpers mínimos =====
function path_from_ref(string $ref, string $fallback = '/login/Mesa_Herramientas.php'): string {
    $p = parse_url($ref, PHP_URL_PATH);
    return $p && str_starts_with($p, '/') ? $p : $fallback;
}
function redirect_msg(string $urlPath, string $msg, int $code = 303): void {
    $qs = (str_contains($urlPath, '?') ? '&' : '?') . 'Msg=' . rawurlencode($msg);
    header('Location: https://kasu.com.mx' . $urlPath . $qs, true, $code);
    exit();
}
function hms_diff(string $a, string $b): string {
    // Devuelve |a - b| como H:i:s, para antelación/retardo/extra
    $t = abs(strtotime($a) - strtotime($b));
    return gmdate('H:i:s', $t);
}
function req(string $k): ?string {
    return isset($_POST[$k]) ? trim((string)$_POST[$k]) : null;
}

// ===== Verificación de sesión =====
if (empty($_SESSION['Vendedor'])) {
    redirect_msg('/login/', 'Sesión inválida');
}

// ===== Reloj base del día =====
$FECHA_HOY   = date('Y-m-d');
$HORA_NOW    = date('H:i:s');

// Horarios de política
$ENTRADA     = '09:00:00';
$TOLERANCIA  = '09:10:00';
$SALIDA      = '17:00:00';
$AVISO       = '10:00:00';

// Etiquetas de eventos
$EV_DESC     = 'Descanso';
$EV_OFI      = 'Oficina';

// Variables de UI por defecto
$Color   = '#04B431';
$Texto   = 'Entrada';
$val     = '';
$display = 'none';

// Ruta de regreso
$referer = req('Host') ?? ($_SERVER['HTTP_REFERER'] ?? '/login/Mesa_Herramientas.php');
$url     = path_from_ref($referer);

// Normaliza input principal
$accionEntrada = req('entrada') ?? req('Entrada') ?? '';
$accionSalida  = req('salida')  ?? req('Salida')  ?? '';
$lat           = req('latitud');
$lon           = req('longitud');

$requireGPS = static function() use ($lat, $lon): bool {
    return !(is_string($lat) && $lat !== '' && is_string($lon) && $lon !== '');
};

// Id interno de empleado y controles previos
$VendedorId = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
if ($VendedorId <= 0) {
    redirect_msg($url, 'Empleado no encontrado');
}

$existeAsis   = (int)$basicas->ConUnCon($mysqli, 'Asistencia', 'Fech_in', $FECHA_HOY, 'usuario_id', $VendedorId);
$_SESSION['exis']     = $basicas->BuscarCampos($mysqli, 'usuario_id', 'Asistencia', 'Fech_in', $FECHA_HOY);
$_SESSION['Registre'] = (string)$basicas->Buscar2Campos($mysqli, 'Salida', 'Asistencia', 'Fech_in', $FECHA_HOY, 'usuario_id', $VendedorId);
$salidaRegistrada     = $_SESSION['Registre'];

// Determinar si es día laboral (1..5) o fin de semana (0,6)
$esLaboral = !in_array((int)date('w', strtotime($FECHA_HOY)), [0, 6], true);

/******************************  INICIO Registro entrada, descansos, comida y salida   *************************************/
if ($esLaboral) {
    // Día laboral
    if ($existeAsis === 0) {
        // No tiene registro del día aún
        $Color = '#04B431';
        $Texto = 'Entrada';
        $val   = '';
        $display = 'none';

        if ($accionEntrada === 'Entrada') {
            $HORA_NOW = date('H:i:s'); // valor fresco

            // Antelación o retardo
            $antelacion = hms_diff($ENTRADA, $HORA_NOW);  // si llegó antes
            $retardo    = hms_diff($HORA_NOW, $TOLERANCIA);

            if (strtotime($HORA_NOW) < strtotime($ENTRADA)) {
                // Llegó antes de la hora de entrada
                if ($requireGPS()) {
                    redirect_msg($url, 'Permite tu ubicación para registrar tu entrada');
                }

                $Vine = [
                    'usuario_id' => $VendedorId,
                    'Fech_in'    => $FECHA_HOY,
                    'Entrada'    => $HORA_NOW,
                    'Antelacion' => $antelacion,
                ];
                $_SESSION['llegue'] = $basicas->InsertCampo($mysqli, 'Asistencia', $Vine);
                $_SESSION['exis']   = $basicas->BuscarCampos($mysqli, 'usuario_id', 'Asistencia', 'Fech_in', $FECHA_HOY);

                $Color = '#FFBF00';
                $Texto = $EV_DESC;

                redirect_msg($url, 'Se registró tu entrada. Llegaste antes');
            }

            if (strtotime($HORA_NOW) > strtotime($ENTRADA) && strtotime($HORA_NOW) < strtotime($SALIDA)) {
                if ($requireGPS()) {
                    redirect_msg($url, 'Permite tu ubicación para registrar tu entrada');
                }

                $Vine = [
                    'usuario_id' => $VendedorId,
                    'Fech_in'    => $FECHA_HOY,
                    'Entrada'    => $HORA_NOW,
                    'Retardo'    => hms_diff($HORA_NOW, $ENTRADA),
                ];

                $_SESSION['llegue'] = $basicas->InsertCampo($mysqli, 'Asistencia', $Vine);
                $_SESSION['exis']   = $basicas->BuscarCampos($mysqli, 'usuario_id', 'Asistencia', 'Fech_in', $FECHA_HOY);
                $Color = '#FFBF00';
                $Texto = $EV_DESC;

                if (strtotime($HORA_NOW) <= strtotime($TOLERANCIA)) {
                    redirect_msg($url, 'Se registró tu entrada con tolerancia');
                }

                if (strtotime($HORA_NOW) >= strtotime($AVISO)) {
                    // Notificar a supervisor por retardo mayor al aviso
                    $stmt = $mysqli->prepare('SELECT Equipo FROM Empleados WHERE Id = ? LIMIT 1');
                    $stmt->bind_param('i', $VendedorId);
                    $stmt->execute();
                    $rsTeam = $stmt->get_result();
                    $team = $rsTeam?->fetch_assoc();
                    $stmt->close();

                    if ($team && !empty($team['Equipo'])) {
                        $supId = (int)$team['Equipo'];
                        $stmt2 = $mysqli->prepare('SELECT Id, Nombre, IdContact FROM Empleados WHERE Id = ? LIMIT 1');
                        $stmt2->bind_param('i', $supId);
                        $stmt2->execute();
                        $rsBoss = $stmt2->get_result();
                        $boss   = $rsBoss?->fetch_assoc();
                        $stmt2->close();

                        if ($boss) {
                            $geo     = 'latitud: ' . ($lat ?? 'n/d') . ' longitud: ' . ($lon ?? 'n/d');
                            $correo  = (string)$basicas->BuscarCampos($mysqli, 'Mail', 'Contacto', 'id', $boss['IdContact']);
                            $nomVend = (string)$basicas->BuscarCampos($mysqli, 'Nombre', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);

                            // Construir y enviar correo
                            $mensa = Correo::Mensaje('Evento Inusual', $boss['Nombre'], $nomVend, 'Entrada', $HORA_NOW, $geo, '', '', '', '', '', '', '', '', '', '', '', '', '', '');
                            Correo::EnviarCorreo($boss['Nombre'], $correo, 'Retardo', (string)$mensa);
                        }
                    }

                    redirect_msg($url, 'Se notificó a tu supervisor por tu hora de llegada');
                }

                redirect_msg($url, 'Se registró tu entrada. Llegaste tarde');
            }

            if ($requireGPS()) {
                redirect_msg($url, 'Permite tu ubicación para registrar tu entrada');
            }

            // Si no entra en ningún caso anterior
            redirect_msg($url, 'Olvidaste registrar entrada. Recuerda checar mañana');
        }
    } elseif ($existeAsis === 1 && $salidaRegistrada === '00:00:00') {
        // Tiene registro de entrada pero no de salida
        if (($accionSalida === 'Salida E' || $accionSalida === 'Salida') && !$requireGPS()) {
            $Color = '#04B431';
            $Texto = 'Entrada';
            $val   = 'disabled';
            $display = 'none';

            $HORA_NOW = date('H:i:s');
            $extra    = hms_diff($HORA_NOW, $SALIDA);

            // Actualiza la salida y extra con tu helper especializado
            if ($accionSalida === 'Salida E') {
                $ok = $basicas->ActCampoSal($mysqli, 'Asistencia', $HORA_NOW, '', $VendedorId, 'Salida', 'Extra', 'Fech_in', 'Salida', $FECHA_HOY, '00:00:00');
            } else {
                $ok = $basicas->ActCampoSal($mysqli, 'Asistencia', $HORA_NOW, $extra, $VendedorId, 'Salida', 'Extra', 'Fech_in', 'Salida', $FECHA_HOY, '00:00:00');
            }

            if ($ok) {
                redirect_msg($url, 'Salida registrada');
            }
        } elseif ($accionEntrada === $EV_DESC && !$requireGPS()) {
            // Marcó descanso
            $Color = '#04B431';
            $Texto = $EV_OFI;
            redirect_msg($url, 'No olvides registrar tu regreso a la oficina');
        } elseif ($accionEntrada === $EV_OFI && !$requireGPS()) {
            // Regreso a oficina
            $Color = '#FFBF00';
            $Texto = $EV_DESC;
            redirect_msg($url, 'Regreso a oficina registrado');
        } else {
            // Alterna color/texto según conteos de eventos Descanso/Oficina en el día
            $des = 0; $ofi = 0;

            $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM Eventos WHERE Usuario = ? AND FechaRegistro LIKE CONCAT(?, '%') AND Evento = ?");
            $usr  = (string)$_SESSION['Vendedor'];

            $evt = $EV_DESC;
            $stmt->bind_param('sss', $usr, $FECHA_HOY, $evt);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $des = (int)($r['c'] ?? 0);

            $evt = $EV_OFI;
            $stmt->bind_param('sss', $usr, $FECHA_HOY, $evt);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $ofi = (int)($r['c'] ?? 0);
            $stmt->close();

            if ($des !== $ofi) {
                $Color = '#04B431';
                $Texto = $EV_OFI;
            } else {
                $Color = '#FFBF00';
                $Texto = $EV_DESC;
            }
        }
    } else {
        // Estado default sin registros
        $Color   = '#04B431';
        $Texto   = 'Entrada';
        $val     = 'disabled';
        $display = 'none';
    }
} else {
    // Fin de semana
    $_SESSION['Registre'] = (string)$basicas->Buscar2Campos($mysqli, 'Salida', 'Asistencia', 'Fech_in', $FECHA_HOY, 'usuario_id', $VendedorId);
    $existeAsis           = (int)$basicas->ConUnCon($mysqli, 'Asistencia', 'Fech_in', $FECHA_HOY, 'usuario_id', $VendedorId);

    $Color = '#04B431';
    $Texto = 'Trabajar';
    $val   = '';
    $HORA_NOW = date('H:i:s');

    if ($accionEntrada === 'Trabajar' && $existeAsis === 0) {
        if ($requireGPS()) {
            redirect_msg($url, 'Permite tu ubicación para registrar tu entrada');
        }

        $Vine = [
            'usuario_id' => $VendedorId,
            'Fech_in'    => $FECHA_HOY,
            'Entrada'    => $HORA_NOW,
        ];
        $_SESSION['llegue'] = $basicas->InsertCampo($mysqli, 'Asistencia', $Vine);
        $_SESSION['exis']   = $basicas->BuscarCampos($mysqli, 'usuario_id', 'Asistencia', 'Fech_in', $FECHA_HOY);

        $Color = '#FFBF00';
        $Texto = 'Descanso';
        redirect_msg($url, 'Se registró tu entrada');
    } elseif ($existeAsis === 1 && $_SESSION['Registre'] === '00:00:00') {
        if ($accionEntrada === $EV_DESC) {
            $Color = '#04B431';
            $Texto = $EV_OFI;
            redirect_msg($url, 'No olvides registrar tu regreso');
        } elseif ($accionEntrada === $EV_OFI) {
            $Color = '#FFBF00';
            $Texto = $EV_DESC;
            redirect_msg($url, 'Regreso registrado');
        } else {
            $des = 0; $ofi = 0;

            $stmt = $mysqli->prepare("SELECT COUNT(*) AS c FROM Eventos WHERE usuario = ? AND FechaRegistro LIKE CONCAT(?, '%') AND Evento = ?");
            $usr  = (string)$_SESSION['Vendedor'];

            $evt = $EV_DESC;
            $stmt->bind_param('sss', $usr, $FECHA_HOY, $evt);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $des = (int)($r['c'] ?? 0);

            $evt = $EV_OFI;
            $stmt->bind_param('sss', $usr, $FECHA_HOY, $evt);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $ofi = (int)($r['c'] ?? 0);
            $stmt->close();

            if ($des !== $ofi) {
                $Color = '#04B431';
                $Texto = $EV_OFI;
            } else {
                $Color = '#FFBF00';
                $Texto = $EV_DESC;
            }
        }
    }
}
/************************         FIN Registro entrada, descansos, comida y salida    *******************************************/

/**************************        INICIO de Cronometro        ******************************************/
$hrEntrada = (string)$basicas->Buscar2Campos($mysqli, 'Entrada', 'Asistencia', 'Fech_in', $FECHA_HOY, 'usuario_id', $VendedorId);
$resMs     = 0;

if ($hrEntrada !== '') {
    $usr = (string)$_SESSION['Vendedor'];

    // Obtener lista ordenada de timestamps de eventos Descanso/Oficina del día
    $stmt = $mysqli->prepare("
        SELECT FechaRegistro
        FROM Eventos
        WHERE Usuario = ?
          AND FechaRegistro LIKE CONCAT(?, '%')
          AND (Evento = ? OR Evento = ?)
        ORDER BY FechaRegistro ASC
    ");
    $stmt->bind_param('ssss', $usr, $FECHA_HOY, $EV_DESC, $EV_OFI);
    $stmt->execute();
    $rs = $stmt->get_result();

    $fechas = [];
    while ($row = $rs->fetch_assoc()) {
        $fechas[] = (string)$row['FechaRegistro'];
    }
    $stmt->close();

    // Si hay impar, asumimos evento abierto al final del día, así que no cerramos el último par
    $resta = (count($fechas) % 2 === 0) ? 0 : 1;

    for ($i = 0; $i < count($fechas) - $resta; $i += 2) {
        $hDes = substr($fechas[$i],  11); // HH:MM:SS
        $hOfi = substr($fechas[$i+1], 11);
        $resMs += (strtotime($hOfi) - strtotime($hDes));
    }

    // ms para compatibilidad con tu UI previa
    $resMs *= 1000;

    // hInicio en formato original "Month d, Y H:i:s"
    $hInicio = date('F') . ' ' . date('d') . ', ' . date('Y') . ' ' . $hrEntrada;
}

// Conversor para debugging o vistas
function segToMin(int $seg): string {
    $horas   = intdiv($seg, 3600);
    $seg    -= $horas * 3600;
    $minutos = intdiv($seg, 60);
    $segundos= $seg - ($minutos * 60);
    return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
}
/**************************        FIN de Cronometro          ******************************************/

// Nota: $Color, $Texto, $val, $display, $hInicio y $resMs quedan disponibles para la vista que incluya este archivo.
