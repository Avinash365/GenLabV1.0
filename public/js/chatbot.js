// Chatbot UI logic
const chatbotIcon = document.getElementById('chatbot-icon');
const chatbotPopup = document.getElementById('chatbot-popup');
const chatbotClose = document.getElementById('chatbot-close');
const chatbotMessages = document.getElementById('chatbot-messages');
const chatbotInputArea = document.getElementById('chatbot-input-area');
const chatbotInput = document.getElementById('chatbot-input');
const chatbotTyping = document.getElementById('chatbot-typing');
let conversation = [];
let welcomeNote = 'Hello! How can I assist you today?';

function showTyping(show){
    if (!chatbotTyping) return;
    chatbotTyping.style.display = show ? 'flex' : 'none';
}

chatbotIcon.onclick = () => {
    chatbotPopup.classList.add('open');
    chatbotIcon.classList.add('hidden');
    chatbotInput.focus();
    if (conversation.length === 0) {
        conversation.push({ sender: 'bot', text: welcomeNote });
    }
    renderMessages();
};
chatbotClose.onclick = () => {
    chatbotPopup.classList.remove('open');
    chatbotIcon.classList.remove('hidden');
};
function renderMessages() {
    chatbotMessages.innerHTML = '';
    conversation.forEach(msg => {
        const div = document.createElement('div');
        div.className = 'chatbot-msg ' + msg.sender;
        const bubble = document.createElement('div');
        bubble.className = 'chatbot-bubble';
        bubble.textContent = msg.text;
        div.appendChild(bubble);
        chatbotMessages.appendChild(div);
    });
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
}
function getCsrfToken() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}
function getBaseUrl(){
    var meta = document.querySelector('meta[name="app-url"]');
    return meta ? meta.getAttribute('content').replace(/\/$/, '') : (location.origin || '');
}
chatbotInputArea.onsubmit = function(e) {
    e.preventDefault();
    const question = chatbotInput.value.trim();
    if (!question) return;
    document.getElementById('chatbot-send').classList.add('sent');
    setTimeout(()=>document.getElementById('chatbot-send').classList.remove('sent'), 220);
    conversation.push({ sender: 'user', text: question });
    renderMessages();
    chatbotInput.value = '';
    showTyping(true);
    // AJAX to backend
    fetch(getBaseUrl() + '/chatbot/query', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: JSON.stringify({ question })
    })
    .then(res => { if(!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
    .then(data => {
        showTyping(false);
        conversation.push({ sender: 'bot', text: data.answer });
        renderMessages();
    })
    .catch((err) => {
        showTyping(false);
        conversation.push({ sender: 'bot', text: 'Sorry, I couldn\'t find that information. Please contact support.' });
        renderMessages();
        try { console.error('Chatbot request failed:', err); } catch(_) {}
    });
};
document.querySelectorAll('.chatbot-suggestion').forEach(function(el) {
    el.onclick = function() {
        chatbotInput.value = el.textContent;
        chatbotInputArea.onsubmit(new Event('submit'));
    };
});
// Close when clicking outside the popup
document.addEventListener('click', function(e){
    if (!chatbotPopup.classList.contains('open')) return;
    var target = e.target;
    var clickedInsidePopup = chatbotPopup.contains(target);
    var clickedIcon = chatbotIcon.contains(target);
    if (!clickedInsidePopup && !clickedIcon) {
        chatbotPopup.classList.remove('open');
        chatbotIcon.classList.remove('hidden');
    }
});
