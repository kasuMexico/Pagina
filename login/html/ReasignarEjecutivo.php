<form method="POST" action="php/Funcionalidad_Empleados.php">
    <!-- Eventos / contexto -->
    <div id="Gps"></div>
    <div data-fingerprint-slot></div>
    <?
    //Extraemos el Id de el usuario
    $IdUsuario = $basicas->BuscarCampos($mysqli,'Id','Usuario','IdContact',$Reg['IdContacto']);
    ?>
    <input type="hidden" name="Host"       value="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES) ?>">
    <input type="hidden" name="nombre"     value="<?= htmlspecialchars($nombre ?? '', ENT_QUOTES) ?>">
    <input type="hidden" name="IdVenta"    value="<?= (int)($Reg['Id'] ?? 0) ?>">
    <input type="hidden" name="IdContact"  value="<?= (int)($Reg['IdContacto'] ?? 0) ?>">
    <input type="hidden" name="IdUsuario"  value="<?= (int)($IdUsuario ?? 0) ?>">
    <input type="hidden" name="Producto"   value="<?= htmlspecialchars($Reg['Producto'] ?? '', ENT_QUOTES) ?>">

    <div class="modal-header">
        <h5 class="modal-title">Reasignar Colaborador</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>

    <div class="modal-body">
        <?php if (!function_exists('h')) { function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } } ?>

        <input type="hidden" name="Host" value="<?= h($_SERVER['PHP_SELF']) ?>">
        <input type="hidden" name="name" value="<?= h($name) ?>">
        <input type="hidden" name="IdEmpleado" value="<?= h($Reg['Id'] ?? '') ?>">

        <p>Nombre del Colaborador</p>
        <h4 class="text-center"><strong><?= h($Reg['Nombre'] ?? 'Colaborador') ?></strong></h4>

        <p>Este ejecutivo está asignado a</p>
        <h4 class="text-center">
        <strong>
            <?php
            if (empty($Reg['Equipo'])) {
                echo 'Sistema';
            } else {
                $IdLider = $basicas->BuscarCampos($mysqli,'Id','Empleados','Id',$Reg['Equipo']);
                if ($IdLider) {
                $rs = $mysqli->query('SELECT * FROM Empleados WHERE Id = '.$IdLider.' LIMIT 1');
                if ($dis = $rs->fetch_assoc()) {
                    $Sucur = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$dis['Sucursal']);
                    $Stats = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$dis['Nivel']);
                    echo h($dis['Nombre'].' - '.$Stats.' - '.$Sucur);
                }
                }
            }

            $nivelActual   = (int)($Reg['Nivel'] ?? 0);
            $nivelSuperior = ($nivelActual === 7) ? 4 : max(1, $nivelActual - 1);
            $esDistrib     = ($nivelActual === 7);

            // Sucursal preseleccionada
            $sucActual = 0;
            if (isset($_POST['IdSucursal']))        $sucActual = (int)$_POST['IdSucursal'];
            elseif (!empty($Reg['IdSucursal']))     $sucActual = (int)$Reg['IdSucursal'];
            elseif (!empty($Reg['Sucursal']))       $sucActual = (int)$Reg['Sucursal'];

            // Líder actual para preselección
            $preVend = (int)($Reg['Equipo'] ?? 0);
            ?>
        </strong>
        </h4>

        <?php if ($esDistrib): ?>
        <?php
            // Mapa de coordinadores por sucursal para el filtrado en cliente
            $coorsMap = [];
            $q = $mysqli->query("SELECT Id, Nombre, Sucursal FROM Empleados WHERE Nivel = 4 AND Nombre <> 'Vacante' ORDER BY Nombre");
            while ($r = $q->fetch_assoc()) {
            $sid    = (int)$r['Sucursal'];
            $nomSuc = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$sid);
            $coorsMap[$sid][] = [
                'id'   => (int)$r['Id'],
                'text' => $r['Nombre'].' - Coordinador - '.$nomSuc
            ];
            }
        ?>

        <label>Sucursal</label>
        <select class="form-control" name="IdSucursal" id="selSucursal" required>
            <?php
            $stmt = $mysqli->prepare("SELECT IdSucursal, nombreSucursal FROM Sucursal WHERE Estatus = 1 ORDER BY nombreSucursal");
            $stmt->execute();
            $rs = $stmt->get_result();
            while ($row = $rs->fetch_assoc()):
                $id  = (int)$row['IdSucursal'];
                $nom = $row['nombreSucursal'];
            ?>
            <option value="<?= $id ?>" <?= ($sucActual === $id ? 'selected' : '') ?>><?= h($nom) ?></option>
            <?php endwhile; $stmt->close(); ?>
        </select>
        <br>

        <label>Coordinador</label>
        <select class="form-control" name="NvoSuperior" id="selCoordinador" required>
            <?php
            if (!empty($coorsMap[$sucActual])) {
                foreach ($coorsMap[$sucActual] as $o) {
                $sel = ($o['id'] === $preVend) ? 'selected' : '';
                echo '<option value="'.(int)$o['id'].'" '.$sel.'>'.h($o['text']).'</option>';
                }
            }
            ?>
        </select>

        <script>
            (function(){
            var map     = <?= json_encode($coorsMap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
            var selSuc  = document.getElementById('selSucursal');
            var selCoor = document.getElementById('selCoordinador');
            var preset  = <?= (int)$preVend ?>;

            function fill(){
                var sid  = selSuc.value;
                var list = map[sid] || [];
                selCoor.innerHTML = '';
                list.forEach(function(o){
                var opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = o.text;
                if (o.id === preset) opt.selected = true;
                selCoor.appendChild(opt);
                });
            }

            if (selSuc && selCoor) {
                fill();
                selSuc.addEventListener('change', function(){ preset = 0; fill(); });
            }
            })();
        </script>

        <?php else: ?>
        <label>Selecciona al superior a quién se asignará</label>
        <select class="form-control" name="NvoSuperior" required>
            <?php
            $sql = "SELECT Id, Nombre, Nivel, Sucursal
                    FROM Empleados
                    WHERE Nivel = ? AND Nombre <> 'Vacante'
                    ORDER BY Nombre";
            $st = $mysqli->prepare($sql);
            $st->bind_param('i', $nivelSuperior);
            $st->execute();
            $res = $st->get_result();
            while ($row = $res->fetch_assoc()):
                $nomSuc = $basicas->BuscarCampos($mysqli,'nombreSucursal','Sucursal','Id',$row['Sucursal']);
                $nomNiv = $basicas->BuscarCampos($mysqli,'NombreNivel','Nivel','Id',$row['Nivel']);
            ?>
            <option value="<?= h($row['Id']) ?>"><?= h($row['Nombre'].' - '.$nomNiv.' - '.$nomSuc) ?></option>
            <?php endwhile; $st->close(); ?>
        </select>
        <?php endif; ?>

        <br>
    </div>

    <div class="modal-footer">
        <input type="submit" name="CambiVend" class="btn btn-primary" value="Cambiar el ejecutivo">
    </div>
    </form>
