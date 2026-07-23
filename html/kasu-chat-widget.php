<?php
/**
 * Widget FAB + Chat KASU – incluible en todas las páginas públicas.
 * Requiere que $tel esté definido antes del include.
 */
?>
<div class="kasu-fab-wrap" aria-live="polite">
    <div class="kasu-fab-panel" id="kasu-fab-panel" hidden>
        <a href="tel:<?php echo isset($tel) ? htmlspecialchars($tel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : ''; ?>" class="kasu-fab-action kasu-fab-action--call" aria-label="Llamar a KASU">
            <span class="kasu-fab-icon" aria-hidden="true">&#x1F4DE;</span>
            Llamar a KASU
        </a>
        <a href="https://wa.me/<?php echo preg_replace('/\D/', '', $tel ?? ''); ?>?text=Hola,%20requiero%20atenci%C3%B3n%20inmediata%20de%20KASU" class="kasu-fab-action kasu-fab-action--whats" target="_blank" rel="noopener" aria-label="Enviar WhatsApp a KASU">
            <span class="kasu-fab-icon" aria-hidden="true">&#x1F4AC;</span>
            Enviar WhatsApp
        </a>
        <button type="button" class="kasu-fab-action kasu-fab-action--chat" id="kasu-chat-open" aria-expanded="false" aria-controls="kasu-chat-overlay">
            <span class="kasu-fab-icon" aria-hidden="true">&#x1F5E8;&#xFE0F;</span>
            Hablar con Asistencia
        </button>
    </div>
    <button type="button" class="kasu-fab" id="kasu-fab" aria-expanded="false" aria-controls="kasu-fab-panel">
        <img src="/assets/images/flor_redonda.svg" alt="" width="34" height="34" loading="lazy" decoding="async">
        Atenci&oacute;n al cliente
    </button>
</div>

<section class="kasu-chat-overlay" id="kasu-chat-overlay" aria-live="polite" hidden>
    <div class="kasu-chat-panel">
        <header class="kasu-chat-panel__header">
            <div class="kasu-chat-panel__brand">
                <img src="/assets/images/flor_redonda.svg" alt="KASU" width="28" height="28" loading="lazy" decoding="async">
                <div>
                    <p class="kasu-chat-panel__title">Chat KASU</p>
                    <span class="kasu-chat-panel__status">En linea</span>
                </div>
            </div>
            <div class="kasu-chat-panel__actions">
                <span class="kasu-chat-panel__pill">Vista 360</span>
                <button type="button" class="kasu-chat-panel__close" id="kasu-chat-close" aria-label="Cerrar chat">&times;</button>
            </div>
        </header>

        <div class="kasu-chat-panel__messages" id="kasu-chat-messages">
            <div class="kasu-chat-message kasu-chat-message--bot">
                <img src="/assets/images/flor_redonda.svg" alt="KASU" class="kasu-chat-avatar" width="26" height="26" loading="lazy" decoding="async">
                <div class="kasu-chat-bubble">
                    Hola, soy la asistente virtual de KASU. &iquest;en que puedo ayudarte hoy?
                </div>
            </div>
        </div>

        <form class="kasu-chat-panel__form" id="kasu-chat-form" autocomplete="off">
            <input type="text" id="kasu-chat-input" name="kasu-chat-input" placeholder="Escribe tu mensaje">
            <button type="submit">Enviar</button>
        </form>
    </div>
</section>

