<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_tools_correo.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Helpers para que la IA ejecute envíos de correo transaccionales:
 *           - Cotización
 *           - Póliza
 *           - Fichas de pago
 *           - Estado de cuenta
 *           - Liga de pago Mercado Pago
 *
 * IMPORTANTE:
 *  - Usa la clase Correo ya existente SOLO para generar la plantilla HTML.
 *  - El envío se hace con PHPMailer y las mismas variables .env que EnviarCorreo.php.
 *  - No hay tokens ni redirecciones; solo devuelve arrays de resultado.
 *  - Todas las funciones devuelven un array ['ok'=>bool, ...] apto para
 *    regresarlo a OpenAI como resultado de tool.
 * ============================================================================
 */

require_once dirname(__DIR__) . '/librerias.php'; // $mysqli, $pros, $basicas, $seguridad, clase Correo

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/** Obtiene o crea instancia global de Correo (para generar plantillas HTML). */
function kasu_get_correo_instance(): Correo {
    global $Correo;
    if (!isset($Correo) || !($Correo instanceof Correo)) {
        $Correo = new Correo();
    }
    return $Correo;
}

/**
 * Enviar correo mediante PHPMailer usando la misma configuración .env
 * que EnviarCorreo.php, pero sin redirecciones ni tokens.
 */
function kasu_mailer_enviar(string $email, string $nombre, string $asunto, string $html): array {
    // Ruta raíz del proyecto donde están vendor/ y .env
    // ia_tools_correo.php está en /eia/Vista-360 → subimos 2 niveles
    $root = realpath(__DIR__ . '/../../') ?: dirname(__DIR__, 2);

    // Autoload de Composer
    $autoload = $root . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }

    // Cargar .env si existe
    if (class_exists(\Dotenv\Dotenv::class) && is_file($root . '/.env')) {
        \Dotenv\Dotenv::createUnsafeImmutable($root)->safeLoad();
    }

    // Helper tipo env()
    $env = static function (string $k, ?string $default = null): ?string {
        $v = $_ENV[$k] ?? $_SERVER[$k] ?? getenv($k);
        if ($v === false || $v === null || $v === '') {
            return $default;
        }
        return is_string($v) ? $v : $default;
    };

    $mail = new PHPMailer(true);

    try {
        // Config SMTP igual que EnviarCorreo.php
        $mail->isSMTP();
        $mail->Host          = $env('SMTP_HOST', 'smtp.hostinger.mx');
        $mail->SMTPAuth      = true;
        $mail->Username      = $env('SMTP_USER', '');
        $mail->Password      = $env('SMTP_PASS', '');
        $mail->SMTPSecure    = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port          = (int)$env('SMTP_PORT', '587');
        $mail->CharSet       = 'UTF-8';
        $mail->Timeout       = 15;
        $mail->SMTPKeepAlive = true;

        $fromEmail = $env('FROM_EMAIL', 'atncliente@kasu.com.mx');
        $fromName  = $env('FROM_NAME',  'KASU');
        $replyTo   = $env('REPLY_TO',   $fromEmail);
        $bounce    = $env('BOUNCE_EMAIL', '');

        $mail->setFrom($fromEmail, $fromName);
        $mail->addReplyTo($replyTo, $fromName);
        if ($bounce !== '') {
            $mail->Sender = $bounce;
        }

        $mail->addAddress($email, $nombre !== '' ? $nombre : $email);

        $mail->isHTML(true);
        $mail->Subject = $asunto !== '' ? $asunto : 'KASU';
        $mail->Body    = $html;
        $mail->AltBody = strip_tags($html);

        $mail->send();

        return [
            'ok'     => true,
            'error'  => null,
            'engine' => 'phpmailer_env',
        ];
    } catch (Exception $e) {
        error_log('[IA_TOOLS_MAIL] PHPMailer error: ' . $mail->ErrorInfo);
        return [
            'ok'     => false,
            'error'  => $mail->ErrorInfo ?: $e->getMessage(),
            'engine' => 'phpmailer_env',
        ];
    }
}

