<?php
declare(strict_types=1);
require_once __DIR__ . '/ia_role_profiles.php';

/**
 * ============================================================================
 * Archivo : ia_context_builder.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Construye el contexto completo para el agente conversacional
 *           incluyendo usuario, historial, permisos y estado del sistema
 * ============================================================================
 */

class IAContextBuilder {
    
    private array $sessionData = [];
    private array $userData = [];
    private array $conversationHistory = [];
    
    /**
     * Inicializa con datos de sesión
     */
    public function __construct(array $session) {
        $this->sessionData = $session;
        $this->loadUserData();
    }
    
    /**
     * Carga datos del usuario desde BD
     */
    private function loadUserData(): void {
        global $mysqli, $basicas;
        
        $idUsuario = (string)($this->sessionData['Vendedor'] ?? '');
        if (empty($idUsuario)) {
            return;
        }
        
        // Datos básicos
        $this->userData = [
            'id_usuario' => $idUsuario,
            'nombre' => (string)$basicas->BuscarCampos($mysqli, 'Nombre', 'Empleados', 'IdUsuario', $idUsuario),
            'nivel' => (int)$basicas->BuscarCampos($mysqli, 'Nivel', 'Empleados', 'IdUsuario', $idUsuario),
            'sucursal_id' => (int)$basicas->BuscarCampos($mysqli, 'Sucursal', 'Empleados', 'IdUsuario', $idUsuario),
            'fecha_ingreso' => (string)$basicas->BuscarCampos($mysqli, 'FechaAlta', 'Empleados', 'IdUsuario', $idUsuario)
        ];
        
        // Nombre de sucursal
        $this->userData['sucursal'] = (string)$basicas->BuscarCampos(
            $mysqli, 'nombreSucursal', 'Sucursal', 'Id', $this->userData['sucursal_id']
        );
        
        // Nombre del nivel
        $this->userData['nombre_nivel'] = (string)$basicas->BuscarCampos(
            $mysqli, 'NombreNivel', 'Nivel', 'Id', $this->userData['nivel']
        );
        
        // Determinar rol funcional
        $this->userData['rol'] = $this->determineUserRole(
            $this->userData['nombre_nivel'],
            $this->userData['nivel']
        );
    }
    
    /**
     * Determina rol funcional basado en nivel
     */
    private function determineUserRole(string $nombreNivel, int $nivel): array {
        return kasu_ia_role_profile($nombreNivel, $nivel);
    }
    
    /**
     * Establece historial de conversación
     */
    public function setConversationHistory(array $history): void {
        $this->conversationHistory = $history;
    }
    
    /**
     * Construye contexto completo para el prompt
     */
    public function buildContext(string $userMessage, array $systemState = []): array {
        return [
            'fecha_hoy' => date('Y-m-d'),
            'hora_actual' => date('H:i:s'),
            'usuario' => $this->userData,
            'historial' => $this->formatHistoryForPrompt(),
            'mensaje_actual' => $userMessage,
            'estado_sistema' => array_merge([
                'modo' => 'produccion',
                'version_ia' => 'gpt-5.1',
                'herramientas_disponibles' => 6
            ], $systemState)
        ];
    }
    
    /**
     * Formatea historial para prompt
     */
    private function formatHistoryForPrompt(): string {
        if (empty($this->conversationHistory)) {
            return "No hay historial previo.\n";
        }
        
        $formatted = "Historial reciente:\n";
        $maxTurns = min(count($this->conversationHistory), 10);
        
        for ($i = max(0, count($this->conversationHistory) - $maxTurns); $i < count($this->conversationHistory); $i++) {
            $turn = $this->conversationHistory[$i];
            $role = $turn['role'] ?? 'unknown';
            $content = $turn['content'] ?? '';
            
            $formatted .= strtoupper($role) . ": " . trim($content) . "\n";
        }
        
        return $formatted;
    }
    
    /**
     * Obtiene datos del usuario
     */
    public function getUserData(): array {
        return $this->userData;
    }
    
    /**
     * Verifica si usuario tiene permisos para ciertas acciones
     */
    public function hasPermission(string $action): bool {
        $nivel = $this->userData['nivel'] ?? 0;
        
        $permisos = [
            'enviar_correo' => $nivel >= 5,
            'actualizar_datos' => $nivel >= 4,
            'generar_cotizacion' => $nivel >= 6,
            'ver_todos_clientes' => $nivel >= 4,
            'ver_estadisticas' => $nivel >= 5
        ];
        
        return $permisos[$action] ?? false;
    }
}
