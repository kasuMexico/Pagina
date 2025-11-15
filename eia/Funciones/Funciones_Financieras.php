<?php
/**
 * Financieras.php
 * Funciones financieras y de apoyo de ventas.
 * Fecha: 2025-11-04
 * Revisado por: JCCM
 */

require_once __DIR__ . '/FunctionUsageTracker.php';
require_once __DIR__ . '/Funciones_Basicas.php';

// Instancia global reutilizable (respetando dependencias existentes)
$basicas = $basicas ?? new Basicas();

class Financieras {

    // Contador de uso
    use UsageTrackerTrait;

    /** @var object|null Instancia de Correo inyectable */
    private $correo = null;

    /** Permite inyectar la instancia de Correo al construir. Opcional. */
    public function __construct($correoInstance = null) {
        if ($correoInstance !== null) {
            $this->correo = $correoInstance;
        }
    }

    /* ========================== Helpers ========================== */

    /** Sanitiza un valor para SQL. */
    public function esc($c0, $valor) {
        $this->trackUsage();
        if ($valor === null) return '';
        return mysqli_real_escape_string($c0, (string)$valor);
    }

    /** Obtiene una venta por Id. */
    public function getVenta($c0, $Vta) {
        $this->trackUsage();
        $Vta = $this->esc($c0, $Vta);
        $sql = "SELECT * FROM `Venta` WHERE `Id` = '$Vta' LIMIT 1";
        $res = mysqli_query($c0, $sql);
        if ($res && ($venta = mysqli_fetch_assoc($res))) return $venta;
        return null;
    }

    /** Datos clave del producto. */
    public function getProductoData($c0, $Producto) {
        $this->trackUsage();
        global $basicas;
        $TasaAnual  = $basicas->BuscarCampos($c0, "TasaAnual",  "Productos", "Producto", $Producto);
        $PlazoPagos = $basicas->BuscarCampos($c0, "PlazoPagos", "Productos", "Producto", $Producto);
        return [
            'TasaAnual'  => (float)$TasaAnual,
            'PlazoPagos' => (int)$PlazoPagos
        ];
    }

    /** Envío de correo de activación por instancia. Fallback a mail(). */
    private function enviarCorreoActivacion(string $cte, string $address, string $asunto, string $idContact, string $dirUrl): bool {
        // 1) Usa $this->correo si está configurado y tiene métodos ->Mensaje y ->EnviarCorreo
        if (is_object($this->correo)) {
            $tieneMensaje = is_callable([$this->correo, 'Mensaje']);
            $tieneEnviar  = is_callable([$this->correo, 'EnviarCorreo']);
            if ($tieneMensaje && $tieneEnviar) {
                $mensaje = $this->correo->Mensaje(
                    $asunto, $cte, $dirUrl,
                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $idContact
                );
                return (bool) $this->correo->EnviarCorreo($cte, $address, $asunto, $mensaje);
            }
        }

        // 2) Si no hay $this->correo, intenta con una instancia global $correo
        if (isset($GLOBALS['correo']) && is_object($GLOBALS['correo'])) {
            $c = $GLOBALS['correo'];
            if (is_callable([$c, 'Mensaje']) && is_callable([$c, 'EnviarCorreo'])) {
                $mensaje = $c->Mensaje(
                    $asunto, $cte, $dirUrl,
                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $idContact
                );
                return (bool) $c->EnviarCorreo($cte, $address, $asunto, $mensaje);
            }
        }

        // 3) Fallback simple con mail()
        $html  = "<html><body>";
        $html .= "<p>Estimado(a) {$cte},</p>";
        $html .= "<p>Tu servicio fue activado. ID de contacto: {$idContact}.</p>";
        $html .= "<p>Acceso: {$dirUrl}</p>";
        $html .= "<p>Gracias por elegir KASU.</p>";
        $html .= "</body></html>";

        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: KASU <no-reply@kasu.com.mx>";

        return @mail($address, $asunto, $html, implode("\r\n", $headers));
    }

    /* ========================== Públicos ========================== */

