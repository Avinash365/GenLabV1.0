<?php $__env->startSection('title', 'Chat'); ?>

<?php
    $page = 'chat';
    $authUser = $authUser ?? null;
    $currentUserId = $myId ?? ($authUser ? $authUser->id : null);
    $currentUserType = $currentUserType ?? ($authUser instanceof \App\Models\Admin ? 'admin' : ($authUser instanceof \App\Models\User ? 'user' : ($authUser instanceof \App\Models\Employee ? 'employee' : null)));
?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Chat layout adjustments to ensure scrolling works with the new theme */
    .chat-wrapper { display: flex; gap: 10px; align-items: stretch; height:87vh;}
    .sidebar-group { flex: 0 0 360px; max-width: 360px; display: flex; flex-direction: column; }
    .sidebar-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .sidebar-body { flex: 1; overflow-y: auto; }
    .chat.chat-messages { flex: 1; display: flex; flex-direction: column; border-left: 1px solid #f3f3f3; }
    .chat-body.chat-page-group { flex: 1; overflow-y: auto; min-height: 0; }
    .chat-footer { flex-shrink: 0; }
     /* center placeholder reliably using flexbox so it remains visible
         even when slimscroll or container heights change */
     .messages { display:flex; align-items:center; justify-content:center; min-height: 360px; padding: 5px; box-sizing: border-box; }
    .messages .centered-placeholder { position: static; transform: translateX(100px); width: 100%; max-width: 520px; }
    
    /* Ensure the search bar stays at top */
    .chat-search-header { padding: 15px; background: #fff; }
    
    /* Animation for typing */
    .animate-typing .dot { animation: blink 1s infinite; }
    .animate-typing .dot:nth-child(2) { animation-delay: 0.2s; }
    .animate-typing .dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes blink { 0% { opacity: 0; } 50% { opacity: 1; } 100% { opacity: 0; } }
    .chat-date-separator { margin: 18px 0; display:block; text-align:center; position:relative; width:100%; }
    .chat-date-separator::before,
    .chat-date-separator::after {
        content: '';
        position: absolute;
        top: 50%;
        height: 1px;
        background: #e6e9ef;
        transform: translateY(-50%);
    }
    .chat-date-separator::before { left: 12px; right: 50%; }
    .chat-date-separator::after { left: 50%; right: 12px; }
    .chat-date-separator .chat-date-pill { display:inline-block; background: #0b3b66; color:#ffffff; padding:8px 22px; border-radius:999px; font-weight:600; font-size:13px; box-shadow: 0 1px 0 rgba(0,0,0,0.04); position:relative; z-index:2; }

    /* Recording UI */
    .action-circle.recording { background: rgba(220,53,69,0.08); color: #dc3545; border-radius:50%; box-shadow: 0 0 0 3px rgba(220,53,69,0.06) inset; }
    .action-circle.recording i { animation: recordingPulse 1s infinite; }
    @keyframes recordingPulse { 0% { transform: scale(1); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
    /* Small red dot indicator for recording */
    .recording-dot { display:inline-block; width:8px; height:8px; background:#dc3545; border-radius:50%; margin-left:6px; vertical-align:middle; box-shadow: 0 0 6px rgba(220,53,69,0.6); }

    /* Audio bubble styles — waveform with left mic circle */
    .message-audio-bubble { display:flex; align-items:center; gap:12px; padding:10px 16px; border-radius:999px; max-width:420px; background:#f1f6fb; box-shadow: 0 1px 0 rgba(0,0,0,0.03); position:relative; }
    .chats-right .message-audio-bubble { background: linear-gradient(90deg,#e6f3ff,#d9efff); }
    .message-audio-bubble .audio-left { flex:0 0 46px; display:flex; align-items:center; justify-content:center; }
    .mic-circle { width:44px; height:44px; border-radius:50%; background:#0b78d1; color:#fff; display:flex; align-items:center; justify-content:center; box-shadow:0 6px 18px rgba(11,120,209,0.12); font-size:18px; }
    .chats-right .mic-circle { background:#0b78d1; }
    .audio-central { flex:1; display:flex; align-items:center; gap:12px; }
    .audio-waveform { display:flex; align-items:flex-end; gap:6px; width:100%; padding:6px 4px; }
    .audio-waveform .bar-vert { width:6px; background:rgba(11,59,102,0.12); border-radius:3px; height:18px; transform-origin:bottom center; transition:height 160ms ease; }
    /* a few preset heights so the static look is like the image */
    .audio-waveform .bar-vert.b1 { height:12px; }
    .audio-waveform .bar-vert.b2 { height:20px; }
    .audio-waveform .bar-vert.b3 { height:28px; }
    .audio-waveform .bar-vert.b4 { height:16px; }
    .audio-waveform .bar-vert.b5 { height:22px; }
    .audio-waveform .bar-vert.b6 { height:14px; }
    .audio-waveform .bar-vert.b7 { height:24px; }
    /* subtle animated wave when playing — smoother, smaller amplitude and staggered timings */
    .audio-waveform .bar-vert { opacity: 0.45; transition: opacity 220ms ease; }
    @keyframes wavePulse {
        0% { transform: scaleY(0.85); }
        40% { transform: scaleY(1.12); }
        100% { transform: scaleY(0.9); }
    }
    .message-audio-bubble.playing .audio-waveform .bar-vert { animation-name: wavePulse; animation-iteration-count: infinite; animation-timing-function: ease-in-out; animation-fill-mode: both; opacity: 1; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b1 { animation-duration: 1100ms; animation-delay: 0ms; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b2 { animation-duration: 1250ms; animation-delay: 80ms; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b3 { animation-duration: 1050ms; animation-delay: 160ms; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b4 { animation-duration: 1200ms; animation-delay: 240ms; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b5 { animation-duration: 950ms; animation-delay: 340ms; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b6 { animation-duration: 1300ms; animation-delay: 420ms; }
    .message-audio-bubble.playing .audio-waveform .bar-vert.b7 { animation-duration: 1000ms; animation-delay: 520ms; }
    .audio-meta { display:flex; align-items:center; gap:8px; font-size:12px; color:#495057; min-width:48px; justify-content:flex-end; }
    .chats-right .audio-meta { color:#08324f; }

    /* When a message contains only the audio bubble, remove extra outer background/padding */
    .message-content.audio-only { background: transparent !important; padding: 0 !important; box-shadow: none !important; }
    .message-content.audio-only .message-audio-bubble { margin: 0; }
    /* When a message contains only a media/file bubble, remove extra outer background/padding */
    .message-content.media-only, .message-content.file-only { background: transparent !important; padding: 0 !important; box-shadow: none !important; }
    .message-content.media-only .message-media-bubble, .message-content.file-only .message-media-bubble { margin: 0; }
    .message-content.media-only .message-file-bubble, .message-content.file-only .message-file-bubble { margin: 0; }
    .message-content.media-only .message-file-link, .message-content.file-only .message-file-link { display:inline-block; }
    /* Emoji picker (polished) */
    .emoji-picker { position: absolute; z-index: 9999; width: 520px; max-width: calc(100vw - 24px); background: #ffffff; border: 1px solid #e9edf2; box-shadow: 0 10px 30px rgba(8, 24, 48, 0.08); border-radius: 12px; padding: 8px; display: none; transform-origin: bottom right; transition: transform 180ms cubic-bezier(.2,.9,.3,1), opacity 140ms ease; opacity: 0; }
    .emoji-picker.show { display: block; opacity: 1; transform: translateY(-6px); }
    .emoji-picker:focus { outline: none; }
    .emoji-picker .emoji-header { display:flex; align-items:center; gap:8px; padding:6px 8px; border-bottom: 1px solid #f3f6f9; }
    .emoji-picker .emoji-search { flex:1; display:flex; align-items:center; background:#f8fafc; padding:8px 10px; border-radius:8px; border:1px solid transparent; transition: box-shadow .12s ease, border-color .12s ease; }
    .emoji-picker .emoji-search input { border: none; background: transparent; width:100%; outline: none; font-size:13px; color:#263238; }
    .emoji-picker .emoji-close { background: transparent; border: none; cursor: pointer; color:#6b7280; font-size:16px; padding:6px; border-radius:6px; }
    .emoji-picker .emoji-close:hover { background:#f1f5f9; }
    .emoji-picker .emoji-recent { display:flex; gap:8px; padding:8px; overflow-x:auto; -webkit-overflow-scrolling: touch; }
    .emoji-picker .emoji-recent .recent-item { min-width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:8px; cursor:pointer; background:#fff; border:1px solid transparent; transition: background .12s ease, transform .08s ease; }
    .emoji-picker .emoji-recent .recent-item:hover { background:#f1f5f9; transform: translateY(-2px); }
    .emoji-grid { display:grid; grid-template-columns: repeat(6, 1fr); gap:8px; padding:8px; max-height:220px; overflow:auto; }
    .emoji-cell { cursor:pointer; font-size:20px; padding:8px; border-radius:8px; display:flex; align-items:center; justify-content:center; transition: background .12s ease, transform .08s ease; }
    .emoji-cell:focus { outline: 2px solid rgba(11,120,209,0.12); box-shadow: 0 4px 14px rgba(11,120,209,0.06); }
    .emoji-cell:hover { background:#f1f5f9; transform: translateY(-4px); }
    .emoji-noresults { padding: 18px; text-align:center; color:#6b7280; font-size:13px; }
    /* Media bubbles */
    /* Media bubble sizing: wider layout for images/videos with a max-height to keep chat compact */
    .message-media-bubble { display:flex; flex-direction:column; gap:8px; max-width:520px; border-radius:12px; background:#fff; padding:8px; box-shadow: 0 6px 18px rgba(11,59,102,0.03); }
    .message-media-bubble img { max-width:520px; width:100%; height:auto; max-height:360px; object-fit:cover; border-radius:8px; display:block; cursor:pointer; }
    .message-media-bubble video { max-width:520px; width:100%; height:auto; max-height:360px; object-fit:cover; border-radius:8px; display:block; }
    /* File bubble sizing: compact single-row card with consistent height */
    .message-file-bubble { display:grid; grid-template-columns: 44px 1fr auto; gap:12px; align-items:center; padding:8px 12px; border-radius:12px; background:#f8fafc; border:1px solid #e9f2ff; max-width:420px; min-width:220px; min-height:52px; }
    .message-file-bubble .file-icon { width:40px; height:40px; border-radius:8px; background:#eef4ff; display:flex; align-items:center; justify-content:center; font-size:16px; color:#0b3b66; flex:0 0 44px; }
    .message-file-bubble .file-meta { display:flex; flex-direction:column; align-items:flex-start; gap:4px; min-width:0; overflow:hidden; }
    .message-file-bubble .file-title { display:block; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:600; color:#0b3b66; font-size:14px; }
    .message-file-bubble .file-ext { font-size:10px; padding:2px 6px; }
    .message-file-bubble .file-sub { font-size:12px; color:#04506f; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-weight:600; }
    .message-file-bubble .file-ext { margin-left:8px; font-size:10px; color:#04506f; background:rgba(11,120,209,0.06); padding:2px 6px; border-radius:6px; font-weight:700; }
    .message-file-bubble .file-sub { font-size:12px; color:#6b7280; margin-top:4px; display:block; }
    .message-file-bubble.compact { padding:6px 8px; }
    .message-file-bubble.compact .file-title { max-width:180px; font-size:13px; }
    .message-file-bubble.compact .file-sub { display:none; }
    .message-media-bubble.uploading { opacity:0.9; position:relative; }
    .message-media-bubble .upload-progress { height:6px; background:#e6eefb; border-radius:6px; overflow:hidden; margin-top:6px; }
    .message-media-bubble .upload-progress .bar { height:100%; width:0%; background:#0b78d1; transition: width .2s ease; }
    /* Polished media/file bubble visuals */
    .chats .message-media-bubble,
    .chats .message-file-bubble { background: #ffffff; border: 1px solid #eef3f8; }
    .chats-right .message-media-bubble,
    .chats-right .message-file-bubble { background: linear-gradient(90deg,#e6f3ff,#d9efff); border: 1px solid rgba(11,120,209,0.08); }
    .message-file-bubble .file-title { font-weight:600; color:#0b3b66; }
    .message-file-bubble .file-sub { font-size:12px; color:#6b7280; }
    .message-file-actions { margin-left:auto; display:flex; gap:8px; align-items:center; }
    .message-file-actions .btn { background:transparent; border:none; cursor:pointer; color:#0b3b66; padding:6px; border-radius:6px; display:inline-flex; align-items:center; justify-content:center; }
    .message-file-actions .btn:hover { background:rgba(11,120,209,0.06); }
    .message-file-link { display:inline-block; text-decoration:none; color:inherit; }
    .message-file-link .message-file-bubble { margin:0; }
    /* Responsive adjustments for narrow viewports */
    @media (max-width: 700px) {
        .message-media-bubble { max-width:360px; }
        .message-media-bubble img, .message-media-bubble video { max-width:360px; max-height:280px; }
        .message-file-bubble { max-width:320px; min-width:160px; }
        .message-file-bubble .file-title { font-size:13px; }
        .message-file-bubble .file-sub { display:none; }
    }
    /* Lightbox overlay */
    .media-lightbox { position:fixed; inset:0; background:rgba(4,10,18,0.7); display:flex; align-items:center; justify-content:center; z-index:20000; padding:20px; }
    .media-lightbox .inner { max-width:100%; max-height:100%; border-radius:10px; overflow:auto; }
    .media-lightbox img, .media-lightbox video { max-width:100%; max-height:calc(100vh - 120px); display:block; border-radius:8px; }
    .media-lightbox .close { position:absolute; top:18px; right:18px; background:rgba(255,255,255,0.9); border-radius:8px; padding:8px; cursor:pointer; }
    /* Typing indicator bubble shown in chat body when remote user is typing */
    .typing-row { display:flex; align-items:flex-start; gap:12px; padding:8px 12px; }
    .typing-row .chat-avatar img { width:40px; height:40px; object-fit:cover; }
    .typing-row .message-content { background:#fff; border-radius:12px; padding:8px 12px; box-shadow: 0 6px 18px rgba(11,59,102,0.03); max-width:320px; }
    .chats-right.typing-row { flex-direction:row-reverse; }
    .typing-row .message-text { color:#6b7280; font-weight:600; }
    /* Reply preview and quoted reply styling */
    .message-reply { border-left: 4px solid #0b78d1; background: rgba(11,120,209,0.03); padding:8px 12px; border-radius:8px; margin-bottom:8px; max-width:480px; }
    .message-reply .reply-sender { font-weight:700; color:#08324f; margin-bottom:4px; font-size:13px; }
    .message-reply .reply-snippet { color:#213240; font-size:13px; max-height:46px; overflow:hidden; text-overflow:ellipsis; }
    .reply-preview { padding:8px 12px; background:#fff; border:1px solid #eef3f8; border-radius:8px; margin-bottom:8px; display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .reply-preview .reply-snippet { color:#6b7280; font-size:13px; }
    .reply-preview .reply-cancel { border:0; background:transparent; cursor:pointer; color:#6b7280 }
    .reply-highlight { box-shadow: 0 0 0 4px rgba(11,120,209,0.14); border-radius:10px; transform: translateY(-2px); transition: box-shadow .18s ease, transform .18s ease; }
    /* Booking group file filters */
    .booking-actions { display:flex; gap:8px; align-items:center; margin-left:12px; }
    .booking-actions .btn { padding:6px 10px; border-radius:8px; font-size:13px; border:1px solid rgba(11,120,209,0.08); background:#fff; color:#0b3b66; cursor:pointer; }
    .booking-actions .btn.active { background:linear-gradient(90deg,#e6f3ff,#d9efff); border-color:rgba(11,120,209,0.14); }
    .booking-actions .count-badge { background:rgba(11,120,209,0.08); padding:2px 6px; border-radius:999px; margin-left:6px; font-weight:700; font-size:12px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="content">
    

    <div class="chat-wrapper">
        <!-- Chats sidebar -->
        <div class="sidebar-group">
            <div id="chats" class="sidebar-content active">
                <div class="chat-search-header">
                    <div class="header-title d-flex align-items-center justify-content-between">
                        <h4 class="mb-3">Chats</h4>
                    </div>
                    <div class="search-wrap">
                        <div class="input-group">
                            <input id="chatSearchInput" type="text" class="form-control" placeholder="Search For Contacts or Messages">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                        </div>
                    </div>
                </div>

                <div class="sidebar-body chat-body" id="chatsidebar">
                    <div class="chat-users-wrap" id="contactsList">
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $contactAvatar = $u->avatar ?? ('https://ui-avatars.com/api/?name=' . urlencode($u->name) . '&background=ffffff&color=0D8ABC&size=128'); ?>
                            <div class="chat-list contact-item" data-user-id="<?php echo e($u->id); ?>" data-avatar="<?php echo e($contactAvatar); ?>" data-last-message-sender-id="<?php echo e($u->last_message_sender_id ?? ''); ?>" data-last-message-sender-type="<?php echo e($u->last_message_sender_type ?? ''); ?>" data-last-message-read-at="<?php echo e($u->last_message_read_at ?? ''); ?>">
                                <a href="javascript:void(0);" class="chat-user-list">
                                    <div class="avatar avatar-lg online me-2">
                                        <img src="<?php echo e($contactAvatar); ?>" class="rounded-circle" alt="image">
                                    </div>
                                    <div class="chat-user-info">
                                        <div class="chat-user-msg">
                                            <h6><?php echo e($u->name); ?></h6>
                                            <?php
                                                $lastMsg = $u->last_message ?? null;
                                                $displayLast = 'Click to open chat';
                                                if ($lastMsg) {
                                                    $lm = trim($lastMsg);
                                                    if (preg_match('/^AUDIO::/i', $lm) || preg_match('/^\[?audio\]?$/i', $lm)) {
                                                        $displayLast = 'Audio';
                                                    } else {
                                                        // Handle reply marker: REPLY::base64(json)::optional_rest OR REPLY::base64
                                                        if (preg_match('/^REPLY::([A-Za-z0-9_\-+=\/]+)(?:::(.*))?$/i', $lm, $m)) {
                                                            $snippet = '';
                                                            $decoded = @base64_decode($m[1], true);
                                                            if ($decoded !== false) {
                                                                $j = @json_decode($decoded, true);
                                                                if (is_array($j) && isset($j['snippet'])) {
                                                                    $snippet = trim($j['snippet']);
                                                                }
                                                            }
                                                            if (!$snippet && !empty($m[2])) {
                                                                $snippet = trim($m[2]);
                                                            }
                                                            if (!$snippet) {
                                                                // fallback: remove marker
                                                                $snippet = preg_replace('/^REPLY::/i', '', $lm);
                                                            }
                                                            // strip legacy name/time prefixes that may be in stored snippet
                                                            $snippet = preg_replace('/^↪.*?:\s*/', '', $snippet);
                                                            $snippet = preg_replace('/^\s*(?:You\s*)?\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i', '', $snippet);
                                                            // shorten for sidebar
                                                            if (function_exists('mb_strlen')) {
                                                                $displayLast = '↪ ' . (mb_strlen($snippet) > 48 ? mb_substr($snippet,0,48) . '...' : $snippet);
                                                            } else {
                                                                $displayLast = '↪ ' . (strlen($snippet) > 48 ? substr($snippet,0,48) . '...' : $snippet);
                                                            }
                                                        } else {
                                                            // normal message: remove any legacy prefixes and show trimmed text
                                                            $clean = preg_replace('/^↪.*?:\s*/', '', $lm);
                                                            $clean = preg_replace('/^\s*(?:You\s*)?\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i', '', $clean);
                                                            $displayLast = $clean;
                                                        }
                                                    }
                                                }
                                            ?>
                                            <?php
                                                // Build pin/check icons + unread badge for server-rendered sidebar
                                                $pinIcons = '';
                                                try {
                                                    if (isset($u->last_message_sender_id) && $u->last_message_sender_id == $currentUserId && ($u->last_message_sender_type ?? $u->type) == $currentUserType) {
                                                        if (!empty($u->last_message_read_at)) {
                                                            $pinIcons = '<i class="ti ti-pinned me-2"></i><i class="ti ti-checks text-success"></i>';
                                                        } else {
                                                            $pinIcons = '<i class="ti ti-pinned me-2"></i><i class="ti ti-check" style="color:#6b7280"></i>';
                                                        }
                                                    }
                                                } catch (\Exception $ex) { $pinIcons = ''; }
                                                $unreadHtml = (isset($u->unread_count) && $u->unread_count > 0) ? '<span class="count-message fs-12 fw-semibold">' . $u->unread_count . '</span>' : '';
                                                $pinHtml = '<div class="chat-pin">' . $pinIcons . $unreadHtml . '</div>';
                                            ?>
                                            <p class="small text-truncate"><?php echo e($displayLast); ?></p>
                                        </div>
                                        <div class="chat-user-time">
                                            <?php
                                                $timeLabel = '';
                                                if (isset($u->last_message_at) && $u->last_message_at) {
                                                    try {
                                                        $dt = $u->last_message_at; // assume Carbon
                                                        // Always show clock time (local server time)
                                                        $timeLabel = $dt->format('h:i A');
                                                    } catch (Exception $ex) {
                                                        $timeLabel = (string) $u->last_message_at;
                                                    }
                                                }
                                            ?>
                                            <span class="time" data-last-message-at="<?php echo e($u->last_message_at ? $u->last_message_at->toRfc3339String() : ''); ?>"><?php echo e($timeLabel); ?></span>
                                            <?php echo $pinHtml; ?>

                                        </div>
                                    </div>
                                </a>
                                <div class="chat-dropdown">
                                    <a href="#" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end p-3">
                                        <li><a class="dropdown-item" href="#"><i class="ti ti-box-align-right me-2"></i>Archive Chat</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="ti ti-heart me-2"></i>Mark as Favourite</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="ti ti-check me-2"></i>Mark as Unread</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="ti ti-pinned me-2"></i>Pin Chats</a></li>
                                        <li><a class="dropdown-item" href="#"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- / Chats sidebar -->

        <!-- Chat -->
        <div class="chat chat-messages show" id="middle">
            <div class="chat-header">
                <div class="user-details">
                    <div class="d-xl-none">
                        <a class="text-muted chat-close me-2" href="#"><i class="fas fa-arrow-left"></i></a>
                    </div>
                    <div class="avatar avatar-lg online flex-shrink-0">
                        <img id="chatHeaderAvatar" src="https://ui-avatars.com/api/?name=User&background=ffffff&color=0D8ABC&size=128" class="rounded-circle" alt="image">
                    </div>
                    <div class="ms-2 overflow-hidden">
                        <h6 id="chatWith">Select a contact</h6>
                        <span class="last-seen" id="chatStatus">Select user to chat</span>
                    </div>
                </div>
                <div class="chat-options">
                    <ul>
                        <li>
                            <div class="booking-actions" id="bookingActions" style="display:none;">
                                <button type="button" id="btnHold" class="btn">Hold <span class="count-badge" id="holdCount">0</span></button>
                                <button type="button" id="btnUnbooked" class="btn">Unbooked <span class="count-badge" id="unbookedCount">0</span></button>
                            </div>
                        </li>
                        <li><a href="javascript:void(0)" class="btn chat-search-btn" data-bs-toggle="tooltip" title="Search"><i class="ti ti-search"></i></a></li>
                        <li>
                            <a class="btn no-bg" href="#" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li><a href="#" class="dropdown-item"><i class="ti ti-volume-off me-2"></i>Mute Notification</a></li>
                                <li><a href="#" class="dropdown-item"><i class="ti ti-clock-hour-4 me-2"></i>Disappearing Message</a></li>
                                <li><a href="#" class="dropdown-item"><i class="ti ti-clear-all me-2"></i>Clear Message</a></li>
                                <li><a href="#" class="dropdown-item"><i class="ti ti-trash me-2"></i>Delete Chat</a></li>
                                <li><a href="#" class="dropdown-item"><i class="ti ti-ban me-2"></i>Block</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="chat-body chat-page-group slimscroll">
                <div class="messages" id="messagesContainer">
                    <!-- Dynamic Messages will load here -->
                        <div class="text-center centered-placeholder">
                            <i class="ti ti-message-dots fs-1" style="opacity: 0.2;"></i>
                            <p class="text-muted">Select a contact to start messaging</p>
                        </div>
                </div>
            </div>

            <div class="chat-footer">
                <form id="sendMessageForm" class="footer-form" onsubmit="return false;">
                    <div class="chat-footer-wrap">
                        <div class="form-item d-flex align-items-center">
                            <a href="#" id="micBtn" class="action-circle" title="Record audio"><i class="ti ti-microphone"></i></a>
                            <span id="recordIndicator" style="display:none; margin-left:8px; color:#dc3545; font-weight:600; font-size:13px;">
                                <span class="recording-dot"></span>
                                <span id="recordTimer" style="margin-left:6px; color:#222; font-weight:500;">0:00</span>
                            </span>
                        </div>
                        <div class="form-wrap">
                            <input type="text" id="messageInput" class="form-control" placeholder="Type Your Message">
                        </div>
                        <div class="form-item emoj-action-foot">
                            <a href="#" class="action-circle"><i class="ti ti-mood-smile"></i></a>
                        </div>
                        <div class="form-item position-relative d-flex align-items-center justify-content-center ">
                            <a href="#" class="action-circle file-action position-absolute"><i class="ti ti-folder"></i></a>
                            <input type="file" class="open-file position-relative" name="files" id="files">
                        </div>
                        <!-- <div class="form-item">
                            <a href="#" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                            <div class="dropdown-menu dropdown-menu-end p-3">
                                <a href="#" class="dropdown-item"><i class="ti ti-camera-selfie me-2"></i>Camera</a>
                                <a href="#" class="dropdown-item"><i class="ti ti-photo-up me-2"></i>Gallery</a>
                                <a href="#" class="dropdown-item"><i class="ti ti-music me-2"></i>Audio</a>
                            </div>
                        </div> -->
                        <div class="form-btn">
                            <button id="sendBtn" class="btn btn-primary" type="submit"><i class="ti ti-send"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /Chat -->
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <!-- Pusher + Echo (CDN) -->
    <script src="https://js.pusher.com/8.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.min.js"></script>
    <script>
        // Initialize Echo with Pusher using env values
        try {
            if (!window.Echo) {
                window.Pusher = window.Pusher || Pusher;
                window.Echo = new window.Echo({
                    broadcaster: 'pusher',
                    key: '<?php echo e(env("PUSHER_APP_KEY")); ?>',
                    cluster: '<?php echo e(env("PUSHER_APP_CLUSTER")); ?>',
                    forceTLS: <?php echo e(env('PUSHER_SCHEME', 'https') === 'https' ? 'true' : 'false'); ?>,
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : ''
                        }
                    }
                });
            }
        } catch (e) { console.warn('Echo init failed', e); }
    </script>
<script>
    let currentChatUser = null;
    const currentUserId = <?php echo e($currentUserId ?? 'null'); ?>;
    const currentUserType = '<?php echo e($currentUserType ?? ''); ?>';
    // persist current user's reaction state across full page reloads by using a global map
    window.chatLocalReactions = window.chatLocalReactions || {};
    let pendingReplyTo = null; // message id we are replying to (client-side)
    let pendingJumpTo = null; // message id to jump to after reload

    function escapeHtml(str){
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function showReplyPreview(meta){
        try {
            clearReplyPreview();
            const foot = document.querySelector('.chat-footer') || document.querySelector('.chat-footer') || document.body;
            const input = document.getElementById('messageInput');
            if (!input) return;
            const container = document.createElement('div');
            container.id = 'replyPreview';
            container.className = 'reply-preview';
            // show only the single arrow prefix and snippet
            container.innerHTML = `<div class="reply-preview-inner">↪ <div class="reply-snippet">${escapeHtml(meta.snippet || '')}</div><button type="button" class="btn btn-link btn-sm reply-cancel">✕</button></div>`;
            // insert before footer input
            const footer = input.closest('.chat-footer') || input.parentElement;
            if (footer) footer.parentElement.insertBefore(container, footer);
            const btn = container.querySelector('.reply-cancel');
            if (btn) btn.addEventListener('click', function(){ pendingReplyTo = null; clearReplyPreview(); });
        } catch(e){}
    }

    function clearReplyPreview(){ const ex = document.getElementById('replyPreview'); if (ex) ex.remove(); }

    // New render function using the requested UI design
    function formatTime(ts){
        if(!ts) return '';
        const d = new Date(ts);
        if (isNaN(d)) return '';
        let h = d.getHours();
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12; if (h === 0) h = 12;
        const m = String(d.getMinutes()).padStart(2,'0');
        return `${h}:${m} ${ampm}`;
    }

    function formatDateLabel(ts){
        if(!ts) return '';
        const d = new Date(ts);
        if (isNaN(d)) return '';
        const now = new Date();
        const ymd = d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate();
        const nyn = now.getFullYear()+'-'+(now.getMonth()+1)+'-'+now.getDate();
        const yesterday = new Date(); yesterday.setDate(now.getDate()-1);
        const yest = yesterday.getFullYear()+'-'+(yesterday.getMonth()+1)+'-'+yesterday.getDate();
        const monthDay = d.toLocaleDateString(undefined, { month: 'long', day: 'numeric' });
        if (ymd === nyn) return `Today, ${monthDay}`;
        if (ymd === yest) return `Yesterday, ${monthDay}`;
        // include year only if different from current year
        const opts = { month: 'long', day: 'numeric' };
        if (d.getFullYear() !== now.getFullYear()) opts.year = 'numeric';
        return d.toLocaleDateString(undefined, opts);
    }

    function renderMessage(msg) {
        const isMine = (msg.sender_id == currentUserId) && ( (msg.sender_type || '') == (currentUserType || '') );
        const wrapper = document.createElement('div');
        wrapper.className = isMine ? 'chats chats-right' : 'chats';
        try { if (msg.id) wrapper.setAttribute('data-message-id', msg.id); else if (msg.message_id) wrapper.setAttribute('data-message-id', msg.message_id); } catch(e){}

        const senderName = isMine ? 'You' : (msg.sender_name || 'User');
        const time = formatTime(msg.created_at || msg.created_at_raw || msg.created_at_formatted);
        
        let avatarUrl = msg.sender_avatar;
        if (!avatarUrl) {
            avatarUrl = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(senderName) + '&background=ffffff&color=0D8ABC&size=64';
        }

        // HTML structure from the target UI
        const incomingAvatar = `<div class="chat-avatar"><img src="${avatarUrl}" class="rounded-circle" alt="image"></div>`;
        const audioHtml = msg.audio_url ? `
            <div class="message-audio-bubble ${isMine ? 'message-audio-right' : 'message-audio-left'}" data-audio-src="${msg.audio_url}">
                <div class="audio-left">
                    <div class="mic-circle" title="Play audio"><i class="ti ti-microphone"></i></div>
                </div>
                <div class="audio-central">
                    <div class="audio-waveform" aria-hidden="true">
                        <span class="bar-vert b1"></span>
                        <span class="bar-vert b2"></span>
                        <span class="bar-vert b3"></span>
                        <span class="bar-vert b4"></span>
                        <span class="bar-vert b5"></span>
                        <span class="bar-vert b6"></span>
                        <span class="bar-vert b7"></span>
                    </div>
                    <div class="audio-meta">
                        <span class="audio-duration">0:00</span>
                    </div>
                </div>
                <audio preload="metadata" src="${msg.audio_url}" style="display:none"></audio>
            </div>` : '';
        // media handling (images, videos, generic files)
        let mediaHtml = '';
        // try common fields that may contain media URL
        let mediaUrl = msg.file_url || msg.media_url || msg.attachment_url || null;
        if (!mediaUrl && msg.attachments && msg.attachments.length) {
            mediaUrl = msg.attachments[0].url || null;
        }
        if (!mediaUrl && typeof msg.content === 'string') {
            const contentStr = msg.content || '';
            const idx = contentStr.indexOf('FILE::');
            if (idx !== -1) {
                // Extract everything after FILE:: and try to determine stored path and original name
                const after = contentStr.substring(idx + 6).trim();
                let path = '';
                let origName = null;
                // If marker uses ::encodedName (server-side canonical form)
                const dd = after.indexOf('::');
                if (dd !== -1) {
                    path = after.substring(0, dd);
                    origName = after.substring(dd + 2).trim();
                    try { origName = decodeURIComponent(origName); } catch(e) { /* keep as-is */ }
                } else {
                    // Otherwise assume path is first token and the rest (if any) is a plain filename
                    const parts = after.split(/\s+/);
                    path = parts[0] || '';
                    const rest = parts.slice(1).join(' ').trim();
                    if (rest) origName = rest;
                }
                if (path) mediaUrl = path;
                try { if (origName) msg._originalFilename = msg._originalFilename || origName; } catch(e){}
            } else {
                const t = msg.content.trim();
                if (/^(https?:\/\/|\/|www\.)/i.test(t) && t.length > 10) {
                    mediaUrl = t;
                }
            }
        }
        if (mediaUrl) {
            const lower = mediaUrl.split('?')[0].toLowerCase();
            const ext = (lower.match(/\.([a-z0-9]{2,5})$/) || [null,''])[1];
            const imageExt = ['png','jpg','jpeg','gif','webp','bmp','svg'];
            const videoExt = ['mp4','webm','ogg','mov'];
            if (imageExt.indexOf(ext) !== -1) {
                mediaHtml = `<div class="message-media-bubble"><a href="${mediaUrl}" class="media-preview" data-type="image" data-src="${mediaUrl}" rel="noopener noreferrer"><img src="${mediaUrl}" alt="image" /></a></div>`;
            } else if (videoExt.indexOf(ext) !== -1) {
                mediaHtml = `<div class="message-media-bubble"><a href="${mediaUrl}" class="media-preview" data-type="video" data-src="${mediaUrl}" rel="noopener noreferrer"><video controls preload="metadata" src="${mediaUrl}"></video></a></div>`;
            } else if (msg.audio_url) {
                // already handled by audioHtml, keep empty
            } else {
                // file bubble: prefer attachment metadata if available
                let fname = '';
                let fsize = '';
                if (msg.attachments && msg.attachments.length) {
                    const a = msg.attachments[0];
                    if (a.name && a.name.trim()) {
                        fname = a.name;
                    }
                    if (a.size) {
                        const s = Number(a.size);
                        if (!isNaN(s)) {
                            if (s < 1024) fsize = s + ' B';
                            else if (s < 1024*1024) fsize = Math.round((s/1024)*10)/10 + ' KB';
                            else fsize = Math.round((s/(1024*1024))*10)/10 + ' MB';
                        }
                    }
                }
                // if controller or client parsed an original filename from the FILE:: marker, use it
                if (!fname && msg._originalFilename) {
                    fname = msg._originalFilename;
                }
                // if original filename not available, fall back to friendly label using extension
                const storageName = decodeURIComponent((mediaUrl.split('?')[0].split('/').pop() || ''));
                const extLabel = ext ? ext.toUpperCase() : '';
                let fullName = fname || '';
                let displayName = '';
                if (fullName) {
                    // show truncated label (with ellipsis) and keep full name in tooltip
                    displayName = fullName.length > 30 ? (fullName.slice(0, 30) + '...') : fullName;
                } else {
                    // show a friendly placeholder like "PDF file" or "Document" instead of the raw storage filename
                    if (extLabel) displayName = extLabel + ' file';
                    else displayName = 'Attachment';
                }
                const fileSubHtml = fsize ? `<div class="file-sub">${fsize}</div>` : '';
                const compactClass = fsize ? '' : 'compact';
                // Make the entire bubble open in a new tab and remove the external redirect icon
                mediaHtml = `<a class="message-file-link" href="${mediaUrl}" target="_blank" rel="noopener noreferrer">
                    <div class="message-file-bubble ${compactClass}">
                        <div class="file-icon" aria-hidden="true"><i class="ti ti-file-text"></i></div>
                        <div class="file-meta">
                            <div class="file-title" title="${(fullName || msg._originalFilename || displayName)}">${escapeHtml(displayName)}${fullName && extLabel ? '<span class="file-ext">' + extLabel + '</span>' : (fullName ? '' : (extLabel ? '<span class="file-ext">' + extLabel + '</span>' : ''))}</div>
                            ${fileSubHtml}
                        </div>
                        <div class="message-file-actions">
                            <button class="btn download-btn" data-src="${mediaUrl}" title="Download"><i class="ti ti-download"></i></button>
                        </div>
                    </div>
                </a>`;
            }
        }
        const rawContent = (msg.content || '').toString();
        // strip legacy client-inserted reply prefixes like "↪ Name: " that may have been saved into content
        let trimmed = rawContent.trim();
        // If content contains FILE:: marker but we failed to build mediaHtml earlier, try to extract an original filename
        try {
            if ((rawContent || '').indexOf('FILE::') !== -1 && !msg._originalFilename) {
                // match FILE::path::encodedName OR FILE::path <space> originalName OR FILE::path::encodedName <space> originalName
                const fileMatch = rawContent.match(/FILE::([^\s:]+)(?:::(\S+))?(?:\s+(.+))?/i);
                if (fileMatch) {
                    const encoded = fileMatch[2] || null;
                    const trailing = fileMatch[3] || null;
                    let candidate = null;
                    if (encoded) {
                        try { candidate = decodeURIComponent(encoded); } catch(e) { candidate = encoded; }
                    }
                    if (!candidate && trailing) candidate = trailing.trim();
                    if (candidate) {
                        try { msg._originalFilename = msg._originalFilename || candidate; } catch(e){}
                    }
                }
            }
        } catch(e){}
        try {
            // remove legacy reply marker
            trimmed = trimmed.replace(/^↪.*?:\s*/,'');
            // remove leading time-like prefixes like "9:55 PM: ", "14 AM: ", or "You 9:55 PM: "
            trimmed = trimmed.replace(/^\s*(?:You\s*)?\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i, '');
            // remove possible 'Name 9:55 PM: ' style prefixes
            trimmed = trimmed.replace(/^\s*[A-Za-z0-9\s]{1,60}\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i, '');
            // fallback: hour+AM/PM like '14 AM:'
            trimmed = trimmed.replace(/^\s*\d{1,2}\s*(?:AM|PM)\s*[:\-–]?\s*/i, '');
        } catch(e){}
        const isAudioMarker = /^\[?audio\]?$/i.test(trimmed) || trimmed.startsWith('AUDIO::');
        const isAttachmentMarker = /^\[?attachment\]?$/i.test(trimmed) || trimmed.startsWith('FILE::');
        const textHtml = (trimmed && !isAudioMarker && !isAttachmentMarker && !mediaHtml) ? `<div class="message-text">${trimmed}</div>` : '';
        // Render reply preview if present
        let replyHtml = '';
        try {
            if (msg.reply_to && typeof msg.reply_to === 'object') {
                const rs = escapeHtml(msg.reply_to.snippet || '');
                const rid = escapeHtml(String(msg.reply_to.id || ''));
                // show only a single arrow prefix and the snippet (no name/time)
                replyHtml = `<div class="message-reply" data-reply-to="${rid}">↪ <div class="reply-snippet">${rs}</div></div>`;
            }
        } catch(e) { replyHtml = ''; }
        // Render reactions if server provided them (array or object map)
        let reactionsHtml = '';
        try {
            const build = (arr) => {
                if (!Array.isArray(arr) || !arr.length) return '';
                const parts = arr.map(r => {
                    const emoji = r.emoji || r.char || r.key || '';
                    const count = (typeof r.count === 'number') ? r.count : (r.c || r.total || 0);
                    const you = !!(r.you || r.you_reacted || r.current_user || r.me);
                    const countHtml = (Number(count) > 1) ? `<span class="count">${Number(count)}</span>` : '';
                    return `<button type="button" class="reaction-btn${you? ' you-reacted':''}" data-emoji="${escapeHtml(emoji)}" ${you? 'data-you="1"':''}><span class="emoji">${escapeHtml(emoji)}</span>${countHtml}</button>`;
                });
                return `<div class="reaction-bar">${parts.join('')}</div>`;
            };

            if (msg.reactions && Array.isArray(msg.reactions)) {
                reactionsHtml = build(msg.reactions);
            } else if (msg.reactions && typeof msg.reactions === 'object') {
                const arr = [];
                Object.keys(msg.reactions).forEach(k => {
                    const v = msg.reactions[k];
                    if (v && typeof v === 'object') arr.push({ emoji: k, count: Number(v.count || v.total || 0), you: !!v.you });
                    else arr.push({ emoji: k, count: Number(v || 0), you: false });
                });
                reactionsHtml = build(arr);
            } else if (msg.reaction_counts && typeof msg.reaction_counts === 'object') {
                const arr = [];
                Object.keys(msg.reaction_counts).forEach(k => arr.push({ emoji: k, count: Number(msg.reaction_counts[k] || 0), you: false }));
                reactionsHtml = build(arr);
            }
        } catch(e) { reactionsHtml = ''; }
        const isAudioOnly = !!(msg.audio_url && !textHtml && !mediaHtml);
        const isMediaOnly = !!(mediaHtml && !textHtml && !msg.audio_url);
        const messageContentClass = isAudioOnly ? 'message-content audio-only' : (isMediaOnly ? (mediaHtml.indexOf('message-file-link') !== -1 ? 'message-content file-only' : 'message-content media-only') : 'message-content');
        const contentHtml = `
            <div class="chat-content">
                <div class="chat-info">
                    <div class="${messageContentClass}">
                        ${replyHtml}
                        ${audioHtml}
                        ${mediaHtml}
                        ${textHtml}
                        ${reactionsHtml}
                        <div class="emoj-group">
                            <ul>
                                <li class="emoj-action"><a href="javascript:void(0);"><i class="ti ti-mood-smile"></i></a></li>
                                <li><a href="#"><i class="ti ti-arrow-forward-up"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="chat-profile-name ${isMine ? 'text-end' : ''}">
                    <h6>${senderName}<i class="ti ti-circle-filled fs-7 mx-2"></i><span class="chat-time">${time}</span>
                    ${isMine ? ( (msg.read_at) ? '<span class="msg-read success read"><i class="ti ti-checks"></i></span>' : '<span class="msg-read"><i class="ti ti-check"></i></span>') : ''}
                    </h6>
                </div>
            </div>`;

        if (isMine) {
            wrapper.innerHTML = contentHtml + incomingAvatar;
        } else {
            wrapper.innerHTML = incomingAvatar + contentHtml;
        }
        return wrapper;
    }

    async function loadMessages(userId, markRead = false) {
        if (!userId) return;
        // If user has scrolled up (viewing earlier messages), skip polling reloads
        try {
            const containerCheck = document.getElementById('messagesContainer');
            const wrapCheck = containerCheck && containerCheck.parentElement ? containerCheck.parentElement : null;
            const clientH = wrapCheck ? wrapCheck.clientHeight : 0;
            const prevH = wrapCheck ? wrapCheck.scrollHeight : 0;
            const prevTop = wrapCheck ? wrapCheck.scrollTop : 0;
            const distFromBottom = prevH - clientH - prevTop;
            const nearB = distFromBottom <= 100;
            const forced = !!(window._forceScrollToBottom);
            if (!markRead && !nearB && !forced) return; // user is reading history; avoid auto-reload
        } catch(e){}
        // prevent concurrent loads which can race and cause scroll jumps
        try {
            if (window._loadingMessages) return;
            window._loadingMessages = true;
        } catch(e){}
        try {
            let url = '/chat/messages/' + encodeURIComponent(userId);
            if (markRead) url += '?mark_read=1';
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            const container = document.getElementById('messagesContainer');
            const containerWrap = container.parentElement;
            // Capture previous scroll state so we can preserve position if user scrolled up
            const prevScrollTop = containerWrap ? containerWrap.scrollTop : 0;
            const prevScrollHeight = containerWrap ? containerWrap.scrollHeight : 0;
            const clientHeight = containerWrap ? containerWrap.clientHeight : 0;
            const distanceFromBottom = prevScrollHeight - clientHeight - prevScrollTop;
            const nearBottom = distanceFromBottom <= 100; // treat as near-bottom if within 100px

            // Preserve any client-side reaction UI so periodic reloads don't remove them
            const existingReactions = {};
            try {
                container.querySelectorAll('.chats[data-message-id]').forEach(e => {
                    try {
                        const id = e.getAttribute('data-message-id');
                        const bar = e.querySelector('.reaction-bar');
                        if (id && bar) existingReactions[String(id)] = bar.outerHTML;
                    } catch (inner) {}
                });
            } catch (er) {}

            container.innerHTML = '';
            let lastDateKey = null;
            data.forEach(m => {
                const ts = m.created_at || m.created_at_raw || null;
                const d = ts ? new Date(ts) : null;
                const dateKey = d ? `${d.getFullYear()}-${d.getMonth()+1}-${d.getDate()}` : null;
                if (dateKey && dateKey !== lastDateKey) {
                    const sep = document.createElement('div');
                    sep.className = 'chat-date-separator';
                    sep.innerHTML = `<div class="chat-date-pill">${formatDateLabel(ts)}</div>`;
                    container.appendChild(sep);
                    lastDateKey = dateKey;
                }
                const newEl = renderMessage(m);
                container.appendChild(newEl);
                // reapply preserved reaction UI if present (helps keep client-side reactions visible across reloads)
                try {
                    const mid = m.id || m.message_id || null;
                    if (mid && existingReactions[String(mid)]) {
                        const content = newEl.querySelector('.message-content') || newEl.querySelector('.chat-content') || newEl;
                        if (content) {
                            // avoid duplicating if server already provided reactions
                            if (!content.querySelector('.reaction-bar')) {
                                const temp = document.createElement('div');
                                temp.innerHTML = existingReactions[String(mid)];
                                const bar = temp.firstElementChild;
                                if (bar) {
                                    content.appendChild(bar);
                                    try { content.classList.add('has-reactions'); } catch(e){}
                                }
                            }
                        }
                    }
                } catch (re) { /* ignore reapply errors */ }
            });

            // If user was near bottom, auto-scroll to bottom. Also honor a one-time force flag when user opened a contact.
            try {
                const forceScroll = !!(window._forceScrollToBottom);
                if (nearBottom || forceScroll) {
                    containerWrap.scrollTop = containerWrap.scrollHeight;
                } else {
                    // preserve approximate viewport position by shifting by the change in height
                    const newScrollHeight = containerWrap.scrollHeight;
                    const delta = newScrollHeight - prevScrollHeight;
                    containerWrap.scrollTop = Math.max(0, prevScrollTop + delta);
                }
                try { if (window._forceScrollToBottom) window._forceScrollToBottom = false; } catch(e){}
                // If we forced a scroll when opening a contact, re-apply after layout settles
                try {
                    if (forceScroll) {
                        setTimeout(() => {
                            try { containerWrap.scrollTop = containerWrap.scrollHeight; } catch(e){}
                        }, 220);
                        // also re-scroll when media finishes loading to avoid image/video layout jumps
                        try {
                            const media = container.querySelectorAll('img, video');
                            media.forEach(m => {
                                try {
                                    const ev = m.tagName.toLowerCase() === 'video' ? 'loadedmetadata' : 'load';
                                    m.addEventListener(ev, () => {
                                        try { containerWrap.scrollTop = containerWrap.scrollHeight; } catch(e){}
                                    });
                                } catch(e){}
                            });
                        } catch(e){}
                    }
                } catch(e){}
            } catch (err) { /* ignore scroll errors */ }

                // initialize audio controls for newly appended messages
            try { 
                // hydrate local reaction map from server-rendered reaction buttons
                try { hydrateChatLocalReactions(); } catch(e){}
                initAudioControls(container); 
                    try { if (window.updateBookingFileCounts) window.updateBookingFileCounts(); } catch(e){}
                    try { sanitizeFileTitles(); } catch(e){}
                    // Reapply any active booking file filter after messages reload so filtered view persists
                    try { if (window.applyBookingFileFilter) window.applyBookingFileFilter(window.currentFileFilterMode || null); } catch(e){}
            } catch (e) { console.error('initAudioControls error', e); }

            // if there's a pending jump request (user clicked a reply that referenced a message not currently in view), try to scroll
            try {
                if (pendingJumpTo) {
                    const target = container.querySelector(`[data-message-id="${pendingJumpTo}"]`);
                    if (target) {
                        try { target.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(e) { target.scrollIntoView(); }
                        target.classList.add('reply-highlight');
                        setTimeout(() => { try { target.classList.remove('reply-highlight'); } catch(e){} }, 2200);
                        pendingJumpTo = null;
                    } else {
                        // not present yet; leave pendingJumpTo set — subsequent polling/load may satisfy it
                    }
                }
            } catch(e){}
            // Refresh sidebar contacts so unread counts update immediately after loading messages
            try { if (typeof refreshContacts === 'function') refreshContacts(); } catch(e) {}
        } catch (e) { console.error("Error loading messages", e); }
        finally {
            try { window._loadingMessages = false; } catch(e){}
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        const contacts = document.getElementById('contactsList');
        // Sidebar search input filtering
        try {
            const chatSearch = document.getElementById('chatSearchInput');
            if (chatSearch) {
                chatSearch.addEventListener('input', (ev) => {
                    const q = String(ev.target.value || '').trim().toLowerCase();
                    const items = contacts.querySelectorAll('.contact-item');
                    items.forEach(it => {
                        try {
                            const name = (it.querySelector('h6') && it.querySelector('h6').innerText || '').toLowerCase();
                            const last = (it.querySelector('.chat-user-msg p') && it.querySelector('.chat-user-msg p').innerText || '').toLowerCase();
                            const match = !q || name.indexOf(q) !== -1 || last.indexOf(q) !== -1;
                            it.style.display = match ? '' : 'none';
                        } catch (ie) { /* ignore per-item errors */ }
                    });
                });
            }
        } catch (e) { /* ignore search wiring errors */ }
        
        // Handle Contact Selection
        contacts.addEventListener('click', function(e){
            const el = e.target.closest('.contact-item');
            if (!el) return;
            
            // UI Active State
            document.querySelectorAll('.contact-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');

            const userId = el.getAttribute('data-user-id');
            const avatar = el.getAttribute('data-avatar');
            const name = el.querySelector('h6').innerText;

            currentChatUser = userId;
            // persist selected contact so refresh restores it
            try { localStorage.setItem('chat_current_user', String(userId)); } catch (e) {}
            document.getElementById('chatWith').innerText = name;
            document.getElementById('chatHeaderAvatar').src = avatar;
            document.getElementById('chatStatus').innerText = 'Online';

            // ensure we scroll to bottom when user explicitly opens a contact
            try { window._forceScrollToBottom = true; } catch(e){}
            loadMessages(userId, true);
            try { if (window.updateBookingFileCounts) setTimeout(window.updateBookingFileCounts, 250); } catch(e){}
            // show booking actions only for booking group: detect by data attribute or name contains 'booking'
            try {
                const actions = document.getElementById('bookingActions');
                if (actions) {
                    const isBooking = (el.getAttribute('data-is-booking') === '1') || /booking/i.test(String(name || ''));
                    actions.style.display = isBooking ? '' : 'none';
                }
            } catch(e){}
            
            // Clear unread count UI
            const badge = el.querySelector('.count-message');
            if (badge) badge.remove();
        });

        // Handle Sending Message
        document.getElementById('sendBtn').addEventListener('click', async function(){
            const input = document.getElementById('messageInput');
            const text = input.value.trim();
            if (!text || !currentChatUser) return;

            // Optimistically clear the input so user can continue typing
            input.value = '';
            const btn = this;
            btn.disabled = true;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const payload = { receiver_id: currentChatUser, content: text };
                if (pendingReplyTo) payload.reply_to = pendingReplyTo;
                const res = await fetch('/chat/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                // persist selected contact
                try { if (currentChatUser) localStorage.setItem('chat_current_user', String(currentChatUser)); } catch (e) {}

                if (res.ok) {
                    // clear pending reply preview
                    pendingReplyTo = null; clearReplyPreview();
                    loadMessages(currentChatUser, true);
                } else {
                    console.error('Send failed', res.status);
                }
            } catch (ex) { console.error('Send error', ex); }
            finally { btn.disabled = false; }
        });

        // Emoji picker: create and wire (polished, accessible)
        (function(){
            const emojiBtn = document.querySelector('.emoj-action-foot .action-circle');
            const input = document.getElementById('messageInput');
            if (!emojiBtn || !input) return;

            const emojis = ['😀','😁','😂','🤣','😊','😍','👍','👏','🎉','😢','🙏','🔥'];

            // lightweight keyword map to support search (small set)
            const keywords = {
                '😀':'grin happy', '😂':'laugh cry', '🤣':'laugh', '😊':'smile', '😍':'love heart', '👍':'thumbs up', '👏':'clap', '🙌':'praise', '🎉':'party', '😮':'surprised', '😢':'sad cry', '🙏':'please thanks', '🔥':'fire', '😉':'wink', '🤔':'thinking', '😅':'relieved', '😎':'cool', '🤗':'hug', '💯':'100', '🎶':'music', '✅':'check', '💬':'chat', '😘':'kiss', '😇':'angel'
            };

            const picker = document.createElement('div');
            picker.className = 'emoji-picker';
            picker.setAttribute('role','dialog');
            picker.setAttribute('aria-label','Emoji picker');
            picker.tabIndex = -1;

            picker.innerHTML = `
                <div class="emoji-header">
                    <div class="emoji-search" role="search">
                        <input type="search" placeholder="Search emojis" aria-label="Search emojis" />
                    </div>
                    <button class="emoji-close" aria-label="Close emoji picker">✕</button>
                </div>
                <div class="emoji-recent" aria-hidden="false"></div>
                <div class="emoji-grid" role="listbox" aria-label="Emoji list"></div>
                <div class="emoji-noresults" style="display:none">No emojis found</div>
            `;

            document.body.appendChild(picker);

            const searchInput = picker.querySelector('.emoji-search input');
            const closeBtn = picker.querySelector('.emoji-close');
            const recentEl = picker.querySelector('.emoji-recent');
            const gridEl = picker.querySelector('.emoji-grid');
            const noresults = picker.querySelector('.emoji-noresults');

            // recent management
            function getRecent(){
                try { return JSON.parse(localStorage.getItem('emoji_recent') || '[]'); } catch(e){ return []; }
            }
            function addRecent(ch){
                try {
                    const list = getRecent().filter(x => x !== ch);
                    list.unshift(ch);
                    while(list.length > 12) list.pop();
                    localStorage.setItem('emoji_recent', JSON.stringify(list));
                    renderRecent();
                } catch(e){}
            }
            function renderRecent(){
                const list = getRecent();
                recentEl.innerHTML = '';
                if (!list.length) {
                    recentEl.style.display = 'none';
                    return;
                }
                recentEl.style.display = '';
                list.forEach(ch => {
                    const it = document.createElement('button');
                    it.className = 'recent-item';
                    it.type = 'button';
                    it.innerText = ch;
                    it.addEventListener('click', () => { insertAtCaret(ch); closePicker(); });
                    recentEl.appendChild(it);
                });
            }

            function renderGrid(filter){
                filter = (filter || '').trim().toLowerCase();
                gridEl.innerHTML = '';
                const items = emojis.filter(ch => {
                    if (!filter) return true;
                    if (ch.indexOf(filter) !== -1) return true;
                    const kw = keywords[ch] || '';
                    return kw.indexOf(filter) !== -1;
                });
                if (!items.length) {
                    noresults.style.display = '';
                    return;
                }
                noresults.style.display = 'none';
                items.forEach((ch, idx) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'emoji-cell';
                    btn.setAttribute('role','option');
                    btn.setAttribute('data-emoji', ch);
                    btn.tabIndex = 0;
                    btn.innerText = ch;
                    btn.addEventListener('click', (ev) => { ev.stopPropagation(); insertAtCaret(ch); addRecent(ch); closePicker(); });
                    btn.addEventListener('keydown', (ev) => {
                        if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); btn.click(); }
                    });
                    gridEl.appendChild(btn);
                });
            }

            function insertAtCaret(val){
                const start = input.selectionStart || input.value.length;
                const end = input.selectionEnd || start;
                const before = input.value.slice(0, start);
                const after = input.value.slice(end);
                input.value = before + val + after;
                const pos = start + val.length;
                input.setSelectionRange(pos, pos);
                input.focus();
            }

            function positionPicker(){
                const rect = emojiBtn.getBoundingClientRect();
                const pickerRect = picker.getBoundingClientRect();
                // try to place above the button first
                let top = rect.top - 12 - pickerRect.height;
                if (top < 8) top = rect.bottom + 8; // fallback below
                // align right edge with button right edge (or fit in viewport)
                let left = rect.right - pickerRect.width;
                if (left < 8) left = 8;
                if (left + pickerRect.width > window.innerWidth - 8) left = Math.max(8, window.innerWidth - 8 - pickerRect.width);
                picker.style.left = left + 'px';
                picker.style.top = top + 'px';
            }

            function openPicker(){
                renderRecent();
                renderGrid('');
                picker.classList.add('show');
                positionPicker();
                setTimeout(()=> searchInput.focus(), 80);
            }
            function closePicker(){ picker.classList.remove('show'); }

            // toggle
            emojiBtn.addEventListener('click', (ev) => { ev.preventDefault(); ev.stopPropagation(); if (picker.classList.contains('show')) closePicker(); else openPicker(); });

            // close when clicking outside
            document.addEventListener('click', (ev) => { if (!picker.contains(ev.target) && !emojiBtn.contains(ev.target)) closePicker(); });

            // close button
            closeBtn.addEventListener('click', (ev) => { ev.preventDefault(); closePicker(); });

            // search
            searchInput.addEventListener('input', (ev) => { renderGrid(searchInput.value || ''); });
            searchInput.addEventListener('keydown', (ev) => {
                if (ev.key === 'ArrowDown') {
                    ev.preventDefault(); const first = gridEl.querySelector('.emoji-cell'); if (first) first.focus();
                } else if (ev.key === 'Escape') { closePicker(); }
            });

            // keyboard navigation inside grid
            gridEl.addEventListener('keydown', (ev) => {
                const cells = Array.from(gridEl.querySelectorAll('.emoji-cell'));
                if (!cells.length) return;
                const idx = cells.indexOf(document.activeElement);
                const cols = 6;
                if (ev.key === 'ArrowRight') { ev.preventDefault(); const n = cells[Math.min(cells.length-1, idx+1)]; if (n) n.focus(); }
                if (ev.key === 'ArrowLeft') { ev.preventDefault(); const n = cells[Math.max(0, idx-1)]; if (n) n.focus(); }
                if (ev.key === 'ArrowDown') { ev.preventDefault(); const n = cells[Math.min(cells.length-1, idx+cols)]; if (n) n.focus(); }
                if (ev.key === 'ArrowUp') { ev.preventDefault(); const n = cells[Math.max(0, idx-cols)]; if (n) n.focus(); }
                if (ev.key === 'Escape') { closePicker(); emojiBtn.focus(); }
            });

            // reposition on resize/scroll
            window.addEventListener('resize', () => { if (picker.classList.contains('show')) positionPicker(); });
            window.addEventListener('scroll', () => { if (picker.classList.contains('show')) positionPicker(); }, true);

            // render recent on init
            renderRecent();
        })();

        // File upload handling (send media files)
        (function(){
            const fileInput = document.getElementById('files');
            if (!fileInput) return;

            function createUploadingBubble(name) {
                const wrap = document.createElement('div');
                wrap.className = 'chats chats-right';
                const html = `
                    <div class="chat-content">
                        <div class="chat-info">
                            <div class="message-content">
                                <div class="message-media-bubble uploading">
                                    <div class="file-placeholder"><strong>${name}</strong></div>
                                    <div class="upload-progress"><div class="bar" style="width:0%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                wrap.innerHTML = html;
                return { el: wrap, bar: wrap.querySelector('.bar') };
            }

            fileInput.addEventListener('change', (ev) => {
                const f = fileInput.files && fileInput.files[0];
                if (!f) return;
                if (!currentChatUser) { alert('Select a contact first'); fileInput.value = ''; return; }

                const container = document.getElementById('messagesContainer');
                const placeholder = createUploadingBubble(f.name);
                container.appendChild(placeholder.el);
                container.parentElement.scrollTop = container.parentElement.scrollHeight;

                const form = new FormData();
                form.append('receiver_id', currentChatUser);
                form.append('content', '');
                if (pendingReplyTo) form.append('reply_to', pendingReplyTo);
                form.append('files', f);

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                // Use xhr to show upload progress
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '/chat/messages', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', token);
                xhr.upload.addEventListener('progress', (p) => {
                    if (!p.lengthComputable) return;
                    const pct = Math.round((p.loaded / p.total) * 100);
                    try { if (placeholder.bar) placeholder.bar.style.width = pct + '%'; } catch(e){}
                });
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        fileInput.value = '';
                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                // try to use returned payload to render the new message (so filename/name metadata is preserved)
                                const json = JSON.parse(xhr.responseText || '{}');
                                // remove placeholder and append server-rendered message
                                try { placeholder.el.remove(); } catch(e){}
                                const container = document.getElementById('messagesContainer');
                                if (container) {
                                    container.appendChild(renderMessage(json));
                                    container.parentElement.scrollTop = container.parentElement.scrollHeight;
                                    try { initAudioControls(container); } catch(e){}
                                } else {
                                    // fallback: reload messages (user-initiated upload -> mark read)
                                    loadMessages(currentChatUser, true);
                                }
                            } catch (e) {
                                console.warn('Could not parse upload response, reloading messages', e);
                                try { loadMessages(currentChatUser, true); } catch(e) { console.warn('reload after upload failed', e); }
                            }
                        } else {
                            alert('File upload failed (HTTP ' + xhr.status + ')');
                            // remove placeholder
                            try { placeholder.el.remove(); } catch(e){}
                        }
                    }
                };
                xhr.send(form);
            });
        })();

        // restore selected chat from previous session if available
        try {
            const saved = localStorage.getItem('chat_current_user');
                    if (saved && contacts) {
                const el = contacts.querySelector(`[data-user-id="${saved}"]`);
                if (el) {
                    // set UI and load messages
                    document.querySelectorAll('.contact-item').forEach(i => i.classList.remove('active'));
                    el.classList.add('active');
                    const avatar = el.getAttribute('data-avatar');
                    const name = el.querySelector('h6') ? el.querySelector('h6').innerText : '';
                    currentChatUser = saved;
                    if (name) document.getElementById('chatWith').innerText = name;
                    if (avatar) document.getElementById('chatHeaderAvatar').src = avatar;
                    document.getElementById('chatStatus').innerText = 'Online';
                    loadMessages(saved, true);
                    try { if (window.updateBookingFileCounts) setTimeout(window.updateBookingFileCounts, 250); } catch(e){}
                    try {
                        const actions = document.getElementById('bookingActions');
                        if (actions) {
                            const isBooking = (el.getAttribute('data-is-booking') === '1') || /booking/i.test(String(name || ''));
                            actions.style.display = isBooking ? '' : 'none';
                        }
                    } catch(e){}
                }
            }
        } catch (e) {}
    });

    // Typing indicator: send typing status when user types and listen for remote typing via Echo
    (function(){
        const input = document.getElementById('messageInput');
        if (!input) return;
        let typingTimer = null;
        const TYPING_DEBOUNCE = 900; // ms to wait before sending stopped typing

        async function sendTypingStatus(isTyping){
            if (!currentChatUser) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                await fetch('/chat/typing', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ receiver_id: currentChatUser, typing: !!isTyping })
                });
            } catch (e) { /* ignore */ }
        }

        input.addEventListener('input', function(){
            // user started typing
            try { sendTypingStatus(true); } catch(e){}
            if (typingTimer) clearTimeout(typingTimer);
            typingTimer = setTimeout(() => { sendTypingStatus(false); typingTimer = null; }, TYPING_DEBOUNCE);
        });

        // Listen for typing events on Echo channel (other users typing to current user)
        try {
            if (window.Echo && currentUserId) {
                const _typingTimers = {};

                function showTypingInBody(fromKey, avatarSrc) {
                    try {
                        const container = document.getElementById('messagesContainer');
                        if (!container) return;
                        if (container.querySelector(`.typing-row[data-typing-from="${fromKey}"]`)) return;
                        const avatar = avatarSrc || (document.querySelector(`.contact-item[data-user-id="${fromKey}"] .avatar img`) || {}).src || '';
                        const el = document.createElement('div');
                        el.className = 'chats typing-row';
                        el.setAttribute('data-typing-from', fromKey);
                        el.innerHTML = `
                            <div class="chat-avatar"><img src="${avatar}" class="rounded-circle" alt=""></div>
                            <div class="chat-content">
                                <div class="chat-info">
                                    <div class="message-content">
                                        <div class="message-text"><span class="animate-typing">is typing <span class="dot"></span><span class="dot"></span><span class="dot"></span></span></div>
                                    </div>
                                </div>
                            </div>`;
                        container.appendChild(el);
                        try { container.parentElement.scrollTop = container.parentElement.scrollHeight; } catch(e){}
                    } catch (e) { }
                }

                function removeTypingInBody(fromKey) {
                    try {
                        const container = document.getElementById('messagesContainer');
                        if (!container) return;
                        const t = container.querySelector(`.typing-row[data-typing-from="${fromKey}"]`);
                        if (t) t.remove();
                    } catch (e) { }
                }

                Echo.private(`chat.user.${currentUserType}.${currentUserId}`)
                    .listen('.ChatTyping', (e) => {
                        try {
                            const senderType = e.sender_type || e.senderType || '';
                            const senderId = e.sender_id || e.senderId || null;
                            const fromKey = senderType && senderId ? `${senderType}:${senderId}` : null;
                            if (!fromKey) return;
                            const el = document.querySelector(`.contact-item[data-user-id="${fromKey}"]`);
                            if (el) {
                                const msgEl = el.querySelector('.chat-user-msg');
                                if (msgEl) {
                                    if (e.typing) {
                                        if (!msgEl.querySelector('.animate-typing')) {
                                            const p = msgEl.querySelector('p.small') || document.createElement('p');
                                            p.classList.add('small');
                                            p.innerHTML = `<span class="animate-typing">is typing <span class="dot"></span><span class="dot"></span><span class="dot"></span></span>`;
                                            if (!msgEl.querySelector('p.small')) msgEl.appendChild(p);
                                        }
                                    } else {
                                        const anim = msgEl.querySelector('.animate-typing');
                                        if (anim) {
                                            const p = anim.closest('p');
                                            if (p) {
                                                const last = el.getAttribute('data-last-message') || el.getAttribute('data-last-msg') || '';
                                                p.innerText = last || 'Click to open chat';
                                            }
                                        }
                                    }
                                }
                            }

                            if (fromKey && String(fromKey) === String(currentChatUser)) {
                                if (e.typing) {
                                    showTypingInBody(fromKey);
                                    try { if (_typingTimers[fromKey]) clearTimeout(_typingTimers[fromKey]); } catch(_){}
                                    _typingTimers[fromKey] = setTimeout(() => { removeTypingInBody(fromKey); delete _typingTimers[fromKey]; }, 4200);
                                } else {
                                    removeTypingInBody(fromKey);
                                    try { if (_typingTimers[fromKey]) { clearTimeout(_typingTimers[fromKey]); delete _typingTimers[fromKey]; } } catch(_){}
                                }
                            }
                        } catch (ex) { }
                    });
            }
        } catch (er) { }
    })();

    // Booking filters: count and filter file messages by reaction state
    (function(){
        const crossEmojis = ['❌','✖️','❎','✖','✕','x'];

        function isFileMessage(chatEl){
            if (!chatEl) return false;
            return !!chatEl.querySelector('.message-file-bubble, .message-file-link');
        }

        function hasReactionBar(chatEl){
            return !!chatEl.querySelector('.reaction-bar');
        }

        function hasCrossReaction(chatEl){
            try {
                const bar = chatEl.querySelector('.reaction-bar');
                if (!bar) return false;
                const btn = bar.querySelector('.reaction-btn');
                if (!btn) return false;
                // find any reaction button with a cross emoji
                const items = Array.from(bar.querySelectorAll('.reaction-btn'));
                return items.some(b => {
                    const e = (b.getAttribute('data-emoji') || b.querySelector('.emoji')?.innerText || '').trim();
                    return crossEmojis.indexOf(e) !== -1;
                });
            } catch (e) { return false; }
        }

        function updateFileCounts(){
            try {
                const container = document.getElementById('messagesContainer');
                if (!container) return;
                let hold = 0, unbooked = 0;
                container.querySelectorAll('.chats').forEach(ch => {
                    if (!isFileMessage(ch)) return;
                    if (hasReactionBar(ch)) {
                        if (hasCrossReaction(ch)) hold++; else { /* other reactions count as booked */ }
                    } else {
                        unbooked++;
                    }
                });
                const holdEl = document.getElementById('holdCount');
                const unEl = document.getElementById('unbookedCount');
                if (holdEl) holdEl.innerText = String(hold);
                if (unEl) unEl.innerText = String(unbooked);
                // show action area only for booking chats and if there are any files
                const anyFiles = (hold + unbooked) > 0;
                const actions = document.getElementById('bookingActions');
                try {
                    const isBooking = (function(){
                        try {
                            const active = document.querySelector('.contact-item.active') || document.querySelector(`[data-user-id="${currentChatUser}"]`);
                            if (active) {
                                if (active.getAttribute('data-is-booking') === '1' || active.getAttribute('data-group') === 'booking') return true;
                                const h = (active.querySelector('h6') && active.querySelector('h6').innerText) || '';
                                if (/booking/i.test(h)) return true;
                            }
                        } catch(e){}
                        const headerName = (document.getElementById('chatWith') && document.getElementById('chatWith').innerText) || '';
                        return /booking/i.test(headerName);
                    })();
                    if (actions) actions.style.display = (anyFiles && isBooking) ? '' : 'none';
                } catch(e) { if (actions) actions.style.display = anyFiles ? '' : 'none'; }
            } catch (e) { /* ignore */ }
        }

        // Persist and apply a file filter mode ('hold'|'unbooked'|'all')
        window.currentFileFilterMode = window.currentFileFilterMode || null;
        function applyFileFilter(mode){
            try {
                // normalize mode
                if (!mode || mode === 'all') mode = null;
                // persist selection so periodic reloads can reapply
                window.currentFileFilterMode = mode;

                const container = document.getElementById('messagesContainer');
                if (!container) return;
                container.querySelectorAll('.chats').forEach(ch => {
                    const isFile = isFileMessage(ch);
                    let show = true;
                    if (mode === 'hold') {
                        show = isFile && hasCrossReaction(ch);
                    } else if (mode === 'unbooked') {
                        show = isFile && !hasReactionBar(ch);
                    }
                    ch.style.display = show ? '' : 'none';
                });
                // keep buttons in sync
                try { updateFilterButtons(); } catch(e){}
            } catch (e) { /* ignore */ }
        }

        // wire buttons
        document.addEventListener('click', function(ev){
            try {
                const bHold = document.getElementById('btnHold');
                const bUn = document.getElementById('btnUnbooked');
                if (!bHold || !bUn) return;
                const t = ev.target.closest && ev.target.closest('#btnHold, #btnUnbooked');
                if (!t) return;
                if (t.id === 'btnHold') {
                    const active = t.classList.contains('active');
                    // toggle
                    document.getElementById('btnHold').classList.toggle('active', !active);
                    document.getElementById('btnUnbooked').classList.remove('active');
                    if (!active) applyFileFilter('hold'); else applyFileFilter('all');
                } else if (t.id === 'btnUnbooked') {
                    const active = t.classList.contains('active');
                    document.getElementById('btnUnbooked').classList.toggle('active', !active);
                    document.getElementById('btnHold').classList.remove('active');
                    if (!active) applyFileFilter('unbooked'); else applyFileFilter('all');
                }
            } catch(e){}
        });

        // helper to sync button active state with the persisted filter
        function updateFilterButtons(){
            try {
                const bHold = document.getElementById('btnHold');
                const bUn = document.getElementById('btnUnbooked');
                const mode = window.currentFileFilterMode || null;
                if (bHold) bHold.classList.toggle('active', mode === 'hold');
                if (bUn) bUn.classList.toggle('active', mode === 'unbooked');
            } catch(e){}
        }

        // expose for use after messages reload
        window.updateBookingFileCounts = updateFileCounts;
        window.applyBookingFileFilter = applyFileFilter;
        // update counts when DOM ready
        document.addEventListener('DOMContentLoaded', function(){ try { updateFileCounts(); } catch(e){} });
    })();

    // Helper: return true if any audio element is currently playing
    function isAnyAudioPlaying(){
        try {
            const audios = Array.from(document.querySelectorAll('audio'));
            return audios.some(a => !a.paused && !a.ended && a.currentTime > 0);
        } catch (e) { return false; }
    }

    // Simple Polling (skip reload while user is listening to audio)
    setInterval(() => { if (currentChatUser && !isAnyAudioPlaying()) loadMessages(currentChatUser); }, 5000);

    // Poll contacts endpoint to refresh sidebar (every 5s)
    async function refreshContacts(){
        const contactsEl = document.getElementById('contactsList');
        if (!contactsEl) return;
        try {
            const res = await fetch('/chat/contacts', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();
            // preserve existing `.chat-pin` and `.chat-dropdown` HTML for contacts so we don't immediately remove server-rendered icons/menus
            const existingPinHtml = {};
            const existingDropdownHtml = {};
            try {
                contactsEl.querySelectorAll('.contact-item').forEach(it => {
                    try {
                        const id = it.getAttribute('data-user-id');
                        const pin = it.querySelector('.chat-pin');
                        const drop = it.querySelector('.chat-dropdown');
                        if (id && pin) existingPinHtml[String(id)] = pin.outerHTML || pin.innerHTML || '';
                        if (id && drop) existingDropdownHtml[String(id)] = drop.outerHTML || drop.innerHTML || '';
                    } catch (inner) { }
                });
            } catch (e) { /* ignore */ }

            // build html
                const html = data.map(u => {
                const avatar = u.avatar || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name) + '&background=ffffff&color=0D8ABC&size=128');
                const unread = u.unread_count && u.unread_count > 0 ? `<span class="count-message fs-12 fw-semibold">${u.unread_count}</span>` : '';

                // sanitize last message: show 'Audio' for audio markers instead of the raw path
                let lastMsg = u.last_message || '';
                // build pin/check icons + unread badge for refreshed sidebar (place inside .chat-pin)
                let pinIcons = '';
                try {
                    // normalize field names coming from server
                    const lastSenderId = (u.last_message_sender_id != null) ? u.last_message_sender_id : (u.last_message_sender_orig_id != null ? u.last_message_sender_orig_id : null);
                    const lastSenderType = u.last_message_sender_type || u.last_message_sender_type_orig || u.type || '';
                    if (lastSenderId != null && String(lastSenderId) === String(currentUserId) && (lastSenderType || '') === currentUserType) {
                        if (u.last_message_read_at) {
                            pinIcons = '<i class="ti ti-pinned me-2"></i><i class="ti ti-checks text-success"></i>';
                            if (!window.TI_ICON_CHECKS) pinIcons = '<i class="ti ti-pinned me-2"></i><span style="color:#00c851; margin-right:6px">✓✓</span>';
                        } else {
                            pinIcons = '<i class="ti ti-pinned me-2"></i><i class="ti ti-check" style="color:#6b7280"></i>';
                            if (!window.TI_ICON_CHECK) pinIcons = '<i class="ti ti-pinned me-2"></i><span style="color:#6b7280; margin-right:6px">✓</span>';
                        }
                    }
                } catch (e) { /* ignore */ }

                let lastHtml = '<p class="small text-truncate">Click to open chat</p>';
                if (lastMsg && lastMsg.trim().length) {
                    try {
                        const raw = String(lastMsg || '').trim();
                        if (/^AUDIO::/i.test(raw) || /^\[?audio\]?$/i.test(raw)) {
                            lastHtml = `<p class="small text-truncate"><i class="ti ti-microphone"></i> Audio</p>`;
                        } else {
                            // check for REPLY::base64(::optional_rest) pattern
                            const m = raw.match(/^REPLY::([A-Za-z0-9_\-+=\/]+)(?:::(.*))?$/i);
                            let snippet = '';
                            if (m) {
                                try {
                                    const decoded = (function(b){ try { return atob(b); } catch(e){ return null; } })(m[1]);
                                    if (decoded) {
                                        try {
                                            const j = JSON.parse(decoded);
                                            if (j && typeof j === 'object' && j.snippet) snippet = String(j.snippet || '').trim();
                                        } catch (je) {
                                            // not json, ignore
                                        }
                                    }
                                } catch(e) { }
                                if (!snippet && m[2]) snippet = String(m[2] || '').trim();
                                if (!snippet) snippet = raw.replace(/^REPLY::/i,'');
                            } else {
                                snippet = raw;
                            }
                            // strip legacy name/time prefixes
                            try { snippet = snippet.replace(/^↪.*?:\s*/, ''); } catch(e){}
                            try { snippet = snippet.replace(/^\s*(?:You\s*)?\d{1,2}:\d{2}\s*(?:AM|PM)?\s*[:\-–]?\s*/i, ''); } catch(e){}
                            // shorten for sidebar
                            const max = 48;
                            if (snippet.length > max) snippet = snippet.slice(0, max) + '...';
                            // show arrow for replies, otherwise plain text
                            if (/^REPLY::/i.test(raw)) {
                                lastHtml = `<p class="small text-truncate">↪ ${escapeHtml(snippet)}</p>`;
                            } else {
                                lastHtml = `<p class="small text-truncate">${escapeHtml(snippet)}</p>`;
                            }
                        }
                    } catch (e) {
                        lastHtml = `<p class="small text-truncate">${escapeHtml(String(lastMsg))}</p>`;
                    }
                }

                // format sidebar time robustly: accept ISO, MySQL 'YYYY-MM-DD HH:MM:SS', or already-formatted strings
                function parseSidebarDate(raw) {
                    if (!raw && raw !== 0) return null;
                    // if already a Date object
                    if (raw instanceof Date) return isNaN(raw) ? null : raw;
                    // numbers (timestamps)
                    if (typeof raw === 'number') return new Date(raw);
                    let s = String(raw).trim();
                    // if it's a short time like "09:41 PM", return null (we'll use it as-is)
                    if (/^\d{1,2}:\d{2}\s*(AM|PM)$/i.test(s)) return null;
                    // convert MySQL 'YYYY-MM-DD HH:MM:SS' to ISO 'YYYY-MM-DDTHH:MM:SS'
                    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/.test(s)) s = s.replace(' ', 'T');
                    // try parsing
                    const parsed = new Date(s);
                    if (!isNaN(parsed)) return parsed;
                    const fallback = Date.parse(s);
                    if (!isNaN(fallback)) return new Date(fallback);
                    return null;
                }

                function formatSidebarTime(raw) {
                    if (!raw && raw !== 0) return '';
                    // if raw already looks like '09:41 PM' return it
                    if (typeof raw === 'string' && /^\d{1,2}:\d{2}\s*(AM|PM)$/i.test(raw.trim())) return raw.trim();
                    const d = parseSidebarDate(raw);
                    if (!d) return String(raw || '');
                    // Always return the local time portion for sidebar
                    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }
                // If this contact is the currently open chat, prefer the time shown in the chat body
                let timeStr = formatSidebarTime(u.last_message_at);
                try {
                    const activeId = String(currentChatUser || '');
                    if (String(u.id) === activeId) {
                        const msgs = document.getElementById('messagesContainer');
                        if (msgs) {
                            // find the last visible .chat-time in messages (prefer last occurrence)
                            const times = msgs.querySelectorAll('.chat-time');
                            if (times && times.length) {
                                const last = times[times.length - 1].innerText.trim();
                                if (last) timeStr = last;
                            }
                        }
                    }
                } catch (e) { /* ignore */ }

                // build chat-pin HTML (pins/checks + unread badge)
                const unreadBadge = unread || '';
                // If server didn't include pinIcons but we have preserved server-rendered pin HTML, reuse it
                let pinHtml;
                if ((!pinIcons || String(pinIcons).trim() === '') && (!unreadBadge || String(unreadBadge).trim() === '')) {
                    const prev = existingPinHtml[String(u.id)];
                    if (prev) {
                        pinHtml = prev;
                    } else {
                        pinHtml = `<div class="chat-pin">${unreadBadge}</div>`;
                    }
                } else {
                    pinHtml = `<div class="chat-pin">${pinIcons}${unreadBadge}</div>`;
                }
                // Build dropdown HTML: reuse preserved dropdown if available, otherwise provide default
                const defaultDropdown = '<div class="chat-dropdown">' +
                    '<a href="#" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>' +
                    '<ul class="dropdown-menu dropdown-menu-end p-3">' +
                        '<li><a class="dropdown-item" href="#"><i class="ti ti-box-align-right me-2"></i>Archive Chat</a></li>' +
                        '<li><a class="dropdown-item" href="#"><i class="ti ti-heart me-2"></i>Mark as Favourite</a></li>' +
                        '<li><a class="dropdown-item" href="#"><i class="ti ti-check me-2"></i>Mark as Unread</a></li>' +
                        '<li><a class="dropdown-item" href="#"><i class="ti ti-pinned me-2"></i>Pin Chats</a></li>' +
                        '<li><a class="dropdown-item" href="#"><i class="ti ti-trash me-2"></i>Delete</a></li>' +
                    '</ul>' +
                '</div>';
                const dropdownHtml = existingDropdownHtml[String(u.id)] || defaultDropdown;
                return `
                    <div class="chat-list contact-item" data-user-id="${u.id}" data-avatar="${avatar}">
                        <a href="javascript:void(0);" class="chat-user-list">
                            <div class="avatar avatar-lg online me-2"><img src="${avatar}" class="rounded-circle" alt="image"></div>
                            <div class="chat-user-info">
                                <div class="chat-user-msg">
                                    <h6>${u.name}</h6>
                                    ${lastHtml}
                                </div>
                                <div class="chat-user-time">
                                    <span class="time">${timeStr}</span>
                                    ${pinHtml}
                                </div>
                            </div>
                        </a>
                        ${dropdownHtml}
                    </div>`;
            }).join('');

            // preserve scroll position and active selection
            const activeId = currentChatUser || null;
            contactsEl.innerHTML = html;
            if (activeId) {
                const sel = contactsEl.querySelector(`[data-user-id="${activeId}"]`);
                if (sel) sel.classList.add('active');
            }
            // Re-apply current search filter (if any) so polling doesn't clear user's search
            try {
                const searchInputEl = document.getElementById('chatSearchInput');
                const q = searchInputEl ? String(searchInputEl.value || '').trim().toLowerCase() : '';
                if (q) {
                    contactsEl.querySelectorAll('.contact-item').forEach(it => {
                        try {
                            const name = (it.querySelector('h6') && it.querySelector('h6').innerText || '').toLowerCase();
                            const last = (it.querySelector('.chat-user-msg p') && it.querySelector('.chat-user-msg p').innerText || '').toLowerCase();
                            const match = !q || name.indexOf(q) !== -1 || last.indexOf(q) !== -1;
                            it.style.display = match ? '' : 'none';
                        } catch (inner) {}
                    });
                }
            } catch (e) { /* ignore */ }
        } catch (e) {
            console.error('refreshContacts error', e);
        }
    }

    // start refreshing contacts after a short delay to avoid overwriting server-rendered sidebar immediately
    setTimeout(() => {
        // initial refresh then continue polling
        refreshContacts();
        setInterval(refreshContacts, 5000);
    }, 2000);

    // If Laravel Echo is available, subscribe to a private channel for this user
    try {
        if (window.Echo && currentUserId) {
            const chName = `private-chat.user.${currentUserType}.${currentUserId}`.replace('private-','');
            // standard Echo private channel subscription
            Echo.private(`chat.user.${currentUserType}.${currentUserId}`)
                .listen('.ChatMessageSent', (e) => {
                    try {
                        // incoming payload for current user (someone sent them a message)
                        // refresh sidebar instantly
                        refreshContacts();
                        // if active chat is with sender, or if this event targets the current group view, append message
                        const senderType = e.sender_type || e.senderType || e.payload?.sender_type || '';
                        const senderId = e.sender_id || e.senderId || e.payload?.sender_id || null;
                        const pref = senderType && senderId ? `${senderType}:${senderId}` : null;
                        const receiverPref = e.receiver_pref || ((e.receiver_type && e.receiver_id) ? `${e.receiver_type}:${e.receiver_id}` : null);
                        if ( (pref && currentChatUser === pref) || (receiverPref && currentChatUser === receiverPref) ) {
                            // append message or reload messages
                            if (typeof e === 'object') {
                                // event payload might be the message object directly
                                const msg = e;
                                // prefer payload property
                                const data = e.payload || e;
                                const container = document.getElementById('messagesContainer');
                                if (container) {
                                    container.appendChild(renderMessage(data));
                                    container.parentElement.scrollTop = container.parentElement.scrollHeight;
                                }
                            } else {
                                loadMessages(currentChatUser);
                            }
                        }
                    } catch (ex) { console.error('Echo event handler error', ex); }
                });
            Echo.private(`chat.user.${currentUserType}.${currentUserId}`)
                .listen('.ChatMessageRead', (e) => {
                    try {
                        // incoming read receipt: mark messages as read in the sender's UI
                        const ids = e && e.message_ids ? e.message_ids : (e && e.message_ids ? e.message_ids : (e && e.payload && e.payload.message_ids ? e.payload.message_ids : []));
                        if (!Array.isArray(ids)) return;
                        ids.forEach(id => {
                            try {
                                const el = document.querySelector(`[data-message-id="${id}"]`);
                                if (el) {
                                    const readEl = el.querySelector('.msg-read');
                                    if (readEl) {
                                        readEl.classList.add('success','read');
                                        readEl.innerHTML = '<i class="ti ti-checks"></i>';
                                    }
                                }
                            } catch (inner) { /* ignore individual id errors */ }
                        });
                    } catch (ex) { console.error('ChatMessageRead handler error', ex); }
                });
            // Reaction updates: listen for server broadcasts and update UI live
            Echo.private(`chat.user.${currentUserType}.${currentUserId}`)
                .listen('.ChatMessageReacted', (payload) => {
                    try {
                        // payload contains: message_id, reactions (map emoji=>count), reaction_users (map emoji=>[userKey])
                        const mid = payload && (payload.message_id || payload.messageId || payload.id) ? (payload.message_id || payload.messageId || payload.id) : null;
                        if (!mid) return;

                        const counts = payload.reactions || {};
                        const usersMap = payload.reaction_users || {};
                        const currentKey = (currentUserType || '') + ':' + (currentUserId || '');

                        // Remove any existing reaction buttons that are no longer present
                        try {
                            const sel = document.querySelector(`[data-message-id="${mid}"]`);
                            if (sel) {
                                const bar = sel.querySelector('.reaction-bar');
                                if (bar) {
                                    // remove buttons for emojis not present in counts
                                    Array.from(bar.querySelectorAll('.reaction-btn')).forEach(b => {
                                        try {
                                            const em = b.getAttribute('data-emoji');
                                            if (!(em in counts)) b.remove();
                                        } catch(e){}
                                    });
                                }
                            }
                        } catch(e){}

                        // Upsert buttons from counts map
                        Object.keys(counts).forEach(emoji => {
                            try {
                                const c = counts[emoji];
                                const users = usersMap[emoji] || [];
                                const youReacted = Array.isArray(users) ? users.indexOf(currentKey) !== -1 : false;
                                upsertReactionUI(mid, emoji, { count: Number(c), you_reacted: !!youReacted });
                            } catch(e){}
                        });

                        // If server indicates that selected_emoji was removed for this user, ensure local state cleared
                        try {
                            if (payload.selected_emoji === null || payload.acted === false) {
                                // If acted === false and userKey not present anywhere, clear local map
                                let found = false;
                                Object.keys(usersMap).forEach(k => { if (Array.isArray(usersMap[k]) && usersMap[k].indexOf(currentKey) !== -1) found = true; });
                                if (!found) try { delete window.chatLocalReactions[String(mid)]; } catch(e){}
                            }
                        } catch(e){}
                    } catch (ex) { console.error('ChatMessageReacted handler error', ex); }
                });
        }
    } catch (er) { /* ignore */ }

    // Initialize audio controls for any audio bubbles rendered on the page
    function initAudioControls(root = document) {
        const bubbles = root.querySelectorAll('.message-audio-bubble');
        bubbles.forEach(b => {
            if (b._audioInitialized) return;
            const audio = b.querySelector('audio');
            const mic = b.querySelector('.mic-circle');
            const durationEl = b.querySelector('.audio-duration');
            const bars = b.querySelectorAll('.bar-vert');
            if (!audio || !mic || !durationEl) return;

            // set total duration when metadata is available
            audio.addEventListener('loadedmetadata', () => {
                const d = Math.floor(audio.duration || 0);
                const mm = Math.floor(d/60);
                const ss = d % 60;
                durationEl.innerText = `${mm}:${String(ss).padStart(2,'0')}`;
            });

            // keep UI in sync when playback state changes
            audio.addEventListener('play', () => {
                b.classList.add('playing');
                mic.innerHTML = '<i class="ti ti-player-pause"></i>';
            });
            audio.addEventListener('pause', () => {
                // if ended event will handle it, but pause also needs to clear playing state
                if (!audio.ended) {
                    b.classList.remove('playing');
                    mic.innerHTML = '<i class="ti ti-microphone"></i>';
                }
            });
            audio.addEventListener('ended', () => {
                b.classList.remove('playing');
                mic.innerHTML = '<i class="ti ti-microphone"></i>';
            });

            function play() {
                // pause all others and clear their UI
                document.querySelectorAll('audio').forEach(a => {
                    if (a !== audio) {
                        try { a.pause(); } catch(e){}
                        const pb = a.closest('.message-audio-bubble');
                        if (pb) {
                            pb.classList.remove('playing');
                            const pm = pb.querySelector('.mic-circle');
                            if (pm) pm.innerHTML = '<i class="ti ti-microphone"></i>';
                        }
                    }
                });
                audio.play().catch(ex => console.error('audio play failed', ex));
                // UI will be set by 'play' event
            }

            function pause() {
                audio.pause();
                // UI will be set by 'pause' event
            }

            // toggle on click anywhere in the bubble for easier UX
            b.addEventListener('click', (ev) => {
                ev.stopPropagation();
                if (audio.paused) play(); else pause();
            });

            b._audioInitialized = true;
        });
    }

    // initialize any audio controls already present
    document.addEventListener('DOMContentLoaded', () => { try { hydrateChatLocalReactions(); } catch(e){}; initAudioControls(); try { if (window.updateBookingFileCounts) window.updateBookingFileCounts(); } catch(e){} });

    // Simple lightbox for media previews (images/videos)
    (function(){
        function openLightbox(type, src) {
            const lb = document.createElement('div');
            lb.className = 'media-lightbox';
            lb.innerHTML = `<div class="inner">${type === 'video' ? `<video controls src="${src}" autoplay></video>` : `<img src="${src}" alt="preview"/>`}</div><button class="close" aria-label="Close">✕</button>`;
            document.body.appendChild(lb);
            lb.querySelector('.close').addEventListener('click', () => lb.remove());
            lb.addEventListener('click', (e) => { if (e.target === lb) lb.remove(); });
        }

        document.addEventListener('click', (ev) => {
            const a = ev.target.closest && ev.target.closest('.media-preview');
            if (!a) return;
            ev.preventDefault();
            const t = a.getAttribute('data-type');
            const s = a.getAttribute('data-src') || a.href;
            if (!s) return;
            openLightbox(t === 'video' ? 'video' : 'image', s);
        });

        // download button handler (delegated)
        document.addEventListener('click', (ev) => {
            const btn = ev.target.closest && ev.target.closest('.download-btn');
            if (!btn) return;
            // prevent the anchor wrapper from also handling this click (so download button doesn't open the file)
            ev.stopPropagation();
            ev.preventDefault();
            const src = btn.getAttribute('data-src');
            if (!src) return;
            // programmatically trigger a download (works even if clicking button inside bubble)
            try {
                const a = document.createElement('a');
                a.href = src;
                a.setAttribute('download', '');
                a.setAttribute('rel', 'noopener noreferrer');
                a.style.display = 'none';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } catch (e) {
                // fallback to opening in new tab
                window.open(src, '_blank');
            }
        });
    })();

        // Ensure we can hydrate local reaction state from server-rendered DOM
        function hydrateChatLocalReactions(root) {
            try {
                root = root || document;
                const container = root.getElementById ? root.getElementById('messagesContainer') : document;
                if (!container) return;
                container.querySelectorAll('.chats[data-message-id]').forEach(el => {
                    try {
                        const mid = el.getAttribute('data-message-id');
                        if (!mid) return;
                        const bar = el.querySelector('.reaction-bar');
                        if (!bar) return;
                        // find any button marked as 'you-reacted' or having data-you
                        const youBtn = bar.querySelector('.reaction-btn.you-reacted, .reaction-btn[data-you]');
                        if (youBtn) {
                            const emoji = youBtn.getAttribute('data-emoji');
                            if (emoji) window.chatLocalReactions[String(mid)] = emoji;
                        }
                        // ensure padding so reaction bar doesn't overlap text
                        try { if (bar && bar.parentElement && bar.parentElement.classList) bar.parentElement.classList.add('has-reactions'); } catch(e){}
                    } catch (e) { /* ignore */ }
                });
            } catch (e) { /* ignore */ }
        }

        // Reaction & reply handlers: open a small emoji palette to react and support quick-reply
    (function(){
        const REACTIONS = ['✅','👍','❤️','😂','😮','😢','👏','❌'];
        // inject minimal styles for reaction UI once
        (function ensureReactionStyles(){
            if (document.getElementById('reaction-styles')) return;
            const s = document.createElement('style');
            s.id = 'reaction-styles';
                s.innerHTML = `
                .reaction-popover{position:absolute;z-index:99999;background:#fff;border:1px solid rgba(0,0,0,0.08);box-shadow:0 6px 18px rgba(0,0,0,0.08);padding:6px;border-radius:8px}
                .reaction-list{display:flex;gap:6px}
                .reaction-item{background:transparent;border:0;padding:6px 8px;font-size:18px;cursor:pointer}
                /* Position reaction buttons at the top-right of the message bubble so they're visible */
                     /* place the reaction bar slightly above the bubble so it doesn't overlap text
                         and style it as a compact pill group */
                     .reaction-bar{position:absolute;top:-10px;display:flex;gap:6px;flex-wrap:nowrap;z-index:50;align-items:center}
                     .chats .message-content{position:relative}
                     .chats.chats-right .reaction-bar{right:8px;left:auto}
                     .chats:not(.chats-right) .reaction-bar{left:8px;right:auto}
                     .reaction-bar{background:rgba(255,255,255,0.95);border:1px solid rgba(11,120,209,0.08);padding:6px;border-radius:20px;box-shadow:0 6px 18px rgba(8,24,48,0.06)}
                     .message-content.has-reactions{padding-top:26px}
                .reaction-btn{display:inline-flex;align-items:center;gap:6px;border-radius:16px;padding:4px 8px;border:1px solid rgba(0,0,0,0.06);background:rgba(255,255,255,0.9);cursor:pointer;min-width:34px}
                .reaction-btn .emoji{font-size:14px}
                .reaction-btn .count{font-size:12px;color:#333}
            `;
            document.head.appendChild(s);
        })();
        let popover = null;
        // track this user's reactions per-message to prevent multiple reacts on same bubble
        const _currentUserKey = (currentUserType || '') + ':' + (currentUserId || '');

        function removeReactionFromUI(messageId, emoji){
            try {
                const sel = document.querySelector(`[data-message-id="${messageId}"]`);
                if (!sel) return;
                const bar = sel.querySelector('.reaction-bar');
                if (!bar) return;
                const btn = bar.querySelector(`.reaction-btn[data-emoji="${emoji}"]`);
                if (!btn) return;
                const cnt = btn.querySelector('.count');
                if (cnt) {
                    const cur = Number(cnt.innerText || '0') - 1;
                    if (cur <= 0) {
                        btn.remove();
                    } else if (cur === 1) {
                        // for single reaction don't show numeric count
                        cnt.remove();
                    } else {
                        cnt.innerText = String(cur);
                    }
                } else {
                    // no numeric count shown - removing user's reaction likely makes it zero
                    btn.remove();
                }
                // if bar empty remove it
                if (bar.children.length === 0) {
                    // remove bar and clear has-reactions padding
                    try {
                        const parent = bar.parentElement;
                        bar.remove();
                        if (parent && parent.classList) parent.classList.remove('has-reactions');
                    } catch(e) { try { bar.remove(); } catch(_){} }
                }
                // clear local mapping
                try { delete window.chatLocalReactions[String(messageId)]; } catch(e){}
            } catch (e) { /* ignore */ }
        }

        function closePopover(){ if (popover) { popover.remove(); popover = null; } }

        function positionPopover(target, pop){
            const r = target.getBoundingClientRect();
            const w = pop.offsetWidth;
            const h = pop.offsetHeight;
            let top = window.scrollY + r.top - h - 8;
            if (top < window.scrollY + 8) top = window.scrollY + r.bottom + 8;
            let left = window.scrollX + r.left + (r.width/2) - (w/2);
            if (left < 8) left = 8;
            if (left + w > window.innerWidth - 8) left = Math.max(8, window.innerWidth - 8 - w);
            pop.style.top = top + 'px'; pop.style.left = left + 'px';
        }

        function openReactionPopup(targetEl, messageEl, messageId){
            closePopover();
            const pop = document.createElement('div');
            pop.className = 'reaction-popover';
            pop.setAttribute('role','dialog');
            pop.innerHTML = `<div class="reaction-list">${REACTIONS.map(e => `<button type="button" class="reaction-item" data-emoji="${e}">${e}</button>`).join('')}</div>`;
            document.body.appendChild(pop);
            pop.addEventListener('click', (ev) => {
                const btn = ev.target.closest('.reaction-item');
                if (!btn) return;
                const emoji = btn.getAttribute('data-emoji');
                if (!messageId) return closePopover();
                sendReaction(messageId, emoji);
                closePopover();
            });
            popover = pop;
            // position after append so measurements work
            try { positionPopover(targetEl, pop); } catch(e){}
        }

        function sendReaction(messageId, emoji){
            try {
                if (!messageId) return;
                const key = String(messageId);
                const current = (window.chatLocalReactions[key] || null);
                const isUnreact = current === emoji; // clicking same emoji should remove

                // optimistic UI update
                if (isUnreact) {
                    try { removeReactionFromUI(messageId, emoji); } catch(e){}
                    try { delete window.chatLocalReactions[key]; } catch(e){}
                } else {
                    // if previously reacted with different emoji, remove that first
                    if (current && current !== emoji) {
                        try { removeReactionFromUI(messageId, current); } catch(e){}
                    }
                    // mark desired emoji locally so UI reflects choice immediately
                    try { window.chatLocalReactions[key] = emoji; } catch(e){}
                    // optimistically add UI
                    try { upsertReactionUI(messageId, emoji, { you_reacted: true }); } catch(e){}
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch('/chat/messages/reaction', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ message_id: messageId, emoji: emoji, remove: !!isUnreact })
                }).then(r => r.json()).then(json => {
                    try {
                        // server payload may include authoritative counts and whether user still reacted
                        const payload = json || {};
                        const acted = (typeof payload.acted === 'boolean') ? payload.acted : null;
                        const sel = (payload.selected_emoji !== undefined) ? payload.selected_emoji : (payload.selectedEmoji !== undefined ? payload.selectedEmoji : null);
                        const you_reacted = (payload.you_reacted === true || payload.user_reacted === true) ? true : (payload.you_reacted === false ? false : null);

                        // Interpret server signals indicating the reaction was removed
                        const serverSaysRemoved = (acted === false) || (sel === null) || (you_reacted === false) || (payload.removed === true);
                        if (serverSaysRemoved) {
                            try { removeReactionFromUI(messageId, emoji); } catch(e){}
                            try { delete window.chatLocalReactions[key]; } catch(e){}
                        } else {
                            // Only upsert if server provides counts or explicitly indicates you reacted
                            if (payload && (typeof payload.count === 'number' || payload.you_reacted === true || (payload.reactions && payload.reactions[emoji] !== undefined))) {
                                try { upsertReactionUI(messageId, emoji, payload); } catch(e){}
                                try { if (payload.you_reacted) window.chatLocalReactions[key] = emoji; } catch(e){}
                            } else {
                                // no authoritative data: avoid creating a phantom button; keep optimistic state
                            }
                        }
                    } catch(e){ console.warn('upsert after send error', e); }
                }).catch(err => {
                    console.warn('Reaction failed', err);
                    // revert optimistic change on error
                    try {
                        if (isUnreact) {
                            // we attempted to remove but server failed; restore UI
                            if (current) upsertReactionUI(messageId, current, { you_reacted: true });
                            try { window.chatLocalReactions[key] = current; } catch(e){}
                        } else {
                            // we attempted to add; remove optimistic UI
                            removeReactionFromUI(messageId, emoji);
                            try { if (current) window.chatLocalReactions[key] = current; else delete window.chatLocalReactions[key]; } catch(e){}
                        }
                    } catch(e){}
                });
            } catch (e) { console.warn('sendReaction error', e); }
        }

        function upsertReactionUI(messageId, emoji, payload){
            try {
                const sel = document.querySelector(`[data-message-id="${messageId}"]`);
                if (!sel) return;
                let bar = sel.querySelector('.reaction-bar');
                if (!bar) {
                    bar = document.createElement('div');
                    bar.className = 'reaction-bar';
                    const contentContainer = sel.querySelector('.message-content') || sel.querySelector('.chat-content') || sel;
                    try { contentContainer.style.position = contentContainer.style.position || 'relative'; } catch(e){}
                    contentContainer.appendChild(bar);
                    try { contentContainer.classList.add('has-reactions'); } catch(e){}
                }

                // find existing reaction button for this emoji
                let btn = bar.querySelector(`.reaction-btn[data-emoji="${emoji}"]`);
                const serverCount = (payload && typeof payload.count === 'number') ? Number(payload.count) : null;
                // Determine whether _this user_ reacted based only on server payload or explicit reaction_users map.
                let youReacted = false;
                try {
                    if (payload && (payload.you_reacted === true || payload.user_reacted === true)) youReacted = true;
                    else if (payload && payload.reaction_users && Array.isArray(payload.reaction_users[emoji])) {
                        const currentKey = (currentUserType || '') + ':' + (currentUserId || '');
                        youReacted = payload.reaction_users[emoji].indexOf(currentKey) !== -1;
                    }
                } catch(e) { youReacted = false; }

                if (btn) {
                    // update count display
                    let cnt = btn.querySelector('.count');
                    if (serverCount !== null) {
                        if (serverCount <= 0) {
                            btn.remove();
                            btn = null;
                        } else if (serverCount === 1) {
                            if (cnt) cnt.remove();
                        } else {
                            if (cnt) cnt.innerText = String(serverCount);
                            else {
                                const span = document.createElement('span');
                                span.className = 'count';
                                span.innerText = String(serverCount);
                                btn.appendChild(span);
                            }
                        }
                    } else {
                        // no server count provided; ensure at least the button exists
                    }
                }

                if (!btn) {
                    // Only create a new button if server provided a count or explicitly indicated you reacted (optimistic add handled elsewhere)
                    if (serverCount === null && !(payload && payload.you_reacted === true)) {
                        return; // don't create a phantom button without server confirmation
                    }
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'reaction-btn';
                    b.setAttribute('data-emoji', emoji);
                    const initialCount = serverCount !== null ? serverCount : (payload && payload.you_reacted ? 1 : 0);
                    if (initialCount > 1) {
                        const span = document.createElement('span');
                        span.className = 'count';
                        span.innerText = String(initialCount);
                        b.innerHTML = `<span class="emoji">${emoji}</span> `;
                        b.appendChild(span);
                    } else {
                        b.innerHTML = `<span class="emoji">${emoji}</span>`;
                    }
                    b.addEventListener('click', () => { sendReaction(messageId, emoji); });
                    bar.appendChild(b);
                    btn = b;
                }

                // mark as you-reacted if applicable
                try {
                    if (youReacted) {
                        btn.classList.add('you-reacted');
                        btn.setAttribute('data-you', '1');
                    } else {
                        btn.classList.remove('you-reacted');
                        btn.removeAttribute('data-you');
                    }
                } catch(e){}
            } catch (e) { console.warn('upsertReactionUI error', e); }
        }

        // quick-reply: insert a short quoted prefix into input
        function quickReplyToMessage(messageEl){
            try {
                const input = document.getElementById('messageInput');
                if (!input) return;
                // Don't inject a prefix into the input (prevents storing name/time in message content)
                input.focus();
                // set pending reply id and show preview
                try {
                    const mid = messageEl.getAttribute('data-message-id');
                    if (mid) {
                        pendingReplyTo = mid;
                        // attempt to gather sender name (first text node) and a clean snippet for preview
                        let sender = '';
                        const nameEl = messageEl.querySelector('.chat-profile-name h6');
                        if (nameEl) {
                            if (nameEl.childNodes && nameEl.childNodes.length) {
                                const first = nameEl.childNodes[0];
                                sender = first && first.nodeType === 3 ? String(first.textContent).trim() : (nameEl.textContent || '').trim();
                            } else {
                                sender = (nameEl.textContent || '').trim();
                            }
                        }
                        const textEl = messageEl.querySelector('.message-text');
                        const fileTitleEl = messageEl.querySelector('.file-title');
                        const snippet = (textEl && textEl.innerText) || (fileTitleEl && (fileTitleEl.getAttribute('title') || fileTitleEl.innerText)) || '';
                        showReplyPreview({ sender_name: sender, snippet: snippet });
                    }
                } catch(e){}
            } catch (e) { }
        }

        // Replace hash-like storage filenames with friendly labels for legacy messages
        function sanitizeFileTitles(){
            try {
                document.querySelectorAll('.message-file-link .file-title').forEach(el => {
                    try {
                        const rawTitle = String(el.getAttribute('title') || '').trim();
                        const text = String(el.innerText || '').trim();
                        // detect storage-hash like names (long, no spaces, mostly alnum and punctuation)
                        const looksLikeHash = rawTitle && (/^[A-Za-z0-9_-]{12,}\.[A-Za-z0-9]{1,6}$/.test(rawTitle) || (rawTitle.length > 20 && rawTitle.indexOf(' ') === -1));
                        if (!looksLikeHash) return;
                        // derive extension label
                        const m = rawTitle.match(/\.([A-Za-z0-9]{1,6})$/);
                        const extLabel = m ? m[1].toUpperCase() : '';
                        const friendly = extLabel ? (extLabel + ' file') : 'Attachment';
                        // update title to friendly label so hover doesn't show storage name
                        el.setAttribute('title', friendly);
                        // if visible text is the raw storage name, replace with friendly text and keep ext
                        if (text === rawTitle || /^[A-Za-z0-9_-]{8,}/.test(text)){
                            if (extLabel) el.innerHTML = escapeHtml(friendly) + '<span class="file-ext">' + extLabel + '</span>';
                            else el.innerText = friendly;
                        }
                    } catch(e) { /* ignore per-element errors */ }
                });
            } catch(e){}
        }

        // global click handler for emoj action and reply icon
        document.addEventListener('click', (ev) => {
            try {
                const msg = ev.target.closest && ev.target.closest('.chats');
                if (msg) {
                    // Reaction: click on the emoj-action container or the smile icon
                    // Only open reactions when user clicks the dedicated reaction control (smile button)
                    if (ev.target.closest && (ev.target.closest('.emoj-action') || ev.target.closest('i.ti-mood-smile'))) {
                        ev.preventDefault(); ev.stopPropagation();
                        const ea = ev.target.closest('.emoj-action') || ev.target.closest('i.ti-mood-smile');
                        const mid = msg.getAttribute('data-message-id') || null;
                        openReactionPopup(ea, msg, mid);
                        return;
                    }

                    // Reply: click on the arrow-forward-up icon or its anchor
                    if (ev.target.closest && (ev.target.closest('i.ti-arrow-forward-up') || ev.target.closest('a') && ev.target.closest('a').querySelector('i.ti-arrow-forward-up'))) {
                        ev.preventDefault(); ev.stopPropagation();
                        quickReplyToMessage(msg);
                        return;
                    }
                }
            } catch (e) { /* ignore */ }

            // clicking outside closes popover
            if (popover && !ev.target.closest('.reaction-popover')) closePopover();
        });

        // clicking a quoted reply should jump to the original message (if present)
        document.addEventListener('click', function(ev){
            try {
                const r = ev.target.closest && ev.target.closest('.message-reply');
                if (!r) return;
                const id = r.getAttribute('data-reply-to') || r.dataset.replyTo;
                if (!id) return;
                const container = document.getElementById('messagesContainer');
                if (!container) return;
                const target = container.querySelector(`[data-message-id="${id}"]`);
                if (target) {
                    try { target.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(e) { target.scrollIntoView(); }
                    target.classList.add('reply-highlight');
                    setTimeout(() => { try { target.classList.remove('reply-highlight'); } catch(e){} }, 2200);
                } else {
                    // If original message not present, set pendingJumpTo and reload messages
                    pendingJumpTo = id;
                    try { loadMessages(currentChatUser, true); } catch(e){}
                }
                ev.preventDefault();
            } catch(e) { /* ignore */ }
        });

        // Listen for reaction events via Echo and update UI
        try {
            if (window.Echo && currentUserId) {
                Echo.private(`chat.user.${currentUserType}.${currentUserId}`)
                    .listen('.ChatMessageReacted', (e) => {
                        try {
                            if (!e || !e.message_id || !e.emoji) return;
                            upsertReactionUI(e.message_id, e.emoji, e);
                        } catch (ex) { console.warn('Echo reaction update error', ex); }
                    });
            }
        } catch (err) { /* ignore */ }
    })();

    // Reformat sidebar times using client timezone so Today/Yesterday matches chat body
    function formatSidebarTimeGlobal(raw) {
        if (!raw && raw !== 0) return '';
        if (typeof raw === 'string' && /^\d{1,2}:\d{2}\s*(AM|PM)$/i.test(raw.trim())) return raw.trim();
        // parse MySQL or ISO
        let s = String(raw || '').trim();
        if (!s) return '';
        if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$/.test(s)) s = s.replace(' ', 'T');
        const d = new Date(s);
        if (isNaN(d)) return s;
        // Always display time in sidebar
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    document.addEventListener('DOMContentLoaded', () => {
        try {
            document.querySelectorAll('.chat-user-time .time[data-last-message-at]').forEach(el => {
                const raw = el.getAttribute('data-last-message-at');
                const formatted = formatSidebarTimeGlobal(raw);
                if (formatted) el.innerText = formatted;
            });
        } catch (e) { /* ignore */ }
    });

    // Microphone recording + send
    (function(){
        const micBtn = document.getElementById('micBtn');
        const recordIndicator = document.getElementById('recordIndicator');
        const recordTimer = document.getElementById('recordTimer');
        if (!micBtn) return;

        let mediaRecorder = null;
        let audioChunks = [];
        let recording = false;
        let timerInterval = null;
        let startTs = null;

        function setRecordingState(on){
            recording = !!on;
            if (recording) {
                micBtn.classList.add('recording');
                micBtn.title = 'Stop recording';
                if (recordIndicator) recordIndicator.style.display = 'inline-block';
                startTs = Date.now();
                if (timerInterval) clearInterval(timerInterval);
                timerInterval = setInterval(updateTimer, 500);
            } else {
                micBtn.classList.remove('recording');
                micBtn.title = 'Record audio';
                if (recordIndicator) recordIndicator.style.display = 'none';
                if (timerInterval) clearInterval(timerInterval);
                if (recordTimer) recordTimer.innerText = '0:00';
                startTs = null;
            }
        }

        function updateTimer(){
            if (!startTs || !recordTimer) return;
            const diff = Math.floor((Date.now() - startTs)/1000);
            const mm = Math.floor(diff/60);
            const ss = diff % 60;
            recordTimer.innerText = `${mm}:${String(ss).padStart(2,'0')}`;
        }

        async function startRecording(){
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Media devices not supported in this browser');
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];
                mediaRecorder.addEventListener('dataavailable', e => { if (e.data && e.data.size) audioChunks.push(e.data); });
                mediaRecorder.addEventListener('stop', async () => {
                    // stop all tracks
                    stream.getTracks().forEach(t => t.stop());
                    setRecordingState(false);
                    const blob = new Blob(audioChunks, { type: 'audio/webm' });
                    audioChunks = [];
                    await sendAudioMessage(blob);
                });
                mediaRecorder.start();
                setRecordingState(true);
            } catch (err) {
                console.error('Could not start microphone:', err);
                alert('Could not access microphone: ' + (err.message || err));
            }
        }

        function stopRecording(){
            if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
            else setRecordingState(false);
        }

        async function sendAudioMessage(blob){
            if (!currentChatUser) {
                alert('Select a contact first');
                return;
            }

            const form = new FormData();
            form.append('receiver_id', currentChatUser);
            form.append('content', '');
            form.append('audio', blob, 'voice-message.webm');

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            try {
                const res = await fetch('/chat/messages', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token || '',
                        'Accept': 'application/json'
                    },
                    body: form
                });

                if (res.ok) {
                    loadMessages(currentChatUser, true);
                } else if (res.status === 422) {
                    const body = await res.json().catch(() => null);
                    let msg = 'Validation failed';
                    if (body && body.errors) {
                        msg = Object.values(body.errors).flat().join('\n');
                    } else if (body && body.message) {
                        msg = body.message;
                    }
                    alert('Failed to send audio (HTTP 422)\n' + msg);
                } else {
                    console.error('Send failed', res.status);
                    alert('Failed to send audio (HTTP ' + res.status + ')');
                }
            } catch (ex) { console.error('Send audio error', ex); alert('Error sending audio: ' + (ex.message||ex)); }
        }

        micBtn.addEventListener('click', function(e){
            e.preventDefault();
            if (!recording) startRecording(); else stopRecording();
        });
    })();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH A:\GenTech\htdocs\GenlabV3.0\GenLabV3.0\resources\views/chat.blade.php ENDPATH**/ ?>