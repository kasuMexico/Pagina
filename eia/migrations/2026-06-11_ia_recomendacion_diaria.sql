-- Una sola recomendación IA por usuario y día.
CREATE TABLE IF NOT EXISTS IA_Recomendacion_Diaria (
    Id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    IdUsuario VARCHAR(100) NOT NULL,
    Fecha DATE NOT NULL,
    Nivel INT NOT NULL DEFAULT 0,
    Puesto VARCHAR(100) NOT NULL,
    Alcance VARCHAR(30) NOT NULL,
    Recomendacion MEDIUMTEXT NOT NULL,
    ContextoHash CHAR(64) DEFAULT NULL,
    Modelo VARCHAR(80) DEFAULT NULL,
    Fuente VARCHAR(30) NOT NULL DEFAULT 'openai',
    ErrorMsg VARCHAR(500) DEFAULT NULL,
    FechaCreacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Id),
    UNIQUE KEY uq_ia_recomendacion_usuario_fecha (IdUsuario, Fecha),
    KEY idx_ia_recomendacion_fecha (Fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Recomendación diaria persistente por usuario; máximo una llamada IA al día';
