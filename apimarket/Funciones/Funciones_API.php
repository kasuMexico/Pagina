<?php
declare(strict_types=1);

/**
 * Helpers compartidos para API Market V1.
 * Mantiene los endpoints en JSON, con rutas absolutas y consultas preparadas.
 */

if (!function_exists('api_json')) {
    function api_json(array $payload, int $status = 200): never
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('api_error')) {
    function api_error(int $status, string $message, array $extra = []): never
    {
        api_json(array_merge([
            'ok' => false,
            'error' => $message,
        ], $extra), $status);
    }
}

if (!function_exists('api_require_post')) {
    function api_require_post(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            api_error(405, 'Metodo no permitido');
        }
    }
}

if (!function_exists('api_read_json')) {
    function api_read_json(): array
    {
        api_require_post();
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            api_error(400, 'JSON requerido');
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            api_error(400, 'JSON invalido');
        }
        return $data;
    }
}

if (!function_exists('api_bearer_token')) {
    function api_bearer_token(): string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if ($header === '' && function_exists('getallheaders')) {
            $headers = getallheaders();
            $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        if (stripos($header, 'Bearer ') !== 0) {
            return '';
        }
        return trim(substr($header, 7));
    }
}

if (!function_exists('api_client_ip')) {
    function api_client_ip(): string
    {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? ''));
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        return substr((string)$ip, 0, 64);
    }
}

if (!function_exists('api_require_db')) {
    function api_require_db($db, string $name = 'base de datos'): mysqli
    {
        if (!($db instanceof mysqli) || $db->connect_errno) {
            api_error(500, 'Conexion no disponible: ' . $name);
        }
        return $db;
    }
}

if (!function_exists('api_bind_execute')) {
    function api_bind_execute(mysqli_stmt $stmt, string $types = '', array $params = []): void
    {
        if ($types !== '') {
            $refs = [];
            foreach ($params as $i => $value) {
                $refs[$i] = $value;
            }
            $args = [$types];
            foreach ($refs as $i => &$value) {
                $args[] = &$value;
            }
            $stmt->bind_param(...$args);
        }
        $stmt->execute();
    }
}

if (!function_exists('api_fetch_one')) {
    function api_fetch_one(mysqli $db, string $sql, string $types = '', array $params = []): ?array
    {
        $stmt = $db->prepare($sql);
        api_bind_execute($stmt, $types, $params);
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $row ?: null;
    }
}

if (!function_exists('api_fetch_all')) {
    function api_fetch_all(mysqli $db, string $sql, string $types = '', array $params = []): array
    {
        $stmt = $db->prepare($sql);
        api_bind_execute($stmt, $types, $params);
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
}

if (!function_exists('api_value')) {
    function api_value(mysqli $db, string $sql, string $types = '', array $params = [])
    {
        $row = api_fetch_one($db, $sql, $types, $params);
        if (!$row) {
            return null;
        }
        return array_values($row)[0] ?? null;
    }
}

if (!function_exists('api_param_type')) {
    function api_param_type($value): string
    {
        if (is_int($value)) {
            return 'i';
        }
        if (is_float($value)) {
            return 'd';
        }
        return 's';
    }
}

if (!function_exists('api_assert_identifier')) {
    function api_assert_identifier(string $name): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new InvalidArgumentException('Identificador SQL invalido');
        }
        return $name;
    }
}

if (!function_exists('api_insert')) {
    function api_insert(mysqli $db, string $table, array $data): int
    {
        if (!$data) {
            throw new InvalidArgumentException('Insert sin datos');
        }
        $table = api_assert_identifier($table);
        $columns = array_map('api_assert_identifier', array_keys($data));
        $marks = implode(', ', array_fill(0, count($columns), '?'));
        $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `', $columns) . '`) VALUES (' . $marks . ')';

        $values = array_values($data);
        $types = implode('', array_map('api_param_type', $values));

        $stmt = $db->prepare($sql);
        api_bind_execute($stmt, $types, $values);
        $id = (int)$db->insert_id;
        $stmt->close();
        return $id;
    }
}

