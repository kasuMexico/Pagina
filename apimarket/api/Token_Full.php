<?php
declare(strict_types=1);

if (!function_exists('api_token_full_handle')) {
    function api_token_full_handle(mysqli $db, array $data, Seguridad $seguridad): void
    {
        if (($data['tipo_peticion'] ?? '') !== 'token_full') {
            api_error(404, 'Peticion desconocida');
        }

        $user = trim((string)($data['nombre_de_usuario'] ?? ''));
        $firmaKey = trim((string)($data['firma_KEY'] ?? ''));
        $curp = api_norm_curp((string)($data['curp_en_uso'] ?? ''));

        if ($user === '' || $firmaKey === '' || $curp === '') {
            api_error(400, 'Faltan nombre_de_usuario, firma_KEY o curp_en_uso');
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

        $secretKey = hash_hmac('sha256', (string)$usrAgentKey, $password);
        $datosCurp = $seguridad->peticion_get($curp);
        if (!is_array($datosCurp)
            || strcasecmp((string)($datosCurp['Response'] ?? ''), 'correct') !== 0
            || strtoupper((string)($datosCurp['StatusCurp'] ?? '')) === 'BD') {
            api_error(417, 'CURP no valida o no elegible');
        }

        $expectedFirma = hash_hmac('sha256', $curp, $secretKey);
        if (!hash_equals($expectedFirma, $firmaKey)) {
            api_error(401, 'Firma invalida');
        }

        $tokenData = [
            'timestamp' => time(),
            'expires_in' => 600,
        ];
        $tokenJson = json_encode($tokenData, JSON_UNESCAPED_UNICODE);
        $token = hash_hmac('sha256', (string)$tokenJson, $expectedFirma);

        api_json([
            'ok' => true,
            'token' => $token,
            'nombre' => trim(
                (string)($datosCurp['Nombre'] ?? '') . ' ' .
                (string)($datosCurp['Paterno'] ?? '') . ' ' .
                (string)($datosCurp['Materno'] ?? '')
            ),
            'token_data' => $tokenData,
        ]);
    }
}

if (realpath((string)($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    require_once __DIR__ . '/../librerias_api.php';
    $db = api_require_db($mysqli ?? null, 'ventas');
    $data = api_read_json();
    api_token_full_handle($db, $data, $seguridad);
}
