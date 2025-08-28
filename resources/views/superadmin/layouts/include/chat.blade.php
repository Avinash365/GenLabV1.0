<!-- Floating Chat Popup (replaces offcanvas) -->
<div id="chatPopup" class="chat-popup" style="display:none;">
    <div class="chat-popup-header">
        <div class="d-flex align-items-center" style="gap:10px;">
            <i class="fa fa-envelope" style="font-size:18px;"></i>
            <h6 class="m-0">Inbox</h6>
        </div>
        <div class="d-flex align-items-center" style="gap:8px;">
            <button id="chatExpandBtn" class="btn btn-light btn-sm" title="Expand"><i class="fa fa-expand"></i></button>
            <button id="chatCloseBtn" class="btn btn-light btn-sm" title="Close"><i class="fa fa-times"></i></button>
        </div>
    </div>
    <div class="chat-popup-body p-0 position-relative" style="height:100%; display:flex;">
        <!-- Sidebar and Conversation reused -->
        <aside class="border-end" style="width:360px; display:flex; flex-direction:column; min-height:0;">
            <div class="p-2" style="background:#f9fafb;">
                <div class="input-group input-group-sm" style="border-radius:8px; overflow:hidden;">
                    <span class="input-group-text bg-white border-0"><i class="fa fa-search text-muted"></i></span>
                    <input id="chatGroupSearch" type="text" class="form-control border-0" placeholder="Search or start new chat" style="background:#fff;">
                </div>
            </div>
            <div id="chatGroups" class="list-group list-group-flush" style="overflow:auto; flex:1;"></div>
        </aside>
        <section class="flex-grow-1 d-flex flex-column" style="min-width:0; min-height:0;">
            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                <div class="d-flex align-items-center" style="gap:12px;">
                    <div id="chatActiveAvatar" class="rounded-circle d-flex align-items-center justify-content-center">G</div>
                    <div>
                        <div id="chatActiveTitle">Select a group</div>
                        <div class="text-muted small" id="chatActiveSubtitle">Messages are visible to everyone. Reactions only visible to sender.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap:10px; color:#54656f;">
                    <i class="fa fa-search"></i>
                    <i class="fa fa-ellipsis-v"></i>
                </div>
            </div>
            <div id="chatMessages" class="flex-grow-1 position-relative wa-wallpaper" style="overflow:auto; padding:24px 24px 12px;">
                <div id="chatEmptyState" class="h-100 w-100 d-flex align-items-center justify-content-center text-center text-muted">
                    <div>
                        <i class="fa fa-comments mb-2" style="font-size:38px; color:#94a3b8;"></i>
                        <div class="fw-semibold">No messages yet</div>
                        <div class="small">Start the conversation by sending a message.</div>
                    </div>
                </div>
                <div id="chatLoading" class="position-absolute top-0 start-50 translate-middle-x mt-3" style="display:none;">
                    <div class="spinner-border spinner-border-sm text-success" role="status"></div>
                </div>
            </div>
            <div class="px-3 py-2">
                <div id="chatInputArea" class="d-flex align-items-center" style="gap:8px; display:none;">
                    <button id="emojiBtn" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Emoji" style="width:36px;height:36px;"><i class="fa fa-smile-o"></i></button>
                    <button id="attachBtn" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Attach" style="width:36px;height:36px;"><i class="fa fa-paperclip"></i></button>
                    <input id="fileInput" type="file" accept="image/*,application/pdf,audio/*" style="display:none;" />
                    <input type="text" id="chatMessageInput" class="form-control shadow-sm" placeholder="Type a message" style="border-radius:20px;">
                    <button id="voiceBtn" class="btn btn-success btn-sm rounded-circle shadow-sm" title="Record voice" style="width:36px;height:36px;"><i class="fa fa-microphone"></i></button>
                    <button id="sendMessageBtn" class="btn btn-success btn-sm rounded-circle shadow-sm" title="Send" style="width:36px;height:36px; display:none;"><i class="fa fa-paper-plane"></i></button>
                </div>
                <div id="recordingHint" class="text-danger small mt-1" style="display:none;">Recording... tap mic to stop</div>
            </div>
        </section>
    </div>
</div>