if (!function_exists('api_update_by_id')) {
    function api_update_by_id(mysqli $db, string $table, array $data, int $id, string $idColumn = 'Id'): void
    {
        if (!$data) {
            return;
        }
        $table = api_assert_identifier($table);
        $idColumn = api_assert_identifier($idColumn);
        $sets = [];
        foreach (array_keys($data) as $column) {
            $column = api_assert_identifier((string)$column);
            $sets[] = '`' . $column . '` = ?';
        }
        $values = array_values($data);
        $values[] = $id;
        $types = implode('', array_map('api_param_type', $values));
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE `' . $idColumn . '` = ? LIMIT 1';
        $stmt = $db->prepare($sql);
        api_bind_execute($stmt, $types, $values);
        $stmt->close();
    }
}

if (!function_exists('api_now')) {
    function api_now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('api_norm_curp')) {
    function api_norm_curp(string $curp): string
    {
        return preg_replace('/\s+/', '', strtoupper(trim($curp))) ?: '';
    }
}

if (!function_exists('api_norm_phone_mx')) {
    function api_norm_phone_mx(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if (strlen($digits) === 12 && substr($digits, 0, 2) === '52') {
            $digits = substr($digits, 2);
        }
        return $digits;
    }
}

if (!function_exists('api_accepts')) {
    function api_accepts($value): bool
    {
        $v = mb_strtolower(trim((string)$value), 'UTF-8');
        return in_array($v, ['on', '1', 'true', 'si', 'sí', 'acepto', 'accept', 'accepted', 'checked'], true);
    }
}

if (!function_exists('api_age_from_curp')) {
    function api_age_from_curp(string $curp)
    {
        $curp = api_norm_curp($curp);
        if (strlen($curp) !== 18) {
            return null;
        }
        $yy = substr($curp, 4, 2);
        $mm = substr($curp, 6, 2);
        $dd = substr($curp, 8, 2);
        $year = ((int)$yy <= (int)date('y')) ? 2000 + (int)$yy : 1900 + (int)$yy;
        $birth = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, (int)$mm, (int)$dd));
        if (!$birth) {
            return null;
        }
        return (new DateTime('today'))->diff($birth)->y;
    }
}

if (!function_exists('api_funeral_product')) {
    function api_funeral_product($age)
    {
        if ($age >= 2 && $age <= 29) return '02a29';
        if ($age >= 30 && $age <= 49) return '30a49';
        if ($age >= 50 && $age <= 54) return '50a54';
        if ($age >= 55 && $age <= 59) return '55a59';
        if ($age >= 60 && $age <= 64) return '60a64';
        if ($age >= 65 && $age <= 69) return '65a69';
        return null;
    }
}

if (!function_exists('api_security_product')) {
    function api_security_product($age)
    {
        if ($age >= 2 && $age <= 29) return 'P02a29';
        if ($age >= 30 && $age <= 49) return 'P30a49';
        if ($age >= 50 && $age <= 54) return 'P50a54';
        if ($age >= 55 && $age <= 59) return 'P55a59';
        if ($age >= 60 && $age <= 64) return 'P60a64';
        if ($age >= 65 && $age <= 69) return 'P65a69';
        return null;
    }
}

if (!function_exists('api_transport_product')) {
    function api_transport_product($age)
    {
        if ($age >= 2 && $age <= 29) return 'T02a29';
        if ($age >= 30 && $age <= 49) return 'T30a49';
        if ($age >= 50 && $age <= 54) return 'T50a54';
        if ($age >= 55 && $age <= 59) return 'T55a59';
        if ($age >= 60 && $age <= 64) return 'T60a64';
        if ($age >= 65 && $age <= 69) return 'T65a69';
        return null;
    }
}

if (!function_exists('api_product_code')) {
    function api_product_code(string $requestedProduct, string $curp): ?string
    {
        $product = trim($requestedProduct);
        $age = api_age_from_curp($curp);
        if (strcasecmp($product, 'Funerario') === 0) {
            return api_funeral_product($age);
        }
        if (strcasecmp($product, 'Seguridad') === 0) {
            return api_security_product($age);
        }
        if (strcasecmp($product, 'Transporte') === 0) {
            return api_transport_product($age);
        }
        return $product !== '' ? $product : null;
    }
}

