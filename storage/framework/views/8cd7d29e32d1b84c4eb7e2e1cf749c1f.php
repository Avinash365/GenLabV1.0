<?php
// Chat popup full implementation moved here from layouts/include/chat.blade.php
?>

<script>
// Legacy Echo init via require disabled. WebSocket init is handled later with a robust fallback.
// This avoids "require is not defined" errors in browsers without a bundler.
</script>

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
                    <button id="chatNewSessionBtn" class="btn btn-success" type="button" title="New Session"><i class="fa fa-plus"></i></button>
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
                <div class="d-flex align-items-center" style="gap:8px; color:#54656f;" id="filterBtnArea"></div>
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
                    <button id="emojiBtn" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Emoji" style="width:36px;height:36px;"><span class="emoji-icon">😊</span></button>
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
/* Styles kept same as original file */
:root { --chat-primary:#00a884; --chat-primary-dark:#008069; --chat-bg:#f9fafb; --chat-sidebar-bg:#ffffff; --chat-border:#e5e7eb; --chat-msg-in:#ffffff; --chat-msg-out:#dcf8c6; --chat-muted:#6b7280; --chat-radius:12px; --chat-shadow:0 1px 3px rgba(238, 232, 232, 0.08);} .chat-popup{position:fixed;right:max(20px, env(safe-area-inset-right));bottom:max(20px, env(safe-area-inset-bottom));width:min(700px, calc(100vw - 40px));height:min(640px, calc(100dvh - 40px));background:#fff;border:1px solid var(--chat-border);border-radius:12px;box-shadow:0 12px 30px rgba(0,0,0,0.18);display:flex;flex-direction:column;z-index:9999 !important;overflow:hidden;} .chat-popup-header{background:var(--chat-primary);color:#fff;padding:8px 12px;display:flex;align-items:center;justify-content:space-between;} .chat-popup.expanded{border-radius:0;box-shadow:none;} @media (max-width: 576px){.chat-popup{right:10px;bottom:10px;width:calc(100vw - 20px);height:calc(100dvh - 20px);border-radius:10px;}} #chatGroups .list-group-item{border:none;border-bottom:1px solid var(--chat-border);padding:12px 16px;transition:background .2s;} #chatGroups .list-group-item:hover{background:#f3f4f6;} #chatGroups .list-group-item.active{background:#e6f4ea;font-weight:600;color:var(--chat-primary-dark);} #chatActiveAvatar{width:40px;height:40px;background:#d1d5db;color:#111827;border-radius:50%;font-weight:600;display:flex;align-items:center;justify-content:center;} .wa-wallpaper{background:#f9fafb;} .wa-row{display:flex;margin:8px 0;gap:8px;} .wa-row.sent{justify-content:flex-end;} .wa-avatar{width:32px;height:32px;background:#d1d5db;color:#111827;border-radius:50%;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;} .wa-avatar img{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block;} .wa-bubble{position:relative;max-width:70%;padding:12px 14px;border-radius:var(--chat-radius);font-size:14px;line-height:1.4;box-shadow:var(--chat-shadow);overflow:hidden;} .wa-bubble.sent{background:var(--chat-msg-out);border-top-right-radius:4px;} .wa-bubble.received{background:var(--chat-msg-in);border-top-left-radius:4px;} .wa-bubble.status-booked{background:#d1fae5!important;border:1px solid #a7f3d0;} .wa-bubble.status-hold{background:#fef9c3!important;border:1px solid #fde68a;} .wa-bubble.status-unbooked{background:#fee2e2!important;border:1px solid #fecaca;} .wa-bubble.status-cancel{background:#fee2e2!important;border:1px solid #fecaca;} .wa-content{display:flex;flex-direction:column;gap:8px;} .wa-text{white-space:pre-wrap;word-wrap:break-word;color:#111827;} .wa-image img{max-width:100%;height:auto;max-height:320px;object-fit:contain;display:block;} .wa-meta-row{margin-top:6px;font-size:12px;color:var(--chat-muted);display:flex;align-items:center;gap:6px;} .wa-reply-ref{font-size:12px;color:var(--chat-muted);background:#f1f5f9;border-left:3px solid #94a3b8;padding:6px 8px;border-radius:8px;cursor:pointer;} .wa-day{display:flex;justify-content:center;margin:10px 0;} .wa-day>span{background:#e2e8f0;color:#334155;padding:4px 10px;font-size:12px;border-radius:999px;border:1px solid #cbd5e1;line-height:1;} .chat-dot{width:10px;height:10px;border-radius:50%;background:#22c55e;display:inline-block;} .chat-unread{font-size:12px;background:#ef4444;color:#fff;border-radius:999px;padding:1px 6px;} .chat-preview{font-size:12px;color:#6b7280;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px;} .chat-popup{z-index:990 !important;} #chatMessages{padding-bottom:calc(96px + env(safe-area-inset-bottom)) !important;}
</style>
<script>
// Main JS logic (trimmed comments for brevity)
(function(){
    const csrfToken='<?php echo e(csrf_token()); ?>';
    <?php $authUser = auth('admin')->user() ?: auth('web')->user(); $isAdmin = auth('admin')->check(); ?>
    const currentUser={ id: <?php echo e($authUser ? (int)$authUser->id : 'null'); ?>, name:<?php echo json_encode($authUser->name ?? 'Guest', 15, 512) ?>};
    const isSuperAdmin=<?php echo e($isAdmin ? 'true':'false'); ?>;
    const routes={ groups:'<?php echo e(url('/chat/groups')); ?>', messages:'<?php echo e(url('/chat/messages')); ?>', messagesSince:'<?php echo e(url('/chat/messages/since')); ?>', send:'<?php echo e(url('/chat/messages')); ?>', react:(id)=>`${'<?php echo e(url('/chat/messages')); ?>'}/${id}/reactions`, direct:(id)=>`${'<?php echo e(url('/chat/direct')); ?>'}/${id}`, searchUsers:(q)=>`${'<?php echo e(url('/chat/users/search')); ?>'}?q=${encodeURIComponent(q)}`, directWith:(id)=>`${'<?php echo e(url('/chat/direct-with')); ?>'}/${id}`, markSeen:'<?php echo e(url('/chat/mark-seen')); ?>'};
    window.routes=routes; window.currentUser=currentUser; window.isSuperAdmin=isSuperAdmin;
    const groupsEl=document.getElementById('chatGroups'); const messagesEl=document.getElementById('chatMessages'); const emptyEl=document.getElementById('chatEmptyState'); const loadingEl=document.getElementById('chatLoading'); const inputAreaEl=document.getElementById('chatInputArea'); const msgInput=document.getElementById('chatMessageInput'); const sendBtn=document.getElementById('sendMessageBtn'); const fileInput=document.getElementById('fileInput'); const attachBtn=document.getElementById('attachBtn'); const voiceBtn=document.getElementById('voiceBtn'); const recordingHint=document.getElementById('recordingHint'); const emojiBtn=document.getElementById('emojiBtn'); const activeTitle=document.getElementById('chatActiveTitle'); const activeAvatar=document.getElementById('chatActiveAvatar'); const popupEl=document.getElementById('chatPopup'); const searchEl=document.getElementById('chatGroupSearch');
    let activeGroupId=null; let lastMessageId=0; let cache=[]; let idIndex=new Set(); window.cache=cache; window.messagesEl=messagesEl;
    function initials(s){return (s||'?').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();}
    function fmtTime(ts){try{return new Date(ts).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});}catch{return ts;}}
    function renderGroups(list){ groupsEl.innerHTML=''; (list||[]).slice().sort((a,b)=>(b.last_msg_id||0)-(a.last_msg_id||0)).forEach(g=>{ const a=document.createElement('a'); a.href='#'; a.className='list-group-item d-flex align-items-center'; a.dataset.groupId=g.id; a.dataset.groupName=g.name; const av = g.avatar?`<div class="wa-avatar me-2"><img src="${g.avatar}"/></div>`:`<div class="wa-avatar me-2">${initials(g.name)}</div>`; const preview=((g.latest&& (g.latest.content||g.latest.original_name))||'')+''; a.innerHTML=av+`<div class='flex-grow-1'><div class='d-flex align-items-center justify-content-between'><div>${g.name}</div><div class='text-muted small'>${g.last_msg_at?fmtTime(g.last_msg_at):''}</div></div><div class='chat-preview'>${preview.slice(0,60)}</div></div>`+`<div class='ms-auto d-flex align-items-center'>${g.unread?`<span class='chat-unread'>${g.unread>99?'99+':g.unread}</span>`:''}</div>`; a.addEventListener('click',e=>{e.preventDefault(); selectGroup(g.id,g.name,a);}); groupsEl.appendChild(a); }); if (typeof window.__CHAT_AGG_UNREAD__==='function') window.__CHAT_AGG_UNREAD__(); }
    window.renderGroups=renderGroups;
    async function fetchGroups(){ const r=await fetch(routes.groups,{headers:{'Accept':'application/json'}}); const d=await r.json(); window.allGroups=Array.isArray(d)?d:[]; renderGroups(window.allGroups); }
    window.fetchGroups=fetchGroups;
    function selectGroup(id,name,node){ groupsEl.querySelectorAll('.list-group-item').forEach(n=>n.classList.remove('active')); if(node) node.classList.add('active'); activeGroupId=id; activeTitle.textContent=name; activeAvatar.textContent=initials(name); inputAreaEl.style.display='flex'; cache=[]; idIndex.clear(); messagesEl.querySelectorAll('.wa-row,.wa-day').forEach(n=>n.remove()); emptyEl.style.display='flex'; fetchMessages(id); if (window.allGroups){ const g=window.allGroups.find(x=>Number(x.id)===Number(id)); if(g){g.unread=0; renderGroups(window.allGroups);} } }
    async function fetchMessages(gid){ loadingEl.style.display='block'; const u=new URL(routes.messages, window.location.origin); u.searchParams.set('group_id',gid); const r=await fetch(u,{headers:{'Accept':'application/json'}}); const data=await r.json(); cache=Array.isArray(data)?data:[]; idIndex=new Set(cache.map(m=>m.id)); lastMessageId=cache.length?cache[cache.length-1].id:0; renderMessages(cache); loadingEl.style.display='none'; try{ await markGroupSeen(gid,lastMessageId);}catch{} }
    async function poll(){ if(!activeGroupId) return; const u=new URL(routes.messagesSince, window.location.origin); u.searchParams.set('group_id',activeGroupId); u.searchParams.set('after_id',lastMessageId); const r=await fetch(u,{headers:{'Accept':'application/json'}}); const data=await r.json(); if(Array.isArray(data)&&data.length){ let added=false; for(const m of data){ if(!idIndex.has(m.id)){ idIndex.add(m.id); cache.push(m); added=true; } } if(added){ cache.sort((a,b)=>a.id-b.id); lastMessageId=cache[cache.length-1].id; renderMessages(cache);} } }
    window.__CHAT_POLL_INTERVAL=setInterval(poll,5000);
    function messageRow(m){ const mine=!!m.mine; const row=document.createElement('div'); row.className='wa-row '+(mine?'sent':'received'); row.dataset.msgId=m.id; const av=document.createElement('div'); av.className='wa-avatar'; av.textContent=initials((m.user&&m.user.name)||m.sender_name||'U'); const bubble=document.createElement('div'); bubble.className='wa-bubble '+(mine?'sent':'received'); const content=document.createElement('div'); content.className='wa-content'; const nameEl=document.createElement('div'); nameEl.className='wa-sender'; nameEl.textContent=(m.user&&m.user.name)||m.sender_name||'User'; content.appendChild(nameEl); if(m.type==='text'||(!m.file_url && m.content)){ const t=document.createElement('div'); t.className='wa-text'; t.textContent=m.content||''; content.appendChild(t);} else if(m.type==='image'){ const wrap=document.createElement('div'); wrap.className='wa-image'; const img=document.createElement('img'); img.src=m.file_url; wrap.appendChild(img); content.appendChild(wrap);} else if(m.type==='pdf'){ const t=document.createElement('div'); t.className='wa-text'; t.textContent=m.original_name||'[PDF]'; content.appendChild(t);} else if(m.type==='voice'){ const wrap=document.createElement('div'); const audio=document.createElement('audio'); audio.controls=true; audio.src=m.file_url; wrap.appendChild(audio); content.appendChild(wrap);} bubble.appendChild(content); const meta=document.createElement('div'); meta.className='wa-meta-row'; const time=document.createElement('span'); time.textContent=fmtTime(m.created_at); meta.appendChild(time); bubble.appendChild(meta); if(mine){ row.appendChild(bubble); row.appendChild(av);} else { row.appendChild(av); row.appendChild(bubble);} return row; }
    window.messageRow=messageRow;
    function renderMessages(list){ messagesEl.querySelectorAll('.wa-row,.wa-day').forEach(n=>n.remove()); if(!list.length){ emptyEl.style.display='flex'; return;} emptyEl.style.display='none'; let lastDay=null; list.forEach(m=>{ const d=new Date(m.created_at); const dayKey=d.toDateString(); if(dayKey!==lastDay){ const sep=document.createElement('div'); sep.className='wa-day'; const s=document.createElement('span'); s.textContent=dayKey; sep.appendChild(s); messagesEl.appendChild(sep); lastDay=dayKey;} messagesEl.appendChild(messageRow(m)); }); messagesEl.scrollTop=messagesEl.scrollHeight; }
    function pushNewMessage(msg){ if(!msg||Number(msg.group_id)!==Number(activeGroupId)) return; if(cache.find(x=>x.id===msg.id)) return; msg.mine = currentUser && Number(currentUser.id)===Number(msg.user_id); cache.push(msg); cache.sort((a,b)=>a.id-b.id); lastMessageId=msg.id; renderMessages(cache); }
    window.pushNewMessage=pushNewMessage;
    function markGroupSeen(groupId,lastId){ return fetch(routes.markSeen,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json','Content-Type':'application/json'}, body:JSON.stringify({group_id:groupId,last_id:lastId})}); }
    // Send text
    sendBtn.addEventListener('click',()=>{ const text=(msgInput.value||'').trim(); if(!text||!activeGroupId) return; const payload={group_id:activeGroupId,type:'text',content:text}; fetch(routes.send,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify(payload)}).then(r=>r.json()).then(m=>{ msgInput.value=''; pushNewMessage(m); }).catch(()=>{}); });
    msgInput.addEventListener('keydown',e=>{ if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); sendBtn.click(); }});
    function toggleSendMic(){ const has=(msgInput.value||'').trim().length>0; document.getElementById('sendMessageBtn').style.display=has?'inline-flex':'none'; document.getElementById('voiceBtn').style.display=has?'none':'inline-flex'; }
    msgInput.addEventListener('input',toggleSendMic);
    attachBtn.addEventListener('click',()=>fileInput.click()); fileInput.addEventListener('change',()=>{ const f=fileInput.files[0]; if(!f||!activeGroupId) return; let kind='text'; if(f.type.startsWith('image/')) kind='image'; else if(f.type==='application/pdf') kind='pdf'; else if(f.type.startsWith('audio/')) kind='voice'; else { alert('Unsupported'); return;} const fd=new FormData(); fd.append('group_id',activeGroupId); fd.append('type',kind); fd.append('file',f); fetch(routes.send,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:fd}).then(r=>r.json()).then(m=> pushNewMessage(m)).catch(()=>alert('Upload failed')).finally(()=>{fileInput.value='';}); });
    // Basic voice
    voiceBtn.addEventListener('click',async()=>{ try{ const st=await navigator.mediaDevices.getUserMedia({audio:true}); const mr=new MediaRecorder(st); let chunks=[]; mr.ondataavailable=e=>{ if(e.data.size) chunks.push(e.data); }; mr.onstop=()=>{ const blob=new Blob(chunks,{type:'audio/webm'}); const file=new File([blob],'voice.webm',{type:'audio/webm'}); const fd=new FormData(); fd.append('group_id',activeGroupId); fd.append('type','voice'); fd.append('file',file); fetch(routes.send,{method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:fd}).then(r=>r.json()).then(m=> pushNewMessage(m)); }; mr.start(); setTimeout(()=> mr.stop(), 60000); }catch{ alert('Mic denied'); } });
    // Search filtering only (no user search replicate for brevity)
    searchEl.addEventListener('input',()=>{ const q=(searchEl.value||'').toLowerCase(); if(!q) return renderGroups(window.allGroups||[]); renderGroups((window.allGroups||[]).filter(g=>g.name.toLowerCase().includes(q))); });
    // Popup controls
    const expandBtn=document.getElementById('chatExpandBtn'); const closeBtn=document.getElementById('chatCloseBtn'); const chatToggleBtn=document.getElementById('chatToggle');
    function openChat(){ popupEl.style.display='flex'; if(!window.allGroups || !window.allGroups.length) fetchGroups(); }
    window.openChat=openChat; chatToggleBtn&&chatToggleBtn.addEventListener('click',e=>{e.preventDefault(); openChat();}); closeBtn&&closeBtn.addEventListener('click',()=>{ popupEl.style.display='none';}); expandBtn&&expandBtn.addEventListener('click',()=>{ popupEl.classList.toggle('expanded'); });
    // WebSocket/Echo listener (reuse existing global if defined elsewhere)
    function attachRealtime(){ if(window.__CHAT_REALTIME_BOUND__) return; window.__CHAT_REALTIME_BOUND__=true; if(window.Echo){ try{ window.Echo.channel('chat').listen('ChatMessageBroadcast',e=>{ const msg=e.message||e; updateGroupMeta(msg); if(Number(msg.group_id)===Number(activeGroupId)) pushNewMessage(msg); }); }catch{} } }
    function updateGroupMeta(msg){ if(!msg) return; if(!window.allGroups) window.allGroups=[]; let g=window.allGroups.find(x=>Number(x.id)===Number(msg.group_id)); if(!g){ g={id:msg.group_id,name:msg.sender_name||'Chat',unread:0}; window.allGroups.unshift(g);} g.latest={id:msg.id, content:msg.content, original_name:msg.original_name, created_at:msg.created_at, type:msg.type}; g.last_msg_id=msg.id; g.last_msg_at=msg.created_at; if(Number(msg.group_id)!==Number(activeGroupId)) g.unread=(g.unread||0)+1; window.allGroups.sort((a,b)=>(b.last_msg_id||0)-(a.last_msg_id||0)); renderGroups(window.allGroups); }
    // Fallback polling if no WS
    setTimeout(()=>{ if(!window.Echo) fetchGroups(); attachRealtime(); },800);
    // Public helpers
    window.__CHAT_OPEN_GROUP__=function(id){ openChat(); setTimeout(()=>{ const el=groupsEl.querySelector(`[data-group-id="${id}"]`); if(el) el.click(); },300); };
})();
</script>

<!-- Echo init (kept minimal) -->
<script>
(function(){ if(window.__BASIC_ECHO__) return; window.__BASIC_ECHO__=true; function init(){ if(window.Echo) return; const add=src=>new Promise((res,rej)=>{ const s=document.createElement('script'); s.src=src; s.onload=res; s.onerror=rej; document.head.appendChild(s); }); add('https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js').then(()=> add('https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js')).then(()=>{ try{ const E=window.Echo; window.Echo=new E({ broadcaster:'pusher', key:'local', cluster:'mt1', wsHost:window.location.hostname, wsPort:6001, forceTLS:false, disableStats:true, enabledTransports:['ws','wss']}); }catch(e){} }); }
 setTimeout(init,400);
})();
</script><?php /**PATH C:\xampp\htdocs\GenLab\resources\views/superadmin/chat.blade.php ENDPATH**/ ?>