    /** Liga de MercadoPago. */
    public function HashMP($c0, $Pr, $Pl) {
        $this->trackUsage();
        $Pr = $this->esc($c0, $Pr);
        $Pl = $this->esc($c0, $Pl);
        $sql = "SELECT `Liga` FROM `MercadoPago` WHERE `Producto` = '$Pr' AND `Plazo` = '$Pl' LIMIT 1";
        $res = mysqli_query($c0, $sql);
        if ($res && ($Reg = mysqli_fetch_assoc($res))) return $Reg["Liga"];
        return null;
    }

    /**
     * Pago periódico sistema francés.
     * $tasa: anual porcentaje. Internamente a mensual decimal.
     */
    public function PagoSI($tasa, $Periodo, $I0) {
        $this->trackUsage();
        $Periodo = (int)$Periodo;
        $I0 = (float)$I0;
        $tm = ($tasa / 100) / 12; // mensual decimal
        if ($Periodo <= 0) return round($I0, 2);
        if ($tm == 0.0)     return round($I0 / $Periodo, 2);
        $factor = pow(1 + $tm, $Periodo);
        $pago = ($I0 * $tm * $factor) / ($factor - 1);
        return round($pago, 2);
    }

    /** Suma condicionada excluyendo 'Mora'. */
    public function SumPag2Con($c0, $c1, $d1, $d2, $d3, $d4, $d5) {
        $this->trackUsage();
        $d3 = $this->esc($c0, $d3);
        $d5 = $this->esc($c0, $d5);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1`
                WHERE `$d2` = '$d3' AND `$d4` = '$d5' AND `status` != 'Mora'";
        $res = mysqli_query($c0, $sql);
        if ($res && ($Reg = mysqli_fetch_assoc($res)) && $Reg['total'] !== null) return (float)$Reg['total'];
        return 0.0;
    }

    /** Suma pagos con status != 'Mora'. */
    public function SumarPagos($c0, $c1, $d1, $d2, $d3) {
        $this->trackUsage();
        $d3 = $this->esc($c0, $d3);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1`
                WHERE `$d2` = '$d3' AND `status` != 'Mora'";
        $res = mysqli_query($c0, $sql);
        if ($res && ($Reg = mysqli_fetch_assoc($res)) && $Reg['total'] !== null) return (float)$Reg['total'];
        return 0.0;
    }

    /** Suma pagos con status = 'Mora'. */
    public function SumarMora($c0, $c1, $d1, $d2, $d3) {
        $this->trackUsage();
        $d3 = $this->esc($c0, $d3);
        $sql = "SELECT SUM(`$c1`) AS total FROM `$d1`
                WHERE `$d2` = '$d3' AND `status` = 'Mora'";
        $res = mysqli_query($c0, $sql);
        if ($res && ($Reg = mysqli_fetch_assoc($res)) && $Reg['total'] !== null) return (float)$Reg['total'];
        return 0.0;
    }

    /** Total del crédito = cuota * número de pagos. */
    public function PagoCredito($c0, $Vta) {
        $this->trackUsage();
        $venta = $this->getVenta($c0, $Vta);
        if (!$venta) return 0.0;

        global $basicas;
        $Producto   = $venta["Producto"];
        $TasaAnual  = (float)$basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $Producto);
        $NumPagos   = (int)$venta["NumeroPagos"];
        $CostoVenta = (float)$venta["CostoVenta"];

