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
                <div class="d-flex align-items-center" style="gap:8px; color:#54656f;" id="filterBtnArea">
                    <!-- Filter buttons will be injected here -->
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
                    <!-- Replace FA icon with visible emoji -->
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
  bottom: max(10px, env(safe-area-inset-bottom));
  width: min(700px, calc(100vw - 40px));
  height: min(620px, calc(100dvh - 40px));
  background:#fff;
  border:1px solid var(--chat-border);
  border-radius:12px;
  box-shadow: 0 12px 30px rgba(0,0,0,0.18);
  display:flex; flex-direction:column;
  z-index:9999 !important;
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
.wa-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block; }
.wa-bubble {
  position: relative;
  max-width: 70%;
  padding: 12px 14px;
  border-radius: var(--chat-radius);
  font-size: 14px;
  line-height: 1.4;
  box-shadow: var(--chat-shadow);
  overflow: hidden;
}
.wa-bubble.sent {
  background: var(--chat-msg-out);
  border-top-right-radius: 4px;
}
.wa-bubble.received {
  background: var(--chat-msg-in);
  border-top-left-radius: 4px;
}
/* Booked status bubble (applies to sent and received) */
.wa-bubble.status-booked { background: #d1fae5 !important; border: 1px solid #a7f3d0; }
.wa-bubble.status-hold { background: #fef9c3 !important; border: 1px solid #fde68a; }
.wa-bubble.status-unbooked { background: #fee2e2 !important; border: 1px solid #fecaca; }
.wa-bubble.status-cancel { background: #fee2e2 !important; border: 1px solid #fecaca; }
.wa-content { display: flex; flex-direction: column; gap: 8px; }
.wa-text { white-space: pre-wrap; word-wrap: break-word; color:#111827; }
.wa-image { max-width: 100%; }
.wa-image img { max-width: 100%; height: auto; max-height: 320px; object-fit: contain; display: block; }
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

/* Sender name label */
.wa-sender { font-size: 12px; font-weight: 600; color: var(--chat-muted); margin-bottom: 4px; }

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
.chat-popup{ z-index: 990 !important; }
/* Ensure composer remains visible and layout can shrink properly */
.chat-popup-body { min-height: 0; }
.chat-popup-body > section { min-height: 0; }
#chatInputArea { flex-shrink: 0; position: sticky; bottom: 0; background:#fff; z-index: 2; }
/* Add bottom space so last messages aren't hidden behind composer */
#chatMessages { padding-bottom: calc(96px + env(safe-area-inset-bottom)) !important; }
.wa-reply-ref { font-size: 12px; color: var(--chat-muted); background:#f1f5f9; border-left:3px solid #94a3b8; padding:6px 8px; border-radius:8px; cursor:pointer; }
.wa-highlight { outline: 2px solid #f59e0b; }
.filter-active.btn-outline-warning { background:#fef9c3; border-color:#fde68a; color:#92400e; }
.filter-active.btn-outline-danger { background:#fee2e2; border-color:#fecaca; color:#991b1b; }

/* Sidebar unread marker */
/* Remove visual dot usage; keep count badge only */
.chat-unread{ font-size:12px; background:#22c55e; color:#fff; border-radius:999px; padding:1px 8px; line-height:18px; min-width:22px; text-align:center; }

/* WhatsApp-like: unread chats look bolder */
#chatGroups .list-group-item.has-unread .chat-title{ font-weight:700; color:#111827; }
#chatGroups .list-group-item.has-unread .chat-preview{ color:#111827; }

/* Day separator chip centered */
.wa-day{ display:flex; justify-content:center; margin:10px 0; }
.wa-day > span{ background:#e2e8f0; color:#334155; padding:4px 10px; font-size:12px; border-radius:999px; border:1px solid #cbd5e1; line-height:1; }

/* WhatsApp-like PDF/document bubble */
.wa-doc{ display:flex; align-items:center; gap:12px; padding:8px 10px; border-radius:10px; background: transparent; }
.wa-doc-link{ display:flex; align-items:center; gap:12px; text-decoration:none; color:inherit; flex:1; min-width:0; }
.wa-doc-icon{ width:44px; height:56px; border-radius:8px; background:#e2e8f0; color:#334155; display:flex; align-items:center; justify-content:center; flex:0 0 auto; }
.wa-doc-icon svg{ width:24px; height:24px; display:block; }
.wa-doc-info{ display:flex; flex-direction:column; min-width:0; }
.wa-doc-name{ font-weight:600; color:#111827; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 260px; }
.wa-doc-meta{ font-size:12px; color: var(--chat-muted); }
.wa-doc-download{ margin-left:auto; color: var(--chat-primary-dark); text-decoration:none; font-size:16px; padding:6px; border-radius:8px; }
.wa-doc-download:hover{ background: rgba(0,0,0,0.06); }

/* Tighter caption under doc */
.wa-bubble .wa-caption{ margin-top:4px; }

/* Hide chat menu button by default, show on bubble hover */
.wa-bubble .chat-menu-btn { display: none; }
.wa-bubble:hover .chat-menu-btn { display: inline-block; }
</style>
<script>
(function(){
    const csrfToken = '<?php echo e(csrf_token()); ?>';
    <?php
        // SAFE: only call auth('superadmin') if guard exists
        $hasSuper = !is_null(config('auth.guards.superadmin') ?? null);
        $web = auth('web')->user();
        $super = $hasSuper ? auth('superadmin')->user() : null;
        $admin = auth('admin')->user();

        // Prefer superadmin/admin for currentUser; fallback to web
        $root = $super ?: $admin;
        $authUser = $root ?: $web;

        // Root admin if super or admin logged in
        $isRootAdmin = ($root !== null);

        // Super-admin in chat if root OR web is promoted
        $isChatAdmin = $isRootAdmin || ($web && ($web->is_chat_admin ?? false));
    ?>
    const currentUser = { id: <?php echo e($authUser ? (int)$authUser->id : 'null'); ?>, name: <?php echo json_encode($authUser->name ?? 'Guest', 15, 512) ?> };
    const isSuperAdmin = <?php echo e($isChatAdmin ? 'true' : 'false'); ?>;
    const isRootAdmin = <?php echo e($isRootAdmin ? 'true' : 'false'); ?>;
    window.currentUser = currentUser;
    window.isSuperAdmin = isSuperAdmin;
    window.isRootAdmin = isRootAdmin;

    const routes = {
        groups: '<?php echo e(url('/chat/groups')); ?>',
        messages: '<?php echo e(url('/chat/messages')); ?>',
        messagesSince: '<?php echo e(url('/chat/messages/since')); ?>',
        send: '<?php echo e(url('/chat/messages')); ?>',
        react: (id) => `${'<?php echo e(url('/chat/messages')); ?>'}/${id}/reactions`,
        direct: (userId) => `${'<?php echo e(url('/chat/direct')); ?>'}/${userId}`,
        searchUsers: (q) => `${'<?php echo e(url('/chat/users/search')); ?>'}?q=${encodeURIComponent(q)}`,
        directWith: (id) => `${'<?php echo e(url('/chat/direct-with')); ?>'}/${id}`,
        markSeen: '<?php echo e(url('/chat/mark-seen')); ?>',
        unreadCounts: '<?php echo e(url('/chat/unread-counts')); ?>',
        // NEW: toggle chat-admin for a user (route to add in routes/web.php)
        setChatAdmin: (userId) => `${'<?php echo e(url('/chat/users')); ?>'}/${userId}/chat-admin`
    };
    window.routes = routes;
    window.chatNotifBadge = document.getElementById('chatNotifBadge');

    // NEW: robust base for absolute URLs (works under subdirectories)
    const APP_BASE = '<?php echo e(rtrim(url('/'), '/')); ?>';

    // Helper: normalize and build absolute URL for file/asset paths
    function toAbsoluteUrl(u){
        if (!u) return '';
        try {
            let s = String(u);
            if (/^https?:\/\//i.test(s)) return s;
            // collapse any repeated /storage/ prefixes
            s = s.replace(/^(\/storage\/)+/, '/storage/');
            // handle relative 'storage/...' (no leading slash)
            if (!s.startsWith('/')) s = '/' + s;
            // join with app base (handles subfolder installs)
            return APP_BASE + s;
        } catch(_) { return String(u || ''); }
    }

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
    // Make the messages container available to realtime handlers
    window.messagesEl = messagesEl;
    if (emojiBtn){
        // Ensure an icon or emoji is visible
        const hasI = !!emojiBtn.querySelector('i');
        if (!hasI) { emojiBtn.textContent = '😊'; }
        else {
            const i = emojiBtn.querySelector('i');
            // Normalize to a known FA4 class and set fallback color
            i.classList.remove('fa-smile'); i.classList.add('fa-smile-o');
            i.style.color = '#6b7280';
        }
    }
    const activeTitle = document.getElementById('chatActiveTitle');
    const activeAvatar = document.getElementById('chatActiveAvatar');
    const popupEl = document.getElementById('chatPopup');

    // Central badge updater: sums unread from allGroups and updates the header badge in real-time
    function updateHeaderBadge(){
        try {
            const badge = window.chatNotifBadge || document.getElementById('chatNotifBadge');
            const total = Array.isArray(allGroups) ? allGroups.reduce((s,g)=> s + (parseInt(g.unread)||0), 0) : 0;
            if (!badge) return;
            if (total > 0) {
                badge.textContent = total > 99 ? '99+' : String(total);
                badge.style.display = 'flex';
            } else {
                badge.textContent = '';
                badge.style.display = 'none';
            }
        } catch(_) {}
    }
    window.__CHAT_UPDATE_BADGE__ = updateHeaderBadge;

    // Server-truth fetch for unread counts; updates allGroups.unread and header badge
    window.__CHAT_FETCH_COUNTS__ = async function(){
        try {
            const res = await fetch(routes.unreadCounts, { headers:{ 'Accept':'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            const groupsCounts = (data && Array.isArray(data.groups)) ? data.groups : [];
            const map = new Map(groupsCounts.map(x=> [String(x.group_id), parseInt(x.count)||0]));
            if (Array.isArray(allGroups)) {
                for (let i=0;i<allGroups.length;i++){
                    const g = allGroups[i];
                    const n = map.get(String(g.id)) || 0;
                    g.unread = n;
                }
            }
            if (typeof renderGroups === 'function') renderGroups(allGroups);
            updateHeaderBadge();
        } catch(_) {}
    };

    // Ensure the messages padding matches composer height (remove extra gap)
    function syncMessagesPadding(){
        if (!messagesEl || !inputAreaEl) return;
        // If composer hidden, still keep a small space
        const composerVisible = inputAreaEl.style.display !== 'none';
        const h = composerVisible ? inputAreaEl.offsetHeight : 0;
        const rh = (recordingHint && recordingHint.style.display !== 'none') ? recordingHint.offsetHeight : 0;
        const extra = 8; // small breathing space
        const pb = Math.max(0, h + rh + extra);
        messagesEl.style.setProperty('padding-bottom', pb + 'px', 'important');
    }

    // Re-parent to <body> to avoid clipping/stacking issues from ancestors
    if (popupEl && popupEl.parentElement !== document.body) {
        document.body.appendChild(popupEl);
    }
    // Restore refs for controls
    const expandBtn = document.getElementById('chatExpandBtn');
    const closeBtn = document.getElementById('chatCloseBtn');
    const chatToggleBtn = document.getElementById('chatToggle');
    const newSessionBtn = document.getElementById('chatNewSessionBtn');

    // Fallback: inject a floating chat toggle if header toggle is missing
    if (!chatToggleBtn && !document.getElementById('chatToggle')) {
        try {
            const fab = document.createElement('button');
            fab.id = 'chatToggle';
            fab.type = 'button';
            fab.title = 'Open chat';
            fab.innerHTML = '<i class="fa fa-comments"></i>';
            fab.className = 'btn btn-success rounded-circle shadow';
            fab.style.cssText = 'position:fixed; right:20px; bottom:20px; width:48px; height:48px; z-index:1000;';
            document.body.appendChild(fab);
        } catch(_) {}
    }

    // Add: filter buttons refs (visible to both admin and users)
    const filterHoldBtn = document.getElementById('filterHoldBtn');
    const filterUnbookedBtn = document.getElementById('filterUnbookedBtn');

    // Persist chat UI state (open/expanded)
    const CHAT_STATE_KEY = 'chat.ui.state';
    const getState = () => { try { return JSON.parse(localStorage.getItem(CHAT_STATE_KEY) || '{}'); } catch { return {}; } };
    const setState = (patch) => { const next = Object.assign({}, getState(), patch); localStorage.setItem(CHAT_STATE_KEY, JSON.stringify(next)); };

    // State
    let activeGroupId = null; let lastMessageId = 0;
    let mediaRecorder = null; let recordedChunks = [];
    let cache = []; let allGroups = [];
    window.allGroups = allGroups; // Expose globally for realtime updates
    let idIndex = new Set(); // track message ids to dedupe
    let polling = false; // prevent overlapping polls

    // Add: filter state and helpers
    const activeFilters = new Set();
    function updateFilterButtons(){
        if (!filterHoldBtn || !filterUnbookedBtn) return;
        filterHoldBtn.classList.toggle('filter-active', activeFilters.has('hold'));
        filterUnbookedBtn.classList.toggle('filter-active', activeFilters.has('unbooked'));
    }
    function adminStatusFromText(t){
        if (!t) return null; const s = String(t).trim().toLowerCase();
        if (s.startsWith('hold')) return 'hold';
        if (s.startsWith('booked')) return 'booked';
        if (s.startsWith('cancel')) return 'cancel';
        // treat "unbooked" text as no status
        if (s.startsWith('unbooked')) return null;
        return null;
    }
    // Compute status by scanning the whole thread (original + all replies),
    // using reactions-derived status first, then admin reply text markers.
    function effectiveStatus(m){
        if (!m) return null;
        // Determine root id
        const rootId = m.reply_to_message_id ? findRootMessageId(m.id) : m.id;
        // Gather thread (root + replies recursively up to a safe guard)
        const thread = [];
        const queue = [rootId];
        const seen = new Set();
        let guard = 0;
        while (queue.length && guard++ < 500){
            const cur = queue.shift(); if (seen.has(cur)) continue; seen.add(cur);
            const node = Array.isArray(cache) ? cache.find(x => x && x.id === cur) : null;
            if (node) thread.push(node);
            const replies = Array.isArray(cache) ? cache.filter(x => x && x.reply_to_message_id === cur) : [];
            for (const r of replies){ queue.push(r.id); }
        }
        if (!thread.length) return null;
        // Sort by id ascending to simulate chronology
        thread.sort((a,b)=> (a.id||0) - (b.id||0));
        let winner = null;
        for (const msg of thread){
            // Prefer reactions-derived status
            if (msg && msg.status){ winner = msg.status; continue; }
            // Fallback to admin reply text markers
            if (msg && msg.sender_guard === 'admin'){
                const s = adminStatusFromText(bestText(msg));
                if (s) winner = s;
            }
        }
        return winner;
    }
    function applyMessageFilters(list){
        // Only filter for Bookings group
        if (!activeFilters.size || String(activeTitle.textContent).trim().toLowerCase() !== 'bookings') return list;
        return list.filter(m => {
            if (!m || m.reply_to_message_id) return false;
            const type = (m.type || m.message_type || '').toString().toLowerCase();
            if (type !== 'pdf' && type !== 'image') return false;
            const st = effectiveStatus(m);
            // Hold filter: only show hold status
            if (activeFilters.has('hold')) return st === 'hold';
            // Unbooked filter: only show messages with no status
            if (activeFilters.has('unbooked')) return !st;
            return false;
        });
    }
    function toggleFilter(kind){
        if (activeFilters.has(kind)) activeFilters.delete(kind); else activeFilters.add(kind);
        updateFilterButtons();
        renderMessages(cache);
    }

    // New: compute and display counts for Hold and Unbooked in current group
    function updateStatusCounts(){
        if (!Array.isArray(cache) || String(activeTitle.textContent).trim().toLowerCase() !== 'bookings') return;
        let hold = 0, unbooked = 0;
        for (const m of cache){
            if (!m || m.reply_to_message_id) continue;
            const type = (m.type || m.message_type || '').toString().toLowerCase();
            if (type !== 'pdf' && type !== 'image') continue;
            const st = effectiveStatus(m);
            if (st === 'hold') hold++;
            else if (!st) unbooked++;
        }
        const filterHoldBtn = document.getElementById('filterHoldBtn');
        const filterUnbookedBtn = document.getElementById('filterUnbookedBtn');
        if (filterHoldBtn){ filterHoldBtn.textContent = `Hold (${hold})`; }
        if (filterUnbookedBtn){ filterUnbookedBtn.textContent = `Unbooked (${unbooked})`; }
    }
    window.__CHAT_UPDATE_COUNTS = updateStatusCounts;

    // Wire header buttons
    filterHoldBtn && filterHoldBtn.addEventListener('click', (e)=>{ e.preventDefault(); toggleFilter('hold'); });
    filterUnbookedBtn && filterUnbookedBtn.addEventListener('click', (e)=>{ e.preventDefault(); toggleFilter('unbooked'); });

    const initials = (s)=> (s||'?').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();
    const fmtTime = (ts)=> { try { const d = new Date(ts); return d.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}); } catch(e){ return ts; } };
    // Helpers for day labels
    const toDate = (ts)=> { try { return new Date(ts); } catch { return new Date(); } };
    const startOfDay = (d)=> { const x = new Date(d); x.setHours(0,0,0,0); return x; };
    const isSameDay = (a,b)=> startOfDay(a).getTime() === startOfDay(b).getTime();
    const dayLabel = (d)=> { const today = startOfDay(new Date()); const md = startOfDay(d); const diff = Math.round((today - md)/86400000); if (diff===0) return 'Today'; if (diff===1) return 'Yesterday'; return d.toLocaleDateString([], { day:'2-digit', month:'short', year:'numeric'}); };

    // Groups
    function renderGroups(list){
        groupsEl.innerHTML = '';
        const sorted = Array.isArray(list) ? list.slice().sort((a,b)=> (b.last_msg_id||0) - (a.last_msg_id||0)) : [];
        sorted.forEach(g => {
            const item = document.createElement('a');
            item.href = '#'; item.className = 'list-group-item d-flex align-items-center';
            item.dataset.groupId = g.id; item.dataset.groupName = g.name;

            const initials2 = (g.name||'?').split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();
            const absAvatar = g.avatar ? toAbsoluteUrl(g.avatar) : null;
            const avatarHtml = absAvatar
                ? `<div class="wa-avatar me-2"><img src="${absAvatar}" alt="${g.name||'Group'}" loading="lazy"></div>`
                : `<div class="wa-avatar me-2">${initials2}</div>`;

            const latest = g.latest || {};
            const unreadCount = parseInt(g.unread)||0;
            const hasUnread = unreadCount > 0;

            // Mark item as unread for bold styling
            if (hasUnread) item.classList.add('has-unread'); else item.classList.remove('has-unread');

            const right = document.createElement('div');
            right.className='ms-auto d-flex align-items-center';
            right.innerHTML = hasUnread ? `<span class="chat-unread">${unreadCount>99?'99+':unreadCount}</span>` : '';

            const meta = document.createElement('div');
            meta.className='flex-grow-1';

            // --- LIMIT PREVIEW LENGTH ---
            let previewText =
                ((latest.content || latest.original_name ||
                 (latest.type==='image' ? '[Image]' :
                  latest.type==='pdf' ? '[PDF]' :
                  latest.type==='voice' ? '[Audio]' : '') || '') + '');

            const PREVIEW_LIMIT = 15;
            if (previewText.length > PREVIEW_LIMIT) {
                previewText = previewText.slice(0, PREVIEW_LIMIT) + '...';
            }

            meta.innerHTML =
                `<div class="d-flex align-items-center justify-content-between">
                    <div class="chat-title">${g.name}</div>
                    <div class="text-muted small">${g.last_msg_at? new Date(g.last_msg_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}):''}</div>
                 </div>
                 <div class="chat-preview">${previewText}</div>`;

            item.innerHTML = avatarHtml + meta.outerHTML + right.outerHTML;
            item.addEventListener('click', (e)=>{ e.preventDefault(); selectGroup(g.id, g.name, item); });
            groupsEl.appendChild(item);
        });
        // Refresh header badge
        if (typeof updateHeaderBadge === 'function') { try { updateHeaderBadge(); } catch(_){} }
    }
    window.renderGroups = renderGroups;

    function selectGroup(id, name, node){
        // Clear filters on group change
        activeFilters.clear(); updateFilterButtons();
        groupsEl.querySelectorAll('.list-group-item').forEach(n=>n.classList.remove('active'));
        if (node) node.classList.add('active');
        activeGroupId = id; window.activeGroupId = id; activeTitle.textContent = name; activeAvatar.textContent = initials(name);
        inputAreaEl.style.display = 'flex';
        lastMessageId = 0; window.lastMessageId = 0; cache = []; idIndex.clear(); // reset index on group change
        messagesEl.querySelectorAll('.wa-row, .reaction-chip').forEach(n=>n.remove()); emptyEl.style.display = 'flex';
        setState({ activeGroupId: id });
        requestAnimationFrame(syncMessagesPadding);
        fetchMessages(id);

        // Clear unread locally for this group so the dot disappears immediately
        try {
            const g = allGroups.find(x => String(x.id) === String(id));
            if (g) { g.unread = 0; renderGroups(allGroups); updateHeaderBadge(); }
        } catch(_) {}

        // Show filter buttons only for Bookings group
        const filterBtnArea = document.getElementById('filterBtnArea');
        if (filterBtnArea) {
            if (String(name).trim().toLowerCase() === 'bookings') {
                filterBtnArea.innerHTML = '<button id="filterHoldBtn" type="button" class="btn btn-sm btn-outline-warning" title="Show Held">Hold</button>' +
                    '<button id="filterUnbookedBtn" type="button" class="btn btn-sm btn-outline-danger" title="Show Unbooked">Unbooked</button>';
                // Re-attach event listeners after injecting buttons
                setTimeout(function(){
                    const filterHoldBtn = document.getElementById('filterHoldBtn');
                    const filterUnbookedBtn = document.getElementById('filterUnbookedBtn');
                    filterHoldBtn && filterHoldBtn.addEventListener('click', function(e){ e.preventDefault(); activeFilters.clear(); activeFilters.add('hold'); updateFilterButtons(); renderMessages(cache); });
                    filterUnbookedBtn && filterUnbookedBtn.addEventListener('click', function(e){ e.preventDefault(); activeFilters.clear(); activeFilters.add('unbooked'); updateFilterButtons(); renderMessages(cache); });
                    updateStatusCounts();
                }, 10);
            } else {
                filterBtnArea.innerHTML = '';
            }
        }
    }

    async function openDirectChat(userId){
        try{
            const res = await fetch(routes.direct(userId), { headers:{ 'Accept':'application/json' } });
            if (!res.ok) throw new Error('dm-fail');
            const g = await res.json();
            // Update/add group with plain name
            const existing = allGroups.find(x=> x.id === g.id);
            if (existing){ existing.name = g.name; }
            else { allGroups.push(g); }
            renderGroups(allGroups);
            const item = groupsEl.querySelector(`.list-group-item[data-group-id="${g.id}"]`);
            selectGroup(g.id, g.name, item);
        } catch(e){ alert('Failed to open direct chat'); }
    }

    // Add: open symmetric direct chat with peer
    async function openPeerChat(userId){
        try{
            // Only real admin uses admin<->user DM; everyone else uses user<->user DM2
            const url = isRootAdmin ? routes.direct(userId) : routes.directWith(userId);
            const res = await fetch(url, { headers:{ 'Accept':'application/json' } });
            if (!res.ok) throw new Error('dm-open-fail');
            const g = await res.json();
            const exist = allGroups.find(x=> x.id === g.id);
            if (exist) exist.name = g.name; else allGroups.unshift(g);
            renderGroups(allGroups);
            const item = groupsEl.querySelector(`.list-group-item[data-group-id="${g.id}"]`);
            selectGroup(g.id, g.name, item);
        } catch(e){ alert('Failed to open chat'); }
    }

    // Keep ONLY this async search handler (remote search)
    let searchAbort = null;
    searchEl.addEventListener('input', async ()=>{
        const q = (searchEl.value||'').trim();
        if (!q){ renderGroups(allGroups); return; }
        try{
            if (searchAbort) searchAbort.abort();
            searchAbort = new AbortController();
            const res = await fetch(routes.searchUsers(q), { headers:{ 'Accept':'application/json' }, signal: searchAbort.signal });
            if (!res.ok) throw new Error('search-fail');
            const users = await res.json();
            // Render results as a temporary list
            groupsEl.innerHTML = '';
            users.forEach(u=>{
                const a = document.createElement('a'); a.href='#'; a.className='list-group-item d-flex align-items-center';
                a.innerHTML = `<div class="wa-avatar me-2">${initials(u.name||('U'+u.id))}</div>
                               <div class="flex-grow-1">
                                 <div class="fw-semibold">${u.name || ('User '+u.id)}</div>
                                 <div class="small text-muted">Start chat</div>
                               </div>`;
                a.addEventListener('click', (e)=>{ e.preventDefault(); openPeerChat(u.id); });
                groupsEl.appendChild(a);
            });
        } catch(e){ /* ignore aborts */ }
    });

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

    function displayName(u){
        if (!u) return 'U';
        const name = (u.name || '').toString().trim();
        if (name) return name;
        return `User ${u.id ?? ''}`.trim();
    }

    function displayInitials(u){
        const n = displayName(u);
        return n.split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase();
    }

    function avatarLabel(m){
        // If avatar URL provided, render <img> (CHANGED: normalize URL)
        if (m && m.user && m.user.avatar) return { html: '<img src="'+toAbsoluteUrl(m.user.avatar)+'" alt="'+(m.user.name||'U')+'" loading="lazy">', text: null };
        const n = (m && m.user && m.user.name) ? m.user.name : (m && m.sender_name ? m.sender_name : 'U');
        const init = n ? n.split(' ').map(p=>p[0]).slice(0,2).join('').toUpperCase() : 'U';
        return { html: null, text: init };
    }

    function senderName(m){
        return (m && m.user && m.user.name) ? m.user.name : ((m && m.sender_name) ? m.sender_name : 'User');
    }

    // Messages
    function applyStatusClass(bubble, status){
        if (!status) return;
        bubble.classList.remove('status-booked','status-hold','status-unbooked','status-cancel');
        if (status === 'booked') bubble.classList.add('status-booked');
        else if (status === 'hold') bubble.classList.add('status-hold');
        else if (status === 'cancel') bubble.classList.add('status-cancel');
        else if (status === 'unbooked') bubble.classList.add('status-unbooked');
    }

    async function sendReply(originalId, text){
        const groupId = activeGroupId || (Array.isArray(cache) ? ((cache.find(x=>x && x.id===originalId) || {}).group_id || null) : null);
        if (!groupId) throw new Error('no-group');
        const payload = {
            group_id: groupId,
            type: 'text',
            content: text,
            message: text,
            body: text,
            text: text,
            description: text,
            reply_to_message_id: originalId
        };
        try{
            const res = await fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify(payload) });
            if (!res.ok) throw new Error('send-fail');
            await res.json();
            fetchMessages(groupId);
        } catch(err){
            const fd = new FormData();
            fd.append('group_id', groupId);
            fd.append('type','text');
            fd.append('content', text);
            fd.append('message', text);
            fd.append('body', text);
            fd.append('text', text);
            fd.append('description', text);
            fd.append('reply_to_message_id', originalId);
            const res = await fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken }, body: fd });
            if (!res.ok) throw new Error('send-fail');
            await res.json();
            fetchMessages(groupId);
        }
    }

    // Add back: reply prompt flow scoped to chat popup
    async function promptReply(originalId){
        let val = '';
        try{
            if (window.Swal && typeof Swal.fire === 'function'){
                const r = await swalInChat({
                    title: 'Reply',
                    input: 'textarea',
                    inputLabel: 'Your message',
                    inputPlaceholder: 'Type your message...',
                    inputAttributes: { 'aria-label': 'Your message' },
                    showCancelButton: true,
                    confirmButtonText: 'Send'
                });
                if (!r || !r.isConfirmed) return; val = (r.value||'').trim();
            } else {
                val = (prompt('Enter your message')||'').trim();
            }
            if (!val) return;
            await sendReply(originalId, val);
        } catch(e){
            console.error('Reply failed', e);
            alert('Failed to send reply. Please try again.');
        }
    }

    // Helper: scope SweetAlert2 to chat popup so it opens within chat, not the whole page
    function bumpChatZ(on){
        if (!popupEl) return;
        if (on){ popupEl.dataset.prevZ = popupEl.style.zIndex || ''; popupEl.style.zIndex = '1205'; }
        else { popupEl.style.zIndex = popupEl.dataset.prevZ || ''; delete popupEl.dataset.prevZ; }
    }
    function swalInChat(options){
        if (!window.Swal || !popupEl) return Swal.fire(options||{});
        const base = {
            target: popupEl,
            backdrop: true,
            heightAuto: false,
            willOpen: () => { bumpChatZ(true); },
            didOpen: () => {
                // Fallback: if container not under chat, move it
                const cont = document.querySelector('.swal2-container');
                if (cont && !popupEl.contains(cont)) { try { popupEl.appendChild(cont); } catch{} }
            },
            willClose: () => { bumpChatZ(false); }
        };
        return Swal.fire(Object.assign(base, options || {}));
    }

    // Messages
    function scrollToMessage(id){
        const node = messagesEl && messagesEl.querySelector ? messagesEl.querySelector(`[data-msg-id="${id}"]`) : null;
        if (!node || !messagesEl) return;
        const containerRect = messagesEl.getBoundingClientRect();
        const nodeRect = node.getBoundingClientRect();
        // Center the target inside the container, with small padding
        const current = messagesEl.scrollTop;
        const delta = (nodeRect.top - containerRect.top) - ((messagesEl.clientHeight - node.offsetHeight) / 2);
        const pad = 12;
        const targetTop = Math.max(0, current + delta - pad);
        messagesEl.scrollTo({ top: targetTop, behavior: 'smooth' });
        node.classList.add('wa-highlight');
        setTimeout(()=> node.classList.remove('wa-highlight'), 1500);
    }

    function getMsgById(id){
        return Array.isArray(cache) ? cache.find(x => x && x.id === id) : null;
    }

    function findRootMessageId(fromId){
        let guard = 0;
        let cur = getMsgById(fromId);
        let rootId = fromId;
        while (cur && cur.reply_to_message_id && guard++ < 50){
            rootId = cur.reply_to_message_id;
            cur = getMsgById(rootId);
        }
        return rootId;
    }

    function messageRow(m){
        const mine = !!m.mine;
        const row = document.createElement('div'); row.className = 'wa-row ' + (mine ? 'sent' : 'received');
        row.dataset.msgId = m.id;
        const avatar = document.createElement('div'); avatar.className='wa-avatar';
        const av = avatarLabel(m);
        if (av && av.html) { avatar.innerHTML = av.html; } else { avatar.textContent = av.text || 'U'; }
        const bubble = document.createElement('div'); bubble.className = 'wa-bubble ' + (mine ? 'sent' : 'received');
        if (m.reply_to_message_id) {
            bubble.addEventListener('click', (e)=>{
                let el = e.target instanceof Element ? e.target : null;
                while (el) {
                    if (el.hasAttribute && el.hasAttribute('data-no-bubble')) return;
                    el = el.parentElement;
                }
                scrollToMessage(m.reply_to_message_id);
            });
        }
        const content = document.createElement('div'); content.className = 'wa-content';
        // Show sender's name only (no sender_guard)
        const senderNameText = m.sender_name || (m.user && m.user.name) || 'User';
        const nameEl = document.createElement('div'); nameEl.className = 'wa-sender'; nameEl.textContent = senderNameText;
        // Only real admin should open legacy direct chats on name click
        if (isRootAdmin && m.user && m.user.id){
            nameEl.classList.add('text-primary');
            nameEl.style.cursor = 'pointer';
            nameEl.title = 'Open direct chat';
            nameEl.addEventListener('click', (e)=>{ e.stopPropagation(); openDirectChat(m.user.id); });
        }
        content.appendChild(nameEl);
        const type = (m.type || m.message_type || '').toString().toLowerCase();
        const original = (m.original_name || m.file_name || m.name || '').toString();
        const mime = (m.mime || m.mimetype || m.file_mime || m.content_type || '').toString().toLowerCase();
        const hasFile = !!m.file_url;
        const textValue = bestText(m);
        const textTrim = (textValue||'').trim();
        const isAdminBooked = (m.sender_guard === 'admin') && /^booked\b/i.test(textTrim);
        const isAdminHold = (m.sender_guard === 'admin') && /^hold\b/i.test(textTrim);
        const isAdminCancel = (m.sender_guard === 'admin') && /^cancel\b/i.test(textTrim);
        const isPdf = type === 'pdf' || (hasFile && (mime === 'application/pdf' || /\.pdf$/i.test(original)));
        if (isAdminBooked) bubble.classList.add('status-booked');
        if (isAdminHold) bubble.classList.add('status-hold');
        if (isAdminCancel) bubble.classList.add('status-cancel');
        const stEff = effectiveStatus(m);
        if (stEff){ applyStatusClass(bubble, stEff); }

        const isImage = type === 'image' || (hasFile && mime.startsWith('image/'));
        const isAudio = type === 'voice' || type === 'audio' || (hasFile && mime.startsWith('audio/'));

        if (type === 'text' || (!hasFile && textValue)){
            const t = document.createElement('div'); t.className = 'wa-text'; t.textContent = textValue || '';
            content.appendChild(t);
        } else if (isImage){
            const wrap = document.createElement('div'); wrap.className = 'wa-image';
            // CHANGED: build absolute URL correctly
            const imgUrl = toAbsoluteUrl(m.file_url || '');
            const img = document.createElement('img'); img.src = imgUrl; img.alt = original || 'image'; img.loading = 'lazy'; img.decoding = 'async';
            wrap.appendChild(img);
            content.appendChild(wrap);
            if (textValue){ const cap = document.createElement('div'); cap.className='wa-caption'; cap.textContent = textValue; content.appendChild(cap); }
        } else if (isPdf){
            // WhatsApp-like document row
            const wrap = document.createElement('div'); wrap.className='wa-doc';
            // CHANGED: build absolute URL correctly
            const fileUrl = toAbsoluteUrl(m.file_url || '');
            const link = document.createElement('a'); link.href = fileUrl; link.target = '_blank'; link.rel = 'noopener noreferrer'; link.className='wa-doc-link';
            link.addEventListener('click', function(e){ e.stopPropagation(); });
            const icon = document.createElement('div'); icon.className='wa-doc-icon';
            icon.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM13 3.5 18.5 9H13V3.5z"/><rect x="7" y="13" width="10" height="1.8" rx=".9" fill="currentColor"/><rect x="7" y="16" width="6" height="1.8" rx=".9" fill="currentColor"/></svg>';
            const info = document.createElement('div'); info.className='wa-doc-info';
            const name = document.createElement('div'); name.className='wa-doc-name'; name.title = original || 'document.pdf'; name.textContent = original || 'Document.pdf';
            const meta = document.createElement('div'); meta.className='wa-doc-meta'; meta.textContent = 'PDF';
            info.appendChild(name); info.appendChild(meta);
            link.appendChild(icon); link.appendChild(info);
            const dl = document.createElement('a'); dl.href = fileUrl; dl.download = original || 'document.pdf'; dl.className='wa-doc-download'; dl.innerHTML = '<i class="fa fa-download"></i>';
            wrap.appendChild(link); wrap.appendChild(dl);
            content.appendChild(wrap);
            if (textValue){
                // Only show caption if it's not a duplicate of sender name
                if (textValue.trim() !== senderNameText.trim()) {
                    const cap = document.createElement('div'); cap.className='wa-caption'; cap.textContent = textValue; content.appendChild(cap);
                }
            }
        } else if (isAudio){
            const wrap = document.createElement('div'); wrap.className='wa-audio';
            // CHANGED: build absolute URL correctly
            const audio = document.createElement('audio'); audio.controls = true; audio.src = toAbsoluteUrl(m.file_url || '');
            wrap.appendChild(audio);
            content.appendChild(wrap);
            if (textValue){ const cap = document.createElement('div'); cap.className='wa-caption'; cap.textContent = textValue; content.appendChild(cap); }
        } else {
            const t = document.createElement('div'); t.className = 'wa-text';
            t.textContent = textValue || '[Unsupported message]';
            content.appendChild(t);
        }
        bubble.appendChild(content);
        // Show message ID at bottom for PDF, Hold, Cancel, Booked bubbles
        if (isPdf || isAdminHold || isAdminCancel || isAdminBooked) {
            const msgIdRow = document.createElement('div'); msgIdRow.className = 'wa-meta-row'; msgIdRow.style.display = 'flex'; msgIdRow.style.justifyContent = 'space-between';
            const msgIdEl = document.createElement('span'); msgIdEl.textContent = 'Message ID: ' + m.id;
            const timeEl = document.createElement('span'); timeEl.textContent = fmtTime(m.created_at);
            msgIdRow.appendChild(msgIdEl);
            msgIdRow.appendChild(timeEl);
            bubble.appendChild(msgIdRow);
        }
        const meta = document.createElement('div'); meta.className = 'wa-meta-row';
        if (!isPdf && !isAdminHold && !isAdminCancel && !isAdminBooked) {
            const time = document.createElement('span'); time.textContent = fmtTime(m.created_at); meta.appendChild(time);
            if (mine){ const check = document.createElement('i'); check.className = 'fa fa-check text-muted'; meta.appendChild(check); }
        }
        // Always show menu button for both admin and user
        const menuBtn = document.createElement('button');
        menuBtn.type = 'button';
        menuBtn.className = 'btn btn-link btn-sm p-0 ms-1 chat-menu-btn';
        menuBtn.innerHTML = '<i class="fa fa-chevron-down"></i>';
        menuBtn.setAttribute('data-no-bubble','');
        menuBtn.addEventListener('click', (e)=>{
            e.preventDefault(); e.stopPropagation();
            showActionDropdown(e.currentTarget, m.id);
        });
        meta.appendChild(menuBtn);
        bubble.appendChild(meta);
        if (mine){ row.appendChild(bubble); row.appendChild(avatar); } else { row.appendChild(avatar); row.appendChild(bubble); }
        // Removed reaction chips display under messages

        // Add reply context if this message is a reply
        if (m.reply_to_message_id){
            const ref = getMsgById(m.reply_to_message_id);
            const snippet = ref ? (bestText(ref) || (ref.type || '')).toString().slice(0,120) : 'message';
            const refBox = document.createElement('div'); refBox.className = 'wa-reply-ref';
            refBox.textContent = `Replying to: ${snippet}`;
            refBox.title = 'Click to view replied message';
            refBox.addEventListener('click', ()=> scrollToMessage(m.reply_to_message_id));
            content.appendChild(refBox);
        }
        return row;
    }
    // Expose message row for realtime append
    window.messageRow = messageRow;

    function renderMessages(list){
        const src = Array.isArray(list) ? list : [];
        const view = applyMessageFilters(src);
        messagesEl.querySelectorAll('.wa-row, .reaction-chip, .wa-day').forEach(n=>n.remove());
        if (!view.length){ emptyEl.style.display = 'flex'; syncMessagesPadding(); return; }
        emptyEl.style.display = 'none';
        let lastDay = null;
        view.forEach(m => {
            const d = toDate(m.created_at);
            if (!lastDay || !isSameDay(d, lastDay)){
                const sep = document.createElement('div'); sep.className='wa-day'; const span = document.createElement('span'); span.textContent = dayLabel(d); sep.appendChild(span); messagesEl.appendChild(sep);
                lastDay = d;
            }
            messagesEl.appendChild(messageRow(m));
        });
        messagesEl.scrollTop = messagesEl.scrollHeight;
        syncMessagesPadding();
        updateStatusCounts();
    }

    // Hold/Cancel action picker -> replaces emoji picker for reactions
    function showEmojiPicker(anchor, messageId){
        const picker = document.createElement('div');
        picker.className = 'border rounded bg-white p-2 shadow position-absolute';
        picker.style.zIndex = 991; // below header dropdowns (1000), above chat (990)
        picker.style.minWidth = '200px';
        picker.innerHTML = `
            <div class="d-grid gap-1">
                <button class="btn btn-sm btn-outline-secondary" data-act="Hold">Hold</button>
                <button class="btn btn-sm btn-outline-success" data-act="Booked">Booked</button>
                <button class="btn btn-sm btn-outline-danger" data-act="Cancel">Cancel</button>
            </div>
        `;

        async function choose(action){
            picker.remove();
            const msg = Array.isArray(cache) ? cache.find(x => x && x.id === messageId) : null;
            const original = msg ? bestText(msg) : '';
            const ref = msg ? `message #${msg.id} by ${senderName(msg)}` : 'message';
            let text = '';

            if (action === 'Booked') {
                text = 'Booked';
            } else if (action === 'Cancel' || action === 'Hold') {
                let reason = '';
                if (window.Swal && Swal.fire) {
                    const r = await swalInChat({
                        title: action,
                        input: 'textarea',
                        inputLabel: 'Reason',
                        inputPlaceholder: 'Enter reason...',
                        inputAttributes: { 'aria-label': 'Reason' },
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        preConfirm: (val)=>{ if(!val || !val.trim()) return 'Please enter a reason'; return val.trim(); }
                    });
                    if (!r || !r.isConfirmed) return; reason = (typeof r.value === 'string' ? r.value.trim() : '').trim();
                } else {
                    reason = prompt(`Enter reason to ${action}:`) || '';
                    reason = reason.trim(); if (!reason) return;
                }
                text = `${action} - Reason: ${reason}\nRef: ${ref}` + (original ? `: "${original.substring(0,200)}"` : '') + (msg && msg.file_url ? ' [Attachment]' : '');
            }

            try { await reactToMessage(messageId, action); } catch(e) {}
            const payload = { group_id: activeGroupId, type: 'text', content: text, reply_to_message_id: messageId };
            if (text) {
                fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify(payload) })
                    .then(r=>r.ok?r.json():Promise.reject())
                    .then(()=> fetchMessages(activeGroupId))
                    .catch(()=> alert('Failed to send action'));
            } else {
                fetchMessages(activeGroupId);
            }
        }

        document.body.appendChild(picker);
        const rect = anchor.getBoundingClientRect();
        picker.style.top = (window.scrollY + rect.bottom + 6) + 'px';
        picker.style.left = (window.scrollX + rect.left) + 'px';

        const closer = (e)=>{ if(!picker.contains(e.target)){ picker.remove(); document.removeEventListener('mousedown', closer);} };
        document.addEventListener('mousedown', closer);

        picker.querySelectorAll('button[data-act]').forEach(btn=>{
            btn.addEventListener('click', (e)=>{ e.preventDefault(); choose(btn.dataset.act); });
        });
    }

    function showActionDropdown(anchor, messageId){
        const isAdmin = isSuperAdmin;
        const picker = document.createElement('div');
        picker.className = 'border rounded bg-white p-2 shadow position-absolute';
        picker.style.zIndex = 991;
        picker.style.minWidth = '220px';
        picker.style.boxShadow = '0 4px 24px rgba(0,0,0,0.10)';
        picker.style.borderRadius = '16px';
        picker.style.padding = '12px 0';
        picker.style.fontSize = '15px';
        // Build menu
        let menuHtml = '<div class="d-grid gap-1">';
        if (isAdmin) {
            menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Hold"><i class="fa fa-pause me-2 text-secondary"></i> Hold</button>';
            menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Booked"><i class="fa fa-check me-2 text-success"></i> Booked</button>';
            menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Cancel"><i class="fa fa-times me-2 text-danger"></i> Cancel</button>';
        }
        menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Reply"><i class="fa fa-reply me-2 text-primary"></i> Reply</button>';
        menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Forward"><i class="fa fa-share me-2 text-info"></i> Forward</button>';
        menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Share"><i class="fa fa-share-alt me-2 text-secondary"></i> Share</button>';

        // CHANGED: only root admin can toggle; show the correct single action based on user's current flag
        const msg = Array.isArray(cache) ? cache.find(x => x && x.id === messageId) : null;
        const canToggle = !!(isRootAdmin && msg && msg.user && msg.user.id);
        if (canToggle){
            const isChatAdminNow = !!msg.user.is_chat_admin;
            menuHtml += '<hr class="my-1">';
            if (!isChatAdminNow) {
                menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="MakeAdmin"><i class="fa fa-user-plus me-2 text-success"></i> Make admin</button>';
            } else {
                menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="RemoveAdmin"><i class="fa fa-user-times me-2 text-danger"></i> Remove admin</button>';
            }
        }

        menuHtml += '<button class="btn btn-sm btn-light text-start px-3 py-2" data-act="Delete"><i class="fa fa-trash me-2 text-danger"></i> Delete</button>';
        menuHtml += '</div>';
        picker.innerHTML = menuHtml;

        // Append picker inside chat popup instead of document.body
        popupEl.appendChild(picker);

        // Position menu relative to anchor inside chat popup
        const anchorRect = anchor.getBoundingClientRect();
        const popupRect = popupEl.getBoundingClientRect();
        const pickerRect = picker.getBoundingClientRect();
        let top = anchorRect.bottom - popupRect.top + 8;
        let left = anchorRect.left - popupRect.left;
        // Prevent overflow right
        if (left + pickerRect.width > popupRect.width - 16) left = popupRect.width - pickerRect.width - 16;
        // Prevent overflow left
        if (left < 8) left = 8;
        // Prevent overflow bottom
        if (top + pickerRect.height > popupRect.height - 16) top = popupRect.height - pickerRect.height - 16;
        picker.style.position = 'absolute';
        picker.style.top = top + 'px';
        picker.style.left = left + 'px';

        const closer = (e)=>{
            // Only close if click is outside picker and outside anchor
            if(!picker.contains(e.target) && e.target !== anchor){
                picker.remove();
                document.removeEventListener('mousedown', closer);
            }
        };
        document.addEventListener('mousedown', closer);

        picker.querySelectorAll('button[data-act]').forEach(btn=>{
            btn.addEventListener('click', (e)=>{ e.preventDefault(); choose(btn.dataset.act); });
        });
        async function choose(action){
            picker.remove();

            // IMPORTANT: compute once and reuse; avoid redeclaring `msg` below
            const currentMsg = msg || (Array.isArray(cache) ? cache.find(x=> x && x.id === messageId) : null);

            if (action === 'Reply') { promptReply(messageId); return; }
            if (action === 'Forward') { promptForward(messageId); return; }
            if (action === 'Share') { promptShare(messageId); return; }
            if (action === 'Delete') { promptDelete(messageId); return; }

            // Make/Remove admin
            if (action === 'MakeAdmin' || action === 'RemoveAdmin') {
                if (!isRootAdmin) return;
                const uid = currentMsg && currentMsg.user ? currentMsg.user.id : null;
                if (!uid) return;
                try{
                    const ok = !window.Swal
                        ? confirm((action==='MakeAdmin'?'Make':'Remove') + ' admin?')
                        : (await Swal.fire({ title: (action==='MakeAdmin'?'Make admin':'Remove admin'), icon:'question', showCancelButton:true, confirmButtonText:'Yes' })).isConfirmed;
                    if (!ok) return;

                    const res = await fetch(routes.setChatAdmin(uid), {
                        method:'POST',
                        headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' },
                        body: JSON.stringify({ is_admin: action === 'MakeAdmin' })
                    });
                    const json = await res.json().catch(()=>null);
                    if (!res.ok) {
                        const err = (json && (json.message || json.error)) || 'Failed to update.';
                        throw new Error(err);
                    }

                    // Optimistic local cache update
                    const newVal = action === 'MakeAdmin';
                    try {
                        if (Array.isArray(cache)) {
                            for (let i=0;i<cache.length;i++){
                                if (cache[i] && cache[i].user && Number(cache[i].user.id) === Number(uid)) {
                                    cache[i].user.is_chat_admin = newVal;
                                }
                            }
                        }
                    } catch(_) {}

                    if (window.Swal) Swal.fire({ icon:'success', title:'Done', timer:1000, showConfirmButton:false });
                    else alert('Done.');
                    if (activeGroupId) { try { fetchMessages(activeGroupId); } catch(_) {} }
                } catch(e){
                    const msgTxt = (e && e.message) ? e.message : 'Failed to update.';
                    if (window.Swal) Swal.fire({ icon:'error', title:'Error', text: msgTxt });
                    else alert(msgTxt);
                }
                return;
            }

            // Hold / Booked / Cancel
            // NOTE: do not redeclare `msg` here
            const original = currentMsg ? bestText(currentMsg) : '';
            const ref = currentMsg ? `message #${currentMsg.id} by ${senderName(currentMsg)}` : 'message';
            let text = '';
            if (action === 'Booked') { text = 'Booked'; }
            else if (action === 'Cancel' || action === 'Hold') {
                let reason = '';
                if (window.Swal && Swal.fire) {
                    const r = await swalInChat({
                        title: action,
                        input: 'textarea',
                        inputLabel: 'Reason',
                        inputPlaceholder: 'Enter reason...',
                        inputAttributes: { 'aria-label': 'Reason' },
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        preConfirm: (val)=>{ if(!val || !val.trim()) return 'Please enter a reason'; return val.trim(); }
                    });
                    if (!r || !r.isConfirmed) return; reason = (typeof r.value === 'string' ? r.value.trim() : '').trim();
                } else {
                    reason = prompt(`Enter reason to ${action}:`) || '';
                    reason = reason.trim(); if (!reason) return;
                }
                text = `${action} - Reason: ${reason}\nRef: ${ref}` + (original ? `: "${original.substring(0,200)}"` : '') + (currentMsg && currentMsg.file_url ? ' [Attachment]' : '');
            }
            try { await reactToMessage(messageId, action); } catch(e) {}
            const payload = { group_id: activeGroupId, type: 'text', content: text, reply_to_message_id: messageId };
            if (text) {
                fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify(payload) })
                    .then(r=>r.ok?r.json():Promise.reject())
                    .then(()=> fetchMessages(activeGroupId))
                    .catch(()=> alert('Failed to send action'));
            } else {
                fetchMessages(activeGroupId);
            }
        }
    }

    async function promptForward(messageId){
        // Pick a group to forward to
        try{
            const groups = allGroups.filter(g=> g.id !== activeGroupId);
            if (!groups.length) return alert('No other groups to forward to.');
            let groupId = null;
            if (window.Swal && Swal.fire){
                const opts = groups.map(g=> `<option value="${g.id}">${g.name}</option>`).join('');
                const r = await swalInChat({
                    title: 'Forward Message',
                    html: `<select id="forwardGroup" class="form-control">${opts}</select>`,
                    showCancelButton: true,
                    confirmButtonText: 'Forward',
                    preConfirm: ()=> document.getElementById('forwardGroup').value
                });
                if (!r || !r.isConfirmed) return;
                groupId = r.value;
            } else {
                groupId = prompt('Enter group Name to forward to:');
            }
            if (!groupId) return;
            const msg = getMsgById(messageId);
            if (!msg) return;
            const payload = {
                group_id: groupId,
                type: msg.type,
                content: bestText(msg),
                reply_to_message_id: null
            };
            if (msg.file_url){
                // For simplicity, just send text; file forwarding needs backend support
                payload.content += '\n[Forwarded attachment: ' + (msg.original_name||'file') + ']';
            }
            await fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' }, body: JSON.stringify(payload) });
            alert('Message forwarded.');
        } catch(e){ alert('Failed to forward.'); }
    }

    function promptShare(messageId){
    const msg = getMsgById(messageId);
    if (!msg) return alert('Message not found.');
    let shareText = bestText(msg);
    if (msg.file_url) {
        shareText += '\n' + msg.file_url;
    }
    if (navigator.share) {
        navigator.share({
            title: 'Shared Message',
            text: shareText,
            url: window.location.href
        }).catch(()=>{});
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(shareText)
            .then(()=> alert('Message copied to clipboard!'))
            .catch(()=> alert('Failed to copy.'));
    }
    }
    
    async function promptDelete(messageId){
        // Use SweetAlert2 for confirmation if available
        if (window.Swal && typeof Swal.fire === 'function') {
            const result = await Swal.fire({
                title: 'Delete Message',
                text: 'Are you sure you want to delete this message?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#d33'
            });
            if (!result.isConfirmed) return;
        } else {
            if (!confirm('Delete this message?')) return;
        }
        try{
            await fetch(routes.send + '/' + messageId, { method:'DELETE', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' } });
            fetchMessages(activeGroupId);
        } catch(e){ alert('Delete failed.'); }
    }

    // Backend
    async function fetchGroups(){
        try {
            const res = await fetch(routes.groups, { headers: { 'Accept':'application/json' } });
            if (!res.ok) {
                console.error('chat/groups failed:', res.status, res.statusText);
                groupsEl.innerHTML = '<div class="p-3 text-muted small">Unable to load chats.</div>';
                return;
            }
            let data;
            try { data = await res.json(); }
            catch(e){
                console.error('chat/groups JSON parse failed');
                groupsEl.innerHTML = '<div class="p-3 text-muted small">Unable to load chats.</div>';
                return;
            }
            allGroups = Array.isArray(data) ? data : [];
            window.allGroups = allGroups;
            renderGroups(allGroups);
            updateHeaderBadge();
            if (window.__CHAT_FETCH_COUNTS__) { try { await window.__CHAT_FETCH_COUNTS__(); } catch(_){} }
            try {
                const state = getState();
                if (state && state.activeGroupId){
                    const item = groupsEl.querySelector(`.list-group-item[data-group-id="${state.activeGroupId}"]`);
                    if (item) { selectGroup(state.activeGroupId, item.dataset.groupName || '', item); }
                }
            } catch(_) {}
        } catch (err) {
            console.error('chat/groups error:', err);
            groupsEl.innerHTML = '<div class="p-3 text-muted small">Unable to load chats.</div>';
        }
    }
    window.fetchGroups = fetchGroups;

    async function fetchMessages(groupId){
        loadingEl.style.display = 'block';
        try {
            const url = new URL(routes.messages, window.location.origin);
            url.searchParams.set('group_id', groupId);
            const res = await fetch(url, { headers: { 'Accept':'application/json' } });
            if (!res.ok) throw new Error('messages-http-' + res.status);
            let data;
            try { data = await res.json(); } catch { throw new Error('messages-json-parse'); }
            // Build fresh cache and id index
            cache = Array.isArray(data) ? data : [];
            idIndex = new Set(cache.map(m=> m && m.id));
            lastMessageId = cache.length ? cache[cache.length-1].id : 0;
            window.lastMessageId = lastMessageId;
            renderMessages(cache);
            // mark this group as seen and refresh header badge
            try {
                await markGroupSeen(groupId, lastMessageId);
                updateHeaderBadge();
                if (window.__CHAT_FETCH_COUNTS__) window.__CHAT_FETCH_COUNTS__();
            } catch(_) {}
        } catch(err){
            console.error('fetchMessages error:', err);
            emptyEl.style.display = 'flex';
        } finally {
            loadingEl.style.display = 'none';
        }
    }
    window.fetchMessages = fetchMessages;

    async function poll(){
        if (polling) return;
        if (!activeGroupId || lastMessageId === null) return;
        polling = true;
        try{
            const url = new URL(routes.messagesSince, window.location.origin);
            url.searchParams.set('group_id', activeGroupId);
            url.searchParams.set('after_id', lastMessageId);
            const res = await fetch(url, { headers: { 'Accept':'application/json' } });
            if (!res.ok) return; // silently skip
            let data = [];
            try { data = await res.json(); } catch { data = []; }
            if (Array.isArray(data) && data.length){
                const fresh = [];
                for (const m of data){ if (!m || idIndex.has(m.id)) continue; idIndex.add(m.id); fresh.push(m); }
                if (fresh.length){
                    cache = cache.concat(fresh);
                    lastMessageId = cache[cache.length-1].id;
                    renderMessages(cache);
                }
            }
        } finally {
            polling = false;
        }
    }

    // Ensure only one polling interval exists globally
    if (window.__CHAT_POLL_INTERVAL) { try { clearInterval(window.__CHAT_POLL_INTERVAL); } catch{} }
    window.__CHAT_POLL_INTERVAL = setInterval(poll, 5000);

    // Generic FormData sender used by uploads and voice notes
    async function sendMessage(formData){
        if (!formData) return;
        try {
            const res = await fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' }, body: formData });
            if (!res.ok) throw new Error('send-fail');
            await res.json();
            if (activeGroupId) fetchMessages(activeGroupId);
        } catch(e){ alert('Failed to send message.'); }
    }

    // Ensure text persists: try JSON with multiple aliases, fallback to FormData
    async function sendTextMessage(text){
        const payload = {
            group_id: activeGroupId,
            type: 'text',
            content: text,
            message: text,
            body: text,
            text: text,
            description: text
        };
        try{
            const res = await fetch(routes.send, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' },
                body: JSON.stringify(payload)
            });
            if (!res.ok) throw new Error('json-fail');
            await res.json();
            fetchMessages(activeGroupId);
        } catch(err){
            const fd = new FormData();
            fd.append('group_id', activeGroupId);
            fd.append('type','text');
            fd.append('content', text);
            fd.append('message', text);
            fd.append('body', text);
            fd.append('text', text);
            fd.append('description', text);
            sendMessage(fd);
        }
    }

    async function reactToMessage(messageId, emoji){
        return fetch(routes.react(messageId), { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify({ type: emoji }) });
    }
    async function clearReaction(messageId){
        return fetch(routes.react(messageId), { method:'DELETE', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' } });
    }

    // Mark a group seen helper
    async function markGroupSeen(groupId, lastId){
        if (!groupId) return;
        try {
            await fetch(routes.markSeen, {
                method:'POST',
                headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' },
                body: JSON.stringify({ group_id: groupId, last_id: lastId || undefined })
            });
        } catch(e) { /* ignore */ }
    }

    // UI events
    // REMOVED: duplicate local search handler (filter by name). The remote handler above already handles this.
    // searchEl.addEventListener('input', async ()=>{ const q = searchEl.value.toLowerCase(); const filtered = allGroups.filter(g=> g.name.toLowerCase().includes(q)); renderGroups(filtered); });

    attachBtn.addEventListener('click', ()=> fileInput.click());
    fileInput.addEventListener('change', ()=>{
        if (!activeGroupId) return; const f = fileInput.files[0]; if (!f) return;
        const mime = (f.type||'').toLowerCase(); const ext = (f.name||'').toLowerCase().split('.').pop();
        const imageExts = new Set(['jpg','jpeg','png','gif','webp','bmp','heic','heif']);
        const audioExts = new Set(['mp3','wav','m4a','ogg','webm','aac','oga']);
        let kind = '';
        if (mime.startsWith('image/') || imageExts.has(ext)) kind = 'image';
        else if (mime === 'application/pdf' || ext === 'pdf') kind = 'pdf';
        else if (mime.startsWith('audio/') || audioExts.has(ext) || mime==='video/webm' || mime==='application/octet-stream') kind = 'voice';
               else kind = 'file';
        if (kind === 'file'){ alert('Unsupported file type. Please upload image, pdf, or audio.'); fileInput.value=''; return; }
        const fd = new FormData(); fd.append('group_id', activeGroupId); fd.append('type', kind); fd.append('file', f);
        fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json' }, body: fd })
            .then(r=> r.ok ? r.json() : Promise.reject(r))
            .then(()=> fetchMessages(activeGroupId))
            .catch(async err=>{ try{ const j=await err.json(); alert(j.message||'Upload failed'); }catch{ alert('Upload failed'); } })
            .finally(()=>{ fileInput.value=''; });
    });
    sendBtn.addEventListener('click', ()=>{
        if (!activeGroupId) return; const text = (msgInput.value||'').trim(); if (!text) { msgInput.focus(); return; }
        sendTextMessage(text);
        msgInput.value=''; toggleSendMic();
    });
    // Enter to send
    msgInput.addEventListener('keydown', (e)=>{
        if (e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); if ((msgInput.value||'').trim()){ sendBtn.click(); } }
    });

    // Emoji insert into composer
    emojiBtn.addEventListener('click', (e)=>{
        e.preventDefault();
        const menu = document.createElement('div'); menu.className='border rounded bg-white p-2 shadow position-absolute';
        menu.style.zIndex = 991; // ensure header menus can overlay chat
        const list = ['😀','😂','😍','👍','🎉','😮','😢','🔥','🙏'];
        list.forEach(em=>{ const b=document.createElement('button'); b.className='btn btn-light btn-sm'; b.textContent=em; b.addEventListener('click', ()=>{ msgInput.value = (msgInput.value||'') + em; toggleSendMic(); menu.remove(); msgInput.focus(); }); menu.appendChild(b); });
        document.body.appendChild(menu);
        const rect = emojiBtn.getBoundingClientRect(); menu.style.top = (window.scrollY + rect.bottom + 6) + 'px'; menu.style.left = (window.scrollX + rect.left) + 'px';
        const closer=(ev)=>{ if(!menu.contains(ev.target)){ menu.remove(); document.removeEventListener('mousedown', closer);} }; document.addEventListener('mousedown', closer);
    });

    // Mic/Send toggle
    function toggleSendMic(){ const hasText = (msgInput.value||'').trim().length > 0; sendBtn.style.display = hasText ? 'inline-flex' : 'none'; voiceBtn.style.display = hasText ? 'none' : 'inline-flex'; syncMessagesPadding(); }
    msgInput.addEventListener('input', toggleSendMic);

    // Voice recording
    async function toggleRecording(){
        if (mediaRecorder && mediaRecorder.state === 'recording'){
            mediaRecorder.stop(); recordingHint.style.display='none'; voiceBtn.classList.remove('btn-danger');
            syncMessagesPadding();
            return;
        }
        try {
           
            const stream = await navigator.mediaDevices.getUserMedia({ audio:true });
            recordedChunks = []; mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
            mediaRecorder.ondataavailable = (e)=>{ if (e.data.size > 0) recordedChunks.push(e.data); };
            mediaRecorder.onstop = ()=>{
                const blob = new Blob(recordedChunks, { type:'audio/webm' });
                const file = new File([blob], 'voice.webm', { type:'audio/webm' });
                const fd = new FormData(); fd.append('group_id', activeGroupId); fd.append('type','voice'); fd.append('file', file);
                sendMessage(fd);
                syncMessagesPadding();
            };
            mediaRecorder.start(); recordingHint.style.display='block'; voiceBtn.classList.add('btn-danger');
            syncMessagesPadding();
        } catch(err){ console.error(err); alert('Microphone access denied.'); }
    }
    voiceBtn.addEventListener('click', toggleRecording);

    function detectTopChrome(){
        let maxBottom = 0;
        try{
            const nodes = document.querySelectorAll('body *');
            for (let i=0;i<nodes.length;i++){
                const el = nodes[i];
                const cs = window.getComputedStyle(el);
                if (!cs) continue;
                const pos = cs.position;
                if (pos !== 'fixed' && pos !== 'sticky') continue;
                const r = el.getBoundingClientRect();
                if (r.height < 40) continue; // ignore small badges
                if (r.top > 12) continue; // only bars near top
                if (r.width < window.innerWidth * 0.4) continue; // must span
                maxBottom = Math.max(maxBottom, r.bottom);
            }
        } catch{}
        return maxBottom;
    }

    function applyExpandedBounds(){
        if (!popupEl.classList.contains('expanded')) return;
        const headerSelectors = ['.header','header','.topbar','.navbar','.app-header','.main-header','#header','.page-header'];
        const sidebarSelectors = [
            '.sidebar','.sidebar-menu','.sidebar-wrapper','.sidebar-main','.app-sidebar','aside.sidebar',
            '.left-side-menu','.sidebar-left','.page-sidebar','#sidebar','#sidebar-wrapper','.side-nav',
            '#sidebarMenu','#kt_aside','.vertical-menu','.ant-layout-sider','.MuiDrawer-root','.nav-sidebar',
            '.navbar-vertical','.layout-sidebar'
        ];
        const boundsSelectors = [
            '[data-chat-bounds]','main','.content-wrapper','.main-content','#content','.app-content','.page-wrapper',
            '#app','.wrapper','#page-content','.container-fluid','.content','#layout-wrapper'
        ];
        const pickMaxBottom = (sels)=> sels.reduce((acc,sel)=>{
            const el = document.querySelector(sel); if (!el) return acc; const r = el.getBoundingClientRect();
            return Math.max(acc, r.bottom);
        }, 0);
        const pickMaxRight = (sels)=> sels.reduce((acc,sel)=>{
            const el = document.querySelector(sel); if (!el) return acc; const r = el.getBoundingClientRect();
            if (r.width <= 0) return acc; return Math.max(acc, r.right);
        }, 0);
        let headerBottom = pickMaxBottom(headerSelectors);
        headerBottom = Math.max(headerBottom, detectTopChrome());
        let leftEdge = pickMaxRight(sidebarSelectors);
        // If no sidebar matched but there is a column taking left side, probe common layout columns
        if (!leftEdge){
            const probe = Array.from(document.body.children).filter(n=>{
                const r = n.getBoundingClientRect(); return r.left < 80 && r.width > 150 && r.height > 200; });
            leftEdge = probe.reduce((m,n)=> Math.max(m, n.getBoundingClientRect().right), 0);
        }
        // Prefer explicit bounds container if present
        let boundsEl = null;
        for (const sel of boundsSelectors){ const c = document.querySelector(sel); if (c){ boundsEl = c; break; } }
        if (boundsEl){
            const br = boundsEl.getBoundingClientRect();
            const top = Math.max(0, Math.max(br.top, headerBottom));
            const bottomLimit = Math.min(window.innerHeight, br.bottom);
            const left = Math.max(br.left, leftEdge);
            const width = Math.max(320, window.innerWidth - left);
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
        // Fallback using viewport
        const top = Math.max(0, headerBottom);
        const left = Math.max(0, leftEdge);
        const width = Math.max(320, window.innerWidth - left);
        const height = Math.max(300, window.innerHeight - top);
        popupEl.style.top = top + 'px';
        popupEl.style.left = left + 'px';
        popupEl.style.width = width + 'px';
        popupEl.style.height = height + 'px';
        popupEl.style.right = '';
        popupEl.style.bottom = '';
        popupEl.style.borderRadius = '0';
    }

    // Hook after open to re-evaluate once layout settles
    const __openPopup = openPopup;
    openPopup = function(){
        __openPopup();
        if (popupEl.classList.contains('expanded')){
            requestAnimationFrame(applyExpandedBounds);
            setTimeout(applyExpandedBounds, 150);
            setTimeout(applyExpandedBounds, 350);
        }
    };

    // Also observe DOM changes that might shift header height
    const mo = new MutationObserver(()=>{ if (popupEl.classList.contains('expanded')) applyExpandedBounds(); });
    mo.observe(document.body, { attributes:false, childList:true, subtree:true });

    // On load, restore persisted state
    (function restorePersisted(){
        const state = getState();
        if (state.expanded) { popupEl.classList.add('expanded'); }
        if (state.open) {
            popupEl.style.display = 'flex';
            if (popupEl.classList.contains('expanded')) { applyExpandedBounds(); requestAnimationFrame(applyExpandedBounds); setTimeout(applyExpandedBounds, 120); }
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
    newSessionBtn && newSessionBtn.addEventListener('click', function(){
        const name = prompt('Enter new session name');
        if (!name || !name.trim()) return;
        createSession(name.trim());
    });

    window.addEventListener('resize', function(){ applyExpandedBounds(); syncMessagesPadding(); });
    window.addEventListener('scroll', function(){ if (popupEl.classList.contains('expanded')) syncMessagesPadding(); }, { passive: true });
    window.addEventListener('load', function(){ if (popupEl.classList.contains('expanded')) { applyExpandedBounds(); } });
    // Remove duplicate extra interval; ensure single guarded timer only
    if (!window.__CHAT_POLL_INTERVAL) { window.__CHAT_POLL_INTERVAL = setInterval(poll, 5000); }

    // --- Make chat popup draggable ---
    let dragActive = false, dragOffset = {x:0, y:0}, dragStart = {x:0, y:0};
    const headerEl = document.querySelector('.chat-popup-header');
    let lastPos = {top:null, left:null};

    function onDragStart(e){
        if (popupEl.classList.contains('expanded')) return; // don't drag in expanded mode
        dragActive = true;
        const rect = popupEl.getBoundingClientRect();
        dragOffset.x = (e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX) - rect.left;
        dragOffset.y = (e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY) - rect.top;
        dragStart.x = rect.left;
        dragStart.y = rect.top;
        document.body.style.userSelect = 'none';
    }
    function onDragMove(e){
        if (!dragActive) return;
        const x = (e.type.startsWith('touch') ? e.touches[0].clientX : e.clientX) - dragOffset.x;
        const y = (e.type.startsWith('touch') ? e.touches[0].clientY : e.clientY) - dragOffset.y;
        // Clamp within viewport
        const vw = window.innerWidth, vh = window.innerHeight;
        const w = popupEl.offsetWidth, h = popupEl.offsetHeight;
        const left = Math.max(0, Math.min(x, vw-w));
        const top = Math.max(0, Math.min(y, vh-h));
        popupEl.style.left = left + 'px';
        popupEl.style.top = top + 'px';
        popupEl.style.right = '';
        popupEl.style.bottom = '';
        lastPos = {top, left};
    }
    function onDragEnd(){
        dragActive = false;
        document.body.style.userSelect = '';
    }
    if (headerEl){
        headerEl.style.cursor = 'move';
        headerEl.addEventListener('mousedown', onDragStart);
        headerEl.addEventListener('touchstart', onDragStart, {passive:false});
        window.addEventListener('mousemove', onDragMove);
        window.addEventListener('touchmove', onDragMove, {passive:false});
        window.addEventListener('mouseup', onDragEnd);
        window.addEventListener('touchend', onDragEnd);
    }

    // --- Hide popup when clicking outside (always, even if expanded) ---
    document.addEventListener('mousedown', function(e){
        if (!popupEl || popupEl.style.display !== 'flex') return;
        if (!popupEl.contains(e.target)){
            popupEl.style.display = 'none';
            setState({ open: false });
        }
    });
    document.addEventListener('touchstart', function(e){
        if (!popupEl || popupEl.style.display !== 'flex') return;
        if (!popupEl.contains(e.target)){
            popupEl.style.display = 'none';
            setState({ open: false });
        }
    });

    // --- When opening, restore last drag position if any ---
    function openPopup(){
        popupEl.style.display = 'flex';
        setState({ open: true });
        if (popupEl.classList.contains('expanded')) { applyExpandedBounds(); requestAnimationFrame(applyExpandedBounds); }
        else if (lastPos.top !== null && lastPos.left !== null){
            popupEl.style.top = lastPos.top + 'px';
            popupEl.style.left = lastPos.left + 'px';
            popupEl.style.right = '';
            popupEl.style.bottom = '';
        }
        if (!allGroups.length) { fetchGroups(); }
    }
    window.openChat = openPopup; window.openPopup = openPopup;
    document.addEventListener('click', function(e){
        const btn = e.target.closest('#chatToggle');
        if (btn){ e.preventDefault(); openPopup(); }
    });
    document.addEventListener('keydown', function(e){ if ((e.ctrlKey||e.metaKey) && e.key === 'i'){ openPopup(); } });

    // Expose globals for external notifications
    window.__CHAT_OPEN_GROUP__ = function(groupId){
        try {
            openPopup();
            let tries = 0;
            const pick = function(){
                const item = document.querySelector(`#chatGroups .list-group-item[data-group-id="${groupId}"]`);
                if (item) { item.click(); return; }
                if (tries++ < 20) { setTimeout(pick, 150); } else { /* give up */ }
            };
            if (!allGroups.length) { fetchGroups().then(()=> pick()).catch(()=> pick()); } else { pick(); }
        } catch(e) {}
    };
    window.__CHAT_QUICK_REPLY__ = async function(groupId, text, replyToId){
        try {
            if (!groupId || !text) return;
            const payload = { group_id: groupId, type:'text', content: text };
            if (replyToId) payload.reply_to_message_id = replyToId;
            await fetch(routes.send, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrfToken, 'Accept':'application/json', 'Content-Type':'application/json' }, body: JSON.stringify(payload) });
            if (window.__CHAT_FETCH_COUNTS__) window.__CHAT_FETCH_COUNTS__();
            if (activeGroupId === groupId) { try { fetchMessages(groupId); } catch(_) {} }
       

        } catch(e) {}
    };
})();
</script>

<!-- Pusher Cloud JS integration -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
(function(){
    // Prevent duplicate Pusher initialization across multiple chat scripts
    if (window.__CHAT_PUSHER_BOUND__) return;
    window.__CHAT_PUSHER_BOUND__ = true;

    // Pusher Cloud integration
    Pusher.logToConsole = true;
    var pusher = new Pusher('500d2fa7a4b11dbfeb91', {
        cluster: 'ap2',
        forceTLS: true
    });
    var channel = pusher.subscribe('chat');
    function handleChatEvent(data) {
        var msg = data && data.message ? data.message : data;
        msg.mine = window.currentUser && Number(msg.user_id) === Number(window.currentUser.id);

        if (Array.isArray(window.allGroups)) {
            let found = false;
            for (let i = 0; i < window.allGroups.length; ++i) {
                if (Number(window.allGroups[i].id) === Number(msg.group_id)) {
                    const g = window.allGroups[i];
                    g.latest = {
                        id: msg.id,
                        type: msg.type,
                        content: msg.content,
                        original_name: msg.original_name,
                        sender_guard: msg.sender_guard,
                        sender_name: msg.sender_name,
                        user: msg.user || null,
                        created_at: msg.created_at
                    };
                    g.last_msg_id = msg.id;
                    g.last_msg_at = msg.created_at;
                    // Only increment unread if not mine and not the active chat
                    if (!msg.mine && Number(msg.group_id) !== Number(window.activeGroupId)) {
                        g.unread = (parseInt(g.unread)||0) + 1;
                    }
                    window.allGroups.splice(i, 1);
                    window.allGroups.unshift(g);
                    found = true;
                    break;
                }
            }
            if (!found) {
                window.allGroups.unshift({
                    id: msg.group_id,
                    slug: msg.group_slug || '',
                    name: msg.sender_name || 'Chat',
                    avatar: (msg.user && msg.user.avatar) || '',
                    latest: {
                        id: msg.id,
                        type: msg.type,
                        content: msg.content,
                        original_name: msg.original_name,
                        sender_guard: msg.sender_guard,
                        sender_name: msg.sender_name,
                        user: msg.user || null,
                        created_at: msg.created_at
                    },
                    last_msg_id: msg.id,
                    last_msg_at: msg.created_at,
                    unread: (!msg.mine && Number(msg.group_id) !== Number(window.activeGroupId)) ? 1 : 0
                });
            }
            if (typeof window.renderGroups === 'function') window.renderGroups(window.allGroups);
        }

        if (typeof window.__CHAT_UPDATE_BADGE__ === 'function') window.__CHAT_UPDATE_BADGE__();

        // --- FIX: Do NOT append message bubble directly for active group ---
        // Instead, always rely on fetchMessages to update the UI.
        if (window.activeGroupId && Number(msg.group_id) === Number(window.activeGroupId)) {
            // Remove direct append:
            // if (typeof messageRow === 'function' && window.messagesEl) {
            //     window.messagesEl.appendChild(messageRow(msg));
            //     window.messagesEl.scrollTop = window.messagesEl.scrollHeight;
            // }
            // Active group is read
            if (Array.isArray(window.allGroups)) {
                for (let i = 0; i < window.allGroups.length; ++i) {
                    if (Number(window.allGroups[i].id) === Number(msg.group_id)) {
                        window.allGroups[i].unread = 0;
                        break;
                    }
                }
                if (typeof window.renderGroups === 'function') window.renderGroups(window.allGroups);
            }
            if (typeof window.__CHAT_UPDATE_BADGE__ === 'function') window.__CHAT_UPDATE_BADGE__();
            // Always fetch messages to update UI
            fetchMessages(window.activeGroupId);
        }
    }
    channel.bind('App\\Events\\MessageSent', handleChatEvent);
    channel.bind('ChatMessageBroadcast', handleChatEvent);

    // --- Debugging: log all events ---
    channel.bind('pusher:subscription_succeeded', function() {
        console.log('[Pusher] Subscribed to chat channel');
    });
    channel.bind('pusher:subscription_error', function(status) {
        console.error('[Pusher] Subscription error:', status);
    });
    channel.bind('pusher:error', function(err) {
        console.error('[Pusher] Error:', err);
    });
})();
</script>
<?php /**PATH A:\GenTech\htdocs\GenlabV1.0\GenLabV1.0\resources\views/superadmin/layouts/include/chat.blade.php ENDPATH**/ ?>