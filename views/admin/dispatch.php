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

    <!-- Title & Quick Filters -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Master Dispatch Board</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Fulfill job requests, auto-match technicians by zone, and track live service progression.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/dispatch?status=pending" class="btn btn-warning rounded-pill px-3 py-2 fw-semibold position-relative">
                <i class="bi bi-bell-fill me-1"></i> Pending Dispatch (Action Required)
            </a>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="card-custom p-4 mb-4">
        <form method="GET" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/dispatch" class="row g-2 mb-3">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Booking Ref #, Customer Name or Phone..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Fulfillment Statuses</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pending Assignment</option>
                    <option value="assigned" <?= $statusFilter === 'assigned' ? 'selected' : '' ?>>Assigned (Pending Acceptance)</option>
                    <option value="accepted" <?= $statusFilter === 'accepted' ? 'selected' : '' ?>>Accepted by Pro</option>
                    <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>In Progress (Work Underway)</option>
                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="zone_id" class="form-select">
                    <option value="0">All Service Zones</option>
                    <?php foreach ($zones as $z): ?>
                        <option value="<?= $z['id'] ?>" <?= $zoneFilter === (int)$z['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($z['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill px-3 fw-medium w-100">Filter</button>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/dispatch" class="btn btn-outline-secondary rounded-pill px-3 fw-medium">Clear</a>
            </div>
        </form>

        <!-- Bookings Master Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th class="border-0">Booking Ref</th>
                        <th class="border-0">Customer & Zone</th>
                        <th class="border-0">Scheduled Slot</th>
                        <th class="border-0">Assigned Technician</th>
                        <th class="border-0">Total Amount</th>
                        <th class="border-0">Payment</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 text-end">Dispatch Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No dispatch bookings matched your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-primary">
                                    <?= htmlspecialchars($b['booking_code']) ?>
                                    <div class="text-muted" style="font-size: 0.72rem;"><?= date('d M Y, h:i A', strtotime($b['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($b['customer_name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.74rem;">
                                        <i class="bi bi-geo-alt text-secondary"></i> <?= htmlspecialchars($b['zone_name'] ?? $b['service_city']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= date('d M Y', strtotime($b['scheduled_date'])) ?></div>
                                    <span class="text-muted small"><?= htmlspecialchars($b['scheduled_time_slot']) ?></span>
                                </td>
                                <td>
                                    <?php if ($b['pro_name']): ?>
                                        <div class="fw-semibold text-dark"><i class="bi bi-person-check-fill text-success me-1"></i><?= htmlspecialchars($b['pro_name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.74rem;"><?= htmlspecialchars($b['pro_phone']) ?></div>
                                    <?php else: ?>
                                        <span class="badge badge-pill-amber">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark">
                                    ₹<?= number_format((float)$b['total_amount'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $b['payment_status'] === 'paid' ? 'badge-pill-green' : 'badge-pill-amber' ?>">
                                        <?= strtoupper($b['payment_status']) ?> (<?= strtoupper($b['payment_method']) ?>)
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $statusBadge = match($b['status']) {
                                            'completed'   => 'badge-pill-green',
                                            'in_progress' => 'badge-pill-blue',
                                            'assigned', 'accepted' => 'badge-pill-blue',
                                            'pending'     => 'badge-pill-amber',
                                            'cancelled'   => 'badge-pill-red',
                                            default       => 'bg-secondary text-white rounded-pill px-2'
                                        };
                                    ?>
                                    <span class="badge <?= $statusBadge ?>">
                                        <?= strtoupper(str_replace('_', ' ', $b['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openDispatchModal(<?= $b['id'] ?>)">
                                        <i class="bi bi-sliders me-1"></i> Dispatch & Track
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: DISPATCH WORKBENCH & ACTION PANEL -->
<div class="modal fade" id="dispatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-bottom bg-light">
                <div>
                    <h5 class="modal-title fw-bold m-0" id="dp_code_title">Order Dispatch Dossier</h5>
                    <span class="text-muted" id="dp_subtitle" style="font-size: 0.75rem;">FixMate Automated Dispatch Control</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="dp_modal_content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer border-top bg-white d-flex justify-content-between">
                <div id="dp_footer_actions"></div>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const canApproveWorkProof = <?= !empty($canApproveProof) ? 'true' : 'false' ?>;
let currentBookingId = 0;
let adminCompletionPoll = 0;

function openDispatchModal(bookingId) {
    currentBookingId = bookingId;
    if (adminCompletionPoll) window.clearInterval(adminCompletionPoll);
    const modalEl = document.getElementById('dispatchModal');
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('dp_modal_content');
    const footer = document.getElementById('dp_footer_actions');

    if (!modalEl.dataset.completionPollCleanup) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (adminCompletionPoll) window.clearInterval(adminCompletionPoll);
            adminCompletionPoll = 0;
        });
        modalEl.dataset.completionPollCleanup = 'true';
    }

    footer.innerHTML = '';
    modal.show();

    fetch(`${appBaseUrl}/admin/dispatch/booking-details?booking_id=${bookingId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                body.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                return;
            }

            const b = res.data.booking;
            const items = res.data.items;
            const proofs = res.data.proofs || [];
            const pros = res.data.eligiblePros;

            document.getElementById('dp_code_title').innerText = `Booking #${b.booking_code} Dispatch`;
            document.getElementById('dp_subtitle').innerText = `Created ${b.created_at} | Scheduled: ${b.scheduled_date} (${b.scheduled_time_slot})`;

            // Line items breakdown
            let itemsHtml = items.map(i => `
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark">${i.service_name}</div>
                        <div class="text-muted small">Qty: ${i.quantity} × ₹${Number(i.unit_price).toFixed(2)}</div>
                    </div>
                    <div class="fw-bold text-dark">₹${Number(i.total_price).toFixed(2)}</div>
                </div>
            `).join('');

            let proofsHtml = proofs.length ? proofs.map(proof => `
                <div class="d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center p-3 border rounded-3 mb-2 bg-light">
                    <div>
                        <div class="fw-bold">${escapeHtml(proof.proof_type.replaceAll('_', ' ').toUpperCase())}
                            <span class="badge ${proof.review_status === 'approved' ? 'text-bg-success' : (proof.review_status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning')} ms-1">${escapeHtml(proof.review_status.toUpperCase())}</span>
                        </div>
                        <div class="small text-muted">${escapeHtml(proof.description || 'No description')} · ${escapeHtml(proof.created_at)}</div>
                        <a href="${appBaseUrl}/admin/dispatch/work-proof?proof_id=${encodeURIComponent(proof.id)}" target="_blank" rel="noopener" class="small">View attachment</a>
                    </div>
                    ${canApproveWorkProof && proof.review_status === 'pending' ? `<div class="d-flex gap-2">${proof.proof_type !== 'after' ? `<button class="btn btn-sm btn-outline-success rounded-pill" onclick="reviewWorkProof(${Number(proof.id)}, 'approved')">Approve evidence</button>` : `<button class="btn btn-sm btn-success rounded-pill" onclick="reviewWorkProof(${Number(proof.id)}, 'approved')">Approve after-proof</button>`}<button class="btn btn-sm btn-outline-danger rounded-pill" onclick="reviewWorkProof(${Number(proof.id)}, 'rejected')">Reject proof</button></div>` : ''}
                </div>
            `).join('') : '<div class="text-muted small p-3 bg-light rounded-3">No technician work proofs have been uploaded for this booking.</div>';

            // Eligible pros options
            let proOptions = pros.map(p => `
                <option value="${p.pro_id}">
                    ${p.name} (★${Number(p.rating_avg).toFixed(1)}, ${p.active_jobs} active jobs)
                </option>
            `).join('');

            footer.innerHTML = `
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-danger rounded-pill px-3" onclick="promptCancelBooking()">
                        <i class="bi bi-x-circle me-1"></i> Cancel Booking
                    </button>
                </div>
            `;

            body.innerHTML = `
                <div class="row g-4">
                    <!-- Left Column: Customer & Service Items -->
                    <div class="col-12 col-lg-7 border-end">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Customer & Location</h6>
                        <div class="row mb-3 small">
                            <div class="col-sm-6">
                                <div><strong>Customer:</strong> ${b.customer_name}</div>
                                <div><strong>Phone:</strong> ${b.customer_phone}</div>
                                <div><strong>Email:</strong> ${b.customer_email}</div>
                            </div>
                            <div class="col-sm-6">
                                <div><strong>Address (${b.addr_label}):</strong></div>
                                <div class="text-muted">${b.address_line1}, ${b.address_line2 || ''}</div>
                                <div class="text-muted">${b.city}, ${b.state} - ${b.postal_code}</div>
                                <div class="text-primary fw-semibold mt-1"><i class="bi bi-geo-alt"></i> Zone: ${b.zone_name || 'Generic'}</div>
                            </div>
                        </div>

                        ${b.notes ? `
                            <div class="p-2 border rounded-3 bg-light small mb-3">
                                <strong>Customer Notes:</strong> ${b.notes}
                            </div>
                        ` : ''}

                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Booked Line Items</h6>
                        ${itemsHtml}

                        <h6 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-2"><i class="bi bi-camera-fill text-primary me-1"></i>Technician Work Proofs</h6>
                        <p class="small text-muted">Approve the after-work evidence here. Completion and technician earnings are released only after the customer shares their one-time code with the technician; COD jobs also require cash-received confirmation.</p>
                        ${proofsHtml}

                        <div class="mt-3 p-3 bg-light rounded-3 small">
                            <div class="d-flex justify-content-between py-1"><span>Subtotal:</span> <span>₹${Number(b.subtotal).toFixed(2)}</span></div>
                            <div class="d-flex justify-content-between py-1"><span>Surge Pricing:</span> <span>+ ₹${Number(b.surge_amount).toFixed(2)}</span></div>
                            <div class="d-flex justify-content-between py-1"><span>Coupon Discount (${b.coupon_code || 'None'}):</span> <span>- ₹${Number(b.discount_amount).toFixed(2)}</span></div>
                            <div class="d-flex justify-content-between py-1"><span>GST Tax (18%):</span> <span>+ ₹${Number(b.tax_amount).toFixed(2)}</span></div>
                            <div class="d-flex justify-content-between py-1 border-top fw-bold fs-6 text-dark"><span>Total Paid/Payable:</span> <span>₹${Number(b.total_amount).toFixed(2)}</span></div>
                        </div>
                        <section id="dpCompletionStatus" class="card-custom p-3 mt-3" aria-live="polite">
                            <h6 class="fw-bold mb-2"><i class="bi bi-check2-square text-primary me-1"></i>Completion &amp; payout status</h6>
                            <div class="small text-muted">Refreshing verified customer code, proof approval, payment and technician earnings…</div>
                        </section>
                    </div>

                    <!-- Right Column: Dispatch Engine -->
                    <div class="col-12 col-lg-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Dispatch Execution Engine</h6>

                        <!-- Current Assignment Status -->
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="text-muted small">Current Technician</div>
                            <div class="fs-5 fw-bold text-dark">${b.pro_name || 'Not Yet Assigned'}</div>
                            ${b.pro_phone ? `<div class="small text-muted">${b.pro_phone} (★${Number(b.pro_rating).toFixed(1)})</div>` : ''}
                            <div class="mt-2">
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1">Current Status: ${b.status.toUpperCase()}</span>
                            </div>
                        </div>

                        <!-- 1. Auto Dispatch Trigger -->
                        <div class="p-3 border rounded-3 mb-3 bg-primary-subtle border-primary-subtle">
                            <div class="fw-bold text-primary mb-1"><i class="bi bi-robot me-1"></i> Auto-Match Best Technician</div>
                            <p class="small text-secondary mb-2">System automatically analyzes pro ratings, nearest zone availability, and lowest active workload.</p>
                            <button class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold w-100" onclick="triggerAutoAssign(${b.id})">
                                <i class="bi bi-lightning-charge me-1"></i> Auto-Dispatch Now
                            </button>
                        </div>

                        <!-- 2. Manual Dispatch Select -->
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="fw-bold text-dark mb-1"><i class="bi bi-person-gear me-1"></i> Manual Dispatch Override</div>
                            <p class="small text-muted mb-2">Select any verified technician active in ${b.zone_name || 'this city'}.</p>
                            
                            <select class="form-select form-select-sm mb-2" id="dp_manual_pro_select">
                                <option value="">Choose Technician...</option>
                                ${proOptions}
                            </select>

                            <button class="btn btn-dark btn-sm rounded-pill px-3 fw-semibold w-100" onclick="triggerManualAssign(${b.id})">
                                Confirm Assignment
                            </button>
                        </div>
                    </div>
                </div>
            `;
            refreshAdminCompletionStatus();
            adminCompletionPoll = window.setInterval(refreshAdminCompletionStatus, 5000);
        })
        .catch(() => {
            body.innerHTML = `<div class="alert alert-danger">Error retrieving dispatch record.</div>`;
        });
}

async function refreshAdminCompletionStatus() {
    const panel = document.getElementById('dpCompletionStatus');
    if (!panel || currentBookingId <= 0) return;
    try {
        const response = await fetch(`${appBaseUrl}/admin/dispatch/completion-status?booking_id=${encodeURIComponent(currentBookingId)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (!response.ok || !result.success) return;
        const booking = result.booking;
        const rows = [
            ['Booking', String(booking.status).replaceAll('_', ' ').toUpperCase()],
            ['After-work proof', (booking.after_proof_status || 'not submitted').replaceAll('_', ' ').toUpperCase()],
            ['Customer completion code', booking.completion_otp_verified_at ? `CONFIRMED · ${booking.completion_otp_verified_at}` : 'NOT CONFIRMED'],
            ['Payment', `${String(booking.payment_status).toUpperCase()} · ${String(booking.payment_method).toUpperCase()}`],
            ['Technician net earning', `₹${Number(booking.pro_earning || 0).toFixed(2)}`],
            ['Platform commission', `₹${Number(booking.commission_amount || 0).toFixed(2)}`],
            ['Wallet credit', Number(booking.pro_earning_credited) ? 'CREDITED' : 'NOT CREDITED']
        ];
        panel.replaceChildren();
        const heading = document.createElement('h6');
        heading.className = 'fw-bold mb-2';
        heading.textContent = 'Completion & payout status · live';
        panel.appendChild(heading);
        rows.forEach(function (row) {
            const line = document.createElement('div');
            line.className = 'd-flex justify-content-between gap-3 py-1 small border-top';
            const label = document.createElement('span');
            label.className = 'text-muted';
            label.textContent = row[0];
            const value = document.createElement('strong');
            value.className = row[0] === 'Wallet credit' && Number(booking.pro_earning_credited) ? 'text-success text-end' : 'text-end';
            value.textContent = row[1];
            line.append(label, value);
            panel.appendChild(line);
        });
    } catch (error) {
        // Keep the previous status while the detail poll recovers.
    }
}

function escapeHtml(value) {
    const node = document.createElement('span');
    node.textContent = String(value ?? '');
    return node.innerHTML;
}

async function reviewWorkProof(proofId, decision, confirmCodPayment = false) {
    if (!canApproveWorkProof) return;
    if (decision === 'approved' && !window.confirm('Approve this work evidence? Job completion and earnings still require the customer completion code.')) return;
    const payload = new FormData();
    payload.append('proof_id', proofId);
    payload.append('decision', decision);
    payload.append('confirm_cod_payment', confirmCodPayment ? '1' : '0');
    try {
        const response = await fetch(`${appBaseUrl}/admin/dispatch/review-work-proof`, { method: 'POST', body: payload });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Could not review proof.');
        alert(result.message);
        openDispatchModal(currentBookingId);
    } catch (error) {
        alert(error.message);
    }
}

function triggerAutoAssign(bookingId) {
    const formData = new FormData();
    formData.append('booking_id', bookingId);

    fetch(`${appBaseUrl}/admin/dispatch/auto-assign`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else {
            alert('Auto-Dispatch Error: ' + res.message);
        }
    })
    .catch(() => alert('Network error during auto-dispatch.'));
}

function triggerManualAssign(bookingId) {
    const proId = document.getElementById('dp_manual_pro_select').value;
    if (!proId) {
        alert('Please choose a technician from the list.');
        return;
    }

    const formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('pro_profile_id', proId);

    fetch(`${appBaseUrl}/admin/dispatch/manual-assign`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else {
            alert('Assignment Failed: ' + res.message);
        }
    })
    .catch(() => alert('Network error during manual assignment.'));
}

function updateStatus(status, reason = null) {
    const formData = new FormData();
    formData.append('booking_id', currentBookingId);
    formData.append('status', status);
    if (reason) formData.append('reason', reason);

    fetch(`${appBaseUrl}/admin/dispatch/update-status`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            location.reload();
        } else {
            alert('Failed to update status: ' + res.message);
        }
    })
    .catch(() => alert('Network error updating status.'));
}

function promptCancelBooking() {
    const reason = prompt('Specify cancellation reason: (e.g. Customer requested, no pro available in area)');
    if (reason === null) return;
    if (reason.trim() === '') {
        alert('Cancellation reason required.');
        return;
    }
    updateStatus('cancelled', reason.trim());
}
</script>