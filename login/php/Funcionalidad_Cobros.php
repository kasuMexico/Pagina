<?php
/********************************************************************************************
 * Qué hace:
 *   Controlador de acciones de cobros/finanzas.
 *
 *   Acciones soportadas (via POST['accion'] o POST['accion_cobro']):
 *
 *   - registrar_deposito
 *       Usado desde Mesa_Finanzas (modal de conciliación).
 *       Espera:
 *         IdPago           (int)  -> Id en tabla Pagos (origen)
 *         IdVenta          (int)  -> Id en tabla Venta
 *         MontoDeposito    (decimal)
 *         FechaDeposito    (Y-m-d)
 *         HoraDeposito     (HH:MM, opcional)
 *         Banco            (string)
 *         ReferenciaDeposito (string, opcional)
 *         Host             (para auditoría / regreso)
 *
 *   - recordatorio_correo
 *       Usado desde Mesa_Finanzas (tabla Pagos).
 *       Espera:
 *         IdVenta
 *         Host
 *       Obtiene correo y liga de pago desde la BD y arma el mensaje.
 *
 *   - recordatorio_sms
 *       Usado desde Mesa_Finanzas (tabla Pagos).
 *       Espera:
 *         IdVenta
 *         Host
 *       Registra un SMS con la liga de pago (otro proceso lo enviará).
 *
 *   - mp_enviar_liga_correo
 *       Usado desde Mesa_Finanzas (tabla Operaciones MP).
 *       Espera:
 *         folio (Venta.IdFIrma / VentasMercadoPago.folio)
 *         Host
 *
 * Tablas involucradas:
 *   - Pagos
 *   - Venta
 *   - Contacto
 *   - VentasMercadoPago
 *   - Eventos
 *   - DepositosBancarios       (sugerida, ver definición)
 *   - RecordatoriosCobro       (sugerida, ver definición)
 *
 * Fecha: 15/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../../eia/librerias.php';
kasu_apply_error_settings(); // 2025-11-18: Log centralizado para Mesa Cobros
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION['Vendedor'])) {
    http_response_code(403);
    header('Location: https://kasu.com.mx/login');
    exit;
}

if (!function_exists('h')) {
    function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/**
 * Helper para redirigir de vuelta a Mesa_Finanzas con mensaje.
 */
function cobros_redirect(string $msg = '', array $extra = []): void {
    $params = [];
    if ($msg !== '') {
        $params['Msg'] = $msg;
    }
    foreach ($extra as $k => $v) {
        $params[$k] = $v;
    }
    $qs = $params ? ('?' . http_build_query($params)) : '';
    header('Location: /login/Mesa_Finanzas.php' . $qs);
    exit;
}

/**
 * Registra un evento en la tabla Eventos.
 */
function cobros_registrar_evento(mysqli $mysqli, string $evento, array $ctx = []): void {
    $IdFinger = $ctx['IdFinger'] ?? null;
    $Contacto = $ctx['Contacto'] ?? null;
    $IdGps    = $ctx['IdGps'] ?? null;
    $Host     = $ctx['Host'] ?? ($_SERVER['PHP_SELF'] ?? 'Funcionalidad_Cobros.php');
    $Usuario  = $ctx['Usuario'] ?? ($_SESSION['Vendedor'] ?? '');
    $IdVta    = $ctx['IdVenta'] ?? null;
    $IdUsr    = $ctx['IdUsr'] ?? $Usuario;
    $Cupon    = $ctx['Cupon'] ?? null;

    $sql = "INSERT INTO Eventos
              (IdFInger, Contacto, Idgps, Evento, Host, MetodGet, Usuario, IdVta,
               IdUsr, connection, timezone, touch, Cupon)
            VALUES (?,?,?,?,?, ?,?,?,?,?, ?, ?,?)";

    $metod  = 'POST';
    $conn   = 'PWA';
    $tz     = date_default_timezone_get() ?: 'America/Mexico_City';
    $touch  = 'Mesa_Finanzas';

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param(
        'ssissssssssss',
        $IdFinger,
        $Contacto,
        $IdGps,
        $evento,
        $Host,
        $metod,
        $Usuario,
        $IdVta,
        $IdUsr,
        $conn,
        $tz,
        $touch,
        $Cupon
    );
    $stmt->execute();
    $stmt->close();
}

