<?PHP
//inlcuir los archivos de funciones
    require_once 'Funciones/Funciones_Basicas.php';
    require_once 'Funciones/Funciones_Correo.php';
    require_once 'Funciones/Funciones_Financieras.php';
    require_once 'Funciones/Funciones_Seguridad.php';
//incluir la conexion a la base de datos
    require_once 'Conexiones/cn_blog.php';
    require_once 'Conexiones/cn_prosp.php';
    require_once 'Conexiones/cn_vtas.php';      //Conexion para Real a la base de datos
    //require_once 'Conexiones/cn_prueba.php';  //Conexion para hacer pruebas la base es una copia exacta de la Real
//Se establecen las variables principales como numero de contacto
    require_once 'php/Telcto.php';
//datos locales
    date_default_timezone_set('America/Mexico_City');
    setlocale(LC_MONETARY, 'es_MX');
    setlocale(LC_TIME, 'es_MX');
    setlocale(LC_ALL,'es_MX');
    $dias = array("Domingo","Lunes", "Mártes", "Miercoles", "Jueves", "Viernes", "Sabado");
    $meses =array("","Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

?>
