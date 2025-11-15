<?php
/********************************************************************************************
 * Qué hace:
 *   Controlador de acciones de cobros/finanzas.
 *
 *   Acciones soportadas (via POST['accion_cobro']):
 *   - registrar_deposito :
 *       Registra un depósito bancario y lo asocia a un pago (Pagos) o a un cobro de MP
 *       (VentasMercadoPago). Marca el pago como conciliado (campo status) si aplica.
 *   - recordatorio_correo :
 *       Registra un recordatorio de pago por correo y, opcionalmente, dispara el envío.
 *   - recordatorio_sms :
 *       Registra un recordatorio de pago por SMS (queda listo para que un job lo envíe).
 *
 * Tablas involucradas (existentes):
 *   - Pagos
 *   - VentasMercadoPago
 *   - Eventos
 *
 * Tablas sugeridas NUEVAS:
 *   - DepositosBancarios
 *       Id INT AI PK
 *       IdOrigen INT NOT NULL        -- Id del pago o de la venta MP
 *       TipoOrigen ENUM('PAGO','MP') NOT NULL
 *       IdVenta INT DEFAULT NULL
 *       Monto DECIMAL(12,2) NOT NULL
 *       FechaDeposito DATE NOT NULL
 *       HoraDeposito TIME DEFAULT NULL
 *       Banco VARCHAR(80) NOT NULL
 *       Referencia VARCHAR(80) DEFAULT NULL
 *       UsuarioConciliacion VARCHAR(30) NOT NULL  -- IdUsuario del que concilia
 *       FechaRegistro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
 *       Notas TEXT NULL
 *
 *   - RecordatoriosCobro
 *       Id INT AI PK
 *       IdVenta INT NOT NULL
 *       Medio ENUM('EMAIL','SMS') NOT NULL
 *       Destino VARCHAR(150) NOT NULL
 *       Mensaje TEXT NOT NULL
 *       Usuario VARCHAR(30) NOT NULL      -- quien lo programó
 *       FechaProgramado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
 *       Enviado TINYINT(1) NOT NULL DEFAULT 0
 *       FechaEnvio DATETIME NULL
 *
 * Fecha: 14/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../../eia/librerias.php';
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
 * Pequeño helper para redirigir de vuelta a Mesa_Finanzas con mensaje.
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
 * Se usa para auditar todas las acciones desde Mesa_Finanzas.
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
 * Debes generar $_SESSION['csrf_cobros'] en Mesa_Finanzas.php y mandarlo en los formularios.
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
 * Acción: registrar_deposito
 *
 * Espera por POST:
 *   - IdOrigen    : int (Id del pago o de la venta MercadoPago)
 *   - TipoOrigen  : 'PAGO' | 'MP'
 *   - IdVenta     : int (Id de la venta en tabla Venta)
 *   - Monto       : decimal
 *   - FechaDeposito (Y-m-d)
 *   - HoraDeposito  (HH:MM) opcional
 *   - Banco         string
 *   - Referencia    string opcional
 *   - Notas         string opcional
 */
