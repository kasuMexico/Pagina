<?php
/**
 * KASU NFT — Tokenización de Pólizas Funerarias
 * Plataforma de divulgación, transparencia y monetización Web3.
 * Archivo: nft.php | 2026-07-24
 */

session_start();
require_once __DIR__ . '/eia/analytics_bootstrap.php';
require_once __DIR__ . '/eia/librerias.php';
require_once __DIR__ . '/eia/php/Telcto.php';
$tel = isset($tel) && $tel !== '' ? $tel : '7208177632';

/* ===== Alert opcional (sanitizado) ===== */
if (isset($_GET['Msg'])) {
    $msg = strip_tags((string)$_GET['Msg']);
    $msg = preg_replace('/[<>"\']/', '', $msg);
    echo "<script>alert(" . json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ");</script>";
}

/* ===== Métricas en tiempo real desde BD ===== */
$metrica_polizas   = 0;
$metrica_fondo      = 0;
$metrica_cobrado    = 0;
$metrica_entregado  = 0;

// --- Métricas financieras del fondo funerario ---
$fondo_estado       = 'CRÍTICO';
$fondo_cobertura    = 0.0;
$fondo_aporta_total = 0.0;
$fondo_costo_total  = 0.0;
$fondo_brecha       = 0.0;
$fondo_rendimiento  = 0.0;
$fondo_valor_actual = 0.0;
$fondo_udi          = 8.20;
$fondo_polizas_act  = 0;

