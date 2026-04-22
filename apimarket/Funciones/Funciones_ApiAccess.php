<?php
declare(strict_types=1);

if (!function_exists('api_access_h')) {
    function api_access_h($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('api_access_schema')) {
    function api_access_schema(mysqli $db): void
    {
        $db->query("
            CREATE TABLE IF NOT EXISTS api_access_users (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(120) NOT NULL,
                correo VARCHAR(190) NOT NULL,
                empresa VARCHAR(160) DEFAULT NULL,
                telefono VARCHAR(30) DEFAULT NULL,
                password_hash VARCHAR(255) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_api_access_users_correo (correo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS api_access_requests (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT UNSIGNED DEFAULT NULL,
                nombre VARCHAR(120) NOT NULL,
                correo VARCHAR(190) NOT NULL,
                empresa VARCHAR(160) DEFAULT NULL,
                telefono VARCHAR(30) DEFAULT NULL,
                website VARCHAR(190) DEFAULT NULL,
                api_accounts TINYINT(1) NOT NULL DEFAULT 0,
                api_customer TINYINT(1) NOT NULL DEFAULT 0,
                api_payments TINYINT(1) NOT NULL DEFAULT 0,
                api_validate_mexico TINYINT(1) NOT NULL DEFAULT 0,
                saldo_solicitado_centavos INT NOT NULL DEFAULT 0,
                mensaje TEXT,
                estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
                admin_user VARCHAR(50) DEFAULT NULL,
                admin_notes TEXT,
                api_user VARCHAR(20) DEFAULT NULL,
                secret_key_usuario VARCHAR(50) DEFAULT NULL,
                secret_key_id INT DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_api_access_requests_estado (estado),
                KEY idx_api_access_requests_user (user_id),
                KEY idx_api_access_requests_correo (correo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS api_access_grants (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                api_user VARCHAR(50) NOT NULL,
                api_key VARCHAR(50) NOT NULL,
                enabled TINYINT(1) NOT NULL DEFAULT 1,
                request_id INT UNSIGNED DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_api_access_grants_user_api (api_user, api_key),
                KEY idx_api_access_grants_request (request_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS api_subdistribuidores (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(120) NOT NULL,
                correo VARCHAR(190) DEFAULT NULL,
                empresa VARCHAR(160) DEFAULT NULL,
                telefono VARCHAR(30) DEFAULT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_api_subdistribuidores_correo (correo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS api_usuarios (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                subdistribuidor_id INT UNSIGNED NOT NULL,
                nombre_de_usuario VARCHAR(50) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_api_usuarios_nombre (nombre_de_usuario),
                KEY idx_api_usuarios_sub (subdistribuidor_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS api_wallets (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                subdistribuidor_id INT UNSIGNED NOT NULL,
                saldo_centavos INT NOT NULL DEFAULT 0,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_api_wallets_sub (subdistribuidor_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->query("
            CREATE TABLE IF NOT EXISTS api_wallet_movs (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                subdistribuidor_id INT UNSIGNED NOT NULL,
                tipo VARCHAR(20) NOT NULL,
                monto_centavos INT NOT NULL,
                ref VARCHAR(190) DEFAULT NULL,
                meta_json TEXT,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_api_wallet_movs_sub (subdistribuidor_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}

if (!function_exists('api_access_money_to_centavos')) {
    function api_access_money_to_centavos($amount): int
    {
        $normalized = str_replace([',', '$', ' '], ['', '', ''], (string)$amount);
        if ($normalized === '' || !is_numeric($normalized)) {
            return 0;
        }
        return max(0, (int)round(((float)$normalized) * 100));
    }
}

if (!function_exists('api_access_centavos_to_money')) {
    function api_access_centavos_to_money(int $centavos): string
    {
        return '$' . number_format($centavos / 100, 2, '.', ',') . ' MXN';
    }
}

if (!function_exists('api_access_slug_user')) {
    function api_access_slug_user(string $name): string
    {
        $raw = strtoupper(trim($name));
        $raw = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $raw) ?: $raw;
        $raw = preg_replace('/[^A-Z0-9]+/', '_', $raw) ?: 'API_USER';
        $raw = trim($raw, '_');
        if ($raw === '') {
            $raw = 'API_USER';
        }
        return substr($raw, 0, 20);
    }
}

if (!function_exists('api_access_random_private_key')) {
    function api_access_random_private_key(): string
    {
        return bin2hex(random_bytes(24));
    }
}

if (!function_exists('api_access_default_api_user')) {
    function api_access_default_api_user(array $request): string
    {
        $base = (string)($request['empresa'] ?? '');
        if (trim($base) === '') {
            $base = (string)($request['nombre'] ?? 'API');
        }
        return api_access_slug_user('API_' . $base);
    }
}

if (!function_exists('api_access_request_apis')) {
    function api_access_request_apis(array $row): array
    {
        $apis = [];
        if ((int)($row['api_accounts'] ?? 0) === 1) {
            $apis[] = 'API_ACCOUNTS';
        }
        if ((int)($row['api_customer'] ?? 0) === 1) {
            $apis[] = 'API_CUSTOMER';
        }
        if ((int)($row['api_payments'] ?? 0) === 1) {
            $apis[] = 'API_PAYMENTS';
        }
        if ((int)($row['api_validate_mexico'] ?? 0) === 1) {
            $apis[] = 'Validate_Mexico';
        }
        return $apis;
    }
}

if (!function_exists('api_access_sync_grants')) {
    function api_access_sync_grants(mysqli $db, string $apiUser, array $request): void
    {
        api_access_schema($db);
        $map = [
            'API_ACCOUNTS' => (int)($request['api_accounts'] ?? 0) === 1,
            'API_CUSTOMER' => (int)($request['api_customer'] ?? 0) === 1,
            'API_PAYMENTS' => (int)($request['api_payments'] ?? 0) === 1,
            'Validate_Mexico' => (int)($request['api_validate_mexico'] ?? 0) === 1,
        ];
        $requestId = (int)($request['id'] ?? 0);
        $stmt = $db->prepare('
            INSERT INTO api_access_grants (api_user, api_key, enabled, request_id)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), request_id = VALUES(request_id)
        ');
        foreach ($map as $apiKey => $enabled) {
            if (!$enabled) {
                continue;
            }
            $enabledInt = $enabled ? 1 : 0;
            $stmt->bind_param('ssii', $apiUser, $apiKey, $enabledInt, $requestId);
            $stmt->execute();
        }
        $stmt->close();
    }
}

if (!function_exists('api_access_has_grant')) {
    function api_access_has_grant(?mysqli $db, string $apiUser, string $apiKey): bool
    {
        if (!$db instanceof mysqli || $apiKey === '' || $apiUser === '') {
            return true;
        }
        try {
            $exists = $db->query("SHOW TABLES LIKE 'api_access_grants'");
            if (!$exists || $exists->num_rows === 0) {
                return true;
            }

            $stmt = $db->prepare('SELECT COUNT(*) AS total FROM api_access_grants WHERE api_user = ?');
            $stmt->bind_param('s', $apiUser);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ((int)($row['total'] ?? 0) === 0) {
                return true;
            }

            $stmt = $db->prepare('SELECT enabled FROM api_access_grants WHERE api_user = ? AND api_key = ? LIMIT 1');
            $stmt->bind_param('ss', $apiUser, $apiKey);
            $stmt->execute();
            $grant = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $grant && (int)$grant['enabled'] === 1;
        } catch (Throwable $e) {
            error_log('[API_ACCESS_GRANT] ' . $e->getMessage());
            return true;
        }
    }
}

if (!function_exists('api_access_insert_or_update_empleado')) {
    function api_access_insert_or_update_empleado(mysqli $db, string $apiUser, string $privateKey, string $name): void
    {
        $exists = false;
        $stmt = $db->prepare('SELECT Id FROM Empleados WHERE IdUsuario = ? LIMIT 1');
        $stmt->bind_param('s', $apiUser);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $exists = true;
        }

        if ($exists) {
            $stmt = $db->prepare('UPDATE Empleados SET Pass = ?, Status = 1 WHERE IdUsuario = ?');
            $stmt->bind_param('ss', $privateKey, $apiUser);
            $stmt->execute();
            $stmt->close();
            return;
        }

        $nivel = 7;
        $sucursal = 1;
        $idContacto = 0;
        $nomina = 0;
        $nombre = substr($name !== '' ? $name : $apiUser, 0, 60);
        $stmt = $db->prepare('
            INSERT INTO Empleados (Nivel, Sucursal, Nombre, IdUsuario, IdContacto, Pass, Nomina, Status, FechaAlta)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, CURDATE())
        ');
        $stmt->bind_param('iissisi', $nivel, $sucursal, $nombre, $apiUser, $idContacto, $privateKey, $nomina);
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('api_access_create_secret_key')) {
    function api_access_create_secret_key(mysqli $db, string $apiUser, string $secretUsuario): int
    {
        $secretUsuario = api_access_slug_user($secretUsuario);
        $stmt = $db->prepare('INSERT INTO Secret_KEY (IdUsuario, Usuario, Status) VALUES (?, ?, NULL)');
        $stmt->bind_param('ss', $apiUser, $secretUsuario);
        $stmt->execute();
        $id = (int)$db->insert_id;
        $stmt->close();
        return $id;
    }
}

if (!function_exists('api_access_set_secret_status')) {
    function api_access_set_secret_status(mysqli $db, int $secretId, ?string $status): void
    {
        $stmt = $db->prepare('UPDATE Secret_KEY SET Status = ? WHERE Id = ?');
        $stmt->bind_param('si', $status, $secretId);
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('api_access_ensure_validate_mexico_user')) {
    function api_access_ensure_validate_mexico_user(mysqli $db, array $request, string $apiUser, int $initialSaldoCentavos = 0, string $adminUser = ''): int
    {
        api_access_schema($db);

        $subId = 0;
        $stmt = $db->prepare('SELECT subdistribuidor_id FROM api_usuarios WHERE nombre_de_usuario = ? LIMIT 1');
        $stmt->bind_param('s', $apiUser);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $subId = (int)$row['subdistribuidor_id'];
        }

        if ($subId <= 0) {
            $nombre = (string)($request['nombre'] ?? $apiUser);
            $correo = (string)($request['correo'] ?? '');
            $empresa = (string)($request['empresa'] ?? '');
            $telefono = (string)($request['telefono'] ?? '');
            $stmt = $db->prepare('
                INSERT INTO api_subdistribuidores (nombre, correo, empresa, telefono, activo)
                VALUES (?, ?, ?, ?, 1)
            ');
            $stmt->bind_param('ssss', $nombre, $correo, $empresa, $telefono);
            $stmt->execute();
            $subId = (int)$db->insert_id;
            $stmt->close();

            $stmt = $db->prepare('
                INSERT INTO api_usuarios (subdistribuidor_id, nombre_de_usuario, activo)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE subdistribuidor_id = VALUES(subdistribuidor_id), activo = 1
            ');
            $stmt->bind_param('is', $subId, $apiUser);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $db->prepare('UPDATE api_usuarios SET activo = 1 WHERE nombre_de_usuario = ?');
            $stmt->bind_param('s', $apiUser);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $db->prepare('
            INSERT INTO api_wallets (subdistribuidor_id, saldo_centavos)
            VALUES (?, 0)
            ON DUPLICATE KEY UPDATE saldo_centavos = saldo_centavos
        ');
        $stmt->bind_param('i', $subId);
        $stmt->execute();
        $stmt->close();

        if ($initialSaldoCentavos > 0) {
            api_access_add_wallet_balance($db, $apiUser, $initialSaldoCentavos, 'Alta API Validate_Mexico', $adminUser);
        }

        return $subId;
    }
}

if (!function_exists('api_access_add_wallet_balance')) {
    function api_access_add_wallet_balance(mysqli $db, string $apiUser, int $amountCentavos, string $reason = '', string $adminUser = ''): void
    {
        api_access_schema($db);
        if ($amountCentavos <= 0) {
            return;
        }

        $stmt = $db->prepare('SELECT subdistribuidor_id FROM api_usuarios WHERE nombre_de_usuario = ? AND activo = 1 LIMIT 1');
        $stmt->bind_param('s', $apiUser);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) {
            throw new RuntimeException('Usuario Validate_Mexico no existe o está inactivo.');
        }
        $subId = (int)$row['subdistribuidor_id'];

        $db->begin_transaction();
        try {
            $stmt = $db->prepare('
                INSERT INTO api_wallets (subdistribuidor_id, saldo_centavos)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE saldo_centavos = saldo_centavos + VALUES(saldo_centavos)
            ');
            $stmt->bind_param('ii', $subId, $amountCentavos);
            $stmt->execute();
            $stmt->close();

            $tipo = 'ABONO';
            $meta = json_encode([
                'reason' => $reason,
                'admin_user' => $adminUser,
                'source' => 'Mesa_ApiMarket',
            ], JSON_UNESCAPED_UNICODE);
            $ref = 'WALLET|' . $apiUser . '|' . date('YmdHis');
            $stmt = $db->prepare('
                INSERT INTO api_wallet_movs (subdistribuidor_id, tipo, monto_centavos, ref, meta_json)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->bind_param('isiss', $subId, $tipo, $amountCentavos, $ref, $meta);
            $stmt->execute();
            $stmt->close();

            $db->commit();
        } catch (Throwable $e) {
            $db->rollback();
            throw $e;
        }
    }
}
