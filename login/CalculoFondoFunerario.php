<?php
/********************************************************************************************
 * CalculoFondoFunerario.php
 * Calcula métricas del fondo de inversión para ventas activas
 * Fecha: 2024-03-20
 ********************************************************************************************/

declare(strict_types=1);

require_once __DIR__ . '/ConfigFondoFunerario.php';

class CalculoFondoFunerario {
    
    private $mysqli;
    private $basicas;
    
    public function __construct(mysqli $mysqli, $basicas) {
        $this->mysqli = $mysqli;
        $this->basicas = $basicas;
    }
    
    /**
     * Obtiene la esperanza de vida restante según edad
     * Usa tabla INEGI simplificada
     */
    public function getEsperanzaVidaRestante(int $edad): int {
        // Tabla simplificada de esperanza de vida en México (INEGI 2023)
        $tablaEsperanza = [
            0 => 75, 1 => 74, 2 => 73, 3 => 72, 4 => 71, 5 => 70, 6 => 69, 7 => 68, 8 => 67, 9 => 66,
            10 => 65, 11 => 64, 12 => 63, 13 => 62, 14 => 61, 15 => 60, 16 => 59, 17 => 58, 18 => 57, 19 => 56,
            20 => 55, 21 => 54, 22 => 53, 23 => 52, 24 => 51, 25 => 50, 26 => 49, 27 => 48, 28 => 47, 29 => 46,
            30 => 45, 31 => 44, 32 => 43, 33 => 42, 34 => 41, 35 => 40, 36 => 39, 37 => 38, 38 => 37, 39 => 36,
            40 => 35, 41 => 34, 42 => 33, 43 => 32, 44 => 31, 45 => 30, 46 => 29, 47 => 28, 48 => 27, 49 => 26,
            50 => 25, 51 => 24, 52 => 23, 53 => 22, 54 => 21, 55 => 20, 56 => 19, 57 => 18, 58 => 17, 59 => 16,
            60 => 15, 61 => 14, 62 => 13, 63 => 12, 64 => 11, 65 => 10, 66 => 9, 67 => 8, 68 => 7, 69 => 6
        ];
        
        return $tablaEsperanza[$edad] ?? 5; // Mínimo 5 años si no está en tabla
    }
    