        $pagoPeriodo = $this->PagoSI($TasaAnual, $NumPagos, $CostoVenta);
        $valorTotal  = $pagoPeriodo * $NumPagos;
        return round($valorTotal, 2);
    }

    /** Saldo aproximado a hoy con interés diario. */
    public function SaldoCredito($c0, $Vta) {
        $this->trackUsage();

        $venta = $this->getVenta($c0, $Vta);
        if (!$venta) return 0.0;

        $fechaHoy   = strtotime(date("Y-m-d"));
        $fechaVenta = strtotime($venta['FechaRegistro']);

        global $basicas;
        $ultimoPagoFecha = $basicas->Max1Dat($c0, "FechaRegistro", "Pagos", "IdVenta", $venta['Id']);
        $fechaUltimoPago = $ultimoPagoFecha ? strtotime($ultimoPagoFecha) : $fechaVenta;

        $diasDesdeVenta      = max(0, (int)floor(($fechaHoy - $fechaVenta) / 86400));
        $diasDesdeUltimoPago = max(0, (int)floor(($fechaHoy - $fechaUltimoPago) / 86400));

        $datosProd  = $this->getProductoData($c0, $venta["Producto"]);
        $tasaAnual  = (float)$datosProd['TasaAnual'];

        $i    = ($tasaAnual / 100) / 365.0; // diaria
        $base = 1 + $i;

        $factorUltimoPago = ($diasDesdeUltimoPago > 0) ? pow($base, $diasDesdeUltimoPago) : 1.0;
        $factorVenta      = ($diasDesdeVenta      > 0) ? pow($base, $diasDesdeVenta)      : 1.0;

        $totalPagos       = (float)$this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $venta["Id"]);
        $valorAcumulado   = $totalPagos / $factorUltimoPago;
        $capitalPendiente = ((float)$venta["CostoVenta"]) - $valorAcumulado;

        $saldoActual = $capitalPendiente * $factorVenta;
        return round(max(0, $saldoActual), 2);
    }

    /** Pago por periodo actual. */
    public function Pago($c0, $IdVta) {
        $this->trackUsage();
        $IdVta = $this->esc($c0, $IdVta);

        global $basicas;

        $totalPagos   = (float)$this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $IdVta);
        $valorCredito = (float)$this->PagoCredito($c0, $IdVta);
        $saldo        = (float)$this->SaldoCredito($c0, $IdVta);

        $numPagos   = (int)$basicas->BuscarCampos($c0, "NumeroPagos", "Venta", "Id", $IdVta);
        $producto   =       $basicas->BuscarCampos($c0, "Producto",    "Venta", "Id", $IdVta);
        $TasaAnual  = (float)$basicas->BuscarCampos($c0, "TasaAnual",  "Productos", "Producto", $producto);
        $CostoVenta = (float)$basicas->BuscarCampos($c0, "CostoVenta", "Venta", "Id", $IdVta);

        // Mantengo /2 si tu lógica ya consideraba quincenas
        $pagoNormal = $this->PagoSI($TasaAnual, $numPagos, $CostoVenta) / 2;

        if ($saldo >= $valorCredito) {
            $pagosRealizados = ($pagoNormal > 0) ? ($totalPagos / $pagoNormal) : 0;
            $pagosRestantes  = max(1, $numPagos - $pagosRealizados);
            return round($saldo / $pagosRestantes, 2);
        } else {
            $diferencia = $totalPagos - $valorCredito;
            return ($diferencia >= 0) ? 0.0 : round($pagoNormal, 2);
        }
    }

    /** Cantidad de pagos pendientes. */
    public function PagosPend($c0, $IdVta) {
        $this->trackUsage();
        $IdVta = $this->esc($c0, $IdVta);

        global $basicas;

        $totalPagos   = (float)$this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $IdVta);
        $valorCredito = (float)$this->PagoCredito($c0, $IdVta);

        $numPagos   = (int)$basicas->BuscarCampos($c0, "NumeroPagos", "Venta", "Id", $IdVta);
        $producto   =       $basicas->BuscarCampos($c0, "Producto",    "Venta", "Id", $IdVta);
        $CostoVenta = (float)$basicas->BuscarCampos($c0, "CostoVenta", "Venta", "Id", $IdVta);
        $TasaAnual  = (float)$basicas->BuscarCampos($c0, "TasaAnual",  "Productos", "Producto", $producto);

        $pagoNormal = $this->PagoSI($TasaAnual, $numPagos, $CostoVenta);
        $pagosRealizados = ($pagoNormal > 0) ? ($totalPagos / $pagoNormal) : 0;
        $pagosRestantes  = max(0, $numPagos - $pagosRealizados);

        return (int)round($pagosRestantes, 0, PHP_ROUND_HALF_DOWN);
    }

    /** Recargo 10%. */
    public function Mora($Pag) {
        $this->trackUsage();
        return round(((float)$Pag) * 1.10, 2);
    }

    /** Simulación desde Contacto.id. */
    public function SimulaCredi($c0, $IdCnc) {
        $this->trackUsage();
        global $basicas;

        $IdCnc = $this->esc($c0, $IdCnc);
        $sql = "SELECT * FROM `Contacto` WHERE `id` = '$IdCnc' LIMIT 1";
        $res = mysqli_query($c0, $sql);
        if ($res && ($contacto = mysqli_fetch_assoc($res))) {
            $tasaAnual   = (float)$basicas->BuscarCampos($c0, "TasaAnual", "Productos", "Producto", $contacto['Producto']);
            $Periodo     = (int)$contacto['Periodo'];
            $Cantidad    = (float)$contacto['Cantidad'];
            $pagoPeriodo = $this->PagoSI($tasaAnual, $Periodo, $Cantidad);
            return round($pagoPeriodo * $Periodo, 2);
        }
        return 0.0;
    }

    /**
     * Actualiza estatus de ventas por reglas de tiempo y pagos.
     */
    public function actualizaVts($c0) {
        $this->trackUsage();
        global $basicas;

        mysqli_query($c0, "TRUNCATE TABLE `Comisiones`");

        $Hoy = strtotime(date("Y-m-d"));
        $maxVenta = (int)$basicas->MaxDat($c0, "Id", "Venta");

        for ($ventaId = 1; $ventaId <= $maxVenta; $ventaId++) {

            $venta = $this->getVenta($c0, $ventaId);
            if (!$venta) continue;

            $SuPag        = (float)$this->SumarPagos($c0, "Cantidad", "Pagos", "IdVenta", $ventaId);
            $valorCredito = (float)$this->PagoCredito($c0, $ventaId);

            $ultimoPagoFecha = $basicas->Max1Dat($c0, "FechaRegistro", "Pagos", "IdVenta", $venta['Id']);

            if ($venta['Status'] === "PREVENTA") {
                if ($SuPag <= 0) {
                    $FecVta = $basicas->BuscarCampos($c0, "FechaRegistro", "Venta", "Id", $ventaId);
                    if ($FecVta) {
                        $fechaLimite = strtotime($FecVta . " + 180 days");
                        if ($Hoy >= $fechaLimite) {
                            $basicas->ActCampo($c0, "Venta", "Usuario", "SISTEMA", $ventaId);
                        }
                    }
                } else {
                    $basicas->ActCampo($c0, "Venta", "Status", "COBRANZA", $ventaId);
                }

            } elseif ($venta['Status'] === "COBRANZA") {
                $baseCanc  = $ultimoPagoFecha ?: $venta['FechaRegistro'];
                $fechaCanc = strtotime(date("Y-m-d", strtotime($baseCanc . " + 90 days")));
                if ($Hoy > $fechaCanc) {
                    $basicas->ActCampo($c0, "Venta", "Status", "CANCELADO", $ventaId);
                } elseif ($SuPag >= $valorCredito) {
                    $basicas->ActCampo($c0, "Venta", "Status", "ACTIVACION", $ventaId);
                }

            } elseif ($venta['Status'] === "ACTIVACION") {
                $baseAct  = $ultimoPagoFecha ?: $venta['FechaRegistro'];
                $fechaAct = strtotime($baseAct . " + 30 days");
                if ($Hoy > $fechaAct) {
                    $basicas->ActCampo($c0, "Venta", "Status", "ACTIVO", $ventaId);

                    // Correo de activación por instancia/variable
                    $Asunto    = '¡BIENVENIDO A KASU!';
                    $IdContact = $basicas->BuscarCampos($c0, "IdContact", "Venta", "Id", $ventaId);
                    $DirUrl    = base64_encode($IdContact);
                    $Cte       = $basicas->BuscarCampos($c0, "Nombre", "Usuario", "IdContact", $IdContact);
                    $Address   = $basicas->BuscarCampos($c0, "Mail",   "Contacto", "Id", $IdContact);

                    if (!empty($Address)) {
                        $this->enviarCorreoActivacion((string)$Cte, (string)$Address, $Asunto, (string)$IdContact, (string)$DirUrl);
                    }
                }
            }
        }
        return $maxVenta;
    }

    /**
     * Recalcula comisiones históricas.
     */
    public function ActualComis($c0) {
        $this->trackUsage();
        global $basicas;

        mysqli_query($c0, "TRUNCATE TABLE `Comisiones`");

        $maxEmpleado       = (int)$basicas->MaxDat($c0, "id", "Empleados");
        $fechaFinSemana    = date("Y-m-d", strtotime("last Sunday"));
        $fechaIniSemana    = date("Y-m-d", strtotime($fechaFinSemana . " - 7 days"));
        $fechaPrimeraVenta = $basicas->MinDat($c0, "FechaRegistro", "Venta");

        if (!$fechaPrimeraVenta) return;

        $diasTranscurridos = (strtotime($fechaFinSemana) - strtotime($fechaPrimeraVenta)) / 86400;
        $semanas = max(0, (int)round($diasTranscurridos / 7, 0));

        for ($sem = 1; $sem <= $semanas; $sem++) {
            $inicioSemana = date("Y-m-d", strtotime($fechaPrimeraVenta . " + " . (($sem - 1) * 7) . " days"));
            $finSemana    = date("Y-m-d", strtotime($fechaPrimeraVenta . " + " . ($sem * 7) . " days"));

            for ($emp = 1; $emp <= $maxEmpleado; $emp++) {
                $sqlEmp = "SELECT * FROM `Empleados` WHERE `Id` = '$emp' AND `Nombre` != 'Vacante' LIMIT 1";
                $resEmp = mysqli_query($c0, $sqlEmp);
                if (!$resEmp || !($empleado = mysqli_fetch_assoc($resEmp))) continue;

                $Vendedor = $empleado['IdUsuario'];
                $Equipo   = $empleado['Equipo'];

                $ventasUni = (int)$basicas->ContarFechas4(
                    $c0, "Venta", "Usuario", $Vendedor,
                    "FechaRegistro", $finSemana, "FechaRegistro", $inicioSemana,
                    "Status", "PREVENTA", "Producto", $empleado['Producto']
                );

                $ventasVal = (float)$basicas->SumarFechasIndis(
                    $c0, "CostoVenta", "Venta", "Usuario", $Vendedor,
                    "FechaRegistro", $finSemana, "FechaRegistro", $inicioSemana,
                    "Status", "PREVENTA"
                );

                $cobranzaVal = (float)$basicas->SumarFechas(
                    $c0, "Cantidad", "Pagos", "Usuario", $Vendedor,
                    "FechaRegistro", $finSemana, "FechaRegistro", $inicioSemana
                );

                $sk = $ventasVal / 10000.0;
                $xy = $cobranzaVal / 10000.0;

                if ((int)$empleado['Nivel'] >= 7) {
                    $comVtas = $ventasVal;
                } else {
                    $porcentajeVtas = ((float)$basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Colocacion")) / 100.0;
                    $comVtas = round($ventasVal * $porcentajeVtas, 2);
                }

                $porcentajeCob = ((float)$basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Cobranza")) / 100.0;
                $comCob = round($xy * $porcentajeCob, 2);

                if ($comVtas > 0 || $comCob > 0) {
                    $datCob = array(
                        "IdVendedor" => $Vendedor,
                        "Equipo"     => $Equipo,
                        "Inicio"     => $inicioSemana,
                        "FIn"        => $finSemana,
                        "VtasUni"    => $ventasUni,
                        "VtasVal"    => $ventasVal,
                        "CobUni"     => 0,
                        "CobVal"     => $cobranzaVal,
                        "ComVtas"    => $comVtas,
                        "ComCob"     => $comCob
                    );
                    $basicas->InsertCampo($c0, "Comisiones", $datCob);
                }
            }
        }

        // Nivel equipos (Nivel <= 4)
        $sqlEquipos = "SELECT * FROM `Empleados` WHERE `Nivel` <= 4 AND `Nombre` != 'Vacante'";
        $resEquipos = $c0->query($sqlEquipos);
        if ($resEquipos) {
            foreach ($resEquipos as $empleado) {
                $sumNVtas = (int)$basicas->Sumar1Fechas($c0, "VtasUni", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
                $sumVtas  = (float)$basicas->Sumar1Fechas($c0, "VtasVal", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
                $sumNCob  = (int)$basicas->Sumar1Fechas($c0, "CobUni", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);
                $sumCob   = (float)$basicas->Sumar1Fechas($c0, "CobVal", "Comisiones", "Equipo", $empleado['Id'], "Inicio", $empleado['FechaAlta']);

                $sk = $sumVtas / 10000.0;
                $xy = $sumCob  / 10000.0;

                $porVtas = ((float)$basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Colocacion")) / 100.0;
                $porCob  = ((float)$basicas->BuscarCampos($c0, "N" . $empleado['Nivel'], "Comision", "Tipo", "Cobranza")) / 100.0;

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

    /**
     * Estado de pago por vencimientos.
     * Requiere $mysqli, $financieras, $basicas globales.
     */
    public function estado_mora_corriente(int $idVenta): array {
        global $mysqli, $financieras, $basicas;

        $venta = $financieras->getVenta($mysqli, $idVenta);
        if (!$venta) return ['ok'=>false,'error'=>'Venta no encontrada'];

        $numMeses  = max(1, (int)$venta['NumeroPagos']);
        $producto  = $venta['Producto'];
        $fechaAlta = new DateTime($venta['FechaRegistro']);
        $hoy       = new DateTime('today');

        // Periodicidad Productos.Perido (1 mensual, 2 quincenal, 4 semanal, etc.)
        $rf = (int)$basicas->BuscarCampos($mysqli, "Perido", "Productos", "Producto", $producto);
        if ($rf <= 0) $rf = 1;

        // Cuota
        if ($numMeses <= 1) {
            $cuota = (float)$financieras->SaldoCredito($mysqli, $idVenta);
            $totalCuotas = 1;
        } else {
            $cuota = (float)$financieras->Pago($mysqli, $idVenta);
            $totalCuotas = $numMeses * $rf;
        }
        $cuota = round($cuota, 2);

        // Primer vencimiento
        $stepDias = max(1, (int)floor(30 / $rf));
        $y = (int)$fechaAlta->format('Y');
        $m = (int)$fechaAlta->format('m');
        $d = (int)$fechaAlta->format('d');

        if ($rf === 1) {
            $venc = new DateTime(date('Y-m-t', $fechaAlta->getTimestamp()));
            if ($venc <= $fechaAlta) $venc = (new DateTime("$y-$m-01"))->modify('last day of next month');
        } elseif ($rf === 2) {
            if ($d <= 15) $venc = new DateTime("$y-$m-15");
            else          $venc = new DateTime(date('Y-m-t', $fechaAlta->getTimestamp()));
            if ($venc <= $fechaAlta) $venc->modify("+{$stepDias} days");
        } else {
            $venc = new DateTime("$y-$m-01");
            while ($venc <= $fechaAlta) { $venc->modify("+{$stepDias} days"); }
        }

        // Cuotas vencidas
        $cuotasVencidas = 0;
        $v = clone $venc;
        while ($v <= $hoy && $cuotasVencidas < $totalCuotas) {
            $cuotasVencidas++;
            $v->modify("+{$stepDias} days");
        }
        $proximo = ($cuotasVencidas >= $totalCuotas) ? 'COMPLETADO' : $v->format('Y-m-d');

        // Pagos reales
        $pagado = (float)$financieras->SumarPagos($mysqli, "Cantidad", "Pagos", "IdVenta", $idVenta);

        // Esperado y estado
        $esperado = round($cuota * $cuotasVencidas, 2);
        $pend     = max(0, round($esperado - $pagado, 2));
        $estado   = ($pend <= 0.01) ? 'AL CORRIENTE' : 'MORA';
        $atraso   = ($cuota > 0) ? (int)ceil($pend / $cuota) : 0;

        return [
            'ok'                  => true,
            'estado'              => $estado,
            'pagado_importe'      => round($pagado, 2),
            'esperado_cuotas'     => $cuotasVencidas,
            'esperado_importe'    => $esperado,
            'pendiente_importe'   => $pend,
            'cuota'               => $cuota,
            'cuotas_vencidas'     => $cuotasVencidas,
            'cuotas_atraso'       => $atraso,
            'proximo_vencimiento' => $proximo,
            'total_cuotas'        => $totalCuotas,
        ];
    }
}
?>