if (!function_exists('api_poliza_id_compacto')) {
    function api_poliza_id_compacto(string $curp, string $fechaAltaUsuario, string $masterKey): string
    {
        $ver = 'K2';
        $msg = strtoupper($curp) . '|' . $fechaAltaUsuario . '|' . $ver;
        $mac = hash_hmac('sha256', $msg, $masterKey);
        $hex60 = substr($mac, 0, 15);
        $body = strtoupper(str_pad(base_convert($hex60, 16, 36), 12, '0', STR_PAD_LEFT));
        $chk = strtoupper(base_convert(substr(hash('crc32b', $ver . $body), -2), 16, 36));
        $chk = substr(str_pad($chk, 2, '0', STR_PAD_LEFT), 0, 1);
        return $ver . $body . $chk;
    }
}

if (!function_exists('api_master_key')) {
    function api_master_key(): string
    {
        $key = (string)(getenv('KASU_MASTER_KEY') ?: ($_ENV['KASU_MASTER_KEY'] ?? ''));
        if ($key === '') {
            api_error(500, 'Config faltante: KASU_MASTER_KEY');
        }
        return $key;
    }
}

if (!function_exists('api_pago_si')) {
    function api_pago_si(float $tasaAnual, int $periodo, float $principal): float
    {
        if ($periodo <= 0) {
            return round($principal, 2);
        }
        $tm = ($tasaAnual / 100) / 12;
        if ($tm == 0.0) {
            return round($principal / $periodo, 2);
        }
        $factor = pow(1 + $tm, $periodo);
        return round(($principal * $tm * $factor) / ($factor - 1), 2);
    }
}

if (!function_exists('api_product_data')) {
    function api_product_data(mysqli $db, string $product): ?array
    {
        return api_fetch_one(
            $db,
            'SELECT Producto, Costo, TasaAnual, MaxCredito, comision, Fideicomiso, Perido FROM Productos WHERE Producto = ? LIMIT 1',
            's',
            [$product]
        );
    }
}

if (!function_exists('api_get_venta')) {
    function api_get_venta(mysqli $db, int $idVenta): ?array
    {
        return api_fetch_one($db, 'SELECT * FROM Venta WHERE Id = ? LIMIT 1', 'i', [$idVenta]);
    }
}

if (!function_exists('api_pago_credito')) {
    function api_pago_credito(mysqli $db, int $idVenta): float
    {
        $venta = api_get_venta($db, $idVenta);
        if (!$venta) return 0.0;
        $product = api_product_data($db, (string)$venta['Producto']);
        if (!$product) return 0.0;
        $periods = max(1, (int)$venta['NumeroPagos']);
        $periodPayment = api_pago_si((float)$product['TasaAnual'], $periods, (float)$venta['CostoVenta']);
        return round($periodPayment * $periods, 2);
    }
}

if (!function_exists('api_pago_credito_values')) {
    function api_pago_credito_values(mysqli $db, string $productCode, int $periods, float $cost): float
    {
        $product = api_product_data($db, $productCode);
        if (!$product) return 0.0;
        $periods = max(1, $periods);
        return round(api_pago_si((float)$product['TasaAnual'], $periods, $cost) * $periods, 2);
    }
}

if (!function_exists('api_sum_pagos')) {
    function api_sum_pagos(mysqli $db, int $idVenta, bool $includeMora = false): float
    {
        if ($includeMora) {
            $val = api_value($db, 'SELECT COALESCE(SUM(Cantidad),0) FROM Pagos WHERE IdVenta = ?', 'i', [$idVenta]);
        } else {
            $val = api_value($db, "SELECT COALESCE(SUM(Cantidad),0) FROM Pagos WHERE IdVenta = ? AND COALESCE(status,'') <> 'Mora'", 'i', [$idVenta]);
        }
        return round((float)$val, 2);
    }
}

