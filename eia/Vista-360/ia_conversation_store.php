<?php
declare(strict_types=1);

/**
 * ============================================================================
 * Archivo : ia_conversation_store.php
 * Carpeta : /eia/Vista-360
 * Qué hace: Almacena y gestiona el historial de conversaciones
 *           Persiste en sesión y opcionalmente en BD
 * ============================================================================
 */

class IAConversationStore {
    
    private string $sessionKey = 'ia_conversation_history';
    private int $maxTurns = 20;
    private bool $persistToDB = false;
    
    /**
     * Configura el almacenamiento
     */
    public function __construct(string $sessionKey = 'ia_conversation_history', int $maxTurns = 20) {
        $this->sessionKey = $sessionKey;
        $this->maxTurns = $maxTurns;
        
        // Inicializar en sesión si no existe
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }
    
    /**
     * Habilita persistencia en BD
     */
    public function enableDBPersistence(bool $enable = true): void {
        $this->persistToDB = $enable;
    }
    
    /**
     * Añade un turno a la conversación
     */
    public function addTurn(string $role, string $content, array $metadata = []): void {
        $turn = [
            'role' => $role,
            'content' => $content,
            'timestamp' => date('Y-m-d H:i:s'),
            'metadata' => $metadata
        ];
        
        // Añadir a sesión
        $_SESSION[$this->sessionKey][] = $turn;
        
        // Mantener límite de turns
        if (count($_SESSION[$this->sessionKey]) > $this->maxTurns) {
            $_SESSION[$this->sessionKey] = array_slice($_SESSION[$this->sessionKey], -$this->maxTurns);
        }
        
        // Persistir en BD si está habilitado
        if ($this->persistToDB) {
            $this->saveTurnToDB($turn);
        }
    }
    
    /**
     * Añade un turno de sistema (acción ejecutada)
     */
    public function addSystemTurn(string $action, array $result, string $summary = ''): void {
        $this->addTurn('system', $summary ?: "Ejecutada acción: {$action}", [
            'action' => $action,
            'result' => $result,
            'type' => 'action_execution'
        ]);
    }
    
    /**
     * Obtiene historial completo
     */
    public function getHistory(): array {
        return $_SESSION[$this->sessionKey] ?? [];
    }
    
    /**
     * Obtiene historial formateado para prompt
     */
    public function getFormattedHistory(int $lastTurns = 10): string {
        $history = $this->getHistory();
        $startIdx = max(0, count($history) - $lastTurns);
        $recent = array_slice($history, $startIdx);
        
        if (empty($recent)) {
            return "No hay historial de conversación reciente.\n";
        }
        
        $formatted = "Historial de conversación reciente:\n\n";
        
        foreach ($recent as $turn) {
            $role = strtoupper($turn['role']);
            $content = $turn['content'];
            $time = date('H:i', strtotime($turn['timestamp']));
            
            $formatted .= "[{$time}] {$role}: {$content}\n";
        }
        
        return $formatted;
    }
    
    /**
     * Obtiene el último turno del usuario
     */
    public function getLastUserMessage(): ?string {
        $history = array_reverse($this->getHistory());
        
        foreach ($history as $turn) {
            if ($turn['role'] === 'user') {
                return $turn['content'];
            }
        }
        
        return null;
    }
    
    /**
     * Obtiene el contexto de la conversación (resumen)
     */
    public function getConversationContext(): array {
        $history = $this->getHistory();
        
        // Extraer temas principales
        $topics = [];
        $mentionedClients = [];
        $mentionedActions = [];
        
        foreach ($history as $turn) {
            // Buscar nombres de clientes (simplificado)
            if (preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)+\b/', $turn['content'], $matches)) {
                foreach ($matches[0] as $name) {
                    if (strlen($name) > 5 && !in_array($name, $mentionedClients)) {
                        $mentionedClients[] = $name;
                    }
                }
            }
            
            // Buscar acciones mencionadas
            $actionKeywords = ['buscar', 'enviar', 'calcular', 'actualizar', 'generar', 'cotización'];
            foreach ($actionKeywords as $keyword) {
                if (stripos($turn['content'], $keyword) !== false && !in_array($keyword, $mentionedActions)) {
                    $mentionedActions[] = $keyword;
                }
            }
        }
        
        return [
            'total_turns' => count($history),
            'start_time' => $history[0]['timestamp'] ?? null,
            'last_user_message' => $this->getLastUserMessage(),
            'mentioned_clients' => array_slice($mentionedClients, 0, 5),
            'mentioned_actions' => $mentionedActions,
            'has_confirmed_actions' => $this->hasConfirmedActions()
        ];
    }
    
    /**
     * Verifica si hay acciones confirmadas en el historial
     */
    private function hasConfirmedActions(): bool {
        foreach ($this->getHistory() as $turn) {
            if (($turn['metadata']['type'] ?? '') === 'action_execution' && 
                ($turn['metadata']['result']['ok'] ?? false)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Limpia el historial
     */
    public function clearHistory(): void {
        $_SESSION[$this->sessionKey] = [];
        
        if ($this->persistToDB) {
            $this->clearDBHistory();
        }
    }
    
    /**
     * Guarda turno en BD (implementación básica)
     */
    private function saveTurnToDB(array $turn): void {
        global $mysqli;
        
        if (!$mysqli) return;
        
        try {
            $userId = $_SESSION['Vendedor'] ?? 'unknown';
            $role = $turn['role'];
            $content = $turn['content'];
            $timestamp = $turn['timestamp'];
            $metadata = json_encode($turn['metadata'] ?? [], JSON_UNESCAPED_UNICODE);
            
            $sql = "INSERT INTO IA_Conversation_Log 
                    (id_usuario, role, content, metadata, timestamp) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('sssss', $userId, $role, $content, $metadata, $timestamp);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            error_log("[IA Conversation Store] Error guardando en BD: " . $e->getMessage());
        }
    }
    
    /**
     * Limpia historial en BD
     */
    private function clearDBHistory(): void {
        global $mysqli;
        
        if (!$mysqli) return;
        
        try {
            $userId = $_SESSION['Vendedor'] ?? '';
            if (empty($userId)) return;
            
            $sql = "DELETE FROM IA_Conversation_Log WHERE id_usuario = ?";
            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('s', $userId);
                $stmt->execute();
                $stmt->close();
            }
        } catch (Throwable $e) {
            // Silenciar error de tabla no existente
        }
    }
}