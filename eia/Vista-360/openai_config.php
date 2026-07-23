<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : openai_config.php (adaptado a DeepSeek)
 * Carpeta : /eia/Vista-360
 * Modelo  : deepseek-chat
 * DeepSeek API es compatible con OpenAI SDK – endpoint /v1/chat/completions
 * ============================================================================
 */

// ---------------------------------------------------------------------------
// Carga de secretos – primero .env, luego secret_config.php como fallback
// ---------------------------------------------------------------------------

// La API key se carga desde .env via librerias.php (parser nativo)
if (!defined('DEEPSEEK_API_KEY')) {
    $envKey = getenv('DEEPSEEK_API_KEY');
    if ($envKey) {
        define('DEEPSEEK_API_KEY', $envKey);
    }
}

// Fallback: secret_config.php (legado, será eliminado)
if (!defined('DEEPSEEK_API_KEY')) {
    $secretFile = __DIR__ . '/secret_config.php';
    if (is_file($secretFile)) {
        require_once $secretFile;
    }
}

// Compatibilidad hacia atrás: redefinir constantes OpenAI → DeepSeek
if (!defined('OPENAI_API_KEY') && defined('DEEPSEEK_API_KEY')) {
    define('OPENAI_API_KEY', DEEPSEEK_API_KEY);
}

// ---------------------------------------------------------------------------
// Configuración general
// ---------------------------------------------------------------------------
if (!defined('DEEPSEEK_API_BASE')) {
    define('DEEPSEEK_API_BASE', 'https://api.deepseek.com/v1');
}

if (!defined('DEEPSEEK_MODEL')) {
    define('DEEPSEEK_MODEL', 'deepseek-chat');
}

// Compatibilidad hacia atrás
if (!defined('OPENAI_API_BASE')) {
    define('OPENAI_API_BASE', DEEPSEEK_API_BASE);
}
if (!defined('OPENAI_MODEL')) {
    define('OPENAI_MODEL', DEEPSEEK_MODEL);
}

/**
 * Verifica que la API key esté configurada.
 */
function openai_assert_config(): void
{
    if (!defined('DEEPSEEK_API_KEY') || !DEEPSEEK_API_KEY) {
        throw new RuntimeException(
            'DEEPSEEK_API_KEY no configurada. Agregala en .env o secret_config.php.'
        );
    }
}

/**
 * Llama a DeepSeek /v1/chat/completions y devuelve texto plano.
 */
function openai_simple_text(string $prompt, int $maxTokens = 400): string
{
    openai_assert_config();

    $url = DEEPSEEK_API_BASE . '/chat/completions';

    $payload = [
        'model'       => DEEPSEEK_MODEL,
        'messages'    => [
            ['role' => 'user', 'content' => $prompt],
        ],
        'max_tokens'  => $maxTokens,
        'temperature' => 0.7,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_API_KEY,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 30,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Error cURL DeepSeek: ' . $err);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Respuesta invalida de DeepSeek: ' . substr($raw, 0, 300));
    }

    if ($status < 200 || $status >= 300) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException('Error DeepSeek: ' . $msg);
    }

    // chat/completions: choices[0].message.content
    $text = trim((string)($data['choices'][0]['message']['content'] ?? ''));
    if ($text === '') {
        throw new RuntimeException('No se encontro texto en la respuesta de DeepSeek.');
    }

    return $text;
}

/**
 * Llama a DeepSeek /v1/chat/completions con historial y devuelve texto + id.
 * DeepSeek no soporta previous_response_id; usamos messages con history.
 *
 * @param string $prompt
 * @param int $maxTokens
 * @param string|null $previousResponseId  (ignorado por DeepSeek, mantenido por compatibilidad)
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

    $url = DEEPSEEK_API_BASE . '/chat/completions';

    $messages = [['role' => 'user', 'content' => $prompt]];

    // Si hay metadata con history, lo inyectamos como mensajes previos
    if (!empty($metadata['history'])) {
        $historyMessages = [];
        foreach ($metadata['history'] as $turn) {
            if (!empty($turn['user'])) {
                $historyMessages[] = ['role' => 'user', 'content' => $turn['user']];
            }
            if (!empty($turn['assistant'])) {
                $historyMessages[] = ['role' => 'assistant', 'content' => $turn['assistant']];
            }
        }
        $messages = array_merge($historyMessages, $messages);
    }

    $payload = [
        'model'       => DEEPSEEK_MODEL,
        'messages'    => $messages,
        'max_tokens'  => $maxTokens,
        'temperature' => 0.7,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_API_KEY,
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 30,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Error cURL DeepSeek: ' . $err);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Respuesta invalida de DeepSeek: ' . substr($raw, 0, 300));
    }

    if ($status < 200 || $status >= 300) {
        $msg = $data['error']['message'] ?? ('HTTP ' . $status);
        throw new RuntimeException('Error DeepSeek: ' . $msg);
    }

    $text = trim((string)($data['choices'][0]['message']['content'] ?? ''));
    if ($text === '') {
        throw new RuntimeException('No se encontro texto en la respuesta de DeepSeek.');
    }

    return [
        'text' => $text,
        'id'   => (string)($data['id'] ?? ''),
    ];
}
