<?php
declare(strict_types=1);

/**
 * Perfiles funcionales usados por la IA para adaptar recomendaciones y alcance.
 */

if (!function_exists('kasu_ia_role_profile')) {
    function kasu_ia_role_profile(string $nombreNivel, int $nivel = 0): array
    {
        $name = mb_strtolower(trim($nombreNivel), 'UTF-8');
        $aliases = [
            'gerente de ruta' => 'gerente ruta',
            'ejecutivo de ventas' => 'ejecutivo ventas',
            'ejecutivo de cobranza' => 'ejecutivo cobranza',
        ];
        $name = $aliases[$name] ?? $name;
        $profiles = [
            'ceo' => [
                'key' => 'director_general',
                'titulo' => 'Director General',
                'alcance' => 'empresa',
                'descripcion' => 'Responsable de la dirección integral, prioridades y resultados totales de KASU.',
                'enfoques' => ['resultados globales', 'riesgos críticos', 'prioridades entre áreas', 'decisiones y responsables'],
                'acciones' => ['definir prioridades', 'pedir responsables y fechas', 'destrabar áreas', 'vigilar tendencias globales'],
                'evitar' => ['asignarle llamadas personales', 'tratarlo como vendedor individual'],
            ],
            'director general' => [
                'key' => 'director_general',
                'titulo' => 'Director General',
                'alcance' => 'empresa',
                'descripcion' => 'Responsable de la dirección integral, prioridades y resultados totales de KASU.',
                'enfoques' => ['resultados globales', 'riesgos críticos', 'prioridades entre áreas', 'decisiones y responsables'],
                'acciones' => ['definir prioridades', 'pedir responsables y fechas', 'destrabar áreas', 'vigilar tendencias globales'],
                'evitar' => ['asignarle llamadas personales', 'tratarlo como vendedor individual'],
            ],
            'director de finanzas' => [
                'key' => 'director_finanzas',
                'titulo' => 'Director de Finanzas',
                'alcance' => 'empresa',
                'descripcion' => 'Responsable de liquidez, cobranza, conciliación, riesgo y disciplina financiera.',
                'enfoques' => ['cobranza global', 'mora y saldos pendientes', 'conciliaciones', 'riesgo financiero'],
                'acciones' => ['priorizar recuperación', 'revisar desviaciones', 'asignar responsables financieros', 'escalar riesgos'],
                'evitar' => ['pedir prospección o cierres personales', 'recomendar tareas de vendedor'],
            ],
            'director de marketing' => [
                'key' => 'director_marketing',
                'titulo' => 'Director de Marketing',
                'alcance' => 'empresa',
                'descripcion' => 'Responsable de generación, calidad, distribución y avance de prospectos.',
                'enfoques' => ['volumen y calidad de prospectos', 'conversión por etapa', 'velocidad de atención', 'carga por ejecutivo'],
                'acciones' => ['redistribuir prospectos', 'corregir cuellos de botella', 'priorizar campañas', 'medir conversión'],
                'evitar' => ['dar consejos financieros', 'tratarlo como ejecutivo de ventas individual'],
            ],
            'director comercial' => [
                'key' => 'director_comercial',
                'titulo' => 'Director Comercial',
                'alcance' => 'empresa',
                'descripcion' => 'Responsable de estrategia comercial, conversión, productividad y cumplimiento de ventas.',
                'enfoques' => ['ventas globales', 'pipeline comercial', 'conversión', 'desempeño de gerencias y equipos'],
                'acciones' => ['priorizar cierres', 'corregir equipos rezagados', 'definir metas comerciales', 'destrabar oportunidades'],
                'evitar' => ['asignarle tareas operativas personales', 'dar recomendaciones financieras fuera de su función'],
            ],
            'mesa de control' => [
                'key' => 'mesa_control',
                'titulo' => 'Mesa de Control',
                'alcance' => 'empresa',
                'descripcion' => 'Unidad central que detecta excepciones, coordina áreas y da seguimiento a acuerdos.',
                'enfoques' => ['pendientes críticos', 'cuellos de botella', 'calidad de información', 'acuerdos sin seguimiento'],
                'acciones' => ['identificar excepciones', 'asignar seguimiento', 'pedir evidencia', 'escalar bloqueos'],
                'evitar' => ['tratarla como vendedor', 'enfocar la recomendación en ventas personales'],
            ],
            'mesa control' => [
                'key' => 'mesa_control',
                'titulo' => 'Mesa de Control',
                'alcance' => 'empresa',
                'descripcion' => 'Unidad central que detecta excepciones, coordina áreas y da seguimiento a acuerdos.',
                'enfoques' => ['pendientes críticos', 'cuellos de botella', 'calidad de información', 'acuerdos sin seguimiento'],
                'acciones' => ['identificar excepciones', 'asignar seguimiento', 'pedir evidencia', 'escalar bloqueos'],
                'evitar' => ['tratarla como vendedor', 'enfocar la recomendación en ventas personales'],
            ],
            'gerente ruta' => [
                'key' => 'gerente',
                'titulo' => 'Gerente de Ruta',
                'alcance' => 'equipo',
                'descripcion' => 'Responsable del resultado completo de su sucursal y del desempeño de sus coordinadores.',
                'enfoques' => ['resultado de sucursal', 'equipos rezagados', 'ventas y cobranza', 'ejecución diaria'],
                'acciones' => ['revisar coordinadores', 'redistribuir cargas', 'definir compromisos diarios', 'escalar bloqueos'],
                'evitar' => ['limitarse a su cartera personal'],
            ],
            'coordinador' => [
                'key' => 'coordinador',
                'titulo' => 'Coordinador',
                'alcance' => 'equipo',
                'descripcion' => 'Responsable de organizar y acompañar la ejecución diaria de sus ejecutivos.',
                'enfoques' => ['actividad diaria del equipo', 'seguimientos vencidos', 'pipeline por ejecutivo', 'coaching'],
                'acciones' => ['asignar seguimientos', 'acompañar cierres', 'corregir inactividad', 'reportar bloqueos al gerente'],
                'evitar' => ['darle una visión exclusivamente global'],
            ],
            'jefe de marketing' => [
                'key' => 'jefe_marketing',
                'titulo' => 'Jefe de Marketing',
                'alcance' => 'equipo',
                'descripcion' => 'Responsable de distribuir prospectos y asegurar su atención por el equipo de Marketing.',
                'enfoques' => ['prospectos sin asignar', 'tiempo de respuesta', 'carga por ejecutivo', 'avance por etapa'],
                'acciones' => ['reasignar prospectos', 'activar seguimientos', 'equilibrar cargas', 'reportar campañas débiles'],
                'evitar' => ['darle tareas financieras o de cobranza'],
            ],
            'ejecutivo de marketing' => [
                'key' => 'ejecutivo_marketing',
                'titulo' => 'Ejecutivo de Marketing',
                'alcance' => 'personal',
                'descripcion' => 'Responsable de contactar, calificar y avanzar los prospectos que tiene asignados.',
                'enfoques' => ['prospectos asignados', 'primer contacto', 'seguimientos', 'calificación y citas'],
                'acciones' => ['contactar prospectos prioritarios', 'registrar seguimiento', 'agendar citas', 'actualizar etapas'],
                'evitar' => ['recomendar decisiones directivas o financieras'],
            ],
            'ejecutivo cobranza' => [
                'key' => 'ejecutivo_cobranza',
                'titulo' => 'Ejecutivo de Cobranza',
                'alcance' => 'personal',
                'descripcion' => 'Responsable de recuperar pagos y dar seguimiento a promesas de pago.',
                'enfoques' => ['mora', 'promesas vencidas', 'cuentas prioritarias', 'recuperación diaria'],
                'acciones' => ['contactar cuentas vencidas', 'obtener compromisos', 'registrar promesas', 'escalar casos de riesgo'],
                'evitar' => ['pedir generación de prospectos o ventas nuevas'],
            ],
            'ejecutivo ventas' => [
                'key' => 'ejecutivo_ventas',
                'titulo' => 'Ejecutivo de Ventas',
                'alcance' => 'personal',
                'descripcion' => 'Responsable de prospectar, presentar, dar seguimiento y cerrar ventas.',
                'enfoques' => ['prospectos personales', 'citas', 'seguimientos', 'cierres y meta'],
                'acciones' => ['contactar oportunidades', 'agendar citas', 'resolver objeciones', 'cerrar ventas'],
                'evitar' => ['darle decisiones de dirección o tareas de otros equipos'],
            ],
            'agente externo' => [
                'key' => 'agente_externo',
                'titulo' => 'Agente Externo',
                'alcance' => 'personal',
                'descripcion' => 'Responsable de generar oportunidades y cerrar ventas en campo.',
                'enfoques' => ['actividad en campo', 'referidos', 'citas', 'cierres'],
                'acciones' => ['visitar contactos', 'pedir referidos', 'agendar citas', 'cerrar oportunidades'],
                'evitar' => ['asignarle tareas administrativas internas'],
            ],
        ];

        $profile = $profiles[$name] ?? [
            'key' => 'colaborador',
            'titulo' => $nombreNivel !== '' ? $nombreNivel : 'Colaborador',
            'alcance' => 'personal',
            'descripcion' => 'Colaborador KASU con responsabilidades definidas por su puesto.',
            'enfoques' => ['pendientes de su función', 'calidad de ejecución', 'seguimiento'],
            'acciones' => ['priorizar pendientes', 'registrar avances', 'escalar bloqueos'],
            'evitar' => ['recomendar tareas fuera de su función'],
        ];
        $profile['nivel'] = $nivel;
        $profile['nombre'] = $profile['titulo'];
        return $profile;
    }

    function kasu_ia_role_instruction(array $profile): string
    {
        return sprintf(
            'Trata al usuario como %s. Su alcance es %s. Enfoca el consejo en: %s. '
            . 'Recomienda acciones propias de su puesto: %s. Evita: %s.',
            (string)($profile['titulo'] ?? 'Colaborador'),
            (string)($profile['alcance'] ?? 'personal'),
            implode(', ', $profile['enfoques'] ?? []),
            implode(', ', $profile['acciones'] ?? []),
            implode(', ', $profile['evitar'] ?? [])
        );
    }

    /**
     * Devuelve usuarios e IDs de empleados visibles para recomendaciones.
     *
     * @return array{user_ids:array<int,string>,employee_ids:array<int,int>}
     */
    function kasu_ia_employee_scope(mysqli $mysqli, string $idUsuario, array $profile): array
    {
        $employees = [];
        $children = [];
        $currentId = 0;
        $currentBranch = 0;
        $branches = [];
        $roleNames = [];
        $stmt = $mysqli->prepare(
            "SELECT e.Id, e.IdUsuario, e.Equipo, e.Sucursal, COALESCE(n.NombreNivel, '') NombreNivel
             FROM Empleados e
             LEFT JOIN Nivel n ON n.Id = e.Nivel
             WHERE e.Nombre <> 'Vacante'"
        );
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $id = (int)$row['Id'];
            $employees[$id] = (string)$row['IdUsuario'];
            $branches[$id] = (int)($row['Sucursal'] ?? 0);
            $roleNames[$id] = (string)($row['NombreNivel'] ?? '');
            $children[(int)($row['Equipo'] ?? 0)][] = $id;
            if (strcasecmp((string)$row['IdUsuario'], $idUsuario) === 0) {
                $currentId = $id;
                $currentBranch = (int)($row['Sucursal'] ?? 0);
            }
        }
        $stmt->close();

        $ids = [];
        if (($profile['alcance'] ?? 'personal') === 'empresa') {
            $ids = array_keys($employees);
        } elseif (($profile['key'] ?? '') === 'gerente' && $currentBranch > 0) {
            foreach ($branches as $id => $branchId) {
                $candidateProfile = kasu_ia_role_profile($roleNames[$id] ?? '', 0);
                if ($branchId === $currentBranch && ($candidateProfile['alcance'] ?? '') !== 'empresa') {
                    $ids[] = (int)$id;
                }
            }
        } elseif (($profile['alcance'] ?? 'personal') === 'equipo' && $currentId > 0) {
            $stack = [$currentId];
            while ($stack) {
                $id = (int)array_pop($stack);
                if ($id <= 0 || isset($ids[$id])) {
                    continue;
                }
                $ids[$id] = $id;
                foreach ($children[$id] ?? [] as $childId) {
                    $stack[] = (int)$childId;
                }
            }
            $ids = array_values($ids);
        } elseif ($currentId > 0) {
            $ids = [$currentId];
        }

        $users = [];
        foreach ($ids as $id) {
            if (!empty($employees[$id])) {
                $users[] = $employees[$id];
            }
        }
        return ['user_ids' => array_values(array_unique($users)), 'employee_ids' => array_values(array_unique($ids))];
    }

    function kasu_ia_sql_string_list(mysqli $mysqli, array $values): string
    {
        $values = array_values(array_unique(array_filter(array_map('strval', $values))));
        if (!$values) {
            return "''";
        }
        return implode(',', array_map(static fn(string $v): string => "'" . $mysqli->real_escape_string($v) . "'", $values));
    }

    function kasu_ia_sql_int_list(array $values): string
    {
        $values = array_values(array_unique(array_filter(array_map('intval', $values), static fn(int $v): bool => $v > 0)));
        return $values ? implode(',', $values) : '0';
    }
}
