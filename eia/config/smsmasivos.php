<?php
/**
 * Configuración SMS Masivos.
 * Lee de variables de entorno; define constantes/vars de respaldo.
 */
declare(strict_types=1);

return [
    'apikey'     => getenv('SMSMASIVOS_APIKEY') ?: 'e0d5181d6e711c49edb318a1971b21c106bd0c3b',
    // Base URL de la API v2: usar host oficial
    'base_url'   => rtrim(getenv('SMSMASIVOS_BASE_URL') ?: 'https://api.smsmasivos.com.mx', '/'),
    'sandbox'    => (int)(getenv('SMSMASIVOS_SANDBOX') ?: 0),
    'public_url' => rtrim(getenv('KASU_PUBLIC_BASE_URL') ?: 'https://kasu.com.mx', '/'),
];