/**
 * Valida token CSRF específico de cobros.
 */
function cobros_check_csrf(): void {
    $tokenSession = $_SESSION['csrf_cobros'] ?? '';
    $tokenPost    = $_POST['csrf'] ?? '';
    if ($tokenSession === '' || !hash_equals((string)$tokenSession, (string)$tokenPost)) {
        http_response_code(403);
        cobros_redirect('Sesión expirada o token inválido. Vuelve a intentar.');
    }
}

/**
 * Obtiene datos básicos de una venta + contacto.
 */
function cobros_obtener_venta(mysqli $mysqli, int $IdVenta): ?array {
    $sql = "
        SELECT
            v.Id,
            v.Nombre,
            v.Producto,
            v.IdContact,
            v.IdFIrma,
            v.Status,
            c.Mail     AS email,
            c.Telefono AS telefono
        FROM Venta v
        LEFT JOIN Contacto c ON c.id = v.IdContact
        WHERE v.Id = ?
        LIMIT 1
    ";
    $st = $mysqli->prepare($sql);
    $st->bind_param('i', $IdVenta);
    $st->execute();
    $row = $st->get_result()->fetch_assoc() ?: null;
    $st->close();
    return $row ?: null;
}

/**
 * Obtiene la liga de pago (init_point) para un folio.
 * Si no existe preferencia, devuelve la URL para crearla.
 */
function cobros_obtener_liga_pago(mysqli $mysqli, string $folio): string {
    $folio = trim($folio);
    if ($folio === '') return '';

    $sql = "SELECT mp_init_point FROM VentasMercadoPago WHERE folio = ? LIMIT 1";
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $folio);
    $st->execute();
    $row = $st->get_result()->fetch_assoc() ?: null;
    $st->close();

    if ($row && !empty($row['mp_init_point'])) {
        return (string)$row['mp_init_point'];
    }

    // Fallback: recrear preferencia
    return 'https://kasu.com.mx/pago/crear_preferencia.php?ref=' . urlencode($folio);
}

/**
 * Acción: registrar_deposito
 * Compatible tanto con el esquema nuevo (IdPago/MontoDeposito/ReferenciaDeposito)
 * como con el original (IdOrigen/TipoOrigen/Monto/Referencia).
 */
