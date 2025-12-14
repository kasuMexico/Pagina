<?php
declare(strict_types=1);

/**
 * Módulo de Portafolio Automático (PHP 8.2)
 * - Registro de operaciones (port_trades)
 * - Posiciones actuales con P&L (port_prices_daily)
 * - Dividendos acumulados (port_dividends)
 * - Estado de sincronización (precios/dividendos por símbolo)
 *
 * Tablas asumidas (ya existentes):
 *   port_trades, port_prices_daily, port_dividends, port_symbol_map
 */

$projectRoot = dirname(__DIR__, 3);
require_once $projectRoot . '/eia/session.php';
kasu_session_start();
require_once $projectRoot . '/eia/librerias.php'; // Debe exponer $mysqli

// Acceso
if (!isset($_SESSION['Vendedor']) && ($_SESSION['dataP'] ?? '') !== 'ValidJCCM') {
    header('Location: https://kasu.com.mx/login');
    exit;
}

// MySQLi robusto
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->set_charset('utf8mb4');

// Helpers
if (!function_exists('h')) {
    function h($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
function money_mx(float $n): string {
    return '$' . number_format($n, 2, '.', ',');
}

// CSRF simple
if (empty($_SESSION['csrf_port'])) {
    $_SESSION['csrf_port'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['csrf_port'];

$mensaje = '';
$error = '';

// Utilidad: acciones disponibles por símbolo
function acciones_disponibles(mysqli $db, string $symbol): float {
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(CASE WHEN side='BUY' THEN qty ELSE -qty END), 0) AS shares
        FROM port_trades
        WHERE symbol = ?
    ");
    $stmt->bind_param('s', $symbol);
    $stmt->execute();
    $stmt->bind_result($shares);
    $stmt->fetch();
    $stmt->close();
    return (float)$shares;
}

// Manejo de POST (registro o borrado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenPost = $_POST['csrf_port'] ?? '';
    if (!hash_equals($csrfToken, $tokenPost)) {
        $error = 'Token CSRF inválido. Recarga la página.';
    } else {
        // Eliminar operación
        if (isset($_POST['delete_id'])) {
            $deleteId = (int)($_POST['delete_id'] ?? 0);
            if ($deleteId > 0) {
                $stmt = $mysqli->prepare("DELETE FROM port_trades WHERE id = ?");
                $stmt->bind_param('i', $deleteId);
                $stmt->execute();
                $stmt->close();
                $mensaje = 'Operación eliminada correctamente.';
            } else {
                $error = 'ID inválido para eliminar.';
            }
        } else {
            // Registrar operación
            $symbol = strtoupper(trim((string)($_POST['symbol'] ?? '')));
            $tradeDate = trim((string)($_POST['trade_date'] ?? ''));
            $side = strtoupper(trim((string)($_POST['side'] ?? '')));
            $qty = (float)($_POST['qty'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $fee = isset($_POST['fee']) ? (float)$_POST['fee'] : 0.0;
            $currency = trim((string)($_POST['currency'] ?? ''));
            $notes = trim((string)($_POST['notes'] ?? ''));

            // Validaciones
            if ($symbol === '' || !preg_match('/^[A-Z0-9.\-]{1,32}$/', $symbol)) {
                $error = 'Símbolo inválido (usa A-Z, 0-9, puntos o guiones).';
            } elseif (!$tradeDate || !DateTime::createFromFormat('Y-m-d', $tradeDate)) {
                $error = 'Fecha inválida.';
            } elseif (!in_array($side, ['BUY', 'SELL'], true)) {
                $error = 'Side inválido (BUY o SELL).';
            } elseif ($qty <= 0) {
                $error = 'Cantidad debe ser mayor a 0.';
            } elseif ($price < 0) {
                $error = 'Precio no puede ser negativo.';
            } elseif ($fee < 0) {
                $error = 'Fee no puede ser negativo.';
            } else {
                // Validar disponibilidad para SELL
                if ($side === 'SELL') {
                    $disp = acciones_disponibles($mysqli, $symbol);
                    if ($qty > $disp + 1e-9) {
                        $error = 'No puedes vender más acciones de las disponibles (' . $disp . ').';
                    }
                }
            }

            if ($error === '') {
                $stmt = $mysqli->prepare("
                    INSERT INTO port_trades (symbol, trade_date, side, qty, price, fee, currency, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param(
                    'sssdddss',
                    $symbol,
                    $tradeDate,
                    $side,
                    $qty,
                    $price,
                    $fee,
                    $currency,
                    $notes
                );
                $stmt->execute();
                $stmt->close();
                $mensaje = 'Operación registrada correctamente.';
            }
        }
    }
}

// Listado de operaciones recientes
$trades = [];
$stmt = $mysqli->prepare("
    SELECT id, symbol, trade_date, side, qty, price, fee, currency, notes
    FROM port_trades
    ORDER BY trade_date DESC, id DESC
    LIMIT 50
");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $trades[] = $row;
}
$stmt->close();

// Mapeo opcional de símbolos para UI (Yahoo)
$symbolMap = [];
$resMap = $mysqli->query("SELECT symbol_input, symbol_yahoo FROM port_symbol_map");
while ($r = $resMap->fetch_assoc()) {
    $symbolMap[$r['symbol_input']] = $r['symbol_yahoo'];
}

// Últimos precios por símbolo
$lastPrices = [];
$resPrice = $mysqli->query("
    SELECT p.symbol, p.close, p.price_date
    FROM port_prices_daily p
    INNER JOIN (
        SELECT symbol, MAX(price_date) AS max_date
        FROM port_prices_daily
        GROUP BY symbol
    ) m ON p.symbol = m.symbol AND p.price_date = m.max_date
");
while ($p = $resPrice->fetch_assoc()) {
    $lastPrices[$p['symbol']] = [
        'price' => (float)$p['close'],
        'date' => $p['price_date'],
    ];
}

// Posiciones actuales
$positions = [];
$totalCostBasis = 0.0;
$totalMarketValue = 0.0;
$totalUnrealized = 0.0;

$resPos = $mysqli->query("
    SELECT 
        symbol,
        SUM(CASE WHEN side='BUY' THEN qty ELSE -qty END) AS shares,
        SUM(CASE WHEN side='BUY' THEN qty ELSE 0 END) AS total_buy_qty,
        SUM(CASE WHEN side='BUY' THEN (qty * price) + fee ELSE 0 END) AS total_buy_cost
    FROM port_trades
    GROUP BY symbol
    HAVING ABS(SUM(CASE WHEN side='BUY' THEN qty ELSE -qty END)) > 1e-9
");

while ($row = $resPos->fetch_assoc()) {
    $symbol = $row['symbol'];
    $shares = (float)$row['shares'];
    $totalBuyQty = (float)$row['total_buy_qty'];
    $totalBuyCost = (float)$row['total_buy_cost'];
    $avgCost = $totalBuyQty > 0 ? $totalBuyCost / $totalBuyQty : 0.0;
    $costBasis = $shares * $avgCost;

    $lastPrice = $lastPrices[$symbol]['price'] ?? 0.0;
    $lastPriceDate = $lastPrices[$symbol]['date'] ?? null;
    $marketValue = $shares * $lastPrice;
    $unrealized = $marketValue - $costBasis;

    $positions[] = [
        'symbol' => $symbol,
        'shares' => $shares,
        'avg_cost' => $avgCost,
        'cost_basis' => $costBasis,
        'last_price' => $lastPrice,
        'market_value' => $marketValue,
        'unrealized' => $unrealized,
        'last_price_date' => $lastPriceDate,
        'symbol_yahoo' => $symbolMap[$symbol] ?? '',
    ];

    $totalCostBasis += $costBasis;
    $totalMarketValue += $marketValue;
    $totalUnrealized += $unrealized;
}

// Dividendos acumulados
$dividendTotals = [];
$dividendGrandTotal = 0.0;
$dividendLastDate = [];

$divRows = $mysqli->query("
    SELECT symbol, pay_date, dividend_per_share
    FROM port_dividends
    ORDER BY pay_date ASC
");

$stmtShares = $mysqli->prepare("
    SELECT COALESCE(SUM(CASE WHEN side='BUY' THEN qty ELSE -qty END), 0) AS shares
    FROM port_trades
    WHERE symbol = ? AND trade_date <= ?
");

while ($d = $divRows->fetch_assoc()) {
    $sym = $d['symbol'];
    $payDate = $d['pay_date'];
    $perShare = (float)$d['dividend_per_share'];

    $stmtShares->bind_param('ss', $sym, $payDate);
    $stmtShares->execute();
    $stmtShares->bind_result($sharesOnDate);
    $stmtShares->fetch();
    $stmtShares->free_result();

    $sharesOnDate = max(0.0, (float)$sharesOnDate);
    $amount = $sharesOnDate * $perShare;

    if (!isset($dividendTotals[$sym])) {
        $dividendTotals[$sym] = 0.0;
    }
    $dividendTotals[$sym] += $amount;
    $dividendGrandTotal += $amount;
    $dividendLastDate[$sym] = $payDate;
}
$stmtShares->close();

// Estado de sincronización (precios/dividendos)
$syncStatus = [];
$symbolsAll = array_unique(array_merge(
    array_column($positions, 'symbol'),
    array_keys($lastPrices),
    array_keys($dividendTotals)
));

foreach ($symbolsAll as $sym) {
    $syncStatus[] = [
        'symbol' => $sym,
        'last_price_date' => $lastPrices[$sym]['date'] ?? null,
        'last_dividend_date' => $dividendLastDate[$sym] ?? null,
        'symbol_yahoo' => $symbolMap[$sym] ?? '',
    ];
}

?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portafolio Automático | KASU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body { background: #f4f6fb; }
        .card { box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: none; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
        .badge-buy { background: #2ecc71; }
        .badge-sell { background: #e74c3c; }
        .table td, .table th { vertical-align: middle; }
        .totals { font-weight: bold; }
        .small-note { font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fa fa-line-chart"></i> Portafolio automático</h4>
                    <small class="opacity-75">Captura operaciones; calculamos posiciones, P&L y dividendos.</small>
                </div>
                <div class="card-body py-2">
                    <a class="btn btn-sm btn-outline-secondary" href="../../Pwa_Analisis_Ventas.php">
                        <i class="fa fa-arrow-left"></i> Regresar a análisis de ventas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= h($mensaje) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= h($error) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Registrar operación -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="fa fa-edit"></i> Registrar operación</strong></div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_port" value="<?= h($csrfToken) ?>">
                        <div class="form-group">
                            <label for="symbol">Símbolo</label>
                            <input type="text" class="form-control" id="symbol" name="symbol" placeholder="MSFT, BLK, GCARSOA1.MX" required>
                        </div>
                        <div class="form-group">
                            <label for="trade_date">Fecha</label>
                            <input type="date" class="form-control" id="trade_date" name="trade_date" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="side">Side</label>
                                <select class="form-control" id="side" name="side" required>
                                    <option value="BUY">BUY</option>
                                    <option value="SELL">SELL</option>
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label for="qty">Cantidad</label>
                                <input type="number" step="0.0001" class="form-control" id="qty" name="qty" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label for="price">Precio</label>
                                <input type="number" step="0.0001" class="form-control" id="price" name="price" required>
                            </div>
                            <div class="form-group col-6">
                                <label for="fee">Fee</label>
                                <input type="number" step="0.0001" class="form-control" id="fee" name="fee" value="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="currency">Moneda (opcional)</label>
                            <input type="text" class="form-control" id="currency" name="currency" placeholder="USD, MXN...">
                        </div>
                        <div class="form-group">
                            <label for="notes">Notas (opcional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-save"></i> Guardar operación
                        </button>
                    </form>
                </div>
            </div>

            <!-- Últimas operaciones -->
            <div class="card mb-3">
                <div class="card-header"><strong><i class="fa fa-history"></i> Últimas 50 operaciones</strong></div>
                <div class="card-body table-responsive" style="height: 360px;">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Símbolo</th>
                                <th>Side</th>
                                <th>Cant</th>
                                <th>Precio</th>
                                <th>Fee</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($trades)): ?>
                            <tr><td colspan="7" class="text-muted text-center">Sin operaciones</td></tr>
                        <?php else: ?>
                            <?php foreach ($trades as $t): ?>
                            <tr>
                                <td><?= h($t['trade_date']) ?></td>
                                <td><?= h($t['symbol']) ?></td>
                                <td>
                                    <span class="badge <?= $t['side'] === 'BUY' ? 'badge-buy' : 'badge-sell' ?>">
                                        <?= h($t['side']) ?>
                                    </span>
                                </td>
                                <td><?= h(number_format((float)$t['qty'], 4, '.', ',')) ?></td>
                                <td><?= h(money_mx((float)$t['price'])) ?></td>
                                <td><?= h(money_mx((float)$t['fee'])) ?></td>
                                <td>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Eliminar operación?');">
                                        <input type="hidden" name="csrf_port" value="<?= h($csrfToken) ?>">
                                        <input type="hidden" name="delete_id" value="<?= h($t['id']) ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Posiciones actuales -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><strong><i class="fa fa-briefcase"></i> Posiciones actuales</strong></div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-md-4">
                            <div class="totals">Costo base</div>
                            <div><?= h(money_mx($totalCostBasis)) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="totals">Valor de mercado</div>
                            <div><?= h(money_mx($totalMarketValue)) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="totals">P&L no realizado</div>
                            <div class="<?= $totalUnrealized >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= h(money_mx($totalUnrealized)) ?>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Símbolo</th>
                                    <th>Yahoo</th>
                                    <th>Acciones</th>
                                    <th>Costo prom.</th>
                                    <th>Costo base</th>
                                    <th>Último</th>
                                    <th>Valor</th>
                                    <th>Unrealized</th>
                                    <th>Precio fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($positions)): ?>
                                <tr><td colspan="9" class="text-center text-muted">Sin posiciones</td></tr>
                            <?php else: ?>
                                <?php foreach ($positions as $p): ?>
                                <tr>
                                    <td><?= h($p['symbol']) ?></td>
                                    <td><?= h($p['symbol_yahoo']) ?></td>
                                    <td><?= h(number_format($p['shares'], 4, '.', ',')) ?></td>
                                    <td><?= h(money_mx($p['avg_cost'])) ?></td>
                                    <td><?= h(money_mx($p['cost_basis'])) ?></td>
                                    <td><?= h(money_mx($p['last_price'])) ?></td>
                                    <td><?= h(money_mx($p['market_value'])) ?></td>
                                    <td class="<?= $p['unrealized'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= h(money_mx($p['unrealized'])) ?>
                                    </td>
                                    <td><?= h($p['last_price_date'] ?? 'Sin datos') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Dividendos + sincronización -->
            <div class="card mb-3">
                <div class="card-header"><strong><i class="fa fa-dollar"></i> Dividendos y sincronización</strong></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Dividendos acumulados</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Símbolo</th>
                                            <th>Total</th>
                                            <th>Último pago</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($dividendTotals)): ?>
                                        <tr><td colspan="3" class="text-center text-muted">Sin dividendos</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($dividendTotals as $sym => $tot): ?>
                                        <tr>
                                            <td><?= h($sym) ?></td>
                                            <td><?= h(money_mx($tot)) ?></td>
                                            <td><?= h($dividendLastDate[$sym] ?? 'N/D') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="totals">
                                            <td>Total</td>
                                            <td><?= h(money_mx($dividendGrandTotal)) ?></td>
                                            <td></td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>Estado de sincronización</h6>
                                <a class="btn btn-sm btn-outline-primary" href="portfolio_sync.php?token=<?= h($csrfToken) ?>">
                                    <i class="fa fa-refresh"></i> Sincronizar ahora
                                </a>
                            </div>
                            <div class="table-responsive" style="max-height: 260px;">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Símbolo</th>
                                            <th>Yahoo</th>
                                            <th>Último precio</th>
                                            <th>Último dividendo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($syncStatus)): ?>
                                        <tr><td colspan="4" class="text-center text-muted">Sin datos</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($syncStatus as $s): ?>
                                        <tr>
                                            <td><?= h($s['symbol']) ?></td>
                                            <td><?= h($s['symbol_yahoo']) ?></td>
                                            <td><?= h($s['last_price_date'] ?? 'Sin datos') ?></td>
                                            <td><?= h($s['last_dividend_date'] ?? 'Sin datos') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <p class="small-note mt-2 mb-0">
                        Dividendos estimados usando shares al corte de cada pay_date; si faltan precios/dividendos, el botón de sincronización apunta a php/AnalisisDatos/portfolio_sync.php.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
