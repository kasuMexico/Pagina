<?php
require_once dirname(__DIR__, 2) . '/eia/librerias.php';
kasu_apply_error_settings(); // 2025-11-18: incluso redirecciones reportan fallos
header('Location: https://kasu.com.mx/login');
?>
