<?php
/**
 * Archivo: Funcionalidad_API_KASU.php
 * Qué hace: Endpoint unificado para acciones de la PWA/API de KASU:
 *  - Actualizar datos de cliente
 *  - Registrar ticket de atención
 *  - Registrar servicio funerario
 *  - Registrar pago y promesas de pago
 *  - Actualizar datos de prospecto
 *  - Actualizar foto de perfil de empleado
 * Compatibilidad: PHP 8.2
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 */

declare(strict_types=1);

// =================== Sesión y dependencias ===================
require_once dirname(__DIR__, 2) . '/eia/session.php';
kasu_session_start();

if (!isset($_SESSION["Vendedor"])) {
    header('Location: https://kasu.com.mx/login/', true, 303);
    exit();
}

require_once '../../eia/librerias.php';
kasu_apply_error_settings(); // 2025-11-18: Centraliza errores en /eia/error.log accesible vía HTTPS

date_default_timezone_set('America/Mexico_City');
$hoy        = date('Y-m-d');
$HoraActual = date('H:i:s');

// ======================================================================
// =============== BLOQUE: ACTUALIZAR DATOS DE UN CLIENTE ===============
// === Qué hace: crea Contacto, vincula a Venta y Usuario. ==============
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (!empty($_POST['ActDatosCTE'])) {
    $calle    = isset($_POST['calle'])    ? $mysqli->real_escape_string((string)$_POST['calle'])    : '';
    $Email    = isset($_POST['Email'])    ? $mysqli->real_escape_string((string)$_POST['Email'])    : '';
    $Telefono = isset($_POST['Telefono']) ? $mysqli->real_escape_string((string)$_POST['Telefono']) : '';
    $Host     = isset($_POST['Host'])     ? $mysqli->real_escape_string((string)$_POST['Host'])     : '';
    $Producto = isset($_POST['Producto']) ? $mysqli->real_escape_string((string)$_POST['Producto']) : '';

    // Auditoría: fingerprint, GPS y evento
    $ids = $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'Cambio_Contacto',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    // Inserta Contacto
    $NvoRegistroarray = [
        "Usuario"   => $_SESSION["Vendedor"],
        "Host"      => $Host,
        "Mail"      => $Email,
        "Telefono"  => $Telefono,
        "calle"     => $calle,
        "Idgps"     => $ids['gps_id'] ?? null,
        "Producto"  => $Producto
    ];
    $NvoRegistro = $basicas->InsertCampo($mysqli, "Contacto", $NvoRegistroarray);

    // Enlaza Contacto con Venta y Usuario
    $basicas->ActCampo($mysqli, "Venta",   "IdContact", $NvoRegistro, (int)$_POST['IdVenta']);
    $basicas->ActCampo($mysqli, "Usuario", "IdContact", $NvoRegistro, (int)$_POST['IdUsuario']);

    $Msg = "Se han actualizado los datos del cliente";

    header('Location: https://kasu.com.mx' . ($_POST['Host'] ?? '/login/Mesa_Herramientas.php') . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode((string)($_POST['nombre'] ?? '')), true, 303);
    exit();
}

