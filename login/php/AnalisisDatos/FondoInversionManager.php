<?php
/********************************************************************************************
 * FondoInversionManager.php
 * Administrador del fondo de inversión - Seguimiento mensual
 * Fecha: 2025-12-07 (Actualizado para compatibilidad con form_fondo.php)
 ********************************************************************************************/

declare(strict_types=1);

require_once __DIR__ . '/ConfigFondoFunerario.php';

class FondoInversionManager {
    
    private $mysqli;
    
    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Verificar si ya existe registro para un mes específico
     */
    public function existeRegistroMes(string $mes): bool {
        $stmt = $this->mysqli->prepare("SELECT Id FROM FondoInversion WHERE Mes = ?");
        $stmt->bind_param('s', $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    
    /**
     * Registra o actualiza el valor del fondo para un mes
     * Versión actualizada para form_fondo.php
     */
    public function registrarValorMensual(
        string $mes,
        float $valorInicial,
        float $valorFinal,
        float $aportaciones = 0.0,
        float $retiros = 0.0,
        float $rendimiento = 0.0,
        float $rendimientoAnualizado = 0.0,
        float $udiValor = 8.5,
        float $metaRendimiento = null,
        float $diferenciaMeta = null,
        string $comentarios = ''
    ): bool {
        
        // Validar formato del mes
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
            throw new InvalidArgumentException('Formato de mes inválido. Use YYYY-MM');
        }
        
        // Validar que no exista ya el registro
        if ($this->existeRegistroMes($mes)) {
            throw new Exception("Ya existe un registro para el mes $mes");
        }
        
        // Calcular rendimiento si no se proporciona
        if ($rendimiento == 0.0 && $valorInicial > 0) {
            $rendimiento = ($valorFinal - $valorInicial - $aportaciones + $retiros) / $valorInicial;
        }
        
        // Calcular rendimiento anualizado si no se proporciona
        if ($rendimientoAnualizado == 0.0) {
            $rendimientoAnualizado = pow(1 + $rendimiento, 12) - 1;
        }
        
        // Obtener meta de rendimiento mínimo si no se proporciona
        if ($metaRendimiento === null) {
            $metaRendimiento = $this->calcularMetaRendimientoMensual();
        }
        
        // Calcular diferencia entre rendimiento real y meta si no se proporciona
        if ($diferenciaMeta === null) {
            $diferenciaMeta = $rendimiento - $metaRendimiento;
        }
        
        // Calcular costo del servicio con UDI actual
        $costoServicio = ConfigFondoFunerario::$UDIS_SERVICIO * $udiValor;
        
        // Separar año y mes
        $partes = explode('-', $mes);
        $año = (int)$partes[0];
        $mesNumero = (int)$partes[1];
        
        $stmt = $this->mysqli->prepare("
            INSERT INTO FondoInversion (
                Mes, Año, MesNumero, ValorInicial, ValorFinal, Aportaciones, Retiros,
                Rendimiento, RendimientoAnualizado, UDI_Valor, CostoServicioUDI,
                MetaRendimientoMinimo, RendimientoRealVsMeta, Comentarios,
                FechaRegistro, FechaActualizacion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        
        $stmt->bind_param(
            'siidddddiddddds',
            $mes,
            $año,
            $mesNumero,
            $valorInicial,
            $valorFinal,
            $aportaciones,
            $retiros,
            $rendimiento,
            $rendimientoAnualizado,
            $udiValor,
            $costoServicio,
            $metaRendimiento,
            $diferenciaMeta,
            $comentarios
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Actualizar registro existente
     */
    public function actualizarRegistro(
        int $id,
        float $valorInicial,
        float $valorFinal,
        float $aportaciones = 0.0,
        float $retiros = 0.0,
        float $rendimiento = 0.0,
        float $udiValor = 8.5,
        string $comentarios = ''
    ): bool {
        
        // Obtener registro actual
        $registro = $this->obtenerRegistroPorId($id);
        if (!$registro) {
            return false;
        }
        
        // Calcular rendimiento si no se proporciona
        if ($rendimiento == 0.0 && $valorInicial > 0) {
            $rendimiento = ($valorFinal - $valorInicial - $aportaciones + $retiros) / $valorInicial;
        }
        
        // Calcular rendimiento anualizado
        $rendimientoAnualizado = pow(1 + $rendimiento, 12) - 1;
        
        // Usar meta original del registro
        $metaRendimiento = $registro['MetaRendimientoMinimo'];
        $diferenciaMeta = $rendimiento - $metaRendimiento;
        
        // Calcular costo del servicio
        $costoServicio = ConfigFondoFunerario::$UDIS_SERVICIO * $udiValor;
        
        $stmt = $this->mysqli->prepare("
            UPDATE FondoInversion SET
                ValorInicial = ?,
                ValorFinal = ?,
                Aportaciones = ?,
                Retiros = ?,
                Rendimiento = ?,
                RendimientoAnualizado = ?,
                UDI_Valor = ?,
                CostoServicioUDI = ?,
                RendimientoRealVsMeta = ?,
                Comentarios = ?,
                FechaActualizacion = CURRENT_TIMESTAMP
            WHERE Id = ?
        ");
        
        $stmt->bind_param(
            'dddddddddsi',
            $valorInicial,
            $valorFinal,
            $aportaciones,
            $retiros,
            $rendimiento,
            $rendimientoAnualizado,
            $udiValor,
            $costoServicio,
            $diferenciaMeta,
            $comentarios,
            $id
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Obtener registro por ID
     */
    public function obtenerRegistroPorId(int $id): ?array {
        $stmt = $this->mysqli->prepare("SELECT * FROM FondoInversion WHERE Id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $registro = $result->fetch_assoc();
        $stmt->close();
        
        return $registro ?: null;
    }
    
    /**
     * Calcula la meta de rendimiento mínimo mensual basado en tasa promedio de productos
     */
    public function calcularMetaRendimientoMensual(): float {
        // Consultar tasa promedio de los productos de la base de datos
        $query = "
            SELECT AVG(TasaAnual) as tasa_promedio 
            FROM Productos 
            WHERE TasaAnual IS NOT NULL AND TasaAnual > 0
        ";
        
        $result = $this->mysqli->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            $tasaPromedioAnual = (float)$row['tasa_promedio'];
        } else {
            $tasaPromedioAnual = 12.0; // 12% anual por defecto
        }
        
        // Convertir tasa anual a mensual
        // Fórmula: (1 + anual)^(1/12) - 1
        $tasaMensual = pow(1 + ($tasaPromedioAnual / 100), 1/12) - 1;
        
        // Meta de rendimiento mínimo: 80% de la tasa promedio mensual
        $metaMinima = $tasaMensual * 0.8;
        
        // Asegurar un mínimo razonable (al menos 0.5% mensual = 6% anual)
        return max(0.005, $metaMinima);
    }
    
    /**
     * Calcular aportaciones mensuales automáticas basadas en productos vendidos
     * Compatible con form_fondo.php
     */
    public function calcularAportacionesMensuales(): array {
        // Consultar la tabla Productos para obtener datos reales
        $query = "
            SELECT 
                COUNT(*) as total_productos,
                AVG(CAST(Costo AS DECIMAL(10,2))) as costo_promedio
            FROM Productos 
            WHERE Producto IS NOT NULL AND Producto != ''
        ";
        
        $result = $this->mysqli->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            $totalProductos = (int)$row['total_productos'];
            $costoPromedio = (float)$row['costo_promedio'];
            
            // Suponemos que cada producto genera una venta mensual
            // y que el 50% del costo va al fondo (según ConfigFondoFunerario::$PORCENTAJE_FONDO)
            $aportacionPorProducto = $costoPromedio * ConfigFondoFunerario::$PORCENTAJE_FONDO;
            $totalMensual = $aportacionPorProducto * $totalProductos;
            
            // Ajustar a valores realistas
            $totalMensual = max(1000, min(50000, $totalMensual));
            
            return [
                'total_polizas' => $totalProductos * 5, // Estimación: 5 ventas por producto
                'aportacion_por_poliza' => $aportacionPorProducto / 5,
                'total_mensual' => round($totalMensual, 2)
            ];
        }
        
        // Valores por defecto si no hay datos
        return [
            'total_polizas' => 100,
            'aportacion_por_poliza' => 50,
            'total_mensual' => 5000
        ];
    }
    
    /**
     * Obtiene el historial completo del fondo
     */
    public function obtenerHistorial(int $limit = 12): array {
        $query = "
            SELECT * FROM FondoInversion 
            ORDER BY Año DESC, MesNumero DESC 
            LIMIT ?
        ";
        
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $historial = [];
        while ($row = $result->fetch_assoc()) {
            $historial[] = $row;
        }
        
        $stmt->close();
        return $historial;
    }
    
    /**
     * Obtiene el último registro del fondo
     */
    public function obtenerUltimoRegistro(): ?array {
        $query = "
            SELECT * FROM FondoInversion 
            ORDER BY Año DESC, MesNumero DESC 
            LIMIT 1
        ";
        
        $result = $this->mysqli->query($query);
        if (!$result) {
            return null;
        }
        return $result->fetch_assoc() ?: null;
    }
    
    /**
     * Calcula estadísticas del fondo para form_fondo.php
     */
    public function calcularEstadisticas(): array {
        $query = "
            SELECT 
                COUNT(*) as total_meses,
                AVG(Rendimiento) as rendimiento_promedio_mensual,
                AVG(MetaRendimientoMinimo) as meta_promedio,
                AVG(RendimientoRealVsMeta) as diferencia_promedio
            FROM FondoInversion
            WHERE Mes >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 12 MONTH), '%Y-%m')
        ";
        
        $result = $this->mysqli->query($query);
        
        if (!$result) {
            return [
                'total_meses' => 0,
                'rendimiento_promedio_mensual' => 0,
                'meta_promedio' => 0,
                'diferencia_promedio' => 0
            ];
        }
        
        $row = $result->fetch_assoc();
        
        return [
            'total_meses' => $row['total_meses'] ?? 0,
            'rendimiento_promedio_mensual' => $row['rendimiento_promedio_mensual'] ?? 0,
            'meta_promedio' => $row['meta_promedio'] ?? 0,
            'diferencia_promedio' => $row['diferencia_promedio'] ?? 0
        ];
    }
    
    /**
     * Obtiene el primer registro del fondo
     */
    private function obtenerPrimerRegistro(): ?array {
        $query = "
            SELECT * FROM FondoInversion 
            ORDER BY Año ASC, MesNumero ASC 
            LIMIT 1
        ";
        
        $result = $this->mysqli->query($query);
        if (!$result) {
            return null;
        }
        return $result->fetch_assoc() ?: null;
    }
    
    /**
     * Proyecta el valor futuro del fondo
     */
    public function proyectarFuturo(
        float $rendimientoMensual,
        float $aportacionesMensuales,
        int $meses = 60
    ): array {
        $ultimo = $this->obtenerUltimoRegistro();
        
        if (!$ultimo) {
            return [];
        }
        
        $valorActual = (float)$ultimo['ValorFinal'];
        $proyecciones = [];
        
        for ($i = 1; $i <= $meses; $i++) {
            $interes = $valorActual * $rendimientoMensual;
            $valorActual = $valorActual + $interes + $aportacionesMensuales;
            
            $proyecciones[] = [
                'mes' => $i,
                'valor' => $valorActual,
                'interes' => $interes,
                'aportaciones' => $aportacionesMensuales
            ];
        }
        
        return $proyecciones;
    }
    
    /**
     * Calcula el umbral mínimo de inversión necesario
     */
    public function calcularUmbralInversion(): array {
        // Obtener el último valor del fondo
        $ultimoRegistro = $this->obtenerUltimoRegistro();
        $valorFondoActual = $ultimoRegistro['ValorFinal'] ?? 0;
        
        // Obtener datos de ventas (simplificado para compatibilidad)
        $aportacionesData = $this->calcularAportacionesMensuales();
        
        $totalPolizas = $aportacionesData['total_polizas'] ?? 100;
        $aportacionMensual = $aportacionesData['total_mensual'] ?? 5000;
        $aportacionAnual = $aportacionMensual * 12;
        
        // Calcular costo total de servicios
        $udiActual = $ultimoRegistro['UDI_Valor'] ?? ConfigFondoFunerario::$UDI_ACTUAL;
        $costoServicio = ConfigFondoFunerario::$UDIS_SERVICIO * $udiActual;
        $costoTotalServicios = $costoServicio * $totalPolizas;
        
        // Calcular cobertura actual
        $coberturaActual = ($valorFondoActual > 0 && $costoTotalServicios > 0) 
            ? $valorFondoActual / $costoTotalServicios 
            : 0;
        
        // Calcular brecha actual
        $brechaActual = max(0, $costoTotalServicios - $valorFondoActual);
        
        // Obtener rendimiento promedio
        $estadisticas = $this->calcularEstadisticas();
        $rendimientoPromedioMensual = (float)($estadisticas['rendimiento_promedio_mensual'] ?? 0.015);
        
        // Determinar estado
        if ($coberturaActual >= 1) {
            $estado = 'SOBRECUMPLIMIENTO';
        } elseif ($coberturaActual >= 0.7) {
            $estado = 'MANEJABLE';
        } else {
            $estado = 'CRÍTICO';
        }
        
        // Calcular aportación mensual necesaria para cerrar brecha en 5 años
        $mesesObjetivo = 60; // 5 años
        
        if ($mesesObjetivo > 0 && $rendimientoPromedioMensual > 0 && $brechaActual > 0) {
            // Fórmula: PMT = (FV * r) / ((1 + r)^t - 1)
            $fv = $brechaActual;
            $r = $rendimientoPromedioMensual;
            $t = $mesesObjetivo;
            
            $aportacionMensualNecesaria = ($fv * $r) / (pow(1 + $r, $t) - 1);
        } else {
            $aportacionMensualNecesaria = 0;
        }
        
        // Calcular años para cerrar brecha con aportación actual
        if ($aportacionMensual > 0 && $rendimientoPromedioMensual > 0 && $brechaActual > 0) {
            // Fórmula: t = log(1 + (FV * r / PMT)) / log(1 + r)
            $fv = $brechaActual;
            $r = $rendimientoPromedioMensual;
            $pmt = $aportacionMensual;
            
            $mesesParaCerrar = log(1 + ($fv * $r / $pmt)) / log(1 + $r);
            $aniosParaCerrar = $mesesParaCerrar / 12;
        } else {
            $mesesParaCerrar = 0;
            $aniosParaCerrar = 0;
        }
        
        return [
            'total_polizas' => $totalPolizas,
            'estado' => $estado,
            'cobertura_actual' => $coberturaActual,
            'aportacion_total' => $aportacionAnual,
            'costo_total_servicios' => $costoTotalServicios,
            'brecha_actual' => $brechaActual,
            'aportacion_mensual_necesaria_5anios' => $aportacionMensualNecesaria,
            'anios_para_cerrar_brecha' => $aniosParaCerrar,
            'meses_para_cerrar_brecha' => $mesesParaCerrar,
            'rendimiento_promedio_mensual' => $rendimientoPromedioMensual
        ];
    }
    
    /**
     * Obtener rendimiento anualizado del último año
     */
    public function obtenerRendimientoAnualizado(): float {
        $query = "
            SELECT AVG(RendimientoAnualizado) as rendimiento_anual
            FROM FondoInversion
            WHERE Mes >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 12 MONTH), '%Y-%m')
        ";
        
        $result = $this->mysqli->query($query);
        if (!$result) {
            return 0;
        }
        
        $row = $result->fetch_assoc();
        return $row['rendimiento_anual'] ?? 0;
    }
    
    /**
     * Obtener resumen anual del fondo
     */
    public function obtenerResumenAnual(int $ano): array {
        $query = "
            SELECT 
                Mes,
                ValorInicial,
                ValorFinal,
                Aportaciones,
                Retiros,
                Rendimiento,
                RendimientoAnualizado,
                UDI_Valor,
                MetaRendimientoMinimo,
                RendimientoRealVsMeta
            FROM FondoInversion 
            WHERE Año = ?
            ORDER BY MesNumero ASC
        ";
        
        $stmt = $this->mysqli->prepare($query);
        $stmt->bind_param('i', $ano);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $resumen = [];
        while ($row = $result->fetch_assoc()) {
            $resumen[] = $row;
        }
        
        $stmt->close();
        return $resumen;
    }
    
    /**
     * Eliminar registro por ID
     */
    public function eliminarRegistro(int $id): bool {
        $stmt = $this->mysqli->prepare("DELETE FROM FondoInversion WHERE Id = ?");
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    
    /**
     * Validar datos del fondo antes de registrar
     */
    public function validarDatosFondo(
        string $mes,
        float $valorInicial,
        float $valorFinal,
        float $aportaciones,
        float $retiros
    ): array {
        $errores = [];
        
        // Validar formato del mes
        if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
            $errores[] = "Formato de mes inválido. Use YYYY-MM";
        }
        
        // Validar que el mes no sea futuro
        $mesActual = date('Y-m');
        if ($mes > $mesActual) {
            $errores[] = "No se puede registrar un mes futuro";
        }
        
        // Validar valores positivos
        if ($valorInicial < 0) {
            $errores[] = "El valor inicial no puede ser negativo";
        }
        
        if ($valorFinal < 0) {
            $errores[] = "El valor final no puede ser negativo";
        }
        
        if ($aportaciones < 0) {
            $errores[] = "Las aportaciones no pueden ser negativas";
        }
        
        if ($retiros < 0) {
            $errores[] = "Los retiros no pueden ser negativas";
        }
        
        // Validar consistencia lógica
        if ($valorInicial > 0 && $valorFinal < $valorInicial - $retiros) {
            $errores[] = "El valor final es demasiado bajo considerando aportaciones y retiros";
        }
        
        // Validar que no sea un mes duplicado
        if ($this->existeRegistroMes($mes)) {
            $errores[] = "Ya existe un registro para el mes $mes";
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}
?>