<?php
/************************************************************************************************************************
 * Seguridad.php
 * Contiene funciones de seguridad: firma de datos, validación CURP vía API,
 * obtención de clave pública y registro de auditoría, además de geocodificación inversa.
 * Fecha: 2025-11-04
 * Revisado por: JCCM
 *************************************************************************************************************************/

require_once __DIR__ . '/FunctionUsageTracker.php';
require_once __DIR__ . '/Funciones_Basicas.php';

// Instancia global para funciones básicas
$basicas = $basicas ?? new Basicas();

class Seguridad {

    // Telemetría de uso
    use UsageTrackerTrait;

    /********************************************************************************************************************
     * Firma()
     * Genera un hash Adler32 con datos del contacto y un “sub” adicional.
     * Entrada: ($c0 mysqli), ($Pr string id contacto), ($sub mixed).
     * Salida: string hash o null.
     * Fecha: 2025-11-04 — Revisado por JCCM
     ********************************************************************************************************************/
    public function Firma($c0, $Pr, $sub) {
        $this->trackUsage();
        $Pr = mysqli_real_escape_string($c0, (string)$Pr);

        $sql = "SELECT `Contacto`.`id`, `Contacto`.`Usuario`, `Contacto`.`Producto`,
                       `Usuario`.`ClaveCurp`, `Contacto`.`FechaRegistro`
                FROM `Contacto`
                INNER JOIN `Usuario` ON `Usuario`.`IdContact` = `Contacto`.`id`
                WHERE `Contacto`.`id` = '$Pr'";

        if ($rs = $c0->query($sql)) {
            if ($row = $rs->fetch_assoc()) {
                $costura = (string)$row['Usuario']
                         . (string)$row['Producto']
                         . (string)$row['ClaveCurp']
                         . (string)$sub
                         . (string)$row['FechaRegistro'];
                return hash('adler32', $costura, false);
            }
        }
        return null;
    }

    /**
     * Genera un identificador compacto y verificable para pólizas.
     *
     * Diseño:
     * - Entradas firmadas: CURP + FechaRegistro de la tabla Usuario + versión fija "K2".
     * - Autenticación: HMAC-SHA256 con clave maestra privada.
     * - Formato: "K2" + 12 chars base36 + 1 dígito de control = 15 caracteres.
     * - Resistente a falsificación (≈2^60 espacio efectivo) y estable aunque cambie Contacto.
     *
     * @param string $curp                 CURP del titular (18 chars). Se usa en mayúsculas.
     * @param string $fechaAltaUsuario     Fecha/hora de alta en Usuario, p.ej. "2025-11-09 13:45:02".
     * @param string $claveMaestra         Clave secreta del sistema (guárdala en .env).
     * @return string                      Código de póliza de 15 caracteres (p.ej. "K2C8R1WZ7M4Q3F").
     */
    function poliza_id_compacto(string $curp, string $fechaAltaUsuario, string $claveMaestra): string {
        $ver = 'K2';
        $msg = strtoupper($curp) . '|' . $fechaAltaUsuario . '|' . $ver;

        // HMAC-SHA256 → tomamos 60 bits (15 hex) y convertimos a base36 en 12 chars
        $mac   = hash_hmac('sha256', $msg, $claveMaestra);     // 64 hex
        $hex60 = substr($mac, 0, 15);                          // 60 bits
        $b36   = strtoupper(str_pad(base_convert($hex60, 16, 36), 12, '0', STR_PAD_LEFT));

        // Dígito de control: CRC32B(ver|body) → últimos 8 bits → base36 → 1 char
        $chk   = strtoupper(base_convert(substr(hash('crc32b', $ver . $b36), -2), 16, 36));
        $chk   = substr(str_pad($chk, 2, '0', STR_PAD_LEFT), 0, 1);

        return $ver . $b36 . $chk; // 15 chars
    }

    /**
     * Verifica que un código de póliza sea válido para los datos provistos.
     *
     * Reglas:
     * - Debe iniciar con "K2" y medir 15 caracteres.
     * - Recalcula el cuerpo con HMAC-SHA256 y compara.
     * - Recalcula y valida el dígito de control.
     *
     * @param string $code                Código a verificar (15 chars).
     * @param string $curp               CURP asociada a la póliza.
     * @param string $fechaAltaUsuario   Fecha/hora de alta en Usuario.
     * @param string $claveMaestra       Misma clave maestra usada en la generación.
     * @return bool                      true si pasa todas las validaciones, false en caso contrario.
     */
    
