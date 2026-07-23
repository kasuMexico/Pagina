<?php
/**
 * Widget flotante de Tickets para Agentes KASU
 * Incluir al final de todas las paginas Mesa_*
 *
 * Auto-detecta el tipo: Mesa_Clientes → 'cliente', Mesa_Prospectos → 'prospecto', otras → 'todos'
 */
$tipoTicket = $tipoTicket ?? 'todos';
if ($tipoTicket === 'todos' && !empty($_SERVER['PHP_SELF'])) {
    $self = basename((string)$_SERVER['PHP_SELF']);
    if (stripos($self, 'Mesa_Clientes') !== false || stripos($self, 'Mesa_Estado') !== false) $tipoTicket = 'cliente';
    elseif (stripos($self, 'Mesa_Prospectos') !== false || stripos($self, 'Mesa_Marketing') !== false) $tipoTicket = 'prospecto';
}
$agente = $_SESSION['Vendedor'] ?? 'Agente';
?>
<style>
.kasu-agent-fab{position:fixed;bottom:20px;right:20px;z-index:9999;font-family:system-ui,-apple-system,sans-serif}
.kasu-agent-fab-btn{width:56px;height:56px;border-radius:50%;background:var(--primary,#012F91);color:#fff;border:0;cursor:pointer;box-shadow:0 4px 16px rgba(0,0,0,.25);display:flex;align-items:center;justify-content:center;font-size:24px;transition:transform .2s;position:relative}
.kasu-agent-fab-btn:hover{transform:scale(1.05)}
.kasu-agent-fab-btn .badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;border-radius:50%;min-width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;padding:0 2px}
.kasu-agent-panel{display:none;position:fixed;bottom:80px;right:20px;width:420px;max-width:calc(100vw - 40px);height:550px;max-height:calc(100vh - 100px);background:#fff;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.2);flex-direction:column;overflow:hidden;z-index:9998}
.kasu-agent-panel.open{display:flex}
.kasu-agent-panel-header{background:var(--primary,#012F91);color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center}
.kasu-agent-panel-header h3{font-size:15px;margin:0}
.kasu-agent-panel-header .close-btn{background:none;border:0;color:#fff;font-size:20px;cursor:pointer;padding:0 4px}
.kasu-agent-tabs{display:flex;border-bottom:1px solid #e5e7eb;background:#f9fafb}
.kasu-agent-tabs button{flex:1;padding:8px;border:0;background:none;cursor:pointer;font-size:12px;color:#6b7280;border-bottom:2px solid transparent;transition:all .2s}
.kasu-agent-tabs button.active{color:var(--primary,#012F91);border-bottom-color:var(--primary,#012F91);font-weight:600}
.kasu-agent-ticket-list{flex:1;overflow-y:auto;padding:8px}
.kasu-agent-ticket-item{padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:6px;cursor:pointer;transition:all .15s;font-size:13px}
.kasu-agent-ticket-item:hover{border-color:var(--primary,#012F91);background:#eef2ff}
.kasu-agent-ticket-item .tcode{font-weight:700;color:var(--primary,#012F91)}
.kasu-agent-ticket-item .tname{color:#374151}
.kasu-agent-ticket-item .tmeta{font-size:11px;color:#9ca3af;margin-top:2px}
.kasu-agent-ticket-item .tbadge{display:inline-block;padding:1px 6px;border-radius:8px;font-size:10px;margin-right:4px}
.kasu-agent-msg-area{flex:1;display:flex;flex-direction:column;background:#f5f5f5}
.kasu-agent-msg-list{flex:1;overflow-y:auto;padding:12px}
.kasu-agent-msg{max-width:85%;margin-bottom:8px;padding:8px 12px;border-radius:10px;font-size:13px;line-height:1.4}
.kasu-agent-msg.agent{background:var(--primary,#012F91);color:#fff;margin-left:auto;border-radius:12px 12px 0 12px}
.kasu-agent-msg.user{background:#fff;border:1px solid #e5e7eb;border-radius:12px 12px 12px 0}
.kasu-agent-msg.system{text-align:center;font-size:10px;color:#9ca3af;margin:4px 0;background:none;max-width:100%}
.kasu-agent-msg-input{display:flex;padding:8px;border-top:1px solid #e5e7eb;background:#fff;gap:8px}
.kasu-agent-msg-input input{flex:1;padding:8px 12px;border:1px solid #d1d5db;border-radius:20px;font-size:13px;outline:none}
.kasu-agent-msg-input button{padding:8px 16px;background:var(--primary,#012F91);color:#fff;border:0;border-radius:20px;cursor:pointer;font-size:13px;font-weight:600}
.kasu-agent-back-btn{background:none;border:0;color:#fff;cursor:pointer;font-size:13px;padding:4px 8px;margin-right:8px}
.kasu-agent-info-bar{padding:8px 16px;background:#fffbeb;font-size:12px;color:#92400e;border-bottom:1px solid #fde68a}
@media(max-width:480px){.kasu-agent-panel{width:100vw;height:100vh;max-width:100vw;max-height:100vh;bottom:0;right:0;border-radius:0}}
</style>

<div class="kasu-agent-fab">
    <button class="kasu-agent-fab-btn" id="agentFabBtn" title="Tickets de Atención" onclick="toggleAgentPanel()">
        💬<span class="badge" id="agentTicketBadge" style="display:none">0</span>
    </button>

    <div class="kasu-agent-panel" id="agentPanel">
        <div class="kasu-agent-panel-header" id="agentPanelHeader">
            <h3 id="agentPanelTitle">Tickets de Atención</h3>
            <button class="close-btn" onclick="toggleAgentPanel()">×</button>
        </div>
        <div class="kasu-agent-tabs" id="agentTabs">
            <button class="active" onclick="filterAgentTickets('abierto')">Abiertos</button>
            <button onclick="filterAgentTickets('en_chat')">En chat</button>
        </div>

        <!-- Vista: lista de tickets -->
        <div class="kasu-agent-ticket-list" id="agentTicketListView"></div>

        <!-- Vista: chat con cliente -->
        <div class="kasu-agent-msg-area" id="agentChatView" style="display:none">
            <div class="kasu-agent-info-bar" id="agentInfoBar"></div>
            <div class="kasu-agent-msg-list" id="agentMsgList"></div>
            <div class="kasu-agent-msg-input">
                <input type="text" id="agentMsgInput" placeholder="Escribe..." onkeydown="if(event.key==='Enter')sendAgentMsg()">
                <button onclick="sendAgentMsg()">Enviar</button>
            </div>
        </div>
    </div>
</div>

<script>
var AB = {
    tipo: '<?= $tipoTicket ?>',
    panel: document.getElementById('agentPanel'),
    open: false,
    currentTicket: null,
    currentFilter: 'abierto',
    refreshInterval: null,
    msgInterval: null,
    chatOpen: false
};

function toggleAgentPanel() {
    AB.open = !AB.open;
    AB.panel.classList.toggle('open', AB.open);
    if (AB.open) {
        loadAgentTickets();
        AB.refreshInterval = setInterval(loadAgentTickets, 15000);
    } else {
        clearInterval(AB.refreshInterval);
        clearInterval(AB.msgInterval);
        AB.chatOpen = false;
        showTicketList();
    }
}

function filterAgentTickets(f) {
    AB.currentFilter = f;
    document.querySelectorAll('#agentTabs button').forEach(function(b,i){
        b.classList.toggle('active', (i===0 && f==='abierto') || (i===1 && f==='en_chat'));
    });
    loadAgentTickets();
}

function loadAgentTickets() {
    fetch('/eia/Vista-360/kasu_agent_chat.php?action=tickets&tipo=' + AB.tipo)
    .then(r => r.json()).then(function(d){
        var tickets = (d.tickets || []).filter(function(t){
            if (AB.currentFilter === 'abierto') return t.status === 'abierto';
            if (AB.currentFilter === 'en_chat') return t.status === 'en_chat';
            return t.status !== 'cerrado';
        });

        // Badge en el boton flotante
        var badge = document.getElementById('agentTicketBadge');
        var count = tickets.length;
        if (count > 0) { badge.textContent = count; badge.style.display = 'flex'; }
        else { badge.style.display = 'none'; }

        // Solo actualizar lista si no esta en modo chat
        if (AB.chatOpen) return;

        var list = document.getElementById('agentTicketListView');
        if (tickets.length === 0) {
            list.innerHTML = '<div style="text-align:center;padding:30px;color:#9ca3af;font-size:13px">No hay tickets ' + AB.currentFilter + '</div>';
            return;
        }
        list.innerHTML = tickets.map(function(t){
            return '<div class="kasu-agent-ticket-item" onclick="openAgentChat(' + t.id + ')">' +
                '<div class="tcode">' + t.ticket_code + '</div>' +
                '<div class="tname">' + (t.nombre_cliente || 'Sin nombre') + '</div>' +
                '<div class="tmeta">' + t.motivo.substring(0,60) + ' · ' + t.created_at.substring(0,16) + '</div>' +
                '<div><span class="tbadge" style="background:' + (t.tipo==='cliente'?'#DBEAFE':'#FEF3C7') + ';color:' + (t.tipo==='cliente'?'#1E40AF':'#92400E') + '">' + t.tipo + '</span>' +
                (t.agente_asignado ? '<span class="tbadge" style="background:#D1FAE5;color:#065F46">' + t.agente_asignado + '</span>' : '') +
                '</div></div>';
        }).join('');
    });
}

function openAgentChat(ticketId) {
    AB.chatOpen = true;
    AB.currentTicket = ticketId;

    document.getElementById('agentTicketListView').style.display = 'none';
    document.getElementById('agentChatView').style.display = 'flex';

    // Cargar info del ticket
    fetch('/eia/Vista-360/kasu_agent_chat.php?action=ticket_info&ticket_id=' + ticketId)
    .then(r => r.json()).then(function(d){
        if (!d.ok) return;
        var t = d.ticket;
        document.getElementById('agentPanelTitle').innerHTML = '<button class="kasu-agent-back-btn" onclick="showTicketList()">←</button>' + t.ticket_code;
        var info = (t.nombre_cliente || 'Sin nombre');
        if (d.cliente) {
            info += ' · ' + (d.cliente.Mail || t.email || '') + ' · Tel: ' + (d.cliente.Telefono || t.telefono || '');
            if (d.cliente.venta) info += ' · Poliza: ' + (d.cliente.venta.IdFIrma || 'N/A');
        }
        document.getElementById('agentInfoBar').textContent = info;

        // Auto-asignar si esta abierto
        if (t.status === 'abierto') {
            var fd = new FormData();
            fd.append('action','assign'); fd.append('ticket_id', ticketId);
            fetch('/eia/Vista-360/kasu_agent_chat.php', {method:'POST',body:fd});
        }
    });

    loadAgentMessages();
    AB.msgInterval = setInterval(loadAgentMessages, 5000);
}

function showTicketList() {
    AB.chatOpen = false;
    AB.currentTicket = null;
    document.getElementById('agentTicketListView').style.display = 'block';
    document.getElementById('agentChatView').style.display = 'none';
    document.getElementById('agentPanelTitle').textContent = 'Tickets de Atención';
    clearInterval(AB.msgInterval);
    loadAgentTickets();
}

function loadAgentMessages() {
    if (!AB.currentTicket) return;
    fetch('/eia/Vista-360/kasu_agent_chat.php?action=messages&ticket_id=' + AB.currentTicket)
    .then(r => r.json()).then(function(d){
        var div = document.getElementById('agentMsgList');
        var msgs = d.messages || [];
        if (msgs.length === 0) { div.innerHTML = '<div class="kasu-agent-msg system">Sin mensajes</div>'; return; }
        div.innerHTML = msgs.map(function(m){
            return '<div class="kasu-agent-msg ' + m.role + '">' + m.content + '</div>';
        }).join('');
        div.scrollTop = div.scrollHeight;
    });
}

function sendAgentMsg() {
    if (!AB.currentTicket) return;
    var input = document.getElementById('agentMsgInput');
    var msg = input.value.trim();
    if (!msg) return;
    input.value = '';
    var fd = new FormData();
    fd.append('action','send'); fd.append('ticket_id', AB.currentTicket); fd.append('mensaje',msg);
    fetch('/eia/Vista-360/kasu_agent_chat.php', {method:'POST',body:fd}).then(function(){
        loadAgentMessages();
    });
}

// Cargar badge al iniciar
(function(){
    fetch('/eia/Vista-360/kasu_agent_chat.php?action=tickets&tipo=' + AB.tipo)
    .then(r => r.json()).then(function(d){
        var n = (d.tickets || []).filter(function(t){return t.status==='abierto'}).length;
        var b = document.getElementById('agentTicketBadge');
        if (n > 0) { b.textContent = n; b.style.display = 'flex'; }
    });
})();
</script>