function cobros_registrar_deposito(mysqli $mysqli): void {
    cobros_check_csrf();

    // Compatibilidad: nuevo formulario usa IdPago; el anterior IdOrigen + TipoOrigen.
    $IdPagoForm = (int)($_POST['IdPago'] ?? 0);
    $IdOrigen   = (int)($_POST['IdOrigen'] ?? $IdPagoForm);
    $TipoOrigen = strtoupper((string)($_POST['TipoOrigen'] ?? 'PAGO'));

    $IdVenta = (int)($_POST['IdVenta'] ?? 0);

    // Monto
    if (isset($_POST['Monto'])) {
        $Monto = (float)$_POST['Monto'];
    } else {
        $Monto = (float)($_POST['MontoDeposito'] ?? 0);
    }

    $FDep = trim((string)($_POST['FechaDeposito'] ?? ''));
    $HDep = trim((string)($_POST['HoraDeposito'] ?? ''));
    $Banco = trim((string)($_POST['Banco'] ?? ''));
    // Compatibilidad de nombres
    $Ref   = trim((string)($_POST['ReferenciaDeposito'] ?? ($_POST['Referencia'] ?? '')));
    $Notas = trim((string)($_POST['Notas'] ?? ''));

    if ($IdOrigen <= 0 || $IdVenta <= 0 || $Monto <= 0 || $FDep === '' || $Banco === '' || !in_array($TipoOrigen, ['PAGO','MP'], true)) {
        cobros_redirect('Datos incompletos para registrar depósito.');
    }

    // Validaciones básicas de fecha/hora
    $dtCheck = DateTime::createFromFormat('Y-m-d', $FDep);
    if (!$dtCheck || $dtCheck->format('Y-m-d') !== $FDep) {
        cobros_redirect('Fecha de depósito inválida.');
    }
    if ($HDep !== '') {
        $tCheck = DateTime::createFromFormat('H:i', $HDep);
        if (!$tCheck || $tCheck->format('H:i') !== $HDep) {
            cobros_redirect('Hora de depósito inválida.');
        }
    } else {
        $HDep = null;
    }

    $Usuario = (string)($_SESSION['Vendedor'] ?? '');
    $Host    = $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? '');

    $mysqli->begin_transaction();
    try {
        // 1) Insertar depósito en tabla DepositosBancarios
        $sql = "INSERT INTO DepositosBancarios
                    (IdOrigen, TipoOrigen, IdVenta, Monto, FechaDeposito, HoraDeposito,
                     Banco, Referencia, UsuarioConciliacion, Notas)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param(
            'isidssssss',
            $IdOrigen,
            $TipoOrigen,
            $IdVenta,
            $Monto,
            $FDep,
            $HDep,
            $Banco,
            $Ref,
            $Usuario,
            $Notas
        );
        $stmt->execute();
        $stmt->close();

        // 2) Marcar como conciliado el origen
        if ($TipoOrigen === 'PAGO') {
            $sqlUp = "UPDATE Pagos SET status = 'CONCILIADO' WHERE Id = ? LIMIT 1";
        } else {
            // Para MP puedes ajustar según tu modelo de negocio
            $sqlUp = "UPDATE VentasMercadoPago SET estatus = 'APLICADO' WHERE id = ? LIMIT 1";
        }
        $st2 = $mysqli->prepare($sqlUp);
        $st2->bind_param('i', $IdOrigen);
        $st2->execute();
        $st2->close();

        // 3) Evento
        cobros_registrar_evento($mysqli, 'Registrar_Deposito', [
            'Host'    => $Host,
            'IdVenta' => $IdVenta,
            'IdUsr'   => $Usuario,
        ]);

        $mysqli->commit();
        cobros_redirect('Depósito registrado y conciliado correctamente.');

    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('[KASU][Cobros] Error registrar_deposito: '.$e->getMessage());
        cobros_redirect('Error al registrar el depósito. Intenta de nuevo.');
    }
}

/**
 * Acción: recordatorio_correo
 *   - Usa IdVenta para obtener correo, folio y liga de pago.
 */
