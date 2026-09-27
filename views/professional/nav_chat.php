<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?><div class="alert alert-success alert-dismissible fade show" role="status"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
    <?php if (!empty($flashError)): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-12 col-lg-5">
            <section class="card-custom p-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-compass-fill text-danger me-2"></i>Booking destination</h5>
                <div class="p-3 border rounded-3 bg-light mb-3">
                    <div class="fw-bold text-dark"><?= htmlspecialchars($chatBooking['customer_name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($chatBooking['address_line1'], ENT_QUOTES, 'UTF-8') ?><?= !empty($chatBooking['address_line2']) ? ', ' . htmlspecialchars($chatBooking['address_line2'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($chatBooking['city'], ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($chatBooking['state'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($chatBooking['postal_code'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if (!empty($chatBooking['landmark'])): ?><div class="text-muted small mt-1">Landmark: <?= htmlspecialchars($chatBooking['landmark'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    <?php if (!empty($chatBooking['latitude']) && !empty($chatBooking['longitude'])): ?>
                        <a href="https://maps.google.com/?q=<?= rawurlencode($chatBooking['latitude'] . ',' . $chatBooking['longitude']) ?>" target="_blank" rel="noopener" class="btn btn-dark btn-sm rounded-pill mt-3 w-100"><i class="bi bi-arrow-up-right-circle me-1"></i>Open route in Google Maps</a>
                    <?php else: ?>
                        <a href="https://maps.google.com/?q=<?= rawurlencode($chatBooking['address_line1'] . ', ' . $chatBooking['city'] . ', ' . $chatBooking['postal_code']) ?>" target="_blank" rel="noopener" class="btn btn-dark btn-sm rounded-pill mt-3 w-100"><i class="bi bi-arrow-up-right-circle me-1"></i>Search destination in Maps</a>
                    <?php endif; ?>
                </div>
                <div id="proChatCompletionStatus" class="alert alert-info mb-0" data-booking-id="<?= (int)$bookingId ?>" data-booking-code="<?= htmlspecialchars($chatBooking['booking_code'], ENT_QUOTES, 'UTF-8') ?>">
                    <strong>Booking #<?= htmlspecialchars($chatBooking['booking_code'], ENT_QUOTES, 'UTF-8') ?></strong> · <span data-pro-chat-booking-status><?= strtoupper(str_replace('_', ' ', $chatBooking['status'])) ?></span><br>
                    Payment: <span data-pro-chat-payment><?= strtoupper(htmlspecialchars($chatBooking['payment_status'], ENT_QUOTES, 'UTF-8')) ?> (<?= strtoupper(htmlspecialchars($chatBooking['payment_method'], ENT_QUOTES, 'UTF-8')) ?>)</span> · After-proof: <span data-pro-chat-proof><?= htmlspecialchars(strtoupper($chatBooking['after_proof_status'] ?? 'NOT SUBMITTED'), ENT_QUOTES, 'UTF-8') ?></span>
                    <div class="small mt-1" data-pro-chat-completion>Customer confirmation updates here; the code itself is only entered in Jobs &amp; Completion.</div>
                    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Open Jobs &amp; Completion</a>
                    <?php if ($chatBooking['status'] === 'in_progress' && !empty($chatBooking['after_proof_status']) && $chatBooking['after_proof_status'] !== 'rejected'): ?>
                        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment/request-completion-otp" method="POST" class="mt-2">
                            <input type="hidden" name="booking_id" value="<?= (int)$bookingId ?>">
                            <input type="hidden" name="return_to" value="chat">
                            <button type="submit" class="btn btn-sm btn-primary rounded-pill"><i class="bi bi-send-check me-1"></i>Send / resend completion code</button>
                        </form>
                    <?php elseif ($chatBooking['status'] === 'in_progress'): ?>
                        <div class="small text-muted mt-2">Upload an after-work proof from Jobs &amp; Completion before requesting the code.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="col-12 col-lg-7">
            <section class="card-custom p-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Chat with <?= htmlspecialchars($chatBooking['customer_name'], ENT_QUOTES, 'UTF-8') ?></h5>
                <div id="proBookingChatMessages" class="p-3 border rounded-3 bg-light mb-3" style="height:350px;overflow-y:auto" aria-live="polite"><div class="text-center py-5 text-muted small">Loading booking conversation…</div></div>
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/nav-chat/send" method="POST" class="d-flex gap-2">
                    <input type="hidden" name="booking_id" value="<?= (int)$bookingId ?>">
                    <input type="text" name="message" class="form-control rounded-pill" maxlength="4000" placeholder="Reply to customer…" required>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" aria-label="Send message"><i class="bi bi-send-fill"></i></button>
                </form>
            </section>
        </div>
    </div>
</div>
<script>
(function () {
    const bookingId = <?= (int)$bookingId ?>;
    const baseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const box = document.getElementById('proBookingChatMessages');
    const statusPanel = document.getElementById('proChatCompletionStatus');
    async function refresh() {
        try {
            const response = await fetch(`${baseUrl}/pro/nav-chat/messages?booking_id=${bookingId}`, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 40;
            box.replaceChildren();
            if (!result.messages.length) {
                const empty = document.createElement('div');
                empty.className = 'text-center py-5 text-muted small';
                empty.textContent = 'No messages yet. Send a message to your customer.';
                box.appendChild(empty);
                return;
            }
            result.messages.forEach(function (message) {
                const own = Number(message.sender_id) === <?= \App\Core\Auth::id() ?>;
                const row = document.createElement('div');
                row.className = `mb-3 ${own ? 'text-end' : 'text-start'}`;
                const bubble = document.createElement('div');
                bubble.className = `d-inline-block p-3 rounded-4 small ${own ? 'bg-primary text-white' : 'bg-white text-dark shadow-sm'}`;
                bubble.style.maxWidth = '80%';
                const sender = document.createElement('strong');
                sender.className = 'd-block small mb-1';
                sender.textContent = own ? 'You' : message.sender_name;
                const text = document.createElement('div');
                text.style.whiteSpace = 'pre-wrap';
                text.textContent = message.message;
                bubble.append(sender, text);
                row.appendChild(bubble);
                box.appendChild(row);
            });
            if (atBottom) box.scrollTop = box.scrollHeight;
        } catch (error) { /* keep the conversation visible during brief network interruptions */ }
    }
    async function refreshCompletionStatus() {
        try {
            const response = await fetch(`${baseUrl}/pro/nav-chat/status?booking_id=${bookingId}`, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const booking = result.booking;
            statusPanel.querySelector('[data-pro-chat-booking-status]').textContent = String(booking.status).replaceAll('_', ' ').toUpperCase();
            statusPanel.querySelector('[data-pro-chat-payment]').textContent = `${String(booking.payment_status).toUpperCase()} (${String(booking.payment_method).toUpperCase()})`;
            statusPanel.querySelector('[data-pro-chat-proof]').textContent = String(booking.after_proof_status || 'NOT SUBMITTED').replaceAll('_', ' ').toUpperCase();
            const settlementReady = booking.after_proof_status === 'approved'
                && (booking.payment_method === 'cod' || booking.payment_status === 'paid');
            statusPanel.querySelector('[data-pro-chat-completion]').textContent = booking.completion_otp_verified
                ? (settlementReady
                    ? 'Customer code verified. Completion and wallet credit are reflected in Jobs & Completion.'
                    : 'Customer code verified. Waiting for proof approval or payment before earnings are released.')
                : (booking.completion_otp_expires_at && new Date(booking.completion_otp_expires_at.replace(' ', 'T')) > new Date()
                    ? `Customer code sent and active until ${new Date(booking.completion_otp_expires_at.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}. Ask them to read it to you. The code is not shown in this panel.`
                    : 'Waiting for customer confirmation. The 4-digit code is never shown in chat or to administration.');
        } catch (error) { /* keep the current status visible */ }
    }
    refresh();
    refreshCompletionStatus();
    window.setInterval(refresh, 4000);
    window.setInterval(refreshCompletionStatus, 5000);
}());
</script>
