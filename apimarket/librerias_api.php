<?PHP
//incluir la conexion a la base de datos
    require __DIR__ . '/../eia/Conexiones/cn_vtas.php';
    //require __DIR__ . '../eia/Conexiones/cn_pruebas.php';    
//inlcuir los archivos de funciones
    require_once('/home/u557645733/domains/kasu.com.mx/public_html/eia/Funciones/Funciones_Basicas.php');
    require_once('/home/u557645733/domains/kasu.com.mx/public_html/eia/Funciones/Funciones_Correo.php');
    require_once('/home/u557645733/domains/kasu.com.mx/public_html/eia/Funciones/Funciones_Financieras.php');
    require_once('/home/u557645733/domains/kasu.com.mx/public_html/eia/Funciones/Funciones_Seguridad.php');
//datos locales
    date_default_timezone_set('America/Mexico_City');
    setlocale(LC_MONETARY, 'es_MX');
    setlocale(LC_TIME, 'es_MX');
    setlocale(LC_ALL,'es_MX');
    $dias = array("Domingo","Lunes", "Mártes", "Miercoles", "Jueves", "Viernes", "Sabado");
    $meses =array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
//Fecha y hora para registros
    $hoy = date('Y-m-d');
    $HoraActual = date('H:i:s');
//creamos una variable general para las funciones
    $basicas = new Basicas();
    $seguridad = new Seguridad();
    $financieras = new Financieras();
    global $mysqli;
// DEBUG: Activar todos los errores y mostrar datos importantes (eliminar en producción)
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
