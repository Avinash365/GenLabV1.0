// Chatbot UI logic
const chatbotIcon = document.getElementById('chatbot-icon');
const chatbotPopup = document.getElementById('chatbot-popup');
const chatbotClose = document.getElementById('chatbot-close');
const chatbotMessages = document.getElementById('chatbot-messages');
const chatbotInputArea = document.getElementById('chatbot-input-area');
const chatbotInput = document.getElementById('chatbot-input');
const chatbotTyping = document.getElementById('chatbot-typing');
var autoBox = document.getElementById('chatbot-autocomplete');
let conversation = [];
const chatbotUserName = (chatbotPopup && chatbotPopup.dataset.userName ? chatbotPopup.dataset.userName.trim() : '') || '';
let welcomeNote = chatbotUserName ? `Hi ${chatbotUserName}! How can I assist you today?` : 'Hello! How can I assist you today?';

// Remove duplicate declarations if any before declaring
// Autocomplete elements & logic
if(!window.__chatbotSuggestInit){
  window.__chatbotSuggestInit = true;
  const suggestionPool = [
    // Invoices
    'How many invoices today?',
    'How many invoices are generated today with the amount?',
    'Total invoices today and total amount',
    'What is the total amount of invoices?',
    'Show total invoice amount this month',
    'Show total invoice amount this year',
    'Sum of all invoices',
    // Bookings overall/timeframes
    'How many bookings today?',
    'How many bookings this week?',
    'How many bookings this month?',
    'How many bookings this year?',
    'How many bookings in all departments today?',
    // Bookings by department
    'How many bookings in department QA today?',
    'How many bookings in department Chemistry this week?',
    'How many bookings in department Microbiology this month?',
    // Bookings by user
    'How many bookings done by user John today?',
    'How many bookings done by user John this week?',
    'How many bookings done by user John this month?',
    'How many bookings done by user John on 2024-05-01?',
    // Bookings by client + timeframe
    'Show me all bookings of client Acme this week',
    'Show me all bookings of client Acme this month',
    'Show me all bookings of client Acme this year',
    'How many bookings of client Acme?',
    // Client letters / ledgers (placeholders for future)
    'How many letters are booked for client Acme this month?',
    'How many letters are booked for client Acme all time?',
    'How many ledgers of client Acme?',
    'Open ledger of marketing John',
    'Open profile of client Acme',
    'Open profile of marketing John',
    // Expenses / payments
    'Open expenses of marketing John',
    'Payment due of company Acme',
    // Job order lookups
    'Status of job order 1234',
    'Status of letter job order 1234',
    'When job order 1234 booked',
    // Marketing / analysts recent
    'Recent bookings of marketing John',
    'Recent bookings of marketer Alice',
    // Profile & attendance
    'Open my profile',
    'Show my attendance',
    // Pending job orders
    'How many job orders are pending in department QA?',
    'How many job orders are pending in department Chemistry?',
    'How many job orders are pending for marketing John?',
    'How many letters are pending for marketing John?',
    // Lab expected dates
    'Which lab expected dates are completed?',
    'Which lab expected dates are pending?',
    // Work done summaries
    'Today work done by computer operator John',
    'Today work done by lab analyst Alice',
    // Products / generic
    'List all products',
    'What products are available?',
    'Who are the marketing persons?',
    'Who are the lab analysts?',
    'How many booking items?',
    'How many quotations?',
    // Generic variants
    'Show bookings this week',
    'Show bookings this month',
    'Show bookings this year'
  ];
  function updateAutocomplete(){
    if(!autoBox) return;
    const v = chatbotInput.value.trim().toLowerCase();
    if(!v){ autoBox.style.display='none'; autoBox.innerHTML=''; return; }
    const matches = suggestionPool.filter(s=>s.toLowerCase().includes(v)).slice(0,6);
    if(matches.length===0){ autoBox.style.display='none'; autoBox.innerHTML=''; return; }
    autoBox.innerHTML = '';
    matches.forEach(text=>{
      const btn = document.createElement('button');
      btn.type='button';
      btn.textContent = text;
      btn.onclick = ()=>{ 
        chatbotInput.value = text; 
        if(autoBox) autoBox.style.display='none'; 
        // Auto-submit the filled suggestion
        if (typeof chatbotInputArea.onsubmit === 'function') {
          chatbotInputArea.onsubmit(new Event('submit'));
        } else {
          chatbotInputArea.dispatchEvent(new Event('submit'));
        }
      };
      autoBox.appendChild(btn);
    });
    autoBox.style.display='flex';
  }
  chatbotInput.addEventListener('input', updateAutocomplete);
  chatbotInput.addEventListener('focus', updateAutocomplete);
  document.addEventListener('click', (e)=>{ if(autoBox && !autoBox.contains(e.target) && e.target!==chatbotInput){ autoBox.style.display='none'; }});
  chatbotInputArea.addEventListener('submit', ()=>{ if(autoBox) autoBox.style.display='none'; });
}