// ======================================================================
// ========= BLOQUE: REGISTRAR UN TICKET DE ATENCIÓN AL CLIENTE =========
// === Qué hace: guarda ticket con prioridad, estado y teléfono. ========
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (!empty($_POST['AltaTicket'])) {
    $Producto    = isset($_POST['Producto'])   ? $mysqli->real_escape_string((string)$_POST['Producto'])   : '';
    $Status      = isset($_POST['Status'])     ? $mysqli->real_escape_string((string)$_POST['Status'])     : '';
    $Prioridad   = isset($_POST['Prioridad'])  ? $mysqli->real_escape_string((string)$_POST['Prioridad'])  : '';
    $Descripcion = isset($_POST['Descripcion'])? $mysqli->real_escape_string((string)$_POST['Descripcion']) : '';
    $Telefono    = isset($_POST['Telefono'])   ? $mysqli->real_escape_string((string)$_POST['Telefono'])   : '';

    $ids = $seguridad->auditoria_registrar(
        $mysqli, $basicas, $_POST, 'Ticket_Atencion', $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    $NvoRegistroarray = [
        "IdVta"         => (int)$_POST["IdVenta"],
        "IdUsr"         => (int)$_POST["IdUsuario"],
        "IdContacto"    => (int)$_POST["IdContact"],
        "Ticket"        => $Descripcion,
        "Vendedor"      => $_SESSION["Vendedor"],
        "Host"          => (string)($_POST["Host"] ?? ''),
        "Prioridad"     => $Prioridad,
        "Status"        => $Status,
        "Telefono"      => $Telefono
    ];
    $basicas->InsertCampo($mysqli, "Atn_Cliente", $NvoRegistroarray);

    $Msg = "Se ha registrado correctamente el Ticket";

    header('Location: https://kasu.com.mx' . ($_POST['Host'] ?? '/login/Mesa_Herramientas.php') . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode((string)($_POST['nombre'] ?? '')), true, 303);
    exit();
}

// ======================================================================
// ============== BLOQUE: REGISTRAR SERVICIO FUNERARIO ==================
// === Qué hace: registra el servicio y marca la venta como FALLECIDO ===
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (!empty($_POST['RegisFun'])) {
    $Prestador    = isset($_POST['Prestador'])    ? $mysqli->real_escape_string((string)$_POST['Prestador'])    : '';
    $RFC          = isset($_POST['RFC'])          ? $mysqli->real_escape_string((string)$_POST['RFC'])          : '';
    $CodigoPostal = isset($_POST['CodigoPostal']) ? $mysqli->real_escape_string((string)$_POST['CodigoPostal']) : '';
    $Firma        = isset($_POST['Firma'])        ? $mysqli->real_escape_string((string)$_POST['Firma'])        : '';
    $Costo        = isset($_POST['Costo'])        ? $mysqli->real_escape_string((string)$_POST['Costo'])        : '';
    $EmpFune      = isset($_POST['EmpFune'])      ? $mysqli->real_escape_string((string)$_POST['EmpFune'])      : '';

    $ids = $seguridad->auditoria_registrar(
        $mysqli, $basicas, $_POST, 'Servicio_Funerario', $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    $NombreCte = $basicas->BuscarCampos($mysqli, 'Nombre', 'Venta', 'Id', (int)$_POST['IdVenta']);

    $NvoRegistroarray = [
        "Usuario"      => $_SESSION["Vendedor"],
        "IdVenta"      => (int)$_POST['IdVenta'],
        "Nombre"       => (string)$NombreCte,
        "Prestador"    => $Prestador,
        "CodigoPostal" => $CodigoPostal,
        "CFDI"         => $Firma,
        "Costo"        => $Costo,
        "EmpFune"      => $EmpFune,
        // Si tu tabla tiene RFC, descomenta:
        // "RFC"       => $RFC,
    ];
    $basicas->InsertCampo($mysqli, "EntregaServicio", $NvoRegistroarray);

    $basicas->ActCampo($mysqli, "Venta", "Status", "FALLECIDO", (int)$_POST['IdVenta']);

    $Msg = "Se ha registrado correctamente el SERVICIO";

    header('Location: https://kasu.com.mx' . ($_POST['Host'] ?? '/login/Mesa_Herramientas.php') . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode((string)$_POST['nombre']), true, 303);
    exit();
}

// ======================================================================
// =================== BLOQUE: REGISTRAR PAGO DE CLIENTE =================
// === Qué hace: aplica mora primero y luego pago normal. ================
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (isset($_POST['Pago'])) {
    $IdVenta   = isset($_POST['IdVenta'])   ? (int)$_POST['IdVenta'] : 0;
    $Metodo    = isset($_POST['Metodo'])    ? $mysqli->real_escape_string((string)$_POST['Metodo']) : '';
    $status    = isset($_POST['Status'])    ? (string)$_POST['Status'] : '';
    $PromesPga = isset($_POST['PromesPga']) ? $mysqli->real_escape_string((string)$_POST['PromesPga']) : null;
    $Promesa   = isset($_POST['Promesa'])   ? $mysqli->real_escape_string((string)$_POST['Promesa'])   : null;

    $PagoProm  = isset($_POST['PagoProm'])  ? (float)$_POST['PagoProm']  : 0.0; // cuota sin mora
    $PagoMora  = isset($_POST['PagoMora'])  ? (float)$_POST['PagoMora']  : 0.0; // cuota con mora
    $Cantidad  = isset($_POST['Cantidad'])  ? (float)$_POST['Cantidad']  : 0.0; // pagado por el cliente

    $hostRaw = (string)($_POST['Host'] ?? '');
    $host    = parse_url($hostRaw, PHP_URL_PATH) ?? '/login/Mesa_Herramientas.php';
    $host    = $host !== '' ? $host : '/login/Mesa_Herramientas.php';
    $host    = preg_replace('/[\r\n]/', '', $host);

    $nombre  = (string)($_POST['nombre'] ?? '');

    $ids = $seguridad->auditoria_registrar(
        $mysqli, $basicas, $_POST, 'Pago_Servicio', $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    // Mora teórica
    $moraTeorica = max(0.0, round($PagoMora - $PagoProm, 2));

    // Primero abona a mora si aplica
    $aplicaMora = 0.00;
    if (strcasecmp($status, 'Mora') === 0 && $moraTeorica > 0) {
        $aplicaMora = min($Cantidad, $moraTeorica);
        if ($aplicaMora > 0) {
            $basicas->InsertCampo($mysqli, "Pagos", [
                "IdVenta"       => $IdVenta,
                "Usuario"       => $_SESSION["Vendedor"],
                "Idgps"         => $ids['gps_id'] ?? null,
                "Cantidad"      => $aplicaMora,
                "Metodo"        => $Metodo,
                "status"        => "Mora",
                "FechaRegistro" => $hoy . " " . $HoraActual
            ]);
        }
    }

    // Luego al pago normal
    $importePago = round($Cantidad - $aplicaMora, 2);
    if ($importePago > 0) {
        $basicas->InsertCampo($mysqli, "Pagos", [
            "IdVenta"       => $IdVenta,
            "Usuario"       => $_SESSION["Vendedor"],
            "Idgps"         => $ids['gps_id'] ?? null,
            "Cantidad"      => $importePago,
            "Metodo"        => $Metodo,
            "status"        => "Pago",
            "FechaRegistro" => $hoy . " " . $HoraActual
        ]);
    }

    // Guarda promesa si viene
    if (!empty($Promesa)) {
        $Vendedor = $basicas->BuscarCampos($mysqli, 'Usuario', 'Venta', 'Id', $IdVenta);
        $basicas->InsertCampo($mysqli, "PromesaPago", [
            "IdVenta"       => $IdVenta,
            "Cantidad"      => $PromesPga,
            "Promesa"       => $Promesa,
            "Usuario"       => $_SESSION["Vendedor"],
            "Vendedor"      => (string)$Vendedor,
            "FechaRegistro" => $hoy . " " . $HoraActual
        ]);
    }

    // Si viene de PWA promesa, actualiza acumulado pagado
    if ($host === "/login/Pwa_Registro_Pagos.php") {
        $idProm = (int)($_POST['Referencia'] ?? 0);
        if ($idProm > 0 && $importePago > 0) {
            $acum = (float)$basicas->BuscarCampos($mysqli, "Pagado", "PromesaPago", "Id", $idProm);
            $basicas->ActCampo($mysqli, "PromesaPago", "Pagado", $acum + $importePago, $idProm);
        }
    }

    /**
     * Cambios de status conforme a pagos
     * CANCELADO o PREVENTA -> COBRANZA
     * COBRANZA o PREVENTA con saldo <= 0 -> ACTIVACION + correo
     */

    // 1) Datos base
    $saldoPendiente = ($IdVenta && $financieras)
    ? (float)$financieras->SaldoCredito($mysqli, $IdVenta)
    : 0.0;

    $StatusVta = (string)$basicas->BuscarCampos($mysqli, 'Status', 'Venta', 'Id', $IdVenta);

    // Debug claro
    echo "<br>Imprimimos el saldo pendiente => {$saldoPendiente}";
    echo "<br>Imprimimos el Id de la Venta => {$IdVenta}";
    echo "<br>Imprimimos el Status de la Venta => {$StatusVta}";

    // Normaliza a centavos para comparar con precisión
    $saldoCents = (int)round($saldoPendiente * 100);

    // 2) Caso A: liquidado y estado en {COBRANZA, PREVENTA} => ACTIVACION + correo
    if ($saldoCents <= 0 && in_array($StatusVta, ['COBRANZA', 'PREVENTA'], true)) {

        // Auditoría
        $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'Correo_Liquidacion_Poliza',
            $HostPost ?? $_SERVER['PHP_SELF']
        );

        // Cambia a ACTIVACION
        $basicas->ActCampo($mysqli, "Venta", "Status", 'ACTIVACION', $IdVenta);

        // Token one-shot para envío de correo
        $_SESSION['mail_token'] = bin2hex(random_bytes(32));
        echo "<br>Imprimimos el Token correo de la Venta => " . $_SESSION['mail_token'];

        // Parámetros para EnviarCorreo.php
        $base   = 'https://kasu.com.mx/eia/EnviarCorreo.php';
        $next   = 'https://kasu.com.mx' . ($host ?? '/login/Mesa_Herramientas.php');
        $params = [
            'Vta_Liquidada' => (int)$IdVenta,
            'mail_token'    => $_SESSION['mail_token'],
            'Redireccion'   => $next,
            'Msg'           => 'Liquidacion de poliza y envio de correo exitoso',
        ];

        // Redirección (descomenta si quieres ejecutar el envío inmediato)
         $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
         header('Location: ' . $base . '?' . $query, true, 303);
         exit;

    }
    // 3) Caso B: si no está liquidado, pero el estado es CANCELADO o PREVENTA => COBRANZA
    elseif (in_array($StatusVta, ['CANCELADO', 'PREVENTA'], true)) {

        $basicas->ActCampo($mysqli, "Venta", "Status", 'COBRANZA', $IdVenta);
        echo "<br>Cambio de status => COBRANZA";
    }

    //Redireccionamos al origen de la peticion
    $Msg = "Pago registrado correctamente";
    header('Location: https://kasu.com.mx' . $host . '?Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode($nombre), true, 303);
    exit();
}

// ======================================================================
// ============== BLOQUE: REGISTRAR PROMESA DE PAGO =====================
// === Qué hace: valida mínimo y registra promesa. ======================
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (isset($_POST['PromPago'])) {
    $IdVenta       = (int)($_POST['IdVenta'] ?? 0);

    // La fecha venía como FechaPromesa en el form
    $FechaPromesa  = $_POST['FechaPromesa'] ?? ($_POST['Promesa'] ?? null);
    $FechaPromesa  = $FechaPromesa ? $mysqli->real_escape_string((string)$FechaPromesa) : null;

    // Normaliza decimales y compara en centavos para evitar errores de punto flotante
    $Cantidad      = isset($_POST['Cantidad'])   ? (float)str_replace([',',' '], ['.',''], (string)$_POST['Cantidad'])   : 0.0;
    $PagoMinimo    = isset($_POST['PagoMinimo']) ? (float)str_replace([',',' '], ['.',''], (string)$_POST['PagoMinimo']) : 0.0;
    $ok = (int)round($Cantidad * 100) >= (int)round($PagoMinimo * 100); // permitir >=

    if ($ok) {
        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Promesa_Pago', $_POST['Host'] ?? $_SERVER['PHP_SELF']);
        $Vendedor = $basicas->BuscarCampos($mysqli, 'Usuario', 'Venta', 'Id', $IdVenta);

        $basicas->InsertCampo($mysqli, "PromesaPago", [
            "IdVenta"       => $IdVenta,
            "Cantidad"      => $Cantidad,
            "Promesa"       => $FechaPromesa,       // guarda la fecha
            "Vendedor"      => (string)$Vendedor,
            "Usuario"       => $_SESSION["Vendedor"],
            "FechaRegistro" => $hoy . " " . $HoraActual
        ]);
        $Msg = "Promesa de pago registrada correctamente";
    } else {
        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Promesa_No_Registrada', $_POST['Host'] ?? $_SERVER['PHP_SELF']);
        $Msg = "No se puede registrar una promesa menor al pago mínimo";
    }

    header('Location: https://kasu.com.mx' . ($_POST['Host'] ?? '/login/Mesa_Herramientas.php')
         . '?Vt=1&Msg=' . rawurlencode($Msg)
         . '&nombre=' . rawurlencode((string)($_POST['nombre'] ?? '')), true, 303);
    exit();
}

// ======================================================================
// ============ BLOQUE: REGISTRAR PROCESO LEAD SALES (EMAIL) ============
// === Qué hace: guarda la frecuencia y programa el primer envio. =======
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (!empty($_POST['LeadSales'])) {
    if (!isset($pros) || !($pros instanceof mysqli)) {
        http_response_code(500);
        exit('BD prospectos no disponible.');
    }

    $idProspecto = isset($_POST['IdProspecto']) ? (int)$_POST['IdProspecto'] : 0;
    $freqRaw = strtoupper(trim((string)($_POST['Frecuencia'] ?? '')));
    $nombre  = trim((string)($_POST['nombre'] ?? ''));

    $hostInput = (string)($_POST['Host'] ?? '/login/Mesa_Prospectos.php');
    $hostPath  = parse_url($hostInput, PHP_URL_PATH) ?: '/login/Mesa_Prospectos.php';

    $validFreq = ['DIARIO', 'SEMANAL', 'QUINCENAL', 'MENSUAL'];
    if ($idProspecto <= 0) {
        $Msg = 'Prospecto invalido.';
        header('Location: https://kasu.com.mx' . $hostPath . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode($nombre), true, 303);
        exit();
    }
    if (!in_array($freqRaw, $validFreq, true)) {
        $Msg = 'Selecciona una frecuencia valida.';
        header('Location: https://kasu.com.mx' . $hostPath . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode($nombre), true, 303);
        exit();
    }

    $seguridad->auditoria_registrar(
        $mysqli, $basicas, $_POST, 'LeadSales_Email', $hostPath
    );

    $fechaRegistro = $hoy . ' ' . $HoraActual;
    $fechaProxima  = $fechaRegistro; // se enviara en el siguiente cron
    $IdVendedor    = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);

    $fuente     = 'Mesa_Prospectos';
    $tipoNota   = 'EMAIL_AUTO';
    $titulo     = 'Seguimiento automatico';
    $comentario = 'Frecuencia: ' . $freqRaw;

    $stmt = $pros->prepare("SELECT Id FROM Prospectos_Seguimiento_IA WHERE IdProspecto = ? AND TipoNota = 'EMAIL_AUTO' ORDER BY Id DESC LIMIT 1");
    if (!$stmt) {
        error_log('LeadSales: prepare select failed: ' . $pros->error);
        $Msg = 'Error al registrar el seguimiento.';
        header('Location: https://kasu.com.mx' . $hostPath . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode($nombre), true, 303);
        exit();
    }
    $stmt->bind_param('i', $idProspecto);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if ($row && isset($row['Id'])) {
        $idReg = (int)$row['Id'];
        $stmtUp = $pros->prepare(
            "UPDATE Prospectos_Seguimiento_IA
             SET IdVendedor = ?, Fuente = ?, Titulo = ?, Comentario = ?, ProximaAccion = ?, FechaProxima = ?
             WHERE Id = ?"
        );
        if ($stmtUp) {
            $stmtUp->bind_param('isssssi', $IdVendedor, $fuente, $titulo, $comentario, $freqRaw, $fechaProxima, $idReg);
            $stmtUp->execute();
            $stmtUp->close();
        }
        $Msg = 'Seguimiento actualizado.';
    } else {
        $relCorreoId = 0;
        $stmtIn = $pros->prepare(
            "INSERT INTO Prospectos_Seguimiento_IA
             (IdProspecto, IdVendedor, Fuente, TipoNota, Titulo, Comentario, ProximaAccion, FechaProxima, RelacionadoCorreoId, FechaRegistro)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        );
        if ($stmtIn) {
            $stmtIn->bind_param(
                'iissssssis',
                $idProspecto,
                $IdVendedor,
                $fuente,
                $tipoNota,
                $titulo,
                $comentario,
                $freqRaw,
                $fechaProxima,
                $relCorreoId,
                $fechaRegistro
            );
            $stmtIn->execute();
            $stmtIn->close();
        }
        $Msg = 'Seguimiento registrado.';
    }

    header('Location: https://kasu.com.mx' . $hostPath . '?Vt=1&Msg=' . rawurlencode($Msg) . '&nombre=' . rawurlencode($nombre), true, 303);
    exit();
}

// ======================================================================
// ============== BLOQUE: ACTUALIZAR DATOS DE UN PROSPECTO ==============
// === Qué hace: actualiza teléfono, email, dirección y servicio. =======
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (!empty($_POST['ActDatosPROS'])) {
    $idProspecto = isset($_POST['IdProspecto']) ? (int)$_POST['IdProspecto'] : 0;
    if ($idProspecto <= 0) {
        http_response_code(400);
        exit('IdProspecto inválido.');
    }

    $CurpRaw          = isset($_POST['CURP']) ? trim((string)$_POST['CURP']) : '';
    $Telefono         = isset($_POST['Telefono']) ? trim((string)$_POST['Telefono']) : '';
    $EmailRaw         = isset($_POST['Email']) ? trim((string)$_POST['Email']) : '';
    $Email            = $EmailRaw !== '' ? ((filter_var($EmailRaw, FILTER_VALIDATE_EMAIL) ?: '')) : '';
    $calle            = isset($_POST['calle']) ? trim((string)$_POST['calle']) : '';
    $Servicio_Interes = isset($_POST['Servicio_Interes']) ? trim((string)$_POST['Servicio_Interes']) : '';
    $nombre           = isset($_POST['nombre']) ? trim((string)$_POST['nombre']) : '';

    $Telefono         = $mysqli->real_escape_string($Telefono);
    $Email            = $mysqli->real_escape_string($Email);
    $calle            = $mysqli->real_escape_string($calle);
    $Servicio_Interes = $mysqli->real_escape_string($Servicio_Interes);

    $hostInput = $_POST['Host'] ?? $_SERVER['PHP_SELF'];
    $hostPath  = parse_url((string)$hostInput, PHP_URL_PATH) ?: '/';

    $seguridad->auditoria_registrar(
        $mysqli, $basicas, $_POST, 'Cambio_Datos_prospecto', $hostPath
    );

    // Usa la conexión $pros para la tabla prospectos
    $stmt = $pros->prepare("SELECT NoTel, Email, Direccion, Servicio_Interes, Curp, FullName, FechaNac FROM prospectos WHERE Id = ?");
    if (!$stmt) {
        error_log('Prepare failed: ' . $pros->error);
        http_response_code(500);
        exit('Error SQL.');
    }
    $stmt->bind_param('i', $idProspecto);
    $stmt->execute();
    $res = $stmt->get_result();
    $Reg = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$Reg) {
        http_response_code(404);
        exit('Prospecto no encontrado.');
    }

    $actualizados = 0;
    $msgCurp = '';

    if ($CurpRaw !== '') {
        $CurpUp = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $CurpRaw));
        $reCurp = '/^[A-Z]{4}\d{6}[HM](AS|BC|BS|CC|CS|CH|CL|CM|CO|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[B-DF-HJ-NP-TV-Z]{3}[A-Z0-9]\d$/';

        if (!empty($Reg['Curp'])) {
            if (strtoupper((string)$Reg['Curp']) !== $CurpUp) {
                $msgCurp = 'El prospecto ya tiene CURP registrada.';
            }
        } elseif (!preg_match($reCurp, $CurpUp)) {
            $msgCurp = 'CURP no válida.';
        } else {
            $curpDup = $basicas->BuscarCampos($pros, 'Id', 'prospectos', 'Curp', $CurpUp);
            if (!empty($curpDup) && (int)$curpDup !== $idProspecto) {
                $msgCurp = 'La CURP ya está registrada.';
            } else {
                $DatProsp = $seguridad->peticion_get($CurpUp);
                if (!is_array($DatProsp) || ($DatProsp['Response'] ?? 'Error') === 'Error') {
                    $msgCurp = (string)($DatProsp['Msg'] ?? 'CURP no válida.');
                } else {
                    $fullName = trim(($DatProsp['Nombre'] ?? '').' '.($DatProsp['Paterno'] ?? '').' '.($DatProsp['Materno'] ?? ''));
                    if ($fullName !== '' && ($Reg['FullName'] ?? '') !== $fullName) {
                        $basicas->ActCampo($pros, "prospectos", "FullName", $fullName, $idProspecto);
                        $actualizados++;
                    }
                    if (($Reg['Curp'] ?? '') !== $CurpUp) {
                        $basicas->ActCampo($pros, "prospectos", "Curp", $CurpUp, $idProspecto);
                        $actualizados++;
                    }
                    $fechaNac = $DatProsp['FechaNacimiento'] ?? '';
                    if ($fechaNac !== '' && ($Reg['FechaNac'] ?? '') !== $fechaNac) {
                        $basicas->ActCampo($pros, "prospectos", "FechaNac", $fechaNac, $idProspecto);
                        $actualizados++;
                    }
                    $msgCurp = 'CURP registrada correctamente.';
                }
            }
        }
    }

    if ($Telefono !== '' && $Reg['NoTel'] !== $Telefono) {
        $basicas->ActCampo($pros, "prospectos", "NoTel", $Telefono, $idProspecto);
        $actualizados++;
    }
    if ($Reg['Email'] !== $Email) {
        $basicas->ActCampo($pros, "prospectos", "Email", $Email, $idProspecto);
        $actualizados++;
    }
    if ($Reg['Direccion'] !== $calle) {
        $basicas->ActCampo($pros, "prospectos", "Direccion", $calle, $idProspecto);
        $actualizados++;
    }
    if ($Reg['Servicio_Interes'] !== $Servicio_Interes) {
        $basicas->ActCampo($pros, "prospectos", "Servicio_Interes", $Servicio_Interes, $idProspecto);
        $actualizados++;
    }

    $Msg = $actualizados > 0 ? "Se actualizaron {$actualizados} campo(s)." : "No hubo cambios.";
    if ($msgCurp !== '') {
        $Msg = trim($Msg . ' ' . $msgCurp);
    }

    $location = 'https://kasu.com.mx' . $hostPath
        . '?Vt=1'
        . '&Msg='    . rawurlencode($Msg)
        . '&nombre=' . rawurlencode($nombre);

    header('Location: ' . $location, true, 303);
    exit();
}