<style>
/* ====== Professional Chat Box Styling ====== */
:root {
  --chat-primary: #00a884;
  --chat-primary-dark: #008069;
  --chat-bg: #f9fafb;
  --chat-sidebar-bg: #ffffff;
  --chat-border: #e5e7eb;
  --chat-msg-in: #ffffff;
  --chat-msg-out: #dcf8c6;
  --chat-muted: #6b7280;
  --chat-radius: 12px;
  --chat-shadow: 0 1px 3px rgba(238, 232, 232, 0.08);
}

/* Floating popup container */
.chat-popup{
  position:fixed;
  right: max(20px, env(safe-area-inset-right));
  bottom: max(20px, env(safe-area-inset-bottom));
  width: min(520px, calc(100vw - 40px));
  height: min(640px, calc(100dvh - 40px));
  background:#fff;
  border:1px solid var(--chat-border);
  border-radius:12px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.18);
  display:flex; flex-direction:column;
  z-index:1032 !important;
  overflow:hidden;
}
.chat-popup-header{ background: var(--chat-primary); color:#fff; padding:8px 12px; display:flex; align-items:center; justify-content:space-between; }
/* Expanded styling driven by JS-calculated bounds (no full-viewport sizing here) */
.chat-popup.expanded{ border-radius:0; box-shadow:none; }

@media (max-width: 576px){
  .chat-popup{ right:10px; bottom:10px; width: calc(100vw - 20px); height: calc(100dvh - 20px); border-radius:10px; }
}

/* Offcanvas */
#chatOffcanvas .offcanvas-header {
  background: var(--chat-primary);
  color: #fff;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}
#chatOffcanvas .offcanvas-body {
  background: var(--chat-bg);
  font-family: "Inter", "Segoe UI", sans-serif;
}

/* Sidebar */
#chatGroups {
  background: var(--chat-sidebar-bg);
}
#chatGroups .list-group-item {
  border: none;
  border-bottom: 1px solid var(--chat-border);
  padding: 12px 16px;
  transition: background 0.2s;
}
#chatGroups .list-group-item:hover {
  background: #f3f4f6;
}
#chatGroups .list-group-item.active {
  background: #e6f4ea;
  font-weight: 600;
  color: var(--chat-primary-dark);
}

/* Header */
#chatOffcanvas .border-bottom {
  background: #f8fafc;
  border-color: var(--chat-border);
}
#chatActiveAvatar {
  width: 40px;
  height: 40px;
  background: #d1d5db;
  color: #111827;
  border-radius: 50%;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}
#chatActiveTitle {
  font-size: 15px;
  font-weight: 600;
}
#chatActiveSubtitle {
  font-size: 12px;
  color: var(--chat-muted);
}