<script>
(function () {
    var fabButton = document.getElementById('kasu-fab');
    var fabPanel = document.getElementById('kasu-fab-panel');
    var chatOpen = document.getElementById('kasu-chat-open');
    var chatOverlay = document.getElementById('kasu-chat-overlay');
    var chatClose = document.getElementById('kasu-chat-close');
    var chatForm = document.getElementById('kasu-chat-form');
    var chatInput = document.getElementById('kasu-chat-input');
    var chatBody = document.getElementById('kasu-chat-messages');

    if (!fabButton || !fabPanel) return;

    var hideFabPanel = function () {
        fabPanel.setAttribute('hidden', 'hidden');
        fabButton.setAttribute('aria-expanded', 'false');
    };

    var showFabPanel = function () {
        fabPanel.removeAttribute('hidden');
        fabButton.setAttribute('aria-expanded', 'true');
    };

    var openChat = function () {
        if (!chatOverlay) return;
        chatOverlay.removeAttribute('hidden');
        if (chatOpen) chatOpen.setAttribute('aria-expanded', 'true');
        if (chatInput) chatInput.focus();
        hideFabPanel();
    };

    var closeChat = function () {
        if (!chatOverlay) return;
        chatOverlay.setAttribute('hidden', 'hidden');
        if (chatOpen) chatOpen.setAttribute('aria-expanded', 'false');
        showFabPanel();
    };

    fabButton.addEventListener('click', function () {
        var chatOpenNow = chatOverlay && !chatOverlay.hasAttribute('hidden');
        if (chatOpenNow) {
            closeChat();
            showFabPanel();
            return;
        }

        var expanded = fabButton.getAttribute('aria-expanded') === 'true';
        fabButton.setAttribute('aria-expanded', String(!expanded));
        if (expanded) {
            fabPanel.setAttribute('hidden', 'hidden');
        } else {
            fabPanel.removeAttribute('hidden');
        }
    });

    if (chatOpen) {
        chatOpen.addEventListener('click', function () {
            var isOpen = chatOverlay && !chatOverlay.hasAttribute('hidden');
            if (isOpen) {
                closeChat();
            } else {
                openChat();
            }
        });
    }

    if (chatClose) {
        chatClose.addEventListener('click', closeChat);
    }

    if (chatForm && chatInput && chatBody) {
        var endpoint = '/eia/Vista-360/kasu_chat_publico.php';
        var typingNode = null;

        var appendUserMessage = function (message) {
            var wrapper = document.createElement('div');
            wrapper.className = 'kasu-chat-message kasu-chat-message--user';
            var bubble = document.createElement('div');
            bubble.className = 'kasu-chat-bubble';
            bubble.textContent = message;
            wrapper.appendChild(bubble);
            chatBody.appendChild(wrapper);
        };

        var appendBotMessage = function (html) {
            var wrapper = document.createElement('div');
            wrapper.className = 'kasu-chat-message kasu-chat-message--bot';
            var avatar = document.createElement('img');
            avatar.src = '/assets/images/flor_redonda.svg';
            avatar.alt = 'KASU';
            avatar.className = 'kasu-chat-avatar';
            avatar.width = 26;
            avatar.height = 26;
            avatar.loading = 'lazy';
            avatar.decoding = 'async';
            var bubble = document.createElement('div');
            bubble.className = 'kasu-chat-bubble';
            bubble.innerHTML = html;
            wrapper.appendChild(avatar);
            wrapper.appendChild(bubble);
            chatBody.appendChild(wrapper);
        };

        var showTyping = function () {
            if (typingNode) return;
            typingNode = document.createElement('div');
            typingNode.className = 'kasu-chat-message kasu-chat-message--bot';
            var avatar = document.createElement('img');
            avatar.src = '/assets/images/flor_redonda.svg';
            avatar.alt = 'KASU';
            avatar.className = 'kasu-chat-avatar';
            avatar.width = 26;
            avatar.height = 26;
            avatar.loading = 'lazy';
            avatar.decoding = 'async';
            var bubble = document.createElement('div');
            bubble.className = 'kasu-chat-bubble';
            bubble.textContent = 'Escribiendo...';
            typingNode.appendChild(avatar);
            typingNode.appendChild(bubble);
            chatBody.appendChild(typingNode);
        };

        var hideTyping = function () {
            if (!typingNode) return;
            typingNode.remove();
            typingNode = null;
        };

        chatForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var message = chatInput.value.trim();
            if (!message) return;

            appendUserMessage(message);
            chatBody.scrollTop = chatBody.scrollHeight;
            chatInput.value = '';
            chatInput.disabled = true;
            showTyping();

            var chatToken = localStorage.getItem('kasu_chat_token');
            if (!chatToken) {
                if (window.crypto && window.crypto.randomUUID) {
                    chatToken = window.crypto.randomUUID();
                } else {
                    chatToken = 'kasu_' + Date.now() + '_' + Math.random().toString(16).slice(2);
                }
                localStorage.setItem('kasu_chat_token', chatToken);
            }

            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    mensaje: message,
                    source: window.location.pathname,
                    chat_token: chatToken
                })
            })
            .then(function (resp) { return resp.json(); })
            .then(function (data) {
                hideTyping();
                chatInput.disabled = false;
                if (data && data.chat_token) {
                    localStorage.setItem('kasu_chat_token', data.chat_token);
                }
                if (data && data.ok && data.html) {
                    appendBotMessage(data.html);
                } else {
                    appendBotMessage('No pude procesar tu solicitud. Intenta de nuevo.');
                }
                chatBody.scrollTop = chatBody.scrollHeight;
            })
            .catch(function () {
                hideTyping();
                chatInput.disabled = false;
                appendBotMessage('No pude conectar con el chat. Intenta mas tarde.');
                chatBody.scrollTop = chatBody.scrollHeight;
            });
        });
    }
})();
</script>