    /**
     * Analiza todas las ventas activas y calcula métricas del fondo
     */
    public function analizarVentasActivas(): array {
        $resultados = [
            'total_ventas_activas' => 0,
            'aportacion_total_fondo' => 0.0,
            'costo_servicio_total' => 0.0,
            'brecha_total' => 0.0,
            'rendimiento_minimo_promedio' => 0.0,
            'rendimiento_minimo_promedio_ponderado' => 0.0,
            'excedente_esperado_total' => 0.0,
            'comision_pasiva_total' => 0.0,
            'detalle_por_edad' => [],
            'resumen_por_rango_edad' => [],
            'alerta_riesgo' => false,
            'mensaje_alerta' => ''
        ];
        
        // Rangos de edad para resumen
        $rangos = ['02-29', '30-49', '50-54', '55-59', '60-64', '65-69'];
        foreach ($rangos as $rango) {
            $resultados['resumen_por_rango_edad'][$rango] = [
                'ventas' => 0,
                'aportacion' => 0.0,
                'rendimiento_minimo_promedio' => 0.0,
                'excedente_esperado' => 0.0
            ];
        }
        
        try {
            // Consultar todas las ventas activas
            $query = "SELECT v.*, u.ClaveCurp 
                     FROM Venta v 
                     LEFT JOIN Usuario u ON v.IdContact = u.IdContact 
                     WHERE v.Status = 'ACTIVO'";
            
            $stmt = $this->mysqli->prepare($query);
            if (!$stmt) {
                return $resultados;
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sumRendimientos = 0.0;
            $sumRendimientosPonderados = 0.0;
            $sumAportaciones = 0.0;
            $contadorVentas = 0;
            
            while ($venta = $result->fetch_assoc()) {
                $contadorVentas++;
                $precioVenta = (float)($venta['CostoVenta'] ?? 0);
                
                // Obtener edad del cliente
                $edad = 30; // Valor por defecto
                if (!empty($venta['ClaveCurp'])) {
                    $edad = (int)$this->basicas->ObtenerEdad($venta['ClaveCurp']);
                }
                
                // Determinar rango de edad
                $rangoEdad = '';
                if ($edad >= 2 && $edad <= 29) $rangoEdad = '02-29';
                elseif ($edad >= 30 && $edad <= 49) $rangoEdad = '30-49';
                elseif ($edad >= 50 && $edad <= 54) $rangoEdad = '50-54';
                elseif ($edad >= 55 && $edad <= 59) $rangoEdad = '55-59';
                elseif ($edad >= 60 && $edad <= 64) $rangoEdad = '60-64';
                elseif ($edad >= 65 && $edad <= 69) $rangoEdad = '65-69';
                
                // Calcular años hasta fallecimiento (esperanza de vida restante)
                $aniosHastaFallecimiento = $this->getEsperanzaVidaRestante($edad);
                
                // Aportación al fondo (50% del precio)
                $aportacionFondo = $precioVenta * ConfigFondoFunerario::$PORCENTAJE_FONDO;
                
                // Costo del servicio
                $costoServicio = ConfigFondoFunerario::getCostoServicioHoy();
                
                // Brecha inicial (diferencia entre aportación y costo)
                $brechaInicial = $costoServicio - $aportacionFondo;
                
                // Rendimiento mínimo requerido
                $rendimientoMinimo = ConfigFondoFunerario::calcularRendimientoMinimo(
                    $precioVenta, 
                    $aniosHastaFallecimiento
                );
                
                // Rendimiento esperado realista (8% anual como objetivo)
                $rendimientoEsperado = 0.08;
                
                // Excedente esperado
                $excedenteEsperado = ConfigFondoFunerario::calcularExcedenteEsperado(
                    $precioVenta,
                    $aniosHastaFallecimiento,
                    $rendimientoEsperado
                );
                
                // Comisión pasiva para equipo
                $comisionPasiva = ConfigFondoFunerario::calcularComisionPasiva(
                    $precioVenta,
                    $aniosHastaFallecimiento,
                    $rendimientoEsperado
                );
                
                // Acumular totales
                $resultados['aportacion_total_fondo'] += $aportacionFondo;
                $resultados['costo_servicio_total'] += $costoServicio;
                $resultados['brecha_total'] += $brechaInicial;
                $resultados['excedente_esperado_total'] += $excedenteEsperado;
                $resultados['comision_pasiva_total'] += $comisionPasiva;
                
                $sumRendimientos += $rendimientoMinimo;
                $sumRendimientosPonderados += ($rendimientoMinimo * $aportacionFondo);
                $sumAportaciones += $aportacionFondo;
                
                // Guardar detalle
                $resultados['detalle_por_edad'][] = [
                    'id_venta' => $venta['Id'] ?? 0,
                    'edad_cliente' => $edad,
                    'precio_venta' => $precioVenta,
                    'aportacion_fondo' => $aportacionFondo,
                    'costo_servicio' => $costoServicio,
                    'brecha_inicial' => $brechaInicial,
                    'anios_hasta_fallecimiento' => $aniosHastaFallecimiento,
                    'rendimiento_minimo_requerido' => $rendimientoMinimo,
                    'rendimiento_minimo_porcentaje' => round($rendimientoMinimo * 100, 2),
                    'excedente_esperado' => $excedenteEsperado,
                    'comision_pasiva_equipo' => $comisionPasiva,
                    'rango_edad' => $rangoEdad
                ];
                
                // Acumular por rango de edad
                if (isset($resultados['resumen_por_rango_edad'][$rangoEdad])) {
                    $resultados['resumen_por_rango_edad'][$rangoEdad]['ventas']++;
                    $resultados['resumen_por_rango_edad'][$rangoEdad]['aportacion'] += $aportacionFondo;
                    $resultados['resumen_por_rango_edad'][$rangoEdad]['rendimiento_minimo_promedio'] += $rendimientoMinimo;
                    $resultados['resumen_por_rango_edad'][$rangoEdad]['excedente_esperado'] += $excedenteEsperado;
                }
            }
            
            $stmt->close();
            
        } catch (Exception $e) {
            // Si hay error, retornar resultados vacíos
            error_log("Error en analizarVentasActivas: " . $e->getMessage());
        }
        
        $resultados['total_ventas_activas'] = $contadorVentas;
        
        // Calcular promedios
        if ($contadorVentas > 0) {
            $resultados['rendimiento_minimo_promedio'] = $sumRendimientos / $contadorVentas;
            $resultados['rendimiento_minimo_promedio_ponderado'] = 
                $sumAportaciones > 0 ? $sumRendimientosPonderados / $sumAportaciones : 0;
        }
        
        // Calcular promedios por rango
        foreach ($resultados['resumen_por_rango_edad'] as $rango => &$datos) {
            if ($datos['ventas'] > 0) {
                $datos['rendimiento_minimo_promedio'] = $datos['rendimiento_minimo_promedio'] / $datos['ventas'];
                $datos['aportacion_promedio'] = $datos['aportacion'] / $datos['ventas'];
            } else {
                $datos['rendimiento_minimo_promedio'] = 0;
                $datos['aportacion_promedio'] = 0;
            }
        }
        
        // Evaluar riesgo
        if ($resultados['rendimiento_minimo_promedio_ponderado'] > 0.15) {
            $resultados['alerta_riesgo'] = true;
            $resultados['mensaje_alerta'] = 'ALTO RIESGO: El rendimiento mínimo requerido promedio (' . 
                round($resultados['rendimiento_minimo_promedio_ponderado'] * 100, 1) . 
                '%) supera el 15% anual. Se recomienda ajustar precios o enfocarse en clientes más jóvenes.';
        } elseif ($resultados['rendimiento_minimo_promedio_ponderado'] > 0.10) {
            $resultados['alerta_riesgo'] = true;
            $resultados['mensaje_alerta'] = 'RIESGO MODERADO: El rendimiento mínimo requerido (' . 
                round($resultados['rendimiento_minimo_promedio_ponderado'] * 100, 1) . 
                '%) es elevado. Monitorear inversiones cuidadosamente.';
        }
        
        return $resultados;
    }
    
    /**
     * Genera recomendaciones de inversión
     */
    public function generarRecomendacionesInversion(array $analisis): array {
        $rmin = $analisis['rendimiento_minimo_promedio_ponderado'] ?? 0;
        
        $recomendaciones = [];
        
        if ($rmin <= 0.06) {
            $recomendaciones[] = [
                'nivel' => 'BAJO',
                'mensaje' => 'Rendimiento objetivo alcanzable',
                'detalle' => 'Con un rendimiento mínimo del ' . round($rmin*100,1) . 
                           '%, puedes invertir en instrumentos conservadores: CETES, bonos gubernamentales, fondos de deuda.',
                'instrumentos_recomendados' => ['CETES', 'Bonos gubernamentales', 'Fondos de deuda', 'UDIBONOS']
            ];
        } elseif ($rmin <= 0.10) {
            $recomendaciones[] = [
                'nivel' => 'MODERADO',
                'mensaje' => 'Requiere mezcla conservadora-agresiva',
                'detalle' => 'Necesitas un rendimiento del ' . round($rmin*100,1) . 
                           '%. Recomendado: 60% instrumentos de deuda, 40% renta variable de bajo riesgo.',
                'instrumentos_recomendados' => ['CETES (40%)', 'UDIBONOS (20%)', 'ETF de dividendos (30%)', 'Acciones blue-chip (10%)']
            ];
        } elseif ($rmin <= 0.15) {
            $recomendaciones[] = [
                'nivel' => 'ALTO',
                'mensaje' => 'Rendimiento elevado - riesgo considerable',
                'detalle' => 'Rendimiento requerido del ' . round($rmin*100,1) . 
                           '% es alto. Considera: 40% deuda, 60% renta variable con potencial de crecimiento.',
                'instrumentos_recomendados' => ['Fondos indexados (40%)', 'Acciones de crecimiento (20%)', 'REITs (10%)', 'CETES (30%)']
            ];
        } else {
            $recomendaciones[] = [
                'nivel' => 'MUY ALTO',
                'mensaje' => 'RIESGO CRÍTICO - Revisar modelo',
                'detalle' => 'Rendimiento del ' . round($rmin*100,1) . 
                           '% es muy difícil de lograr consistentemente. Revisa precios o enfoque comercial.',
                'instrumentos_recomendados' => ['Urgente revisar estructura de precios', 'Enfocar ventas en jóvenes', 'Aumentar aportación al fondo']
            ];
        }
        
        return $recomendaciones;
    }
}
?>