<?php
/********************************************************************************************
 * form_fondo.php
 * Formulario para registrar valores mensuales del fondo
 * Fecha: 2024-03-20
 * Modificado: 2025-12-07
 ********************************************************************************************/

declare(strict_types=1);

require_once dirname(__DIR__) . '/eia/session.php';
kasu_session_start();
require_once __DIR__ . '/../eia/librerias.php';

if (!isset($_SESSION['Vendedor']) && ($_SESSION['dataP'] ?? '') !== 'ValidJCCM') {
    header('Location: https://kasu.com.mx/login');
    exit;
}

require_once 'FondoInversionManager.php';
require_once 'CalculoFondoFunerario.php';
require_once 'ConfigFondoFunerario.php';

$fondoManager = new FondoInversionManager($mysqli);
$calculadorFondo = new CalculoFondoFunerario($mysqli, $basicas);

// Procesar formulario
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mes = $_POST['mes'] ?? '';
        $valorInicial = (float)($_POST['valor_inicial'] ?? 0);
        $valorFinal = (float)($_POST['valor_final'] ?? 0);
        $rendimientoPorcentaje = (float)($_POST['rendimiento_porcentaje'] ?? 0);
        $rendimientoMonto = (float)($_POST['rendimiento_monto'] ?? 0);
        $aportaciones = (float)($_POST['aportaciones'] ?? 0);
        $retiros = (float)($_POST['retiros'] ?? 0);
        $udiValor = (float)($_POST['udi_valor'] ?? 8.5);
        $comentarios = $_POST['comentarios'] ?? '';
        
        // Validaciones
        if (empty($mes) || $valorInicial <= 0) {
            throw new Exception('Mes y valor inicial son requeridos');
        }
        
        // Verificar si ya existe el mes
        if ($fondoManager->existeRegistroMes($mes)) {
            throw new Exception('Ya existe un registro para el mes ' . $mes);
        }
        
        // Calcular rendimiento basado en porcentaje o monto
        $rendimientoCalculado = 0;
        if ($rendimientoPorcentaje != 0) {
            // Si se ingresa porcentaje, calcular monto
            $rendimientoCalculado = $valorInicial * ($rendimientoPorcentaje / 100);
        } elseif ($rendimientoMonto != 0) {
            // Si se ingresa monto, calcular porcentaje
            $rendimientoCalculado = $rendimientoMonto;
            $rendimientoPorcentaje = ($valorInicial != 0) ? ($rendimientoMonto / $valorInicial) * 100 : 0;
        } else {
            // Si no se especifica, calcular automáticamente
            $rendimientoCalculado = $valorFinal - $valorInicial - $aportaciones + $retiros;
            $rendimientoPorcentaje = ($valorInicial != 0) ? ($rendimientoCalculado / $valorInicial) * 100 : 0;
        }
        
        // Calcular rendimiento anualizado (aproximación mensual * 12)
        $rendimientoAnualizado = $rendimientoPorcentaje * 12;
        
        // Calcular meta de rendimiento mínimo (basado en tasa promedio de productos)
        $metaRendimientoMinimo = $fondoManager->calcularMetaRendimientoMensual();
        
        // Calcular diferencia entre real y meta
        $rendimientoRealVsMeta = ($rendimientoPorcentaje / 100) - $metaRendimientoMinimo;
        
        // Registrar en la base de datos
        $success = $fondoManager->registrarValorMensual(
            $mes,
            $valorInicial,
            $valorFinal,
            $aportaciones,
            $retiros,
            $rendimientoPorcentaje / 100, // Convertir a decimal para BD
            $rendimientoAnualizado / 100, // Convertir a decimal para BD
            $udiValor,
            $metaRendimientoMinimo,
            $rendimientoRealVsMeta,
            $comentarios
        );
        
        if ($success) {
            $mensaje = 'Registro del fondo actualizado exitosamente para ' . $mes . 
                      ' | Rendimiento: ' . number_format($rendimientoPorcentaje, 2) . '%' .
                      ' (' . ($rendimientoCalculado >= 0 ? '+' : '') . '$' . number_format($rendimientoCalculado, 2) . ')';
        } else {
            $error = 'Error al guardar el registro';
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Obtener datos actuales
$ultimoRegistro = $fondoManager->obtenerUltimoRegistro();
$estadisticas = $fondoManager->calcularEstadisticas();
$umbral = $fondoManager->calcularUmbralInversion();
$analisisFondo = $calculadorFondo->analizarVentasActivas();

// Asegurar que tenemos arrays válidos
$umbral = $umbral ?? [
    'total_polizas' => 0,
    'estado' => 'CRÍTICO',
    'cobertura_actual' => 0,
    'aportacion_total' => 0,
    'costo_total_servicios' => 0,
    'brecha_actual' => 0,
    'aportacion_mensual_necesaria_5anios' => 0,
    'anios_para_cerrar_brecha' => 0
];

$estadisticas = $estadisticas ?? [
    'rendimiento_promedio_mensual' => 0,
    'meta_promedio' => 0,
    'diferencia_promedio' => 0,
    'total_meses' => 0
];

// Calcular mes sugerido (próximo mes)
$mesSugerido = date('Y-m');
if ($ultimoRegistro) {
    $ultimoMes = $ultimoRegistro['Mes'];
    $siguienteMes = date('Y-m', strtotime($ultimoMes . ' +1 month'));
    if ($siguienteMes <= date('Y-m')) {
        $mesSugerido = $siguienteMes;
    }
}

// Calcular Aportaciones Automáticas (base de datos de productos)
$aportacionesAutomaticas = $fondoManager->calcularAportacionesMensuales();

// Función para formatear dinero
function format_money($amount): string {
    return '$' . number_format((float)$amount, 2, '.', ',');
}

// Función para formatear número
function format_num($num): string {
    return number_format((float)$num, 0, '.', ',');
}

// Función para formatear porcentaje
function format_percent($percent, $decimals = 2): string {
    return number_format((float)$percent, $decimals) . '%';
}
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro Fondo de Inversión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card {
            border-left: 4px solid;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .stat-card.success { border-color: #2ecc71; }
        .stat-card.warning { border-color: #f39c12; }
        .stat-card.danger { border-color: #e74c3c; }
        .stat-card.info { border-color: #3498db; }
        .progress {
            height: 25px;
            margin-bottom: 10px;
        }
        .progress-bar {
            font-weight: bold;
        }
        .badge-success { background-color: #2ecc71; }
        .badge-warning { background-color: #f39c12; }
        .badge-danger { background-color: #e74c3c; }
        .badge-info { background-color: #3498db; }
        .badge-secondary { background-color: #95a5a6; }
        .tab-content {
            padding: 15px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <!-- Header -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fa fa-chart-line"></i> Registro Mensual del Fondo de Inversión
                        </h4>
                        <small class="opacity-75">Valor inicial = Valor final del mes anterior</small>
                    </div>
                </div>
                
                <!-- Mensajes -->
                <?php if ($mensaje): ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <?= htmlspecialchars($mensaje) ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="row mt-3">
            <!-- Columna izquierda: Formulario -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-edit"></i> Registrar Valores del Mes
                            <?php if ($ultimoRegistro): ?>
                            <small class="text-muted float-right">
                                Valor final anterior: <strong><?= format_money($ultimoRegistro['ValorFinal']) ?></strong>
                            </small>
                            <?php endif; ?>
                        </h5>
                        
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="mes">Mes (YYYY-MM)</label>
                                <input type="month" class="form-control" id="mes" name="mes" 
                                       value="<?= htmlspecialchars($mesSugerido) ?>" required>
                                <small class="form-text text-muted">Ejemplo: 2024-03</small>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="valor_inicial">Valor Inicial del Mes (MXN)</label>
                                    <input type="number" step="0.01" class="form-control" 
                                           id="valor_inicial" name="valor_inicial" 
                                           value="<?= isset($ultimoRegistro['ValorFinal']) ? htmlspecialchars($ultimoRegistro['ValorFinal']) : '0' ?>" required>
                                    <small class="form-text text-muted">Valor al inicio del mes</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="valor_final">Valor Final del Mes (MXN)</label>
                                    <input type="number" step="0.01" class="form-control" 
                                           id="valor_final" name="valor_final" required>
                                    <small class="form-text text-muted">Valor al final del mes</small>
                                </div>
                            </div>
                            
                            <!-- Aportaciones automáticas -->
                            <div class="form-group">
                                <label for="aportaciones">Aportaciones del Mes (MXN)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" 
                                           id="aportaciones" name="aportaciones" 
                                           value="<?= htmlspecialchars($aportacionesAutomaticas['total_mensual']) ?>">
                                    <div class="input-group-append">
                                        <span class="input-group-text bg-info text-white" 
                                              title="Aportación automática calculada de las ventas">
                                            <i class="fa fa-calculator"></i> Auto
                                        </span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Nuevos ingresos al fondo. 
                                    Valor automático: <?= format_money($aportacionesAutomaticas['total_mensual']) ?> 
                                    (<?= format_num($aportacionesAutomaticas['total_polizas']) ?> pólizas)
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="retiros">Retiros del Mes (MXN)</label>
                                <input type="number" step="0.01" class="form-control" 
                                       id="retiros" name="retiros" value="0">
                                <small class="form-text text-muted">Pagos por servicios funerarios</small>
                            </div>
                            
                            <!-- Rendimiento en pestañas -->
                            <ul class="nav nav-tabs" id="rendimientoTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="porcentaje-tab" data-toggle="tab" href="#porcentaje" role="tab">
                                        Porcentaje (%)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="monto-tab" data-toggle="tab" href="#monto" role="tab">
                                        Monto ($)
                                    </a>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="rendimientoTabContent">
                                <div class="tab-pane fade show active" id="porcentaje" role="tabpanel">
                                    <div class="form-group mt-2">
                                        <label for="rendimiento_porcentaje">Rendimiento (% Mensual)</label>
                                        <input type="number" step="0.01" class="form-control" 
                                               id="rendimiento_porcentaje" name="rendimiento_porcentaje" value="0">
                                        <small class="form-text text-muted">
                                            Porcentaje de rendimiento del mes. 
                                            Si es negativo, incluir signo: -0.5
                                        </small>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="monto" role="tabpanel">
                                    <div class="form-group mt-2">
                                        <label for="rendimiento_monto">Rendimiento (MXN Mensual)</label>
                                        <input type="number" step="0.01" class="form-control" 
                                               id="rendimiento_monto" name="rendimiento_monto" value="0">
                                        <small class="form-text text-muted">
                                            Monto ganado o perdido en el mes. 
                                            Si es pérdida, incluir signo: -5000
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="udi_valor">Valor de la UDI</label>
                                <input type="number" step="0.0001" class="form-control" 
                                       id="udi_valor" name="udi_valor" 
                                       value="<?= htmlspecialchars(ConfigFondoFunerario::$UDI_ACTUAL) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="comentarios">Comentarios</label>
                                <textarea class="form-control" id="comentarios" 
                                          name="comentarios" rows="3" placeholder="Ej: Mercado volátil, alto rendimiento bonos, pérdida por caída de bolsa, etc."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-save"></i> Guardar Registro
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Último registro -->
                <?php if ($ultimoRegistro): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Último Registro: <?= htmlspecialchars($ultimoRegistro['Mes']) ?></h6>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Valor Final:</span>
                                <strong><?= format_money($ultimoRegistro['ValorFinal']) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Rendimiento Mensual:</span>
                                <strong><?= format_percent($ultimoRegistro['Rendimiento'] * 100) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Anualizado:</span>
                                <strong><?= format_percent($ultimoRegistro['RendimientoAnualizado'] * 100) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Meta del Mes:</span>
                                <strong><?= format_percent($ultimoRegistro['MetaRendimientoMinimo'] * 100) ?></strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Vs Meta:</span>
                                <?php $diferenciaUltimo = (float)($ultimoRegistro['RendimientoRealVsMeta'] ?? 0); ?>
                                <strong class="<?= $diferenciaUltimo >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= format_percent($diferenciaUltimo * 100) ?>
                                </strong>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Columna derecha: Estadísticas y Umbral -->
            <div class="col-md-6">
                <!-- Umbral de inversión -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-bullseye"></i> Umbral Mínimo de Inversión
                            <small class="float-right">Meta: <?= format_percent($estadisticas['meta_promedio'] * 100) ?> mensual</small>
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stat-card info">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1">Total Pólizas Activas</h6>
                                        <h3 class="card-title"><?= format_num($umbral['total_polizas']) ?></h3>
                                        <p class="card-text small">
                                            Aporte: <?= format_money($aportacionesAutomaticas['total_mensual']) ?>/mes
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php 
                                $estado = $umbral['estado'];
                                $estadoClass = $estado == 'CRÍTICO' ? 'danger' : ($estado == 'MANEJABLE' ? 'warning' : 'success');
                                ?>
                                <div class="stat-card <?= $estadoClass ?>">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-1">Estado Fondo</h6>
                                        <h3 class="card-title"><?= htmlspecialchars($estado) ?></h3>
                                        <p class="card-text small">Cobertura: <?= number_format((float)$umbral['cobertura_actual'] * 100, 1) ?>%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barra de cobertura -->
                        <div class="mt-3">
                            <label>Cobertura del Fondo</label>
                            <?php 
                            $cobertura = (float)$umbral['cobertura_actual'] * 100;
                            $barClass = $cobertura >= 100 ? 'bg-success' : ($cobertura >= 70 ? 'bg-warning' : 'bg-danger');
                            ?>
                            <div class="progress">
                                <div class="progress-bar <?= $barClass ?>" 
                                     role="progressbar" 
                                     style="width: <?= min(100, $cobertura) ?>%"
                                     aria-valuenow="<?= $cobertura ?>"
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= number_format($cobertura, 1) ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?php if ($cobertura >= 100): ?>
                                    ✅ Fondo sobrecumple requerimientos
                                <?php elseif ($cobertura >= 70): ?>
                                    ⚠️ Fondo en nivel aceptable
                                <?php elseif ($cobertura >= 40): ?>
                                    ⚠️ Fondo en nivel crítico - Monitorear
                                <?php else: ?>
                                    ❌ Fondo insuficiente - Acción requerida
                                <?php endif; ?>
                            </small>
                        </div>
                        
                        <!-- Métricas clave -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <small class="text-muted d-block">Aportación Total</small>
                                        <strong><?= format_money($umbral['aportacion_total']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body py-2">
                                        <small class="text-muted d-block">Costo Total Servicios</small>
                                        <strong><?= format_money($umbral['costo_total_servicios']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <?php $brechaPositiva = (float)$umbral['brecha_actual'] > 0; ?>
                                <div class="card <?= $brechaPositiva ? 'bg-danger text-white' : 'bg-success text-white' ?>">
                                    <div class="card-body py-2">
                                        <small class="d-block">Brecha Actual</small>
                                        <strong><?= format_money($umbral['brecha_actual']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body py-2">
                                        <small class="d-block">Aporte Mensual Necesario (5 años)</small>
                                        <strong><?= format_money($umbral['aportacion_mensual_necesaria_5anios']) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recomendación -->
                        <div class="mt-3 alert 
                            <?= $estado == 'CRÍTICO' ? 'alert-danger' : 
                               ($estado == 'MANEJABLE' ? 'alert-warning' : 'alert-success') ?>">
                            <h6>Recomendación:</h6>
                            <?php if ($estado == 'CRÍTICO'): ?>
                                <p class="mb-0">
                                    <strong>Acción inmediata requerida:</strong> 
                                    El fondo necesita aportaciones adicionales de 
                                    <?= format_money($umbral['aportacion_mensual_necesaria_5anios']) ?> 
                                    mensuales para alcanzar cobertura en 5 años.
                                </p>
                            <?php elseif ($estado == 'MANEJABLE'): ?>
                                <p class="mb-0">
                                    <strong>Monitoreo cercano:</strong> 
                                    Mantener aportaciones y buscar mejorar rendimiento.
                                    Necesitas <?= number_format((float)$umbral['anios_para_cerrar_brecha'], 1) ?> años 
                                    para cerrar brecha al ritmo actual.
                                </p>
                            <?php else: ?>
                                <p class="mb-0">
                                    <strong>En buen camino:</strong> 
                                    El fondo está sobrecumpliendo. Puedes considerar 
                                    aumentar el % de excedente para equipo o reducir riesgo.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Rendimiento requerido vs real -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fa fa-chart-bar"></i> Rendimiento: Requerido vs Real
                        </h5>
                        
                        <?php if ($estadisticas['total_meses'] > 0): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6>Promedio Mensual</h6>
                                    <?php 
                                    $rendReal = (float)$estadisticas['rendimiento_promedio_mensual'];
                                    $metaReal = (float)$estadisticas['meta_promedio'];
                                    ?>
                                    <div class="display-4 <?= $rendReal >= $metaReal ? 'text-success' : 'text-danger' ?>">
                                        <?= format_percent($rendReal * 100) ?>
                                    </div>
                                    <small class="text-muted">Real</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <h6>Meta Mensual</h6>
                                    <div class="display-4 text-info">
                                        <?= format_percent($metaReal * 100) ?>
                                    </div>
                                    <small class="text-muted">Requerido</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>Diferencia Promedio:</h6>
                            <?php 
                            $diferencia = (float)$estadisticas['diferencia_promedio'];
                            $diffClass = $diferencia >= 0 ? 'bg-success' : 'bg-danger';
                            $width = min(100, abs($diferencia) * 1000);
                            ?>
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar <?= $diffClass ?>" 
                                     role="progressbar" 
                                     style="width: <?= $width ?>%;"
                                     aria-valuenow="<?= $diferencia * 100 ?>"
                                     aria-valuemin="-20" 
                                     aria-valuemax="20">
                                    <?= format_percent($diferencia * 100) ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                Positivo = Supera meta | Negativo = No alcanza meta
                            </small>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fa fa-chart-line fa-3x mb-3"></i>
                            <p>No hay datos de rendimiento histórico disponibles</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabla de historial -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fa fa-history"></i> Historial del Fondo (Últimos 12 meses)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Valor Inicial</th>
                                        <th>Valor Final</th>
                                        <th>Aportaciones</th>
                                        <th>Retiros</th>
                                        <th>Rend. Mensual</th>
                                        <th>Rend. Anual</th>
                                        <th>Meta</th>
                                        <th>Diferencia</th>
                                        <th>UDI</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    try {
                                        $historial = $fondoManager->obtenerHistorial(12);
                                        if (!empty($historial)) {
                                            foreach ($historial as $registro): 
                                                $diferencia = (float)($registro['RendimientoRealVsMeta'] ?? 0);
                                                $estadoClass = $diferencia >= 0.002 ? 'success' : ($diferencia >= -0.002 ? 'warning' : 'danger');
                                                $estadoText = $diferencia >= 0.002 ? '✅ Excelente' : ($diferencia >= -0.002 ? '⚠️ Aceptable' : '❌ Bajo');
                                                // Calcular rendimiento en monto
                                                $rendimientoMonto = $registro['ValorInicial'] * $registro['Rendimiento'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($registro['Mes']) ?></td>
                                        <td><?= format_money($registro['ValorInicial']) ?></td>
                                        <td><strong><?= format_money($registro['ValorFinal']) ?></strong></td>
                                        <td><?= format_money($registro['Aportaciones']) ?></td>
                                        <td><?= format_money($registro['Retiros']) ?></td>
                                        <td>
                                            <?= format_percent($registro['Rendimiento'] * 100) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= $rendimientoMonto >= 0 ? '+' : '' ?><?= format_money($rendimientoMonto) ?>
                                            </small>
                                        </td>
                                        <td><?= format_percent($registro['RendimientoAnualizado'] * 100) ?></td>
                                        <td><?= format_percent($registro['MetaRendimientoMinimo'] * 100) ?></td>
                                        <td class="<?= $diferencia >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= format_percent($diferencia * 100) ?>
                                        </td>
                                        <td>$<?= number_format((float)$registro['UDI_Valor'], 4) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $estadoClass ?>">
                                                <?= $estadoText ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; 
                                        } else {
                                    ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-muted py-4">
                                            <i class="fa fa-database fa-3x mb-3"></i>
                                            <p>No hay registros históricos disponibles</p>
                                            <small>Registra el primer valor del fondo para comenzar</small>
                                        </td>
                                    </tr>
                                    <?php } 
                                    } catch (Exception $e) { ?>
                                    <tr>
                                        <td colspan="11" class="text-center text-danger py-4">
                                            <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                                            <p>Error al cargar el historial</p>
                                            <small><?= htmlspecialchars($e->getMessage()) ?></small>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-calcular rendimiento si se cambian los valores
            function calcularRendimiento() {
                var inicial = parseFloat($('#valor_inicial').val()) || 0;
                var final = parseFloat($('#valor_final').val()) || 0;
                var aportaciones = parseFloat($('#aportaciones').val()) || 0;
                var retiros = parseFloat($('#retiros').val()) || 0;
                
                var porcentajeInput = $('#rendimiento_porcentaje');
                var montoInput = $('#rendimiento_monto');
                
                if (inicial > 0) {
                    // Calcular monto y porcentaje
                    var rendimientoMonto = final - inicial - aportaciones + retiros;
                    var rendimientoPorcentaje = (rendimientoMonto / inicial) * 100;
                    
                    // Actualizar campos si están en 0
                    if (porcentajeInput.val() == 0 && montoInput.val() == 0) {
                        montoInput.val(rendimientoMonto.toFixed(2));
                        porcentajeInput.val(rendimientoPorcentaje.toFixed(4));
                    }
                }
            }
            
            // Calcular al cambiar valores
            $('#valor_final, #aportaciones, #retiros').on('change', calcularRendimiento);
            
            // Sincronizar porcentaje y monto
            $('#rendimiento_porcentaje').on('input', function() {
                var porcentaje = parseFloat($(this).val()) || 0;
                var inicial = parseFloat($('#valor_inicial').val()) || 0;
                var monto = inicial * (porcentaje / 100);
                $('#rendimiento_monto').val(monto.toFixed(2));
            });
            
            $('#rendimiento_monto').on('input', function() {
                var monto = parseFloat($(this).val()) || 0;
                var inicial = parseFloat($('#valor_inicial').val()) || 0;
                var porcentaje = (inicial != 0) ? (monto / inicial) * 100 : 0;
                $('#rendimiento_porcentaje').val(porcentaje.toFixed(4));
            });
            
            // Manejar pestañas
            $('#rendimientoTab a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
            
            // Validación del formulario
            $('form').on('submit', function(e) {
                var mes = $('#mes').val();
                var inicial = $('#valor_inicial').val();
                var final = $('#valor_final').val();
                
                if (!mes || mes.length !== 7) {
                    alert('Ingrese un mes válido en formato YYYY-MM');
                    e.preventDefault();
                    return false;
                }
                
                if (parseFloat(inicial) <= 0) {
                    alert('El valor inicial debe ser mayor a 0');
                    e.preventDefault();
                    return false;
                }
                
                if (parseFloat(final) <= 0) {
                    alert('El valor final debe ser mayor a 0');
                    e.preventDefault();
                    return false;
                }
                
                // Confirmación si hay pérdida
                var rendimientoPorcentaje = parseFloat($('#rendimiento_porcentaje').val()) || 0;
                var rendimientoMonto = parseFloat($('#rendimiento_monto').val()) || 0;
                
                if (rendimientoPorcentaje < 0 || rendimientoMonto < 0) {
                    return confirm('⚠️ Se está registrando una PÉRDIDA en el fondo. ¿Continuar?');
                }
            });
        });
    </script>
</body>
</html>