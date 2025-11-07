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
    // CSRF (obligatorio si viene en el formulario)
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $Host    = $mysqli->real_escape_string($_POST['Host'] ?? '/login/index.php');
    $Usuario = trim((string)($_POST['Usuario'] ?? ''));
    $Pass    = (string)($_POST['PassWord'] ?? '');

    if ($Usuario === '' || $Pass === '') {
        header('Location: https://kasu.com.mx/login/index.php?data=4');
        exit;
    }

    $hashDB = $basicas->BuscarCampos($mysqli, "Pass", "Empleados", "IdUsuario", $Usuario);
    if (!empty($hashDB) && hash('sha256', $Pass) === $hashDB) {
        $idEmp = $basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $Usuario);
        $_SESSION['Vendedor']   = $Usuario;
        $_SESSION['IdVendedor'] = $idEmp;

        // Auditoría
        $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'LoginOK',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );

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
        http_response_code(403);
        exit;
    }

    $Host    = $mysqli->real_escape_string($_POST['Host'] ?? '/login/index.php');
    $Usuario = trim((string)($_POST['Usuario'] ?? ''));
    $PassAct = (string)($_POST['PassAct']   ?? '');
    $P1      = (string)($_POST['PassWord1'] ?? '');
    $P2      = (string)($_POST['PassWord2'] ?? '');

    if ($P1 !== $P2) {
        header('Location: https://kasu.com.mx/login/index.php?action=cp&data=2');
        exit;
    }

    $hashDB = $basicas->BuscarCampos($mysqli, "Pass", "Empleados", "IdUsuario", $Usuario);
    if (empty($hashDB) || hash('sha256', $PassAct) !== $hashDB) {
        header('Location: https://kasu.com.mx/login/index.php?action=cp&data=5');
        exit;
    }

    $idEmp = $basicas->BuscarCampos($mysqli, "Id", "Empleados", "IdUsuario", $Usuario);
    $basicas->ActCampo($mysqli, "Empleados", "Pass", hash('sha256', $P1), $idEmp);

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

/********************************* BLOQUE: CAMBIO DE VENDEDOR REVISION 05/11/2025 JCCM *********************************/
/* Qué hace: Actualiza sucursal/equipo del empleado y registra auditoría */
if (isset($_POST['CambiVend'])) {
    // CSRF opcional
    if (isset($_POST['csrf']) && !hash_equals($_SESSION['csrf_auth'] ?? '', (string)$_POST['csrf'])) {
        http_response_code(403);
        exit;
    }

    $Host        = $mysqli->real_escape_string((string)($_POST['Host']       ?? ''));
    $name        = $mysqli->real_escape_string((string)($_POST['name']       ?? ''));
    $IdVenta     = $mysqli->real_escape_string((string)($_POST['IdVenta']    ?? ''));
    $IdContact   = $mysqli->real_escape_string((string)($_POST['IdContact']  ?? ''));
    $IdUsuario   = $mysqli->real_escape_string((string)($_POST['IdUsuario']  ?? ''));
    $IdEmpleado  = $mysqli->real_escape_string((string)($_POST['IdEmpleado'] ?? ''));
    $IdSucursal  = $mysqli->real_escape_string((string)($_POST['IdSucursal'] ?? ''));
    $NvoSuperior = $mysqli->real_escape_string((string)($_POST['NvoSuperior']?? ''));

    $BaseSuperior = $basicas->BuscarCampos($mysqli, "Equipo",   "Empleados", "Id", $IdEmpleado);
    $BaseSucursal = $basicas->BuscarCampos($mysqli, "Sucursal", "Empleados", "Id", $IdEmpleado);

    if ($IdSucursal != $BaseSucursal || $NvoSuperior != $BaseSuperior) {
        // Auditoría
        $seguridad->auditoria_registrar(
            $mysqli,
            $basicas,
            $_POST,
            'Cambio_de_Superior',
            $_POST['Host'] ?? $_SERVER['PHP_SELF']
        );
        // Actualización
        $basicas->ActCampo($mysqli, "Empleados", "Sucursal", $IdSucursal, $IdEmpleado);
        $basicas->ActCampo($mysqli, "Empleados", "Equipo",   $NvoSuperior, $IdEmpleado);
        $alert = "El colaborador se actualizó correctamente";
    } else {
        $alert = "El colaborador no puede registrarse en la misma sucursal o en el mismo equipo";
    }

    header('Location: https://kasu.com.mx' . $Host . '?&name=' . $name . '&Msg=' . $alert);
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
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario",  null,     $RegIdPlaza);
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
        http_response_code(403);
        exit;
    }

    $Id    = $mysqli->real_escape_string((string)($_POST['Id']    ?? ''));
    $Nombre= $mysqli->real_escape_string((string)($_POST['Nombre']?? ''));
    $Email = $mysqli->real_escape_string((string)($_POST['Email'] ?? ''));
    $User  = $mysqli->real_escape_string((string)($_POST['User']  ?? ''));
    $Host  = $mysqli->real_escape_string((string)($_POST['Host']  ?? ''));
    $name  = $mysqli->real_escape_string((string)($_POST['name']  ?? ''));

    $dRc     = mt_rand();
    $dirUrl1 = base64_encode((string)$dRc);
    $DirUrl  = "https://kasu.com.mx/login/index.php?data=" . $dirUrl1 . "&Usr=" . $Id;

    $basicas->ActCampo($mysqli, "Empleados", "Pass", $dRc, $Id);

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
        http_response_code(403);
        exit;
    }

    $PassWord1   = (string)($_POST['PassWord1'] ?? '');
    $PassWord2   = (string)($_POST['PassWord2'] ?? '');
    $data        = (string)($_POST['data']      ?? '');
    $fingerprint = (string)($_POST['fingerprint'] ?? '');
    $Host        = $mysqli->real_escape_string((string)($_POST['Host'] ?? ''));

    if ($PassWord1 === $PassWord2) {
        $decodedPass  = base64_decode($data, true);
        $PassRecordId = $decodedPass !== false ? $basicas->BuscarCampos($mysqli, "Id", "Empleados", "Pass", $decodedPass) : '';

        if (!empty($PassRecordId)) {
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
            $basicas->ActCampo($mysqli, "Empleados", "IdUsuario", (string)($_POST['User'] ?? ''), $PassRecordId);
            $basicas->ActCampo($mysqli, "Empleados", "Pass", $PassSHa, $PassRecordId);

            $dta = 3; // Exitoso
        } else {
            $dta = 1; // Token ya usado/no válido
        }
    } else {
        $dta = 2; // No coinciden
    }

    header('Location: https://kasu.com.mx' . $Host . '?Vt=1&Data=' . $dta);
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
?>