if (!function_exists('api_saldo_credito')) {
    function api_saldo_credito(mysqli $db, int $idVenta): float
    {
        $venta = api_get_venta($db, $idVenta);
        if (!$venta) return 0.0;

        $fechaVenta = strtotime((string)$venta['FechaRegistro']);
        $fechaHoy = strtotime(date('Y-m-d'));
        if (!$fechaVenta || !$fechaHoy) return 0.0;

        $ultimoPago = api_value($db, 'SELECT MAX(FechaRegistro) FROM Pagos WHERE IdVenta = ?', 'i', [$idVenta]);
        $fechaUltimoPago = $ultimoPago ? strtotime((string)$ultimoPago) : $fechaVenta;

        $diasDesdeVenta = max(0, (int)floor(($fechaHoy - $fechaVenta) / 86400));
        $diasDesdeUltimoPago = max(0, (int)floor(($fechaHoy - $fechaUltimoPago) / 86400));

        $product = api_product_data($db, (string)$venta['Producto']);
        $tasaAnual = $product ? (float)$product['TasaAnual'] : 0.0;
        $i = ($tasaAnual / 100) / 365.0;
        $base = 1 + $i;
        $factorUltimoPago = ($diasDesdeUltimoPago > 0) ? pow($base, $diasDesdeUltimoPago) : 1.0;
        $factorVenta = ($diasDesdeVenta > 0) ? pow($base, $diasDesdeVenta) : 1.0;

        $pagos = api_sum_pagos($db, $idVenta, false);
        $valorAcumulado = $factorUltimoPago > 0 ? ($pagos / $factorUltimoPago) : $pagos;
        $capitalPendiente = (float)$venta['CostoVenta'] - $valorAcumulado;
        return round(max(0, $capitalPendiente * $factorVenta), 2);
    }
}

if (!function_exists('api_pago_periodo')) {
    function api_pago_periodo(mysqli $db, int $idVenta): float
    {
        $venta = api_get_venta($db, $idVenta);
        if (!$venta) return 0.0;

        $totalPagos = api_sum_pagos($db, $idVenta, false);
        $valorCredito = api_pago_credito($db, $idVenta);
        $saldo = api_saldo_credito($db, $idVenta);
        $product = api_product_data($db, (string)$venta['Producto']);
        if (!$product) return 0.0;

        $numPagos = max(1, (int)$venta['NumeroPagos']);
        $pagoNormal = api_pago_si((float)$product['TasaAnual'], $numPagos, (float)$venta['CostoVenta']) / 2;

        if ($saldo >= $valorCredito) {
            $pagosRealizados = ($pagoNormal > 0) ? ($totalPagos / $pagoNormal) : 0;
            $pagosRestantes = max(1, $numPagos - $pagosRealizados);
            return round($saldo / $pagosRestantes, 2);
        }

        $diferencia = $totalPagos - $valorCredito;
        return ($diferencia >= 0) ? 0.0 : round($pagoNormal, 2);
    }
}

if (!function_exists('api_pagos_pendientes')) {
    function api_pagos_pendientes(mysqli $db, int $idVenta): int
    {
        $venta = api_get_venta($db, $idVenta);
        if (!$venta) return 0;
        $product = api_product_data($db, (string)$venta['Producto']);
        if (!$product) return 0;

        $totalPagos = api_sum_pagos($db, $idVenta, false);
        $numPagos = max(1, (int)$venta['NumeroPagos']);
        $pagoNormal = api_pago_si((float)$product['TasaAnual'], $numPagos, (float)$venta['CostoVenta']);
        $pagosRealizados = ($pagoNormal > 0) ? ($totalPagos / $pagoNormal) : 0;
        return (int)round(max(0, $numPagos - $pagosRealizados), 0, PHP_ROUND_HALF_DOWN);
    }
}

