<?php
/**
 * @package dompdf
 * @link    http://dompdf.github.com/
 * @author  Benj Carson <benjcarson@digitaljunkies.ca>
 * @author  Helmut Tischer <htischer@weihenstephan.org>
 * @author  Fabien Ménager <fabien.menager@gmail.com>
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

//error_reporting(E_STRICT | E_ALL | E_DEPRECATED);
//ini_set("display_errors", 1);

PHP_VERSION >= 5.0 or die("DOMPDF requires PHP 5.0+");

/**
 * La raíz de su instalación de DOMPDF
 */
define("DOMPDF_DIR", str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__))));

/**
 * La ubicación del directorio de inclusión de DOMPDF
 */
define("DOMPDF_INC_DIR", DOMPDF_DIR . "/include");

/**
 * La ubicación del directorio lib DOMPDF
 */
define("DOMPDF_LIB_DIR", DOMPDF_DIR . "/lib");

/**
 * Algunas instalaciones no tienen $_SERVER['DOCUMENT_ROOT']
 * http://fyneworks.blogspot.com/2007/08/php-documentroot-in-iis-windows-servers.html
 */
if( !isset($_SERVER['DOCUMENT_ROOT']) ) {
  $path = "";

  if ( isset($_SERVER['SCRIPT_FILENAME']) )
    $path = $_SERVER['SCRIPT_FILENAME'];
  elseif ( isset($_SERVER['PATH_TRANSLATED']) )
    $path = str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']);

  $_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($path, 0, 0-strlen($_SERVER['PHP_SELF'])));
}

/** Incluya el archivo de configuración personalizado si existe */
if ( file_exists(DOMPDF_DIR . "/dompdf_config.custom.inc.php") ){
  require_once(DOMPDF_DIR . "/dompdf_config.custom.inc.php");
}

//FIXME: Algunas definiciones de funciones se basan en las constantes definidas por DOMPDF. Sin embargo, ¿esta ubicación podría resultar problemática?
require_once(DOMPDF_INC_DIR . "/functions.inc.php");

/**
 * Nombre de usuario y contraseña utilizados por la utilidad de configuración en www/
 */
def("DOMPDF_ADMIN_USERNAME", "user");
def("DOMPDF_ADMIN_PASSWORD", "password");

/**
 * La ubicación del directorio de fuentes DOMPDF
 *
 * La ubicación del directorio donde DOMPDF almacenará las fuentes y las métricas de fuentes
 * Nota: Este directorio debe existir y ser escribible por el proceso del servidor web.
 * *Tenga en cuenta la barra diagonal final.*
 *
 * Notas sobre fuentes:
 * Se pueden agregar métricas de fuentes .afm adicionales ejecutando load_font.php desde la línea de comandos.
 *
 * Solo las "fuentes Base 14" originales están presentes en todos los visores de PDF. Las fuentes adicionales deben
 * estar incrustado en el archivo pdf o es posible que el PDF no se muestre correctamente. Esto puede significativamente
 * aumentar el tamaño del archivo a menos que esté habilitado el subconjunto de fuentes. Antes de incrustar una fuente, por favor
 * Revisa tus derechos bajo la licencia de la fuente.
 *
 * Cualquier especificación de fuente en el HTML fuente se traduce a la fuente más cercana disponible
 * en el directorio de fuentes.
 *
 * Las "fuentes Base 14" estándar de pdf son:
 * Courier, Courier-Bold, Courier-BoldOblique, Courier-Oblique,
 * Helvetica, Helvetica-Bold, Helvetica-BoldOblique, Helvetica-Oblique,
 * Times-Roman, Times-Bold, Times-BoldItalic, Times-Italic,
 * Icono, ZapfDingbats.
 */
def("DOMPDF_FONT_DIR", DOMPDF_DIR . "/lib/fonts/");

/**
* La ubicación del directorio de caché de fuentes DOMPDF
*
* Este directorio contiene las métricas de fuentes almacenadas en caché para las fuentes utilizadas por DOMPDF.
* Este directorio puede ser el mismo que DOMPDF_FONT_DIR
*
* Nota: Este directorio debe existir y ser escribible por el proceso del servidor web.
 */
