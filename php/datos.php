<?php
/**
 * Generación de póliza PDF desde HTML con datos de Usuario/Contacto.
 * Fecha: 2025-11-03
 * Revisado por: JCCM
 *
 * Notas PHP 8.2:
 * - Sanitizamos entrada $_GET['id'] y validamos existencia de $mysqli.
 * - Evitamos avisos por variables no definidas. Valores por defecto seguros.
 * - Compatibilidad DOMPDF: soporta proyectos con dompdf_config.inc.php (clase DOMPDF)
 *   y también instalación moderna vía autoload (clase \Dompdf\Dompdf).
 */

ob_start();

// === Carga DOMPDF con compatibilidad vieja/nueva ===
$__dompdf_loaded = false;
if (is_file(__DIR__ . '/../formato/dompdfMaster/dompdf_config.inc.php')) {
    require __DIR__ . '/../formato/dompdfMaster/dompdf_config.inc.php'; // legado
    $__dompdf_loaded = class_exists('DOMPDF', false);
}
if (!$__dompdf_loaded) {
    // intento moderno
    $autoload1 = __DIR__ . '/../formato/vendor/autoload.php';
    $autoload2 = __DIR__ . '/../login/Generar_PDF/dompdfMaster/vendor/autoload.php';
    if (is_file($autoload1)) {
        require $autoload1;
    } elseif (is_file($autoload2)) {
        require $autoload2;
    }
}

date_default_timezone_set('America/Mexico_City');
// datos de la fecha en php
$fecha = date("Y-m-d-H-i-s");

require_once __DIR__ . '/../eia/php/Funciones_kasu.php';

// Validación conexión
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    exit('Error: conexión a base de datos no disponible.');
}

// Sanitizar id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Inicializar variables para evitar avisos
$name = '';
$curp = '';
$cont = 0;
$email = '';
$phone = '';

// Consultas
if ($id > 0) {
    // Usuario
    $sql = "SELECT Nombre, ClaveCurp, IdContact FROM Usuario WHERE IdContact = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param('i', $id);
        if ($stmt->execute() && ($res = $stmt->get_result())) {
            if ($row = $res->fetch_assoc()) {
                $name = isset($row['Nombre']) ? (string)$row['Nombre'] : '';
                $curp = isset($row['ClaveCurp']) ? (string)$row['ClaveCurp'] : '';
                $cont = isset($row['IdContact']) ? (int)$row['IdContact'] : 0;
            }
            $res->free();
        }
        $stmt->close();
    }

    // Contacto
    if ($cont > 0) {
        $sql = "SELECT Mail, Telefono FROM Contacto WHERE id = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            $stmt->bind_param('i', $cont);
            if ($stmt->execute() && ($res = $stmt->get_result())) {
                if ($row = $res->fetch_assoc()) {
                    $email = isset($row['Mail']) ? (string)$row['Mail'] : '';
                    $phone = isset($row['Telefono']) ? (string)$row['Telefono'] : '';
                }
                $res->free();
            }
            $stmt->close();
        }
    }
}

