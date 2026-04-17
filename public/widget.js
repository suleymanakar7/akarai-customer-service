document.addEventListener('DOMContentLoaded', function() {
    const widget = document.getElementById('ai-mh-widget');
    const button = document.getElementById('ai-mh-button');
    const chatWindow = document.getElementById('ai-mh-chat-window');
    const closeBtn = document.getElementById('ai-mh-close');
    const startChatBtn = document.getElementById('ai-mh-start-chat');
    const sendBtn = document.getElementById('ai-mh-send');
    const userInput = document.getElementById('ai-mh-user-input');
    const msgContainer = document.getElementById('ai-mh-messages');
    const attentionBubble = document.getElementById('ai-mh-attention-bubble');
    const attentionClose = document.getElementById('ai-mh-attention-close');
    
    // I18n and Global Config
    const config = typeof akarai_cs_obj !== 'undefined' ? akarai_cs_obj : { i18n: {} };
    const i18n = config.i18n || {};

    let currentConvId = null;

    // --- ATTENTION BUBBLE ---
    const hideBubble = () => {
        if (attentionBubble) {
            attentionBubble.classList.remove('ai-mh-bubble-visible');
            attentionBubble.style.display = 'none';
        }
    };

    setTimeout(() => {
        if (chatWindow && (chatWindow.style.display === 'none' || chatWindow.style.display === '')) {
            attentionBubble.classList.add('ai-mh-bubble-visible');
            setTimeout(hideBubble, 8000);
        }
    }, 15000);

    attentionBubble.addEventListener('click', (e) => {
        if (e.target === attentionClose) return;
        hideBubble();
        chatWindow.style.display = 'flex';
    });

    if (attentionClose) {
        attentionClose.addEventListener('click', (e) => {
            e.stopPropagation();
            hideBubble();
        });
    }

    button.onclick = () => {
        hideBubble();
        chatWindow.style.display = chatWindow.style.display === 'none' || chatWindow.style.display === '' ? 'flex' : 'none';
        if (chatWindow.style.display === 'flex' && currentConvId) {
            userInput.focus();
        }
    };
    closeBtn.onclick = () => chatWindow.style.display = 'none';

    // Start Chat (Lead Capture)
    startChatBtn.onclick = async () => {
        const name = document.getElementById('ai-mh-name').value;
        const phone = document.getElementById('ai-mh-phone').value;
        const kvkkAccepted = document.getElementById('ai-mh-kvkk-check').checked;

        // Validation
        if (!name || !phone) return alert(i18n.welcome_lead || 'Please fill in your details.');
        
        // Basic global phone validation
        if (phone.length < 7) {
            return alert(i18n.phone_placeholder || 'Please enter a valid phone number.');
        }

        if (!kvkkAccepted) return alert(i18n.agreement_text || 'Please agree to the privacy policy.');

        startChatBtn.disabled = true;
        startChatBtn.innerText = i18n.start_chat || 'Starting...';

        try {
            const response = await fetch(`${config.rest_url}lead`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, phone, is_kvkk_accepted: 1 })
            });

            if (!response.ok) throw new Error('API request failed');

            const data = await response.json();
            
            if (data.conv_id) {
                currentConvId = data.conv_id;
                document.getElementById('ai-mh-lead-form').style.display = 'none';
                document.getElementById('ai-mh-input-area').style.display = 'block';
                if (data.greeting) {
                    typeMessage(data.greeting, () => {
                        showFAQs();
                    });
                }
                userInput.focus();
            }
        } catch (err) {
            console.error(err);
            alert(i18n.error_msg || 'Connection error.');
        } finally {
            startChatBtn.disabled = false;
        }
    };

    function showFAQs() {
        const faqs = config.faqs ? config.faqs.filter(f => f !== '') : [];
        if (faqs.length === 0) return;

        const div = document.createElement('div');
        div.className = 'ai-mh-msg ai-mh-bot ai-mh-faq-container';
        let html = '<div class="ai-mh-faqs-bubble">';
        html += `<p>${i18n.faq_title || 'Frequently Asked Questions'}</p>`;
        faqs.forEach(faq => {
            html += `<button class="ai-mh-faq-btn-live">${faq}</button>`;
        });
        html += '</div>';
        div.innerHTML = html;
        msgContainer.appendChild(div);
        msgContainer.scrollTop = msgContainer.scrollHeight;

        div.querySelectorAll('.ai-mh-faq-btn-live').forEach(btn => {
            btn.onclick = () => {
                userInput.value = btn.innerText;
                sendMessage();
            };
        });
    }

    // Send Message
    const sendMessage = async () => {
        const message = userInput.value.trim();
        if (!message || !currentConvId) return;

        // Clear live FAQs
        const oldFaqs = document.querySelector('.ai-mh-faq-container');
        if (oldFaqs) oldFaqs.remove();

        userInput.value = '';
        addMessage(message, 'user');
        
        const loadingId = addLoading();

        try {
            const response = await fetch(`${config.rest_url}chat`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ conv_id: currentConvId, message })
            });

            if (!response.ok) throw new Error('API request failed');

            const data = await response.json();
            
            removeLoading(loadingId);
            if (data.reply) {
                typeMessage(data.reply);
            }
        } catch (err) {
            removeLoading(loadingId);
            typeMessage(i18n.error_msg || 'Sorry, I cannot answer right now.');
        }
    };

    sendBtn.onclick = sendMessage;
    userInput.onkeypress = (e) => { if (e.key === 'Enter') sendMessage(); };

    function addMessage(text, role) {
        const div = document.createElement('div');
        div.className = `ai-mh-msg ai-mh-${role}`;
        div.innerHTML = `<div class="ai-mh-bubble">${text}</div>`;
        msgContainer.appendChild(div);
        msgContainer.scrollTop = msgContainer.scrollHeight;
        return div;
    }

    function addLoading() {
        const id = 'loading-' + Date.now();
        const div = document.createElement('div');
        div.id = id;
        div.className = 'ai-mh-msg ai-mh-bot';
        div.innerHTML = `<div class="ai-mh-bubble"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>`;
        msgContainer.appendChild(div);
        msgContainer.scrollTop = msgContainer.scrollHeight;
        return id;
    }

    function removeLoading(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    function typeMessage(text, onComplete = null) {
        const div = document.createElement('div');
        div.className = 'ai-mh-msg ai-mh-bot';
        const bubble = document.createElement('div');
        bubble.className = 'ai-mh-bubble';
        div.appendChild(bubble);
        msgContainer.appendChild(div);
        
        let i = 0;
        const interval = setInterval(() => {
            bubble.textContent += text.charAt(i);
            i++;
            msgContainer.scrollTop = msgContainer.scrollHeight;
            if (i >= text.length) {
                clearInterval(interval);
                if (onComplete) onComplete();
            }
        }, 30);
    }
});