def("DOMPDF_FONT_CACHE", DOMPDF_FONT_DIR);

/**
* La ubicación de un directorio temporal.
*
* El directorio especificado debe ser escribible por el proceso del servidor web.
* Se requiere el directorio temporal para descargar imágenes remotas y cuando
* usando el back-end de PFDLib.
 */
def("DOMPDF_TEMP_DIR", sys_get_temp_dir());

/**
* ==== IMPORTANTE ====
*
* "chroot" de dompdf: evita que dompdf acceda a los archivos del sistema u otros
* archivos en el servidor web. Todos los archivos locales abiertos por dompdf deben estar en una
* subdirectorio de este directorio. NO lo establezca en '/' ya que esto podría
* permitir que un atacante use dompdf para leer cualquier archivo en el servidor. Este
* debe ser una ruta absoluta.
* Esto solo se verifica en la llamada de línea de comando por dompdf.php, pero no por
* uso directo de la clase como:
 * $dompdf = new DOMPDF();	$dompdf->load_html($htmldata); $dompdf->render(); $pdfdata = $dompdf->output();
 */
def("DOMPDF_CHROOT", realpath(DOMPDF_DIR));

/**
* Ya sea para usar fuentes Unicode o no.
*
* Cuando se establece en verdadero, el backend de PDF debe establecerse en "CPDF" y las fuentes deben ser
* cargado a través de load_font.php.
*
* Cuando está habilitado, dompdf puede admitir todos los glifos Unicode. Cualquier glifo usado en un
* Sin embargo, el documento debe estar presente en sus fuentes.
 */
def("DOMPDF_UNICODE_ENABLED", true);

/**
 * Ya sea para hacer subconjuntos de fuentes o no.
 */
def("DOMPDF_ENABLE_FONTSUBSETTING", false);

/**
* El backend de renderizado de PDF para usar
 *
 * Las configuraciones válidas son 'PDFLib', 'CPDF' (la clase PDF de R&OS incluida), 'GD' y
 * 'automóvil'. 'auto' buscará PDFLib y lo usará si lo encuentra, o si no lo hará
 * recurrir a CPDF. 'GD' convierte archivos PDF en archivos gráficos. {@enlace
 * Canvas_Factory} finalmente determina qué clase de representación instanciar
 * basado en esta configuración.
 *
 * Los backends de renderizado de PDFLib y CPDF proporcionan suficiente renderizado
 * capacidades para dompdf, sin embargo, características adicionales (por ejemplo, objeto,
 * compatibilidad con imágenes y fuentes, etc.) difieren entre backends. Por favor mira
 * {@link PDFLib_Adapter} para obtener más información sobre el backend de PDFLib
 * y {@link CPDF_Adapter} y lib/class.pdf.php para obtener más información
 * en CPDF. Consulte también la documentación de cada backend en los enlaces
 * abajo.
 *
 * El backend de renderizado GD es un poco diferente a PDFLib y
 * CPDF. Varias características de CPDF y PDFLib no son compatibles o no
 * no tiene ningún sentido al crear archivos de imagen. Por ejemplo,
 * no se admiten varias páginas, ni tampoco los 'objetos' de PDF. tener un
 * consulte {@link GD_Adapter} para obtener más información. El soporte de GD es nuevo
 * y experimental, así que úsalo bajo tu propio riesgo.
 *
 * @link http://www.pdflib.com
 * @link http://www.ros.co.nz/pdf
 * @link http://www.php.net/image
 */
def("DOMPDF_PDF_BACKEND", "CPDF");

/**
* Clave de licencia de PDFlib
 *
 * Si utiliza una versión comercial con licencia de PDFlib, especifique
 * su clave de licencia aquí. Si está utilizando PDFlib-Lite o está evaluando
 * la versión comercial de PDFlib, comente esta configuración.
 *
 * @link http://www.pdflib.com
 *
 * Si pdflib está presente en el servidor web y se selecciona automáticamente o explícitamente arriba,
 * ¡debe existir un código de licencia real!
 */
//def("DOMPDF_PDFLIB_LICENSE", "su clave de licencia aquí");

