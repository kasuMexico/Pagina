-- =============================================================================
-- API Market KASU — Migración: tabla api_keys (Fase 3, Paso 2 / HIGH-04)
-- -----------------------------------------------------------------------------
-- Almacena claves X-API-Key de alta entropía SOLO como hash (sha256).
-- La clave en claro se entrega una única vez al cliente por canal seguro.
-- Fecha: 2026-08-14
-- =============================================================================

CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    api_user VARCHAR(50) NOT NULL,
    key_hash CHAR(64) NOT NULL,
    label VARCHAR(120) DEFAULT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    expires_at DATETIME NULL DEFAULT NULL,
    last_used_at DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_api_keys_hash (key_hash),
    KEY idx_api_keys_user (api_user),
    KEY idx_api_keys_enabled (enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Aprovisionamiento de una clave para un usuario existente.
-- La clave en claro NO se guarda; solo su hash sha256. Genera la clave del lado
-- seguro (PHP/CLI) y captura el hash para insertarlo:
--
--   $plain = bin2hex(random_bytes(32));   // 64 hex -> entregar una sola vez
--   $hash  = hash('sha256', $plain);      // lo que se guarda
--
--   INSERT INTO api_keys (api_user, key_hash, label, enabled)
--   VALUES ('TU_USUARIO_API', '<hash_sha256>', 'Produccion', 1);
--
-- Revocación (deshabilita sin borrar el registro):
--   UPDATE api_keys SET enabled = 0 WHERE api_user = 'TU_USUARIO_API';
-- -----------------------------------------------------------------------------
