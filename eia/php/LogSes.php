<?PHP
//indicar que se inicia una sesion
	session_start();
    //Vaciamos las variables y redireccionamos si el pago es a credito
        unset($_SESSION["Ventana"]);
        unset($_SESSION["Producto"]);
        unset($_SESSION["nombre"]);
        unset($_SESSION["Cnc"]);
        unset($_SESSION["Costo"]);
        unset($_SESSION["Tasa"]);
        unset($_SESSION["Mail"]);
        unset($_SESSION["Tel"]);
        unset($_SESSION["Dir"]);
        unset($_SESSION["CURP"]);
    //Redireccionamos a la ventana de registro
        header('Location: https://kasu.com.mx/login/registro.php');
?>