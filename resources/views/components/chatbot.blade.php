<!-- Chatbot Floating Icon and Popup -->
<div id="chatbot-icon" title="Chat with Assistant">
    <i class="fas fa-comment-dots" style="font-size: 26px;"></i>
</div>
<div id="chatbot-popup" role="dialog" aria-modal="true" aria-labelledby="chatbot-title">
    <div id="chatbot-header">
        <div class="cb-header-left">
            <div class="chatbot-avatar" aria-hidden="true">VA</div>
            <div class="cb-titles">
                <div id="chatbot-title" class="chatbot-header-title">Virtual Assistant</div>
                <div class="chatbot-header-sub"><span class="chatbot-status-dot" aria-hidden="true"></span> Online</div>
            </div>
        </div>
        <button id="chatbot-close" aria-label="Close" title="Close"><i class="fas fa-times"></i></button>
    </div>
    <div id="chatbot-suggestions" class="chatbot-suggestions">
        <button type="button" class="chatbot-suggestion">How many bookings made today?</button>
        <button type="button" class="chatbot-suggestion">Price of booking?</button>
        <button type="button" class="chatbot-suggestion">How to contact support?</button>
    </div>
    <div id="chatbot-messages" aria-live="polite" aria-relevant="additions"></div>
    <div id="chatbot-typing" class="chatbot-typing" aria-hidden="true" style="display:none;">
        <span></span><span></span><span></span>
    </div>
    <form id="chatbot-input-area" autocomplete="off">
        <input type="text" id="chatbot-input" placeholder="Type your question..." required />
        <button type="submit" id="chatbot-send" aria-label="Send"><i class="fas fa-paper-plane"></i></button>
    </form>
</div>
