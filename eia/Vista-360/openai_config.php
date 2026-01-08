<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : openai_config.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Configuración central y funciones helper para consumir
 *           la API REST de OpenAI desde KASU (Vista 360).
 * Modelo   : gpt-5.1
 * ============================================================================
 */

// ---------------------------------------------------------------------------
// Carga de secretos (API KEY y PROJECT ID)
// ---------------------------------------------------------------------------
$secretFile = __DIR__ . '/secret_config.php';
if (is_file($secretFile)) {
    require_once $secretFile;
}

// Permitir variables de entorno como fallback
if (!defined('OPENAI_API_KEY')) {
    $envKey = getenv('OPENAI_API_KEY');
    if ($envKey) define('OPENAI_API_KEY', $envKey);
}

if (!defined('OPENAI_PROJECT_ID')) {
    $envProject = getenv('OPENAI_PROJECT_ID');
    if ($envProject) define('OPENAI_PROJECT_ID', $envProject);
}

// ---------------------------------------------------------------------------
// Configuración general
// ---------------------------------------------------------------------------
if (!defined('OPENAI_API_BASE')) {
    define('OPENAI_API_BASE', 'https://api.openai.com/v1');
}

// Usaremos GPT-5.1
if (!defined('OPENAI_MODEL')) {
    define('OPENAI_MODEL', 'gpt-5.1');
}

/**
 * Verifica que la API key y el Project ID estén configurados.
 */
function openai_assert_config(): void
{
    if (!defined('OPENAI_API_KEY') || !OPENAI_API_KEY) {
        throw new RuntimeException(
            'OPENAI_API_KEY no configurada. Edita secret_config.php o usa variable de entorno.'
        );
    }

    if (!defined('OPENAI_PROJECT_ID') || !OPENAI_PROJECT_ID) {
        throw new RuntimeException(
            'OPENAI_PROJECT_ID no configurado. Edita secret_config.php o usa variable de entorno.'
        );
    }
}

/**
 * Llama al endpoint /v1/responses (modelo gpt-5.1) y devuelve texto plano.
 *
 * @param string $prompt
 * @param int $maxTokens
 * @return string
 */
function openai_simple_text(string $prompt, int $maxTokens = 400): string
{
    openai_assert_config();

    $url = OPENAI_API_BASE . '/responses';

    // IMPORTANTE:
    // GPT-5.1 no usa `temperature` en Responses API. No lo enviamos.
    $payload = [
        'model'             => OPENAI_MODEL,
        'input'             => $prompt,
        'max_output_tokens' => $maxTokens
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
            'OpenAI-Project: ' . OPENAI_PROJECT_ID,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 20,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Error cURL al llamar a OpenAI: ' . $err);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Respuesta inválida de OpenAI: ' . substr($raw, 0, 300));
    }

    if ($status < 200 || $status >= 300) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException('Error OpenAI: ' . $msg);
    }

    // Responses API: buscamos output_text
    if (!isset($data['output']) || !is_array($data['output'])) {
        throw new RuntimeException('Respuesta sin campo "output" en Responses API.');
    }

    foreach ($data['output'] as $item) {
        if (($item['type'] ?? null) !== 'message') continue;

        foreach ($item['content'] as $part) {
            if (($part['type'] ?? null) === 'output_text') {
                return trim((string)$part['text']);
            }
        }
    }

    throw new RuntimeException('No se encontró texto en la respuesta de OpenAI.');
}

/**
 * Llama al endpoint /v1/responses y devuelve texto + id de respuesta.
 *
 * @param string $prompt
 * @param int $maxTokens
 * @param string|null $previousResponseId
 * @param array $metadata
 * @return array{text:string,id:string}
 */
function openai_simple_text_with_id(
    string $prompt,
    int $maxTokens = 400,
    ?string $previousResponseId = null,
    array $metadata = []
): array {
    openai_assert_config();

    $url = OPENAI_API_BASE . '/responses';

    $payload = [
        'model'             => OPENAI_MODEL,
        'input'             => $prompt,
        'max_output_tokens' => $maxTokens
    ];

    if ($previousResponseId) {
        $payload['previous_response_id'] = $previousResponseId;
    }
    if (!empty($metadata)) {
        $payload['metadata'] = $metadata;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
            'OpenAI-Project: ' . OPENAI_PROJECT_ID,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 20,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Error cURL al llamar a OpenAI: ' . $err);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Respuesta inválida de OpenAI: ' . substr($raw, 0, 300));
    }

    if ($status < 200 || $status >= 300) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException('Error OpenAI: ' . $msg);
    }

    $text = '';
    if (isset($data['output']) && is_array($data['output'])) {
        foreach ($data['output'] as $item) {
            if (($item['type'] ?? null) !== 'message') {
                continue;
            }
            foreach ($item['content'] as $part) {
                if (($part['type'] ?? null) === 'output_text') {
                    $text = trim((string)$part['text']);
                    break 2;
                }
            }
        }
    }

    if ($text === '') {
        throw new RuntimeException('No se encontró texto en la respuesta de OpenAI.');
    }

    return [
        'text' => $text,
        'id'   => (string)($data['id'] ?? ''),
    ];
}
