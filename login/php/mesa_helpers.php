<?php
declare(strict_types=1);

if (function_exists('kasu_apply_error_settings')) {
    kasu_apply_error_settings(); // 2025-11-18: helpers reportan errores a /eia/error.log
}

if (!function_exists('mesa_status_chip')) {
    function mesa_status_chip(string $status): string
    {
        $map = [
            'ACTIVO'          => ['label' => 'Activo',           'icon' => 'check_circle',    'class' => 'status-ok'],
            'COBRANZA'        => ['label' => 'Cobranza',         'icon' => 'request_quote',   'class' => 'status-warn'],
            'COBRO'           => ['label' => 'Cobro',            'icon' => 'request_quote',   'class' => 'status-warn'],
            'PAGO'            => ['label' => 'Pago',             'icon' => 'paid',            'class' => 'status-ok'],
            'MORA'            => ['label' => 'En mora',          'icon' => 'schedule',        'class' => 'status-warn'],
            'CANCELADO'       => ['label' => 'Cancelado',        'icon' => 'cancel',          'class' => 'status-danger'],
            'CANCELO'         => ['label' => 'Canceló',          'icon' => 'cancel',          'class' => 'status-danger'],
            'ACTIVACION'      => ['label' => 'Activación',       'icon' => 'bolt',            'class' => 'status-info'],
            'PREVENTA'        => ['label' => 'Preventa',         'icon' => 'factory',         'class' => 'status-info'],
            'FUNERARIO'       => ['label' => 'Funerario',        'icon' => 'local_florist',   'class' => 'status-info'],
            'SEGURIDAD'       => ['label' => 'Seguridad',        'icon' => 'verified_user',   'class' => 'status-info'],
            'TRANSPORTE'      => ['label' => 'Transporte',       'icon' => 'local_shipping',  'class' => 'status-info'],
            'DISTRIBUIDOR'    => ['label' => 'Distribuidor',     'icon' => 'store',           'class' => 'status-info'],
            'RETIRO'          => ['label' => 'Retiro',           'icon' => 'savings',         'class' => 'status-info'],
            'VALIDO'          => ['label' => 'Válido',           'icon' => 'task_alt',        'class' => 'status-ok'],
            'VALIDO VENTA'    => ['label' => 'Válido Venta',     'icon' => 'shopping_cart',   'class' => 'status-ok'],
            'VENTA'           => ['label' => 'Venta',            'icon' => 'shopping_bag',    'class' => 'status-ok'],
            'OK'              => ['label' => 'OK',               'icon' => 'task_alt',        'class' => 'status-ok'],
        ];

        $key   = strtoupper(trim($status));
        $entry = $map[$key] ?? ['label' => ($status !== '' ? $status : 'Sin estado'), 'icon' => 'info', 'class' => 'status-default'];

        $label = htmlspecialchars($entry['label'], ENT_QUOTES, 'UTF-8');
        $icon  = htmlspecialchars($entry['icon'], ENT_QUOTES, 'UTF-8');
        $class = htmlspecialchars($entry['class'], ENT_QUOTES, 'UTF-8');

        return '<span class="status-chip ' . $class . '"><i class="material-icons" aria-hidden="true">'
            . $icon . '</i><span>' . $label . '</span></span>';
    }
}

if (!function_exists('kasu_ensure_marketing_roles')) {
    /**
     * Mantiene disponibles los puestos administrativos de Marketing sin depender
     * de identificadores fijos en la tabla Nivel.
     *
     * @return array{jefe:int,ejecutivo:int}
     */
    function kasu_ensure_marketing_roles(mysqli $mysqli): array
    {
        static $roles = null;
        if (is_array($roles)) {
            return $roles;
        }

        $wanted = [
            'jefe' => 'Jefe de Marketing',
            'ejecutivo' => 'Ejecutivo de Marketing',
        ];
        $roles = ['jefe' => 0, 'ejecutivo' => 0];

        try {
            $find = $mysqli->prepare('SELECT Id FROM Nivel WHERE NombreNivel = ? LIMIT 1');
            $insert = $mysqli->prepare('INSERT INTO Nivel (NombreNivel) VALUES (?)');

            foreach ($wanted as $key => $name) {
                $find->bind_param('s', $name);
                $find->execute();
                $row = $find->get_result()->fetch_assoc();

                if (!$row) {
                    $insert->bind_param('s', $name);
                    $insert->execute();
                    $roles[$key] = (int)$insert->insert_id;
                } else {
                    $roles[$key] = (int)$row['Id'];
                }
            }

            $find->close();
            $insert->close();
        } catch (Throwable $e) {
            error_log('No se pudieron preparar puestos de Marketing: ' . $e->getMessage());
        }

        return $roles;
    }
}