function cobros_recordatorio_correo(mysqli $mysqli): void {
    cobros_check_csrf();

    $IdVenta = (int)($_POST['IdVenta'] ?? 0);
    $Host    = $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? '');

    if ($IdVenta <= 0) {
        cobros_redirect('Venta inválida para recordatorio por correo.');
    }

    $venta = cobros_obtener_venta($mysqli, $IdVenta);
    if (!$venta) {
        cobros_redirect('No se encontró la venta para recordatorio por correo.');
    }

    $email = filter_var((string)($venta['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        cobros_redirect('La venta no tiene correo válido para recordatorio.');
    }

    $folio   = (string)($venta['IdFIrma'] ?? '');
    $nombre  = trim((string)($venta['Nombre'] ?? 'Cliente KASU'));
    $producto= trim((string)($venta['Producto'] ?? 'Servicio KASU'));

    $ligaPago = cobros_obtener_liga_pago($mysqli, $folio);

    $mensaje = "Hola {$nombre}:\n\n"
             . "Te recordamos el pago de tu servicio {$producto} en KASU.\n\n"
             . "Puedes completar tu pago en el siguiente enlace seguro:\n{$ligaPago}\n\n"
             . "Referencia de tu contratación: {$folio}\n\n"
             . "Si ya realizaste tu pago, por favor ignora este mensaje.\n\n"
             . "KASU - Protege a quien amas.";

    $Usuario = (string)($_SESSION['Vendedor'] ?? '');

    $mysqli->begin_transaction();
    try {
        // 1) Registrar en RecordatoriosCobro
        $sql = "INSERT INTO RecorditariosCobro
                    (IdVenta, Medio, Destino, Mensaje, Usuario)
                VALUES (?, 'EMAIL', ?, ?, ?)";
        // OJO: si tu tabla se llama exactamente RecordatoriosCobro, corrige el nombre:
        // $sql = "INSERT INTO RecordatoriosCobro ...";
        $sql = str_replace('RecorditariosCobro', 'RecordatoriosCobro', $sql);

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isss', $IdVenta, $email, $mensaje, $Usuario);
        $stmt->execute();
        $stmt->close();

        // 2) Evento
        cobros_registrar_evento($mysqli, 'Recordatorio_Correo', [
            'Host'    => $Host,
            'IdVenta' => $IdVenta,
            'IdUsr'   => $Usuario,
        ]);

        $mysqli->commit();

        // 3) Envío inmediato (sencillo). Puedes sustituir por tu propio sistema.
        @mail(
            $email,
            'Recordatorio de pago KASU',
            $mensaje,
            "From: notificaciones@kasu.com.mx\r\nContent-Type: text/plain; charset=UTF-8"
        );

        cobros_redirect('Recordatorio de pago enviado correctamente.');

    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('[KASU][Cobros] Error recordatorio_correo: '.$e->getMessage());
        cobros_redirect('Error al registrar/enviar el recordatorio de correo.');
    }
}

/**
 * Acción: recordatorio_sms
 *   - Usa IdVenta para obtener teléfono, folio y liga de pago.
 *   - Sólo registra en RecordatoriosCobro (otro proceso envía el SMS).
 */
function cobros_recordatorio_sms(mysqli $mysqli): void {
    cobros_check_csrf();

    $IdVenta = (int)($_POST['IdVenta'] ?? 0);
    $Host    = $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? '');

    if ($IdVenta <= 0) {
        cobros_redirect('Venta inválida para recordatorio SMS.');
    }

    $venta = cobros_obtener_venta($mysqli, $IdVenta);
    if (!$venta) {
        cobros_redirect('No se encontró la venta para recordatorio SMS.');
    }

    $tel = preg_replace('/\D+/', '', (string)($venta['telefono'] ?? ''));
    if (strlen($tel) === 10) {
        $tel = '52' . $tel;
    }
    if ($tel === '') {
        cobros_redirect('La venta no tiene teléfono válido para SMS.');
    }

    $folio    = (string)($venta['IdFIrma'] ?? '');
    $producto = trim((string)($venta['Producto'] ?? 'Servicio KASU'));
    $ligaPago = cobros_obtener_liga_pago($mysqli, $folio);

    $mensaje = "KASU: para completar tu contratación de {$producto}, "
             . "puedes pagar en {$ligaPago} Ref: {$folio}";

    $Usuario = (string)($_SESSION['Vendedor'] ?? '');

    $mysqli->begin_transaction();
    try {
        $sql = "INSERT INTO RecordatoriosCobro
                    (IdVenta, Medio, Destino, Mensaje, Usuario)
                VALUES (?, 'SMS', ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isss', $IdVenta, $tel, $mensaje, $Usuario);
        $stmt->execute();
        $stmt->close();

        cobros_registrar_evento($mysqli, 'Recordatorio_SMS', [
            'Host'    => $Host,
            'IdVenta' => $IdVenta,
            'IdUsr'   => $Usuario,
        ]);

        $mysqli->commit();
        cobros_redirect('Recordatorio SMS registrado correctamente (pendiente de envío).');

    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('[KASU][Cobros] Error recordatorio_sms: '.$e->getMessage());
        cobros_redirect('Error al registrar el recordatorio SMS.');
    }
}

/**
 * Acción: mp_enviar_liga_correo
 *   - Usa folio (IdFIrma / VentasMercadoPago.folio) para obtener Venta + correo.
 */
function cobros_mp_enviar_liga_correo(mysqli $mysqli): void {
    cobros_check_csrf();

    $folio = trim((string)($_POST['folio'] ?? ''));
    $Host  = $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? '');

    if ($folio === '') {
        cobros_redirect('Folio inválido para enviar liga de pago.');
    }

    // Buscar MP + Venta + Contacto
    $sql = "
        SELECT
            mp.folio,
            mp.amount,
            v.Id          AS IdVenta,
            v.Nombre      AS Nombre,
            v.Producto    AS Producto,
            v.IdFIrma     AS IdFIrma,
            c.Mail        AS email
        FROM VentasMercadoPago mp
        LEFT JOIN Venta v      ON v.IdFIrma = mp.folio
        LEFT JOIN Contacto c   ON c.id = v.IdContact
        WHERE mp.folio = ?
        LIMIT 1
    ";
    $st = $mysqli->prepare($sql);
    $st->bind_param('s', $folio);
    $st->execute();
    $row = $st->get_result()->fetch_assoc() ?: null;
    $st->close();

    if (!$row) {
        cobros_redirect('No se encontró la operación de Mercado Pago para ese folio.');
    }

    $IdVenta = (int)($row['IdVenta'] ?? 0);
    if ($IdVenta <= 0) {
        cobros_redirect('El folio no está asociado a una venta válida.');
    }

    $email = filter_var((string)($row['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        cobros_redirect('La operación no tiene correo válido para enviar la liga.');
    }

    $producto = trim((string)($row['Producto'] ?? 'Servicio KASU'));
    $nombre   = trim((string)($row['Nombre'] ?? 'Cliente KASU'));

    $ligaPago = cobros_obtener_liga_pago($mysqli, $folio);

    $mensaje = "Hola {$nombre}:\n\n"
             . "Te compartimos nuevamente la liga para completar el pago de tu servicio {$producto} en KASU.\n\n"
             . "Liga de pago: {$ligaPago}\n\n"
             . "Referencia de tu compra: {$folio}\n\n"
             . "Si ya realizaste tu pago, por favor ignora este mensaje.\n\n"
             . "KASU - Protege a quien amas.";

    $Usuario = (string)($_SESSION['Vendedor'] ?? '');

    $mysqli->begin_transaction();
    try {
        // Registrar en RecordatoriosCobro
        $sqlIns = "INSERT INTO RecordatoriosCobro
                      (IdVenta, Medio, Destino, Mensaje, Usuario)
                   VALUES (?, 'EMAIL', ?, ?, ?)";
        $stmt = $mysqli->prepare($sqlIns);
        $stmt->bind_param('isss', $IdVenta, $email, $mensaje, $Usuario);
        $stmt->execute();
        $stmt->close();

        cobros_registrar_evento($mysqli, 'MP_Enviar_Liga_Correo', [
            'Host'    => $Host,
            'IdVenta' => $IdVenta,
            'IdUsr'   => $Usuario,
        ]);

        $mysqli->commit();

        @mail(
            $email,
            'Liga de pago KASU',
            $mensaje,
            "From: notificaciones@kasu.com.mx\r\nContent-Type: text/plain; charset=UTF-8"
        );

        cobros_redirect('Liga de pago enviada correctamente.');

    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('[KASU][Cobros] Error mp_enviar_liga_correo: '.$e->getMessage());
        cobros_redirect('Error al enviar la liga de pago.');
    }
}

/* ============================================================================
 * ROUTER PRINCIPAL
 * ========================================================================== */

// Acepta tanto 'accion' como 'accion_cobro'
$accion = $_POST['accion'] ?? ($_POST['accion_cobro'] ?? '');

switch ($accion) {
    case 'registrar_deposito':
        cobros_registrar_deposito($mysqli);
        break;

    case 'recordatorio_correo':
        cobros_recordatorio_correo($mysqli);
        break;

    case 'recordatorio_sms':
        cobros_recordatorio_sms($mysqli);
        break;

    case 'mp_enviar_liga_correo':
        cobros_mp_enviar_liga_correo($mysqli);
        break;

    default:
        cobros_redirect('Acción de cobros no reconocida.');
}
