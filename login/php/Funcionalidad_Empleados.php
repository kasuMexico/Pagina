<?php
/********************************************************************************************
 * Qué hace: Controlador de autenticación y gestión de empleados para KASU:
 *  - Login y cambio/registro de contraseña
 *  - Pagos de comisiones
 *  - Cambio de vendedor (equipo/sucursal)
 *  - Baja de empleado
 *  - Alta de empleado (desde prospectos o directo)
 *  - Actualización de datos de contacto
 *  - Reenvío de enlace para registro de contraseña
 *  - Registro de problemas
 * Fecha: 05/11/2025
 * Revisado por: JCCM
 ********************************************************************************************/

declare(strict_types=1);

/* ==========================================================================================
 * BLOQUE: Depuración y sesión
 * Qué hace: Activa errores en entorno de desarrollo e inicia la sesión
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

/* ==========================================================================================
 * BLOQUE: Dependencias y configuración
 * Qué hace: Carga librerías, configura zona horaria y variables de tiempo
 * Fecha: 05/11/2025 — Revisado por: JCCM
 * ========================================================================================== */
require_once '../../eia/librerias.php';
date_default_timezone_set('America/Mexico_City');

$hoy        = date('Y-m-d');
$HoraActual = date('H:i:s');

/**************************************** BLOQUE: LOGIN Revisado 05/11/2025 JCCM ****************************************/
/* Qué hace: Autentica al usuario contra Empleados.Pass (sha256) y registra auditoría */
if (!empty($_POST['Login'])) {
    ini_set('log_errors', '1');
    $logFile = defined('KASU_ERROR_LOG_FILE')
        ? KASU_ERROR_LOG_FILE
        : dirname(__DIR__, 2) . '/eia/error.log';
    ini_set('error_log', $logFile);
    // CSRF (obligatorio si viene en el formulario)
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        error_log('[KASU][Login] CSRF inválido');
        http_response_code(403);
        exit;
    }

    $HostRaw = (string)($_POST['Host'] ?? '/login/index.php');
    $Host    = $mysqli->real_escape_string($HostRaw);
    $Usuario = trim((string)($_POST['Usuario'] ?? ''));
    $Pass    = (string)($_POST['PassWord'] ?? '');

    if ($Usuario === '' || $Pass === '') {
        error_log('[KASU][Login] Usuario o contraseña vacíos');
        header('Location: https://kasu.com.mx/login/index.php?data=4');
        exit;
    }

    error_log("[KASU][Login] Intento de acceso para {$Usuario}");
    if (autenticarVendedor($mysqli)) {
        $usuarioSesion = (string)($_SESSION['Vendedor'] ?? $Usuario);
        $idEmp = (int)$basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $usuarioSesion);
        $_SESSION['IdEmpleado'] = $idEmp;
        $_SESSION['IdVendedor'] = $usuarioSesion;
        if (empty($_SESSION['csrf_logout'])) {
            $_SESSION['csrf_logout'] = bin2hex(random_bytes(32));
        }

        // Auditoría
        $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'LoginOK',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );

        error_log("[KASU][Login] Acceso concedido a {$usuarioSesion}");
        header('Location: https://kasu.com.mx/login/Pwa_Principal.php');
        exit;
    }

    // Auditoría fallo
    $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'LoginFAIL',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    header('Location: https://kasu.com.mx/login/index.php?data=4');
    exit;
}

/********************* BLOQUE: CAMBIAR CONTRASEÑA (usuario conoce su contraseña actual) *******************************/
/* Qué hace: Verifica Pass actual y actualiza a nuevo hash sha256; registra auditoría */
if (!empty($_POST['CambiarPass'])) {
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        error_log('[KASU][CambiarPass] CSRF inválido');
        http_response_code(403);
        exit;
    }

    $Host    = $mysqli->real_escape_string($_POST['Host'] ?? '/login/index.php');
    $Usuario = trim((string)($_POST['Usuario'] ?? ''));
    $PassAct = (string)($_POST['PassAct']   ?? '');
    $P1      = (string)($_POST['PassWord1'] ?? '');
    $P2      = (string)($_POST['PassWord2'] ?? '');

    if ($P1 !== $P2) {
        error_log('[KASU][CambiarPass] Nueva contraseña no coincide');
        header('Location: https://kasu.com.mx/login/index.php?action=cp&data=2');
        exit;
    }

    $hashDB = $basicas->BuscarCampos($mysqli, "Pass", "Empleados", "IdUsuario", $Usuario);
    if (empty($hashDB) || hash('sha256', $PassAct) !== $hashDB) {
        error_log("[KASU][CambiarPass] Contraseña actual incorrecta para {$Usuario}");
        header('Location: https://kasu.com.mx/login/index.php?action=cp&data=5');
        exit;
    }

    $idEmp = $basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $Usuario);
    $basicas->ActCampo($mysqli, "Empleados", "Pass", hash('sha256', $P1), $idEmp);
    error_log("[KASU][CambiarPass] Contraseña actualizada para {$Usuario}");

    // Auditoría
    $seguridad->auditoria_registrar(
        $mysqli,
        $basicas,
        $_POST,
        'PassChangeOK',
        $_POST['Host'] ?? $_SERVER['PHP_SELF']
    );

    header('Location: https://kasu.com.mx/login/index.php?data=6');
    exit;
}