if (isset($mysqli) && $mysqli instanceof mysqli) {
    // Pólizas activas emitidas
    $r = $mysqli->query("SELECT COUNT(*) AS c FROM Venta WHERE Status IN ('ACTIVO','ACTIVACION')");
    if ($r) { $row = $r->fetch_assoc(); $metrica_polizas = (int)($row['c'] ?? 0); $r->free(); }

    // Fondo total (suma de subtotales de pólizas activas)
    $r = $mysqli->query("SELECT COALESCE(SUM(Subtotal),0) AS c FROM Venta WHERE Status IN ('ACTIVO','ACTIVACION')");
    if ($r) { $row = $r->fetch_assoc(); $metrica_fondo = (float)($row['c'] ?? 0); $r->free(); }

    // Total cobrado histórico
    $r = $mysqli->query("SELECT COALESCE(SUM(Cantidad),0) AS c FROM Pagos WHERE status IS NULL OR status != 'Mora'");
    if ($r) { $row = $r->fetch_assoc(); $metrica_cobrado = (float)($row['c'] ?? 0); $r->free(); }

    // Valor total de ventas activas por producto (para gráfico de barras)
    $valor_ventas_activas = 0.0;
    $ventas_por_producto  = [];
    $r = $mysqli->query("
        SELECT v.Producto, COALESCE(SUM(v.CostoVenta),0) AS total
        FROM Venta v
        WHERE v.Status = 'ACTIVO'
        GROUP BY v.Producto
        ORDER BY total DESC
    ");
    while ($r && $row = $r->fetch_assoc()) {
        $prod = $row['Producto'];
        $total = (float)$row['total'];
        if ($total > 0) {
            $ventas_por_producto[] = ['producto' => $prod, 'total' => $total];
            $valor_ventas_activas += $total;
        }
    }
    if ($r) $r->free();
    $max_barra = count($ventas_por_producto) > 0 ? max(array_column($ventas_por_producto, 'total')) : 1;

    // --- Fondo Funerario: métricas de solvencia (mismo cálculo que Pwa_Analisis_Ventas) ---
    $r = $mysqli->query("
        SELECT
            COUNT(*) AS polizas,
            COALESCE(SUM(v.CostoVenta * (p.Fideicomiso / 100.0)), 0) AS aportacion_total
        FROM Venta v
        JOIN (
            SELECT Producto, Fideicomiso
            FROM Productos
            WHERE Id IN (SELECT MAX(Id) FROM Productos GROUP BY Producto)
        ) p ON p.Producto = v.Producto
        WHERE v.Status IN ('ACTIVO','ACTIVACION')
    ");
    if ($r) {
        $row = $r->fetch_assoc();
        $fondo_polizas_act  = (int)($row['polizas'] ?? 0);
        $fondo_aporta_total = (float)($row['aportacion_total'] ?? 0);
        $r->free();
    }

    // --- Valor actual del fondo: portafolio real (port_trades + port_prices_daily) ---
    // Mismo cálculo que Pwa_Analisis_Ventas (línea 385-469)
    // Tasa USD/MXN para convertir precios de acciones americanas
    $usdmxn = 20.50;

    $lastPrices = [];
    $r = $mysqli->query("
        SELECT p.symbol, p.close
        FROM port_prices_daily p
        INNER JOIN (
            SELECT symbol, MAX(price_date) AS max_date
            FROM port_prices_daily GROUP BY symbol
        ) m ON p.symbol = m.symbol AND p.price_date = m.max_date
    ");
    while ($r && $p = $r->fetch_assoc()) {
        $lastPrices[$p['symbol']] = (float)$p['close'];
    }
    if ($r) $r->free();

    $pfTotalCost   = 0.0;
    $pfTotalMarket = 0.0;
    $r = $mysqli->query("
        SELECT symbol,
            MAX(currency) AS currency,
            SUM(CASE WHEN side='BUY' THEN qty ELSE -qty END) AS shares,
            SUM(CASE WHEN side='BUY' THEN qty ELSE 0 END) AS total_buy_qty,
            SUM(CASE WHEN side='BUY' THEN (qty * price) + fee ELSE 0 END) AS total_buy_cost
        FROM port_trades
        GROUP BY symbol
        HAVING ABS(SUM(CASE WHEN side='BUY' THEN qty ELSE -qty END)) > 1e-9
    ");
    while ($r && $row = $r->fetch_assoc()) {
        $symbol       = $row['symbol'];
        $currency     = strtolower($row['currency'] ?? '');
        $shares       = (float)$row['shares'];
        $totalBuyQty  = (float)$row['total_buy_qty'];
        $totalBuyCost = (float)$row['total_buy_cost'];
        $avgCost      = $totalBuyQty > 0 ? $totalBuyCost / $totalBuyQty : 0.0;
        $costBasis    = $shares * $avgCost;
        $lastPrice    = $lastPrices[$symbol] ?? 0.0;

        // Convertir precio USD → MXN si la acción es americana y el trade está en pesos
        $isUS = ($currency === 'mxn' && !str_contains($symbol, '.MX'));
        if ($isUS && $lastPrice > 0) {
            $lastPrice = $lastPrice * $usdmxn;
        }

        $marketValue  = $shares * $lastPrice;
        $pfTotalCost   += $costBasis;
        $pfTotalMarket += $marketValue;
    }
    if ($r) $r->free();

    // Rendimiento del portafolio
    $fondo_valor_actual = $pfTotalMarket;
    $fondo_rendimiento  = $pfTotalCost > 0 ? round((($pfTotalMarket - $pfTotalCost) / $pfTotalCost) * 100, 2) : 0.0;

    // Costo servicio proyectado (mismo método que CalculoFondoFunerario)
    $fondo_udi = 8.20;
    $costo_por_poliza = 14500.0;
    $r = $mysqli->query("SELECT AVG(Costo) AS avg_costo FROM EntregaServicio WHERE Costo IS NOT NULL AND Costo > 0 AND (FechaEntrega IS NULL OR FechaEntrega >= DATE_SUB(NOW(), INTERVAL 24 MONTH))");
    if ($r && $row = $r->fetch_assoc()) {
        $avg = (float)($row['avg_costo'] ?? 0);
        if ($avg > 0) $costo_por_poliza = min($avg, 2600 * $fondo_udi);
        $r->free();
    }

    // Costo servicios total y cobertura (con valor de mercado del portafolio, igual que Pwa_Analisis_Ventas)
    $costoServicios = round($costo_por_poliza * $fondo_polizas_act, 2);
    $coberturaPf    = $costoServicios > 0 ? ($pfTotalMarket / $costoServicios) : 0;
    $brechaPf       = max(0.0, $costoServicios - $pfTotalMarket);

    $fondo_costo_total = $costoServicios;
    $fondo_cobertura   = round($coberturaPf * 100, 1);
    $fondo_brecha      = $brechaPf;

    if ($fondo_cobertura >= 100) {
        $fondo_estado = 'SANO';
    } elseif ($fondo_cobertura >= 70) {
        $fondo_estado = 'MANEJABLE';
    } else {
        $fondo_estado = 'CRÍTICO';
    }
}

/* ===== Parámetro de idioma ===== */
$Lgj = filter_input(INPUT_GET, 'Lg', FILTER_UNSAFE_RAW) ?: 'Espanol';
$idiomasValidos = ['Espanol','Ingles','Aleman'];
if (!in_array($Lgj, $idiomasValidos, true)) {
    $Lgj = 'Espanol';
}

/* ===== Traducciones ===== */
$t = [
    'Espanol' => [
        'lang' => 'es', 'lang_label' => 'Idioma',
        // Hero
        'hero_eyebrow'    => 'KASU NFT · Web3',
        'hero_title'      => 'Invierte en Pólizas Funerarias Titularizadas',
        'hero_subtitle'   => 'Activos digitales respaldados por fideicomisos del mundo real',
        'cta_primary'     => 'Ver Colección en OpenSea',
        'cta_secondary'   => 'Conectar Wallet',
        'trust_0'         => 'Respaldo en fideicomiso real',
        'trust_1'         => 'Liquidación en USDC',
        'trust_2'         => 'Royalties del 5% en reventas',
        // Cómo funciona
        'how_eyebrow'     => 'Modelo de Negocio',
        'how_title'       => 'Cómo funciona la tokenización 50/50',
        'how_lead'        => 'KASU emite un NFT por cada póliza funeraria. El inversionista Web3 compra el token al 50% del valor FIAT y al fallecimiento del titular cobra el 50% de la utilidad pasiva generada por el fideicomiso.',
        'how_step1_title' => '1. Cliente tradicional compra su póliza en FIAT',
        'how_step1_desc'  => 'Sin wallet, sin cripto. El cliente paga su servicio funerario normalmente y queda protegido.',
        'how_step1b_title' => '2. El dinero va al fideicomiso F0003',
        'how_step1b_desc'  => 'KASU transfiere la aportación al fideicomiso real que respalda cada póliza, garantizando la solvencia del fondo.',
        'how_step2_title' => '3. KASU mintea el NFT al 50% del valor de la póliza',
        'how_step2_desc'  => 'El inversionista adquiere en OpenSea un token respaldado por el valor que el fideicomiso F0003 asigna a esta póliza.',
        'how_step3_title' => '4. Mercado secundario libre',
        'how_step3_desc'  => 'El NFT se negocia en OpenSea. KASU recibe 5% de regalías por cada reventa (ERC-2981).',
        'how_step4_title' => '5. Liquidación al fallecimiento',
        'how_step4_desc'  => 'Utilidad Pasiva = Fondo − Costo Servicio. 50% para KASU, 50% para el dueño del NFT en USDC.',
        'how_example_title' => 'Ejemplo real: Póliza 30a49 · $3,000 MXN',
        'how_example_col1'  => 'Etapa',
        'how_example_col2'  => 'Ingreso KASU',
        'how_example_col3'  => 'Inversionista NFT',
        'how_row1_label'    => '1. Venta FIAT',
        'how_row1_kasu'     => '$3,000 MXN',
        'how_row1_inv'      => '—',
        'how_row2_label'    => '2. Mint NFT (50%)',
        'how_row2_kasu'     => '$1,500 MXN',
        'how_row2_inv'      => 'Compra al 50% descuento',
        'how_row3_label'    => '3. Secundario',
        'how_row3_kasu'     => '5% royalty',
        'how_row3_inv'      => 'Precio de mercado',
        'how_row4_label'    => '4. Liquidación',
        'how_row4_kasu'     => '$2,000 MXN (50%)',
        'how_row4_inv'      => '$2,000 MXN en USDC',
        'how_total_kasu'    => 'Total KASU: $4,500 +',
        'how_total_inv'     => 'Ganancia: +$500 vs compra',
        // Garantías
        'guarantee_title'      => 'Garantía de Rescisión por Mora',
        'guarantee_text'       => 'Si la póliza entra en estado CANCELADO por mora (+90 días en COBRANZA sin abonar), el Smart Contract invalida el token, retirando los derechos de cobro de utilidad pasiva para proteger el fondo.',
        'guarantee_anon_title' => 'Anonimización de Datos',
        'guarantee_anon_text'  => 'Los metadatos del NFT solo exponen folio (#1156), rango de edad (30a49), año de emisión y valor del fondo. Nunca nombre, CURP o teléfono del titular.',
        // Dashboard
        'dash_eyebrow'      => 'Dashboard',
        'dash_title'        => 'Métricas en Tiempo Real',
        'dash_lead'         => 'El fondo KASU invierte en activos bursátiles reales (acciones, FIBRAs, ETFs). Este panel muestra en tiempo real el valor actual del portafolio, las aportaciones al fideicomiso F0003, y la cobertura frente al costo proyectado de los servicios funerarios.',
        'dash_polizas'      => 'Pólizas Emitidas',
        'dash_fondo'        => 'Fondo Total (MXN)',
        'dash_cobrado'      => 'Total Cobrado (MXN)',
        'dash_entregado'    => 'Rendimientos Entregados',
        'dash_footer'       => '* Los rendimientos se integran con el Smart Contract en Polygon/Base. Montos en USDC.',
        // Fondo Funerario
        'fondo_eyebrow'     => 'Salud del Fondo',
        'fondo_title'       => 'Análisis del Fondo Funerario',
        'fondo_lead'        => 'Métricas de solvencia calculadas en tiempo real. El fondo se compone de las aportaciones de cada póliza activa al fideicomiso (F0003).',
        'fondo_estado_label' => 'Estado del Fondo',
        'fondo_cobertura_label' => 'Cobertura de Servicios',
        'fondo_polizas_label' => 'Pólizas Activas',
        'fondo_aporta_label' => 'Aportación Total al Fondo',
        'fondo_costo_label' => 'Costo Servicios Total',
        'fondo_brecha_label' => 'Brecha a Cubrir',
        'fondo_rend_label' => 'Rendimiento Anual Est.',
        'fondo_udi_label' => 'UDI Actual',
        'fondo_nota'        => '* Máximo del servicio: 2,600 UDIs por póliza. Las aportaciones representan la proporción del fideicomiso de cada producto.',
        // Claim Vault
        'claim_eyebrow'     => 'Claim Vault',
        'claim_title'       => 'Portal de Reclamo',
        'claim_lead'        => 'Conecta tu wallet para verificar tu saldo pendiente y reclamar rendimientos en USDC.',
        'claim_connect'     => 'Conectar Wallet',
        'claim_check'       => 'Verificar Saldo',
        'claim_placeholder' => 'Conecta tu wallet (MetaMask, WalletConnect o Coinbase) para consultar tu saldo disponible en el Claim Vault.',
        'claim_network'     => 'Red: Polygon / Base · Stablecoin: USDC',
    ],
    'Ingles' => [
        'lang' => 'en', 'lang_label' => 'Language',
        'hero_eyebrow'    => 'KASU NFT · Web3',
        'hero_title'      => 'Invest in Tokenized Funeral Policies',
        'hero_subtitle'   => 'Digital assets backed by real-world trust contracts',
        'cta_primary'     => 'View Collection on OpenSea',
        'cta_secondary'   => 'Connect Wallet',
        'trust_0'         => 'Real trust-backed',
        'trust_1'         => 'Settled in USDC',
        'trust_2'         => '5% royalties on resales',
        'how_eyebrow'     => 'Business Model',
        'how_title'       => 'How 50/50 Tokenization Works',
        'how_lead'        => 'KASU mints an NFT for each funeral policy. The Web3 investor buys the token at 50% of the FIAT value and, upon the holder\'s passing, collects 50% of the passive income generated by the trust.',
        'how_step1_title' => '1. Traditional client buys their policy in FIAT',
        'how_step1_desc'  => 'No wallet, no crypto. The client pays for their funeral service as usual and is fully covered.',
        'how_step1b_title' => '2. Money goes to the F0003 trust',
        'how_step1b_desc'  => 'KASU transfers the contribution to the real trust backing each policy, ensuring the fund\'s solvency.',
        'how_step2_title' => '3. KASU mints the NFT at 50% of the policy value',
        'how_step2_desc'  => 'The investor acquires a token on OpenSea backed by the value the F0003 trust allocates to this policy.',
        'how_step3_title' => '4. Free secondary market',
        'how_step3_desc'  => 'The NFT trades on OpenSea. KASU earns 5% royalties on every resale (ERC-2981).',
        'how_step4_title' => '5. Settlement upon passing',
        'how_step4_desc'  => 'Passive Income = Fund − Service Cost. 50% to KASU, 50% to the NFT owner in USDC.',
        'how_example_title' => 'Real example: Policy 30a49 · $3,000 MXN',
        'how_example_col1'  => 'Stage',
        'how_example_col2'  => 'KASU Income',
        'how_example_col3'  => 'NFT Investor',
        'how_row1_label'    => '1. FIAT Sale',
        'how_row1_kasu'     => '$3,000 MXN',
        'how_row1_inv'      => '—',
        'how_row2_label'    => '2. Mint NFT (50%)',
        'how_row2_kasu'     => '$1,500 MXN',
        'how_row2_inv'      => 'Buy at 50% off',
        'how_row3_label'    => '3. Secondary',
        'how_row3_kasu'     => '5% royalty',
        'how_row3_inv'      => 'Market price',
        'how_row4_label'    => '4. Settlement',
        'how_row4_kasu'     => '$2,000 MXN (50%)',
        'how_row4_inv'      => '$2,000 MXN in USDC',
        'how_total_kasu'    => 'Total KASU: $4,500+',
        'how_total_inv'     => 'Profit: +$500 vs purchase',
        'guarantee_title'      => 'Default Rescission Guarantee',
        'guarantee_text'       => 'If the policy enters CANCELADO status due to default (+90 days in COBRANZA without payment), the Smart Contract invalidates the token, removing passive income claim rights to protect the fund.',
        'guarantee_anon_title' => 'Data Anonymization',
        'guarantee_anon_text'  => 'NFT metadata only exposes: folio (#1156), age range (30a49), issuance year, and estimated fund value. Never name, government ID, or phone number.',
        'dash_eyebrow'      => 'Dashboard',
        'dash_title'        => 'Real-Time Metrics',
        'dash_lead'         => 'The KASU fund invests in real stock market assets (stocks, FIBRAs, ETFs). This dashboard shows in real time the current portfolio value, contributions to the F0003 trust, and coverage against the projected cost of funeral services.',
        'dash_polizas'      => 'Policies Issued',
        'dash_fondo'        => 'Total Fund (MXN)',
        'dash_cobrado'      => 'Total Collected (MXN)',
        'dash_entregado'    => 'Returns Delivered',
        'dash_footer'       => '* Returns integrate with the Smart Contract on Polygon/Base. Amounts in USDC.',
        // Fund Health
        'fondo_eyebrow'     => 'Fund Health',
        'fondo_title'       => 'Funeral Fund Analysis',
        'fondo_lead'        => 'Solvency metrics calculated in real time. The fund consists of contributions from each active policy to the trust (F0003).',
        'fondo_estado_label' => 'Fund Status',
        'fondo_cobertura_label' => 'Service Coverage',
        'fondo_polizas_label' => 'Active Policies',
        'fondo_aporta_label' => 'Total Fund Contributions',
        'fondo_costo_label' => 'Total Service Cost',
        'fondo_brecha_label' => 'Gap to Cover',
        'fondo_rend_label' => 'Est. Annual Return',
        'fondo_udi_label' => 'Current UDI',
        'fondo_nota'        => '* Maximum service cost: 2,600 UDIs per policy. Contributions represent each product\'s trust proportion.',
        'claim_eyebrow'     => 'Claim Vault',
        'claim_title'       => 'Claim Portal',
        'claim_lead'        => 'Connect your wallet to check your pending balance and claim returns in USDC.',
        'claim_connect'     => 'Connect Wallet',
        'claim_check'       => 'Check Balance',
        'claim_placeholder' => 'Connect your wallet (MetaMask, WalletConnect, or Coinbase) to view your available balance in the Claim Vault.',
        'claim_network'     => 'Network: Polygon / Base · Stablecoin: USDC',
    ],
    'Aleman' => [
        'lang' => 'de', 'lang_label' => 'Sprache',
        'hero_eyebrow'    => 'KASU NFT · Web3',
        'hero_title'      => 'Investiere in tokenisierte Bestattungsvorsorge',
        'hero_subtitle'   => 'Digitale Vermögenswerte, gedeckt durch reale Treuhandverträge',
        'cta_primary'     => 'Kollektion auf OpenSea',
        'cta_secondary'   => 'Wallet verbinden',
        'trust_0'         => 'Reale Treuhanddeckung',
        'trust_1'         => 'Auszahlung in USDC',
        'trust_2'         => '5% Lizenzgebühren bei Weiterverkauf',
        'how_eyebrow'     => 'Geschäftsmodell',
        'how_title'       => 'So funktioniert die 50/50-Tokenisierung',
        'how_lead'        => 'KASU prägt einen NFT für jede Bestattungsvorsorge. Der Web3-Investor kauft den Token zu 50% des FIAT-Werts und erhält beim Tod des Versicherten 50% der passiven Erträge aus dem Treuhandvermögen.',
        'how_step1_title' => '1. Traditioneller Kunde kauft Police in FIAT',
        'how_step1_desc'  => 'Ohne Wallet, ohne Krypto. Der Kunde zahlt normal und ist vollständig abgesichert.',
        'how_step1b_title' => '2. Das Geld geht an den F0003-Treuhandfonds',
        'how_step1b_desc'  => 'KASU überträgt den Beitrag an den realen Treuhandfonds, der jede Police absichert und die Solvenz des Fonds gewährleistet.',
        'how_step2_title' => '3. KASU prägt NFT zu 50% des Policenwerts',
        'how_step2_desc'  => 'Der Investor erwirbt auf OpenSea einen Token, gedeckt durch den Wert, den der F0003-Treuhandfonds dieser Police zuweist.',
        'how_step3_title' => '4. Freier Sekundärmarkt',
        'how_step3_desc'  => 'Der NFT wird auf OpenSea gehandelt. KASU erhält 5% Lizenzgebühren bei jedem Weiterverkauf (ERC-2981).',
        'how_step4_title' => '5. Abrechnung im Todesfall',
        'how_step4_desc'  => 'Passives Einkommen = Fonds − Servicekosten. 50% an KASU, 50% an den NFT-Eigentümer in USDC.',
        'how_example_title' => 'Reales Beispiel: Police 30a49 · $3.000 MXN',
        'how_example_col1'  => 'Phase',
        'how_example_col2'  => 'Einnahmen KASU',
        'how_example_col3'  => 'NFT-Investor',
        'how_row1_label'    => '1. FIAT-Verkauf',
        'how_row1_kasu'     => '$3.000 MXN',
        'how_row1_inv'      => '—',
        'how_row2_label'    => '2. NFT-Prägung (50%)',
        'how_row2_kasu'     => '$1.500 MXN',
        'how_row2_inv'      => 'Kauf mit 50% Rabatt',
        'how_row3_label'    => '3. Sekundärmarkt',
        'how_row3_kasu'     => '5% Lizenzgebühr',
        'how_row3_inv'      => 'Marktpreis',
        'how_row4_label'    => '4. Abrechnung',
        'how_row4_kasu'     => '$2.000 MXN (50%)',
        'how_row4_inv'      => '$2.000 MXN in USDC',
        'how_total_kasu'    => 'Gesamt KASU: $4.500+',
        'how_total_inv'     => 'Gewinn: +$500 vs Kauf',
        'guarantee_title'      => 'Rücktrittsgarantie bei Zahlungsverzug',
        'guarantee_text'       => 'Tritt die Police aufgrund von Zahlungsverzug in den Status CANCELADO (+90 Tage in COBRANZA ohne Zahlung), entwertet der Smart Contract den Token und entzieht die Ansprüche auf passive Erträge zum Schutz des Fonds.',
        'guarantee_anon_title' => 'Datenanonymisierung',
        'guarantee_anon_text'  => 'NFT-Metadaten zeigen nur: Folio (#1156), Altersgruppe (30a49), Ausgabejahr und Fondswert. Niemals Name, Ausweisnummer oder Telefonnummer.',
        'dash_eyebrow'      => 'Dashboard',
        'dash_title'        => 'Echtzeit-Metriken',
        'dash_lead'         => 'Der KASU-Fonds investiert in reale Börsenwerte (Aktien, FIBRAs, ETFs). Dieses Dashboard zeigt in Echtzeit den aktuellen Portfoliowert, die Beiträge zum F0003-Treuhandfonds und die Deckung im Verhältnis zu den projizierten Kosten der Bestattungsdienste.',
        'dash_polizas'      => 'Ausgegebene Policen',
        'dash_fondo'        => 'Gesamtfonds (MXN)',
        'dash_cobrado'      => 'Gesamteinnahmen (MXN)',
        'dash_entregado'    => 'Auszahlungen',
        'dash_footer'       => '* Auszahlungen werden mit dem Smart Contract auf Polygon/Base integriert. Beträge in USDC.',
        // Fonds-Gesundheit
        'fondo_eyebrow'     => 'Fonds-Gesundheit',
        'fondo_title'       => 'Analyse des Bestattungsfonds',
        'fondo_lead'        => 'Solvenzkennzahlen in Echtzeit. Der Fonds besteht aus Beiträgen jeder aktiven Police zum Treuhandvermögen (F0003).',
        'fondo_estado_label' => 'Fonds-Status',
        'fondo_cobertura_label' => 'Deckungsgrad',
        'fondo_polizas_label' => 'Aktive Policen',
        'fondo_aporta_label' => 'Gesamtbeiträge',
        'fondo_costo_label' => 'Gesamtkosten',
        'fondo_brecha_label' => 'Deckungslücke',
        'fondo_rend_label' => 'Geschätzte Rendite',
        'fondo_udi_label' => 'Aktuelle UDI',
        'fondo_nota'        => '* Maximale Kosten: 2.600 UDIs pro Police. Beiträge entsprechen dem Treuhandanteil jedes Produkts.',
        'claim_eyebrow'     => 'Claim Vault',
        'claim_title'       => 'Auszahlungsportal',
        'claim_lead'        => 'Verbinde deine Wallet, um dein Guthaben zu prüfen und Erträge in USDC zu beanspruchen.',
        'claim_connect'     => 'Wallet verbinden',
        'claim_check'       => 'Guthaben prüfen',
        'claim_placeholder' => 'Verbinde deine Wallet (MetaMask, WalletConnect oder Coinbase), um dein verfügbares Guthaben im Claim Vault einzusehen.',
        'claim_network'     => 'Netzwerk: Polygon / Base · Stablecoin: USDC',
    ],
];
$tx = $t[$Lgj] ?? $t['Espanol'];

// Formatear montos para display
$fmt = function($v) { return '$' . number_format($v, 0, '.', ',') . ' MXN'; };
?>
<!DOCTYPE html>
<html lang="<?= $tx['lang'] ?>">
<head>
  <?php
    $lg  = $Lgj;
    $abs = 'https://kasu.com.mx/nft' . (isset($_GET['Lg']) ? '?Lg=' . urlencode($lg) : '');
    $titleMap = [
      'Espanol' => 'KASU NFT | Tokenización de Pólizas Funerarias en Web3',
      'Ingles'  => 'KASU NFT | Tokenized Funeral Policies on Web3',
      'Aleman'  => 'KASU NFT | Tokenisierte Bestattungsvorsorge auf Web3',
    ];
    $descMap = [
      'Espanol' => 'Invierte en pólizas funerarias titularizadas. Activos digitales respaldados por fideicomisos reales con liquidación en USDC.',
      'Ingles'  => 'Invest in tokenized funeral policies. Digital assets backed by real trusts with USDC settlement.',
      'Aleman'  => 'Investiere in tokenisierte Bestattungsvorsorge. Digitale Vermögenswerte mit USDC-Abrechnung.',
    ];
    $title = htmlspecialchars($titleMap[$lg] ?? $titleMap['Espanol'], ENT_QUOTES, 'UTF-8');
    $desc  = htmlspecialchars($descMap[$lg]  ?? $descMap['Espanol'],  ENT_QUOTES, 'UTF-8');
    $ogimg = 'https://kasu.com.mx/assets/images/nft2.gif';
  ?>
  <meta charset="utf-8">
  <title><?= $title ?></title>
  <meta name="description" content="<?= $desc ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="robots" content="index,follow,max-image-preview:large">
  <meta name="author" content="KASU Servicios a Futuro">
  <meta name="keywords" content="KASU, NFT, tokenización, pólizas funerarias, Web3, USDC, OpenSea, fideicomiso">
  <meta name="theme-color" content="#0f3d4c">

  <link rel="canonical" href="<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>">
  <link rel="alternate" href="https://kasu.com.mx/nft?Lg=Espanol" hreflang="es-MX">
  <link rel="alternate" href="https://kasu.com.mx/nft?Lg=Ingles"  hreflang="en">
  <link rel="alternate" href="https://kasu.com.mx/nft?Lg=Aleman"  hreflang="de">
  <link rel="alternate" href="https://kasu.com.mx/nft"             hreflang="x-default">

  <meta property="og:type" content="website">
  <meta property="og:site_name" content="KASU">
  <meta property="og:url" content="<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>">
  <meta property="og:title" content="<?= $title ?>">
  <meta property="og:description" content="<?= $desc ?>">
  <meta property="og:image" content="<?= $ogimg ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $title ?>">
  <meta name="twitter:description" content="<?= $desc ?>">
  <meta name="twitter:image" content="<?= $ogimg ?>">

  <script type="application/ld+json">
  {
    "@context":"https://schema.org",
    "@type":"WebPage",
    "url":"<?= htmlspecialchars($abs, ENT_QUOTES, 'UTF-8') ?>",
    "name":"<?= $title ?>",
    "description":"<?= $desc ?>",
    "isPartOf":{"@type":"WebSite","name":"KASU","url":"https://kasu.com.mx"}
  }
  </script>

  <link rel="icon" type="image/png" sizes="48x48" href="/assets/images/Index/florkasu-48.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/assets/images/Index/florkasu-96.png">
  <link rel="icon" type="image/png" sizes="192x192" href="/assets/images/Index/florkasu-192.png">
  <link rel="icon" type="image/png" sizes="512x512" href="/assets/images/Index/florkasu-512.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/images/Index/florkasu-180.png">
  <link rel="stylesheet" type="text/css" href="/assets/css/fonts.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-menu.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/index-home.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/nft.css?v=<?php echo $VerCache;?>">
  <link rel="stylesheet" type="text/css" href="/assets/css/kasu-chat.css?v=<?php echo $VerCache;?>">
</head>
<body class="kasu-ui">
  <?php require_once __DIR__ . '/html/MenuPrincipal.php'; ?>

  <main class="kasu-nft">

    <!-- ═══════════════════════════════════════════════ -->
    <!-- 1. HERO SECTION                                 -->
    <!-- ═══════════════════════════════════════════════ -->
    <section class="kasu-nft__hero" aria-labelledby="kasu-nft-hero-title">
      <div class="container">
        <div class="kasu-nft__hero-top">
          <div class="kasu-nft__brand">
            <img src="/assets/images/kasu_logo.jpeg" alt="KASU" width="48" height="48">
            <span>KASU</span>
          </div>
          <div class="kasu-nft__lang">
            <label for="kasu-lang" class="sr-only"><?= htmlspecialchars($tx['lang_label'], ENT_QUOTES, 'UTF-8') ?></label>
            <select id="kasu-lang" class="kasu-nft__lang-select" onchange="this.value && (window.location = this.value);">
              <option value="https://kasu.com.mx/nft?Lg=Espanol" <?= $Lgj === 'Espanol' ? 'selected' : '' ?>>Español</option>
              <option value="https://kasu.com.mx/nft?Lg=Ingles" <?= $Lgj === 'Ingles' ? 'selected' : '' ?>>English</option>
              <option value="https://kasu.com.mx/nft?Lg=Aleman" <?= $Lgj === 'Aleman' ? 'selected' : '' ?>>Deutsch</option>
            </select>
          </div>
        </div>

        <div class="row align-items-center">
          <div class="col-lg-6">
            <p class="kasu-nft__eyebrow"><?= htmlspecialchars($tx['hero_eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
            <h1 id="kasu-nft-hero-title" class="kasu-nft__title"><?= htmlspecialchars($tx['hero_title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="kasu-nft__subtitle"><?= htmlspecialchars($tx['hero_subtitle'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="kasu-nft__cta">
              <a href="https://opensea.io/collection/kasunft" target="_blank" rel="noopener noreferrer" class="btn kasu-nft__btn kasu-nft__btn-primary">
                <?= htmlspecialchars($tx['cta_primary'], ENT_QUOTES, 'UTF-8') ?>
              </a>
              <a href="#" id="kasu-wallet-connect" class="btn kasu-nft__btn kasu-nft__btn-secondary">
                <?= htmlspecialchars($tx['cta_secondary'], ENT_QUOTES, 'UTF-8') ?>
              </a>
            </div>
            <ul class="kasu-nft__trust">
              <li><?= htmlspecialchars($tx['trust_0'], ENT_QUOTES, 'UTF-8') ?></li>
              <li><?= htmlspecialchars($tx['trust_1'], ENT_QUOTES, 'UTF-8') ?></li>
              <li><?= htmlspecialchars($tx['trust_2'], ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
          </div>
          <div class="col-lg-6">
            <div class="kasu-nft__hero-media">
              <a href="https://opensea.io/collection/kasunft" target="_blank" rel="noopener noreferrer" aria-label="KASU NFT en OpenSea">
                <img src="/assets/images/nft2.gif" alt="KASU NFT" loading="lazy" decoding="async" width="960" height="960">
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- 2. CÓMO FUNCIONA EL MODELO                      -->
    <!-- ═══════════════════════════════════════════════ -->
    <section class="kasu-nft__section" id="kasu-nft-how" aria-labelledby="kasu-nft-how-title">
      <div class="container">
        <div class="kasu-nft__section-header">
          <p class="kasu-nft__eyebrow"><?= htmlspecialchars($tx['how_eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
          <h2 id="kasu-nft-how-title" class="kasu-nft__section-title"><?= htmlspecialchars($tx['how_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="kasu-nft__lead"><?= htmlspecialchars($tx['how_lead'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <!-- Diagrama de pasos -->
        <div class="kasu-nft__how-steps">
          <div class="kasu-nft__how-step">
            <div class="kasu-nft__how-step-icon">🏠</div>
            <h3><?= htmlspecialchars($tx['how_step1_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['how_step1_desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="kasu-nft__how-arrow">→</div>
          <div class="kasu-nft__how-step">
            <div class="kasu-nft__how-step-icon">🏛️</div>
            <h3><?= htmlspecialchars($tx['how_step1b_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['how_step1b_desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="kasu-nft__how-arrow">→</div>
          <div class="kasu-nft__how-step">
            <div class="kasu-nft__how-step-icon">🪙</div>
            <h3><?= htmlspecialchars($tx['how_step2_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['how_step2_desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="kasu-nft__how-arrow">→</div>
          <div class="kasu-nft__how-step">
            <div class="kasu-nft__how-step-icon">🔄</div>
            <h3><?= htmlspecialchars($tx['how_step3_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['how_step3_desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="kasu-nft__how-arrow">→</div>
          <div class="kasu-nft__how-step">
            <div class="kasu-nft__how-step-icon">💰</div>
            <h3><?= htmlspecialchars($tx['how_step4_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['how_step4_desc'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>



        <!-- Garantías -->
        <div class="kasu-nft__guarantees">
          <div class="kasu-nft__guarantee-card">
            <h3>🛡️ <?= htmlspecialchars($tx['guarantee_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['guarantee_text'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
          <div class="kasu-nft__guarantee-card">
            <h3>🔒 <?= htmlspecialchars($tx['guarantee_anon_title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($tx['guarantee_anon_text'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      </div>
    </section>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- 3. DASHBOARD UNIFICADO                          -->
    <!-- ═══════════════════════════════════════════════ -->
    <?php
      $estadoColor = $fondo_estado === 'SANO' ? '#2e7d32' : ($fondo_estado === 'MANEJABLE' ? '#ed6c02' : '#d32f2f');
      $estadoIcono = $fondo_estado === 'SANO' ? '🟢' : ($fondo_estado === 'MANEJABLE' ? '🟠' : '🔴');
      $valor_portafolio = $fondo_valor_actual > 0 ? $fondo_valor_actual : $fondo_aporta_total;
    ?>
    <section class="kasu-nft__section kasu-nft__section--muted" id="kasu-nft-dash" aria-labelledby="kasu-nft-dash-title">
      <div class="container">
        <div class="kasu-nft__section-header">
          <p class="kasu-nft__eyebrow"><?= htmlspecialchars($tx['dash_eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
          <h2 id="kasu-nft-dash-title" class="kasu-nft__section-title"><?= htmlspecialchars($tx['dash_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="kasu-nft__lead"><?= htmlspecialchars($tx['dash_lead'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <!-- Valor Actual del Portafolio -->
        <div class="kasu-nft__fondo-status" style="background:#fff;border-radius:22px;padding:1.8rem 2rem;border:1px solid var(--kasu-nft-border);box-shadow:var(--kasu-nft-shadow);text-align:center;margin-bottom:1.8rem;">
          <span style="font-size:2.2rem;display:block;margin-bottom:0.3rem;">📈</span>
          <div style="font-size:2.8rem;font-weight:800;color:var(--kasu-nft-accent);">
            <?= $fmt($valor_portafolio) ?>
          </div>
          <span style="color:var(--kasu-nft-ink-soft);font-size:0.9rem;">Valor Actual Portafolio</span>
        </div>

        <!-- Grid unificado 4 tarjetas -->
        <?php
          $colores_pie = ['#0f3d4c','#1a6b82','#2e8b57','#c9a15a','#8b4513','#6b3fa0','#b8324e','#4a7c96','#d4956a','#5c8a5c'];
          $conic = []; $acum = 0; $pie_legend = '';
          foreach ($ventas_por_producto as $i => $vp):
            $pct_pie = $valor_ventas_activas > 0 ? round(($vp['total'] / $valor_ventas_activas) * 100, 1) : 0;
            $c = $colores_pie[$i % count($colores_pie)];
            $conic[] = $c . ' ' . $acum . '% ' . ($acum + $pct_pie) . '%';
            $acum += $pct_pie;
            $pie_legend .= '<div style="display:flex;justify-content:space-between;"><span><strong style="color:'.$c.';">•</strong> '.htmlspecialchars($vp['producto']).'</span><strong>'.$pct_pie.'%</strong></div>';
          endforeach;
          $pie_gradient = count($conic) > 0 ? implode(', ', $conic) : '#e8ecf2 0% 100%';
        ?>
        <div class="kasu-nft__metrics-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.2rem;align-items:stretch;">

          <!-- Tarjeta 1: Pólizas Emitidas -->
          <div class="kasu-nft__metric-card" style="background:#fff;border-radius:22px;padding:1.5rem;border:1px solid var(--kasu-nft-border);box-shadow:var(--kasu-nft-shadow);text-align:center;display:flex;flex-direction:column;justify-content:center;align-items:center;">
            <div style="font-size:1.8rem;margin-bottom:0.5rem;">📋</div>
            <div style="font-size:1.8rem;font-weight:700;color:#0d1628;"><?= number_format($metrica_polizas, 0) ?></div>
            <div style="font-size:0.85rem;color:var(--kasu-nft-ink-soft);font-weight:500;"><?= htmlspecialchars($tx['dash_polizas'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>

          <!-- Tarjeta 2: Aportación Total -->
          <div class="kasu-nft__metric-card" style="background:#fff;border-radius:22px;padding:1.5rem;border:1px solid var(--kasu-nft-border);box-shadow:var(--kasu-nft-shadow);text-align:center;display:flex;flex-direction:column;justify-content:center;align-items:center;">
            <div style="font-size:1.8rem;margin-bottom:0.5rem;">🏛️</div>
            <div style="font-size:1.6rem;font-weight:700;color:#0d1628;"><?= $fmt($fondo_aporta_total) ?></div>
            <div style="font-size:0.85rem;color:var(--kasu-nft-ink-soft);font-weight:500;"><?= htmlspecialchars($tx['fondo_aporta_label'], ENT_QUOTES, 'UTF-8') ?></div>
          </div>

          <!-- Tarjeta 3: Estado + Cobertura -->
          <div class="kasu-nft__metric-card" style="background:#fff;border-radius:22px;padding:1.5rem;border:1px solid var(--kasu-nft-border);box-shadow:var(--kasu-nft-shadow);text-align:center;display:flex;flex-direction:column;justify-content:center;align-items:center;">
            <div style="font-size:1.8rem;margin-bottom:0.5rem;"><?= $estadoIcono ?></div>
            <div style="font-size:1.6rem;font-weight:700;color:<?= $estadoColor ?>;"><?= $fondo_estado ?></div>
            <div style="font-size:0.85rem;color:var(--kasu-nft-ink-soft);font-weight:500;"><?= number_format($fondo_cobertura, 1) ?>% Cobertura · Rend. <?= number_format($fondo_rendimiento, 1) ?>%</div>
          </div>

          <!-- Tarjeta 4: Gráfica de pastel composición por producto -->
          <div class="kasu-nft__metric-card" style="background:#fff;border-radius:22px;padding:1.2rem 1.5rem;border:1px solid var(--kasu-nft-border);box-shadow:var(--kasu-nft-shadow);display:flex;flex-direction:column;justify-content:space-between;">
            <p style="font-weight:600;font-size:0.85rem;color:var(--kasu-nft-ink-soft);margin-bottom:0.8rem;text-align:center;">Composición por producto</p>
            <div style="display:flex;align-items:center;justify-content:space-around;gap:1rem;">
              <div style="width:90px;height:90px;border-radius:50%;flex-shrink:0;background:conic-gradient(<?= $pie_gradient ?>);"></div>
              <div style="font-size:0.72rem;display:flex;flex-direction:column;gap:2px;width:100%;">
                <?= $pie_legend ?>
              </div>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ═══════════════════════════════════════════════ -->
    <!-- 4. CLAIM VAULT (Portal de Reclamo)              -->
    <!-- ═══════════════════════════════════════════════ -->
    <section class="kasu-nft__section" id="kasu-nft-claim" aria-labelledby="kasu-nft-claim-title">
      <div class="container">
        <div class="kasu-nft__section-header">
          <p class="kasu-nft__eyebrow"><?= htmlspecialchars($tx['claim_eyebrow'], ENT_QUOTES, 'UTF-8') ?></p>
          <h2 id="kasu-nft-claim-title" class="kasu-nft__section-title"><?= htmlspecialchars($tx['claim_title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="kasu-nft__lead"><?= htmlspecialchars($tx['claim_lead'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="kasu-nft__claim-card">
              <div class="kasu-nft__claim-placeholder">
                <span class="kasu-nft__claim-wallet-icon">🔐</span>
                <p><?= htmlspecialchars($tx['claim_placeholder'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="kasu-nft__claim-actions">
                  <button class="btn kasu-nft__btn kasu-nft__btn-primary" id="kasu-claim-connect">
                    <?= htmlspecialchars($tx['claim_connect'], ENT_QUOTES, 'UTF-8') ?>
                  </button>
                  <button class="btn kasu-nft__btn kasu-nft__btn-outline" id="kasu-claim-check" disabled>
                    <?= htmlspecialchars($tx['claim_check'], ENT_QUOTES, 'UTF-8') ?>
                  </button>
                </div>
                <p class="kasu-nft__claim-network"><?= htmlspecialchars($tx['claim_network'], ENT_QUOTES, 'UTF-8') ?></p>
              </div>
              <!-- Balance (oculto hasta conectar) -->
              <div class="kasu-nft__claim-balance" id="kasu-claim-balance" style="display:none;">
                <span class="kasu-nft__claim-balance-label">Saldo Disponible</span>
                <span class="kasu-nft__claim-balance-value" id="kasu-claim-amount">$0.00 USDC</span>
                <button class="btn kasu-nft__btn kasu-nft__btn-primary" id="kasu-claim-redeem">Reclamar USDC</button>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="kasu-nft__claim-info">
              <h3>Smart Contract</h3>
              <p>El Claim Vault está gobernado por un Smart Contract desplegado en Polygon/Base. Los fondos se liberan automáticamente cuando el oráculo confirma el evento de liquidación de la póliza asociada al NFT.</p>
              <ul class="kasu-nft__list">
                <li>Verificación on-chain del ownership del NFT</li>
                <li>Liquidación automática vía oráculo</li>
                <li>Fondos custodiados en el Smart Contract</li>
                <li>Sin intervención manual de KASU</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer>
    <?php require_once __DIR__ . '/html/footer.php'; ?>
  </footer>

  <script src="/assets/js/jquery-2.1.0.min.js"></script>
  <script src="/assets/js/bootstrap.min.js"></script>
  <script src="/assets/js/waypoints.min.js"></script>
  <script src="/assets/js/imgfix.min.js"></script>
  <script src="/assets/js/jquery.counterup.min.js"></script>
  <script src="/assets/js/scrollreveal.min.js"></script>
  <script src="/assets/js/custom.js"></script>
  <script type="text/javascript" async src="https://d335luupugsy2.cloudfront.net/js/loader-scripts/28dd2782-ee7d-4b25-82b1-f5993b27764a-loader.js"></script>
  <?php require_once __DIR__ . '/html/kasu-chat-widget.php'; ?>

</body>
</html>