// ======================================================================
// ========= BLOQUE: ACTUALIZAR IMAGEN DE FOTO DE PERFIL (Empleado) =====
// === Qué hace: valida, reorienta, redimensiona y guarda JPG. ==========
// === Fecha: 05/11/2025 | Revisado por: JCCM ===========================
// ======================================================================
if (!empty($_POST['btnEnviar'])) {
    $redirect = function (string $msg, string $fallback = '/login/Mesa_Herramientas.php') {
        $ref  = (string)($_POST['Host'] ?? ($_SERVER['HTTP_REFERER'] ?? $fallback));
        $path = parse_url($ref, PHP_URL_PATH) ?: $fallback;
        $qs   = 'Msg=' . rawurlencode($msg);
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Location: https://kasu.com.mx' . $path . (strpos($path, '?') === false ? '?' : '&') . $qs, true, 303);
        exit();
    };

    $VendId = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
    if ($VendId <= 0) {
        $redirect('Empleado inválido.');
    }

    if (empty($_FILES['subirImg']) || !is_uploaded_file($_FILES['subirImg']['tmp_name'])) {
        $redirect('No se recibió archivo.');
    }
    $up = $_FILES['subirImg'];
    if (!empty($up['error'])) {
        $redirect('Error de carga: ' . (string)$up['error']);
    }

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = (string)finfo_file($finfo, $up['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed, true)) {
        $redirect('Formato no permitido. Usa JPG, PNG o WEBP.');
    }

    $fsDir = realpath(__DIR__ . '/../assets/img/perfil');
    if ($fsDir === false) {
        $redirect('Directorio de destino no existe.');
    }
    $destDir = rtrim($fsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    // Carga fuente según MIME
    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $src = @imagecreatefromjpeg($up['tmp_name']);
            break;
        case 'image/png':
            $src = @imagecreatefrompng($up['tmp_name']);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                $redirect('WEBP no soportado en el servidor.');
            }
            $src = @imagecreatefromwebp($up['tmp_name']);
            break;
        default:
            $src = null;
    }
    if (!$src) {
        $redirect('No se pudo procesar la imagen.');
    }

    // Orientación EXIF solo JPEG
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($up['tmp_name']);
        if (!empty($exif['Orientation'])) {
            switch ((int)$exif['Orientation']) {
                case 3: $src = imagerotate($src, 180, 0); break;
                case 6: $src = imagerotate($src, -90, 0); break;
                case 8: $src = imagerotate($src, 90, 0); break;
            }
        }
    }

    // Redimensionar
    $maxSide = 800;
    $w = imagesx($src);
    $h = imagesy($src);
    $scale = (max($w, $h) > $maxSide) ? ($maxSide / max($w, $h)) : 1.0;
    $newW = (int)round($w * $scale);
    $newH = (int)round($h * $scale);

    $dst = imagecreatetruecolor($newW, $newH);
    $white = imagecolorallocate($dst, 255, 255, 255);
    imagefill($dst, 0, 0, $white);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

    $ts        = date('Ymd_His');
    $fileHist  = $destDir . $VendId . '_' . $ts . '.jpg';
    $fileAlias = $destDir . $VendId . '.jpg';

    if (!@imagejpeg($dst, $fileHist, 82)) {
        @imagedestroy($src);
        @imagedestroy($dst);
        $redirect('No se pudo guardar la imagen (histórico).');
    }
    @chmod($fileHist, 0644);

    if (!@imagejpeg($dst, $fileAlias, 82)) {
        @imagedestroy($src);
        @imagedestroy($dst);
        $redirect('No se pudo guardar la imagen (alias).');
    }
    @chmod($fileAlias, 0644);

    @imagedestroy($src);
    @imagedestroy($dst);

    // Si requieres guardar el nombre en BD:
    // $basicas->ActCampo($mysqli, 'Empleados', 'Foto', basename($fileHist), $VendId);

    $_SESSION['FotoCacheBust'] = time();

    $redirect('Foto de perfil actualizada.');
}