// Common intents for suggestions
const suggestionPool = [
  'How many invoices today?',
  'How many invoices are generated today with the amount?',
  'Total invoices today and total amount',
  'What is the total amount of invoices?',
  'Show total invoice amount this month',
  'Show total invoice amount this year',
  'Sum of all invoices',
  'How many bookings today?',
  'How many bookings this week?',
  'How many bookings this month?',
  'How many bookings this year?',
  'How many bookings in all departments today?',
  'How many bookings in department QA today?',
  'How many bookings in department Chemistry this week?',
  'How many bookings in department Microbiology this month?',
  'How many bookings done by user John today?',
  'How many bookings done by user John this week?',
  'How many bookings done by user John this month?',
  'How many bookings done by user John on 2024-05-01?',
  'Show me all bookings of client Acme this week',
  'Show me all bookings of client Acme this month',
  'Show me all bookings of client Acme this year',
  'How many bookings of client Acme?',
  'How many letters are booked for client Acme this month?',
  'How many letters are booked for client Acme all time?',
  'How many ledgers of client Acme?',
  'Open ledger of marketing John',
  'Open profile of client Acme',
  'Open profile of marketing John',
  'Open expenses of marketing John',
  'Payment due of company Acme',
  'Status of job order 1234',
  'Status of letter job order 1234',
  'When job order 1234 booked',
  'Recent bookings of marketing John',
  'Recent bookings of marketer Alice',
  'Open my profile',
  'Show my attendance',
  'How many job orders are pending in department QA?',
  'How many job orders are pending in department Chemistry?',
  'How many job orders are pending for marketing John?',
  'How many letters are pending for marketing John?',
  'Which lab expected dates are completed?',
  'Which lab expected dates are pending?',
  'Today work done by computer operator John',
  'Today work done by lab analyst Alice',
  'List all products',
  'What products are available?',
  'Who are the marketing persons?',
  'Who are the lab analysts?',
  'How many booking items?',
  'How many quotations?',
  'Show bookings this week',
  'Show bookings this month',
  'Show bookings this year'
];

function showTyping(show){
    if (!chatbotTyping) return;
    chatbotTyping.style.display = show ? 'flex' : 'none';
}