if (!function_exists('api_estado_mora_corriente')) {
    function api_estado_mora_corriente(mysqli $db, int $idVenta): array
    {
        $venta = api_get_venta($db, $idVenta);
        if (!$venta) {
            return ['ok' => false, 'error' => 'Venta no encontrada'];
        }
        $product = api_product_data($db, (string)$venta['Producto']);
        $periodicidad = max(1, (int)($product['Perido'] ?? 1));
        $numMeses = max(1, (int)$venta['NumeroPagos']);
        $fechaAlta = new DateTime((string)$venta['FechaRegistro']);
        $hoy = new DateTime('today');

        if ($numMeses <= 1) {
            $cuota = api_saldo_credito($db, $idVenta);
            $totalCuotas = 1;
        } else {
            $cuota = api_pago_periodo($db, $idVenta);
            $totalCuotas = $numMeses * $periodicidad;
        }
        $cuota = round((float)$cuota, 2);

        $stepDias = max(1, (int)floor(30 / $periodicidad));
        $y = (int)$fechaAlta->format('Y');
        $m = (int)$fechaAlta->format('m');
        $d = (int)$fechaAlta->format('d');

        if ($periodicidad === 1) {
            $venc = new DateTime(date('Y-m-t', $fechaAlta->getTimestamp()));
            if ($venc <= $fechaAlta) $venc = (new DateTime("$y-$m-01"))->modify('last day of next month');
        } elseif ($periodicidad === 2) {
            $venc = ($d <= 15) ? new DateTime("$y-$m-15") : new DateTime(date('Y-m-t', $fechaAlta->getTimestamp()));
            if ($venc <= $fechaAlta) $venc->modify("+{$stepDias} days");
        } else {
            $venc = new DateTime("$y-$m-01");
            while ($venc <= $fechaAlta) $venc->modify("+{$stepDias} days");
        }

        $cuotasVencidas = 0;
        $v = clone $venc;
        while ($v <= $hoy && $cuotasVencidas < $totalCuotas) {
            $cuotasVencidas++;
            $v->modify("+{$stepDias} days");
        }

        $pagado = api_sum_pagos($db, $idVenta, false);
        $esperado = round($cuota * $cuotasVencidas, 2);
        $pendiente = max(0, round($esperado - $pagado, 2));
        $estado = ($pendiente <= 0.01) ? 'AL CORRIENTE' : 'MORA';

        return [
            'ok' => true,
            'estado' => $estado,
            'pagado_importe' => round($pagado, 2),
            'esperado_cuotas' => $cuotasVencidas,
            'esperado_importe' => $esperado,
            'pendiente_importe' => $pendiente,
            'cuota' => $cuota,
            'cuotas_vencidas' => $cuotasVencidas,
            'cuotas_atraso' => ($cuota > 0) ? (int)ceil($pendiente / $cuota) : 0,
            'proximo_vencimiento' => ($cuotasVencidas >= $totalCuotas) ? 'COMPLETADO' : $v->format('Y-m-d'),
            'total_cuotas' => $totalCuotas,
        ];
    }
}

if (!function_exists('api_find_venta_by_curp_poliza')) {
    function api_find_venta_by_curp_poliza(mysqli $db, string $curp, string $poliza): ?array
    {
        return api_fetch_one(
            $db,
            "SELECT
                v.*,
                c.Mail AS Mail,
                c.Telefono AS Telefono,
                u.ClaveCurp AS ClaveCurp,
                u.Nombre AS NombreUsuario,
                u.Paterno AS Paterno,
                u.Materno AS Materno
             FROM Venta v
             JOIN Usuario u ON u.IdContact = v.IdContact
             LEFT JOIN Contacto c ON c.id = v.IdContact
             WHERE u.ClaveCurp = ? AND v.IdFIrma = ?
             ORDER BY v.Id DESC
             LIMIT 1",
            'ss',
            [api_norm_curp($curp), trim($poliza)]
        );
    }
}

