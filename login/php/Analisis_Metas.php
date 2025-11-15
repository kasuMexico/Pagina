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
$SUeldos    = 0.0;  // Sueldos por pagar
$comisiones = 0.0;  // Total de comisiones acumuladas
$ComGenHoy  = 0.0;  // Comisiones generadas hoy (por contacto)

/* ===== 2) Metas del usuario ===== */
$IdAsig  = $basicas->Buscar1Fechas($mysqli, 'Id', 'Asignacion', 'Usuario', $_SESSION['Vendedor'], 'Fecha', $Fec0);
$MetaVta = (float)$basicas->BuscarCampos($mysqli, 'MVtas',      'Asignacion', 'Id', $IdAsig);
$MetaCob = (float)$basicas->BuscarCampos($mysqli, 'MCob',       'Asignacion', 'Id', $IdAsig);
$Normali = (float)$basicas->BuscarCampos($mysqli, 'Normalidad', 'Asignacion', 'Id', $IdAsig);

/* ===== 3) Empleados activos ===== */
$sqlEmpl    = "SELECT * FROM Empleados WHERE Nombre <> 'Vacante'";
$resultEmpl = $mysqli->query($sqlEmpl);
if ($resultEmpl === false) {
    // En PHP 8.2 mysqli lanza excepción si está activado MYSQLI_REPORT_STRICT.
    // Aquí solo retornamos para no romper el panel.
    return;
}

while ($empleado = $resultEmpl->fetch_assoc()) {
    $idEmpleado = (string)$empleado['IdUsuario'];
    $nivelEmp   = (int)$empleado['Nivel'];

    // Nivel >= 5: suma directo ventas/pagos del usuario
    if ($nivelEmp >= 5) {
        $ventas = (float)$basicas->Sumar1Fechas($mysqli, 'CostoVenta', 'Venta', 'Usuario', $idEmpleado, 'FechaRegistro', $Fec0);
        $pagos  = (float)$basicas->Sumar1Fechas($mysqli, 'Cantidad',   'Pagos', 'Usuario', $idEmpleado, 'FechaRegistro', $Fec0);
        $VtasHoy += $ventas;
        $CobHoy  += $pagos;

    // Nivel <= 4: suma de su equipo
    } elseif ($nivelEmp <= 4) {
        $equipoId  = (int)$empleado['Id']; // Id interno del empleado como jefe/equipo
        $sqlEquipo = "SELECT * FROM Empleados WHERE Equipo = {$equipoId}";
        if ($resultEquipo = $mysqli->query($sqlEquipo)) {
            while ($miembro = $resultEquipo->fetch_assoc()) {
                $usrEq  = (string)$miembro['IdUsuario'];
                $ventas = (float)$basicas->Sumar1Fechas($mysqli, 'CostoVenta', 'Venta', 'Usuario', $usrEq, 'FechaRegistro', $Fec0);
                $pagos  = (float)$basicas->Sumar1Fechas($mysqli, 'Cantidad',   'Pagos', 'Usuario', $usrEq, 'FechaRegistro', $Fec0);
                $VtasHoy += $ventas;
                $CobHoy  += $pagos;
            }
        }
    }

    /* ===== 3.1) Sueldo por nivel =====
     * Mantengo el orden original para no alterar la lógica existente.
     */
    if ($nivelEmp >= 7) {
        $Sueldo = 0;
    } elseif ($nivelEmp <= 6) {
        $Sueldo = 6000;
    } elseif ($nivelEmp <= 5) {
        $Sueldo = 6000;
    } elseif ($nivelEmp <= 4) {
        $Sueldo = 8000;
    } elseif ($nivelEmp <= 3) {
        $Sueldo = 10000;
    } elseif ($nivelEmp <= 2) {
        $Sueldo = 15000;
    } elseif ($nivelEmp <= 1) {
        $Sueldo = 20000;
    } else {
        $Sueldo = 0;
    }
    $SUeldos += (float)$Sueldo;

    /* ===== 4) Comisiones y saldo ===== */
    $comVtas   = (float)$basicas->Sumar1cond($mysqli, 'ComVtas',  'Comisiones',        'IdVendedor', $idEmpleado);
    $comCob    = (float)$basicas->Sumar1cond($mysqli, 'ComCob',   'Comisiones',        'IdVendedor', $idEmpleado);
    $pagosCom  = (float)$basicas->Sumar1cond($mysqli, 'Cantidad', 'Comisiones_pagos',  'IdVendedor', $idEmpleado);
    $totalCom  = $comVtas + $comCob;
    $Saldo     = $totalCom - $pagosCom;
    if ($Saldo < 0) { $Saldo = 0.0; }
    $comisiones += $Saldo;
}

/* ===== 5) Ajuste de sueldos por quincena ===== */
$Quincena     = strtotime($Fec0 . ' +15 days');
$hoyTimestamp = strtotime(date('Y-m-d'));
if ($hoyTimestamp > $Quincena) {
    $SUeldos /= 2;
}

/* ===== 6) Avances y colores ===== */
$AvVtas = ($VtasHoy > 0) ? ($MetaVta / $VtasHoy) : 0.0;     // ratio, no %
$AvCob  = ($MetaCob > 0) ? (($CobHoy / $MetaCob) * 100.0) : 0.0; // porcentaje %

$spv = $basicas->ColorPor($MetaCob, $CobHoy);
$bxo = $basicas->ColorPor($MetaVta, $VtasHoy);

/* ===== 7) Ajuste para nivel 7 (externos) ===== */
$nivelUsuario = (int)$basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $_SESSION['Vendedor']);
if ($nivelUsuario === 7) {
    $AvCob = $AvVtas;
}

// Variables quedan en ámbito global para que el archivo principal las lea:
// $VtasHoy, $CobHoy, $SUeldos, $comisiones, $ComGenHoy, $AvVtas, $AvCob, $spv, $bxo