chatbotIcon.onclick = () => {
    if (window.__CHATBOT_SUPPRESS_CLICK) { window.__CHATBOT_SUPPRESS_CLICK = false; return; }
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
        if (msg.sender === 'bot') {
            // Basic sanitization: strip script tags
            const safe = msg.text.replace(/<script/ig,'&lt;script').replace(/<\/script>/ig,'')
            bubble.innerHTML = safe;
        } else {
            bubble.textContent = msg.text;
        }
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

// Draggable chatbot icon with localStorage persistence
(function(){
  if(!chatbotIcon) return;
  const storageKey = 'chatbot.icon.pos';
  function loadPos(){ try { const raw = localStorage.getItem(storageKey); if(!raw) return; const p = JSON.parse(raw); if(p && typeof p.x==='number' && typeof p.y==='number'){ applyPos(p.x,p.y); } } catch(e){} }
  function applyPos(x,y){
    const pad = 8; const w = chatbotIcon.offsetWidth || 56; const h = chatbotIcon.offsetHeight || 56; const vw = window.innerWidth; const vh = window.innerHeight;
    x = Math.min(vw - w - pad, Math.max(pad, x));
    y = Math.min(vh - h - pad, Math.max(pad, y));
    chatbotIcon.style.left = x + 'px'; chatbotIcon.style.top = y + 'px'; chatbotIcon.style.right = 'auto'; chatbotIcon.style.bottom = 'auto';
  }
  function savePos(x,y){ try { localStorage.setItem(storageKey, JSON.stringify({x,y})); } catch(e){} }
  let dragging = false; let startX=0, startY=0, origX=0, origY=0; let moved=false; const THRESH=5;
  function pointerDown(e){ if(chatbotIcon.classList.contains('hidden')) return; dragging = true; moved=false; chatbotIcon.classList.add('dragging'); const rect = chatbotIcon.getBoundingClientRect(); origX = rect.left; origY = rect.top; startX = (e.touches? e.touches[0].clientX : e.clientX); startY = (e.touches? e.touches[0].clientY : e.clientY); e.preventDefault(); }
  function pointerMove(e){ if(!dragging) return; const cx = (e.touches? e.touches[0].clientX : e.clientX); const cy = (e.touches? e.touches[0].clientY : e.clientY); const dx = cx - startX; const dy = cy - startY; if (Math.abs(dx) > THRESH || Math.abs(dy) > THRESH) moved = true; applyPos(origX + dx, origY + dy); }
  function pointerUp(){ if(!dragging) return; dragging = false; chatbotIcon.classList.remove('dragging'); const rect = chatbotIcon.getBoundingClientRect(); savePos(rect.left, rect.top); if (moved) { window.__CHATBOT_SUPPRESS_CLICK = true; } }
  chatbotIcon.addEventListener('mousedown', pointerDown); chatbotIcon.addEventListener('touchstart', pointerDown, {passive:false});
  window.addEventListener('mousemove', pointerMove); window.addEventListener('touchmove', pointerMove, {passive:false});
  window.addEventListener('mouseup', pointerUp); window.addEventListener('touchend', pointerUp);
  window.addEventListener('resize', ()=>{ const rect = chatbotIcon.getBoundingClientRect(); applyPos(rect.left, rect.top); });
  loadPos();
})();

// Draggable popup (header as handle) with persistence
(function(){
  const popup = document.getElementById('chatbot-popup');
  const header = document.getElementById('chatbot-header');
  if(!popup || !header) return;
  const key = 'chatbot.popup.pos';
  function apply(x,y){
    const pad=8; const vw=window.innerWidth; const vh=window.innerHeight;
    const w = popup.offsetWidth; const h = popup.offsetHeight;
    x=Math.min(vw-w-pad, Math.max(pad,x));
    y=Math.min(vh-h-pad, Math.max(pad,y));
    popup.style.left = x+'px';
    popup.style.top = y+'px';
    popup.style.right='auto'; popup.style.bottom='auto';
  }
  function load(){ try{ const p=JSON.parse(localStorage.getItem(key)); if(p && typeof p.x==='number'){ apply(p.x,p.y); } }catch(e){} }
  function save(x,y){ try{ localStorage.setItem(key, JSON.stringify({x,y})); }catch(e){} }
  let drag=false,startX=0,startY=0,origX=0,origY=0;
  function down(e){ if(!popup.classList.contains('open')) return; drag=true; popup.classList.add('dragging'); const r=popup.getBoundingClientRect(); origX=r.left; origY=r.top; startX=(e.touches?e.touches[0].clientX:e.clientX); startY=(e.touches?e.touches[0].clientY:e.clientY); e.preventDefault(); }
  function move(e){ if(!drag) return; const cx=(e.touches?e.touches[0].clientX:e.clientX); const cy=(e.touches?e.touches[0].clientY:e.clientY); apply(origX + (cx-startX), origY + (cy-startY)); }
  function up(){ if(!drag) return; drag=false; popup.classList.remove('dragging'); const r=popup.getBoundingClientRect(); save(r.left,r.top); }
  header.addEventListener('mousedown', down); header.addEventListener('touchstart', down, {passive:false});
  window.addEventListener('mousemove', move); window.addEventListener('touchmove', move, {passive:false});
  window.addEventListener('mouseup', up); window.addEventListener('touchend', up);
  window.addEventListener('resize', ()=>{ const r=popup.getBoundingClientRect(); apply(r.left,r.top); });
  load();
})();
