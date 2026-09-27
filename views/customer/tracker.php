<div class="container py-3">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger border-0 rounded-pill px-4"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <h4 id="billing" class="fw-bold mb-2">My Bookings &amp; Tracker</h4>
    <p class="text-muted mb-4">View each booking's itemized charges and download a PDF cost summary.</p>

    <div class="row g-4">
        <?php if (empty($bookings)): ?>
            <div class="col-12 text-center py-5 text-muted">No active or past bookings found. <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer">Book a service now!</a></div>
        <?php else: ?>
            <?php foreach ($bookings as $b): ?>
                <div class="col-12 col-lg-6">
                    <div class="card-custom p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                            <div>
                                <h6 class="fw-bold m-0 text-primary">#<?= htmlspecialchars($b['booking_code']) ?></h6>
                                <span class="text-muted small">Date: <?= date('d M Y', strtotime($b['scheduled_date'])) ?> (<?= htmlspecialchars($b['scheduled_time_slot']) ?>)</span>
                            </div>
                            <span class="badge bg-primary rounded-pill" data-booking-status><?= strtoupper(str_replace('_', ' ', $b['status'])) ?></span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($b['pro_name'] ?: 'Technician Assignment in Progress') ?></div>
                                <div class="text-muted small"><?= $b['pro_phone'] ? '<i class="bi bi-telephone"></i> ' . $b['pro_phone'] : 'Will be notified via SMS' ?></div>
                            </div>
                            <div class="fw-bold fs-5 text-success">₹<?= number_format((float)$b['total_amount'], 2) ?></div>
                        </div>

                        <div class="small text-muted mb-2" data-payment-status>Payment: <?= htmlspecialchars(strtoupper($b['payment_status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></div>
                        <div id="completionOtp-<?= (int)$b['id'] ?>" class="alert alert-info align-items-center gap-3" role="status" data-completion-booking="<?= (int)$b['id'] ?>" hidden></div>

                        <div class="d-flex flex-wrap gap-2 border-top pt-3">
                            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/bookings/invoice?booking_id=<?= (int)$b['id'] ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-download me-1"></i>Download bill (PDF)
                            </a>
                            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/chat?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                <i class="bi bi-chat-dots"></i> Chat
                            </a>
                            <?php if (($b['payment_method'] ?? '') === 'online' && ($b['payment_status'] ?? '') === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="retryBookingPayment('<?= htmlspecialchars($b['booking_code'], ENT_QUOTES, 'UTF-8') ?>', this)"><i class="bi bi-credit-card me-1"></i>Pay online</button>
                            <?php endif; ?>
                            <?php if (in_array($b['status'], ['pending', 'assigned', 'accepted'])): ?>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="openReschedule(<?= $b['id'] ?>)">Reschedule</button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="openCancel(<?= $b['id'] ?>)">Cancel</button>
                            <?php elseif ($b['status'] === 'completed' && empty($b['review_id'])): ?>
                                <button class="btn btn-sm btn-success rounded-pill px-3" onclick="openReview(<?= $b['id'] ?>, <?= $b['professional_id'] ?>)">
                                    <i class="bi bi-star"></i> Rate & Review
                                </button>
                            <?php elseif (!empty($b['review_id'])): ?>
                                <span class="badge rounded-pill text-bg-warning px-3 py-2" title="Your submitted rating">
                                    <i class="bi bi-star-fill me-1"></i> You rated <?= (int)$b['review_rating'] ?>/5
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($b['review_id']) && !empty($b['review_comment'])): ?>
                            <div class="small text-muted mt-2" aria-label="Your review">
                                “<?= htmlspecialchars($b['review_comment'], ENT_QUOTES, 'UTF-8') ?>”
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Reschedule -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/bookings/reschedule" method="POST" class="modal-content card-custom border-0">
            <input type="hidden" name="booking_id" id="resched_bid">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Reschedule Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">New Date</label>
                    <input type="date" name="scheduled_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">New Slot</label>
                    <select name="scheduled_time_slot" class="form-select">
                        <option value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</option>
                        <option value="02:00 PM - 04:00 PM">02:00 PM - 04:00 PM</option>
                        <option value="04:00 PM - 06:00 PM">04:00 PM - 06:00 PM</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="submit" class="btn btn-primary rounded-pill px-4">Confirm Reschedule</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Cancel -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/bookings/cancel" method="POST" class="modal-content card-custom border-0">
            <input type="hidden" name="booking_id" id="cancel_bid">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Cancel Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label small fw-bold">Reason for Cancellation</label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="Specify why you are cancelling..."></textarea>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="submit" class="btn btn-danger rounded-pill px-4">Cancel Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Review -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/bookings/submit-review" method="POST" class="modal-content card-custom border-0">
            <input type="hidden" name="booking_id" id="rev_bid">
            <input type="hidden" name="pro_id" id="rev_pro_id">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Rate Your Experience</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Star Rating</label>
                    <select name="rating" class="form-select">
                        <option value="5">★★★★★ (5 - Excellent)</option>
                        <option value="4">★★★★☆ (4 - Good)</option>
                        <option value="3">★★★☆☆ (3 - Average)</option>
                        <option value="2">★★☆☆☆ (2 - Poor)</option>
                        <option value="1">★☆☆☆☆ (1 - Terrible)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Review Feedback</label>
                    <textarea name="comment" class="form-control" rows="3" placeholder="Tell us how the technician performed..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="submit" class="btn btn-success rounded-pill px-4">Publish Review</button>
            </div>
        </form>
    </div>
</div>

<script>
function openReschedule(bid) {
    document.getElementById('resched_bid').value = bid;
    new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
}
function openCancel(bid) {
    document.getElementById('cancel_bid').value = bid;
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}
function openReview(bid, proId) {
    document.getElementById('rev_bid').value = bid;
    document.getElementById('rev_pro_id').value = proId;
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}
function retryBookingPayment(bookingCode, button) {
    const originalLabel = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Opening secure checkout';
    window.startFixmatePayment(bookingCode, function (message) {
        window.alert(message);
        button.disabled = false;
        button.innerHTML = originalLabel;
    });
}

const completionCodeUrl = <?= json_encode(($baseUrl ?? '') . '/customer/bookings/completion-code', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
async function refreshCustomerCompletionCodes() {
    const panels = document.querySelectorAll('[data-completion-booking]');
    await Promise.all(Array.from(panels, async function (panel) {
        try {
            const response = await fetch(`${completionCodeUrl}?booking_id=${encodeURIComponent(panel.dataset.completionBooking)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            const progress = result.progress || {};
            const card = panel.closest('.card-custom');
            const statusBadge = card && card.querySelector('[data-booking-status]');
            if (statusBadge && progress.status) statusBadge.textContent = progress.status.replaceAll('_', ' ').toUpperCase();
            const paymentStatus = card && card.querySelector('[data-payment-status]');
            if (paymentStatus && progress.payment_status) paymentStatus.textContent = `Payment: ${progress.payment_status.toUpperCase()} · ${String(progress.payment_method || '').toUpperCase()}`;
            panel.className = 'alert d-flex align-items-center gap-3';
            if (progress.status === 'completed') {
                panel.classList.add('alert-success');
                panel.textContent = 'Completion confirmed. Your bill is available below; technician earnings have been released.';
                panel.hidden = false;
                return;
            }
            if (!result.active) {
                panel.classList.add('alert-info');
                const message = progress.status !== 'in_progress'
                    ? `Booking status: ${String(progress.status || '').replaceAll('_', ' ')}. Your bill is available below.`
                    : (progress.payment_method === 'online' && progress.payment_status !== 'paid' && progress.after_proof_status === 'approved' && progress.completion_otp_verified_at
                        ? 'Your code is confirmed. Completion is waiting for online payment.'
                        : (progress.completion_otp_verified_at
                            ? 'Your code is confirmed. Completion is waiting for administrator proof approval.'
                            : (progress.status === 'in_progress' ? 'The technician is working. Your completion code will appear here when requested.' : '')));
                if (!message) {
                    panel.hidden = true;
                    return;
                }
                panel.textContent = message;
                panel.hidden = false;
                return;
            }
            const icon = document.createElement('i');
            icon.className = 'bi bi-shield-lock-fill fs-4';
            icon.setAttribute('aria-hidden', 'true');
            const content = document.createElement('div');
            const heading = document.createElement('strong');
            heading.textContent = 'Share this completion code with your technician';
            const expiry = document.createElement('div');
            expiry.className = 'small';
            expiry.textContent = `Expires at ${new Date(result.expires_at.replace(' ', 'T')).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}. Share it only after the work is complete.`;
            const code = document.createElement('div');
            code.className = 'display-6 fw-bold letter-spacing-otp';
            code.setAttribute('aria-label', 'Your four-digit completion code');
            code.textContent = result.code;
            content.replaceChildren(heading, expiry, code);
            panel.replaceChildren(icon, content);
            panel.hidden = false;
        } catch (error) {
            // Keep the tracker usable if the network is briefly unavailable.
        }
    }));
}
refreshCustomerCompletionCodes();
window.setInterval(refreshCustomerCompletionCodes, 12000);
</script>