// Helpers de salida segura
function e_txt(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang='es'>
<head>
 <meta charset="utf-8">
 <title>POLIZA KASU</title>
 <link rel="stylesheet" href="../assets/css/poliza.css">
</head>
<body>
    <div class="container">
        <table class="t-h">
            <tr>
                <td><h1 class="ha-text"><strong> SERVICIO A FUTURO / SOLICITUD DE APORTACI&Oacute;N </strong></h1></td>
            </tr>
            <tr>
                <td><h1 class="hb-text"> PARTES DE CONTRATO</h1></td>
            </tr>
        </table>
        <img src="../assets/poliza/img/transp.png" class="header">
        <div class="w-tab t-one">
            <img src="../assets/poliza/img/1.jpg" class="h-lo">
            <table class="date">
                <tr>
                    <td>NOMBRE:</td>
                    <td></td>
                    <td>CAPITAL & FONDEO MEXICO S.A. de C.V. SOFOM ENR</td>
                </tr>
                <tr>
                    <td>DOMICILIO:</td>
                    <td></td>
                    <td>Avenida Presiente ;asarik,No. 61,Int 901-9, Colonia Polanco V secci&oacute;n</td>
                </tr>
                <tr>
                    <td>ACTA CONSTITUTIVA:</td>
                    <td></td>
                    <td>30,515 volumen ordinario DXXV(QUINIENTOS TREINTA Y CINCO)</td>
                </tr>
                <tr>
                    <td>PODER:</td>
                    <td></td>
                    <td>30,515 volumen ordinario DXXV(QUINIENTOS TREINTA Y CINCO)</td>
                </tr>
            </table>
        </div>
        <img src="../assets/poliza/img/LINE7.png" class="h-line">
        <div class="w-tab">
            <img src="../assets/poliza/img/2.png" class="h-lt">
            <table class="date">
                <tr>
                    <td>NOMBRE:</td>
                    <td>GRUPO C&M ATLACOMULCO S.A. DE C.V.</td>
                </tr>
                <tr>
                    <td>DOMICILIO:</td>
                    <td>Privada Vire, No.2 Int.10 col.Centro,Atlacomulco esatdo de M&eacute;xico</td>
                </tr>
                <tr>
                    <td>ACTA CONSTITUTIVA:</td>
                    <td>30,569 volumen ordinario DXXIX(QUINIENTOS TREINTA Y NUEVE)</td>
                </tr>
                <tr>
                    <td>PODER:</td>
                    <td>30,569 volumen ordinario DXXIX(QUINIENTOS TREINTA Y NUEVE)</td>
                </tr>
            </table>
        </div>
        <img src="../assets/poliza/img/LINE7.png" class="h-line">
        <img src="../assets/poliza/img/3.png" class="h-ltr">
        <table class="ba date">
            <tr>
                <td class="mb" colspan="3">CURP Solicitante:</td>
                <td class="mb ct"> <?php echo e_txt($curp); ?></td>
            </tr>
            <tr>
                <td class="bl">Tel&eacute;fono:</td>
                <td class="bl ct"><?php echo e_txt($phone); ?></td>
                <td class="bl">e-mail</td>
                <td class="bl ct"><?php echo e_txt($email); ?></td>
            </tr>
        </table>
        <img src="../assets/poliza/img/LINE7.png" class="h-line">
        <img src="../assets/poliza/img/4.png" class="h-lf">
        <table class="ser">
            <tr>
                <td colspan="6"></td>
                <td class="mw">Servico Funerario</td>
                <td class="mw">Contado</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td class="mw">0 a 29 a&ntilde;os</td>
                <td class="mda">$1,680.00 MXN</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td class="mw">30 a 49 a&ntilde;os</td>
                <td class="md">$2,090.00 MXN</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5" class="mw">Servicio Universitario</td>
                <td></td>
                <td class="mw">50 a 54 a&ntilde;os</td>
                <td class="mda">$2,836.00 MXN</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="2" class="mw">Contado</td>
                <td colspan="2" class="mda">$82,920.00 MXN</td>
                <td colspan="2"></td>
                <td class="mw">55 a 59 a&ntilde;os</td>
                <td class="md">$3,693.00 MXN</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td class="mw">60 a 64 a&ntilde;os</td>
                <td class="mda">$4,791.00 MXN</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6"></td>
                <td class="mw">65 a 69 a&ntilde;os</td>
                <td class="md">$6,230.00 MXN</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="5"></td>
                <td colspan="2" class="mw">TRADICIONAL</td>
                <td colspan="2" class="mw">CREMACI&Oacute;N</td>
                <td colspan="2" class="mw">ECOL&Oacute;GICO</td>
            </tr>
        </table>
        <img src="../assets/poliza/img/LINE7.png" class="h-line">
        <img src="../assets/poliza/img/5.png" class="h-lfi">
        <table class="ba date">
            <tr>
                <td>
                    <p class="ob-texto">
                        El presente se sujeta a las disposiciones estipuladas en el contrato de fideicomiso o irrevocable de garant&iacute;a no.f/0003.<br>
                        Capital & fondeo de M&eacute;xico, puede reservarse el derecho de proveer el servicio cuando el aportante incumpla total o parcialmente en alguna de sus obligaciones con la misma.<br>
                        La empresa se reserva el servicio si se incurre en omisiones sobre la informaci&oacute;n asentada en la presente;<br>
                        Sobre siniestros acaecidos como consecuci&oacute;n de conflictos armados o por "cat&aacute;strofe o calamidad nacional", Siniestros de caracter catastr&oacute;fico acaecidos como consecuencia de <br>
                        reacci&oacute;n o radiaci&oacute;n nuclear o contaminaci&oacute;n radiactiva.<br><br>
                        LA PRESENTE TIENE UN RANGO DE EDAD PARA LOS BENEFICIOS DE 2 A 65 A&Ntilde;OS en caso del servicio funerario, el SERVICIO UNIVERSITARIO podra ser utilizado 10 a&ntilde;os <br>
                        despu&eacute;s de su contrataci&oacute;n la empresa se reserva el derecho de prestar el servici&oacute; cuando no cumplan con este criterio.<br>
                        <br>
                        La cantidad abonada al F/003, nunca exceder&aacute; los montos fijados en el fideicomiso F/003, el restante se considera pago de comisiones, cargos y gastos que ser&aacute;n pagados por el cliente.
                    </p>
                </td>
            </tr>
        </table>
        <img src="../assets/poliza/img/LINE7.png" class="h-line">
        <h2 class="url">CONSULTA NUESTRO AVISO DE PRIVACIDAD EN :WWW.KASU.COM.MX/AVISOPRIVACIDAD.HTML</h2>
        <br>
        <img src="../assets/poliza/img/6.png" class="h-ls">
        <table class="bt tab date">
            <tr>
                <td class="fi-tx" ><strong>CAPITAL & FONDEO MEXICO S.A DE C.V SOFOM  <br> ENTIDAD NO REGULADA </strong></td>
                <td class="bt-l con md fi-tx" rowspan="3">EL SOLICITANTE</td>
            </tr>
            <tr>
                <td class="sp-w"> s  </td>
            </tr>
            <tr>
                <td class="sp-w">  s </td>
            </tr>
        </table>
        <img src="../assets/poliza/img/firma.png" class="img-f">
        <br>
        <p class="duda">En caso de emergencia comunicarse desde cualquier <br> parte de la Rep&uacute;blica: Tel.712 141 62  69 <br>Para mas infromaci&oacute;n ingrese a nuestra  p&aacute;gina web: </p>
        <img src="../assets/poliza/img/img.png" class="fin">
    </div>
    <br><br><br><br><br>
    <div class="container">
        <br><br>
        <h1 class="hd-tit">CONTRATO DE PRESTACI&Oacute;N DE SERVICIOS</h1>
        <h2 class="hd-sub">A N T E C E D E N T E S</h2>
        <br>
        <p class="hd-text">Mediante el contrato de <strong>Fideicomiso</strong> protocolizado en fecha <strong>20 de mayo del 2016</strong> denominado a partir de este y para el presente como <strong>&#8220;Fideicomiso F/0003&#8221;</strong>, se nombr&oacute; a <strong>&#8220;GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.&#8221;</strong> como
                                                            fideicomitente y fideicomisario en tercer lugar, y a <strong>CAPITAL & FONDEO M&Eacute;XICO S.A. DE C.V. SOFOM ENR</strong> Fiduciaria y Fideicomisaria en primer lugar y que mediante un contrato de cesi&oacute;n de aportaciones, firmado por <strong>GRUPO C&M
                                                            ATLACOMULCO S.A.P.I. DE C.V.</strong> y <strong>CAPITAL & FONDEO M&Eacute;XICO S.A. DE C.V. SOFOM ENR</strong> se protocolizan las solicitudes de acceso al <strong>Fideicomiso F/0003</strong> y con la aceptaci&oacute;n de los mismos se les nombra a toda persona descrita en el
                                                            contrato de aportaci&oacute;n como Fideicomisario en Segundo Lugar. Por lo que al momento de firmarse el contrato, <strong>&#8220;EL CLIENTE&#8221;</strong> ser&aacute; acreedor a los beneficios que el fideicomiso se&ntilde;ala tomando como consideraci&oacute;n los servicios especificados
                                                            en la solicitud de <strong>SERVICIO A FUTURO</strong> de cada <strong>CLIENTE</strong>, mismas que se deben anexar al contrato de sesi&oacute;n.</p>
        <p class="hd-text">Las aportaciones de cada <strong>CLIENTE</strong> al mencionado <strong>Fideicomiso F/0003</strong> son documentados por el <strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong> mediante un recibo impreso o digital donde se especificara lo siguiente;</p>
        <div class="hd-vs">
            <ul><li class="hd-v">Contrato de Cesi&oacute;n al cual pertenece <strong>EL CLIENTE</strong>.</li></ul>
            <ul><li class="hd-v">Nombre de <strong>EL CLIENTE</strong>.</li></ul>
            <ul><li class="hd-v">Clave &Uacute;nica de Registro de Poblaci&oacute;n de <strong>EL CLIENTE</strong>.</li></ul>
            <ul><li class="hd-v">Contrato de servicio a futuro de <strong>EL CLIENTE</strong>.</li></ul>
            <ul><li class="hd-v">Recibo de Deposito de valor unitario de Servicio a futuro de <strong>EL CLIENTE</strong>.</li></ul>
        </div>
        <p class="hd-text">Que en el Contrato <strong>Fideicomiso F/0003</strong> se especifica que <strong>GRUPO C&M ATLACOMULCO S.A. DE C.V.</strong> ser&aacute; el &uacute;nico distribuidor y comercializado con autorizaciones para realizar los contratos de aportaci&oacute;n descritos en las clausulas
                                                                anteriores.</p>
        <br>
        <h4 class="hd-t">D E C L A R A C I O N E S</h4>
        <br>
        <p class="hd-text">Declara la sociedad denominada <strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong> a trav&eacute;s de su representante;</p>
        <p class="hd-text"><strong>A)</strong> Que es una sociedad constituida al amparo de las leyes mexicanas el d&iacute;a <strong>14 DEL MES DE JULIO DEL 2015</strong>, otorgada ante la fe de la Licenciada en Derecho NORMA V&Eacute;LEZ BAUTISTA, titular de la Notaria Publica No. 83 del Estado de
                                                            M&eacute;xico, con residencia en Atlacomulco, mediante la escritura p&uacute;blica <strong>NO. 30,569 (TREINTA MIL QUINIENTOS SESENTA Y NUEVE)</strong>.
                                                    <strong>B)</strong> Que su Registro Federal de Contribuyente es <strong>GCA150714E32</strong>.
                                                    <strong>C)</strong> Su <strong>Representante</strong> cuenta con las facultades suficientes para celebrar el presente contrato en su nombre y representaci&oacute;n as&iacute; como obligarla en los t&eacute;rminos y condiciones del presente con sus anexos y referencias a otros instrumentos,
                                                    seg&uacute;n consta en la &#8220;<strong>ESCRITURA P&Uacute;BLICA NO. 30,569 (TREINTA MIL QUINIENTOS SESENTA Y NUEVE), DEL D&Iacute;A 14 D&Iacute;AS DEL MES DE JULIO DEL 2015</strong>, otorgada ante la fe de la licenciada en derecho NORMA V&Eacute;LES BAUTISTA,
                                                    titular de la Notaria Publica No. 83 del Estado de M&eacute;xico, con residencia en Atlacomulco&#8221;.
                                                    <strong>D)</strong> Que en este acto se constituye como &#8220;<strong>PRESTADOR DE SERVICIOS</strong>&#8221;.
                                                    <strong>E)</strong> No ha iniciado ni se tiene conocimiento de que se haya iniciado procedimiento alguno tendiente a declararlo en concurso mercantil, en estado de insolvencia o liquidaci&oacute;n respectivamente.
                                                    <strong>F)</strong> No tiene ning&uacute;n conocimiento de que se haya iniciado acci&oacute;n o procedimiento alguno ante cualquier &oacute;rgano jurisdiccional que I) Afecte o pudiera afectar materialmente la legalidad, validez o exigibilidad del presente contrato o de los dem&aacute;s
                                                    documentos de la operaci&oacute;n, o de cualesquiera de sus obligaciones derivadas o relacionadas con el presente contrato o con los dem&aacute;s documentos de la operaci&oacute;n de los que es parte, II) Pudiera anular o impedir la transmisi&oacute;n de los
                                                    derechos de cobro cedidos al patrimonio del fideicomiso conforme al presente contrato y al contrato de sesi&oacute;n original o subsecuentes, III) Pudiera impugnar o impedir la emisi&oacute;n o cualquier reapertura subsecuente.
                                                    <strong>G)</strong> Conduce su negocio y operaciones de acuerdo a las leyes aplicables correspondientes, cuenta con todos los permisos necesarios para llevar a cabo las operaciones a que haya lugar, as&iacute; como estar dentro de los reglamentos, leyes,
                                                    decretos y &oacute;rdenes de cualquier autoridad gubernamental que le sean aplicables al bien y a sus propiedades.
                                                    <strong>H)</strong> Reconoce y acepta que I) La veracidad y exactitud de sus declaraciones contenidas en el presente contrato, II) La valides y exigibilidad del presente contrato y as&iacute; como los dem&aacute;s documentos de la operaci&oacute;n de los que es parte, III) La
                                                    validez y exigibilidad de la transmisi&oacute;n de la propiedad y titularidad de los derechos de cobro cedidos a favor del fiduciario, motivo determinante de la voluntad del fiduciario para llevar a cabo el presente, IV) Que es propietario de los derechos
                                                    de cobro materia del presente.
                                                    <strong>I)</strong> A la fecha del presente contrato no existe huelga, paro, suspensi&oacute;n o reducci&oacute;n de labores, procedimientos colectivos de trabajo u otro procedimiento laboral similar en curso, que afecte o pudiera llegar a afectar materialmente cualquiera de
                                                    sus activos e instalaciones correspondientes.
                                                    Declara &#8220;<strong>EL CLIENTE</strong>&#8221; por propia cuenta;<br>
                                                    <strong>A)</strong> Que es de su inter&eacute;s firmar la presente solicitud de ingreso al <strong>Fideicomiso F/0003</strong> firmado entre <strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V. Y CAPITAL & FONDEO M&Eacute;XICO S.A. DE C.V. SOFOM ENR</strong> <br>
                                                    <strong>B)</strong> Que declara que conoce los alcances que el <strong>Fideicomiso F/0003</strong> as&iacute; como las responsabilidades que el mismo le confiere, y que es su deseo ser parte del mismo. <br>
                                                    <strong>C)</strong> Que declara que sus datos personales los otorgo a <strong>GRUPO C&M ATLACOMULCO S.A. DE C.V.</strong> para ser registrados de forma digital en sus bases de datos internos ubicados en <a class="hd-url" href="https://kasu.com.mx/">www.kasu.com.mx</a><br>
                                                    <strong>D)</strong> Que habiendo le&iacute;do el aviso de privacidad y la hoja de uso de datos autoriza que <strong>GRUPO C&M ATLACOMULCO S.A. DE C.V.</strong> utilice sus datos como mejor considere, as&iacute; como a sus asociados con la finalidad de ofrecer un mejor servicio a
                                                        los actuales y futuros <strong>CLIENTES</strong>.
                                                    <strong>E)</strong> Que en el presente acto se constituye con el nombre de &#8220;<strong>EL CLIENTE</strong>&#8221;.</p>
        <br>
        <h4 class="hd-t">C L &Aacute; U S U L A S</h4>
        <br>
        <p class="hd-text"><strong>PRIMERA. OBJETO:</strong> EL presente contrato especifica los medios mediante los cuales &#8220;<strong>EL CLIENTE</strong>&#8221; puede acceder a los beneficios y servicios amparados con el patrimonio del <strong>fideicomiso F/0003</strong> descrito en la <strong>CL&Aacute;USULA &#8220;A&#8221;</strong> de los
                                                            <strong>ANTECEDENTES</strong> del presente Contrato los cuales son; Realizar ya sea por cuenta propia, a trav&eacute;s de alguna de sus empresas controladas o mediante alg&uacute;n tercero subcontratado uno o varios de los servicios descritos en la CLAUSULA
                                                            <strong>SEGUNDA</strong> del presente seg&uacute;n lo establezca el registro de &#8220;<strong>EL CLIENTE</strong>&#8221; o <strong>BENEFICIARIO</strong> asentado en las bases de datos ubicadas en www.kasu.com.mx .
                                                            <strong>SEGUNDA. SERVICIOS:</strong> La prestaci&oacute;n de los servicios est&aacute; sujeta a las siguientes condicionantes y medios de ejecuci&oacute;n para poder hacerse valido;
                                                            <strong>SERVICIO FUNERARIO;</strong> el servicio incluye los siguiente anexos; <strong>A) Servicio de traslado</strong>; el servicio el servicio se prestara en un radio que no exceda los 60 (sesenta) kil&oacute;metros de distancia, el servicio de traslado constara de los traslados
                                                            necesarios del cuerpo entre la agencia funeraria a la zona donde habr&aacute; de requerirse el traslado entre servicios de salubridad, Ministerio P&uacute;blico u Hospital, oh en su caso el traslado de su casa al pante&oacute;n, sin embargo en ning&uacute;n caso
                                                            exceder&aacute; la suma de 60 (sesenta) kil&oacute;metros del lugar donde ocurri&oacute; la deceso de &#8220;<strong>EL CLIENTE</strong>&#8221;, <strong>B) Servicio de Sala de Velaci&oacute;n</strong>; el servicio se prestara en los espacios que las agencias funerarias, propias o de terceros mismas que
                                                            deber&aacute;n contar con un espacio m&iacute;nimo para <strong>50 personas</strong>, con los espacios suficientes para sentarse, adem&aacute;s la sala de velaci&oacute;n deber&aacute; contar con espacio para comida o cafeter&iacute;a y estacionamiento para <strong>5 autos</strong> como requerimiento m&iacute;nimo,
                                                            la agencia funeraria deber&aacute; de contar con los insumos necesarios para realizar el servicio funerario, los cuales deber&aacute;n integrarse de; floreros de aluminio para el funeral, niquelados o bronce, equipo de velaci&oacute;n con porta ata&uacute;d, <strong>C) Servicio de
                                                            cafeter&iacute;a</strong>; la agencia funeraria deber&aacute; otorgar cuando menos <strong>50</strong> lonches a modo de cafeter&iacute;a, los cuales deber&aacute;n incluir; caf&eacute; o t&eacute; herbal para <strong>50</strong> personas, galletas para <strong>50</strong> personas, agua para <strong>50</strong> personas, s&aacute;ndwich para <strong>50</strong> personas. <strong>D)
                                                            Servicio de Equipo de velaci&oacute;n</strong>, la agencia funeraria deber&aacute; proporcionar a manera de comodato a los familiares de &#8220;<strong>EL CLIENTE</strong>&#8221; los insumos necesarios para realizar el servicio funerario en el lugar que indiquen los mismos, siempre y
                                                            cuando no excedan la suma de los kil&oacute;metros se&ntilde;alados en el <strong>INCISO</strong> A de la <strong>CLAUSULA SEGUNDA</strong> del presente, los cuales deber&aacute;n integrarse de; floreros para funeral de aluminio, niquelados o bronce, equipo de velaci&oacute;n con porta
                                                            ata&uacute;d, as&iacute; mismo la agencia deber&aacute; de acordar con los familiares de &#8220;<strong>EL CLIENTE</strong>&#8221; para recuperar sus insumos y los gastos de recuperaci&oacute;n correr&aacute;n por cuenta de la agencia funeraria. <strong>E) Acondicionamiento del Cuerpo</strong> La agencia
                                                            funeraria ya sea por medios propios o subcontrataci&oacute;n de terceros deber&aacute; de realizar los siguientes servicios; embalsamado del cuerpo de &#8220;<strong>EL CLIENTE</strong>&#8221;, maquillaje funerario de &#8220;<strong>EL CLIENTE</strong>&#8221;, mortaja funeraria de &#8220;<strong>EL CLIENTE</strong>&#8221; ,<strong>F)
                                                            Servicio de Cremaci&oacute;n</strong>, La agencia funeraria realizara el servicio de cremaci&oacute;n por cuenta propia o por medio de un tercero subcontratado, <strong>F) Ata&uacute;d</strong>, La agencia funeraria proporcionara un ata&uacute;d de madera barnizada, que deber&aacute; ce&ntilde;irse a
                                                            dos supuestos, si el servicio ser&aacute; de inhumaci&oacute;n el ata&uacute;d deber&aacute; entregarse a la familia para que sea sepultado con <strong>&#8220;EL CLIENTE&#8221;</strong>, si el servicio fuese de cremaci&oacute;n se deber&aacute; proporcionar un Ata&uacute;d de madera barnizada en forma de
                                                            comodato a la familia para los servicios de velaci&oacute;n del cuerpo, recuper&aacute;ndolo al momento de la cremaci&oacute;n, la agencia se asegurara de realizar los tr&aacute;mites y servicios necesarios para la prestaci&oacute;n de los servicios antes mencionados del inciso
                                                            A al F, la suma de todos los servicios mencionados en el presente inciso no exceder&aacute; el valor se&ntilde;alado para tal fin en el <strong>Fideicomiso F/0003</strong> donde se especifican los valores para este del cual se podr&aacute; consultar una copia en
                                                                <a class="hd-url" href="https://kasu.com.mx/documentacion/fideicomiso.pdf">www.kasu.com.mx/documentacion/fideicomiso.pdf</a></p>
        <p class="hd-text">Dicho servicio comenzara a tener vigencia a partir de los pasados 30 (treinta) d&iacute;as de la liquidaci&oacute;n y activaci&oacute;n en el fideicomiso del presente contrato. <br>
                                                            <strong>EDUCACI&Oacute;N UNIVERSITARIA PARA BENEFICIARIO</strong>; Sabemos que la educaci&oacute;n es una cuesti&oacute;n que todo padre quiere cubrir y otorgar a su hijo, por ello el servicio universitario cubrir&aacute; las siguientes cuestiones: <strong>A) INSCRIPCI&Oacute;N Y RE
                                                            INSCRIPCIONES</strong>. Dentro del servicio de educaci&oacute;n universitaria, se realizara el pago de la inscripci&oacute;n y re inscripci&oacute;n en caso de que la universidad llegase a ser p&uacute;blica; si la instituci&oacute;n en la que ingresase <strong>&#8220;EL CLIENTE&#8221;</strong> llegase a ser
                                                            particular, de igual forma se cubrir&aacute; la inscripci&oacute;n y re inscripciones correspondientes a 10 semestres universitarios o al monto no excedente en lo estipulado por el <strong>sfideicomiso F/0003, B) PAGO DE CUOTAS ORDINARIAS</strong>. Se le denominan
                                                            cuotas ordinarias a todos aquellos pagos correspondientes a las colegiaturas que deber&aacute;n cubrirse mensualmente en las escuelas, dichos costos ser&aacute;n asumidos en su totalidad por <strong>&#8220;fideicomiso F/0003&#8221;</strong>, a lo largo de la duraci&oacute;n de la carrera
                                                            universitaria sin exceder el monto estipulado por el mismo <strong>C) PAGO DE ACCESORIOS A LAS CUOTAS ORDINARIAS</strong>. El pago de accesorios a las cuotas estipuladas de igual forma ser&aacute;n cubiertas por <strong>&#8220;fideicomiso F/0003&#8221;</strong>, donde la
                                                            universidad deber&aacute; solicitar la cobertura de dichos accesorios, oh en su caso el beneficio con copia simple de dicho pago para acreditar tal petici&oacute;n. <strong>D) PAGO DE DERECHOS DE EXAMEN</strong>. En caso de que la escuela lo amerite. <strong>&#8220;fideicomiso
                                                            F/0003,&#8221;</strong> Deber&aacute; cubrir el monto correspondiente al derecho de ex&aacute;menes correspondientes a cada materia a lo largo de los <strong>10 semestres</strong> de duraci&oacute;n, ya sea de &iacute;ndole p&uacute;blica o privada; manifestando en el presente escrito que <strong>&#8220;fideicomiso
                                                            F/0003,&#8221;</strong> Se abstendr&aacute; de cubrir dicho pago siempre y cuando todos aquellos ex&aacute;menes sean provenientes de extraordinarios, t&iacute;tulos o recursos a lo largo de la carrera, oh en su defecto al momento en el que sea agotada dicha cantidad que
                                                            cubrir&aacute; el servicio universitario, estipulado en el <strong>fideicomiso F/0003</strong>; dicho servicio se har&aacute; valer &uacute;nicamente al haber trascurrido un plazo de 10 (diez) a&ntilde;os. <br>
                                                            Dicho pago no cubre los gastos de excursiones, cursos, dinero extra al pago de las clases ordinarias, intercambios; en caso de que llegasen a presentarse a lo largo de la universidad, estos correr&aacute;n a cuanta del contratante. </p>
        <p class="hd-text"> <strong>TERCERA. INFORMACI&Oacute;N OTORGADA: &#8220;GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V. &#8221;</strong> deber&aacute; informar a la persona que solicite la
                                                            celebraci&oacute;n del presente, sobre el alcance real del mismo, as&iacute; como las caracter&iacute;sticas que &#8220;<strong>EL CLIENTE</strong>&#8221; deba cumplir para ser aceptado en el &#8220;
                                                            <strong>FIDEICOMISO F/0003</strong>&#8221;, cuidando que se re&uacute;nan los requisitos de la solicitud para su plenitud de efectos. Por su parte &#8220;<strong>EL CLIENTE</strong>&#8221; se compromete
                                                            a comunicar a &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong>&#8221; Las gestiones y tr&aacute;mites que efect&uacute;e en relaci&oacute;n con los tr&aacute;mites objeto del presente.
                                                            <strong>CUARTA. AMORTIZACIONES: &#8220;EL CLIENTE&#8221;</strong> se compromete a cubrir el total pactado en la cl&aacute;usula quinta del presente instrumento en un plazo no mayor a <strong>12 meses</strong>
                                                            contados a partir de la firma del presente, mismas que ser&aacute;n depositadas en las cuentas que &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong>&#8221; o sus acreedores relacionados con el
                                                            &#8220;<strong>FIDEICOMISO F/0003</strong>&#8221; designen para tal fin. Que al tiempo se constituye la cantidad descrita en la secci&oacute;n pago apartado; costo por afiliaci&oacute;n, como garant&iacute;a
                                                            de pago por incumplimiento. <strong>QUINTA. DURACI&Oacute;N:</strong> El presente contrato es por tiempo indefinido, debido a la prescripci&oacute;n en el <strong>Fideicomiso F/0003</strong>  sin que cualquiera
                                                            de las partes pueda darlo por terminado. <strong>SEXTA. NOTIFICACIONES: &#8220;GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.&#8221;</strong> se compromete a dar aviso ya sea por escrito o de manera telef&oacute;nica a
                                                            &#8220;<strong>EL CLIENTE</strong>&#8221; de cualquier cambio de domicilio dentro de los diez d&iacute;as naturales siguientes al cambio, o de cualquier acto importante que necesite ser notificado. <strong>S&Eacute;PTIMA.
                                                                RESCISI&Oacute;N</strong>: En caso de incumplimiento de cualquiera de las obligaciones establecidas en el presente contrato, las partes podr&aacute;n rescindir de pleno derecho sin necesidad de declaraci&oacute;n judicial,
                                                                mediante simple aviso por escrito desde la fecha en que ocurra la violaci&oacute;n. En caso de rescisi&oacute;n, &#8220;<strong>EL CLIENTE</strong>&#8221; deber&aacute; pagar las comisiones pendientes a
                                                                &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong>&#8221; y &eacute;ste estar&aacute; obligado a devolver la documentaci&oacute;n que obre en su poder. Manifestando en el presente acto que, al momento de la
                                                                cancelaci&oacute;n del servicio por parte del contratante <strong>NO SE REALIZAR&Aacute; REEMBOLSO ALGUNO</strong> de la cantidad abonada por parte de &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong>&#8221;,
                                                                con excepci&oacute;n al servicio universitario, con el respectivo para de comisiones al grupo. <strong>OCTAVA. CESI&Oacute;N DE DERECHOS</strong>. Las obligaciones y derechos concedidos por el presente s&oacute;lo podr&aacute;n
                                                                ser cedidos por &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I DE C.V</strong>&#8221;, en lo que respecta a los derechos de cobro a &#8220;<strong>LOS CLIENTES</strong>&#8221; y en la obligaci&oacute;n de prestar el servicio a sus
                                                                empresas integradas o bien terceros autorizados por la mismas, dependiendo del servicio contratado.  <strong>NOVENA. REGULACI&Oacute;N</strong>: Para la interpretaci&oacute;n y cumplimiento del presente contrato, se estar&aacute;
                                                                a lo dispuesto en la Ley General de T&iacute;tulos y Operaciones de Cr&eacute;dito, y en caso de controversia, las partes se someter&aacute;n a la jurisdicci&oacute;n de los tribunales correspondientes, renunciando expresamente a
                                                                cualquier otra que pudiera corresponderles en raz&oacute;n de su domicilio actual o futuro. <strong>D&Eacute;CIMA. ACEPTACI&Oacute;N</strong>. Al recibir la tarjeta presente &#8220;<strong>EL CLIENTE</strong>&#8221; hace del conocimiento
                                                                de &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong>&#8221; que cuenta con conocimiento de la operativa del servicio, los procesos y procedimientos mediante los cuales puede hacer valido el servicio, as&iacute; como todas las
                                                                accesorias que este le brinda y las responsabilidades que &eacute;l mismo le exige para el cumplimiento de los descrito en la cl&aacute;usula primera del presente. <strong>D&Eacute;CIMA PRIMERA. AUTONOM&Iacute;A DE LAS DISPOSICIONES</strong>.
                                                                La invalidez ilegalidad o falta de coercibilidad de cualquiera de las disposiciones contenidas en el contrato no afectar&aacute; la validez y exigibilidad de las dem&aacute;s disposiciones acordadas por <strong>LAS PARTES; D&Eacute;CIMO SEGUNDA.- JURISDICCI&Oacute;N Y TRIBUNALES COMPETENTES</strong>.
                                                                Para la interpretaci&oacute;n y cumplimiento del presente instrumento, las partes se someten a la jurisdicci&oacute;n y competencia de los tribunales que correspondan al lugar que se suscribe este contrato, o a los tribunales elecci&oacute;n &#8220;<strong>GRUPO C&M ATLACOMULCO S.A.P.I. DE C.V.</strong>&#8221;,
                                                                renunciando a cualquier otro fuero que por raz&oacute;n de su domicilio presente o futuro les pudiera corresponder.</p>
        <br>
        <h4 class="hd-t">SOLICITANTE</h4>
    </div>
    <br><br><br><br><br><br><br>
    <div class="container">
        <img src="../assets/poliza/img/transp.png" class="hc-header">
        <table class="tc-le">
            <tr class="hc-tab" style="margin-top:50px; padding:0px;">
                <td > <h1 class="hc-text">GRUPO C&M ATLACOMULCO S.A DE C.V </h1></td>
            </tr>
            <tr>
                <td> <h1 class="hc-text">Edificio Rafael Valencia #2 Segundo Piso Col. Centro </h1></td>
            </tr>
            <tr>
                <td> <h1 class="hc-text">Atlacomulco, Estado de M&eacute;xico, M&eacute;xico </h1></td>
            </tr>
            <tr>
                <td> <h1 class="hc-text">Sucursal Atlacomulco: 712 5975 763 y 712 1416 269 </h1></td>
            </tr>
            <tr>
                <td> <h1 class="hc-text">E-mail: atncliente@kasu.com.mx </h1></td>
            </tr>
            <tr>
                <td> <h1 class="hc-text">www.kasu.com.mx </h1></td>
            </tr>
        </table>

        <h1 class="hc-tit">&#161;Felicidades&#33;</h1>
        <p class="hc-sub">Sabemos que proteger a tu familia es un trabajo de por vida,<br>
                acabas de adquirir el mejor respaldo para gastos disponible en M&eacute;xico. <br>
                &#161;Gracias por permitirnos estar cerca de ti&#33;</p>
        <img src="../assets/poliza/img/question.png" class="ask">
        <div class="ask-q">
            <h2 class="hc-tsub">DUDAS Y PREGUNTAS FRECUENTES</h2>
            <p class="hc-ts"><strong>- &#191;C&oacute;mo funciona&#63;</strong> <br>C&M atrav&eacute;s de sus agencias tiene la obligaci&oacute;n de prestar el servicio establezca el contrato ya sea funerario o universitario simplemente con realizar una llamada a los telefonos en su p&oacute;liza,el servicio es integramente realizado.</p>
            <p class="hc-ts"><strong>- &#191;Qu&eacute; ampara mi dinero&#63;</strong><br>Puedes sentirte seguro/a ya que tu dinero esta amparado por el fideicomiso f/0003; mismo que esta respaldado por la Secretar&iacute;a de Hacienda y Cr&eacute;dito P&uacute;blico.</p>
            <p class="hc-ts"><strong>-&#191;C&oacute;mo verifico que el fideicomiso es legal&#63;</strong><br>Una vez siendo cliente, puedes acceder a nuestra p&aacute;gina www.kasu.com.mx donde atrav&eacute;s de la CURP y ah&iacute; puedes verificar tu estado as&iacute; como el tipo de servicio que seleccionaste, del mismo modo se encuentra el fideicomiso f/0003 facilitando su descarga y verificaci&oacute;n.</p>
            <p class="hc-ts"><strong>-&#191;Por qu&eacute; pago poco y recibo un servicio de mayor valor&#63;</strong><br>Una parte de su dinero se va al fideicomiso y la otra se invierte en negocios de bajo riesgo, donde se trabaja de forma correcta para la adquisici&oacute;n del costo total del servicio a futuro, cuando se requiera.</p>
        </div>

        <table>
            <tr>
                <td class="hc-serf">
                    <div>
                        <h1 class="hc-sert"> SERVICIO FUNERARIO</h1>
                        <h4 class="hc-ques">&#191;C&oacute;mo funciona&#63;</h4>
                        <p class="hc-ques-tex">Una vez pagado el servicio, y pasados 30 d&iacute;as naturales de la liqui-daci&oacute;n el servicio se activa.</p>
                        <h4 class="hc-ques">&#191;Qu&eacute; puede cancelar mi servicio&#63;</h4>
                        <p class="hc-ques-tex">Falta de pago por un periodo mayor a 60 d&iacute;as naturales o acumular m&aacute;s de tres atrasos en el pago.</p>
                        <h4 class="hc-ques">&#191;Qu&eacute; incluye el servicio&#63;</h4>
                        <div class="hd-vs">
                            <ul>
                                <li class="hc-vires">Asesor&iacute;a legal para la tramitaci&oacute;n correspondiente.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Ata&uacute;d met&aacute;lico medio cristal,ata&uacute;d de madera barnizada o urna para las cenizas.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Traslado m&aacute;ximo de 60km.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Sala de velaci&oacute;n de excelencia est&aacute;ndar y servicio integral de cafeter&iacute;a.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Cremaci&oacute;n,( en caso detener seleccionado este tipo de servicio ).</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Equipo de velaci&oacute;n.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Arreglo est&eacute;tico.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Embalsamado del cuerpo.</li>
                            </ul>
                        </div>
                        <p class="hc-tedob"><strong>&#191;El servicio es trasferible&#63;</strong> No.</p>
                        <h4 class="hc-ques">&#191;Puedo cambiar de servicios o de funeraria&#63;</h4>
                        <p class="hc-ques-tex">KASU, cuenta con la m&aacute;s amplia red funeraria de M&eacute;xico, sin em-bargo puedes escoger otra funeraria siempre y cuando no exceda el topede costo del servicio.</p>
                        <h4 class="hc-ques">&#191;Puedo cambiar de servicio un vez contratado&#63;</h4>
                        <p class="hc-ques-tex">SI, siempre y cuando solicites a un ejecutivo KASU o acudas direct-amente a tu sucursal; en ning&uacute;n caso se podr&aacute; cambiar el tipo de servicio una vez que el titular ha fallecido.</p>
                        <h4 class="hc-ques">&#191;Com&oacute; lo hago valer&#63;</h4>
                        <p class="hc-ques-tex">Acudiendo directamente a la funeraria o marcando a los n&uacute;meros asignados presentando su tarjeta y poliza.</p>
                    </div>
                </td>

                <td class="hc-seru">
                    <div>
                        <h1  class="hc-sert">SERVICIO UNIVERSITARIO</h1>
                        <h4 class="hc-ques">&#191;C&oacute;mo funciona&#63;</h4>
                        <p class="hc-ques-tex">Una vez pagado el servicio, y al haber transcurrido 10 años el servicio se activa.</p>
                        <h4 class="hc-ques">&#191;Qu&eacute; puede cancelar mi servicio&#63;</h4>
                        <p class="hc-ques-tex">  Falta de pago por un periodo mayor a 60 d&iacute;as natu-rales o cumular m&aacute;s de tres atrasos en el pago.</p>
                        <h4 class="hc-ques">&#191;Qu&eacute; incluye el servicio&#63;</h4>
                        <div class="hd-vs">
                            <ul>
                                <li class="hc-vires">Pago de colegiaturas ( 10 semestres ).</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Inscripciones y reinscripciones.</li>
                            </ul>
                            <ul>
                                <li class="hc-vires">Periodos ordinarios.</li>
                            </ul>
                        </div>
                        <p class="hc-tedob"><strong>&#191;El servicio es trasferible&#63;</strong> No.</p>
                        <h4 class="hc-ques">&#191;Com&oacute; lo hago valer&#63;</h4>
                        <p class="hc-ques-tex">Acudiendo a nuestra sucursal con el contrato y tarjeta; para hacer valer su servicio.</p>
                        <h4 class="hc-ques">&#191;Qu&eacute; modificaciones se pueden hacer&#63;</h4>
                        <p class="hc-ques-tex">Cambiar debeneficiario; siempre y cuando se haya contratado bajo los acuerdos y estipulaciones del contrato.</p>

                        <h3>Para m&aacute;s informaci&oacute;n ingresa a nuestra p&aacute;gina web:</h3>
                        <img src="../assets/poliza/img/pagin.png" class="hc-url">
                    </div>
                </td>
            </tr>
        </table>
        <img src="../assets/poliza/img/trans2.png" class="hc-pie">
    </div>
</body>
</html>
<?php
/**
 * Render y guardado del PDF.
 * Crea la carpeta DATES/ si no existe y emite el PDF al navegador.
 */

// Resolver clase DOMPDF disponible
$__use_legacy = class_exists('DOMPDF', false);
if (!$__use_legacy && class_exists(\Dompdf\Dompdf::class, false)) {
    $__use_modern = true;
} elseif ($__use_legacy) {
    $__use_modern = false;
} else {
    http_response_code(500);
    exit('Error: DOMPDF no disponible.');
}

// Asegurar carpeta de salida
$dirOut = __DIR__ . '/DATES';
if (!is_dir($dirOut)) {
    @mkdir($dirOut, 0775, true);
}

if (isset($__use_modern) && $__use_modern) {
    $dompdf = new \Dompdf\Dompdf();
} else {
    $dompdf = new DOMPDF();
}

$dompdf->set_paper("A4", "portrait");
$dompdf->load_html(ob_get_clean());
$dompdf->render();

$output = $dompdf->output();
$nombrePdf = "KASU-" . $fecha . ".pdf";
file_put_contents($dirOut . "/" . $nombrePdf, $output);

// stream al navegador
$dompdf->stream($nombrePdf);