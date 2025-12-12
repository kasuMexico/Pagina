<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_agente_config.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Configuración centralizada del agente conversacional
 *           Permite ajustar comportamientos sin modificar código principal
 * ============================================================================
 */

return [
    // Configuración general
    'general' => [
        'agent_name' => 'Asistente KASU',
        'agent_version' => '2.0.0',
        'default_timezone' => 'America/Mexico_City',
        'max_response_length' => 2000,
        'enable_debug_log' => false
    ],
    
    // Configuración de OpenAI
    'openai' => [
        'model' => 'gpt-5.1',
        'max_tokens_planning' => 1200,
        'max_tokens_response' => 800,
        'temperature' => 0.7,
        'timeout_seconds' => 30
    ],
    
    // Configuración de conversación
    'conversation' => [
        'max_history_turns' => 25,
        'session_key' => 'ia_agente_conversacion',
        'enable_persistence' => false,
        'cleanup_old_sessions_hours' => 24
    ],
    
    // Configuración de ejecución
    'execution' => [
        'require_confirmation' => true,
        'max_sequential_actions' => 5,
        'action_timeout_seconds' => 15,
        'stop_on_first_error' => false,
        'enable_action_logging' => true
    ],
    
    // Permisos por nivel de usuario
    'permissions' => [
        // Nivel => [tools permitidas]
        7 => ['*'], // Agente externo: todas
        6 => ['*'], // Ejecutivo ventas: todas
        5 => ['buscar_cliente_prospecto', 'calcular_estado_credito', 'enviar_correo_cliente', 'obtener_informacion_completa'],
        4 => ['*'], // Coordinador: todas
        3 => ['*'], // Gerente: todas
        2 => ['*'], // Mesa control: todas
        1 => ['*']  // Dirección: todas
    ],
    
    // Mensajes predefinidos
    'messages' => [
        'welcome' => '¡Hola! Soy tu asistente de IA de KASU. ¿En qué puedo ayudarte hoy?',
        'confirmation_prompt' => '¿Deseas ejecutar esta acción?',
        'action_success' => '✅ Acción completada exitosamente.',
        'action_failed' => '❌ No se pudo completar la acción.',
        'no_results' => 'No encontré resultados para tu búsqueda.',
        'need_more_info' => 'Necesito más información para ayudarte. ¿Podrías ser más específico?',
        'error_generic' => 'Ocurrió un error. Por favor, intenta nuevamente.',
        'error_timeout' => 'La operación está tomando más tiempo de lo esperado. Intenta nuevamente.'
    ],
    
    // Estilos para respuestas HTML
    'styles' => [
        'success_color' => '#10b981',
        'error_color' => '#ef4444',
        'warning_color' => '#f59e0b',
        'info_color' => '#3b82f6',
        'font_family' => 'system-ui, -apple-system, sans-serif',
        'border_radius' => '8px'
    ]
];