if (!function_exists('kasu_ensure_director_roles')) {
    /**
     * Mantiene disponibles los puestos directivos especializados.
     *
     * @return array{general:int,finanzas:int,marketing:int,comercial:int}
     */
    function kasu_ensure_director_roles(mysqli $mysqli): array
    {
        static $roles = null;
        if (is_array($roles)) {
            return $roles;
        }

        $wanted = [
            'general' => 'Director General',
            'finanzas' => 'Director de Finanzas',
            'marketing' => 'Director de Marketing',
            'comercial' => 'Director Comercial',
        ];
        $roles = ['general' => 0, 'finanzas' => 0, 'marketing' => 0, 'comercial' => 0];

        try {
            $find = $mysqli->prepare('SELECT Id FROM Nivel WHERE NombreNivel = ? LIMIT 1');
            $insert = $mysqli->prepare('INSERT INTO Nivel (NombreNivel) VALUES (?)');
            foreach ($wanted as $key => $name) {
                $find->bind_param('s', $name);
                $find->execute();
                $row = $find->get_result()->fetch_assoc();
                if ($row) {
                    $roles[$key] = (int)$row['Id'];
                    continue;
                }
                $insert->bind_param('s', $name);
                $insert->execute();
                $roles[$key] = (int)$insert->insert_id;
            }
            $find->close();
            $insert->close();
        } catch (Throwable $e) {
            error_log('No se pudieron preparar puestos directivos: ' . $e->getMessage());
        }
        return $roles;
    }
}

if (!function_exists('kasu_role_name')) {
    function kasu_role_name(mysqli $mysqli, int $nivel): string
    {
        static $cache = [];
        if ($nivel <= 0) {
            return '';
        }
        $cacheKey = spl_object_id($mysqli) . ':' . $nivel;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }
        try {
            $stmt = $mysqli->prepare('SELECT NombreNivel FROM Nivel WHERE Id = ? LIMIT 1');
            $stmt->bind_param('i', $nivel);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $cache[$cacheKey] = strtolower(trim((string)($row['NombreNivel'] ?? '')));
            return $cache[$cacheKey];
        } catch (Throwable $e) {
            error_log('No se pudo consultar el puesto: ' . $e->getMessage());
            return '';
        }
    }
}

if (!function_exists('kasu_director_role_key')) {
    function kasu_director_role_key(mysqli $mysqli, int $nivel): string
    {
        $name = kasu_role_name($mysqli, $nivel);
        $roles = [
            'ceo' => 'general',
            'director general' => 'general',
            'director de finanzas' => 'finanzas',
            'director de marketing' => 'marketing',
            'director comercial' => 'comercial',
        ];
        return $roles[$name] ?? '';
    }
}