/**
 * Envío genérico:
 *  - genera HTML con Correo::Mensaje()
 *  - envía con kasu_mailer_enviar()
 *  - registra auditoría opcional
 */
function kasu_enviar_correo_simple(
    string $asunto,
    string $email,
    string $nombre,
    array $data,
    string $idRef,
    string $eventoAuditoria = ''
): array {
    global $seguridad, $mysqli, $basicas;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'ok'    => false,
            'error' => 'Correo destino inválido',
        ];
    }

    // Auditoría si se especifica evento
    if ($eventoAuditoria !== '' && isset($seguridad)) {
        try {
            $seguridad->auditoria_registrar(
                $mysqli,
                $basicas,
                ['IA_TOOL' => $eventoAuditoria, 'IdRef' => $idRef, 'Email' => $email],
                $eventoAuditoria,
                $_SERVER['PHP_SELF'] ?? 'IA_TOOL'
            );
        } catch (Throwable $e) {
            error_log('[IA_TOOLS] Auditoría fallida: ' . $e->getMessage());
        }
    }

    // Generar HTML usando plantilla oficial
    $correo = kasu_get_correo_instance();
    $html   = $correo->Mensaje($asunto, $data, $idRef);

    if (!is_string($html) || $html === '') {
        $html = '<p>Estimado(a) ' . htmlspecialchars($nombre ?: 'Cliente', ENT_QUOTES, 'UTF-8') . '.</p>';
    }

    // Enviar con PHPMailer/.env
    $envio = kasu_mailer_enviar($email, $nombre, $asunto, $html);

    return [
        'ok'      => $envio['ok'],
        'asunto'  => $asunto,
        'email'   => $email,
        'nombre'  => $nombre,
        'id_ref'  => $idRef,
        'data'    => $data,
        'mensaje' => $envio['ok']
            ? 'Correo enviado correctamente'
            : ('No se pudo enviar el correo' . ($envio['error'] ? (': ' . $envio['error']) : '')),
        'engine'  => $envio['engine'] ?? 'desconocido',
        'error'   => $envio['error'] ?? null,
    ];
}

/**
 * TOOL: Enviar póliza a un cliente por IdVenta.
 * Similar al flujo Vta_Liquidada / EnviarPoliza en tu script actual.
 */
function ia_tool_enviar_poliza(int $idVenta): array {
    global $mysqli, $basicas;

    if ($idVenta <= 0) {
        return ['ok' => false, 'error' => 'IdVenta inválido'];
    }

    $sql = "
        SELECT v.Id,
               v.IdContact,
               v.Nombre      AS cliente,
               c.Mail        AS email
        FROM Venta v
        LEFT JOIN Contacto c ON c.id = v.IdContact
        WHERE v.Id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'error' => 'No se pudo preparar la consulta de Venta'];
    }
    $stmt->bind_param('i', $idVenta);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    if (!$info) {
        return ['ok' => false, 'error' => 'Venta no encontrada'];
    }

    $idContact = (string)($info['IdContact'] ?? '');
    $nombre    = (string)($info['cliente'] ?? 'Cliente KASU');
    $email     = (string)($info['email'] ?? '');

    if ($email === '') {
        // Fallback: buscar en Usuario por IdContact
        $email = (string)$basicas->BuscarCampos($mysqli, 'Email', 'Usuario', 'IdContact', $idContact);
    }

    if ($email === '') {
        return ['ok' => false, 'error' => 'No se encontró correo para el cliente'];
    }

    $asunto = '¡BIENVENIDO A KASU!';
    $data   = [
        'Cte'    => $nombre,
        'DirUrl' => base64_encode($idContact),
    ];

    return kasu_enviar_correo_simple($asunto, $email, $nombre, $data, (string)$idVenta, 'IA_Envio_Poliza');
}

