<?php
//Contador de veces que se usa cada funcion para seguimiento *JCCM
require_once __DIR__ . '/FunctionUsageTracker.php';
require_once __DIR__ . '/Funciones_Basicas.php';
//creamos una variable general para las funciones
$basicas = new Basicas();

class Financieras {
    
    // Usa el trait para poder registrar el uso de los métodos.
    use UsageTrackerTrait;

    /* ========================== Métodos Privados (Helpers) ========================== */

    /**
     * Sanitiza un valor para su uso en consultas SQL.
     */
    public function esc($c0, $valor) {
        $this->trackUsage();  // Registra el uso de este método.
        return mysqli_real_escape_string($c0, $valor);
    }

    /**
     * Obtiene el registro de venta dado su ID.
     */
    public function getVenta($c0, $Vta) {
        $this->trackUsage();  // Registra el uso de este método.
        $Vta = $this->esc($c0, $Vta);
        $sql = "SELECT * FROM `Venta` WHERE `Id` = '$Vta'";
        $res = mysqli_query($c0, $sql);
        if ($res && $venta = mysqli_fetch_assoc($res)) {
            return $venta;
        }
        return null;
    }

    /**
     * Obtiene datos del producto (por ejemplo, TasaAnual y PlazoPagos) usando la función Basicas.
     */
    public function getProductoData($c0, $Producto) {
        $this->trackUsage();  // Registra el uso de este método.
                    // 3) Instancio Basicas para poder llamar a BuscarCampos
        require_once 'Funciones_Basicas.php';   // Ajusta la ruta
        $basicas = new Basicas();
        // Se espera que $basicas->BuscarCampos retorne los valores necesarios.
        $TasaAnual = $basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $Producto);
        $PlazoPagos = $basicas->BuscarCampos($c0, "PlazoPagos", "Productos", "Producto", $Producto);
        return [
            'TasaAnual'  => $TasaAnual,
            'PlazoPagos' => $PlazoPagos
        ];
    }

    /* ========================== Métodos Públicos ========================== */

    /**
     * Retorna la liga de MercadoPago para el producto $Pr y plazo $Pl.
     */
    public function HashMP($c0, $Pr, $Pl) {
        $this->trackUsage();  // Registra el uso de este método.
        $Pr = $this->esc($c0, $Pr);
        $Pl = $this->esc($c0, $Pl);
        $sql = "SELECT `Liga` FROM `MercadoPago` WHERE `Producto` = '$Pr' AND `Plazo` = '$Pl'";
        $res = mysqli_query($c0, $sql);
        if ($res && $Reg = mysqli_fetch_assoc($res)) {
            return $Reg["Liga"];
        }
        return null;
    }

    /**
     * Calcula el pago periódico (cuota fija) de un crédito.
     * Fórmula: Pago = (I0 * t * (1+t)^n) / ((1+t)^n - 1)
     * @param float $tasa  Tasa anual (en porcentaje)
     * @param int   $Periodo Número de pagos
     * @param float $I0     Valor a financiar
     */
    public function PagoSI($tasa, $Periodo, $I0) {
        $this->trackUsage();  // Registra el uso de este método.
        // Convertir tasa anual a tasa mensual en decimal
        $tm = ($tasa / 100) / 12;
        if ($tm == 0 || $Periodo <= 0) {
            return round($I0 / max($Periodo, 1), 2);
        }
        $factor = pow(1 + $tm, $Periodo);
        $pago = ($I0 * $tm * $factor) / ($factor - 1);
        return round($pago, 2);
    }

    /**
     * Suma la columna $c1 de la tabla $d1 bajo las condiciones:
     * `$d2` = $d3, `$d4` = $d5 y excluye registros con status 'Mora'.
     */
    public function SumPag2Con($c0, $c1, $d1, $d2, $d3, $d4, $d5) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $this->esc($c0, $d3);
        $d5 = $this->esc($c0, $d5);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` 
                WHERE `$d2` = '$d3' AND `$d4` = '$d5' AND `status` != 'Mora'";
        $res = mysqli_query($c0, $sql);
        if ($res && $Reg = mysqli_fetch_assoc($res)) {
            return $Reg['total'];
        }
        return 0;
    }

    /**
     * Suma la columna $c1 de la tabla $d1 bajo la condición `$d2` = $d3 y con status distinto de 'Mora'.
     */
    public function SumarPagos($c0, $c1, $d1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $this->esc($c0, $d3);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` 
                WHERE `$d2` = '$d3' AND `status` != 'Mora'";
        $res = mysqli_query($c0, $sql);
        if ($res && $Reg = mysqli_fetch_assoc($res)) {
            return $Reg['total'];
        }
        return 0;
    }

    /**
     * Suma la columna $c1 de la tabla $d1 bajo la condición `$d2` = $d3 y con status igual a 'Mora'.
     */
    public function SumarMora($c0, $c1, $d1, $d2, $d3) {
        $this->trackUsage();  // Registra el uso de este método.
        $d3 = $this->esc($c0, $d3);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1` 
                WHERE `$d2` = '$d3' AND `status` = 'Mora'";
        $res = mysqli_query($c0, $sql);
        if ($res && $Reg = mysqli_fetch_assoc($res)) {
            return $Reg['total'];
        }
        return 0;
    }

    /**
     * Calcula el valor total a pagar de un crédito.
     * Recupera la venta, obtiene la tasa (mensual) del producto y multiplica la cuota periódica por el número de pagos.
     */
    public function PagoCredito($c0, $Vta) {
        $this->trackUsage();  // Registra el uso de este método.
        if (!$venta = $this->getVenta($c0, $Vta)) {
            return 0;
        }
        $Producto = $venta["Producto"];
            // 3) Instancio Basicas para poder llamar a BuscarCampos
        require_once 'Funciones_Basicas.php';   // Ajusta la ruta
        $basicas = new Basicas();
        // Se obtiene la tasa anual del producto y se convierte a mensual
        $TasaAnual = $basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $Producto);
        $tasaMensual = ($TasaAnual / 12);
        // Se calcula el pago periódico
        $pagoPeriodo = $this->PagoSI($tasaMensual, $venta["NumeroPagos"], $venta["CostoVenta"]);
        $valorTotal = $pagoPeriodo * $venta["NumeroPagos"];
        return round($valorTotal, 2);
    }

/**
 * Calcula el saldo actual de un crédito.
 * Se toma el costo de venta y se descuenta el valor acumulado de los pagos, aplicando un factor de interés compuesto
 * según los días transcurridos desde el último pago (o desde la venta si no hay pagos).
 * Se usa 86400 segundos/día para la conversión.
 */
public function SaldoCredito($c0, $Vta) {
    $this->trackUsage();

    // 1) Obtener la venta
    if (! $venta = $this->getVenta($c0, $Vta)) {
        return 0;
    }

    // 2) Fechas: hoy, venta y último pago
    $fechaHoy        = strtotime(date("Y-m-d"));
    $fechaVenta      = strtotime($venta['FechaRegistro']);

    require_once 'Funciones_Basicas.php';
    $basicas         = new Basicas();
    $ultimoPagoFecha = $basicas->Max1Dat($c0, "FechaRegistro", "Pagos", "IdVenta", $venta['Id']);
    $fechaUltimoPago = $ultimoPagoFecha
        ? strtotime($ultimoPagoFecha)
        : $fechaVenta;

    // 3) Días transcurridos
    $diasDesdeVenta       = floor(($fechaHoy - $fechaVenta) / 86400);
    $diasDesdeUltimoPago  = floor(($fechaHoy - $fechaUltimoPago) / 86400);

    // 4) Datos del producto
    $datosProd    = $this->getProductoData($c0, $venta["Producto"]);
    $tasaAnual    = $datosProd['TasaAnual'];
    $plazoPagos   = $datosProd['PlazoPagos'] ?: 1;  // evita división por cero

    // 5) Tasa diaria en decimal
    $tasaDiaria = ($tasaAnual / 12) / $plazoPagos;
    $i          = $tasaDiaria / 100;

    // 6) Factores de interés (asegurar base > 0)
    $base = 1 + $i;
    if ($base <= 0) {
        // imposible o tasa = -100%
        return round((float)$venta["CostoVenta"], 2);
    }
    $factorUltimoPago = pow($base, $diasDesdeUltimoPago);
    $factorVenta      = pow($base, $diasDesdeVenta);

    // 7) Acumulado de pagos y capital pendiente
    $totalPagos      = $this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $venta["Id"]);
    if ($factorUltimoPago == 0) {
        // no crece nada
        return 0;
    }
    $valorAcumulado  = $totalPagos / $factorUltimoPago;
    $capitalPendiente= $venta["CostoVenta"] - $valorAcumulado;

    // 8) Ajustar capital según factor de venta
    $saldoActual = $capitalPendiente * $factorVenta;

    return round($saldoActual, 2);
}


    /**
     * Calcula el pago que debe dar el cliente, comparando el saldo, el valor total a pagar y otros parámetros.
     */
    public function Pago($c0, $IdVta) {
        $this->trackUsage();  // Registra el uso de este método.
        $IdVta = $this->esc($c0, $IdVta);
        $totalPagos = $this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $IdVta);
        $valorCredito = $this->PagoCredito($c0, $IdVta);
        $saldo = $this->SaldoCredito($c0, $IdVta);
                    // 3) Instancio Basicas para poder llamar a BuscarCampos
        require_once 'Funciones_Basicas.php';   // Ajusta la ruta
        $basicas = new Basicas();
        $numPagos = $basicas->BuscarCampos($c0, "NumeroPagos", "Venta", "Id", $IdVta);
        $producto = $basicas->BuscarCampos($c0, "Producto", "Venta", "Id", $IdVta);
        $TasaAnual = $basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $producto) / 12;
        $CostoVenta = $basicas->BuscarCampos($c0, "CostoVenta", "Venta", "Id", $IdVta);
        // Se calcula el pago periódico normal
        $pagoNormal = $this->PagoSI($TasaAnual, $numPagos, $CostoVenta) / 2;
        if ($saldo >= $valorCredito) {
            $pagosRealizados = $totalPagos / $pagoNormal;
            $pagosRestantes = $numPagos - $pagosRealizados;
            return round($saldo / $pagosRestantes, 2);
        } else {
            $diferencia = $totalPagos - $valorCredito;
            return ($diferencia >= 0) ? 0 : round($pagoNormal, 2);
        }
    }

    /**
     * Retorna la cantidad de pagos pendientes (como número entero) para la venta $IdVta.
     */
    public function PagosPend($c0, $IdVta) {
        $this->trackUsage();  // Registra el uso de este método.
        $IdVta = $this->esc($c0, $IdVta);
        $totalPagos = $this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $IdVta);
        $valorCredito = $this->PagoCredito($c0, $IdVta);
                    // 3) Instancio Basicas para poder llamar a BuscarCampos
        require_once 'Funciones_Basicas.php';   // Ajusta la ruta
        $basicas = new Basicas();
        $numPagos = $basicas->BuscarCampos($c0, "NumeroPagos", "Venta", "Id", $IdVta);
        $producto = $basicas->BuscarCampos($c0, "Producto", "Venta", "Id", $IdVta);
        $CostoVenta = $basicas->BuscarCampos($c0, "CostoVenta", "Venta", "Id", $IdVta);
        $TasaAnual = $basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $producto) / 12;
        $pagoNormal = $this->PagoSI($TasaAnual, $numPagos, $CostoVenta);
        $pagosRealizados = $totalPagos / $pagoNormal;
        $pagosRestantes = $numPagos - $pagosRealizados;
        return round($pagosRestantes, 0, PHP_ROUND_HALF_DOWN);
    }

    /**
     * Calcula la mora de un pago, añadiéndole un 10% al valor.
     */
    public function Mora($Pag) {
        $this->trackUsage();  // Registra el uso de este método.
        return round($Pag * 1.10, 2);
    }

    /**
     * Simula un crédito basado en los datos de un contacto ($IdCnc).
     */
    public function SimulaCredi($c0, $IdCnc) {
        $this->trackUsage();  // Registra el uso de este método.
        $IdCnc = $this->esc($c0, $IdCnc);
        $sql = "SELECT * FROM `Contacto` WHERE `id` = '$IdCnc'";
        $res = mysqli_query($c0, $sql);
        if ($res && $contacto = mysqli_fetch_assoc($res)) {
            $tasaAnual = $basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $contacto['Producto']);
            $tasaMensual = $tasaAnual / 12;
            $pagoPeriodo = $this->PagoSI($tasaMensual, $contacto['Periodo'], $contacto['Cantidad']);
            return $pagoPeriodo * $contacto['Periodo'];
        }
        return 0;
    }

    /**
     * Actualiza las ventas (cambia el status) según condiciones de tiempo y pagos.
     * La función recorre cada venta (usando un contador desde 1 hasta el máximo de ventas)
     * y actualiza su status de PREVENTA a COBRANZA, ACTIVACION o ACTIVO.
     */
    public function actualizaVts($c0) {
        $this->trackUsage();  // Registra el uso de este método.
        // Reinicia la tabla de comisiones (según la lógica del negocio)
        mysqli_query($c0, "TRUNCATE TABLE `Comisiones`");
        $Hoy = strtotime(date("Y-m-d"));
        $maxVenta = $basicas->MaxDat($c0, "Id", "Venta");
        for ($ventaId = 1; $ventaId <= $maxVenta; $ventaId++) {
            $SuPag = $this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $ventaId);
            $saldo = $this->PagoCredito($c0, $ventaId);
            $venta = $basicas->getVentaStatic($c0, $ventaId); // Suponiendo que Basicas tiene un método estático para obtener la venta.
            if ($venta) {
                $ultimoPagoFecha = $basicas->Max1Dat($c0, "FechaRegistro", "Pagos", "IdVenta", $venta['Id']);
                if ($venta['Status'] == "PREVENTA") {
                    if (empty($SuPag)) {
                        $FecVta = $basicas->BuscarCampos($c0, "FechaRegistro", "Venta", "Id", $ventaId);
                        $fechaLimite = strtotime($FecVta . " + 180 days");
                        if ($Hoy >= $fechaLimite) {
                            $basicas->ActCampo($c0, "Venta", "Usuario", "SISTEMA", $ventaId);
                        }
                    } else {
                        $basicas->ActCampo($c0, "Venta", "Status", "COBRANZA", $ventaId);
                    }
                } elseif ($venta['Status'] == "COBRANZA") {
                    $fechaCanc = strtotime(date("Y-m-d", strtotime($ultimoPagoFecha . " + 90 days")));
                    if ($Hoy > $fechaCanc) {
                        $basicas->ActCampo($c0, "Venta", "Status", "CANCELADO", $ventaId);
                    } elseif ($SuPag >= $saldo) {
                        $basicas->ActCampo($c0, "Venta", "Status", "ACTIVACION", $ventaId);
                    }
                } elseif ($venta['Status'] == "ACTIVACION") {
                    $fechaAct = strtotime($ultimoPagoFecha . " + 30 days");
                    if ($Hoy > $fechaAct) {
                        $basicas->ActCampo($c0, "Venta", "Status", "ACTIVO", $ventaId);
                        // Envío de correo de activación
                        $Asunto = '¡BIENVENIDO A KASU!';
                        $IdContact = $basicas->BuscarCampos($c0, "IdContact", "Venta", "Id", $ventaId);
                        $DirUrl = base64_encode($IdContact);
                        $Cte = $basicas->BuscarCampos($c0, "Nombre", "Usuario", "IdContact", $IdContact);
                        $Address = $basicas->BuscarCampos($c0, "Mail", "Contacto", "Id", $IdContact);
                        if (!empty($Address)) {
                            $Mensaje = Correo::Mensaje($Asunto, $Cte, $DirUrl, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $IdContact);
                            Correo::EnviarCorreo($Cte, $Address, $Asunto, $Mensaje);
                        }
                    }
                }
            }
        }
        return $maxVenta;
    }

    /**
     * Actualiza la tabla de comisiones. Se calcula la comisión de cada vendedor (o equipo)
     * por semana, utilizando ventas y pagos acumulados, y se registra el resultado en la tabla.
     */
    public function ActualComis($c0) {
        $this->trackUsage();  // Registra el uso de este método.
        mysqli_query($c0, "TRUNCATE TABLE `Comisiones`");
        $maxEmpleado = $basicas->MaxDat($c0, "id", "Empleados");
        // Se determina la semana actual (último domingo y la semana previa)
        $fechaFinSemana = date("Y-m-d", strtotime("last Sunday"));
        $fechaIniSemana = date("Y-m-d", strtotime($fechaFinSemana . " - 7 days"));
        // Se determina la cantidad de semanas transcurridas desde la primera venta
        $fechaPrimeraVenta = $basicas->MinDat($c0, "FechaRegistro", "Venta");
        $diasTranscurridos = (strtotime($fechaFinSemana) - strtotime($fechaPrimeraVenta)) / 86400;
        $semanas = round($diasTranscurridos / 7, 0);
        // Procesar cada semana
        for ($sem = 1; $sem <= $semanas; $sem++) {
            $inicioSemana = date("Y-m-d", strtotime($fechaPrimeraVenta . " + " . (($sem - 1) * 7) . " days"));
            $finSemana = date("Y-m-d", strtotime($fechaPrimeraVenta . " + " . ($sem * 7) . " days"));
            // Para cada empleado (vendedor activo)
            for ($emp = 1; $emp <= $maxEmpleado; $emp++) {
                $sqlEmp = "SELECT * FROM `Empleados` WHERE `Id` = '$emp' AND `Nombre` != 'Vacante'";
                $resEmp = mysqli_query($c0, $sqlEmp);
                if ($resEmp && $empleado = mysqli_fetch_assoc($resEmp)) {
                    $Vendedor = $empleado['IdUsuario'];
                    $Equipo = $empleado['Equipo'];
                    // Se cuentan las ventas (uni) y se suman los valores durante la semana
                    $ventasUni = $basicas->ContarFechas4($c0, "Venta", "Usuario", $Vendedor, "FechaRegistro", $finSemana, "FechaRegistro", $inicioSemana, "Status", "PREVENTA", "Producto", $empleado['Producto']);
                    $ventasVal = $basicas->SumarFechasIndis($c0, "CostoVenta", "Venta", "Usuario", $Vendedor, "FechaRegistro", $finSemana, "FechaRegistro", $inicioSemana, "Status", "PREVENTA");
                    // Se suman los pagos en la semana
                    $cobranzaVal = $basicas->SumarFechas($c0, "Cantidad", "Pagos", "Usuario", $Vendedor, "FechaRegistro", $finSemana, "FechaRegistro", $inicioSemana);
                    // Calcular factores para comisión (por ejemplo, porcentajes según el nivel)
                    $sk = $ventasVal / 10000;
                    $xy = $cobranzaVal / 10000;
                    // Determinar comisión por ventas y por cobranza según el nivel
                    if ($empleado['Nivel'] >= 7) {
                        $comVtas = $ventasVal;
                    } else {
                        // Para niveles 6 a 1 se aplican porcentajes que se obtienen mediante $basicas->BuscarCampos
                        $porcentajeVtas = $basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Colocacion") / 100;
                        $comVtas = round($ventasVal * $porcentajeVtas, 2);
                    }
                    $porcentajeCob = $basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Cobranza") / 100;
                    $comCob = round($xy * $porcentajeCob, 2);
                    // Solo se registra si hay comisión por ventas o cobranza
                    if ($comVtas > 0 || $comCob > 0) {
                        $datCob = array(
                            "IdVendedor" => $Vendedor,
                            "Equipo"     => $Equipo,
                            "Inicio"     => $inicioSemana,
                            "FIn"        => $finSemana,
                            "VtasUni"    => $ventasUni,
                            "VtasVal"    => $ventasVal,
                            "CobUni"     => 0, // Si no se tiene el número de cobranza, se puede dejar en 0
                            "CobVal"     => $cobranzaVal,
                            "ComVtas"    => $comVtas,
                            "ComCob"     => $comCob
                        );
                        $basicas->InsertCampo($c0, "Comisiones", $datCob);
                    }
                }
            }
        }
        // Se procesan comisiones a nivel de equipos para empleados con Nivel <= 4
        $sqlEquipos = "SELECT * FROM `Empleados` WHERE `Nivel` <= 4 AND `Nombre` != 'Vacante'";
        $resEquipos = $c0->query($sqlEquipos);
        foreach ($resEquipos as $empleado) {
            $sumNVtas = $basicas->Sumar1Fechas($c0, "VtasUni", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
            $sumVtas   = $basicas->Sumar1Fechas($c0, "VtasVal", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
            $sumNCob   = $basicas->Sumar1Fechas($c0, "CobUni", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
            $sumCob    = $basicas->Sumar1Fechas($c0, "CobVal", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
            $sk = $sumVtas / 10000;
            $xy = $sumCob / 10000;
            $porVtas = $basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Colocacion") / 100;
            $porCob  = $basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Cobranza") / 100;
            $comCol = round($sk * $porVtas, 2);
            $comCob = round($xy * $porCob, 2);
            if ($comCol > 0 || $comCob > 0) {
                $datCob = array(
                    "IdVendedor" => $empleado['IdUsuario'],
                    "Equipo"     => $empleado['Equipo'],
                    "Inicio"     => $empleado['FechaAlta'],
                    "FIn"        => date('Y-m-d'),
                    "VtasUni"    => $sumNVtas,
                    "VtasVal"    => $sumVtas,
                    "CobUni"     => $sumNCob,
                    "CobVal"     => $sumCob,
                    "ComVtas"    => $comCol,
                    "ComCob"     => $comCob
                );
                $basicas->InsertCampo($c0, "Comisiones", $datCob);
            }
        }
    }
}
?>
