<?php
require __DIR__ . '/../config/mp.php';
header('Content-Type: text/plain; charset=utf-8');
echo "SDK OK\n";
echo "PUBLIC_KEY: " . (mp_public_key() ? 'OK' : 'VACIA') . "\n";