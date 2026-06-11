-- Homologa puestos directivos y permisos administrativos.
-- Nivel 2 se conserva como Mesa de Control y no recibe acceso financiero.

UPDATE Nivel
SET NombreNivel = 'Mesa de Control'
WHERE Id = 2;

INSERT INTO Nivel (NombreNivel)
SELECT 'Director General'
WHERE NOT EXISTS (SELECT 1 FROM Nivel WHERE NombreNivel = 'Director General');

INSERT INTO Nivel (NombreNivel)
SELECT 'Director de Finanzas'
WHERE NOT EXISTS (SELECT 1 FROM Nivel WHERE NombreNivel = 'Director de Finanzas');

INSERT INTO Nivel (NombreNivel)
SELECT 'Director de Marketing'
WHERE NOT EXISTS (SELECT 1 FROM Nivel WHERE NombreNivel = 'Director de Marketing');

INSERT INTO Nivel (NombreNivel)
SELECT 'Director Comercial'
WHERE NOT EXISTS (SELECT 1 FROM Nivel WHERE NombreNivel = 'Director Comercial');

INSERT INTO Nivel (NombreNivel)
SELECT 'Jefe de Marketing'
WHERE NOT EXISTS (SELECT 1 FROM Nivel WHERE NombreNivel = 'Jefe de Marketing');

INSERT INTO Nivel (NombreNivel)
SELECT 'Ejecutivo de Marketing'
WHERE NOT EXISTS (SELECT 1 FROM Nivel WHERE NombreNivel = 'Ejecutivo de Marketing');

CREATE TABLE IF NOT EXISTS Nivel_Permisos (
    Nivel INT NOT NULL,
    Permiso VARCHAR(80) NOT NULL,
    Permitido TINYINT(1) NOT NULL DEFAULT 1,
    FechaActualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (Nivel, Permiso),
    KEY idx_nivel_permisos_permiso (Permiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
COMMENT='Permisos administrativos autorizados por puesto';

-- La migración administra únicamente estos permisos.
DELETE FROM Nivel_Permisos
WHERE Permiso IN (
    'finance',
    'marketing',
    'commercial',
    'employees.manage',
    'api_market'
);

-- CEO heredado y Director General: visión global.
INSERT INTO Nivel_Permisos (Nivel, Permiso, Permitido)
SELECT Id, permisos.Permiso, 1
FROM Nivel
CROSS JOIN (
    SELECT 'finance' AS Permiso
    UNION ALL SELECT 'marketing'
    UNION ALL SELECT 'commercial'
    UNION ALL SELECT 'employees.manage'
    UNION ALL SELECT 'api_market'
) permisos
WHERE NombreNivel IN ('CEO', 'Director General');

-- Dirección de Finanzas: información y operación financiera.
INSERT INTO Nivel_Permisos (Nivel, Permiso, Permitido)
SELECT Id, 'finance', 1
FROM Nivel
WHERE NombreNivel = 'Director de Finanzas';

-- Dirección de Marketing: marketing y gestión comercial de prospectos.
INSERT INTO Nivel_Permisos (Nivel, Permiso, Permitido)
SELECT Id, permisos.Permiso, 1
FROM Nivel
CROSS JOIN (
    SELECT 'marketing' AS Permiso
    UNION ALL SELECT 'commercial'
) permisos
WHERE NombreNivel = 'Director de Marketing';

-- Dirección Comercial: clientes, ventas y prospectos.
INSERT INTO Nivel_Permisos (Nivel, Permiso, Permitido)
SELECT Id, 'commercial', 1
FROM Nivel
WHERE NombreNivel = 'Director Comercial';

-- Mesa de Control conserva su función administrativa central, sin Finanzas.
INSERT INTO Nivel_Permisos (Nivel, Permiso, Permitido)
SELECT Id, permisos.Permiso, 1
FROM Nivel
CROSS JOIN (
    SELECT 'marketing' AS Permiso
    UNION ALL SELECT 'commercial'
    UNION ALL SELECT 'employees.manage'
    UNION ALL SELECT 'api_market'
) permisos
WHERE Id = 2;

-- Gerencia de Ruta conserva alcance comercial.
INSERT INTO Nivel_Permisos (Nivel, Permiso, Permitido)
SELECT Id, 'commercial', 1
FROM Nivel
WHERE Id = 3;
