<?php
declare(strict_types=1);
use MercadoPago\MercadoPagoConfig;

require_once __DIR__ . '/../vendor/autoload.php';

(function (): void {
  $root = dirname(__DIR__);
  if (is_file($root.'/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($root);
    $dotenv->safeLoad();
  }
})();

function mp_access_token(): string {
  $t = getenv('MP_ACCESS_TOKEN') ?: ($_ENV['MP_ACCESS_TOKEN'] ?? '');
  if ($t === '') { http_response_code(500); exit('Config error: MP_ACCESS_TOKEN vacío.'); }
  return $t;
}
function mp_public_key(): string {
  return getenv('MP_PUBLIC_KEY') ?: ($_ENV['MP_PUBLIC_KEY'] ?? '');
}

MercadoPagoConfig::setAccessToken(mp_access_token());