if (!function_exists('kasu_level_has_permission')) {
    /**
     * Consulta la matriz de permisos de la base.
     * Devuelve null mientras la migración todavía no exista para conservar compatibilidad.
     */
    function kasu_level_has_permission(mysqli $mysqli, int $nivel, string $permiso): ?bool
    {
        static $tableAvailable = [];
        static $cache = [];

        if ($nivel <= 0 || $permiso === '') {
            return false;
        }

        $dbKey = (string)spl_object_id($mysqli);
        $cacheKey = $dbKey . ':' . $nivel . ':' . $permiso;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }
        if (($tableAvailable[$dbKey] ?? null) === false) {
            return null;
        }

        try {
            $stmt = $mysqli->prepare(
                'SELECT Permitido FROM Nivel_Permisos WHERE Nivel = ? AND Permiso = ? LIMIT 1'
            );
            $stmt->bind_param('is', $nivel, $permiso);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $tableAvailable[$dbKey] = true;
            $cache[$cacheKey] = (bool)($row['Permitido'] ?? false);
            return $cache[$cacheKey];
        } catch (mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1146) {
                $tableAvailable[$dbKey] = false;
                return null;
            }
            error_log('No se pudo consultar Nivel_Permisos: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('kasu_can_access_finance')) {
    function kasu_can_access_finance(mysqli $mysqli, int $nivel): bool
    {
        $permission = kasu_level_has_permission($mysqli, $nivel, 'finance');
        if ($permission !== null) {
            return $permission;
        }
        return in_array(kasu_director_role_key($mysqli, $nivel), ['general', 'finanzas'], true);
    }

    function kasu_can_access_marketing(mysqli $mysqli, int $nivel): bool
    {
        $permission = kasu_level_has_permission($mysqli, $nivel, 'marketing');
        if ($permission !== null) {
            return $permission;
        }
        return $nivel === 2 || in_array(kasu_director_role_key($mysqli, $nivel), ['general', 'marketing'], true);
    }

    function kasu_can_access_commercial(mysqli $mysqli, int $nivel): bool
    {
        $permission = kasu_level_has_permission($mysqli, $nivel, 'commercial');
        if ($permission !== null) {
            return $permission;
        }
        return ($nivel > 0 && $nivel <= 3)
            || in_array(kasu_director_role_key($mysqli, $nivel), ['general', 'marketing', 'comercial'], true);
    }

    function kasu_can_manage_employees(mysqli $mysqli, int $nivel): bool
    {
        $permission = kasu_level_has_permission($mysqli, $nivel, 'employees.manage');
        if ($permission !== null) {
            return $permission;
        }
        return $nivel === 1 || $nivel === 2 || kasu_director_role_key($mysqli, $nivel) === 'general';
    }

    function kasu_can_access_api_market(mysqli $mysqli, int $nivel): bool
    {
        $permission = kasu_level_has_permission($mysqli, $nivel, 'api_market');
        if ($permission !== null) {
            return $permission;
        }
        return $nivel === 2 || kasu_director_role_key($mysqli, $nivel) === 'general';
    }

    function kasu_require_finance_access(mysqli $mysqli, $basicas): int
    {
        if (empty($_SESSION['Vendedor'])) {
            http_response_code(401);
            exit('Sesión requerida.');
        }
        $nivel = (int)$basicas->BuscarCampos(
            $mysqli,
            'Nivel',
            'Empleados',
            'IdUsuario',
            (string)$_SESSION['Vendedor']
        );
        if (!kasu_can_access_finance($mysqli, $nivel)) {
            http_response_code(403);
            exit('No tienes permisos para consultar información financiera.');
        }
        return $nivel;
    }
}

if (!function_exists('kasu_marketing_role_key')) {
    function kasu_marketing_role_key(mysqli $mysqli, int $nivel): string
    {
        if ($nivel <= 0) {
            return '';
        }

        try {
            $name = kasu_role_name($mysqli, $nivel);
            if ($name === 'jefe de marketing') {
                return 'jefe';
            }
            if ($name === 'ejecutivo de marketing') {
                return 'ejecutivo';
            }
        } catch (Throwable $e) {
            error_log('No se pudo consultar puesto de Marketing: ' . $e->getMessage());
        }
        return '';
    }
}

if (!function_exists('kasu_load_empleados_tree')) {
    function kasu_load_empleados_tree(mysqli $mysqli): array
    {
        $byId = [];
        $children = [];
        $sql = "
            SELECT e.Id, e.IdUsuario, e.Equipo, e.Nivel,
                   COALESCE(n.NombreNivel, '') AS NombreNivel
            FROM Empleados e
            LEFT JOIN Nivel n ON n.Id = e.Nivel
        ";
        if ($res = $mysqli->query($sql)) {
            while ($row = $res->fetch_assoc()) {
                $id = (int)$row['Id'];
                $byId[$id] = [
                    'IdUsuario' => (string)($row['IdUsuario'] ?? ''),
                    'Equipo' => (int)($row['Equipo'] ?? 0),
                    'Nivel' => (int)($row['Nivel'] ?? 0),
                    'NombreNivel' => (string)($row['NombreNivel'] ?? ''),
                ];
                $parent = (int)($row['Equipo'] ?? 0);
                $children[$parent][] = $id;
            }
            $res->close();
        }
        return [$byId, $children];
    }

    function kasu_collect_descendant_ids(int $rootId, array $byId, array $children): array
    {
        if ($rootId <= 0) {
            return [];
        }
        $scope = [];
        $stack = [$rootId];
        while ($stack) {
            $current = (int)array_pop($stack);
            if (!isset($byId[$current]) || isset($scope[$current])) {
                continue;
            }
            $scope[$current] = true;
            foreach ($children[$current] ?? [] as $childId) {
                $stack[] = (int)$childId;
            }
        }
        return array_keys($scope);
    }

    function kasu_collect_descendants(int $rootId, array $byId, array $children): array
    {
        $scope = [];
        foreach (kasu_collect_descendant_ids($rootId, $byId, $children) as $id) {
            $usr = strtoupper(trim((string)($byId[$id]['IdUsuario'] ?? '')));
            if ($usr !== '') {
                $scope[$usr] = true;
            }
        }
        return array_keys($scope);
    }

    function kasu_find_ancestor_level(int $startId, array $byId, int $targetLevel): ?int
    {
        $current = $startId;
        while ($current > 0 && isset($byId[$current])) {
            if ((int)($byId[$current]['Nivel'] ?? 0) === $targetLevel) {
                return $current;
            }
            $parent = (int)($byId[$current]['Equipo'] ?? 0);
            if ($parent === 0 || $parent === $current) {
                break;
            }
            $current = $parent;
        }
        return null;
    }

    function kasu_scope_user_ids(int $nivel, int $empleadoId, array $byId, array $children): ?array
    {
        $roleName = strtolower(trim((string)($byId[$empleadoId]['NombreNivel'] ?? '')));
        if (($nivel > 0 && $nivel <= 2) || in_array($roleName, ['ceo', 'director general'], true)) {
            return null;
        }
        if ($nivel <= 0 || $empleadoId <= 0) {
            return [];
        }

        $rootId = $empleadoId;
        if ($nivel === 5) {
            $gerente = kasu_find_ancestor_level($empleadoId, $byId, 3);
            if ($gerente !== null) {
                $rootId = $gerente;
            }
        }
        return kasu_collect_descendants($rootId, $byId, $children);
    }
}

if (!function_exists('kasu_marketing_assignment_ids')) {
    function kasu_marketing_assignment_ids(
        string $marketingRole,
        int $empleadoId,
        array $byId,
        array $children
    ): ?array {
        if ($marketingRole === '') {
            return null;
        }
        if ($marketingRole === 'ejecutivo') {
            return [];
        }

        $ids = [];
        foreach (kasu_collect_descendant_ids($empleadoId, $byId, $children) as $id) {
            $roleName = strtolower(trim((string)($byId[$id]['NombreNivel'] ?? '')));
            if ($roleName === 'ejecutivo de marketing') {
                $ids[] = (int)$id;
            }
        }
        return $ids;
    }
}

if (!function_exists('kasu_marketing_can_manage_prospect')) {
    function kasu_marketing_can_manage_prospect(
        mysqli $mysqli,
        mysqli $pros,
        string $usuario,
        int $prospectoId
    ): bool {
        if ($usuario === '' || $prospectoId <= 0) {
            return false;
        }

        $stmtEmployee = $mysqli->prepare('SELECT Id, Nivel FROM Empleados WHERE IdUsuario = ? LIMIT 1');
        $stmtEmployee->bind_param('s', $usuario);
        $stmtEmployee->execute();
        $employee = $stmtEmployee->get_result()->fetch_assoc();
        $stmtEmployee->close();

        $nivel = (int)($employee['Nivel'] ?? 0);
        if (kasu_marketing_role_key($mysqli, $nivel) === '') {
            return true;
        }

        [$byId, $children] = kasu_load_empleados_tree($mysqli);
        $scopeUsers = kasu_scope_user_ids($nivel, (int)($employee['Id'] ?? 0), $byId, $children) ?? [];
        $scopeSet = array_fill_keys($scopeUsers, true);

        $stmtProspect = $pros->prepare('SELECT Asignado FROM prospectos WHERE Id = ? LIMIT 1');
        $stmtProspect->bind_param('i', $prospectoId);
        $stmtProspect->execute();
        $prospect = $stmtProspect->get_result()->fetch_assoc();
        $stmtProspect->close();

        $assignedId = (int)($prospect['Asignado'] ?? 0);
        $assignedUser = strtoupper(trim((string)($byId[$assignedId]['IdUsuario'] ?? '')));
        return $assignedUser !== '' && isset($scopeSet[$assignedUser]);
    }
}
