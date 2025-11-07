<?php
// FunctionUsageTracker.php
// Registro de uso de funciones con timestamp y archivo invocador.
// Fecha: 2025-11-04 — Revisado por JCCM

class FunctionUsageTracker {
    /**
     * Acumulado en memoria por función: "Clase::método" => conteo.
     * Se mantiene para compatibilidad con getUsage().
     */
    private static $usage = array();

    /**
     * Ruta del log de eventos detallados.
     * Por defecto en el mismo directorio de este archivo.
     */
    private static $logfile = __DIR__ . '/usage_log.txt';

    /**
     * Inicializa o cambia la ruta del archivo de log.
     * Opcional. Si no se llama, se usa el default.
     */
    public static function init(string $path): void {
        self::$logfile = $path;
    }

    /**
     * Incrementa el contador para la función indicada y escribe
     * un evento con timestamp y archivo invocador.
     *
     * @param string $class  Nombre de la clase.
     * @param string $method Nombre del método.
     */
    public static function increment($class, $method) {
        // 1) Contador en memoria
        $key = $class . '::' . $method;
        if (!isset(self::$usage[$key])) {
            self::$usage[$key] = 0;
        }
        self::$usage[$key]++;

        // 2) Toma de contexto de ejecución
        $ts = date('c'); // ISO 8601
        $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // Archivo “script” que originó la llamada
        $scriptFile = 'unknown';
        for ($i = count($bt) - 1; $i >= 0; $i--) {
            if (!empty($bt[$i]['file'])) {
                $scriptFile = $bt[$i]['file'];
                break;
            }
        }

        // Contexto HTTP (si aplica)
        $reqUri = $_SERVER['REQUEST_URI'] ?? '';
        $client = $_SERVER['REMOTE_ADDR'] ?? '';

        // 3) Línea a persistir en formato CSV simple
        $line = sprintf(
            "%s,class=%s,method=%s,script=%s,uri=%s,ip=%s,count=%d\n",
            $ts,
            $class,
            $method,
            basename($scriptFile),
            $reqUri,
            $client,
            self::$usage[$key]
        );

        // 4) Persistencia con bloqueo para evitar corrupción en concurrencia
        try {
            @file_put_contents(self::$logfile, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Silencioso por compatibilidad
        }
    }

    /**
     * Retorna el arreglo de uso de funciones acumulado en memoria.
     *
     * @return array<string,int>
     */
    public static function getUsage() {
        return self::$usage;
    }

    /**
     * Escribe un snapshot del acumulado en memoria a un archivo.
     * No afecta al log detallado por evento.
     *
     * @param string $filename Nombre del archivo (por defecto "usage_report.txt").
     */
    public static function outputUsage($filename = "usage_report.txt") {
        $data  = "Reporte de uso de funciones (snapshot " . date('c') . "):\n";
        $data .= print_r(self::$usage, true);
        @file_put_contents($filename, $data);
    }

    /**
     * Lee un log persistente y devuelve un agregado ['Clase::metodo' => int].
     * Tolera formatos:
     *  1) CSV línea a línea generado por increment() (actual).
     *  2) Salida tipo print_r de outputUsage() (histórico).
     *  3) JSON llano si en algún momento se guardó en ese formato.
     *
     * @param string|null $filename Ruta del archivo; usa el logfile por defecto si es null.
     * @return array<string,int>
     */
    public static function readPersistent(?string $filename = null): array {
        $file = $filename ?: self::$logfile;
        if (!is_file($file)) {
            return [];
        }
        $raw = @file_get_contents($file);
        if ($raw === false || $raw === '') {
            return [];
        }

        // 1) Intentar JSON directo o embebido
        $jsonStart = strpos($raw, '{');
        if ($jsonStart !== false) {
            $json = substr($raw, $jsonStart);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $out = [];
                foreach ($decoded as $k => $v) {
                    if (is_numeric($v)) {
                        $out[(string)$k] = (int)$v;
                    }
                }
                if ($out) {
                    return $out;
                }
            }
        }

        // 2) Parseo del CSV línea a línea generado por increment()
        // Formato:
        // 2025-11-04T01:23:45+00:00,class=MiClase,method=miMetodo,script=archivo.php,uri=/ruta,ip=1.2.3.4,count=7
        $out = [];
        $lines = preg_split('/\R/u', $raw) ?: [];
        foreach ($lines as $line) {
            if (strpos($line, 'class=') === false || strpos($line, 'method=') === false) {
                continue;
            }
            // Extraer class, method y count
            $class  = self::extractField($line, 'class');
            $method = self::extractField($line, 'method');
            $count  = self::extractField($line, 'count');

            if ($class !== '' && $method !== '') {
                $key = $class . '::' . $method;
                // Si la línea trae count acumulado, nos quedamos con el máximo visto
                // para evitar duplicar por re-lectura de eventos repetidos.
                $val = is_numeric($count) ? (int)$count : 1;
                if (!isset($out[$key]) || $val > $out[$key]) {
                    $out[$key] = $val;
                }
            }
        }
        if ($out) {
            return $out;
        }

        // 3) Parseo de print_r de outputUsage():
        // Ejemplo:
        // Reporte de uso...
        // Array
        // (
        //     [Clase::metodo] => 3
        //     [Otra::foo] => 12
        // )
        if (preg_match_all('/\[\s*([^\]]+)\s*\]\s*=>\s*(\d+)/', $raw, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $k = trim($row[1]);
                $v = (int)$row[2];
                $out[$k] = $v;
            }
            if ($out) {
                return $out;
            }
        }

        // 4) Formato alterno: “X se ha llamado N veces.”
        if (preg_match_all('/^(.*?)\s+se ha llamado\s+(\d+)\s+veces\./m', $raw, $m2, PREG_SET_ORDER)) {
            foreach ($m2 as $row) {
                $k = trim($row[1]);
                $v = (int)$row[2];
                if ($k !== '') {
                    $out[$k] = $v;
                }
            }
            if ($out) {
                return $out;
            }
        }

        return [];
    }

    /**
     * Extrae el valor de un campo clave=valor en una línea CSV simple.
     * Retorna '' si no está presente.
     */
    private static function extractField(string $line, string $key): string {
        // Busca "key=" y corta hasta la próxima coma o fin de línea.
        $needle = $key . '=';
        $pos = strpos($line, $needle);
        if ($pos === false) {
            return '';
        }
        $start = $pos + strlen($needle);
        $end = strpos($line, ',', $start);
        $val = ($end === false) ? substr($line, $start) : substr($line, $start, $end - $start);
        return trim($val);
    }
}

/**
 * Trait que añade un método para registrar el uso de la función.
 * Mantiene compatibilidad con el código existente.
 */
trait UsageTrackerTrait {
    protected function trackUsage() {
        FunctionUsageTracker::increment(__CLASS__, __FUNCTION__);
    }
}