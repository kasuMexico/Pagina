<?php
// Inicia la sesión
session_start();

// Elimina todas las variables de sesión
$_SESSION = array();

// Si se usan cookies para la sesión, se borra la cookie asociada.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruye la sesión
session_destroy();

// Redirecciona al usuario a la página de registro (o login)
header('Location: https://kasu.com.mx/login/registro.php');
exit;
?>
