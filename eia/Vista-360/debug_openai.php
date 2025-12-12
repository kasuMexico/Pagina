<?php
header('Content-Type: text/plain; charset=utf-8');
echo "=== PRUEBA ESPECÍFICA RESPONSES API ===\n\n";

require_once __DIR__ . '/openai_config.php';  // La versión CORREGIDA

// Prueba 1: Mínimo de tokens
echo "1. Probando con mínimo de tokens (16)...\n";
try {
    $respuesta = openai_simple_text("Di solo 'HOLA'", 16);
    echo "   ✅ Respuesta: " . $respuesta . "\n";
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Prueba 2: Análisis de mensaje real
echo "\n2. Analizando mensaje del usuario...\n";
$userMessage = "busca al cliente jose carlos cabrera monroy y muestrame su email";

$systemPrompt = <<<PROMPT
Eres un analizador de intenciones para KASU. Analiza el mensaje del usuario y determina qué acción necesita.

Formato de respuesta JSON:
{
  "accion_principal": "buscar_cliente",
  "parametros": {
    "nombre": "texto a buscar",
    "tipo": "cliente|prospecto|ambos"
  },
  "razonamiento": "explicación breve"
}

Responde SOLO con el JSON, nada más.
PROMPT;

try {
    $analisis = openai_simple_text($systemPrompt . "\n\nUsuario: " . $userMessage, 200);
    echo "   ✅ Análisis recibido\n";
    
    // Intentar parsear JSON
    $jsonStart = strpos($analisis, '{');
    $jsonEnd = strrpos($analisis, '}');
    
    if ($jsonStart !== false && $jsonEnd !== false) {
        $jsonStr = substr($analisis, $jsonStart, $jsonEnd - $jsonStart + 1);
        $data = json_decode($jsonStr, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   JSON parseado correctamente:\n";
            echo "   - Acción: " . ($data['accion_principal'] ?? 'N/A') . "\n";
            echo "   - Nombre: " . ($data['parametros']['nombre'] ?? 'N/A') . "\n";
        } else {
            echo "   ❌ Error parseando JSON: " . json_last_error_msg() . "\n";
            echo "   Respuesta cruda: " . $analisis . "\n";
        }
    } else {
        echo "   ❌ No se encontró JSON en la respuesta\n";
        echo "   Respuesta: " . $analisis . "\n";
    }
    
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Prueba 3: Integración con tools
echo "\n3. Probando integración con tools registry...\n";
try {
    // Cargar tools registry
    require_once __DIR__ . '/ia_tools_registry.php';
    $toolsRegistry = new IAToolsRegistry();
    $toolsRegistry->initializeKasutools();
    
    $tools = $toolsRegistry->getToolsForOpenAI();
    echo "   ✅ Tools cargadas: " . count($tools) . "\n";
    
    // Crear prompt con tools
    $toolsPrompt = "Herramientas disponibles:\n" . json_encode($tools, JSON_UNESCAPED_UNICODE);
    $toolsPrompt .= "\n\nMensaje del usuario: \"$userMessage\"\n\n";
    $toolsPrompt .= "¿Qué herramienta debería usar y con qué parámetros? Responde con JSON.";
    
    $respuestaTools = openai_simple_text($toolsPrompt, 300);
    echo "   ✅ Respuesta con tools: " . substr($respuestaTools, 0, 100) . "...\n";
    
} catch (Throwable $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}