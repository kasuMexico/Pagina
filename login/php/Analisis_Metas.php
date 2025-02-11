<?php
/**************************************************************************************************
 * BLOQUE: ANÁLISIS DE METAS Y COMISIONES
 * Este script calcula las ventas acumuladas, pagos, sueldos y comisiones del mes en curso,
 * utilizando funciones definidas en la clase Basicas (y Financieras, en su caso).
 **************************************************************************************************/

// Se asume que la sesión ya se inició y que se cargaron las librerías necesarias, por ejemplo:
// session_start();
// require_once '../../eia/librerias.php';
// date_default_timezone_set('America/Mexico_City');

// Definir la fecha base: primer día del mes (formato "Y-m-d")
$Fec0 = date("Y-m-d", strtotime('first day of this month'));
//echo "DEBUG: Fecha base (primer día del mes): $Fec0<br>";

// Inicializar acumuladores
$VtasHoy    = 0.0;  // Ventas acumuladas del mes
$CobHoy     = 0.0;  // Pagos acumulados del mes
$SUeldos    = 0.0;  // Sueldos por pagar
$comisiones = 0.0;  // Total de comisiones acumuladas
$ComGenHoy  = 0.0;  // Comisiones generadas hoy (por contacto)

// Obtener las metas asignadas al usuario (vendedor actual)
// Se utiliza la función Buscar1Fechas para obtener el ID de asignación para la fecha base
$IdAsig  = $basicas->Buscar1Fechas($mysqli, "Id", "Asignacion", "Usuario", $_SESSION["Vendedor"], "Fecha", $Fec0);
if (empty($IdAsig)) {
    //echo "DEBUG: No se encontró asignación para el usuario " . htmlspecialchars($_SESSION["Vendedor"]) . " en la fecha $Fec0.<br>";
} else {
    //echo "DEBUG: ID de asignación obtenido: $IdAsig<br>";
}

$MetaVta = $basicas->BuscarCampos($mysqli, "MVtas", "Asignacion", "Id", $IdAsig);
$MetaCob = $basicas->BuscarCampos($mysqli, "MCob", "Asignacion", "Id", $IdAsig);
$Normali = $basicas->BuscarCampos($mysqli, "Normalidad", "Asignacion", "Id", $IdAsig);

//echo "DEBUG: Meta de ventas: $MetaVta, Meta de cobranza: $MetaCob, Normalidad: $Normali<br>";

// CONSULTA: Obtener todos los empleados activos (excluyendo aquellos cuyo Nombre es 'Vacante')
$sqlEmpl = "SELECT * FROM Empleados WHERE Nombre != 'Vacante'";
$resultEmpl = $mysqli->query($sqlEmpl);

if ($resultEmpl === false) {
    die("ERROR en consulta de empleados: " . $mysqli->error);
}

