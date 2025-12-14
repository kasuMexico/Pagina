<?php
declare(strict_types=1);

/**
 * ConfigFondoFunerario.php
 * Fuente de verdad para parámetros del modelo de fondo funerario.
 *
 * - Máximo del servicio: 2600 UDIs.
 * - Proyectado: promedio real EntregaServicio.Costo (ventana meses) CAPADO al máximo.
 * - Hoy (modelo): por defecto usa el proyectado (ventana MESES_COSTO_SERVICIO).
 */

final class ConfigFondoFunerario
{
    public const TOPE_UDIS_SERVICIO = 2600.0;

    /** UDI actual (fallback). Si luego lo mueves a BD, aquí lo consumes. */
    public static float $UDI_ACTUAL = 8.20;

    /**
     * Costo servicio proyectado por póliza (MXN) cuando no hay datos históricos.
     * Es el valor base que quieres ver en el dashboard (ej: $14,500).
     */
    public const COSTO_SERVICIO_PROYECTADO_DEFAULT = 14500.0;

    /** Ventana por defecto para el “hoy/modelo” */
    public const MESES_COSTO_SERVICIO = 6;

    /** Ventana para “proyectado” (más largo) */
    public const MESES_COSTO_PROYECTADO = 24;

    public const EDAD_META_EVENTO = 80;
    public const EDAD_MIN = 18;
    public const ANIOS_MIN = 1;

    public static function getUdiActual(): float
    {
        return max(0.0001, (float)self::$UDI_ACTUAL);
    }

    /** Máximo del servicio en MXN (2600 UDI * UDI) */
    public static function getCostoServicioMaximo(): float
    {
        return self::TOPE_UDIS_SERVICIO * self::getUdiActual();
    }

    /** Alias compatibilidad */
    public static function getTopeServicioMXN(): float
    {
        return self::getCostoServicioMaximo();
    }

    /** Promedio real del costo (últimos $meses meses). 0 si no hay datos. */
    public static function getCostoServicioReal(mysqli $db, int $meses): float
    {
        $meses = max(1, $meses);
        $ini = (new DateTimeImmutable('today', new DateTimeZone('America/Mexico_City')))
            ->modify('-' . $meses . ' months')
            ->format('Y-m-d');

        $sql = "SELECT AVG(Costo) AS avg_costo
                FROM EntregaServicio
                WHERE Costo IS NOT NULL AND Costo > 0
                  AND (FechaEntrega IS NULL OR FechaEntrega >= ?)";

        $st = $db->prepare($sql);
        $st->bind_param('s', $ini);
        $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $st->close();

        return max(0.0, (float)($row['avg_costo'] ?? 0.0));
    }

    /**
     * Proyectado = promedio real en ventana larga, CAPADO al máximo.
     * Si no hay datos reales -> máximo.
     */
    public static function getCostoServicioProyectado(mysqli $db, int $mesesBack = self::MESES_COSTO_PROYECTADO): float
    {
        $real = self::getCostoServicioReal($db, $mesesBack);
        $max  = self::getCostoServicioMaximo();

        // Si no hay datos, usa el valor base configurado (no el máximo)
        if ($real <= 0.0) return min(self::COSTO_SERVICIO_PROYECTADO_DEFAULT, $max);

        return min($real, $max);
    }

    /**
     * “Hoy” usado por el modelo.
     * Si quieres que TODO sea proyectado, deja esto apuntando a Proyectado.
     */
    public static function getCostoServicioHoy(mysqli $db): float
    {
        // Importante: aquí decides tu “base”
        return self::getCostoServicioProyectado($db, self::MESES_COSTO_PROYECTADO);
        // Si quisieras “más corto”, usa MESES_COSTO_SERVICIO.
    }

    public static function estimarAniosHastaEvento(int $edad): int
    {
        $edad = max(self::EDAD_MIN, $edad);
        $anios = self::EDAD_META_EVENTO - $edad;
        return max(self::ANIOS_MIN, (int)$anios);
    }

    public static function pctToProp($v): float
    {
        if ($v === null || $v === '') return 0.0;
        return max(0.0, ((float)$v) / 100.0);
    }

    public static function cargarProductos(mysqli $db): array
    {
        $out = [];
        $sql = "SELECT
                    Producto, Costo, TasaAnual, Perido, PlazoPagos, MaxCredito,
                    Fideicomiso, TFideicomiso,
                    Comision_Vta, Comision_Cob, Porc_Vtas, Porc_Cobr,
                    Validez, FechaRegistro
                FROM Productos";

        $rs = $db->query($sql);
        foreach ($rs as $p) {
            $prod = (string)$p['Producto'];
            if ($prod === '') continue;

            $out[$prod] = [
                'Producto'      => $prod,
                'Costo'         => (float)($p['Costo'] ?? 0),
                'TasaAnual'     => (float)($p['TasaAnual'] ?? 0),
                'Perido'        => (int)($p['Perido'] ?? 1),
                'PlazoPagos'    => (string)($p['PlazoPagos'] ?? ''),
                'MaxCredito'    => (int)($p['MaxCredito'] ?? 0),

                'Fideicomiso'   => self::pctToProp($p['Fideicomiso'] ?? 0),
                'TFideicomiso'  => self::pctToProp($p['TFideicomiso'] ?? 0),

                'Comision_Vta'  => (float)($p['Comision_Vta'] ?? 0),
                'Comision_Cob'  => (float)($p['Comision_Cob'] ?? 0),
                'Porc_Vtas'     => self::pctToProp($p['Porc_Vtas'] ?? 0),
                'Porc_Cobr'     => self::pctToProp($p['Porc_Cobr'] ?? 0),

                'Validez'       => (string)($p['Validez'] ?? ''),
                'FechaRegistro' => (string)($p['FechaRegistro'] ?? ''),
            ];
        }
        return $out;
    }
}