/**
 * vista de medios de destino html que debe convertirse en pdf.
 * Lista de tipos y reglas de análisis para futuras extensiones:
 * http://www.w3.org/TR/REC-html40/types.html
 * pantalla, tty, tv, proyección, portátil, impresión, braille, aural, todo
 * Nota: Aural está obsoleto en CSS 2.1 porque se reemplaza por voz en CSS 3.
 * Tenga en cuenta que, aunque el archivo pdf generado está destinado a la salida impresa,
 * el contenido deseado puede ser diferente (por ejemplo, pantalla o vista de proyección del archivo html).
 * Por lo tanto, permita la especificación del contenido aquí.
 */
def("DOMPDF_DEFAULT_MEDIA_TYPE", "screen");

  /**
  * El tamaño de papel predeterminado.
  *
  * El estándar de América del Norte es "letra"; otros países generalmente "a4"
  *
  * @see CPDF_Adapter::PAPER_SIZES para tamaños válidos
  */
def("DOMPDF_DEFAULT_PAPER_SIZE", "letter");

/**
* La familia de fuentes predeterminada
*
* Se utiliza si no se pueden encontrar fuentes adecuadas. Esto debe existir en la carpeta de fuentes.
 * @var string
 */
def("DOMPDF_DEFAULT_FONT", "serif");

/**
* Configuración de imagen DPI
*
* Esta configuración determina la configuración de DPI predeterminada para imágenes y fuentes. el
* El DPI se puede anular para las imágenes en línea configurando explícitamente el
* los atributos de estilo de ancho y alto de la imagen (es decir, si la imagen es nativa
* el ancho es de 600 píxeles y usted especifica el ancho de la imagen como 72 puntos,
* la imagen tendrá un DPI de 600 en el PDF renderizado. El DPI de
* las imágenes de fondo no se pueden anular y se controlan por completo
* a través de este parámetro.
*
* A los efectos de DOMPDF, píxeles por pulgada (PPI) = puntos por pulgada (DPI).
* Si un tamaño en html se da como px (o sin unidad como tamaño de imagen),
* esto dice el tamaño correspondiente en pt.
* Esto ajusta los tamaños relativos para que sean similares a la representación del
* página html en un navegador de referencia.
*
* En pdf, siempre 1 pt = 1/72 pulgada
*
* Resolución de representación de varios navegadores en px por pulgada:
*Windows Firefox e Internet Explorer:
* SystemControl->Propiedades de visualización->FontResolution: Predeterminado: 96, fuentes grandes: 120, personalizado:?
* Linux Firefox:
* about:config *resolución: Predeterminado:96
* (la dimensión de la pantalla xorg en mm y la configuración de dpi de la fuente de escritorio se ignoran)
*
* Tenga cuidado con el factor de zoom de fuente/imagen adicional del navegador.
*
* En las imágenes, el tamaño <img> en el atributo de píxel, el estilo img css, se anulan
* la dimensión de la imagen real en px para renderizar.
 *
 * @var int
 */
def("DOMPDF_DPI", 96);

/**
* Habilitar PHP en línea
*
* Si esta configuración se establece en verdadero, DOMPDF evaluará automáticamente
* PHP en línea contenido dentro de las etiquetas <script type="text/php"> ... </script>.
*
* Habilitar esto para documentos en los que no confía (por ejemplo, html remoto arbitrario
* páginas) es un riesgo de seguridad. Establezca esta opción en falso si desea procesar
* Documentos no confiables.
 *
 * @var bool
 */
def("DOMPDF_ENABLE_PHP", false);

/**
* Habilitar javascript en línea
*
* Si esta configuración se establece en verdadero, DOMPDF insertará automáticamente
* Código JavaScript contenido dentro de las etiquetas <script type="text/javascript"> ... </script>.
 *
 * @var bool
 */
def("DOMPDF_ENABLE_JAVASCRIPT", true);

/**
* Habilitar el acceso remoto a archivos
*
* Si esta configuración se establece en verdadero, DOMPDF accederá a sitios remotos para
* imágenes y archivos CSS según sea necesario.
* Esto es necesario para parte del caso de prueba www/test/image_variants.html hasta www/examples.php
*
* ¡Atención!
* Esto puede ser un riesgo de seguridad, en particular en combinación con DOMPDF_ENABLE_PHP y
* permitir el acceso remoto a dompdf.php o permitir que se pase el código html remoto a
* $dompdf = nuevo DOMPDF(); $dompdf->load_html(...);
* Esto permite a los usuarios anónimos descargar contenido de Internet legalmente dudoso que en
* el rastreo parece ser descargado por su servidor, o permite código php malicioso
* en páginas html remotas para ser ejecutadas por su servidor con los privilegios de su cuenta.
 *
 * @var bool
 */