/********************************************************************************************
 * Qué hace
 *   1) Inserta/actualiza metas para TODOS los NIVEL 6 (uno por persona) con la meta indicada.
 *   2) Consolida usando lo ya grabado en `Asignacion`: suma MVtas por `Equipo` y registra
 *      a los niveles 4, luego 3 y finalmente 1. (7 no participa).
 *   3) Ignora empleados con Equipo=0.
 *   4) Redirige al host de origen con ?Msg=... en base64.
 *
 * Requisito de índice (una sola vez):
 *   ALTER TABLE Asignacion ADD UNIQUE KEY uq_usuario_fecha (Usuario, Fecha);
 *
 * Modificado: 2025-11-10 — JCCM
 ********************************************************************************************/

// Helper: POST→GET con ?Msg=... en base64
function go_back_with_msg(string $url, string $msg): never {
    if ($url === '') $url = $_SERVER['HTTP_REFERER'] ?? $_SERVER['PHP_SELF'];
    $enc = base64_encode($msg);
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    $loc = $url . $sep . 'Msg=' . rawurlencode($enc);
    if (!headers_sent()) { header('Location: '.$loc, true, 303); exit; }
    $safe = htmlspecialchars($loc, ENT_QUOTES, 'UTF-8');
    echo "<script>location.href='{$safe}'</script><noscript><meta http-equiv='refresh' content='0;url={$safe}'></noscript>";
    exit;
}

