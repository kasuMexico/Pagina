<?php
/**************************************************************************************************
 * ARCHIVO: php/Analisis_Metas.php
 * FECHA: 2025-11-05
 * REVISADO POR: JCCM
 *
 * ¿QUÉ HACE?
 *  - Calcula ventas, pagos, sueldos y comisiones del mes en curso para el panel de análisis.
 *  - Usa helpers de Basicas/Financieras ya incluidos por la página que lo requiere.
 *
 * BLOQUES:
 *  1) Setup de fecha base del mes.
 *  2) Lectura de metas del usuario asignado.
 *  3) Recorrido de empleados para acumular ventas/pagos y sueldos.
 *  4) Cálculo de comisiones por empleado y saldo pendiente.
 *  5) Ajuste de sueldos por quincena.
 *  6) Cálculo de avances de ventas/cobranza y colores.
 *  7) Ajuste para nivel 7 (externos).
 *
 * NOTAS PHP 8.2:
 *  - Casts explícitos a float/int para evitar “passing null to parameter of type string/number”.
 *  - Validaciones básicas de recursos mysqli antes de iterar.
 **************************************************************************************************/

// Se asume que la sesión y librerías ya están cargadas en el archivo principal.
// session_start();
// require_once '../../eia/librerias.php';
// date_default_timezone_set('America/Mexico_City');

// Guardia mínima si alguien incluye este archivo sin $mysqli o sin $_SESSION:
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    // Evita fatal en includes anticipados
    return;
}
if (!isset($_SESSION['Vendedor'])) {
    return;
}

/* ===== 1) Fecha base del mes ===== */
$Fec0 = date('Y-m-d', strtotime('first day of this month'));

/* ===== Acumuladores ===== */
$VtasHoy    = 0.0;  // Ventas acumuladas del mes
$CobHoy     = 0.0;  // Pagos acumulados del mes
$SUeldos    = 0.0;  // Sueldos por pagar (no usado en panel actual)
$comisiones = 0.0;  // Total de comisiones acumuladas (no usado en panel actual)
$ComGenHoy  = 0.0;  // Comisiones generadas hoy (por contacto)
$PolizasMes = 0;
$CobranzaPlataforma = 0.0;
$CobranzaMpPendiente = 0.0;
$CobranzaSucursales = 0.0;
$MetaPolizas = 0.0;

/* ===== 2) Metas del usuario ===== */
$IdAsig  = $basicas->Buscar1Fechas($mysqli, 'Id', 'Asignacion', 'Usuario', $_SESSION['Vendedor'], 'Fecha', $Fec0);
$MetaVta = (float)$basicas->BuscarCampos($mysqli, 'MVtas',      'Asignacion', 'Id', $IdAsig);
$MetaCob = (float)$basicas->BuscarCampos($mysqli, 'MCob',       'Asignacion', 'Id', $IdAsig);
$Normali = (float)$basicas->BuscarCampos($mysqli, 'Normalidad', 'Asignacion', 'Id', $IdAsig);
$MetaPolizas = 0.0;
if ($colPoliza = $mysqli->query("SHOW COLUMNS FROM Asignacion LIKE 'MPolizas'")) {
    if ($colPoliza->num_rows > 0) {
        $MetaPolizas = (float)$basicas->BuscarCampos($mysqli, 'MPolizas', 'Asignacion', 'Id', $IdAsig);
    }
    $colPoliza->close();
}

/* ===== 3) Red de empleados ===== */
$empleados   = [];
$descendientes = [];
$IDUsuarioSesion = (string)$_SESSION['Vendedor'];
$VendId = 0;
$nivelUsuario = 0;