function cobros_registrar_deposito(mysqli $mysqli): void {
    cobros_check_csrf();

    $IdOrigen   = (int)($_POST['IdOrigen']   ?? 0);
    $TipoOrigen = strtoupper(trim((string)($_POST['TipoOrigen'] ?? '')));
    $IdVenta    = (int)($_POST['IdVenta']    ?? 0);
    $Monto      = (float)($_POST['Monto']    ?? 0);
    $FDep       = trim((string)($_POST['FechaDeposito'] ?? ''));
    $HDep       = trim((string)($_POST['HoraDeposito']  ?? ''));
    $Banco      = trim((string)($_POST['Banco']         ?? ''));
    $Ref        = trim((string)($_POST['Referencia']    ?? ''));
    $Notas      = trim((string)($_POST['Notas']         ?? ''));

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

        // 2) Marcar como conciliado el origen si existe un campo status adecuado
        if ($TipoOrigen === 'PAGO') {
            // Ejemplo: actualizar Pagos.status = 'CONCILIADO'
            $sqlUp = "UPDATE Pagos SET status = 'CONCILIADO' WHERE Id = ? LIMIT 1";
        } else {
            // Mercado Pago: actualizar VentasMercadoPago.c_status_admin (campo sugerido)
            $sqlUp = "UPDATE VentasMercadoPago SET c_status_admin = 'CONCILIADO' WHERE id = ? LIMIT 1";
        }
        $st2 = $mysqli->prepare($sqlUp);
        $st2->bind_param('i', $IdOrigen);
        $st2->execute();
        $st2->close();

        // 3) Evento
        cobros_registrar_evento($mysqli, 'Registrar_Deposito', [
            'Host'    => $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? ''),
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
 *
 * Espera por POST:
 *   - IdVenta
 *   - Destino (correo)
 *   - Mensaje
 */
function cobros_recordatorio_correo(mysqli $mysqli): void {
    cobros_check_csrf();

    $IdVenta = (int)($_POST['IdVenta'] ?? 0);
    $destino = trim((string)($_POST['Destino'] ?? ''));
    $mensaje = trim((string)($_POST['Mensaje'] ?? ''));

    if ($IdVenta <= 0 || $destino === '' || $mensaje === '') {
        cobros_redirect('Datos incompletos para recordatorio por correo.');
    }

    $Usuario = (string)($_SESSION['Vendedor'] ?? '');

    $mysqli->begin_transaction();
    try {
        // 1) Registrar en RecordatoriosCobro
        $sql = "INSERT INTO RecordatoriosCobro
                    (IdVenta, Medio, Destino, Mensaje, Usuario)
                VALUES (?, 'EMAIL', ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isss', $IdVenta, $destino, $mensaje, $Usuario);
        $stmt->execute();
        $stmt->close();

        // 2) Evento
        cobros_registrar_evento($mysqli, 'Recordatorio_Correo', [
            'Host'    => $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? ''),
            'IdVenta' => $IdVenta,
            'IdUsr'   => $Usuario,
        ]);

        $mysqli->commit();

        // 3) Opcional: envío inmediato vía mail() o integrando EnviarCorreo.php
        //    Aquí queda sólo como ejemplo simple. Puedes sustituirlo por tu propio sistema.
        @mail(
            $destino,
            'Recordatorio de pago KASU',
            $mensaje,
            "From: notificaciones@kasu.com.mx\r\nContent-Type: text/plain; charset=UTF-8"
        );

        cobros_redirect('Recordatorio enviado/registrado correctamente.');

    } catch (\Throwable $e) {
        $mysqli->rollback();
        error_log('[KASU][Cobros] Error recordatorio_correo: '.$e->getMessage());
        cobros_redirect('Error al registrar el recordatorio de correo.');
    }
}

/**
 * Acción: recordatorio_sms
 *
 * Espera por POST:
 *   - IdVenta
 *   - Destino (teléfono)
 *   - Mensaje
 *
 * Aquí sólo se registra en RecordatoriosCobro.
 * Un proceso externo (cron) debería leer estos registros y enviar el SMS con tu proveedor.
 */
function cobros_recordatorio_sms(mysqli $mysqli): void {
    cobros_check_csrf();

    $IdVenta = (int)($_POST['IdVenta'] ?? 0);
    $destino = trim((string)($_POST['Destino'] ?? ''));
    $mensaje = trim((string)($_POST['Mensaje'] ?? ''));

    if ($IdVenta <= 0 || $destino === '' || $mensaje === '') {
        cobros_redirect('Datos incompletos para recordatorio por SMS.');
    }

    $Usuario = (string)($_SESSION['Vendedor'] ?? '');

    $mysqli->begin_transaction();
    try {
        $sql = "INSERT INTO RecordatoriosCobro
                    (IdVenta, Medio, Destino, Mensaje, Usuario)
                VALUES (?, 'SMS', ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('isss', $IdVenta, $destino, $mensaje, $Usuario);
        $stmt->execute();
        $stmt->close();

        cobros_registrar_evento($mysqli, 'Recordatorio_SMS', [
            'Host'    => $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? ''),
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

/* ============================================================================
 * ROUTER PRINCIPAL
 * ========================================================================== */

$accion = $_POST['accion_cobro'] ?? '';

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

    default:
        cobros_redirect('Acción de cobros no reconocida.');
}