def("DOMPDF_ENABLE_REMOTE", true);

/**
* El registro de salida de depuración
 * @var string
 */
def("DOMPDF_LOG_OUTPUT_FILE", DOMPDF_FONT_DIR."log.html"); /*JCCM ( htm -> html)*/

/**
 * Una proporción aplicada a la altura de las fuentes para parecerse más a la altura de línea de los navegadores
 */
def("DOMPDF_FONT_HEIGHT_RATIO", 1.1);

/**
* Habilitar CSS flotante
 *
 * Permite a las personas deshabilitar el soporte flotante de CSS
 * @var bool
 */
def("DOMPDF_ENABLE_CSS_FLOAT", false);

/**
 * Habilite el cargador automático DOMPDF incorporado
 *
 * @var bool
 */
def("DOMPDF_ENABLE_AUTOLOAD", false/*true*/);

/**
 * Anteponga la función de carga automática DOMPDF a la pila spl_autoload
 *
 * @var bool
 */
def("DOMPDF_AUTOLOAD_PREPEND", true);

/**
 * Use el analizador HTML5 Lib más que experimental
 */
def("DOMPDF_ENABLE_HTML5PARSER", false);
require_once(DOMPDF_LIB_DIR . "/html5lib/Parser.php");

// ### Fin de las opciones configurables por el usuario ###

/**
 * Load autoloader
 */
if (DOMPDF_ENABLE_AUTOLOAD) {
  require_once(DOMPDF_INC_DIR . "/autoload.inc.php");
  // require_once(DOMPDF_LIB_DIR . "/php-font-lib/classes/font.cls.php");
}

/**
 * Asegúrese de que PHP esté trabajando con texto internamente utilizando la codificación de caracteres UTF8.
 */
mb_internal_encoding('UTF-8');

/**
* Matriz global de advertencias generadas por el analizador DomDocument y
 * clase de hoja de estilo
 *
 * @var array
 */
global $_dompdf_warnings;
$_dompdf_warnings = array();

/**
 * Si es verdadero, $_dompdf_warnings se descarga al terminar el script cuando se usa
 * dompdf/dompdf.php o después de renderizar al usar la clase DOMPDF.
 * Al usar la clase, establecer este valor en verdadero le impedirá
 * transmisión del PDF.
 *
 * @var bool
 */
global $_dompdf_show_warnings;
$_dompdf_show_warnings = false;

/**
* Si es verdadero, todo el árbol se vuelca en la salida estándar en dompdf.cls.php.
 * Establecer este valor en verdadero le impedirá transmitir el PDF.
 *
 * @var bool
 */
global $_dompdf_debug;
$_dompdf_debug = false;

/**
 * Matriz de tipos de mensajes de depuración habilitados
 *
 * @var array
 */
global $_DOMPDF_DEBUG_TYPES;
$_DOMPDF_DEBUG_TYPES = array(); //array("page-break" => 1);

/* Opcionalmente habilite diferentes clases de salida de depuración antes del contenido pdf.
 * Visible si muestra pdf como texto,
 * P.ej. en la visualización repetida del mismo pdf en el navegador cuando el pdf no se saca de
 * la memoria caché del navegador y la salida prematura impiden la configuración del tipo MIME.
 */
def('DEBUGPNG', false); //false
def('DEBUGKEEPTEMP', false); //false
def('DEBUGCSS', false); //false

/* Depuración de diseño. Mostrará rectángulos alrededor de diferentes niveles de bloque.
 * Visible en el propio PDF.
 */
def('DEBUG_LAYOUT', false); //false
def('DEBUG_LAYOUT_LINES', true);
def('DEBUG_LAYOUT_BLOCKS', true);
def('DEBUG_LAYOUT_INLINE', true);
def('DEBUG_LAYOUT_PADDINGBOX', true);