/* Messages */
.wa-wallpaper {
  background: #f9fafb;
}
.wa-row {
  display: flex;
  margin: 8px 0;
  gap: 8px;
}
.wa-row.sent { justify-content: flex-end; }
.wa-avatar {
  width: 32px;
  height: 32px;
  background: #d1d5db;
  color: #111827;
  border-radius: 50%;
  font-size: 13px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
}
.wa-bubble {
  position: relative;
  max-width: 70%;
  padding: 12px 14px;
  border-radius: var(--chat-radius);
  font-size: 14px;
  line-height: 1.4;
  box-shadow: var(--chat-shadow);
}
.wa-bubble.sent {
  background: var(--chat-msg-out);
  border-top-right-radius: 4px;
}
.wa-bubble.received {
  background: var(--chat-msg-in);
  border-top-left-radius: 4px;
}
.wa-content { display: flex; flex-direction: column; gap: 8px; }
.wa-text { white-space: pre-wrap; word-wrap: break-word; color:#111827; }
.wa-image img { max-width: 100%; border-radius: 8px; display:block; }
.wa-caption { font-size: 13px; color:#334155; }
.wa-file { display:flex; align-items:center; gap:12px; }
.wa-file .wa-icon { width:40px; height:40px; border-radius:8px; background:#fee2e2; color:#dc2626; display:flex; align-items:center; justify-content:center; }
.wa-file .wa-info { display:flex; flex-direction:column; }
.wa-file .wa-name { font-weight:600; color:#111827; line-height:1.2; }
.wa-file .wa-actions { display:flex; gap:10px; font-size:12px; }
.wa-file .wa-actions a { color: var(--chat-primary-dark); text-decoration:none; }
.wa-audio audio { width: 100%; max-width: 320px; height: 36px; }
/* Meta row below content (not overlapping) */
.wa-meta-row { margin-top: 6px; font-size: 12px; color: var(--chat-muted); display:flex; align-items:center; gap:6px; }
.wa-row.sent .wa-meta-row { justify-content:flex-end; }
.wa-row.received .wa-meta-row { justify-content:flex-start; }
.wa-meta {
  position: absolute;
  right: 8px;
  bottom: 4px;
  font-size: 11px;
  color: var(--chat-muted);
  display: flex;
  align-items: center;
  gap: 4px;
}

/* Input */
#chatInputArea {
  background: #fff;
  border-radius: 24px;
  padding: 6px 10px;
  box-shadow: var(--chat-shadow);
}
#chatMessageInput {
  border: none;
  background: transparent;
  outline: none;
  font-size: 14px;
}
#chatMessageInput:focus { box-shadow: none; }

/* Buttons */
#chatInputArea button {
  border: none;
  transition: background 0.2s;
}
#chatInputArea button:hover { background: #f3f4f6 !important; }
#sendMessageBtn,
#voiceBtn {
  background: var(--chat-primary) !important;
  border-color: var(--chat-primary) !important;
  color: #fff;
}
#sendMessageBtn:hover,
#voiceBtn:hover {
  background: var(--chat-primary-dark) !important;
}

/* Reactions */
.reaction-chip {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 2px 8px;
  font-size: 12px;
  margin-left: 4px;
}

