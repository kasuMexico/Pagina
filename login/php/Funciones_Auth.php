<?php
/**
 * Verifica las credenciales del vendedor en la tabla Empleados.
 * Si el usuario y la contraseña (en base64) coinciden, inicia sesión y
 * llena $_SESSION["Vendedor"] con el IdUsuario.
 *
 * @param mysqli $mysqli  Conexión activa a la base de datos.
 * @return bool           true si el login fue exitoso, false en caso contrario.
 */
function autenticarVendedor(mysqli $mysqli): bool {
    // 1. Recoger y sanear inputs
    $user = filter_input(INPUT_POST, 'Usuario', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'PassWord', FILTER_SANITIZE_STRING);
    if (empty($user) || $pass === null) {
        return false;
    }

    // 2. Encriptar la contraseña con base64
    $passEnc = hash('sha256', $pass);

    // 3. Preparar y ejecutar la consulta
    $sql = "SELECT `IdUsuario` FROM `Empleados` WHERE `IdUsuario` = ? AND `Pass` = ? LIMIT 1";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('ss', $user, $passEnc);
        $stmt->execute();
        $stmt->store_result();

        // 4. Si hay coincidencia, iniciar sesión
        if ($stmt->num_rows === 1) {
            session_start();
            // Guardamos el usuario en sesión
            $_SESSION["Vendedor"] = $user;
            $stmt->close();
            return true;
        }
        $stmt->close();
    }

    return false;
}