if (!empty($_POST['Asignar'])) {
    /* ===== Auditoría ===== */
    $seguridad->auditoria_registrar(
        $mysqli, $basicas, $_POST, 'Registro_Metas', $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    /* ===== Entradas ===== */
    $metaNivel6   = max(0.0, (float)($_POST['MetaMes'] ?? 0)); // meta por persona de nivel 6
    $normalidad   = max(0, min(100, (int)($_POST['Normalidad'] ?? 0)));
    $fechaPeriodo = date('Y-m-01');
    $hostReturn   = (string)($_POST['Host'] ?? ($_SERVER['HTTP_REFERER'] ?? $_SERVER['PHP_SELF']));

    /* ===== Utilitarios ===== */
    // Carga empleados por nivel → [Id => ['u'=>IdUsuario, 'sup'=>Equipo]]
    $load_level = function(int $nivel) use ($mysqli): array {
        $out = [];
        $st = $mysqli->prepare("SELECT Id, IdUsuario, Equipo FROM Empleados WHERE Nivel = ?");
        $st->bind_param('i', $nivel);
        $st->execute();
        $rs = $st->get_result();
        while ($r = $rs->fetch_assoc()) $out[(int)$r['Id']] = ['u'=>(string)$r['IdUsuario'], 'sup'=>(int)$r['Equipo']];
        $st->close();
        return $out;
    };

    // Suma MVtas en Asignacion para un conjunto de IDs, agrupando por Equipo = IdEmpleado
    $sum_from_asignacion = function(array $idsMap, string $fecha) use ($mysqli): array {
        if (!$idsMap) return [];
        $ids = implode(',', array_map('intval', array_keys($idsMap)));
        $sql = "SELECT Equipo AS IdEmpleado, SUM(MVtas) AS Total
                FROM Asignacion
                WHERE Fecha = ? AND Equipo IN ($ids)
                GROUP BY Equipo";
        $st = $mysqli->prepare($sql);
        $st->bind_param('s', $fecha);
        $st->execute();
        $rs = $st->get_result();
        $out = [];
        while ($r = $rs->fetch_assoc()) $out[(int)$r['IdEmpleado']] = (float)$r['Total'];
        $st->close();
        return $out;
    };

    /* ===== UPSERT Asignacion ===== */
    $sqlUp = "INSERT INTO Asignacion
                (Usuario, Equipo, MVtas, MCob, Normalidad, Fecha, FechaRegistro)
              VALUES (?,?,?,?,?, ?, NOW())
              ON DUPLICATE KEY UPDATE
                Equipo=VALUES(Equipo),
                MVtas=VALUES(MVtas),
                MCob=VALUES(MCob),
                Normalidad=VALUES(Normalidad),
                FechaRegistro=NOW()";
    $ins = $mysqli->prepare($sqlUp);
    if (!$ins) go_back_with_msg($hostReturn, 'Error prepare: '.$mysqli->error);

    // Variables ligadas (bind una vez)
    $UsuarioVar = '';            // s
    $EquipoVar  = 0;             // i
    $MVtasVar   = 0.0;           // d
    $MCobVar    = 0.0;           // d
    $NormVar    = $normalidad;   // i
    $FechaVar   = $fechaPeriodo; // s
    $ins->bind_param('siddis', $UsuarioVar, $EquipoVar, $MVtasVar, $MCobVar, $NormVar, $FechaVar);

    /* ===== Transacción ===== */
    $mysqli->begin_transaction();
    try {
        /* 1) Nivel 6: asigna uno por persona, ignora Equipo=0 */
        $L6 = $load_level(6);
        foreach ($L6 as $id6 => $d6) {
            $sup = (int)$d6['sup'];
            if ($sup <= 0) continue; // ignora no asignados
            $UsuarioVar = $d6['u'];
            $EquipoVar  = $sup;                 // su superior es nivel 4
            $MVtasVar   = round($metaNivel6, 2);
            $MCobVar    = 0.0;
            if (!$ins->execute()) throw new Exception($ins->error);
        }

        /* 2) Nivel 4: suma lo ya insertado donde Equipo = Id del 4 */
        $L4   = $load_level(4);
        $tot4 = $sum_from_asignacion($L4, $fechaPeriodo);
        foreach ($tot4 as $id4 => $total) {
            if ($total <= 0 || !isset($L4[$id4])) continue;
            $UsuarioVar = $L4[$id4]['u'];
            $EquipoVar  = $L4[$id4]['sup'];     // su superior es nivel 3 (puede ser 0? normalmente >0)
            if ($EquipoVar <= 0) continue;      // ignora si no tiene superior
            $MVtasVar   = round($total, 2);
            $MCobVar    = 0.0;
            if (!$ins->execute()) throw new Exception($ins->error);
        }

        /* 3) Nivel 3: suma lo ya insertado donde Equipo = Id del 3 */
        $L3   = $load_level(3);
        $tot3 = $sum_from_asignacion($L3, $fechaPeriodo);
        foreach ($tot3 as $id3 => $total) {
            if ($total <= 0 || !isset($L3[$id3])) continue;
            $UsuarioVar = $L3[$id3]['u'];
            $EquipoVar  = $L3[$id3]['sup'];     // su superior es nivel 1
            if ($EquipoVar <= 0) continue;
            $MVtasVar   = round($total, 2);
            $MCobVar    = 0.0;
            if (!$ins->execute()) throw new Exception($ins->error);
        }

        /* 4) Nivel 1: suma lo ya insertado donde Equipo = Id del 1 */
        $L1   = $load_level(1);
        $tot1 = $sum_from_asignacion($L1, $fechaPeriodo);
        foreach ($tot1 as $id1 => $total) {
            if ($total <= 0 || !isset($L1[$id1])) continue;
            $UsuarioVar = $L1[$id1]['u'];
            $EquipoVar  = $L1[$id1]['sup'];     // puede ser 0
            $MVtasVar   = round($total, 2);
            $MCobVar    = 0.0;
            if (!$ins->execute()) throw new Exception($ins->error);
        }

        $ins->close();
        $mysqli->commit();

        go_back_with_msg($hostReturn, 'Asignacion realizada correctamente');
    } catch (Throwable $e) {
        $mysqli->rollback();
        go_back_with_msg($hostReturn, 'Error en asignacion: '.$e->getMessage());
    }
}