/********************************* BLOQUE: PAGO DE COMISIONES *********************************/
/* Qué hace: Registra un pago de comisión en Comisiones_pagos */
if (isset($_POST['PagoCom'])) {
    // CSRF opcional para compatibilidad
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $Cantidad   = $mysqli->real_escape_string((string)($_POST['Cantidad']   ?? ''));
    $IdEmpleado = $mysqli->real_escape_string((string)($_POST['IdEmpleado'] ?? ''));
    $Cuenta     = $mysqli->real_escape_string((string)($_POST['Cuenta']     ?? ''));
    $RefDepo    = $mysqli->real_escape_string((string)($_POST['RefDepo']    ?? ''));
    $Host       = $mysqli->real_escape_string((string)($_POST['Host']       ?? ''));
    $name       = $mysqli->real_escape_string((string)($_POST['name']       ?? ''));

    $DatGps = [
        "Cantidad"      => $Cantidad,
        "IdVendedor"    => $IdEmpleado,
        "UsrResgistra"  => $_SESSION["Vendedor"] ?? '',
        "Banco"         => $Cuenta,
        "Referencia"    => $RefDepo,
        "fechaRegistro" => $hoy . " " . $HoraActual
    ];

    $basicas->InsertCampo($mysqli, "Comisiones_pagos", $DatGps);

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit;
}