/* Override in expanded mode: layout handled via JS bounds on main body */
.chat-popup.expanded { box-shadow: none; }
/* Stacking fix to ensure chat is above content but below modals */
.chat-popup{ z-index: 1032 !important; }
/* Ensure composer remains visible and layout can shrink properly */
.chat-popup-body { min-height: 0; }
.chat-popup-body > section { min-height: 0; }
#chatInputArea { flex-shrink: 0; position: sticky; bottom: 0; background:#fff; z-index: 2; }
/* Add bottom space so last messages aren't hidden behind composer */
#chatMessages { padding-bottom: calc(96px + env(safe-area-inset-bottom)) !important; }
</style>
<script>
(function(){
    const csrfToken = '{{ csrf_token() }}';
    @php $authUser = auth('web')->user() ?: auth('admin')->user(); @endphp
    const currentUser = { id: {{ $authUser ? (int)$authUser->id : 'null' }}, name: @json($authUser->name ?? 'Guest') };
    const routes = {
        groups: '{{ url('/chat/groups') }}',
        messages: '{{ url('/chat/messages') }}',
        messagesSince: '{{ url('/chat/messages/since') }}',
        send: '{{ url('/chat/messages') }}',
        react: (id) => `${'{{ url('/chat/messages') }}'}/${id}/reactions`
    };

    // Elements
    const groupsEl = document.getElementById('chatGroups');
    const searchEl = document.getElementById('chatGroupSearch');
    const messagesEl = document.getElementById('chatMessages');
    const emptyEl = document.getElementById('chatEmptyState');
    const loadingEl = document.getElementById('chatLoading');
    const inputAreaEl = document.getElementById('chatInputArea');
    const msgInput = document.getElementById('chatMessageInput');
    const sendBtn = document.getElementById('sendMessageBtn');
    const fileInput = document.getElementById('fileInput');
    const attachBtn = document.getElementById('attachBtn');
    const voiceBtn = document.getElementById('voiceBtn');
    const recordingHint = document.getElementById('recordingHint');
    const emojiBtn = document.getElementById('emojiBtn');
    const activeTitle = document.getElementById('chatActiveTitle');
    const activeAvatar = document.getElementById('chatActiveAvatar');
    const popupEl = document.getElementById('chatPopup');
    // Re-parent to <body> to avoid clipping/stacking issues from ancestors
    if (popupEl && popupEl.parentElement !== document.body) {
        document.body.appendChild(popupEl);
    }
    // Restore refs for controls
    const expandBtn = document.getElementById('chatExpandBtn');
    const closeBtn = document.getElementById('chatCloseBtn');
    const chatToggleBtn = document.getElementById('chatToggle');

    // Persist chat UI state (open/expanded)
    const CHAT_STATE_KEY = 'chat.ui.state';
    const getState = () => { try { return JSON.parse(localStorage.getItem(CHAT_STATE_KEY) || '{}'); } catch { return {}; } };
    const setState = (patch) => { const next = Object.assign({}, getState(), patch); localStorage.setItem(CHAT_STATE_KEY, JSON.stringify(next)); };

    // State
    let activeGroupId = null; let lastMessageId = 0;
    let mediaRecorder = null; let recordedChunks = [];
    let cache = []; let allGroups = [];

    const initials = (s)=> (s||'?').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();
    const fmtTime = (ts)=> { try { const d = new Date(ts); return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}); } catch(e){ return ts; } };

    // Groups
    function renderGroups(list){
        groupsEl.innerHTML = '';
        list.forEach(g => {
            const item = document.createElement('a');
            item.href = '#'; item.className = 'list-group-item d-flex align-items-center';
            item.dataset.groupId = g.id; item.dataset.groupName = g.name;
            item.innerHTML = `<div class="wa-avatar me-2">${initials(g.name)}</div>
                              <div class="flex-grow-1">
                                <div class="fw-semibold">${g.name}</div>
                                <div class="small text-muted">Everyone can view</div>
                              </div>
                              <div class="text-muted small">&nbsp;</div>`;
            item.addEventListener('click', (e)=>{ e.preventDefault(); selectGroup(g.id, g.name, item); });
            groupsEl.appendChild(item);
        });
    }

    function selectGroup(id, name, node){
        groupsEl.querySelectorAll('.list-group-item').forEach(n=>n.classList.remove('active'));
        if (node) node.classList.add('active');
        activeGroupId = id; activeTitle.textContent = name; activeAvatar.textContent = initials(name);
        inputAreaEl.style.display = 'flex';
        lastMessageId = 0; cache = []; messagesEl.querySelectorAll('.wa-row, .reaction-chip').forEach(n=>n.remove()); emptyEl.style.display = 'flex';
        fetchMessages(id);
    }

    // Robust text extractor: scans common keys, regex-matching keys, containers, and arrays; ignores numeric-only strings
    function bestText(m){
        if (!m || typeof m !== 'object') return '';
        const primary = ['content','message','body','text','description','caption','title','name','msg','value','content_text'];
        const containers = ['data','attributes','payload','meta','details','info'];
        const ignore = new Set(['id','user','group','group_id','created_at','updated_at','file_url','type','message_type','mime','mimetype','file_mime','content_type','original_name','file_name']);
        const likeText = /(text|message|content|caption|body|desc|title)/i;
        const seen = new Set();
        const isUseful = (s)=> typeof s === 'string' && !!s.trim() && !/^\d+$/.test(s.trim());
        function scan(val, depth){
            if (val == null || depth > 5) return '';
            if (typeof val === 'string') return isUseful(val) ? val.trim() : '';
            if (typeof val !== 'object') return '';
            if (seen.has(val)) return '';
            seen.add(val);
            // 1) Common text keys
            for (const k of primary){ if (k in val){ const got = scan(val[k], depth+1); if (got) return got; } }
            // 2) Keys that look like text
            for (const k in val){ if (ignore.has(k)) continue; if (likeText.test(k)){ const got = scan(val[k], depth+1); if (got) return got; } }
            // 3) Containers
            for (const k of containers){ if (k in val){ const got = scan(val[k], depth+1); if (got) return got; } }
            // 4) Any other string fields
            for (const k in val){ if (ignore.has(k)) continue; const v = val[k]; if (typeof v === 'string' && isUseful(v)) return v.trim(); }
            // 5) Arrays and nested objects
            if (Array.isArray(val)){
                for (const el of val){ const got = scan(el, depth+1); if (got) return got; }
            } else {
                for (const k in val){ if (ignore.has(k)) continue; const got = scan(val[k], depth+1); if (got) return got; }
            }
            return '';
        }
        return scan(m, 0);
    }

    // Messages
    function messageRow(m){
        const mine = currentUser.id && m.user && m.user.id === currentUser.id;
        const row = document.createElement('div'); row.className = 'wa-row ' + (mine ? 'sent' : 'received');
        const avatar = document.createElement('div'); avatar.className='wa-avatar'; avatar.textContent= initials(m.user?.name || 'U');
        const bubble = document.createElement('div'); bubble.className = 'wa-bubble ' + (mine ? 'sent' : 'received');

        const content = document.createElement('div'); content.className = 'wa-content';
        const type = (m.type || m.message_type || '').toString().toLowerCase();
        const original = (m.original_name || m.file_name || m.name || '').toString();
        const mime = (m.mime || m.mimetype || m.file_mime || m.content_type || '').toString().toLowerCase();
        const hasFile = !!m.file_url;
        const textValue = bestText(m);

        const isImage = type === 'image' || (hasFile && mime.startsWith('image/'));
        const isPdf = type === 'pdf' || (hasFile && (mime === 'application/pdf' || /\.pdf$/i.test(original)));
        const isAudio = type === 'voice' || type === 'audio' || (hasFile && mime.startsWith('audio/'));

        if (type === 'text' || (!hasFile && textValue)){
            const t = document.createElement('div'); t.className = 'wa-text'; t.textContent = textValue || '';
            content.appendChild(t);
        } else if (isImage){
            const wrap = document.createElement('div'); wrap.className = 'wa-image';
            const img = document.createElement('img'); img.src = m.file_url; img.alt = original || 'image'; wrap.appendChild(img);
            content.appendChild(wrap);
            if (textValue){ const cap = document.createElement('div'); cap.className='wa-caption'; cap.textContent = textValue; content.appendChild(cap); }
        } else if (isPdf){
            const file = document.createElement('div'); file.className='wa-file';
            const icon = document.createElement('div'); icon.className='wa-icon'; icon.innerHTML = '<i class="fa fa-file-pdf-o"></i>';
            const info = document.createElement('div'); info.className='wa-info';
            const name = document.createElement('div'); name.className='wa-name'; name.textContent = original || 'Document.pdf';
            const actions = document.createElement('div'); actions.className='wa-actions';
            const view = document.createElement('a'); view.href = m.file_url; view.target='_blank'; view.textContent='View';
            const dl = document.createElement('a'); dl.href = m.file_url; dl.download = original || 'document.pdf'; dl.textContent='Download';
            actions.appendChild(view); actions.appendChild(dl);
            info.appendChild(name); if (textValue){ const cap=document.createElement('div'); cap.className='wa-caption'; cap.textContent=textValue; info.appendChild(cap); }
            info.appendChild(actions);
            file.appendChild(icon); file.appendChild(info);
            content.appendChild(file);
        } else if (isAudio){
            const wrap = document.createElement('div'); wrap.className='wa-audio';
            const audio = document.createElement('audio'); audio.controls = true; audio.src = m.file_url; wrap.appendChild(audio);
            content.appendChild(wrap);
            if (textValue){ const cap = document.createElement('div'); cap.className='wa-caption'; cap.textContent = textValue; content.appendChild(cap); }
        } else {
            const t = document.createElement('div'); t.className = 'wa-text';
            t.textContent = textValue || '[Unsupported message]';
            content.appendChild(t);
        }

        bubble.appendChild(content);

        const meta = document.createElement('div'); meta.className = 'wa-meta-row';
        const time = document.createElement('span'); time.textContent = fmtTime(m.created_at); meta.appendChild(time);
        if (mine){ const check = document.createElement('i'); check.className = 'fa fa-check text-muted'; meta.appendChild(check); }
        const reactBtn = document.createElement('button'); reactBtn.className='btn btn-link btn-sm p-0 ms-1'; reactBtn.textContent='React'; reactBtn.addEventListener('click', (e)=> showEmojiPicker(e.currentTarget, m.id));
        meta.appendChild(reactBtn);
        bubble.appendChild(meta);

        if (mine){ row.appendChild(bubble); row.appendChild(avatar); } else { row.appendChild(avatar); row.appendChild(bubble); }

        if (m.reactions && m.reactions.length){
            const counts = m.reactions.reduce((acc,r)=>{ acc[r.type]=(acc[r.type]||0)+1; return acc;},{});
            const rx = document.createElement('div'); rx.className='mt-1 d-flex ' + (mine ? 'justify-content-end' : 'justify-content-start');
            Object.entries(counts).forEach(([k,v])=>{ const chip = document.createElement('span'); chip.className='reaction-chip'; chip.textContent=`${k} ${v}`; rx.appendChild(chip); });
            row.appendChild(rx);
        }
        return row;
    }

    function renderMessages(list){
        messagesEl.querySelectorAll('.wa-row, .reaction-chip').forEach(n=>n.remove());
        if (!list.length){ emptyEl.style.display = 'flex'; return; }
        emptyEl.style.display = 'none';
        list.forEach(m => messagesEl.appendChild(messageRow(m)));
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // Emoji picker for reactions
    function showEmojiPicker(anchor, messageId){
        const picker = document.createElement('div'); picker.className = 'border rounded bg-white p-1 shadow position-absolute'; picker.style.zIndex=1080;
        const emojis = ['👍','❤️','😂','🎉','😮','😢'];
        emojis.forEach(em => { const b=document.createElement('button'); b.className='btn btn-light btn-sm'; b.textContent=em; b.addEventListener('click', ()=>{ reactToMessage(messageId, em).then(()=>{ picker.remove(); if (activeGroupId) fetchMessages(activeGroupId); }); }); picker.appendChild(b); });
        document.body.appendChild(picker);
        const rect = anchor.getBoundingClientRect(); picker.style.top = (window.scrollY + rect.bottom + 6) + 'px'; picker.style.left = (window.scrollX + rect.left) + 'px';
        const closer=(e)=>{ if(!picker.contains(e.target)){ picker.remove(); document.removeEventListener('mousedown', closer);} }; document.addEventListener('mousedown', closer);
    }

    // Backend
    async function fetchGroups(){
        const res = await fetch(routes.groups, { headers: { 'Accept':'application/json' } });
        allGroups = await res.json(); renderGroups(allGroups);
        const first = groupsEl.querySelector('.list-group-item'); if (first) selectGroup(first.dataset.groupId, first.dataset.groupName, first);
    }

    async function fetchMessages(groupId){
        loadingEl.style.display = 'block';
        const url = new URL(routes.messages, window.location.origin); url.searchParams.set('group_id', groupId);
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
        const data = await res.json();
        cache = Array.isArray(data) ? data : []; lastMessageId = cache.length ? cache[cache.length-1].id : 0;
        renderMessages(cache); loadingEl.style.display = 'none';
    }

    async function poll(){
        if (!activeGroupId || lastMessageId === null) return;
        const url = new URL(routes.messagesSince, window.location.origin);
        url.searchParams.set('group_id', activeGroupId); url.searchParams.set('after_id', lastMessageId);
        const res = await fetch(url, { headers: { 'Accept':'application/json' } });
        const data = await res.json();
        if (Array.isArray(data) && data.length){ cache = cache.concat(data); lastMessageId = cache[cache.length-1].id; renderMessages(cache); }
    }

    async function sendMessage(fd){
        const res = await fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken }, body: fd });
        if (!res.ok){ alert('Failed to send'); return; }
        await res.json(); fetchMessages(activeGroupId);
    }

    async function reactToMessage(messageId, emoji){
        return fetch(routes.react(messageId), { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify({ type: emoji }) });
    }

    // UI events
    searchEl.addEventListener('input', ()=>{ const q = searchEl.value.toLowerCase(); const filtered = allGroups.filter(g=> g.name.toLowerCase().includes(q)); renderGroups(filtered); });
    attachBtn.addEventListener('click', ()=> fileInput.click());
    fileInput.addEventListener('change', ()=>{
        if (!activeGroupId) return; const f = fileInput.files[0]; if (!f) return;
        const kind = f.type.startsWith('image/') ? 'image' : (f.type === 'application/pdf' ? 'pdf' : (f.type.startsWith('audio/') ? 'voice' : 'file'));
        if (kind === 'file'){ alert('Unsupported file type.'); fileInput.value=''; return; }
        const fd = new FormData(); fd.append('group_id', activeGroupId); fd.append('type', kind); fd.append('file', f);
        sendMessage(fd); fileInput.value='';
    });
    sendBtn.addEventListener('click', ()=>{
        if (!activeGroupId) return; const text = (msgInput.value||'').trim(); if (!text) { msgInput.focus(); return; }
        const fd = new FormData(); fd.append('group_id', activeGroupId); fd.append('type','text'); fd.append('content', text);
        sendMessage(fd); msgInput.value=''; toggleSendMic();
    });

    // Emoji insert into composer
    emojiBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        const menu = document.createElement('div'); menu.className='border rounded bg-white p-2 shadow position-absolute';
        const list = ['😀','😂','😍','👍','🎉','😮','😢','🔥','🙏'];
        list.forEach(em=>{ const b=document.createElement('button'); b.className='btn btn-light btn-sm'; b.textContent=em; b.addEventListener('click', ()=>{ msgInput.value = (msgInput.value||'') + em; toggleSendMic(); menu.remove(); msgInput.focus(); }); menu.appendChild(b); });
        document.body.appendChild(menu);
        const rect = emojiBtn.getBoundingClientRect(); menu.style.top = (window.scrollY + rect.bottom + 6) + 'px'; menu.style.left = (window.scrollX + rect.left) + 'px';
        const closer=(ev)=>{ if(!menu.contains(ev.target)){ menu.remove(); document.removeEventListener('mousedown', closer);} }; document.addEventListener('mousedown', closer);
    });

    // Mic/Send toggle
    function toggleSendMic(){ const hasText = (msgInput.value||'').trim().length > 0; sendBtn.style.display = hasText ? 'inline-flex' : 'none'; voiceBtn.style.display = hasText ? 'none' : 'inline-flex'; }
    msgInput.addEventListener('input', toggleSendMic);

    // Voice recording
    async function toggleRecording(){
        if (mediaRecorder && mediaRecorder.state === 'recording'){
            mediaRecorder.stop(); recordingHint.style.display='none'; voiceBtn.classList.remove('btn-danger'); return;
        }
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
            recordedChunks = []; mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
            mediaRecorder.ondataavailable = (e)=>{ if (e.data.size > 0) recordedChunks.push(e.data); };
            mediaRecorder.onstop = ()=>{ const blob = new Blob(recordedChunks, { type:'audio/webm' }); const file = new File([blob], 'voice.webm', { type:'audio/webm' }); const fd = new FormData(); fd.append('group_id', activeGroupId); fd.append('type','voice'); fd.append('file', file); sendMessage(fd); };
            mediaRecorder.start(); recordingHint.style.display='block'; voiceBtn.classList.add('btn-danger');
        } catch(err){ console.error(err); alert('Microphone access denied.'); }
    }
    voiceBtn.addEventListener('click', toggleRecording);

    function applyExpandedBounds(){
        if (!popupEl.classList.contains('expanded')) return;
        // Prefer a specific bounds container if present
        const boundsEl = document.querySelector('[data-chat-bounds]')
            || document.querySelector('main')
            || document.querySelector('.content-wrapper')
            || document.querySelector('.main-content')
            || document.querySelector('#content')
            || document.querySelector('.app-content')
            || document.querySelector('.page-wrapper')
            || null;
        const headerEl = document.querySelector('.header');
        const hb = headerEl ? headerEl.getBoundingClientRect() : { bottom: 0 };
        // Detect visible footer height (for sticky/footer-on-screen)
        const footerSelectors = ['.footer','footer','.main-footer','.app-footer','.site-footer'];
        let footerVisible = 0; let fb = null;
        for (const sel of footerSelectors){
            const f = document.querySelector(sel);
            if (!f) continue;
            const fr = f.getBoundingClientRect();
            const visible = Math.max(0, Math.min(window.innerHeight, fr.bottom) - Math.max(0, fr.top));
            if (visible > footerVisible){ footerVisible = visible; fb = fr; }
        }
        if (boundsEl){
            const br = boundsEl.getBoundingClientRect();
            const top = Math.max(0, Math.max(br.top, hb.bottom));
            const bottomLimit = Math.min(window.innerHeight - footerVisible, br.bottom);
            const left = Math.max(0, br.left);
            const width = Math.max(320, br.width);
            const height = Math.max(300, bottomLimit - top);
            popupEl.style.top = top + 'px';
            popupEl.style.left = left + 'px';
            popupEl.style.width = width + 'px';
            popupEl.style.height = height + 'px';
            popupEl.style.right = '';
            popupEl.style.bottom = '';
            popupEl.style.borderRadius = '0';
            return;
        }
        // Fallback: compute from header/sidebar/footer
        const sidebarSelectors = ['.sidebar','.sidebar-menu','.sidebar-wrapper','.sidebar-main','.app-sidebar','aside.sidebar'];
        let left = 0;
        for (const sel of sidebarSelectors){
            const s = document.querySelector(sel);
            if (s){
                const r = s.getBoundingClientRect();
                if (r.width > 0) { left = Math.max(left, r.right); }
            }
        }
        const top = Math.max(0, hb.bottom);
        const width = Math.max(320, window.innerWidth - left);
        const height = Math.max(300, window.innerHeight - top - footerVisible);
        popupEl.style.top = top + 'px';
        popupEl.style.left = left + 'px';
        popupEl.style.width = width + 'px';
        popupEl.style.height = height + 'px';
        popupEl.style.right = '';
        popupEl.style.bottom = '';
        popupEl.style.borderRadius = '0';
    }

    function openPopup(){
        popupEl.style.display = 'flex';
        setState({ open: true });
        if (popupEl.classList.contains('expanded')) { applyExpandedBounds(); requestAnimationFrame(applyExpandedBounds); }
        if (!allGroups.length) { fetchGroups(); }
    }

    // On load, restore persisted state
    (function restorePersisted(){
        const state = getState();
        if (state.expanded) { popupEl.classList.add('expanded'); }
        if (state.open) {
            popupEl.style.display = 'flex';
            if (popupEl.classList.contains('expanded')) { applyExpandedBounds(); requestAnimationFrame(applyExpandedBounds); }
            if (!allGroups.length) { fetchGroups(); }
        }
    })();

    // Open/Close/Expand handlers
    chatToggleBtn && chatToggleBtn.addEventListener('click', function(e){
        e.preventDefault(); openPopup();
    });
    closeBtn && closeBtn.addEventListener('click', function(){ popupEl.style.display = 'none'; setState({ open: false }); });
    expandBtn && expandBtn.addEventListener('click', function(){
        popupEl.classList.toggle('expanded');
        setState({ expanded: popupEl.classList.contains('expanded') });
        const i = this.querySelector('i');
        if (popupEl.classList.contains('expanded')) { i.classList.remove('fa-expand'); i.classList.add('fa-compress'); applyExpandedBounds(); }
        else {
            i.classList.remove('fa-compress'); i.classList.add('fa-expand');
            popupEl.style.top = ''; popupEl.style.left = ''; popupEl.style.right = ''; popupEl.style.bottom = ''; popupEl.style.width = ''; popupEl.style.height = ''; popupEl.style.borderRadius = '';
        }
    });

    window.addEventListener('resize', applyExpandedBounds);
    window.addEventListener('scroll', applyExpandedBounds, { passive: true });

    // Remove offcanvas dependency; fetch on first open via handler above
    // document.getElementById('chatOffcanvas').addEventListener('shown.bs.offcanvas', ()=>{ fetchGroups(); });
    setInterval(poll, 5000);

    function openPopup(){
        popupEl.style.display = 'flex';
        setState({ open: true });
        if (popupEl.classList.contains('expanded')) { applyExpandedBounds(); requestAnimationFrame(applyExpandedBounds); }
        if (!allGroups.length) { fetchGroups(); }
    }
    // Replace direct binding with delegated listener (covers dynamically loaded header)
    document.addEventListener('click', function(e){
        const btn = e.target.closest('#chatToggle');
        if (btn){ e.preventDefault(); openPopup(); }
    });
    // Also expose open on keyboard shortcut (optional)
    document.addEventListener('keydown', function(e){ if ((e.ctrlKey||e.metaKey) && e.key === 'i'){ openPopup(); } });
})();
</script>
