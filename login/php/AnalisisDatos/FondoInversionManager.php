<?php
declare(strict_types=1);

/**
 * FondoInversionManager.php
 * - Lee historial real del fondo (tabla FondoInversion).
 * - Calcula rendimiento mensual y anualizado.
 * - Expone “dónde está invertido” (campo Instrumento/Ubicacion si existe).
 *
 * Compatibilidad: MariaDB/MySQL (Hostinger) + PHP 8.2
 */

final class FondoInversionManager
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->db->set_charset('utf8mb4');
    }

    /**
     * MariaDB: evitar SHOW TABLES LIKE ? con placeholders.
     * Usamos information_schema (soporta prepared).
     */
    private function tableExists(string $table): bool
    {
        $sql = "SELECT 1
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                LIMIT 1";
        $st = $this->db->prepare($sql);
        $st->bind_param('s', $table);
        $st->execute();
        $ok = (bool)$st->get_result()->fetch_row();
        $st->close();
        return $ok;
    }

    public function obtenerHistorial(int $limiteMeses = 6): array
    {
        if (!$this->tableExists('FondoInversion')) {
            return [];
        }

        $limiteMeses = max(1, $limiteMeses);

        $cols = $this->listarColumnas('FondoInversion');
        $colInstrumento = $this->pickFirstExisting($cols, [
            'Instrumento','instrumento','Ubicacion','ubicacion','DondeInvertido','Donde','Broker','CasaBolsa'
        ]);
        $colMes = $this->pickFirstExisting($cols, ['Mes','mes','Periodo','periodo']);
        $colVI  = $this->pickFirstExisting($cols, ['ValorInicial','valor_inicial','VI','Inicial']);
        $colVF  = $this->pickFirstExisting($cols, ['ValorFinal','valor_final','VF','Final']);
        $colAp  = $this->pickFirstExisting($cols, ['Aportaciones','aportaciones','Depositos','depositos','Entradas']);
        $colRet = $this->pickFirstExisting($cols, ['Retiros','retiros','Salidas']);

        if (!$colMes || !$colVI || !$colVF) {
            return [];
        }

        $sql = "SELECT
                    {$colMes} AS Mes,
                    {$colVI}  AS ValorInicial,
                    {$colVF}  AS ValorFinal,
                    COALESCE(" . ($colAp ?: "0") . ",0)  AS Aportaciones,
                    COALESCE(" . ($colRet ?: "0") . ",0) AS Retiros" .
                ($colInstrumento ? ", {$colInstrumento} AS Instrumento" : ", '' AS Instrumento") . "
                FROM FondoInversion
                ORDER BY Mes DESC
                LIMIT " . (int)$limiteMeses;

        $rs = $this->db->query($sql);
        $out = [];

        foreach ($rs as $r) {
            $vi = (float)($r['ValorInicial'] ?? 0);
            $vf = (float)($r['ValorFinal'] ?? 0);
            $ap = (float)($r['Aportaciones'] ?? 0);
            $re = (float)($r['Retiros'] ?? 0);

            $base = max(0.01, ($vi + $ap - $re));
            $rend = ($vf - ($vi + $ap - $re)) / $base;

            $out[] = [
                'Mes' => (string)$r['Mes'],
                'ValorInicial' => $vi,
                'ValorFinal' => $vf,
                'Aportaciones' => $ap,
                'Retiros' => $re,
                'Instrumento' => (string)($r['Instrumento'] ?? ''),
                'Rendimiento' => $rend,
                'MetaRendimientoMinimo' => 0.0,
                'RendimientoRealVsMeta' => 0.0,
            ];
        }

        return $out;
    }

    public function calcularEstadisticas(int $limiteMeses = 12): array
    {
        $hist = $this->obtenerHistorial($limiteMeses);
        if (!$hist) {
            return [
                'valor_actual' => 0.0,
                'rendimiento_promedio_mensual' => 0.0,
                'rendimiento_promedio_anual' => 0.0,
                'total_meses' => 0,
                'donde_invertido' => '',
                'meta_promedio' => 0.0,
                'diferencia_promedio' => 0.0,
            ];
        }

        $valorActual = (float)$hist[0]['ValorFinal'];
        $sumR = 0.0;
        $n = 0;

        $inst = [];
        foreach ($hist as $h) {
            $sumR += (float)$h['Rendimiento'];
            $n++;
            $t = trim((string)($h['Instrumento'] ?? ''));
            if ($t !== '') $inst[$t] = true;
        }

        $rMensual = $n > 0 ? ($sumR / $n) : 0.0;
        $rAnual = pow(1.0 + $rMensual, 12) - 1.0;

        return [
            'valor_actual' => $valorActual,
            'rendimiento_promedio_mensual' => $rMensual,
            'rendimiento_promedio_anual' => $rAnual,
            'total_meses' => $n,
            'donde_invertido' => implode(' | ', array_keys($inst)),
            'meta_promedio' => 0.0,
            'diferencia_promedio' => 0.0,
        ];
    }

    /**
     * Umbral operativo.
     * - Ahora acepta 0 argumentos (para que no truene tu Pwa_Analisis_Ventas.php).
     * - Si no pasas datos, devuelve ceros y estado CRÍTICO.
     */
    public function calcularUmbralInversion(?float $aportacion_total = null, ?float $costo_total_servicios = null): array
    {
        $aportacion_total = $aportacion_total ?? 0.0;
        $costo_total_servicios = $costo_total_servicios ?? 0.0;

        $aportacion_total = max(0.0, $aportacion_total);
        $costo_total_servicios = max(0.0, $costo_total_servicios);

        $brecha = max(0.0, $costo_total_servicios - $aportacion_total);
        $cobertura = $costo_total_servicios > 0 ? ($aportacion_total / $costo_total_servicios) : 0.0;

        $estado = 'CRÍTICO';
        if ($costo_total_servicios <= 0.0 && $aportacion_total <= 0.0) {
            $estado = 'CRÍTICO';
        } elseif ($cobertura >= 1.0) {
            $estado = 'SANO';
        } elseif ($cobertura >= 0.7) {
            $estado = 'MANEJABLE';
        }

        $aporteMensual5 = $brecha / (5 * 12);
        $aniosCerrar = $aporteMensual5 > 0 ? ($brecha / ($aporteMensual5 * 12)) : 0.0;

        return [
            'estado' => $estado,
            'aportacion_total' => $aportacion_total,
            'costo_total_servicios' => $costo_total_servicios,
            'brecha_actual' => $brecha,
            'cobertura_actual' => $cobertura,
            'aportacion_mensual_necesaria_5anios' => $aporteMensual5,
            'anios_para_cerrar_brecha' => $aniosCerrar,
        ];
    }

    /* ========================= helpers internos ========================= */

    private function listarColumnas(string $tabla): array
    {
        $rs = $this->db->query("SHOW COLUMNS FROM {$tabla}");
        $out = [];
        foreach ($rs as $r) {
            $out[] = (string)$r['Field'];
        }
        return $out;
    }

    private function pickFirstExisting(array $cols, array $candidates): ?string
    {
        $set = array_flip($cols);
        foreach ($candidates as $c) {
            if (isset($set[$c])) return $c;
        }
        return null;
    }
}
