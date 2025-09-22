<?php
//Contador de veces que se usa cada funcion para seguimiento *JCCM
require_once 'FunctionUsageTracker.php';

//creamos una variable general para las funciones
$basicas = new Basicas();
class Seguridad {
    
    // Usa el trait para poder registrar el uso de los métodos.
    use UsageTrackerTrait;

    /**
     * Genera un hash con los datos del cliente registrados en la venta.
     * Se utiliza: Usuario, Producto, ClaveCurp, $sub y FechaRegistro.
     *
     * @param mysqli $c0 Conexion a la base de datos.
     * @param string $Pr Identificador del contacto.
     * @param mixed  $sub Valor adicional a concatenar.
     * @return string|null Hash generado con Adler32 o null en caso de fallo.
     */
    public function Firma($c0, $Pr, $sub) {
        $this->trackUsage();  // Registra el uso de este método.
        $Pr = mysqli_real_escape_string($c0, $Pr);
        // Se usa JOIN explícito para relacionar Contacto y Usuario.
        $aguja = "SELECT `Contacto`.`id`, `Contacto`.`Usuario`, `Contacto`.`Producto`, 
                         `Usuario`.`ClaveCurp`, `Contacto`.`FechaRegistro`
                  FROM `Contacto`
                  INNER JOIN `Usuario` ON `Usuario`.`IdContact` = `Contacto`.`id`
                  WHERE `Contacto`.`id` = '$Pr'";
        if ($hilos = $c0->query($aguja)) {
            if ($tela = $hilos->fetch_assoc()) {
                $costura = $tela['Usuario'] . $tela['Producto'] . $tela['ClaveCurp'] . $sub . $tela['FechaRegistro'];
                $firma = hash("adler32", $costura, FALSE);
                return $firma;
            }
        }
        return null;
    }

    /**
     * Consulta una API REST para validar una CURP y retorna el resultado en forma de array.
     *
     * @param string $Curp Clave CURP a consultar.
     * @return array|string Array con la respuesta decodificada o "error" en caso de fallo.
     */
    public function peticion_get($Curp) {
        $this->trackUsage();  // Registra el uso de este método.
        $Curp = urlencode($Curp);
        $url = "https://conectame.ddns.net/rest/api.php?m=curp&user=Kasu&pass=]Q*[4Jt7eBw5!aY5&val=" . $Curp;
        $conexion = curl_init();
        curl_setopt($conexion, CURLOPT_URL, $url);
        curl_setopt($conexion, CURLOPT_HTTPGET, TRUE);
        curl_setopt($conexion, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($conexion, CURLOPT_RETURNTRANSFER, 1);
        $respuesta = curl_exec($conexion);
        if ($respuesta === false) {
            curl_close($conexion);
            return "error";
        }
        curl_close($conexion);
        return json_decode($respuesta, true);
    }

    /**
     * Retorna la clave pública de un certificado digital.
     * Se obtiene la dirección de claves del usuario y se busca el certificado correspondiente.
     *
     * @param mysqli $c0 Conexion a la base de datos.
     * @param string $d8 Identificador del Usuario.
     * @return string Mensaje de error o la clave pública.
     */
    public function ObtenerClave($c0, $d8) {
        $this->trackUsage();  // Registra el uso de este método.
        $d8 = mysqli_real_escape_string($c0, $d8);
        $Dir = $basicas->BuscarCampos($c0, "DireccionClaves", "Usuario", "Id", $d8);
        if (!$Dir) {
            return "No se encontró Dirección de Claves";
        }
        $Cons = "SELECT `X509` FROM `ClavesUnicas` WHERE `Direccion` = '$Dir'";
        // Usar el método orientado a objetos para ejecutar la consulta
        if ($resp = $c0->query($Cons)) {
            if ($Cert = $resp->fetch_assoc()) {
                $X509 = $Cert['X509'];
                $pub_key = openssl_pkey_get_public($X509);
                if (!$pub_key) {
                    return "Error en el certificado";
                }
                $keyData = openssl_pkey_get_details($pub_key);
                if (empty($keyData['key'])) {
                    return "Error en el certificado";
                }
                return $keyData['key'];
            }
        }
        return "Certificado no encontrado";
    }

    // Requiere $basicas->BuscarCampos() y ->InsertCampo()
    public function auditoria_registrar($mysqli, $basicas, array $post, string $evento, string $host): array {
        // 1) Fingerprint: idempotente por valor
        $fpVal = $post['fingerprint'] ?? null;
        $fpId  = null;
        if ($fpVal) {
            $fpId = $basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fpVal);
            if (empty($fpId)) {
                $datFinger = [
                    "fingerprint"   => $fpVal,
                    "browser"       => $post['browser']    ?? '',
                    "flash"         => $post['flash']      ?? '',
                    "canvas"        => $post['canvas']     ?? '',
                    "connection"    => $post['connection'] ?? '',
                    "cookie"        => $post['cookie']     ?? '',
                    "display"       => $post['display']    ?? '',
                    "fontsmoothing" => $post['fontsmoothing'] ?? '',
                    "fonts"         => $post['fonts']      ?? '',
                    "formfields"    => $post['formfields'] ?? '',
                    "java"          => $post['java']       ?? '',
                    "language"      => $post['language']   ?? '',
                    "silverlight"   => $post['silverlight']?? '',
                    "os"            => $post['os']         ?? '',
                    "timezone"      => $post['timezone']   ?? '',
                    "touch"         => $post['touch']      ?? '',
                    "truebrowser"   => $post['truebrowser']?? '',
                    "plugins"       => $post['plugins']    ?? '',
                    "useragent"     => $post['useragent']  ?? ''
                ];
                $fpId = $basicas->InsertCampo($mysqli, "FingerPrint", $datFinger);
            }
        }

        // 2) GPS (acepta Precision o Presicion)
        $lat = $post['Latitud']   ?? null;
        $lon = $post['Longitud']  ?? null;
        $pre = $post['Precision'] ?? ($post['Presicion'] ?? null);
        $gpsId = null;
        if ($lat !== null && $lon !== null && $pre !== null) {
            $datGps = ["Latitud"=>$lat, "Longitud"=>$lon, "Presicion"=>$pre];
            $gpsId = $basicas->InsertCampo($mysqli, "gps", $datGps);
        }

        // Captamos las variables de la venta
        $ventaId    = isset($post['IdVenta'])   ? (int)$post['IdVenta']   : null;
        $contactoId = isset($post['IdContact']) ? (int)$post['IdContact'] : null;
        $IdUsuario  = isset($post['IdUsuario']) ? (int)$post['IdUsuario'] : null;

        // 3) Evento
        $now = date('Y-m-d H:i:s');
        $datEvt = [
            "IdFInger"      => $fpId,
            "Contacto"      => $contactoId,
            "IdVta"         => $ventaId,
            "IdUsr"         => $IdUsuario,
            "Idgps"         => $gpsId,
            "Host"          => $host,
            "Evento"        => $evento,
            "Usuario"       => $_SESSION["Vendedor"],
            "FechaRegistro" => $now
        ];

        $evtId = $basicas->InsertCampo($mysqli, "Eventos", $datEvt);

        return [
            "fingerprint_id"=>$fpId, 
            "gps_id"=>$gpsId, 
            "evento_id"=>$evtId
        ];    
    }
}
?>