    function poliza_id_verificar(string $code, string $curp, string $fechaAltaUsuario, string $claveMaestra): bool {
        if (strlen($code) !== 15 || substr($code, 0, 2) !== 'K2') return false;
        $body = substr($code, 2, 12);
        $chk  = substr($code, 14, 1);
        if (!preg_match('/^[A-Z0-9]{12}$/', $body)) return false;

        $ver = 'K2';
        $msg = strtoupper($curp) . '|' . $fechaAltaUsuario . '|' . $ver;

        $mac   = hash_hmac('sha256', $msg, $claveMaestra);
        $hex60 = substr($mac, 0, 15);
        $b36   = strtoupper(str_pad(base_convert($hex60, 16, 36), 12, '0', STR_PAD_LEFT));
        if ($b36 !== $body) return false;

        $chk2 = strtoupper(base_convert(substr(hash('crc32b', $ver . $b36), -2), 16, 36));
        $chk2 = substr(str_pad($chk2, 2, '0', STR_PAD_LEFT), 0, 1);

        return hash_equals($chk, $chk2);
    }    
    /********************************************************************************************************************
     * peticion_get()
     * Llama API REST para validar CURP y retorna array decodificado.
     * Entrada: ($Curp string).
     * Salida: array| "error".
     * Fecha: 2025-11-04 — Revisado por JCCM
     ********************************************************************************************************************/
    public function peticion_get($Curp) {
        $this->trackUsage();

        $Curp = urlencode((string)$Curp);
        $url  = "https://conectame.ddns.net/rest/api.php?m=curp&user=Kasu&pass=]Q*[4Jt7eBw5!aY5&val={$Curp}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $respuesta = curl_exec($ch);
        if ($respuesta === false) {
            curl_close($ch);
            return "error";
        }
        curl_close($ch);

        return json_decode($respuesta, true);
    }

    /********************************************************************************************************************
     * ObtenerClave()
     * Obtiene la clave pública (PEM) a partir del certificado X509 almacenado.
     * Entrada: ($c0 mysqli), ($d8 string Id de Usuario).
     * Salida: string con clave pública o mensaje de error.
     * Fecha: 2025-11-04 — Revisado por JCCM
     ********************************************************************************************************************/
    public function ObtenerClave($c0, $d8) {
        $this->trackUsage();
        global $basicas; // usa la instancia global existente

        $d8 = mysqli_real_escape_string($c0, (string)$d8);

        $Dir = $basicas->BuscarCampos($c0, "DireccionClaves", "Usuario", "Id", $d8);
        if (!$Dir) {
            return "No se encontró Dirección de Claves";
        }

        $Dir = mysqli_real_escape_string($c0, (string)$Dir);
        $sql = "SELECT `X509` FROM `ClavesUnicas` WHERE `Direccion` = '$Dir'";

        if ($resp = $c0->query($sql)) {
            if ($Cert = $resp->fetch_assoc()) {
                $X509 = (string)$Cert['X509'];
                $pub  = openssl_pkey_get_public($X509);
                if (!$pub) {
                    return "Error en el certificado";
                }
                $det = openssl_pkey_get_details($pub);
                if (empty($det['key'])) {
                    return "Error en el certificado";
                }
                return $det['key'];
            }
        }
        return "Certificado no encontrado";
    }

    /********************************************************************************************************************
     * auditoria_registrar()
     * Registra fingerprint, posición GPS y evento de auditoría.
     * Entrada: ($mysqli), ($basicas), ($post array), ($evento string), ($host string).
     * Salida: array con ids creados: fingerprint_id, gps_id, evento_id.
     * Fecha: 2025-11-04 — Revisado por JCCM
     ********************************************************************************************************************/
    public function auditoria_registrar($mysqli, $basicas, array $post, string $evento, string $host): array {
        $this->trackUsage();

        // 1) Fingerprint idempotente
        $fpVal = $post['fingerprint'] ?? null;
        $fpId  = null;
        if ($fpVal) {
            $fpId = $basicas->BuscarCampos($mysqli, "id", "FingerPrint", "fingerprint", $fpVal);
            if (empty($fpId)) {
                $datFinger = [
                    "fingerprint"   => (string)($post['fingerprint']   ?? ''),
                    "browser"       => (string)($post['browser']       ?? ''),
                    "flash"         => (string)($post['flash']         ?? ''),
                    "canvas"        => (string)($post['canvas']        ?? ''),
                    "connection"    => (string)($post['connection']    ?? ''),
                    "cookie"        => (string)($post['cookie']        ?? ''),
                    "display"       => (string)($post['display']       ?? ''),
                    "fontsmoothing" => (string)($post['fontsmoothing'] ?? ''),
                    "fonts"         => (string)($post['fonts']         ?? ''),
                    "formfields"    => (string)($post['formfields']    ?? ''),
                    "java"          => (string)($post['java']          ?? ''),
                    "language"      => (string)($post['language']      ?? ''),
                    "silverlight"   => (string)($post['silverlight']   ?? ''),
                    "os"            => (string)($post['os']            ?? ''),
                    "timezone"      => (string)($post['timezone']      ?? ''),
                    "touch"         => (string)($post['touch']         ?? ''),
                    "truebrowser"   => (string)($post['truebrowser']   ?? ''),
                    "plugins"       => (string)($post['plugins']       ?? ''),
                    "useragent"     => (string)($post['useragent']     ?? '')
                ];
                $fpId = $basicas->InsertCampo($mysqli, "FingerPrint", $datFinger);
            }
        }

        // 2) GPS opcional
        $latitud  = $post['latitud']  ?? null;
        $longitud = $post['longitud'] ?? null;
        $accuracy = $post['accuracy'] ?? null;
        $gpsId = null;
        if ($latitud !== null && $longitud !== null && $accuracy !== null) {
            $datGps = [
                "latitud"  => $latitud,
                "longitud" => $longitud,
                "accuracy" => $accuracy
            ];
            $gpsId = $basicas->InsertCampo($mysqli, "gps", $datGps);
        }

        // 3) Variables de venta/usuario
        $ventaId    = isset($post['IdVenta'])   ? (int)$post['IdVenta']   : null;
        $contactoId = isset($post['IdContact']) ? (int)$post['IdContact'] : null;
        $IdUsuario  = isset($post['IdUsuario']) ? (int)$post['IdUsuario'] : null;

        // Usuario de sesión o “PLATAFORMA”
        $Usuario = isset($_SESSION['Vendedor']) ? $_SESSION['Vendedor'] : 'PLATAFORMA';

        // 4) Evento
        $now = date('Y-m-d H:i:s');
        $datEvt = [
            "IdFInger"      => $fpId,
            "Contacto"      => $contactoId,
            "IdVta"         => $ventaId,
            "IdUsr"         => $IdUsuario,
            "Idgps"         => $gpsId,
            "Host"          => $host,
            "Evento"        => $evento,
            "Usuario"       => $Usuario,
            "FechaRegistro" => $now
        ];

        $evtId = $basicas->InsertCampo($mysqli, "Eventos", $datEvt);

        return [
            "fingerprint_id" => $fpId,
            "gps_id"         => $gpsId,
            "evento_id"      => $evtId
        ];
    }

