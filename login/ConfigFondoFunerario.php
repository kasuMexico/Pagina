<?php
/********************************************************************************************
 * ConfigFondoFunerario.php
 * Parámetros para cálculo de rendimiento mínimo del fondo funerario
 * Fecha: 2024-03-20
 ********************************************************************************************/

declare(strict_types=1);

class ConfigFondoFunerario {
    
    // Tabla de precios por edad (MXN)
    public static $PRECIOS_POR_EDAD = [
        '02-29' => 2500.00,
        '30-49' => 3000.00,
        '50-54' => 4000.00,
        '55-59' => 5000.00,
        '60-64' => 6500.00,
        '65-69' => 8000.00
    ];
    
    // Parámetros del nuevo modelo
    public static $PORCENTAJE_FONDO = 0.50;          // 50% al fondo
    public static $PORCENTAJE_COMISION_INICIAL = 0.25; // 25% comisiones iniciales
    public static $PORCENTAJE_IVA = 0.16;            // 16% IVA
    public static $PORCENTAJE_UTILIDAD_INICIAL = 0.09; // 9% utilidad inicial
    
    // Parámetros del servicio
    public static $UDI_ACTUAL = 8.50;                // Valor actual de 1 UDI en MXN
    public static $UDIS_SERVICIO = 2600;             // Servicio acotado a 2600 UDIs
    
    // Porcentaje de excedente para equipo (ingreso pasivo)
    public static $PORCENTAJE_EXCEDENTE_EQUIPO = 0.02; // 2% del excedente
    
    // Cálculo del costo real del servicio en MXN hoy
    public static function getCostoServicioHoy(): float {
        return self::$UDIS_SERVICIO * self::$UDI_ACTUAL; // 2600 * 8.50 = 22,100 MXN
    }
    
    // Obtener precio según edad
    public static function getPrecioPorEdad(int $edad): float {
        if ($edad >= 2 && $edad <= 29) return self::$PRECIOS_POR_EDAD['02-29'];
        if ($edad >= 30 && $edad <= 49) return self::$PRECIOS_POR_EDAD['30-49'];
        if ($edad >= 50 && $edad <= 54) return self::$PRECIOS_POR_EDAD['50-54'];
        if ($edad >= 55 && $edad <= 59) return self::$PRECIOS_POR_EDAD['55-59'];
        if ($edad >= 60 && $edad <= 64) return self::$PRECIOS_POR_EDAD['60-64'];
        if ($edad >= 65 && $edad <= 69) return self::$PRECIOS_POR_EDAD['65-69'];
        return 0.0;
    }
    
    // Calcular tasa mínima de rendimiento requerida
    public static function calcularRendimientoMinimo(float $precioVenta, int $aniosHastaFallecimiento): float {
        $costoServicio = self::getCostoServicioHoy();
        $aporteFondo = $precioVenta * self::$PORCENTAJE_FONDO;
        
        if ($aporteFondo <= 0 || $aniosHastaFallecimiento <= 0) {
            return 0.0;
        }
        
        // Fórmula: r_min = (costoServicio / aporteFondo)^(1/t) - 1
        $ratio = $costoServicio / $aporteFondo;
        $rMin = pow($ratio, 1 / $aniosHastaFallecimiento) - 1;
        
        return max(0.0, $rMin); // No permitir negativo
    }
    
    // Calcular excedente esperado
    public static function calcularExcedenteEsperado(
        float $precioVenta, 
        int $aniosHastaFallecimiento, 
        float $rendimientoEsperado
    ): float {
        $costoServicio = self::getCostoServicioHoy();
        $aporteFondo = $precioVenta * self::$PORCENTAJE_FONDO;
        $rMin = self::calcularRendimientoMinimo($precioVenta, $aniosHastaFallecimiento);
        
        // Excedente = AporteFondo * [(1+r_esperado)^t - (1+r_min)^t]
        $excedente = $aporteFondo * (
            pow(1 + $rendimientoEsperado, $aniosHastaFallecimiento) - 
            pow(1 + $rMin, $aniosHastaFallecimiento)
        );
        
        return max(0.0, $excedente);
    }
    
    // Calcular comisión pasiva para equipo
    public static function calcularComisionPasiva(
        float $precioVenta, 
        int $aniosHastaFallecimiento, 
        float $rendimientoEsperado
    ): float {
        $excedente = self::calcularExcedenteEsperado(
            $precioVenta, 
            $aniosHastaFallecimiento, 
            $rendimientoEsperado
        );
        
        return $excedente * self::$PORCENTAJE_EXCEDENTE_EQUIPO;
    }
}
?>