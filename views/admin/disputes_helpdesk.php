<div class="container-fluid p-0">

    <!-- Flash Alerts -->
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Title & Overview -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Reviews, Disputes & Helpdesk Center</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Customer grievance arbitration, review moderation, and conflict resolutions.</p>
        </div>
    </div>

    <!-- Navigation Pills Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="helpdeskTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active px-4 py-2 rounded-pill fw-semibold" data-bs-toggle="pill" data-bs-target="#disputes-tab-pane" type="button">
                <i class="bi bi-shield-exclamation me-2"></i>Customer Disputes & Arbitration (<?= count($disputes) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link px-4 py-2 rounded-pill fw-semibold" data-bs-toggle="pill" data-bs-target="#reviews-tab-pane" type="button">
                <i class="bi bi-star-half me-2"></i>Customer Reviews & Ratings (<?= count($reviews) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- TAB 1: DISPUTES ARBITRATION -->
        <div class="tab-pane fade show active" id="disputes-tab-pane">
            <div id="approval-workflow" class="card-custom p-4 mb-4" style="scroll-margin-top: 1rem;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Customer / Pro Approval Workflow</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Live shared status · Approve appears for pending proof, Complete when all checks pass, and Cancel for eligible bookings.</span>
                    </div>
                    <span class="badge bg-primary rounded-pill" id="workflowCount">Loading…</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.84rem;">
                        <thead class="table-light text-muted" style="font-size: 0.72rem; text-transform: uppercase;">
                            <tr>
                                <th class="border-0">Booking / Parties</th>
                                <th class="border-0">Pro & Customer Confirmation</th>
                                <th class="border-0">After-work Proof</th>
                                <th class="border-0">Payment</th>
                                <th class="border-0">Dispute / Job State</th>
                                <th class="border-0 text-end">Required Admin Action</th>
                            </tr>
                        </thead>
                        <tbody id="workflowRows">
                            <tr><td colspan="6" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Syncing shared workflow state…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Active Grievances & Disputed Orders</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Arbitrate and record binding resolutions on disputed transactions</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                            <tr>
                                <th class="border-0">Booking Ref</th>
                                <th class="border-0">Raised By</th>
                                <th class="border-0">Dispute Reason</th>
                                <th class="border-0">Customer & Pro</th>
                                <th class="border-0">Order Value</th>
                                <th class="border-0">Dispute Status</th>
                                <th class="border-0 text-end">Arbitration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($disputes)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>
                                        No active disputes pending platform arbitration.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($disputes as $d): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= htmlspecialchars($d['booking_code']) ?>
                                            <div class="text-muted small"><?= date('d M Y', strtotime($d['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($d['raised_by_name']) ?></div>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill small"><?= strtoupper($d['raised_by_role']) ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 200px;"><?= htmlspecialchars($d['reason']) ?></div>
                                            <div class="text-muted small text-truncate" style="max-width: 250px;"><?= htmlspecialchars($d['details']) ?></div>
                                        </td>
                                        <td>
                                            <div class="small"><strong>Customer:</strong> <?= htmlspecialchars($d['customer_name']) ?></div>
                                            <div class="small text-muted"><strong>Pro:</strong> <?= htmlspecialchars($d['pro_name'] ?? 'Unassigned') ?></div>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            ₹<?= number_format((float)$d['total_amount'], 2) ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $dispBadge = match($d['status']) {
                                                    'open'         => 'badge-pill-red',
                                                    'under_review' => 'badge-pill-amber',
                                                    'resolved'     => 'badge-pill-green',
                                                    'dismissed'    => 'bg-secondary text-white rounded-pill px-2',
                                                    default        => 'bg-secondary text-white'
                                                };
                                            ?>
                                            <span class="badge <?= $dispBadge ?>">
                                                <?= strtoupper(str_replace('_', ' ', $d['status'])) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if (in_array($d['status'], ['open', 'under_review'], true)): ?>
                                                <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openArbitrationModal(<?= (int)$d['id'] ?>)">
                                                    <i class="bi bi-hammer me-1"></i> Resolve Dispute
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 2: REVIEWS MODERATION -->
        <div class="tab-pane fade" id="reviews-tab-pane">
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Customer Feedback & Rating Moderation</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Approve, hide or flag published testimonials</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                            <tr>
                                <th class="border-0">Rating</th>
                                <th class="border-0">Customer Feedback</th>
                                <th class="border-0">Booking Ref</th>
                                <th class="border-0">Reviewed Pro</th>
                                <th class="border-0">Date</th>
                                <th class="border-0">State</th>
                                <th class="border-0 text-end">Moderation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reviews)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No customer reviews on file.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reviews as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="text-warning fw-bold">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?>"></i>
                                                <?php endfor; ?>
                                                <span class="text-dark ms-1">(<?= $r['rating'] ?>.0)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($r['customer_name']) ?></div>
                                            <div class="text-muted fst-italic" style="font-size: 0.8rem;">"<?= htmlspecialchars($r['comment'] ?? 'No written comment.') ?>"</div>
                                        </td>
                                        <td class="fw-bold text-primary"><?= htmlspecialchars($r['booking_code']) ?></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($r['pro_name']) ?></td>
                                        <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                        <td>
                                            <span class="badge <?= $r['status'] === 'published' ? 'badge-pill-green' : ($r['status'] === 'flagged' ? 'badge-pill-red' : 'badge-pill-amber') ?>">
                                                <?= strtoupper($r['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-3" data-bs-toggle="dropdown">
                                                    Action
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li><button class="dropdown-item text-success" onclick="moderateReview(<?= $r['id'] ?>, 'published')"><i class="bi bi-check-circle me-2"></i>Publish</button></li>
                                                    <li><button class="dropdown-item text-secondary" onclick="moderateReview(<?= $r['id'] ?>, 'hidden')"><i class="bi bi-eye-slash me-2"></i>Hide from Public</button></li>
                                                    <li><button class="dropdown-item text-danger" onclick="moderateReview(<?= $r['id'] ?>, 'flagged')"><i class="bi bi-flag me-2"></i>Flag as Abusive</button></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: DISPUTE ARBITRATION WORKBENCH -->
<div class="modal fade" id="arbitrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/helpdesk/resolve-dispute" method="POST" class="modal-content card-custom border-0 shadow">
            <input type="hidden" name="dispute_id" id="arb_dispute_id" value="">
            <div class="modal-header border-bottom bg-light">
                <div>
                    <h5 class="modal-title fw-bold m-0" id="arb_title">Arbitration Workbench</h5>
                    <span class="text-muted" style="font-size: 0.75rem;">Official platform resolution & conflict recording</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="arb_body">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer border-top bg-white">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4" <?= empty($canApproveProof) ? 'disabled' : '' ?>>Save Binding Resolution</button>
            </div>
        </form>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const canApproveHelpdeskProof = <?= !empty($canApproveProof) ? 'true' : 'false' ?>;
const canManageHelpdeskBookings = <?= !empty($canManageHelpdeskBookings) ? 'true' : 'false' ?>;

function escapeHtml(value) {
    const node = document.createElement('span');
    node.textContent = String(value ?? '');
    return node.innerHTML;
}

function workflowAction(item) {
    if (item.dispute_id) {
        return `<button class="btn btn-sm btn-danger rounded-pill px-3" onclick="openArbitrationModal(${Number(item.dispute_id)})"><i class="bi bi-hammer me-1"></i>Resolve Dispute</button>`;
    }
    if (item.booking_status === 'disputed') {
        return '<span class="text-danger small">Disputed booking has no active dispute record</span>';
    }
    const activeBooking = ['pending', 'assigned', 'accepted', 'in_progress'].includes(item.booking_status);
    const codSatisfied = item.payment_method !== 'cod' || Number(item.cod_received) === 1;
    const paymentSatisfied = item.payment_method === 'cod'
        ? codSatisfied
        : item.payment_status === 'paid';
    const completionReady = item.booking_status === 'in_progress'
        && item.proof_status === 'approved'
        && Boolean(item.customer_confirmed_at)
        && paymentSatisfied;
    const controls = [];
    if (item.proof_id && item.proof_status === 'pending' && canApproveHelpdeskProof) {
        controls.push(`<a class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener" href="${appBaseUrl}/admin/dispatch/work-proof?proof_id=${Number(item.proof_id)}">Review Proof</a>`);
        controls.push(`<button class="btn btn-sm btn-success rounded-pill" onclick="reviewHelpdeskProof(${Number(item.proof_id)}, 'approved')">Approve</button>`);
        controls.push(`<button class="btn btn-sm btn-outline-danger rounded-pill" onclick="reviewHelpdeskProof(${Number(item.proof_id)}, 'rejected')">Reject</button>`);
    } else if (completionReady && canApproveHelpdeskProof) {
        controls.push(`<button class="btn btn-sm btn-success rounded-pill" onclick="completeHelpdeskBooking(${Number(item.booking_id)})"><i class="bi bi-check-circle me-1"></i>Complete Booking</button>`);
    } else if (item.proof_id && item.proof_status === 'approved') {
        controls.push('<span class="badge text-bg-success rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Proof Approved</span>');
        if (item.booking_status === 'in_progress' && !completionReady) {
            const waiting = [];
            if (!item.customer_confirmed_at) waiting.push('customer confirmation');
            if (item.payment_method === 'cod' && Number(item.cod_received) !== 1) waiting.push('COD receipt confirmation');
            else if (item.payment_method !== 'cod' && item.payment_status !== 'paid') waiting.push('payment');
            if (waiting.length) controls.push(`<span class="w-100 small text-muted">Waiting for ${escapeHtml(waiting.join(' and '))}</span>`);
        }
    } else if (item.proof_id) {
        controls.push(`<a class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" rel="noopener" href="${appBaseUrl}/admin/dispatch/work-proof?proof_id=${Number(item.proof_id)}">Review Proof</a>`);
    } else {
        controls.push('<span class="text-muted small">Awaiting technician after-proof</span>');
    }
    if (activeBooking && canManageHelpdeskBookings) {
        controls.push(item.payment_status === 'paid'
            ? '<button class="btn btn-sm btn-outline-secondary rounded-pill" disabled title="Payment is already recorded. Process the refund before cancelling."><i class="bi bi-x-circle me-1"></i>Cancel (Refund Required)</button>'
            : `<button class="btn btn-sm btn-outline-danger rounded-pill" onclick="cancelHelpdeskBooking(${Number(item.booking_id)})"><i class="bi bi-x-circle me-1"></i>Cancel</button>`);
    }
    if (completionReady && !canApproveHelpdeskProof) {
        controls.push('<span class="small text-success">Ready for admin completion</span>');
    }
    return `<div class="d-flex justify-content-end flex-wrap gap-1">${controls.join('')}</div>`;
}

async function postHelpdeskAction(path, values) {
    const payload = new FormData();
    Object.entries(values).forEach(([key, value]) => payload.append(key, String(value)));
    const response = await fetch(`${appBaseUrl}${path}`, { method: 'POST', body: payload, headers: { 'Accept': 'application/json' } });
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Admin action failed.');
    return result;
}

async function completeHelpdeskBooking(bookingId) {
    if (!canApproveHelpdeskProof || !window.confirm('Complete this booking using the verified proof, customer confirmation, and payment state?')) return;
    try {
        const result = await postHelpdeskAction('/admin/helpdesk/complete-booking', { booking_id: bookingId });
        window.alert(result.message);
        await refreshHelpdeskWorkflow();
    } catch (error) {
        window.alert(error.message);
    }
}

async function cancelHelpdeskBooking(bookingId) {
    if (!canManageHelpdeskBookings) return;
    const reason = window.prompt('Enter the reason for cancelling this booking:');
    if (reason === null) return;
    if (reason.trim().length < 5) {
        window.alert('Please enter a cancellation reason of at least 5 characters.');
        return;
    }
    if (!window.confirm('Cancel this booking? Customer and Pro panels will show the cancelled status.')) return;
    try {
        const result = await postHelpdeskAction('/admin/helpdesk/cancel-booking', { booking_id: bookingId, reason: reason.trim() });
        window.alert(result.message);
        await refreshHelpdeskWorkflow();
    } catch (error) {
        window.alert(error.message);
    }
}

async function refreshHelpdeskWorkflow() {
    const rows = document.getElementById('workflowRows');
    try {
        const response = await fetch(`${appBaseUrl}/admin/helpdesk/workflow-status`, { headers: { 'Accept': 'application/json' } });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to sync workflow.');
        const items = result.items || [];
        document.getElementById('workflowCount').textContent = `${items.length} active`;
        if (!items.length) {
            rows.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><i class="bi bi-check-circle-fill text-success fs-4 d-block mb-1"></i>No active proof approvals or disputed bookings.</td></tr>';
            return;
        }
        rows.innerHTML = items.map(item => {
            const dispute = item.dispute_id
                ? `<span class="badge ${item.dispute_status === 'open' ? 'text-bg-danger' : 'text-bg-warning'}">${escapeHtml(String(item.dispute_status).replaceAll('_', ' ').toUpperCase())}</span><div class="small text-muted mt-1">${escapeHtml(item.dispute_reason || '')}</div>`
                : (item.booking_status === 'disputed'
                    ? '<span class="badge text-bg-danger">DISPUTE RECORD MISSING</span>'
                    : `<span class="badge bg-light text-dark">${escapeHtml(String(item.booking_status).replaceAll('_', ' ').toUpperCase())}</span>`);
            const proof = item.proof_id
                ? `<span class="badge ${item.proof_status === 'approved' ? 'text-bg-success' : (item.proof_status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning')}">${escapeHtml(String(item.proof_status || 'pending').toUpperCase())}</span><div class="small text-muted mt-1">${escapeHtml(item.proof_description || 'No proof description')}</div>`
                : '<span class="text-muted small">Not submitted</span>';
            const customerConfirmation = item.customer_confirmed_at
                ? `<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Confirmed</span><div class="small text-muted">${escapeHtml(item.customer_confirmed_at)}</div>`
                : '<span class="text-muted">Customer code not confirmed</span>';
            const proConfirmation = item.pro_confirmation_requested_at
                ? `<div><i class="bi bi-send-check me-1 text-primary"></i>Pro requested completion code</div><div class="small text-muted">${escapeHtml(item.pro_confirmation_requested_at)}</div>`
                : '<div class="text-muted">Pro has not requested completion code</div>';
            return `<tr>
                <td><strong class="text-primary">${escapeHtml(item.booking_code)}</strong><div class="small">${escapeHtml(item.customer_name)} / ${escapeHtml(item.pro_name || 'Unassigned pro')}</div><div class="small text-muted">₹${Number(item.total_amount || 0).toFixed(2)}</div></td>
                <td><div><i class="bi bi-person-badge me-1 text-primary"></i>${escapeHtml(item.pro_name || 'No pro assigned')} · ${escapeHtml(String(item.booking_status).replaceAll('_', ' ').toUpperCase())}</div><div class="small mt-1">${proConfirmation}</div><div class="small mt-1">Customer: ${customerConfirmation}</div></td>
                <td>${proof}${item.proof_submitted_at ? `<div class="small text-muted">${escapeHtml(item.proof_submitted_at)}</div>` : ''}</td>
                <td><strong>${escapeHtml(String(item.payment_status).toUpperCase())}</strong><div class="small text-muted">${escapeHtml(String(item.payment_method).toUpperCase())}</div></td>
                <td>${dispute}</td><td class="text-end">${workflowAction(item)}</td>
            </tr>`;
        }).join('');
    } catch (error) {
        if (!rows.querySelector('.workflow-sync-error')) {
            rows.innerHTML = `<tr><td colspan="6" class="workflow-sync-error text-center text-danger py-3">${escapeHtml(error.message)} · retrying automatically</td></tr>`;
        }
    }
}

async function reviewHelpdeskProof(proofId, decision) {
    if (!canApproveHelpdeskProof) return;
    const message = decision === 'approved'
        ? 'Approve this after-work proof? Booking completion still requires customer confirmation and paid/COD payment.'
        : 'Reject this after-work proof? The technician can submit another proof.';
    if (!window.confirm(message)) return;
    const payload = new FormData();
    payload.append('proof_id', String(proofId));
    payload.append('decision', decision);
    try {
        const response = await fetch(`${appBaseUrl}/admin/helpdesk/review-work-proof`, { method: 'POST', body: payload });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Proof review failed.');
        await refreshHelpdeskWorkflow();
    } catch (error) {
        window.alert(error.message);
    }
}

function openArbitrationModal(disputeId) {
    document.getElementById('arb_dispute_id').value = disputeId;
    const modalEl = document.getElementById('arbitrationModal');
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('arb_body');
    modal.show();

    fetch(`${appBaseUrl}/admin/helpdesk/dispute-details?dispute_id=${disputeId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                body.innerHTML = `<div class="alert alert-danger">${escapeHtml(res.message)}</div>`;
                return;
            }

            const d = res.data.dispute;
            document.getElementById('arb_title').innerText = `Arbitrate Dispute: Booking #${d.booking_code}`;
            const proofsHtml = (res.data.proofs || []).map(proof => `
                <div class="d-flex justify-content-between align-items-center border rounded-3 p-2 mb-1 small">
                    <span><strong>${escapeHtml(String(proof.proof_type).replaceAll('_', ' ').toUpperCase())}</strong> · ${escapeHtml(String(proof.review_status).toUpperCase())} · ${escapeHtml(proof.description || 'No description')}</span>
                    <a target="_blank" rel="noopener" href="${appBaseUrl}/admin/dispatch/work-proof?proof_id=${Number(proof.id)}">Review Proof</a>
                </div>`).join('') || '<div class="small text-muted">No work proofs attached.</div>';

            body.innerHTML = `
                <div class="p-3 border rounded-3 bg-light mb-3">
                    <div class="row small">
                        <div class="col-6">
                            <div><strong>Customer:</strong> ${escapeHtml(d.customer_name)} (${escapeHtml(d.customer_phone)})</div>
                            <div><strong>Professional:</strong> ${escapeHtml(d.pro_name || 'Unassigned')}</div>
                        </div>
                        <div class="col-6 text-end">
                            <div><strong>Order Amount:</strong> ₹${Number(d.total_amount).toFixed(2)}</div>
                            <div><strong>Payment State:</strong> ${escapeHtml(String(d.payment_status).toUpperCase())}</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Dispute Grievance Claimed</label>
                    <div class="p-2 border rounded-3 bg-light text-danger fw-semibold small">${escapeHtml(d.reason)}</div>
                    <div class="p-2 border rounded-3 bg-light text-muted small mt-1">${escapeHtml(d.details)}</div>
                </div>

                <div class="mb-3"><label class="form-label small fw-bold">Booking Work Evidence</label>${proofsHtml}</div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Arbitration Decision</label>
                        <select name="status" class="form-select" required>
                            <option value="resolved" ${d.status === 'resolved' ? 'selected' : ''}>Resolved (Claim Satisfied)</option>
                            <option value="under_review" ${d.status === 'under_review' ? 'selected' : ''}>Under Active Investigation</option>
                            <option value="dismissed" ${d.status === 'dismissed' ? 'selected' : ''}>Dismissed (No Merit)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small fw-bold">Resolution Statement & Settlement Terms</label>
                    <textarea name="resolution_notes" rows="3" class="form-control" placeholder="Explain the arbitration result and any separately recorded settlement/refund..." required>${escapeHtml(d.resolution_notes || '')}</textarea>
                </div>
            `;
        })
        .catch(() => {
            body.innerHTML = '<div class="alert alert-danger">Error retrieving dispute record.</div>';
        });
}

document.querySelector('#arbitrationModal form').addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!canApproveHelpdeskProof) return;
    const form = event.currentTarget;
    const submit = form.querySelector('[type="submit"]');
    submit.disabled = true;
    try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Dispute resolution failed.');
        bootstrap.Modal.getInstance(document.getElementById('arbitrationModal')).hide();
        window.location.reload();
    } catch (error) {
        const body = document.getElementById('arb_body');
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger mt-3';
        alert.textContent = error.message;
        body.prepend(alert);
    } finally {
        submit.disabled = false;
    }
});

function moderateReview(reviewId, status) {
    const formData = new FormData();
    formData.append('review_id', reviewId);
    formData.append('status', status);

    fetch(`${appBaseUrl}/admin/helpdesk/moderate-review`, {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        let result;
        try {
            result = await response.json();
        } catch {
            throw new Error(`Server error (HTTP ${response.status}). Check the PHP error log.`);
        }
        if (!response.ok || !result.success) {
            throw new Error(result.message || `Review moderation failed (HTTP ${response.status}).`);
        }
        location.reload();
    })
    .catch(error => alert(`Review moderation failed: ${error.message}`));
}

refreshHelpdeskWorkflow();
window.setInterval(refreshHelpdeskWorkflow, 5000);
</script>