    /********************************************************************************************************************
     * reverseGeocodeAddress()
     * Hace reverse geocoding con Nominatim y estandariza dirección. Compara CP.
     * Entrada: (lat float), (lon float), (cpUsuario string opcional), (accuracy float opcional).
     * Salida: array con campos normalizados y cp_match.
     * Fecha: 2025-11-04 — Revisado por JCCM
     ********************************************************************************************************************/
    public function reverseGeocodeAddress(float $latitud, float $longitud, string $cpUsuario = '', float $accuracy = 0.0): array {
        $this->trackUsage();

        $out = [
            'ok'           => false,
            'display_name' => '',
            'calle'        => '',
            'colonia'      => '',
            'municipio'    => '',
            'estado'       => '',
            'pais'         => '',
            'cp_api'       => '',
            'cp_usuario'   => preg_replace('/\D+/', '', $cpUsuario),
            'cp_match'     => 0,
            'latitud'      => $latitud,
            'longitud'     => $longitud,
            'accuracy'     => $accuracy,
            'error'        => ''
        ];

        if (!$latitud || !$longitud) {
            $out['error'] = 'Coordenadas inválidas.';
            return $out;
        }

        $url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2'
             . '&lat=' . rawurlencode((string)$latitud)
             . '&lon=' . rawurlencode((string)$longitud)
             . '&addressdetails=1';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'KASU/1.0 (soporte@kasu.com.mx)',
            CURLOPT_TIMEOUT        => 8,
        ]);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($res === false || !$res) {
            $out['error'] = 'Fallo en geocodificación: ' . ($err ?: 'sin respuesta');
            return $out;
        }

        $j = json_decode($res, true);
        if (!is_array($j)) {
            $out['error'] = 'Respuesta inválida del servicio.';
            return $out;
        }

        $addr = $j['address'] ?? [];

        $calle     = trim(($addr['road'] ?? '') . ' ' . ($addr['house_number'] ?? ''));
        $colonia   = $addr['neighbourhood'] ?? ($addr['suburb'] ?? ($addr['quarter'] ?? ''));
        $municipio = $addr['city'] ?? ($addr['town'] ?? ($addr['village'] ?? ($addr['county'] ?? '')));
        $estado    = $addr['state'] ?? '';
        $pais      = $addr['country'] ?? '';
        $cp_api    = preg_replace('/\D+/', '', (string)($addr['postcode'] ?? ''));

        if ($out['cp_usuario'] && $cp_api && $out['cp_usuario'] === $cp_api) {
            $out['cp_match'] = 1;
        }

        $out['ok']           = true;
        $out['display_name'] = (string)($j['display_name'] ?? '');
        $out['calle']        = $calle;
        $out['colonia']      = $colonia;
        $out['municipio']    = $municipio;
        $out['estado']       = $estado;
        $out['pais']         = $pais;
        $out['cp_api']       = $cp_api;

        return $out;
    }
}
?>