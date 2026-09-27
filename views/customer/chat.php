<div class="container py-3">
    <div class="card-custom p-4 mx-auto" style="max-width: 700px;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
            <div><h5 class="fw-bold m-0"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Job Discussion &amp; Direct Chat</h5><div class="small text-muted">Chatting with <?= htmlspecialchars($chatBooking['professional_name'] ?? 'your technician', ENT_QUOTES, 'UTF-8') ?></div></div>
            <span class="badge bg-light text-dark border">#<?= htmlspecialchars($chatBooking['booking_code'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <div id="chatCompletionStatus" class="alert alert-info" data-booking-id="<?= (int)$bookingId ?>" data-progress-url="<?= htmlspecialchars(($baseUrl ?? '') . '/customer/bookings/completion-code', ENT_QUOTES, 'UTF-8') ?>" hidden></div>

        <div id="bookingChatMessages" class="p-3 border rounded-3 bg-light mb-3" style="height: 350px; overflow-y: auto;" aria-live="polite">
            <div class="text-center py-5 text-muted small">Loading booking conversation…</div>
        </div>

        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/chat/send" method="POST" class="d-flex gap-2">
            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
            <input type="text" name="message" class="form-control rounded-pill" maxlength="4000" placeholder="Type instructions or questions..." required>
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-send-fill"></i></button>
        </form>
    </div>
</div>
<script>
(function () {
    const bookingId = <?= (int)$bookingId ?>;
    const baseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const messagesBox = document.getElementById('bookingChatMessages');
    const statusBox = document.getElementById('chatCompletionStatus');
    let lastMessageId = 0;

    async function refreshChat() {
        try {
            const response = await fetch(`${baseUrl}/customer/chat/messages?booking_id=${bookingId}`, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const atBottom = messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight < 40;
            messagesBox.replaceChildren();
            if (!result.messages.length) {
                const empty = document.createElement('div');
                empty.className = 'text-center py-5 text-muted small';
                empty.textContent = 'No messages yet. Send a message to your assigned technician.';
                messagesBox.appendChild(empty);
                return;
            }
            result.messages.forEach(function (message) {
                const own = Number(message.sender_id) === <?= \App\Core\Auth::id() ?>;
                const row = document.createElement('div');
                row.className = `mb-3 ${own ? 'text-end' : 'text-start'}`;
                const bubble = document.createElement('div');
                bubble.className = `d-inline-block p-3 rounded-4 small ${own ? 'bg-primary text-white' : 'bg-white text-dark shadow-sm'}`;
                bubble.style.maxWidth = '75%';
                const sender = document.createElement('div');
                sender.className = 'fw-bold';
                sender.style.fontSize = '.72rem';
                sender.textContent = own ? 'You' : message.sender_name;
                const text = document.createElement('div');
                text.style.whiteSpace = 'pre-wrap';
                text.textContent = message.message;
                bubble.append(sender, text);
                row.appendChild(bubble);
                messagesBox.appendChild(row);
                lastMessageId = Math.max(lastMessageId, Number(message.id));
            });
            if (atBottom) messagesBox.scrollTop = messagesBox.scrollHeight;
        } catch (error) { /* retain last conversation during network interruptions */ }
    }

    async function refreshCompletionStatus() {
        try {
            const response = await fetch(`${statusBox.dataset.progressUrl}?booking_id=${bookingId}`, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const progress = result.progress || {};
            statusBox.replaceChildren();
            if (result.active) {
                statusBox.className = 'alert alert-info d-flex align-items-center justify-content-between gap-3';
                const message = document.createElement('div');
                message.innerHTML = '<strong>Customer completion code</strong><div class="small">This code is also on My bookings. Share it only with your technician after work is complete.</div>';
                const code = document.createElement('strong');
                code.className = 'fs-2 letter-spacing-otp';
                code.textContent = result.code;
                statusBox.append(message, code);
                statusBox.hidden = false;
            } else if (progress.status === 'completed') {
                statusBox.className = 'alert alert-success';
                statusBox.textContent = 'Completion confirmed. Your bill is ready in My bookings.';
                statusBox.hidden = false;
            } else if (progress.completion_otp_verified_at && progress.after_proof_status !== 'approved') {
                statusBox.className = 'alert alert-success';
                statusBox.textContent = 'Your completion code was confirmed. Fixmate administration is reviewing the after-work proof.';
                statusBox.hidden = false;
            } else if (progress.status === 'in_progress') {
                statusBox.className = 'alert alert-secondary';
                statusBox.textContent = 'The technician is working. A completion code will appear here when requested.';
                statusBox.hidden = false;
            } else {
                statusBox.hidden = true;
            }
        } catch (error) { /* keep previous completion status */ }
    }
    refreshChat();
    refreshCompletionStatus();
    window.setInterval(refreshChat, 4000);
    window.setInterval(refreshCompletionStatus, 5000);
}());
</script>