<?php
// FunctionUsageTracker.php

class FunctionUsageTracker {
    // Arreglo estático para guardar la cuenta de uso.
    private static $usage = array();

    /**
     * Incrementa el contador para la función indicada.
     *
     * @param string $class  Nombre de la clase.
     * @param string $method Nombre de la función.
     */
    public static function increment($class, $method) {
        $key = $class . "::" . $method;
        if (!isset(self::$usage[$key])) {
            self::$usage[$key] = 0;
        }
        self::$usage[$key]++;
    }

    /**
     * Retorna el arreglo de uso de funciones.
     *
     * @return array
     */
    public static function getUsage() {
        return self::$usage;
    }

    /**
     * Escribe el reporte de uso en un archivo.
     *
     * @param string $filename Nombre del archivo (por defecto "usage_log.txt").
     */
    public static function outputUsage($filename = "usage_log.txt") {
        $data = "Reporte de uso de funciones:\n" . print_r(self::$usage, true);
        file_put_contents($filename, $data);
    }
}

/**
 * Trait que añade un método para registrar el uso de la función.
 * Simplemente llama a FunctionUsageTracker::increment() pasando el nombre de la clase y la función actual.
 */
trait UsageTrackerTrait {
    protected function trackUsage() {
        FunctionUsageTracker::increment(__CLASS__, __FUNCTION__);
    }
}
?>