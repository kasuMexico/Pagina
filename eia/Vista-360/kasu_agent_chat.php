<?php
declare(strict_types=1);

/**
 * Portal de Chat de Atencion a Cliente KASU
 * Accesible desde Mesa_Clientes.php y Mesa_Prospectos.php
 * Requiere sesion de empleado autenticado.
 */

$sessionFile = __DIR__ . '/../session.php';
if (is_file($sessionFile)) {
    require_once $sessionFile;
    if (function_exists('kasu_session_start')) {
        kasu_session_start();
    } else {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
} else {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

require_once __DIR__ . '/../librerias.php';

global $mysqli, $pros;

if (empty($_SESSION['Vendedor'])) {
    http_response_code(403);
    exit('Acceso denegado. Inicia sesion en Mesa_Clientes o Mesa_Prospectos.');
}

$agente = (string)$_SESSION['Vendedor'];

// === API: listar tickets abiertos ===
if (isset($_GET['action']) && $_GET['action'] === 'tickets') {
    header('Content-Type: application/json; charset=utf-8');
    $tipo = (string)($_GET['tipo'] ?? 'todos');
    $sql = "SELECT * FROM kasu_support_tickets WHERE status != 'cerrado'";
    if ($tipo === 'prospecto' || $tipo === 'cliente') {
        $sql .= " AND tipo = '" . $pros->real_escape_string($tipo) . "'";
    }
    $sql .= " ORDER BY created_at DESC LIMIT 50";
    $res = $pros->query($sql);
    $tickets = [];
    while ($row = $res->fetch_assoc()) {
        $tickets[] = $row;
    }
    echo json_encode(['ok' => true, 'tickets' => $tickets], JSON_UNESCAPED_UNICODE);
    exit;
}

// === API: tomar ticket ===
if (isset($_POST['action']) && $_POST['action'] === 'assign') {
    header('Content-Type: application/json; charset=utf-8');
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    if ($ticketId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Ticket requerido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt = $pros->prepare("UPDATE kasu_support_tickets SET status='en_chat', agente_asignado=? WHERE id=? AND status='abierto'");
    $stmt->bind_param('si', $agente, $ticketId);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        $pros->query("INSERT INTO kasu_support_messages (ticket_id, role, content) VALUES ({$ticketId}, 'system', '{$agente} ha tomado el ticket')");
        echo json_encode(['ok' => true, 'mensaje' => 'Ticket asignado'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Ticket ya fue tomado por otro agente'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// === API: enviar mensaje del agente ===
if (isset($_POST['action']) && $_POST['action'] === 'send') {
    header('Content-Type: application/json; charset=utf-8');
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    $message = trim((string)($_POST['mensaje'] ?? ''));
    if ($ticketId <= 0 || $message === '') {
        echo json_encode(['ok' => false, 'error' => 'Ticket y mensaje requeridos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pros->prepare("INSERT INTO kasu_support_messages (ticket_id, role, content) VALUES (?, 'agent', ?)");
    $stmt->bind_param('is', $ticketId, $message);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true, 'mensaje' => 'Enviado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// === API: mensajes de un ticket ===
if (isset($_GET['action']) && $_GET['action'] === 'messages') {
    header('Content-Type: application/json; charset=utf-8');
    $ticketId = (int)($_GET['ticket_id'] ?? 0);
    if ($ticketId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Ticket requerido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $res = $pros->query("SELECT * FROM kasu_support_messages WHERE ticket_id = {$ticketId} ORDER BY created_at ASC");
    $messages = [];
    while ($row = $res->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode(['ok' => true, 'messages' => $messages], JSON_UNESCAPED_UNICODE);
    exit;
}

// === API: cerrar ticket ===
if (isset($_POST['action']) && $_POST['action'] === 'close') {
    header('Content-Type: application/json; charset=utf-8');
    $ticketId = (int)($_POST['ticket_id'] ?? 0);
    if ($ticketId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Ticket requerido'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $pros->query("UPDATE kasu_support_tickets SET status='cerrado' WHERE id={$ticketId}");
    $pros->query("INSERT INTO kasu_support_messages (ticket_id, role, content) VALUES ({$ticketId}, 'system', '{$agente} cerro el ticket')");
    echo json_encode(['ok' => true, 'mensaje' => 'Ticket cerrado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// === API: obtener info del ticket (detalles del cliente) ===
if (isset($_GET['action']) && $_GET['action'] === 'ticket_info') {
    header('Content-Type: application/json; charset=utf-8');
    $ticketId = (int)($_GET['ticket_id'] ?? 0);
    $res = $pros->query("SELECT * FROM kasu_support_tickets WHERE id = {$ticketId} LIMIT 1");
    $ticket = $res->fetch_assoc() ?: null;
    if (!$ticket) {
        echo json_encode(['ok' => false, 'error' => 'Ticket no encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Si el ticket tiene id_contact, obtenemos datos completos del cliente
    $clienteData = null;
    $idContact = (int)($ticket['id_contact'] ?? 0);
    $idVenta = (int)($ticket['id_venta'] ?? 0);
    if ($idContact > 0) {
        $cRes = $mysqli->query("SELECT * FROM Contacto WHERE id = {$idContact} LIMIT 1");
        $clienteData = $cRes->fetch_assoc() ?: null;
    }
    if ($idVenta > 0) {
        $vRes = $mysqli->query("SELECT Id, IdFIrma, Producto, Status, CostoVenta, Subtotal, NumeroPagos FROM Venta WHERE Id = {$idVenta} LIMIT 1");
        $clienteData['venta'] = $vRes->fetch_assoc() ?: null;
    }

    echo json_encode(['ok' => true, 'ticket' => $ticket, 'cliente' => $clienteData], JSON_UNESCAPED_UNICODE);
    exit;
}

// === VISTA HTML ===
$tipo = (string)($_GET['tipo'] ?? 'todos');
$esProspectos = ($tipo === 'prospecto');
$esClientes = ($tipo === 'cliente');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>KASU | Atencion a Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/fonts.css">
    <style>
        :root{--primary:#012F91;--bg:#f5f5f5;--card:#fff;--border:#e5e7eb;--text:#1f2937;--muted:#6b7280;--success:#10b981;--warn:#f59e0b}
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--text);display:flex;height:100vh}
        .sidebar{width:320px;background:var(--card);border-right:1px solid var(--border);display:flex;flex-direction:column}
        .sidebar-header{padding:16px;border-bottom:1px solid var(--border);background:var(--primary);color:#fff}
        .sidebar-header h2{font-size:16px;margin-bottom:4px}
        .sidebar-header .agente{font-size:12px;opacity:.8}
        .ticket-list{flex:1;overflow-y:auto;padding:8px}
        .ticket-item{padding:12px;border:1px solid var(--border);border-radius:8px;margin-bottom:8px;cursor:pointer;transition:all .2s}
        .ticket-item:hover{border-color:var(--primary)}
        .ticket-item.active{border-color:var(--primary);background:#EEF2FF}
        .ticket-item .ticket-code{font-weight:700;font-size:14px}
        .ticket-item .ticket-name{font-size:13px;color:var(--muted)}
        .ticket-item .ticket-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:11px;margin-top:4px}
        .badge-prospecto{background:#FEF3C7;color:#92400E}
        .badge-cliente{background:#DBEAFE;color:#1E40AF}
        .badge-abierto{background:#FEE2E2;color:#991B1B}
        .badge-en_chat{background:#D1FAE5;color:#065F46}
        .main{flex:1;display:flex;flex-direction:column}
        .main-header{padding:16px;border-bottom:1px solid var(--border);background:var(--card)}
        .main-header h3{font-size:16px}
        .main-header .ticket-meta{font-size:12px;color:var(--muted);margin-top:4px}
        .client-info{padding:12px 16px;background:#FFFBEB;border-bottom:1px solid var(--border);font-size:13px}
        .messages{flex:1;overflow-y:auto;padding:16px}
        .msg{margin-bottom:12px;max-width:80%}
        .msg.agent{margin-left:auto;background:var(--primary);color:#fff;padding:10px 14px;border-radius:12px 12px 0 12px}
        .msg.user{background:var(--card);border:1px solid var(--border);padding:10px 14px;border-radius:12px 12px 12px 0}
        .msg.system{text-align:center;font-size:11px;color:var(--muted);margin:8px 0}
        .msg-input-area{padding:16px;border-top:1px solid var(--border);background:var(--card);display:flex;gap:8px}
        .msg-input-area input{flex:1;padding:10px;border:1px solid var(--border);border-radius:8px;font-size:14px}
        .msg-input-area button{padding:10px 20px;background:var(--primary);color:#fff;border:0;border-radius:8px;cursor:pointer;font-weight:600}
        .empty-state{display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);font-size:14px}
        .actions-bar{display:flex;gap:8px;padding:8px 16px;border-bottom:1px solid var(--border);background:var(--card)}
        .actions-bar button{padding:6px 14px;border:1px solid var(--border);border-radius:6px;background:#fff;cursor:pointer;font-size:12px}
        .actions-bar button.assign-btn{background:var(--primary);color:#fff;border-color:var(--primary)}
        .actions-bar button.close-btn{color:#991B1B;border-color:#FECACA}
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>Chat de Atencion</h2>
        <div class="agente">Agente: <?= htmlspecialchars($agente, ENT_QUOTES, 'UTF-8') ?></div>
        <div style="margin-top:8px;display:flex;gap:4px">
            <button onclick="filter('todos')" style="flex:1;padding:4px;border:1px solid rgba(255,255,255,.3);border-radius:4px;background:<?= $tipo==='todos'?'rgba(255,255,255,.2)':'transparent' ?>;color:#fff;cursor:pointer;font-size:11px">Todos</button>
            <button onclick="filter('prospecto')" style="flex:1;padding:4px;border:1px solid rgba(255,255,255,.3);border-radius:4px;background:<?= $esProspectos?'rgba(255,255,255,.2)':'transparent' ?>;color:#fff;cursor:pointer;font-size:11px">Prospectos</button>
            <button onclick="filter('cliente')" style="flex:1;padding:4px;border:1px solid rgba(255,255,255,.3);border-radius:4px;background:<?= $esClientes?'rgba(255,255,255,.2)':'transparent' ?>;color:#fff;cursor:pointer;font-size:11px">Clientes</button>
        </div>
    </div>
    <div class="ticket-list" id="ticketList">
        <div style="text-align:center;padding:20px;color:var(--muted)">Cargando tickets...</div>
    </div>
</div>
<div class="main">
    <div class="main-header" id="mainHeader">
        <h3>Selecciona un ticket</h3>
        <div class="ticket-meta">Los tickets abiertos aparecen en la lista izquierda</div>
    </div>
    <div class="actions-bar" id="actionsBar" style="display:none">
        <button class="assign-btn" id="assignBtn" onclick="assignTicket()">Tomar Ticket</button>
        <button onclick="refreshMessages()">Refrescar</button>
        <button class="close-btn" onclick="closeTicket()">Cerrar Ticket</button>
    </div>
    <div class="client-info" id="clientInfo" style="display:none"></div>
    <div class="messages" id="messages">
        <div class="empty-state">Selecciona un ticket para ver la conversacion</div>
    </div>
    <div class="msg-input-area" id="msgInputArea" style="display:none">
        <input type="text" id="msgInput" placeholder="Escribe tu mensaje..." onkeydown="if(event.key==='Enter')sendMessage()">
        <button onclick="sendMessage()">Enviar</button>
    </div>
</div>

<script>
let currentTicket = null;
let currentTipo = '<?= $tipo ?>';

function filter(tipo) { window.location = '?tipo=' + tipo; }

async function loadTickets() {
    const res = await fetch('?action=tickets&tipo=' + currentTipo);
    const data = await res.json();
    const list = document.getElementById('ticketList');
    if (!data.tickets || data.tickets.length === 0) {
        list.innerHTML = '<div style="text-align:center;padding:20px;color:var(--muted)">No hay tickets abiertos</div>';
        return;
    }
    list.innerHTML = data.tickets.map(t => `
        <div class="ticket-item ${currentTicket && currentTicket.id === t.id ? 'active' : ''}" onclick="selectTicket(${t.id})">
            <div class="ticket-code">${t.ticket_code}</div>
            <div class="ticket-name">${t.nombre_cliente || 'Sin nombre'} - ${t.motivo ? t.motivo.substring(0,40) : ''}</div>
            <span class="ticket-badge badge-${t.tipo}">${t.tipo}</span>
            <span class="ticket-badge badge-${t.status}">${t.status}</span>
            ${t.agente_asignado ? '<div style="font-size:11px;color:var(--muted);margin-top:4px">Atendido por: '+t.agente_asignado+'</div>' : ''}
        </div>
    `).join('');
}

async function selectTicket(id) {
    currentTicket = { id: id };
    const res = await fetch('?action=ticket_info&ticket_id=' + id);
    const data = await res.json();
    if (!data.ok) return;

    const t = data.ticket;
    document.getElementById('mainHeader').innerHTML = '<h3>#' + t.ticket_code + ' - ' + (t.nombre_cliente || 'Sin nombre') + '</h3><div class="ticket-meta">' + t.tipo + ' · ' + t.motivo + '</div>';

    // Mostrar info del cliente si existe
    if (data.cliente) {
        let html = 'Cliente: ' + (data.cliente.Mail || t.email || 'N/A') + ' · Tel: ' + (data.cliente.Telefono || t.telefono || 'N/A');
        if (data.cliente.venta) {
            html += ' · Poliza: ' + (data.cliente.venta.IdFIrma || 'N/A') + ' · Status: ' + data.cliente.venta.Status;
        }
        document.getElementById('clientInfo').innerHTML = html;
        document.getElementById('clientInfo').style.display = 'block';
    }

    document.getElementById('actionsBar').style.display = 'flex';
    document.getElementById('msgInputArea').style.display = 'flex';
    document.getElementById('assignBtn').style.display = (t.status === 'abierto') ? 'inline-block' : 'none';

    await refreshMessages();
    await loadTickets();
}

async function refreshMessages() {
    if (!currentTicket) return;
    const res = await fetch('?action=messages&ticket_id=' + currentTicket.id);
    const data = await res.json();
    const div = document.getElementById('messages');
    if (!data.messages || data.messages.length === 0) {
        div.innerHTML = '<div class="empty-state">Sin mensajes</div>';
        return;
    }
    div.innerHTML = data.messages.map(m =>
        m.role === 'system'
            ? '<div class="msg system">' + m.content + '</div>'
            : '<div class="msg ' + m.role + '">' + m.content + '</div>'
    ).join('');
    div.scrollTop = div.scrollHeight;
}

async function assignTicket() {
    if (!currentTicket) return;
    const form = new FormData();
    form.append('action', 'assign');
    form.append('ticket_id', currentTicket.id);
    const res = await fetch('', { method: 'POST', body: form });
    const data = await res.json();
    if (data.ok) {
        await selectTicket(currentTicket.id);
    } else {
        alert(data.error);
    }
}

async function sendMessage() {
    if (!currentTicket) return;
    const input = document.getElementById('msgInput');
    const msg = input.value.trim();
    if (!msg) return;
    input.value = '';

    const form = new FormData();
    form.append('action', 'send');
    form.append('ticket_id', currentTicket.id);
    form.append('mensaje', msg);
    await fetch('', { method: 'POST', body: form });
    await refreshMessages();
}

async function closeTicket() {
    if (!currentTicket || !confirm('Cerrar este ticket?')) return;
    const form = new FormData();
    form.append('action', 'close');
    form.append('ticket_id', currentTicket.id);
    await fetch('', { method: 'POST', body: form });
    currentTicket = null;
    document.getElementById('messages').innerHTML = '<div class="empty-state">Ticket cerrado</div>';
    document.getElementById('actionsBar').style.display = 'none';
    document.getElementById('msgInputArea').style.display = 'none';
    document.getElementById('clientInfo').style.display = 'none';
    await loadTickets();
}

loadTickets();
setInterval(loadTickets, 15000);
setInterval(refreshMessages, 8000);
</script>
</body>
</html>
