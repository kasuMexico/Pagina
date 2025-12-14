<?php
declare(strict_types=1);

/**
 * Cliente SMS Masivos API v2.
 * Uso: $cli = new SmsMasivosClient($configArray); $cli->sendSms(['7123456789'], 'Mensaje...', ['shorten_url'=>1]);
 */
final class SmsMasivosClient
{
    private string $apikey;
    private string $baseUrl;
    private int $sandbox;

    public function __construct(array $config)
    {
        $this->apikey  = (string)($config['apikey'] ?? '');
        $this->baseUrl = rtrim((string)($config['base_url'] ?? ''), '/');
        $this->sandbox = (int)($config['sandbox'] ?? 0);
    }

    public function sendSms(array $numbers, string $message, array $opts = []): array
    {
        $normalized = $this->normalizeNumbers($numbers);
        if (empty($normalized)) {
            return ['success'=>false,'status'=>0,'code'=>'no_numbers','message'=>'Sin números válidos','result'=>null,'raw'=>''];
        }

        $msg = $this->sanitizeMessage($message);
        $body = [
            'message'      => $msg,
            'numbers'      => implode(',', $normalized),
            'country_code' => '52',
        ];
        if (!empty($opts['shorten_url'])) {
            $body['shorten_url'] = 1;
        }
        if (!empty($opts['extra_params']) && is_array($opts['extra_params'])) {
            $body['extra_params'] = json_encode(array_slice($opts['extra_params'], 0, 3));
        }
        if ($this->sandbox) {
            $body['sandbox'] = 1;
        }

        $url = $this->baseUrl . '/sms/send';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($body),
            CURLOPT_HTTPHEADER     => ['apikey: ' . $this->apikey, 'Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);
        $raw    = curl_exec($ch);
        $errno  = curl_errno($ch);
        $err    = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0) {
            return ['success'=>false,'status'=>$status,'code'=>'curl_'.$errno,'message'=>$err,'result'=>null,'raw'=>(string)$raw];
        }

        $decoded = json_decode((string)$raw, true);
        $code = $decoded['code'] ?? null;
        $ok = ($status >= 200 && $status < 300 && (($decoded['success'] ?? false) || $code === 'ok'));

        return [
            'success' => (bool)$ok,
            'status'  => $status,
            'code'    => $code,
            'message' => $decoded['message'] ?? null,
            'result'  => $decoded,
            'raw'     => (string)$raw,
        ];
    }

    private function normalizeNumbers(array $numbers): array
    {
        $out = [];
        foreach ($numbers as $n) {
            $digits = preg_replace('/\D+/', '', (string)$n);
            if (str_starts_with($digits, '52') && strlen($digits) === 12) {
                $digits = substr($digits, 2);
            }
            if (strlen($digits) === 10) {
                $out[] = $digits;
            }
        }
        return array_values(array_unique($out));
    }

    private function sanitizeMessage(string $msg): string
    {
        $map = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
            'ñ'=>'n','Ñ'=>'N','ü'=>'u','Ü'=>'U',
        ];
        $msg = strtr($msg, $map);
        // remover caracteres de control
        $msg = preg_replace('/[^\P{C}\n\r\t]/u', '', $msg);
        return trim($msg);
    }
}