if ($rsEmp = $mysqli->query("SELECT Id, IdUsuario, Nivel, Equipo FROM Empleados WHERE Nombre <> 'Vacante'")) {
    while ($row = $rsEmp->fetch_assoc()) {
        $idEmp = (int)$row['Id'];
        $idUsuario = (string)$row['IdUsuario'];
        $nivelEmp = (int)$row['Nivel'];
        $equipoEmp = (int)($row['Equipo'] ?? 0);

        $empleados[$idEmp] = [
            'usuario' => $idUsuario,
            'nivel'   => $nivelEmp,
            'equipo'  => $equipoEmp,
        ];
        if ($equipoEmp > 0) {
            $descendientes[$equipoEmp][] = $idEmp;
        }
        if ($idUsuario === $IDUsuarioSesion) {
            $VendId = $idEmp;
            $nivelUsuario = $nivelEmp;
        }
    }
    $rsEmp->close();
}

$visCache = [];
$collectUsuarios = static function (int $id) use (&$collectUsuarios, &$visCache, $empleados, $descendientes): array {
    if (isset($visCache[$id])) {
        return $visCache[$id];
    }
    $lista = [];
    if (isset($empleados[$id]['usuario']) && $empleados[$id]['usuario'] !== '') {
        $lista[] = $empleados[$id]['usuario'];
    }
    foreach ($descendientes[$id] ?? [] as $childId) {
        $lista = array_merge($lista, $collectUsuarios($childId));
    }
    $visCache[$id] = $lista;
    return $lista;
};

$teamUsers = [];
if ($VendId > 0) {
    $teamUsers = $collectUsuarios($VendId);
}
if (!$teamUsers) {
    $teamUsers = [$IDUsuarioSesion];
}
$teamUsers = array_values(array_unique(array_filter($teamUsers)));
$mesActualIni = date('Y-m-01');

foreach ($teamUsers as $usr) {
    $VtasHoy += (float)$basicas->Sumar1Fechas($mysqli, 'CostoVenta', 'Venta', 'Usuario', $usr, 'FechaRegistro', $Fec0);
    $CobHoy  += (float)$basicas->Sumar1Fechas($mysqli, 'Cantidad',   'Pagos', 'Usuario', $usr, 'FechaRegistro', $Fec0);
    $PolizasMes += (int)$basicas->Cuenta1Fec($mysqli, 'Venta', 'Usuario', $usr, 'FechaRegistro', $mesActualIni);
}
$CobranzaSucursales = $CobHoy;

/* ===== 4) Cobranza plataforma y Mercado Pago pendiente ===== */
$CobranzaPlataforma = (float)$basicas->Sumar1Fechas($mysqli, 'Cantidad', 'Pagos', 'Usuario', 'PLATAFORMA', 'FechaRegistro', $Fec0);
$sqlPend = "SELECT COALESCE(SUM(amount),0) AS total FROM VentasMercadoPago WHERE UPPER(COALESCE(estatus_pago,'')) <> 'APLICADO'";
if ($resPend = $mysqli->query($sqlPend)) {
    $CobranzaMpPendiente = (float)($resPend->fetch_assoc()['total'] ?? 0.0);
    $resPend->close();
}
if ($nivelUsuario === 1) {
    $CobHoy = $CobranzaSucursales + $CobranzaPlataforma + $CobranzaMpPendiente;
}

/* ===== 5) Avances y colores ===== */
$AvVtas = ($MetaVta > 0) ? (($VtasHoy / $MetaVta) * 100.0) : 0.0;
$AvCob  = ($MetaCob > 0) ? (($CobHoy / $MetaCob) * 100.0) : 0.0;
$spv = $basicas->ColorPor($MetaCob, $CobHoy);
$bxo = $basicas->ColorPor($MetaVta, $VtasHoy);

/* ===== 6) Ajuste para nivel 7 (externos) ===== */
if ($nivelUsuario === 7) {
    $AvCob = $AvVtas;
}

// Variables quedan en ámbito global para que el archivo principal las lea:
// $VtasHoy, $CobHoy, $CobranzaSucursales, $CobranzaPlataforma, $CobranzaMpPendiente, $PolizasMes,
// $MetaPolizas, $ComGenHoy, $AvVtas, $AvCob, $spv, $bxo, $nivelUsuario