// Recorrer cada empleado para acumular ventas, pagos, sueldos y comisiones
while ($empleado = $resultEmpl->fetch_assoc()) {
    $idEmpleado = $empleado["IdUsuario"];
    $nivelEmp = (int)$empleado['Nivel'];

    //echo "DEBUG: Procesando empleado $idEmpleado (Nivel $nivelEmp)<br>";

    // Si el empleado es de Nivel ≥ 5, se suman ventas y pagos directamente
    if ($nivelEmp >= 5) {
        $ventas = (float)$basicas->Sumar1Fechas($mysqli, "CostoVenta", "Venta", "Usuario", $idEmpleado, "FechaRegistro", $Fec0);
        $pagos  = (float)$basicas->Sumar1Fechas($mysqli, "Cantidad", "Pagos", "Usuario", $idEmpleado, "FechaRegistro", $Fec0);
        $VtasHoy += $ventas;
        $CobHoy  += $pagos;
        //echo "DEBUG: Empleado $idEmpleado (Nivel>=5): Ventas = $ventas, Pagos = $pagos<br>";
    }
    // Si el empleado es de Nivel ≤ 4, se suman ventas y pagos de los miembros de su equipo
    elseif ($nivelEmp <= 4) {
        $equipoId = $mysqli->real_escape_string($empleado['Id']);
        $sqlEquipo = "SELECT * FROM Empleados WHERE Equipo = '$equipoId'";
        $resultEquipo = $mysqli->query($sqlEquipo);
        if ($resultEquipo) {
            while ($miembro = $resultEquipo->fetch_assoc()) {
                $ventas = (float)$basicas->Sumar1Fechas($mysqli, "CostoVenta", "Venta", "Usuario", $miembro["IdUsuario"], "FechaRegistro", $Fec0);
                $pagos  = (float)$basicas->Sumar1Fechas($mysqli, "Cantidad", "Pagos", "Usuario", $miembro["IdUsuario"], "FechaRegistro", $Fec0);
                $VtasHoy += $ventas;
                $CobHoy  += $pagos;
                //echo "DEBUG: Miembro de equipo {$miembro['IdUsuario']} (Nivel<=4): Ventas = $ventas, Pagos = $pagos<br>";
            }
        }
    }

    // Determinar el sueldo asignado según el Nivel del empleado
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
    $SUeldos += $Sueldo;
    //echo "DEBUG: Empleado $idEmpleado: Sueldo asignado = $Sueldo<br>";

    // Calcular comisiones:
    $comVtas = (float)$basicas->Sumar1cond($mysqli, "ComVtas", "Comisiones", "IdVendedor", $idEmpleado);
    $comCob  = (float)$basicas->Sumar1cond($mysqli, "ComCob", "Comisiones", "IdVendedor", $idEmpleado);
    $pagosCom = (float)$basicas->Sumar1cond($mysqli, "Cantidad", "Comisiones_pagos", "IdVendedor", $idEmpleado);
    $totalComision = $comVtas + $comCob;
    $Saldo = $totalComision - $pagosCom;
    if ($Saldo < 0) {
        $Saldo = 0;
    }
    $comisiones += $Saldo;
    //echo "DEBUG: Empleado $idEmpleado: Comisiones generadas = $totalComision, Pagos de comisiones = $pagosCom, Saldo = $Saldo<br>";
}

// Ajustar sueldos: si la fecha actual es posterior al día 15 del mes, se divide el total de sueldos entre 2
$Quincena = strtotime($Fec0 . ' +15 days');
$hoyTimestamp = strtotime(date("Y-m-d"));
if ($hoyTimestamp > $Quincena) {
    $SUeldos /= 2;
    //echo "DEBUG: Sueldos ajustados (después del día 15): $SUeldos<br>";
}

// Cálculo de avances:
// Porcentaje de ventas: meta de ventas / ventas acumuladas (si hay ventas)
$AvVtas = ($VtasHoy > 0) ? $MetaVta / $VtasHoy : 0;
// Porcentaje de cobranza: (pagos acumulados / meta de cobranza) * 100
$AvCob = ($MetaCob > 0) ? ($CobHoy / $MetaCob) * 100 : 0;

// Obtener colores según el avance (usando la función ColorPor)
$spv = $basicas->ColorPor($MetaCob, $CobHoy);
$bxo = $basicas->ColorPor($MetaVta, $VtasHoy);

// Ajuste para vendedores externos (Nivel 7): igualar avance de cobranza al de ventas
$nivelUsuario = (int)$basicas->BuscarCampos($mysqli, "Nivel", "Empleados", "IdUsuario", $_SESSION["Vendedor"]);
if ($nivelUsuario === 7) {
    $AvCob = $AvVtas;
    //echo "DEBUG: Vendedor externo, ajuste de avance de cobranza: $AvCob<br>";
}

/*/ Mostrar resultados de depuración finales
echo "DEBUG: Ventas acumuladas del mes: $VtasHoy<br>";
echo "DEBUG: Pagos acumulados del mes: $CobHoy<br>";
echo "DEBUG: Meta de ventas: $MetaVta<br>";
echo "DEBUG: Meta de cobranza: $MetaCob<br>";
echo "DEBUG: Avance de ventas: " . round($AvVtas * 100, 2) . "%<br>";
echo "DEBUG: Avance de cobranza: " . round($AvCob, 2) . "%<br>";
echo "DEBUG: Total de sueldos por pagar: $SUeldos<br>";
echo "DEBUG: Total de comisiones acumuladas: $comisiones<br>";
echo "DEBUG: Comisiones generadas hoy (por contacto): " . number_format($ComGenHoy, 2, '.', ',') . "<br>";
*/
?>