/********************************* BLOQUE: CAMBIO DE VENDEDOR — Rev. 10/11/2025 JCCM *********************************/
/* Qué hace: Actualiza sucursal y/o superior (Equipo) del empleado. Registra auditoría y redirige con Msg base64. */
if (isset($_POST['CambiVend'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    /* === Entradas normalizadas === */
    $hostIn       = (string)($_POST['Host'] ?? '');
    $name         = (string)($_POST['name'] ?? '');
    $idEmpleado   = (int)($_POST['IdEmpleado'] ?? 0);
    $idSucursal   = (int)($_POST['IdSucursal'] ?? 0);  // puede venir vacío en algunos flujos
    $nvaSucursal  = (int)($_POST['NvaSucursal'] ?? 0); // NUEVO: sucursal destino
    $nvoSuperior  = (int)($_POST['NvoSuperior'] ?? 0);

    // Destino de sucursal: prioriza NvaSucursal, si no viene usa IdSucursal
    $sucDestino = $nvaSucursal > 0 ? $nvaSucursal : $idSucursal;

    // Valores actuales en BD
    $baseSuperior = (int)$basicas->BuscarCampos($mysqli, "Equipo",   "Empleados", "Id", $idEmpleado);
    $baseSucursal = (int)$basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "Id", $idEmpleado);

    $cambiaSup = ($nvoSuperior > 0 && $nvoSuperior !== $baseSuperior);
    $cambiaSuc = ($sucDestino  > 0 && $sucDestino  !== $baseSucursal);

    if ($cambiaSup || $cambiaSuc) {
        // Auditoría
        $seguridad->auditoria_registrar(
            $mysqli, $basicas, $_POST, 'Cambio_de_Superior', $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );

        // Actualizaciones puntuales
        if ($cambiaSuc) $basicas->ActCampo($mysqli, "Empleados", "Sucursal", $sucDestino,  $idEmpleado);
        if ($cambiaSup) $basicas->ActCampo($mysqli, "Empleados", "Equipo",   $nvoSuperior, $idEmpleado);

        $alert = 'El colaborador se actualizó correctamente';
    } else {
        $alert = 'Sin cambios: mismo superior y sucursal';
    }

    /* === Redirección POST→GET con ?Msg= en base64 === */
    $msg = rawurlencode(base64_encode($alert));

    // Resuelve URL destino
    $host = $hostIn !== '' ? $hostIn : ($_SERVER['HTTP_REFERER'] ?? $_SERVER['PHP_SELF']);
    // Si no es absoluta, préfix con dominio
    if (!preg_match('~^https?://~i', $host)) {
        $host = 'https://kasu.com.mx' . $host;
    }
    $sep = (strpos($host, '?') !== false) ? '&' : '?';
    header('Location: ' . $host . $sep . 'name=' . rawurlencode($name) . '&Msg=' . $msg, true, 303);
    exit;
}

/********************************* BLOQUE: BAJA DE EMPLEADO *********************************/
/* Qué hace: Libera plaza de Empleados y guarda motivo en Contacto */
if (isset($_POST['BajaEmp'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $IdEmpleado = $mysqli->real_escape_string((string)($_POST['IdEmpleado'] ?? ''));
    $MotivoBaja = $mysqli->real_escape_string((string)($_POST['MotivoBaja'] ?? ''));
    $Host       = $mysqli->real_escape_string((string)($_POST['Host']       ?? ''));
    $name       = $mysqli->real_escape_string((string)($_POST['name']       ?? ''));

    $IdBAse = $basicas->BuscarCampos($mysqli, "IdContacto", "Empleados", "Id", $IdEmpleado);

    $basicas->ActCampo($mysqli, "Empleados", "Nombre",    "Vacante", $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "IdUsuario", "Usuario", $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "IdContacto", null,     $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "Pass",       null,     $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "Equipo",     null,     $IdEmpleado);
    $basicas->ActCampo($mysqli, "Empleados", "Sucursal",   null,     $IdEmpleado);

    $basicas->ActCampo($mysqli, "Contacto", "Motivo", $MotivoBaja, $IdBAse);

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&name=' . $name);
    exit;
}

/**************************** BLOQUE: CREAR EMPLEADO  Revisado 05/11/2025 JCCM *******************************************/
/* Qué hace: Alta de empleado. Si proviene de Pwa_Prospectos, toma datos del prospecto y libera plaza */
if (isset($_POST['CreaEmpl'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $Nombre      = $mysqli->real_escape_string((string)($_POST['Nombre']     ?? ''));
    $Email       = $mysqli->real_escape_string((string)($_POST['Email']      ?? ''));
    $Telefono    = $mysqli->real_escape_string((string)($_POST['Telefono']   ?? ''));
    $Direccion   = $mysqli->real_escape_string((string)($_POST['Direccion']  ?? ''));
    $calle       = $mysqli->real_escape_string((string)($_POST['calle']      ?? '')); // Prospectos
    $Host        = $mysqli->real_escape_string((string)($_POST['Host']       ?? ''));
    $name        = $mysqli->real_escape_string((string)($_POST['name']       ?? ''));
    $Nivel       = $mysqli->real_escape_string((string)($_POST['Nivel']      ?? ''));
    $Sucursal    = $mysqli->real_escape_string((string)($_POST['Sucursal']   ?? ''));
    $Lider       = $mysqli->real_escape_string((string)($_POST['Lider']      ?? ''));
    $Cuenta      = $mysqli->real_escape_string((string)($_POST['Cuenta']     ?? ''));
    $IdProspecto = $mysqli->real_escape_string((string)($_POST['IdProspecto']?? ''));

    if ($Host === "/login/Pwa_Prospectos.php") {
        // Datos del prospecto
        $venta = "SELECT * FROM prospectos WHERE Id='" . $IdProspecto . "' LIMIT 1";
        $res   = $pros->query($venta);
        $Reg   = $res && $res->fetch_assoc() ? $res->fetch_assoc() : null;

        if (!$Reg) {
            header('Location: https://kasu.com.mx' . $Host . '?&name=' . $name . '&Msg=Prospecto no encontrado');
            exit;
        }

        // Usuario sugerido por nombre
        $Sg1t  = substr($Reg['FullName'] ?? '', 0, 3);
        $Sg2t  = substr($Reg['FullName'] ?? '', -3);
        $Dil   = $Sg1t . $Sg2t;
        $DirUrl = strtoupper($Dil);
        $dRc    = mt_rand();

        // Actualiza prospecto
        $basicas->ActCampo($pros, "prospectos", "NoTel",      $Telefono,    $IdProspecto);
        $basicas->ActCampo($pros, "prospectos", "Email",      $Email,       $IdProspecto);
        $basicas->ActCampo($pros, "prospectos", "Direccion",  $calle,       $IdProspecto);
        $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1,           $IdProspecto);

        // Contacto
        $ArrayContacto = [
            "Usuario"  => $_SESSION["Vendedor"] ?? '',
            "Host"     => $Host,
            "Mail"     => $Email,
            "Telefono" => $Telefono,
            "calle"    => $calle,
            "Producto" => "Distribuidor"
        ];
        $IdContacto = $basicas->InsertCampo($mysqli, "Contacto", $ArrayContacto);

        // Usuario
        $IdUsuario = $basicas->BuscarCampos($mysqli, "id", "Usuario", "ClaveCurp", $Reg['Curp'] ?? '');
        if (empty($IdUsuario)) {
            $ArrayUsuario = [
                "Usuario"       => $_SESSION["Vendedor"] ?? '',
                "IdContact"     => $IdContacto,
                "Tipo"          => 'Distribuidor',
                "Nombre"        => $Reg['FullName'] ?? '',
                "ClaveCurp"     => $Reg['Curp'] ?? '',
                "Email"         => $Email,
                "FechaRegistro" => $hoy . " " . $HoraActual
            ];
            $IdUsuario = $basicas->InsertCampo($mysqli, "Usuario", $ArrayUsuario);
        } else {
            $basicas->ActCampo($mysqli, "Usuario", "IdContact", $IdContacto, $IdUsuario);
        }

        // Plaza disponible para nivel 7
        $EspEMp = $basicas->Buscar2Campos($mysqli, "Id", "Empleados", "Sucursal", 0, "Nivel", 7);
        if (!empty($EspEMp)) {
            // Auditoría
            $seguridad->auditoria_registrar(
                $mysqli,
                $basicas,
                $_POST,
                'Autorizacion_Distibuidor',
                $_POST['Host'] ?? $_SERVER['PHP_SELF']
            );

            // Sucursal del autorizador
            $SucursalAut = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "IdUsuario", $_SESSION["Vendedor"] ?? '');

            // Alta en plaza
            $basicas->ActCampo($mysqli, "Empleados", "Nombre",     $Reg['FullName'] ?? '', $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario",  $DirUrl,               $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "IdContacto", $IdContacto,           $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "FechaAlta",  $hoy,                  $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "Sucursal",   $SucursalAut,          $EspEMp);
            $basicas->ActCampo($mysqli, "Empleados", "Telefono",   $Telefono,             $EspEMp);
        }

        $alert = "El distribuidor se registró correctamente. Asigna sucursal y coordinador con mesa de control. Luego podrá ingresar al sistema.";
        header('Location: https://kasu.com.mx' . $Host . '?&name=' . $name . '&Msg=' . $alert);
        exit;
    } else {
        // Usuario sugerido
        $Sg1t   = substr($Nombre, 0, 3);
        $Sg2t   = substr($Nombre, -3);
        $Dil    = $Sg1t . $Sg2t;
        $DirUrl = strtoupper($Dil);
        $dRc    = mt_rand();

        // Contacto para empleado directo
        $DatContac = [
            "Usuario"   => $_SESSION["Vendedor"] ?? '',
            "Host"      => $Host,
            "Mail"      => $Email,
            "Telefono"  => $Telefono,
            "Direccion" => $Direccion,
            "Producto"  => "Empleado"
        ];
        $uSR = $basicas->InsertCampo($mysqli, "Contacto", $DatContac);

        // Plaza disponible por nivel
        $RegIdPlaza = $basicas->Buscar2Campos($mysqli, "Id", "Empleados", "Sucursal", 0, "Nivel", $Nivel);
        if (!empty($RegIdPlaza)) {
            $basicas->ActCampo($mysqli, "Empleados", "Nombre",     $Nombre,  $RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "IdContacto", $uSR,     $RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario",  $DirUrl,  $RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "Pass",       $dRc,     $RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "Equipo",     $Lider,   $RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "Sucursal",   $Sucursal,$RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "FechaAlta",  $hoy,     $RegIdPlaza);
            $basicas->ActCampo($mysqli, "Empleados", "Cuenta",     $Cuenta,  $RegIdPlaza);
        }

        if ((string)$Nivel === '7') {
            $contra = '&Add=' . $uSR;
            if (!empty($IdProspecto)) {
                $basicas->ActCampo($pros, "prospectos", "Cancelacion", 1, $IdProspecto);
            }
        }

        // Si necesitas redirigir, descomenta:
        // header('Location: https://kasu.com.mx' . $Host . '?Ml=1&name=' . $name . ($contra ?? ''));
        // exit;
    }
}

/********************************* BLOQUE: REENVÍO DE CONTRASEÑA *********************************/
/* Qué hace: Genera token temporal en Empleados.Pass y envía enlace de registro de contraseña */
if (isset($_POST['ReenCOntra'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        error_log('[KASU][ReenCOntra] CSRF inválido');
        http_response_code(403);
        exit;
    }

    $Id        = $mysqli->real_escape_string((string)($_POST['Id']        ?? ''));
    $Nombre    = $mysqli->real_escape_string((string)($_POST['Nombre']    ?? ''));
    $Email     = $mysqli->real_escape_string((string)($_POST['Email']     ?? ''));
    $IdUsuario = $mysqli->real_escape_string((string)($_POST['IdUsuario'] ?? ''));
    $Host      = $mysqli->real_escape_string((string)($_POST['Host']      ?? ''));
    $name      = $mysqli->real_escape_string((string)($_POST['name']      ?? ''));

    $dRc     = mt_rand();
    $dirUrl1 = base64_encode((string)$dRc);
    $DirUrl  = "https://kasu.com.mx/login/index.php?data=" . $dirUrl1 . "&Usr=" . rawurlencode($IdUsuario !== '' ? $IdUsuario : (string)$Id);

    $basicas->ActCampo($mysqli, "Empleados", "Pass", $dRc, $Id);
    error_log("[KASU][ReenCOntra] Token generado para {$IdUsuario}");

    $Mensaje = $Correo->Mensaje('RESTABLECIMIENTO DE CONTRASEÑA', $Nombre, $DirUrl, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    $Correo->EnviarCorreo($Nombre, $Email, 'RESTABLECIMIENTO DE CONTRASEÑA', $Mensaje);

    header('Location: https://kasu.com.mx' . $Host . '?Ml=1&name=' . $name);
    exit;
}

/********************************* BLOQUE: GENERAR CONTRASEÑA (Registro de Contraseña) *********************************/
/* Qué hace: Toma el token base64(data) guardado en Empleados.Pass, fija nuevo Pass sha256 e inserta evento+GPS+FP */
if (!empty($_POST['GenCont'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        error_log('[KASU][GenCont] CSRF inválido');
        http_response_code(403);
        exit;
    }

    $PassWord1   = (string)($_POST['PassWord1'] ?? '');
    $PassWord2   = (string)($_POST['PassWord2'] ?? '');
    $tokenRaw    = (string)($_POST['data']      ?? '');
    $fingerprint = (string)($_POST['fingerprint'] ?? '');
    $Host        = $mysqli->real_escape_string((string)($_POST['Host'] ?? '/login/index.php'));

    if ($PassWord1 === $PassWord2) {
        $decodedPass  = base64_decode($tokenRaw, true);
        $PassRecordId = $decodedPass !== false ? $basicas->BuscarCampos($mysqli, "Id", "Empleados", "Pass", $decodedPass) : '';

        if (!empty($PassRecordId)) {
            error_log("[KASU][GenCont] Token válido para empleado {$PassRecordId}");
            // GPS
            $gpsLogin = $basicas->InsertCampo($mysqli, "gps", [
                "latitud"  => $mysqli->real_escape_string((string)($_POST['latitud']  ?? '')),
                "longitud" => $mysqli->real_escape_string((string)($_POST['longitud'] ?? '')),
                "accuracy" => $mysqli->real_escape_string((string)($_POST['accuracy'] ?? ''))
            ]);

            // Fingerprint
            if (empty($basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fingerprint))) {
                $DatFinger = [
                    "fingerprint"   => $fingerprint,
                    "browser"       => (string)($_POST['browser']      ?? ''),
                    "flash"         => (string)($_POST['flash']        ?? ''),
                    "canvas"        => (string)($_POST['canvas']       ?? ''),
                    "connection"    => (string)($_POST['connection']   ?? ''),
                    "cookie"        => (string)($_POST['cookie']       ?? ''),
                    "display"       => (string)($_POST['display']      ?? ''),
                    "fontsmoothing" => (string)($_POST['fontsmoothing']?? ''),
                    "fonts"         => (string)($_POST['fonts']        ?? ''),
                    "formfields"    => (string)($_POST['formfields']   ?? ''),
                    "java"          => (string)($_POST['java']         ?? ''),
                    "language"      => (string)($_POST['language']     ?? ''),
                    "silverlight"   => (string)($_POST['silverlight']  ?? ''),
                    "os"            => (string)($_POST['os']           ?? ''),
                    "timezone"      => (string)($_POST['timezone']     ?? ''),
                    "touch"         => (string)($_POST['touch']        ?? ''),
                    "truebrowser"   => (string)($_POST['truebrowser']  ?? ''),
                    "plugins"       => (string)($_POST['plugins']      ?? ''),
                    "useragent"     => (string)($_POST['useragent']    ?? '')
                ];
                $basicas->InsertCampo($mysqli, "FingerPrint", $DatFinger);
            }

            // Evento
            $DatEventos = [
                "IdFInger"      => $fingerprint,
                "Idgps"         => $gpsLogin,
                "Host"          => $Host,
                "Evento"        => (string)($_POST['Evento']  ?? "AltaPass"),
                "Usuario"       => (string)($_POST['Usuario'] ?? ''),
                "FechaRegistro" => $hoy . " " . $HoraActual
            ];
            $basicas->InsertCampo($mysqli, "Eventos", $DatEventos);

            // Actualiza pass
            $PassSHa = hash('sha256', $PassWord1);
            $basicas->ActCampo($mysqli, "Empleados", "Pass", $PassSHa, $PassRecordId);

            $dta = 3; // Exitoso
        } else {
            error_log('[KASU][GenCont] Token inválido o ya utilizado');
            $dta = 1; // Token ya usado/no válido
        }
    } else {
        error_log('[KASU][GenCont] Contraseñas no coinciden');
        $dta = 2; // No coinciden
    }

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&Data=' . (int)$dta);
    exit;
}

/********************************* BLOQUE: ACTUALIZAR DATOS DE UN EMPLEADO *********************************/
/* Qué hace: Si cambian Mail/Teléfono/Dirección, crea nuevo Contacto y re-enlaza Usuario y Venta */
if (isset($_POST['CamDat'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $Host      = $mysqli->real_escape_string((string)($_POST['Host'] ?? ''));
    $Nombre    = $mysqli->real_escape_string((string)($_POST['Nombre'] ?? ''));
    $IdContact = $mysqli->real_escape_string((string)($_POST['IdContact'] ?? ''));
    $Direccion = $mysqli->real_escape_string((string)($_POST['Direccion'] ?? ''));
    $Telefono  = $mysqli->real_escape_string((string)($_POST['Telefono']  ?? ''));
    $Mail      = $mysqli->real_escape_string((string)($_POST['Mail']      ?? ''));

    $sql  = "SELECT * FROM Contacto WHERE Id = '$IdContact'";
    $recs = mysqli_query($mysqli, $sql);

    $val  = 0;
    if ($recs && ($Recg = mysqli_fetch_assoc($recs))) {
        if (($Recg['Direccion'] ?? '') !== $Direccion) { $val++; }
        if (($Recg['Telefono']  ?? '') !== $Telefono)  { $val++; }
        if (($Recg['Mail']      ?? '') !== $Mail)      { $val++; }
    }

    $IdVenta = $basicas->BuscarCampos($mysqli, "Id", "Venta", "IdContact", $IdContact);
    $SDTM    = !empty($IdVenta) ? $basicas->BuscarCampos($mysqli, "Producto", "Venta", "Id", $IdVenta) : '';

    if ($val > 0) {
        $Pripg = [
            "Usuario"   => $_SESSION["Vendedor"] ?? '',
            "Host"      => $mysqli->real_escape_string((string)($_POST['Host'] ?? '')),
            "Mail"      => $Mail,
            "Telefono"  => $Telefono,
            "Direccion" => $Direccion,
            "Producto"  => $SDTM
        ];
        $NvoCnc = $basicas->InsertCampo($mysqli, "Contacto", $Pripg);
        $basicas->ActTab($mysqli, "Usuario", "IdContact", $NvoCnc, "IdContact", $IdContact);
        if (!empty($IdVenta)) {
            $basicas->ActCampo($mysqli, "Venta", "IdContact", $NvoCnc, $IdVenta);
        }
    }

    // Correo de actualización
    $Mensaje = $Correo->Mensaje(
        "ACTUALIZACION DE DATOS",
        $Nombre,
        'https://kasu.com.mx/ActualizacionDatos/index.php',
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $IdVenta
    );
    $Correo->EnviarCorreo($Nombre, $Mail, "ACTUALIZACION DE DATOS", $Mensaje);

    header('Location: https://kasu.com.mx' . $Host);
    exit;
}

/********************************* BLOQUE: REGISTRO DE PROBLEMAS *********************************/
/* Qué hace: Inserta un reporte de problema asociado al usuario de sesión y redirige con mensaje */
if (isset($_POST['Reporte'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $Host      = $mysqli->real_escape_string((string)($_POST['Host'] ?? ''));
    $problema  = $mysqli->real_escape_string((string)($_POST['problema'] ?? ''));

    $DatProblema = [
        "Usuario"  => $_SESSION["Vendedor"] ?? '',
        "Problema" => $problema
    ];
    $basicas->InsertCampo($mysqli, "Problemas", $DatProblema);

    $msg = base64_encode('Gracias por enviarnos tu reporte');
    header('Location: https://kasu.com.mx' . $Host . '?Msg=' . $msg);
    exit;
}
/********************************* BLOQUE: MESA MARKETING — TARJETAS *********************************/
/* Qué hace:
   - crear_tarjeta: inserta en PostSociales. Sube imagen a /assets/images/cupones/ si viene archivo.
   - activar_tarjeta / desactivar_tarjeta: cambia Status.
   - borrar_tarjeta: elimina registro.
   - actualizar_vigencia: define Validez_Fin (acepta NULL).
   - Todas las acciones: registran auditoría con $seguridad->auditoria_registrar y redirigen al Host con Msg.
   Requiere que la vista haya puesto un token CSRF en $_SESSION['csrf_mm'] y campo POST 'csrf'.
*/
if (isset($_POST['accion'])) {
    // ---------- CSRF ----------
    $csrfPost = (string)($_POST['csrf'] ?? '');
    $csrfOk   = $csrfPost !== ''
             && (hash_equals($_SESSION['csrf_mm']   ?? '', $csrfPost)
              || hash_equals($_SESSION['csrf_auth'] ?? '', $csrfPost));
    if (!$csrfOk) {
        http_response_code(403);
        exit('CSRF inválido');
    }

    // ---------- Utilidades comunes ----------
    $Host      = $mysqli->real_escape_string((string)($_POST['Host'] ?? '/login/Mesa_Marketing.php'));
    $Vendedor  = (string)($_SESSION['Vendedor'] ?? '');
    $accion    = (string)$_POST['accion'];
    $go = function(string $msg) use ($Host){
        header('Location: https://kasu.com.mx' . $Host . '?Msg=' . urlencode($msg));
        exit;
    };

    // === Helper: optimiza imagen a JPG máx 1200px, fondo blanco si PNG ===
    function saveOptimizedImage(string $tmp, string $destJpgPath, string $mime, int $maxW=1200, int $maxH=1200, int $quality=82): bool {
        [$w,$h] = @getimagesize($tmp);
        if (!$w || !$h) return false;
        $scale = min($maxW/$w, $maxH/$h, 1);
        $nw = (int)floor($w*$scale);
        $nh = (int)floor($h*$scale);

        // Carga
        if ($mime === 'image/png')      { $src = @imagecreatefrompng($tmp); }
        elseif ($mime === 'image/jpeg') { $src = @imagecreatefromjpeg($tmp); }
        else                            { return false; }

        if (!$src) return false;

        // Lienzo destino JPG
        $dst = imagecreatetruecolor($nw,$nh);
        // Fondo blanco por si la fuente tenía transparencia
        $white = imagecolorallocate($dst, 255,255,255);
        imagefilledrectangle($dst, 0,0, $nw,$nh, $white);

        // Reescalado
        imagecopyresampled($dst, $src, 0,0, 0,0, $nw,$nh, $w,$h);

        // Guardar como JPG comprimido
        $ok = imagejpeg($dst, $destJpgPath, $quality);

        imagedestroy($src);
        imagedestroy($dst);
        return $ok;
    }

    // ---------- Crear tarjeta ----------
    if ($accion === 'crear_tarjeta') {
        // Campos base
        $Tipo       = $mysqli->real_escape_string((string)($_POST['Tipo']      ?? 'Vta'));      // Vta | Art
        $Red        = $mysqli->real_escape_string((string)($_POST['Red']       ?? 'facebook'));
        $TitA       = $mysqli->real_escape_string((string)($_POST['TitA']      ?? ''));
        $DesA       = $mysqli->real_escape_string((string)($_POST['DesA']      ?? ''));
        $Producto   = $mysqli->real_escape_string((string)($_POST['Producto']  ?? ''));
        $Dire       = $mysqli->real_escape_string((string)($_POST['Dire']      ?? ''));
        $Status     = (int)($_POST['Status'] ?? 1);
        $Descuento  = (int)($_POST['Descuento'] ?? 0);
        $ValidezFin = trim((string)($_POST['Validez_Fin'] ?? ''));

        // Resolver imagen: archivo o texto
        $imgValue = '';
        $rootPath = dirname(__DIR__); // / (raíz del sitio)
        $uploadDir = $rootPath . '../../assets/images/cupones/';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0755, true); }

        if (!empty($_FILES['ImgFile']['name']) && $_FILES['ImgFile']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($_FILES['ImgFile']['tmp_name']);
            if (!in_array($mime, ['image/jpeg','image/png'], true)) {
                $go('Formato no permitido. Usa JPG o PNG.');
            }

            // Nombre base y destino .jpg optimizado
            $base = preg_replace('/[^a-zA-Z0-9_-]/','', pathinfo($_FILES['ImgFile']['name'], PATHINFO_FILENAME));
            $file = $base . '_' . date('Ymd_His') . '_' . substr(sha1(random_bytes(8)),0,6) . '.jpg';
            $dest = $uploadDir . $file;

            // Redimensiona y comprime
            if (!saveOptimizedImage($_FILES['ImgFile']['tmp_name'], $dest, $mime, 1200, 1200, 82)) {
                $go('No se pudo optimizar la imagen.');
            }

            // Valor a guardar en DB
            $imgValue = ($Tipo === 'Art')
                ? 'https://kasu.com.mx/assets/images/cupones/' . $file
                : $file;
        }else {
            // Tomar texto de imagen (nombre o URL)
            $imgTxt = trim((string)($_POST['Img'] ?? ''));
            if ($imgTxt === '') {
                $go('Debes subir una imagen o indicar un nombre/URL de imagen.');
            }
            if ($Tipo === 'Art') {
                // Art espera URL completa; si dieron nombre, lo convertimos a URL interna
                $imgValue = preg_match('#^https?://#i', $imgTxt)
                    ? $imgTxt
                    : ('https://kasu.com.mx/assets/images/cupones/' . ltrim($imgTxt, '/'));
            } else {
                // Vta almacena solo el nombre de archivo
                $imgValue = basename($imgTxt);
            }
        }

        // Armar payload para PostSociales
        $row = [
            'Img'          => $imgValue,
            'TitA'         => $TitA,
            'DesA'         => $DesA,
            'Dire'         => $Dire,
            'Red'          => $Red,
            'Descuento'    => $Descuento,
            'Producto'     => $Producto,
            'Status'       => $Status,
            'Tipo'         => $Tipo,
            // timestamp permite NULL
            'Validez_Fin'  => ($ValidezFin !== '' ? $ValidezFin : null),
        ];

        // Insertar
        $idNew = $basicas->InsertCampo($mysqli, 'PostSociales', $row);

        // Auditoría de creación
        $aud = $_POST;
        $aud['IdTarjeta'] = $idNew;
        $aud['ImgFinal']  = $imgValue;
        $aud['Usuario']   = $Vendedor;
        $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $aud,
            'TarjetaCrear',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );

        $go('Tarjeta creada correctamente. Id=' . (int)$idNew);
    }

    // ---------- Activar tarjeta ----------
    if ($accion === 'activar_tarjeta') {
        $Id = (int)($_POST['Id'] ?? 0);
        if ($Id <= 0) $go('Id inválido');
        $basicas->ActCampo($mysqli, 'PostSociales', 'Status', 1, $Id);

        $seguridad->auditoria_registrar(
            $mysqli, $basicas, ['Id'=>$Id,'Usuario'=>$Vendedor],
            'TarjetaActivar', $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        $go('Tarjeta activada');
    }

    // ---------- Desactivar tarjeta ----------
    if ($accion === 'desactivar_tarjeta') {
        $Id = (int)($_POST['Id'] ?? 0);
        if ($Id <= 0) $go('Id inválido');
        $basicas->ActCampo($mysqli, 'PostSociales', 'Status', 0, $Id);

        $seguridad->auditoria_registrar(
            $mysqli, $basicas, ['Id'=>$Id,'Usuario'=>$Vendedor],
            'TarjetaDesactivar', $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        $go('Tarjeta desactivada');
    }

    // ---------- Borrar tarjeta ----------
    if ($accion === 'borrar_tarjeta') {
        $Id = (int)($_POST['Id'] ?? 0);
        if ($Id <= 0) $go('Id inválido');

        if ($stmt = $mysqli->prepare("DELETE FROM PostSociales WHERE Id=? LIMIT 1")) {
            $stmt->bind_param('i', $Id);
            $stmt->execute();
            $stmt->close();
        }

        $seguridad->auditoria_registrar(
            $mysqli, $basicas, ['Id'=>$Id,'Usuario'=>$Vendedor],
            'TarjetaBorrar', $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        $go('Tarjeta borrada');
    }

    // ---------- Actualizar vigencia ----------
    if ($accion === 'actualizar_vigencia') {
        $Id = (int)($_POST['Id'] ?? 0);
        if ($Id <= 0) $go('Id inválido');
        $ValidezFin = trim((string)($_POST['Validez_Fin'] ?? ''));

        // Prepared para permitir NULL real
        $sql = "UPDATE PostSociales SET Validez_Fin=? WHERE Id=?";
        $stmt = $mysqli->prepare($sql);
        if ($ValidezFin === '') {
            $null = null;
            $stmt->bind_param('si', $null, $Id);
        } else {
            $stmt->bind_param('si', $ValidezFin, $Id);
        }
        $stmt->execute();
        $stmt->close();

        $seguridad->auditoria_registrar(
            $mysqli, $basicas, ['Id'=>$Id,'Validez_Fin'=>$ValidezFin,'Usuario'=>$Vendedor],
            'TarjetaVigencia', $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        $go('Vigencia actualizada');
    }

    // Si llegó una acción desconocida
    $go('Acción no reconocida');
}


/* ==================== BOTONES: Activar | Desactivar | Borrar | Actualizar vigencia ==================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
  // --- utilidades locales ---
  $redir = function(string $msg) {
    $ref = $_SERVER['HTTP_REFERER'] ?? '/login/Mesa_Marketing.php';
    // aseguremos ruta interna
    $url = (strpos($ref, 'http') === 0) ? $ref : ('https://kasu.com.mx' . $ref);
    $glue = (strpos($url, '?') !== false) ? '&' : '?';
    header('Location: ' . $url . $glue . 'Msg=' . urlencode($msg));
    exit;
  };
  $assert_csrf = function() {
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
      http_response_code(403); exit;
    }
  };

  try {
    $assert_csrf();

    $accion = (string)$_POST['accion'];
    $id     = (int)($_POST['Id'] ?? 0);
    $host   = $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? '/php/Funcionalidad_Empleados.php');

    if ($id <= 0) { $redir('ID inválido'); }

    switch ($accion) {
      case 'activar_tarjeta':
        // Status = 1
        $basicas->ActCampo($mysqli, 'PostSociales', 'Status', 1, $id);
        // Auditoría
        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Tarjeta_Activar', $host);
        $redir('Tarjeta activada');
        break;

      case 'desactivar_tarjeta':
        // Status = 0
        $basicas->ActCampo($mysqli, 'PostSociales', 'Status', 0, $id);
        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Tarjeta_Desactivar', $host);
        $redir('Tarjeta desactivada');
        break;

      case 'borrar_tarjeta':
        // Borrado lógico: Status = 9 (evita perder histórico y conteos)
        $basicas->ActCampo($mysqli, 'PostSociales', 'Status', 9, $id);
        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Tarjeta_Borrar', $host);
        $redir('Tarjeta eliminada');
        break;

      case 'actualizar_vigencia':
        // Validez_Fin desde <input type="date"> → Y-m-d 23:59:59
        $val = trim((string)($_POST['Validez_Fin'] ?? ''));
        if ($val === '') { $redir('Fecha vacía'); }
        $dt = DateTime::createFromFormat('Y-m-d', $val);
        if ($dt === false) { $redir('Fecha inválida'); }
        $dt->setTime(23, 59, 59);
        $fecha = $dt->format('Y-m-d H:i:s');

        $stmt = $mysqli->prepare('UPDATE PostSociales SET Validez_Fin=? WHERE Id=? LIMIT 1');
        $stmt->bind_param('si', $fecha, $id);
        $stmt->execute();
        $stmt->close();

        $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Tarjeta_Vigencia', $host);
        $redir('Vigencia actualizada');
        break;

      default:
        $redir('Acción no reconocida');
    }
  } catch (Throwable $e) {
    $msg = 'Error: ' . substr($e->getMessage(), 0, 120);
    if (isset($seguridad)) {
      $seguridad->auditoria_registrar($mysqli, $basicas, $_POST, 'Tarjeta_Error', $_POST['Host'] ?? ($_SERVER['PHP_SELF'] ?? ''));
    }
    // Evita filtrar stack completo al usuario
    $redir($msg);
  }
}

?>
