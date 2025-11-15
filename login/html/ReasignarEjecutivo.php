<?php
/********************************************************************************************
 * Qué hace: Modal "Reasignar Colaborador". Reasigna superior y permite cambiar sucursal.
 * Cambios 10/11/2025:
 *   - Para NO distribuidores: precarga en JS un mapa de superiores por sucursal.
 *   - Si el empleado no tiene sucursal, se autoelige la primera sucursal con opciones.
 ********************************************************************************************/
?>

<form method="POST" action="php/Funcionalidad_Empleados.php">
    <!-- Eventos / contexto -->
    <div id="Gps"></div>
    <div data-fingerprint-slot></div>

    <?php
    if (!function_exists('h')) { function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }

    // Extraemos el Id de Usuario a partir del contacto del registro actual
    $IdUsuario = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Usuario', 'IdContact', $Reg['IdContacto'] ?? 0);
    ?>

    <input type="hidden" name="Host"       value="<?php echo h($_SERVER['PHP_SELF']); ?>">
    <input type="hidden" name="nombre"     value="<?php echo h($nombre ?? ''); ?>">
    <input type="hidden" name="IdVenta"    value="<?php echo (int)($Reg['Id'] ?? 0); ?>">
    <input type="hidden" name="IdContact"  value="<?php echo (int)($Reg['IdContacto'] ?? 0); ?>">
    <input type="hidden" name="IdUsuario"  value="<?php echo (int)$IdUsuario; ?>">
    <input type="hidden" name="Producto"   value="<?php echo h($Reg['Producto'] ?? ''); ?>">

    <div class="modal-header">
        <h5 class="modal-title">Reasignar Colaborador</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>

    <div class="modal-body">
        <?php if (!function_exists('h')) { function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } } ?>

        <!-- Contexto interno para el script que procesa el cambio -->
        <input type="hidden" name="Host" value="<?php echo h($_SERVER['PHP_SELF']); ?>">
        <input type="hidden" name="name" value="<?php echo h($name ?? ''); ?>">
        <input type="hidden" name="IdEmpleado" value="<?php echo h($Reg['Id'] ?? ''); ?>">

        <p>Nombre del Colaborador</p>
        <h4 class="text-center"><strong><?php echo h($Reg['Nombre'] ?? 'Colaborador'); ?></strong></h4>

        <p>Este ejecutivo está asignado a</p>
        <h4 class="text-center">
        <strong>
            <?php
            if (empty($Reg['Equipo'])) {
                echo 'Sistema';
            } else {
                $IdLider = (int)$basicas->BuscarCampos($mysqli, 'Id', 'Empleados', 'Id', (int)($Reg['Equipo'] ?? 0));
                if ($IdLider > 0) {
                    $stmtL = $mysqli->prepare('SELECT Id, Nombre, Sucursal, Nivel FROM Empleados WHERE Id = ? LIMIT 1');
                    $stmtL->bind_param('i', $IdLider);
                    $stmtL->execute();
                    $dis = $stmtL->get_result()->fetch_assoc() ?: null;
                    $stmtL->close();

                    if ($dis) {
                        $Sucur = $basicas->BuscarCampos($mysqli, 'nombreSucursal', 'Sucursal', 'Id', (int)$dis['Sucursal']);
                        $Stats = $basicas->BuscarCampos($mysqli, 'NombreNivel', 'Nivel', 'Id', (int)$dis['Nivel']);
                        echo h(($dis['Nombre'] ?? 'N/D') . ' - ' . $Stats . ' - ' . $Sucur);
                    } else {
                        echo 'Sistema';
                    }
                } else {
                    echo 'Sistema';
                }
            }

            // Jerarquía fija
            $nivelActual   = (int)($Reg['Nivel'] ?? 0);
            $mapSup        = [6 => 4, 4 => 3, 3 => 1];
            $nivelSuperior = ($nivelActual === 7) ? 4 : ($mapSup[$nivelActual] ?? 0);
            $esDistrib     = ($nivelActual === 7);

            // Sucursal preseleccionada
            $sucActual = 0;
            if (isset($_POST['IdSucursal']))        { $sucActual = (int)$_POST['IdSucursal']; }
            elseif (!empty($Reg['IdSucursal']))     { $sucActual = (int)$Reg['IdSucursal']; }
            elseif (!empty($Reg['Sucursal']))       { $sucActual = (int)$Reg['Sucursal']; }

            // Líder actual para preselección
            $preVend = (int)($Reg['Equipo'] ?? 0);
            ?>
        </strong>
        </h4>

        <?php if ($esDistrib): ?>
            <?php
            // Mapa de coordinadores por sucursal
            $coorsMap = [];
            if ($q = $mysqli->query("SELECT Id, Nombre, Sucursal FROM Empleados WHERE Nivel = 4 AND Nombre <> 'Vacante' ORDER BY Nombre")) {
                while ($r = $q->fetch_assoc()) {
                    $sid    = (int)$r['Sucursal'];
                    $nomSuc = $basicas->BuscarCampos($mysqli, 'nombreSucursal', 'Sucursal', 'Id', $sid);
                    $coorsMap[$sid][] = ['id'=>(int)$r['Id'], 'text'=>$r['Nombre'].' - Coordinador - '.$nomSuc];
                }
            }
            ?>

            <label>Sucursal</label>
            <select class="form-control" name="IdSucursal" id="selSucursal" required>
                <?php
                $stmt = $mysqli->prepare("SELECT Id, nombreSucursal FROM Sucursal WHERE Estatus = 1 ORDER BY nombreSucursal");
                $stmt->execute();
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()):
                    $id  = (int)$row['Id'];
                    $nom = (string)$row['nombreSucursal'];
                ?>
                <option value="<?php echo $id; ?>" <?php echo ($sucActual === $id ? 'selected' : ''); ?>><?php echo h($nom); ?></option>
                <?php endwhile; $stmt->close(); ?>
            </select>
            <br>

            <label>Coordinador</label>
            <select class="form-control" name="NvoSuperior" id="selCoordinador" required></select>

            <input type="hidden" name="NvaSucursal" id="NvaSucursal" value="<?php echo (int)$sucActual; ?>">

            <script>
                (function(){
                    var map     = <?php echo json_encode($coorsMap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
                    var selSuc  = document.getElementById('selSucursal');
                    var selCoor = document.getElementById('selCoordinador');
                    var preset  = <?php echo (int)$preVend; ?>;
                    var outSuc  = document.getElementById('NvaSucursal');

                    function firstSucWithOptions(){
                        var keys = Object.keys(map);
                        for (var i=0;i<keys.length;i++){
                            if ((map[keys[i]]||[]).length) return keys[i];
                        }
                        return null;
                    }
                    function fill(){
                        var sid  = selSuc.value || firstSucWithOptions();
                        if (!sid) { selCoor.innerHTML = '<option value="">Sin opciones disponibles</option>'; return; }
                        if (!selSuc.value) selSuc.value = sid;

                        var list = map[sid] || [];
                        selCoor.innerHTML = '';
                        list.forEach(function(o){
                            var opt = document.createElement('option');
                            opt.value = o.id;
                            opt.textContent = o.text;
                            if (o.id === preset) opt.selected = true;
                            selCoor.appendChild(opt);
                        });
                        if (!selCoor.value && list.length) selCoor.value = list[0].id;
                        if (outSuc) outSuc.value = sid;
                    }
                    fill();
                    selSuc.addEventListener('change', function(){ preset = 0; fill(); });
                })();
            </script>

        <?php else: ?>
            <?php
            /* ===========================
             * NUEVO: precargar superiores por sucursal en JS
             *   - Si nivelActual=6 → superiores nivel 4 agrupados por sucursal
             *   - Si nivelActual=4 → superiores nivel 3 (los agrupamos por sucursal para auto-llenado)
             *   - Si nivelActual=3 → superiores nivel 1 (igual)
             * =========================== */
            $nivelObjetivo = ($nivelActual === 6) ? 4 : $nivelSuperior;

            // Sucursales activas
            $sucs = [];
            $sx = $mysqli->query("SELECT Id, nombreSucursal FROM Sucursal WHERE Estatus = 1 ORDER BY nombreSucursal");
            while ($r = $sx->fetch_assoc()) { $sucs[(int)$r['Id']] = (string)$r['nombreSucursal']; }

            // Superiores agrupados por sucursal
            $superMap = [];
            $sqlSup = "SELECT Id, Nombre, Sucursal FROM Empleados WHERE Nivel = ? AND Nombre <> 'Vacante' ORDER BY Nombre";
            $stSup = $mysqli->prepare($sqlSup);
            $stSup->bind_param('i', $nivelObjetivo);
            $stSup->execute();
            $rsSup = $stSup->get_result();
            while ($row = $rsSup->fetch_assoc()) {
                $sid = (int)$row['Sucursal'];
                if ($sid <= 0) continue; // ignora sin sucursal
                if (!isset($superMap[$sid])) $superMap[$sid] = [];
                $nomSuc = $sucs[$sid] ?? '';
                $superMap[$sid][] = [
                    'id'   => (int)$row['Id'],
                    'text' => $row['Nombre'].' - '.($nivelObjetivo===4?'Coordinador':($nivelObjetivo===3?'Gerente':'Director')).' - '.$nomSuc
                ];
            }
            ?>

            <label>Selecciona al superior a quién se asignará</label>
            <select class="form-control" name="NvoSuperior" id="selSuperior" required></select>

            <br>
            <label>Sucursal destino</label>
            <select class="form-control" name="NvaSucursal" id="selNvaSucursal" required>
                <?php foreach ($sucs as $sid => $nom): ?>
                    <option value="<?php echo (int)$sid; ?>" <?php echo ($sucActual === (int)$sid ? 'selected' : ''); ?>>
                        <?php echo h($nom); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <script>
                (function(){
                    var map = <?php echo json_encode($superMap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
                    var selSuc = document.getElementById('selNvaSucursal');
                    var selSup = document.getElementById('selSuperior');
                    var preset = <?php echo (int)$preVend; ?>;

                    function firstSucWithOptions(){
                        var keys = Object.keys(map);
                        for (var i=0;i<keys.length;i++){
                            if ((map[keys[i]]||[]).length) return keys[i];
                        }
                        return null;
                    }
                    function ensureSucursalSelected(){
                        if (!selSuc.value || !(map[selSuc.value]||[]).length) {
                            var sid = firstSucWithOptions();
                            if (sid) selSuc.value = sid;
                        }
                    }
                    function fillSup(){
                        ensureSucursalSelected();
                        var sid = selSuc.value;
                        var list = map[sid] || [];
                        selSup.innerHTML = '';

                        if (!list.length) {
                            var opt = document.createElement('option');
                            opt.value = '';
                            opt.textContent = 'Sin opciones disponibles';
                            selSup.appendChild(opt);
                            return;
                        }
                        list.forEach(function(o){
                            var opt = document.createElement('option');
                            opt.value = o.id;
                            opt.textContent = o.text;
                            if (o.id === preset) opt.selected = true;
                            selSup.appendChild(opt);
                        });
                        if (!selSup.value) selSup.value = list[0].id; // selecciona la primera opción
                    }

                    fillSup();
                    selSuc.addEventListener('change', function(){ preset = 0; fillSup(); });
                })();
            </script>
        <?php endif; ?>

        <br>
    </div>

    <div class="modal-footer">
        <input type="submit" name="CambiVend" class="btn btn-primary" value="Cambiar el ejecutivo">
    </div>
</form>