/**
 * TOOL: Enviar fichas de pago por IdVenta.
 * Basado en la rama EnviarFichas del script actual.
 */
function ia_tool_enviar_fichas_pago(int $idVenta): array {
    global $mysqli, $basicas;

    if ($idVenta <= 0) {
        return ['ok' => false, 'error' => 'IdVenta inválido'];
    }

    $sql = "
        SELECT v.Id,
               v.Nombre      AS cliente,
               c.Mail        AS email
        FROM Venta v
        LEFT JOIN Contacto c ON c.id = v.IdContact
        WHERE v.Id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'error' => 'No se pudo preparar la consulta de Venta'];
    }
    $stmt->bind_param('i', $idVenta);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    if (!$info) {
        return ['ok' => false, 'error' => 'Venta no encontrada'];
    }

    $nombre = (string)($info['cliente'] ?? 'Cliente KASU');
    $email  = (string)($info['email'] ?? '');

    if ($email === '') {
        // Fallback (por si acaso)
        $idContact = (string)$basicas->BuscarCampos($mysqli, 'IdContact', 'Venta', 'Id', $idVenta);
        $email     = (string)$basicas->BuscarCampos($mysqli, 'Mail', 'Contacto', 'Id', $idContact);
    }

    if ($email === '') {
        return ['ok' => false, 'error' => 'No se encontró correo para el cliente'];
    }

    $asunto = 'ENVIO DE FICHAS DE PAGO';
    $dirUrl = 'https://kasu.com.mx/login/Generar_PDF/Fichas_Pago_pdf.php?Cte=' . base64_encode((string)$idVenta);

    $data = [
        'Cte'    => $nombre,
        'DirUrl' => $dirUrl,
    ];

    return kasu_enviar_correo_simple($asunto, $email, $nombre, $data, (string)$idVenta, 'IA_Envio_Fichas');
}

/**
 * TOOL: Enviar estado de cuenta por IdVenta.
 * Basado en EnviarEdoCta del script actual.
 */
function ia_tool_enviar_estado_cuenta(int $idVenta): array {
    global $mysqli, $basicas;

    if ($idVenta <= 0) {
        return ['ok' => false, 'error' => 'IdVenta inválido'];
    }

    $sql = "
        SELECT v.Id,
               v.Nombre      AS cliente,
               c.Mail        AS email
        FROM Venta v
        LEFT JOIN Contacto c ON c.id = v.IdContact
        WHERE v.Id = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'error' => 'No se pudo preparar la consulta de Venta'];
    }
    $stmt->bind_param('i', $idVenta);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    if (!$info) {
        return ['ok' => false, 'error' => 'Venta no encontrada'];
    }

    $nombre = (string)($info['cliente'] ?? 'Cliente KASU');
    $email  = (string)($info['email'] ?? '');

    if ($email === '') {
        $idContact = (string)$basicas->BuscarCampos($mysqli, 'IdContact', 'Venta', 'Id', $idVenta);
        $email     = (string)$basicas->BuscarCampos($mysqli, 'Mail', 'Contacto', 'Id', $idContact);
    }

    if ($email === '') {
        return ['ok' => false, 'error' => 'No se encontró correo para el cliente'];
    }

    $asunto = 'ENVÍO DE ESTADO DE CUENTA';
    $dirUrl = 'https://kasu.com.mx/login/Generar_PDF/Estado_Cuenta_pdf.php?busqueda=' . base64_encode((string)$idVenta);

    $data = [
        'Cte'    => $nombre,
        'DirUrl' => $dirUrl,
    ];

    return kasu_enviar_correo_simple($asunto, $email, $nombre, $data, (string)$idVenta, 'IA_Envio_EdoCta');
}

/**
 * TOOL: Enviar liga de pago Mercado Pago por IdVenta.
 * Basado en la rama EnFi del script actual.
 */