if (!function_exists('api_validar_usr_api')) {
    function api_validar_usr_api(mysqli $db, string $user, string $agent)
    {
        $parts = explode('_', $agent, 2);
        $usuario = $parts[0] ?? '';
        $unico = $parts[1] ?? '';
        if ($usuario === '' || $unico === '') {
            return false;
        }
        $row = api_fetch_one(
            $db,
            'SELECT Usuario, Id, Status FROM Secret_KEY WHERE Usuario = ? AND Id = ? AND IdUsuario = ? LIMIT 1',
            'sss',
            [$usuario, $unico, $user]
        );
        if (!$row) {
            return false;
        }
        if (array_key_exists('Status', $row) && $row['Status'] !== null) {
            return false;
        }
        return (string)$row['Usuario'] . '_' . (string)$row['Id'];
    }
}

if (!function_exists('api_token_verify')) {
    function api_token_verify(string $token, array $data, string $secretKey)
    {
        $timestamp = (int)($data['token_data']['timestamp'] ?? 0);
        $expires = (int)($data['token_data']['expires_in'] ?? 0);
        $curp = api_norm_curp((string)($data['curp_en_uso'] ?? ''));
        if ($token === '' || $secretKey === '' || $timestamp <= 0 || $expires <= 0 || $curp === '') {
            return false;
        }
        $firmaA = hash_hmac('sha256', $curp, $secretKey);
        $tokenDataJson = json_encode(['timestamp' => $timestamp, 'expires_in' => $expires], JSON_UNESCAPED_UNICODE);
        $firmaB = hash_hmac('sha256', (string)$tokenDataJson, $firmaA);
        if (!hash_equals($firmaB, $token)) {
            return false;
        }
        if (($timestamp + $expires) < time()) {
            return 'exced_time';
        }
        return true;
    }
}

if (!function_exists('api_validate_bearer_or_exit')) {
    function api_validate_bearer_or_exit(mysqli $db, array $data, string $apiKey = ''): array
    {
        $token = api_bearer_token();
        if ($token === '') {
            api_error(401, 'Falta Authorization: Bearer');
        }
        $user = trim((string)($data['nombre_de_usuario'] ?? ''));
        if ($user === '') {
            api_error(400, 'Falta nombre_de_usuario');
        }
        if (empty($data['token_data']) || !is_array($data['token_data'])) {
            api_error(400, 'Falta token_data');
        }
        $agent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        $usrAgentKey = api_validar_usr_api($db, $user, $agent);
        if (!$usrAgentKey) {
            api_error(403, 'Credenciales API invalidas');
        }
        $password = (string)(api_value($db, 'SELECT Pass FROM Empleados WHERE IdUsuario = ? LIMIT 1', 's', [$user]) ?? '');
        if ($password === '') {
            api_error(403, 'Usuario API no existe');
        }
        $secret = hash_hmac('sha256', (string)$usrAgentKey, $password);
        $valid = api_token_verify($token, $data, $secret);
        if ($valid === false) {
            api_error(401, 'Token invalido');
        }
        if ($valid === 'exced_time') {
            api_error(418, 'Tiempo de operacion excedido para este token');
        }
        if ($apiKey !== '') {
            global $mysqli_api;
            if (function_exists('api_access_has_grant') && !api_access_has_grant($mysqli_api ?? null, $user, $apiKey)) {
                api_error(403, 'Usuario API sin permiso para ' . $apiKey);
            }
        }
        return [
            'usuario' => $user,
            'usr_agent_key' => (string)$usrAgentKey,
            'secret_key' => $secret,
            'token' => $token,
        ];
    }
}

if (!function_exists('api_log_event')) {
    function api_log_event(mysqli $db, array $data, string $event, ?int $idContact = null, ?int $idVenta = null): void
    {
        try {
            api_insert($db, 'Eventos', [
                'Contacto' => $idContact ?? (string)($data['nombre_de_usuario'] ?? ''),
                'Evento' => $event,
                'MetodGet' => (string)($data['request'] ?? $data['tipo_peticion'] ?? ''),
                'Host' => (string)($_SERVER['PHP_SELF'] ?? ''),
                'Usuario' => substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'IdVta' => $idVenta,
                'IdUsr' => (string)($data['nombre_de_usuario'] ?? ''),
                'FechaRegistro' => api_now(),
            ]);
        } catch (Throwable $e) {
            error_log('[API Market] No se pudo registrar evento: ' . $e->getMessage());
        }
    }
}
