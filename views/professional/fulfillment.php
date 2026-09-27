<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger border-0 rounded-pill px-4"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <h4 class="fw-bold mb-1"><i class="bi bi-card-checklist text-primary me-2"></i>Jobs &amp; Completion Tracking</h4>
    <p class="text-muted small mb-4">Customer codes, proof approval, payment, and wallet credit refresh automatically while this page is open.</p>

    <div class="row g-4">
        <?php if (empty($jobs)): ?>
            <div class="col-12 text-center py-5 text-muted">No jobs currently under execution.</div>
        <?php else: ?>
            <?php foreach ($jobs as $j): ?>
                <div class="col-12 col-lg-6">
                        <?php
                            $afterProofStatus = (string)($j['after_proof_status'] ?? '');
                            if ($j['status'] !== 'in_progress') {
                                $completionState = 'job_' . $j['status'];
                            } elseif ($afterProofStatus === '' || $afterProofStatus === 'rejected') {
                                $completionState = 'proof_needed';
                            } elseif (!empty($j['completion_otp_verified_at'])) {
                                $completionState = $j['payment_method'] === 'online' && $j['payment_status'] !== 'paid' ? 'awaiting_payment' : 'awaiting_approval';
                            } elseif (!empty($j['completion_otp_active'])) {
                                $completionState = 'code_ready';
                            } else {
                                $completionState = 'code_needed';
                            }
                        ?>
                        <div class="card-custom p-4 h-100" data-job-booking="<?= (int)$j['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <span class="badge bg-primary rounded-pill">#<?= htmlspecialchars($j['booking_code']) ?></span>
                                <span class="badge bg-dark rounded-pill" data-job-status><?= strtoupper(str_replace('_', ' ', $j['status'])) ?></span>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($j['customer_name']) ?></h5>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i><?= htmlspecialchars($j['address_line1']) ?>, <?= htmlspecialchars($j['city']) ?></p>
                        
                        <div class="d-flex justify-content-between border-top pt-3 mt-3">
                            <span class="fw-bold fs-5 text-success">Net Earning: ₹<?= number_format((float)$j['pro_earning'], 2) ?></span>
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/nav-chat?booking_id=<?= $j['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-chat-dots"></i> Chat</a>
                                    <div class="d-grid gap-2 w-100 mt-2" data-completion-actions data-booking-id="<?= (int)$j['id'] ?>" data-state="<?= htmlspecialchars($completionState, ENT_QUOTES, 'UTF-8') ?>" data-otp-expiry="<?= htmlspecialchars((string)($j['completion_otp_expires_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <?php if ($completionState === 'job_completed'): ?>
                                                <div class="small fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i>Completed · Customer confirmed</div>
                                                <div class="small text-muted">Payment: <?= htmlspecialchars(strtoupper((string)$j['payment_status']), ENT_QUOTES, 'UTF-8') ?> · <?= !empty($j['pro_earning_credited']) ? '₹' . number_format((float)$j['pro_earning'], 2) . ' credited to your wallet' : 'Wallet credit pending' ?></div>
                                            <?php elseif ($completionState === 'job_assigned' || $completionState === 'job_accepted'): ?>
                                                <div class="small text-muted"><?= $completionState === 'job_assigned' ? 'Accept the job to begin.' : 'Start the job when you arrive.' ?></div>
                                            <?php else: ?>
                                    <?php if ($completionState === 'proof_needed'): ?>
                                        <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/proof-of-work?booking_id=<?= (int)$j['id'] ?>" class="btn btn-sm btn-success rounded-pill px-3"><i class="bi bi-camera-fill me-1"></i>Submit after-proof</a>
                                    <?php elseif ($completionState === 'code_needed'): ?>
                                        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment/request-completion-otp" method="POST">
                                            <input type="hidden" name="booking_id" value="<?= (int)$j['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-send me-1"></i>Send / resend customer code</button>
                                        </form>
                                    <?php elseif ($completionState === 'code_ready'): ?>
                                        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment/verify-completion-otp" method="POST" class="d-flex flex-column gap-2">
                                            <input type="hidden" name="booking_id" value="<?= (int)$j['id'] ?>">
                                            <label class="small fw-semibold" for="completion-code-<?= (int)$j['id'] ?>">Enter customer's 4-digit code</label>
                                            <input id="completion-code-<?= (int)$j['id'] ?>" name="completion_code" class="form-control form-control-sm" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="one-time-code" required>
                                            <?php if (($j['payment_method'] ?? '') === 'cod' && ($j['payment_status'] ?? '') !== 'paid'): ?>
                                                <div class="form-check"><input class="form-check-input" type="checkbox" name="cod_received" id="cod-received-<?= (int)$j['id'] ?>" value="1" required><label class="form-check-label small" for="cod-received-<?= (int)$j['id'] ?>">I received the full cash payment of ₹<?= number_format((float)$j['total_amount'], 2) ?></label></div>
                                            <?php endif; ?>
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3"><i class="bi bi-shield-check me-1"></i>Verify &amp; finish job</button>
                                            <span class="small text-muted">Code expires <?= date('h:i A', strtotime((string)$j['completion_otp_expires_at'])) ?></span>
                                        </form>
                                    <?php else: ?>
                                        <div class="small fw-semibold text-success" data-completion-message><i class="bi bi-check-circle-fill me-1"></i>Customer confirmed · <?= $completionState === 'awaiting_payment' ? 'awaiting online payment' : 'awaiting administrator proof approval' ?></div>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    </div>
                                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/fulfillment/update-status" method="POST" class="d-inline">
                                    <input type="hidden" name="booking_id" value="<?= $j['id'] ?>">
                                    <?php if ($j['status'] === 'assigned'): ?>
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3">Accept Job</button>
                                    <?php elseif ($j['status'] === 'accepted'): ?>
                                        <input type="hidden" name="status" value="in_progress">
                                        <button type="submit" class="btn btn-sm btn-info rounded-pill px-3">Start Work</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    const updatesUrl = <?= json_encode(($baseUrl ?? '') . '/pro/fulfillment/updates', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const baseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

    function stateFor(job) {
        if (job.status !== 'in_progress') return `job_${job.status}`;
        if (!job.after_proof_status || job.after_proof_status === 'rejected') return 'proof_needed';
        if (job.completion_otp_verified_at) {
            return job.payment_method === 'online' && job.payment_status !== 'paid' ? 'awaiting_payment' : 'awaiting_approval';
        }
        return Number(job.completion_otp_active) === 1
            ? 'code_ready'
            : 'code_needed';
    }

    function updateActionSlot(slot, job, state) {
        const currentExpiry = String(job.completion_otp_expires_at || '');
        if (slot.dataset.state === state && (state !== 'code_ready' || slot.dataset.otpExpiry === currentExpiry)) return;
        slot.dataset.state = state;
        slot.dataset.otpExpiry = currentExpiry;
        const bookingId = Number(job.id);
        if (state === 'proof_needed') {
            slot.innerHTML = `<a href="${baseUrl}/pro/proof-of-work?booking_id=${bookingId}" class="btn btn-sm btn-success rounded-pill px-3"><i class="bi bi-camera-fill me-1"></i>Submit after-proof</a>`;
        } else if (state === 'code_needed') {
            slot.innerHTML = `<form action="${baseUrl}/pro/fulfillment/request-completion-otp" method="POST"><input type="hidden" name="booking_id" value="${bookingId}"><button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="bi bi-send me-1"></i>Send / resend customer code</button></form>`;
        } else if (state === 'code_ready') {
            const expiry = new Date(job.completion_otp_expires_at.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const cashCheck = job.payment_method === 'cod' && job.payment_status !== 'paid'
                ? `<div class="form-check"><input class="form-check-input" type="checkbox" name="cod_received" id="cod-received-${bookingId}" value="1" required><label class="form-check-label small" for="cod-received-${bookingId}">I received the full cash payment of ₹${Number(job.total_amount).toFixed(2)}</label></div>`
                : '';
            slot.innerHTML = `<form action="${baseUrl}/pro/fulfillment/verify-completion-otp" method="POST" class="d-flex flex-column gap-2"><input type="hidden" name="booking_id" value="${bookingId}"><label class="small fw-semibold" for="completion-code-${bookingId}">Enter customer's 4-digit code</label><input id="completion-code-${bookingId}" name="completion_code" class="form-control form-control-sm" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="one-time-code" required>${cashCheck}<button type="submit" class="btn btn-sm btn-primary rounded-pill px-3"><i class="bi bi-shield-check me-1"></i>Verify &amp; finish job</button><span class="small text-muted">Code expires ${expiry}</span></form>`;
        } else if (state === 'awaiting_payment' || state === 'awaiting_approval') {
            const status = state === 'awaiting_payment' ? 'awaiting online payment' : 'awaiting administrator proof approval';
            slot.innerHTML = `<div class="small fw-semibold text-success" data-completion-message><i class="bi bi-check-circle-fill me-1"></i>Customer confirmed · ${status}</div>`;
        } else if (state === 'job_completed') {
            slot.innerHTML = `<div class="small fw-semibold text-success"><i class="bi bi-check-circle-fill me-1"></i>Completed · Customer confirmed</div><div class="small text-muted">Payment: ${String(job.payment_status).toUpperCase()} · ${Number(job.pro_earning_credited) ? `₹${Number(job.pro_earning).toFixed(2)} credited to your wallet` : 'Wallet credit pending'}</div>`;
        } else if (state === 'job_assigned' || state === 'job_accepted') {
            slot.textContent = state === 'job_assigned' ? 'Accept the job to begin.' : 'Start the job when you arrive.';
        }
    }

    async function refreshFulfillmentStatuses() {
        try {
            const response = await fetch(updatesUrl, { headers: { 'Accept': 'application/json' } });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const jobsById = new Map(result.jobs.map(job => [String(job.id), job]));
            document.querySelectorAll('[data-job-booking]').forEach(function (card) {
                const id = card.dataset.jobBooking;
                const job = jobsById.get(id);
                if (!job) {
                    card.remove();
                    return;
                }
                const badge = card.querySelector('[data-job-status]');
                if (badge) badge.textContent = job.status.replaceAll('_', ' ').toUpperCase();
                const slot = card.querySelector('[data-completion-actions]');
                if (slot) updateActionSlot(slot, job, stateFor(job));
            });
        } catch (error) {
            // Keep the controls and forms usable if polling temporarily fails.
        }
    }
    window.setInterval(refreshFulfillmentStatuses, 5000);
}());
</script>