function ia_tool_enviar_liga_pago(int $idVenta): array {
    global $mysqli;

    if ($idVenta <= 0) {
        return ['ok' => false, 'error' => 'IdVenta inválido'];
    }

    $sql = "
      SELECT
        v.Id,
        v.Nombre         AS cliente,
        v.IdFIrma        AS folio,
        c.Mail           AS email,
        vm.mp_init_point AS mp_init_point
      FROM Venta v
      LEFT JOIN Contacto c           ON c.id = v.IdContact
      LEFT JOIN VentasMercadoPago vm ON vm.folio = v.IdFIrma
      WHERE v.Id = ?
      LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'error' => 'No se pudo preparar la consulta de Venta'];
    }
    $stmt->bind_param('i', $idVenta);
    $stmt->execute();
    $info = $stmt->get_result()->fetch_assoc() ?: [];
    $stmt->close();

    if (!$info) {
        return ['ok' => false, 'error' => 'Venta no encontrada'];
    }

    $nombre = (string)($info['cliente'] ?? 'Cliente KASU');
    $email  = (string)($info['email'] ?? '');
    $folio  = trim((string)($info['folio'] ?? ''));
    $dirUrl = trim((string)($info['mp_init_point'] ?? ''));

    if ($dirUrl === '' && $folio !== '') {
        // Fallback igual que tu script: genera preferencia si no hay init_point guardado
        $dirUrl = 'https://kasu.com.mx/pago/crear_preferencia.php?ref=' . rawurlencode($folio);
    }

    if ($dirUrl === '') {
        return ['ok' => false, 'error' => 'No se encontró liga de pago para esta venta'];
    }

    if ($email === '') {
        return ['ok' => false, 'error' => 'No se encontró correo para el cliente'];
    }

    $asunto = 'PAGO PENDIENTE';
    $data   = [
        'Cte'    => $nombre,
        'DirUrl' => $dirUrl,
    ];

    return kasu_enviar_correo_simple($asunto, $email, $nombre, $data, (string)$idVenta, 'IA_Envio_Liga_MP');
}

/**
 * TOOL: Enviar cotización (PrespEnviado) por Id de PrespEnviado.
 * Equivalente a EnCoti, pero la IA pasa el Id numérico directamente.
 */
function ia_tool_enviar_cotizacion(int $idPrespEnviado): array {
    global $pros, $basicas;

    if ($idPrespEnviado <= 0) {
        return ['ok' => false, 'error' => 'Id de cotización inválido'];
    }

    // IdProspecto desde PrespEnviado
    $idProspecto = (int)$basicas->BuscarCampos($pros, 'IdProspecto', 'PrespEnviado', 'Id', $idPrespEnviado);
    if ($idProspecto <= 0) {
        return ['ok' => false, 'error' => 'No se encontró el prospecto para esta cotización'];
    }

    $nombre = (string)$basicas->BuscarCampos($pros, 'FullName', 'prospectos', 'Id', $idProspecto);
    $email  = (string)$basicas->BuscarCampos($pros, 'Email',    'prospectos', 'Id', $idProspecto);

    if ($email === '') {
        return ['ok' => false, 'error' => 'No se encontró correo para el prospecto'];
    }

    $asunto = 'ENVÍO DE COTIZACIÓN';

    // En tu script, la liga apunta a un PDF con parámetros similares
    $enc   = base64_encode((string)$idPrespEnviado);
    $dirUrl = 'https://kasu.com.mx/login/Generar_PDF/Cotizacion_pdf.php'
            . '?busqueda=' . rawurlencode($enc)
            . '&idp='      . urlencode((string)$idProspecto);

    $data = [
        'Nombre' => $nombre,
        'DirUrl' => $dirUrl,
    ];

    return kasu_enviar_correo_simple($asunto, $email, $nombre, $data, (string)$idPrespEnviado, 'IA_Envio_Cotizacion');
}
