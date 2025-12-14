<?php
declare(strict_types=1);

/**
 * CalculoFondoFunerario.php
 * - Analiza pólizas ACTIVAS (Venta) y calcula:
 *   - Aportación al fondo por póliza (Venta.CostoVenta * Productos.Fideicomiso)
 *   - Costo objetivo del servicio por póliza (min(promedio EntregaServicio, tope 2600 UDI))
 *   - Brecha total = costo_servicio_total - aportación_total_fondo
 *   - Meta promedio ponderada (TFideicomiso por mix de productos)
 *   - Ingreso residual vendedor: 20% del excedente de rendimiento vs meta (en dinero)
 *
 * Dependencias:
 * - ConfigFondoFunerario.php
 * - FondoInversionManager.php
 */

require_once __DIR__ . '/ConfigFondoFunerario.php';
require_once __DIR__ . '/FondoInversionManager.php';

final class CalculoFondoFunerario
{
    private mysqli $db;
    private $basicas;

    private const SHARE_EXCEDENTE_VENDEDOR = 0.20;

    public function __construct(mysqli $db, $basicas)
    {
        $this->db = $db;
        $this->basicas = $basicas;
        $this->db->set_charset('utf8mb4');
    }

    public function analizarVentasActivas(): array
    {
        $productos = ConfigFondoFunerario::cargarProductos($this->db);

        // Base “proyectado”
        $costoServicioMaximo    = ConfigFondoFunerario::getCostoServicioMaximo();
        $costoServicioProyectado= ConfigFondoFunerario::getCostoServicioProyectado(
            $this->db,
            ConfigFondoFunerario::MESES_COSTO_PROYECTADO
        );

        // Rendimiento real del fondo (anualizado) desde historial
        $fondoMgr = new FondoInversionManager($this->db);
        $statsFondo = $fondoMgr->calcularEstadisticas(12);
        $rendRealAnual = (float)($statsFondo['rendimiento_promedio_anual'] ?? 0.0);

        $rs = $this->db->query("
            SELECT Id, Producto, CostoVenta, FechaRegistro, IdContact
            FROM Venta
            WHERE Status = 'ACTIVO'
        ");

        $totalVentas = 0;
        $aportacionTotal = 0.0;
        $costoServiciosTotal = 0.0;
        $brechaTotal = 0.0;

        $sumRmin = 0.0;
        $sumPeso = 0.0;
        $sumRmetaPond = 0.0;

        $resumenEdad = [];
        $excedenteEsperadoTotal = 0.0;
        $comisionPasivaTotal = 0.0;

        foreach ($rs as $v) {
            $prod = (string)$v['Producto'];
            if (!isset($productos[$prod])) continue;

            $costoVenta = (float)$v['CostoVenta'];
            if ($costoVenta <= 0) continue;

            $totalVentas++;
            $cfg = $productos[$prod];

            $fideProp = (float)$cfg['Fideicomiso'];   // 0-1
            $metaProd = (float)$cfg['TFideicomiso'];  // 0-1

            // Aportación por póliza
            $aportacion = $costoVenta * $fideProp;

            // >>> TODO basado en PROYECTADO <<<
            $costoServicio = $costoServicioProyectado;

            $edad  = $this->obtenerEdadVenta((int)$v['IdContact']);
            $anios = ConfigFondoFunerario::estimarAniosHastaEvento($edad);

            $rMin = $this->calcRendMinAnual($aportacion, $costoServicio, $anios);

            $excedente = $this->calcExcedenteDinero($aportacion, $metaProd, $rendRealAnual, $anios);
            $comisionResidual = self::SHARE_EXCEDENTE_VENDEDOR * $excedente;

            $aportacionTotal += $aportacion;
            $costoServiciosTotal += $costoServicio;

            $brecha = max(0.0, $costoServicio - $aportacion);
            $brechaTotal += $brecha;

            $sumRmin += $rMin;
            $sumPeso += 1.0;

            $sumRmetaPond += ($metaProd * max(0.0, $aportacion));

            $excedenteEsperadoTotal += $excedente;
            $comisionPasivaTotal += $comisionResidual;

            $rango = $this->rangoEdad($edad);
            if (!isset($resumenEdad[$rango])) {
                $resumenEdad[$rango] = [
                    'ventas' => 0,
                    'aportacion_total' => 0.0,
                    'aportacion_promedio' => 0.0,
                    'rendimiento_minimo_promedio' => 0.0,
                    'meta_producto_promedio' => 0.0,
                    'excedente_esperado' => 0.0,
                    'comision_pasiva' => 0.0,
                ];
            }
            $resumenEdad[$rango]['ventas']++;
            $resumenEdad[$rango]['aportacion_total'] += $aportacion;
            $resumenEdad[$rango]['rendimiento_minimo_promedio'] += $rMin;
            $resumenEdad[$rango]['meta_producto_promedio'] += $metaProd;
            $resumenEdad[$rango]['excedente_esperado'] += $excedente;
            $resumenEdad[$rango]['comision_pasiva'] += $comisionResidual;
        }

        foreach ($resumenEdad as $k => $d) {
            $n = max(1, (int)$d['ventas']);
            $resumenEdad[$k]['aportacion_promedio'] = $d['aportacion_total'] / $n;
            $resumenEdad[$k]['rendimiento_minimo_promedio'] = $d['rendimiento_minimo_promedio'] / $n;
            $resumenEdad[$k]['meta_producto_promedio'] = $d['meta_producto_promedio'] / $n;
        }

        $rMinProm = $sumPeso > 0 ? ($sumRmin / $sumPeso) : 0.0;
        $metaPonderada = $aportacionTotal > 0 ? ($sumRmetaPond / $aportacionTotal) : 0.0;

        $alerta = false;
        $msg = '';
        if ($aportacionTotal > 0 && $metaPonderada > 0 && $rendRealAnual + 1e-9 < $metaPonderada) {
            $alerta = true;
            $msg = 'El rendimiento real anual del fondo está por debajo de la meta promedio ponderada por producto. Se requiere ajustar estrategia o aportaciones.';
        }

        return [
            'total_ventas_activas' => $totalVentas,

            // Fondo / servicio (PROYECTADO)
            'aportacion_total_fondo' => $aportacionTotal,
            'costo_servicio_unitario_proyectado' => $costoServicioProyectado,
            'costo_servicio_unitario_maximo' => $costoServicioMaximo,
            'costo_servicio_total' => $costoServiciosTotal,
            'brecha_total' => $brechaTotal,

            // Tasas
            'rendimiento_minimo_promedio' => $rMinProm,
            'rendimiento_minimo_promedio_ponderado' => $metaPonderada,
            'rendimiento_real_anual' => $rendRealAnual,

            // Incentivos
            'excedente_esperado_total' => $excedenteEsperadoTotal,
            'comision_pasiva_total' => $comisionPasivaTotal,
            'porcentaje_comision_excedente' => self::SHARE_EXCEDENTE_VENDEDOR,

            'resumen_por_rango_edad' => $resumenEdad,

            'alerta_riesgo' => $alerta,
            'mensaje_alerta' => $msg,

            'costo_tope_mxn' => $costoServicioMaximo,
            'costo_real_promedio_mxn' => ConfigFondoFunerario::getCostoServicioReal($this->db, ConfigFondoFunerario::MESES_COSTO_PROYECTADO),
            'donde_invertido' => (string)($statsFondo['donde_invertido'] ?? ''),
        ];
    }

    /**
     * Recomendaciones simples según brecha y si el rendimiento real está debajo de la meta.
     * Se mantiene la lógica previa para no romper el dashboard.
     */
    public function generarRecomendacionesInversion(array $analisis): array
    {
        $brecha = (float)($analisis['brecha_total'] ?? 0);
        $aport = (float)($analisis['aportacion_total_fondo'] ?? 0);
        $meta = (float)($analisis['rendimiento_minimo_promedio_ponderado'] ?? 0);
        $real = (float)($analisis['rendimiento_real_anual'] ?? 0);

        $ratio = $aport > 0 ? ($brecha / $aport) : 1.0;

        $nivel = 'BAJO';
        if ($ratio > 0.8) $nivel = 'CRÍTICO';
        elseif ($ratio > 0.5) $nivel = 'ALTO';
        elseif ($ratio > 0.25) $nivel = 'MODERADO';

        $out = [];

        $out[] = [
            'nivel' => $nivel,
            'mensaje' => 'Brecha vs aportación',
            'detalle' => 'La brecha total indica cuánto falta para cubrir el costo objetivo de servicios con las aportaciones actuales (proyectado).',
            'instrumentos_recomendados' => ['CETES/Bonos', 'Fondos deuda corto plazo', 'Deuda corporativa grado inversión'],
        ];

        if ($meta > 0 && $real + 1e-9 < $meta) {
            $out[] = [
                'nivel' => 'CRÍTICO',
                'mensaje' => 'Rendimiento real por debajo de la meta del mix de productos',
                'detalle' => 'El rendimiento anual real estimado del fondo está por debajo de la meta ponderada (TFideicomiso). Ajustar asignación o aumentar aportación.',
                'instrumentos_recomendados' => ['Estrategia balanceada', 'Bonos + variable', 'Rebalanceo mensual'],
            ];
        } else {
            $out[] = [
                'nivel' => 'BAJO',
                'mensaje' => 'Rendimiento real cumple o supera metas',
                'detalle' => 'El fondo está en zona de cumplimiento. Se puede habilitar ingreso residual (20% excedente vs meta) sin poner en riesgo la cobertura.',
                'instrumentos_recomendados' => ['Balanceado', 'Deuda + ETFs', 'Reinversión de excedentes'],
            ];
        }

        return $out;
    }

    private function obtenerEdadVenta(int $idContact): int
    {
        try {
            $curp = (string)$this->basicas->BuscarCampos($this->db, 'ClaveCurp', 'Usuario', 'IdContact', $idContact);
            $edad = (int)$this->basicas->ObtenerEdad($curp);
            return max(ConfigFondoFunerario::EDAD_MIN, $edad > 0 ? $edad : ConfigFondoFunerario::EDAD_MIN);
        } catch (\Throwable $e) {
            return ConfigFondoFunerario::EDAD_MIN;
        }
    }

    private function rangoEdad(int $edad): string
    {
        $edad = max(0, $edad);
        $bins = [[18,29],[30,39],[40,49],[50,59],[60,69],[70,79],[80,120]];
        foreach ($bins as [$a,$b]) {
            if ($edad >= $a && $edad <= $b) return "{$a}-{$b}";
        }
        return "N/D";
    }

    private function calcRendMinAnual(float $aportacion, float $objetivo, int $anios): float
    {
        $aportacion = max(0.0, $aportacion);
        $objetivo = max(0.0, $objetivo);
        $anios = max(1, $anios);

        if ($aportacion <= 0.0) return 1.0;
        if ($aportacion >= $objetivo) return 0.0;

        $ratio = $objetivo / $aportacion;
        return max(0.0, pow($ratio, 1.0 / $anios) - 1.0);
    }

    private function calcExcedenteDinero(float $aportacion, float $rMeta, float $rReal, int $anios): float
    {
        $aportacion = max(0.0, $aportacion);
        $rMeta = max(0.0, $rMeta);
        $rReal = max(-0.99, $rReal);
        $anios = max(1, $anios);

        if ($aportacion <= 0.0) return 0.0;
        if ($rReal <= $rMeta) return 0.0;

        $fvReal = $aportacion * pow(1.0 + $rReal, $anios);
        $fvMeta = $aportacion * pow(1.0 + $rMeta, $anios);

        return max(0.0, $fvReal - $fvMeta);
    }
}
