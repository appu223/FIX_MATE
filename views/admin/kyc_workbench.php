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

    <!-- Title & Summary -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Technician Dossiers & KYC Workbench</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Inspect identity proofs, verify tradesman qualifications, and configure commission margins.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#createTechnicianModal">
                <i class="bi bi-person-plus-fill me-1"></i> Add Technician
            </button>
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications?kyc_status=pending" class="btn btn-warning rounded-pill px-3 py-2 fw-semibold position-relative">
                <i class="bi bi-exclamation-circle me-1"></i> Pending Verification Queue
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card-custom p-4 mb-4">
        <form method="GET" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search technician by name, phone, or email..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="kyc_status" class="form-select">
                    <option value="">All Verification States</option>
                    <option value="pending" <?= $kycFilter === 'pending' ? 'selected' : '' ?>>Pending Review (Action Needed)</option>
                    <option value="verified" <?= $kycFilter === 'verified' ? 'selected' : '' ?>>Verified & Approved</option>
                    <option value="rejected" <?= $kycFilter === 'rejected' ? 'selected' : '' ?>>Rejected Submissions</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill px-4 fw-medium">Filter Pros</button>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications" class="btn btn-outline-secondary rounded-pill px-3 fw-medium">Reset</a>
            </div>
        </form>

        <!-- Technicians Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th class="border-0">Pro Dossier</th>
                        <th class="border-0">Experience & Rating</th>
                        <th class="border-0">ID Proof Type</th>
                        <th class="border-0">KYC Status</th>
                        <th class="border-0">Commission</th>
                        <th class="border-0">Wallet Balance</th>
                        <th class="border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pros)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No professional technician dossiers found matching criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pros as $p): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.82rem;">
                                            <?= strtoupper(substr($p['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="text-muted" style="font-size: 0.74rem;">#PRO-<?= str_pad((string)$p['pro_id'], 4, '0', STR_PAD_LEFT) ?> | <?= htmlspecialchars($p['phone']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= $p['experience_years'] ?> yrs experience</div>
                                    <div class="text-warning small">
                                        <i class="bi bi-star-fill"></i> <?= number_format((float)$p['rating_avg'], 2) ?> 
                                        <span class="text-muted" style="font-size: 0.72rem;">(<?= $p['rating_count'] ?> jobs)</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        <i class="bi bi-file-earmark-person me-1"></i><?= htmlspecialchars($p['id_proof_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $badge = match($p['kyc_status']) {
                                            'verified' => 'badge-pill-green',
                                            'pending'  => 'badge-pill-amber',
                                            'rejected' => 'badge-pill-red',
                                            default    => 'bg-secondary text-white'
                                        };
                                    ?>
                                    <span class="badge <?= $badge ?>">
                                        <?= strtoupper($p['kyc_status']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-primary">
                                    <?= number_format((float)$p['commission_rate'], 2) ?>%
                                </td>
                                <td class="fw-bold text-dark">
                                    ₹<?= number_format((float)$p['wallet_balance'], 2) ?>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3" onclick="openInspectionWorkbench(<?= $p['pro_id'] ?>)">
                                        <i class="bi bi-search me-1"></i> Inspect Dossier
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

<!-- MODAL: CREATE TECHNICIAN ACCOUNT -->
<div class="modal fade" id="createTechnicianModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications/create-technician" method="POST" class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold">Add Technician</h5>
                    <p class="small text-muted mb-0">Creates a partner account and adds it to pending KYC review.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3"><label class="form-label" for="new-pro-name">Full name</label><input class="form-control" id="new-pro-name" name="name" required maxlength="120" autocomplete="name"></div>
                <div class="mb-3"><label class="form-label" for="new-pro-email">Email</label><input class="form-control" id="new-pro-email" type="email" name="email" required maxlength="191" autocomplete="email"></div>
                <div class="mb-3"><label class="form-label" for="new-pro-phone">Phone</label><input class="form-control" id="new-pro-phone" name="phone" required maxlength="20" autocomplete="tel"></div>
                <div class="row g-3">
                    <div class="col-6"><label class="form-label" for="new-pro-experience">Experience (years)</label><input class="form-control" id="new-pro-experience" type="number" name="experience_years" min="0" max="80" value="1" required></div>
                    <div class="col-6"><label class="form-label" for="new-pro-proof">ID proof type</label><select class="form-select" id="new-pro-proof" name="id_proof_type"><option>Aadhaar Card</option><option>PAN Card</option><option>Driving License</option><option>Voter ID</option></select></div>
                </div>
                <div class="mt-3"><label class="form-label" for="new-pro-password">Temporary password</label><input class="form-control" id="new-pro-password" type="password" name="password" minlength="8" autocomplete="new-password" required><div class="form-text">At least 8 characters. Share it securely with the technician.</div></div>
                <div class="alert alert-info small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>The account starts pending verification and cannot access the partner panel until KYC is approved.</div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create &amp; add to KYC queue</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: KYC INSPECTION & COMMISSION WORKBENCH -->
<div class="modal fade" id="kycWorkbenchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-bottom bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="wb_name">Technician Dossier</h5>
                        <span class="text-muted" id="wb_pro_tag" style="font-size: 0.75rem;">FixMate KYC Workbench</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4" id="wb_content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2">Loading documents and technician dossier...</div>
                </div>
            </div>

            <div class="modal-footer border-top bg-white d-flex justify-content-between">
                <div id="wb_decision_buttons"></div>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
const canVerifyKyc = <?= !empty($canVerify) ? 'true' : 'false' ?>;
let currentProId = 0;

function openInspectionWorkbench(proId) {
    currentProId = proId;
    const modalEl = document.getElementById('kycWorkbenchModal');
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('wb_content');
    const footerButtons = document.getElementById('wb_decision_buttons');

    footerButtons.innerHTML = '';
    modal.show();

    fetch(`${appBaseUrl}/admin/kyc-verifications/pro-dossier?pro_id=${proId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                body.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                return;
            }

            const d = res.data.dossier;
            const services = res.data.services;
            const zones = res.data.zones;
            const jobs = res.data.jobs;

            document.getElementById('wb_name').innerText = d.name;
            document.getElementById('wb_pro_tag').innerText = `ID #PRO-${String(d.pro_id).padStart(4, '0')} | Joined ${new Date(d.created_at).toLocaleDateString()}`;

            // Build dynamic decision buttons
            footerButtons.innerHTML = canVerifyKyc ? `
                <div class="d-flex gap-2">
                    <button class="btn btn-success rounded-pill px-4 fw-semibold" onclick="submitKycDecision('verified')">
                        <i class="bi bi-check2-circle me-1"></i> Approve & Verify
                    </button>
                    <button class="btn btn-danger rounded-pill px-4 fw-semibold" onclick="promptRejectKyc()">
                        <i class="bi bi-x-circle me-1"></i> Reject Documents
                    </button>
                </div>
            ` : '<span class="text-muted small">Only an administrator can make KYC decisions.</span>';

            let serviceBadges = services.length === 0 ? '<span class="text-muted small">No service categories mapped.</span>' :
                services.map(s => `<span class="badge bg-light text-dark border p-2 me-1 mb-1">${s.category_name}: ${s.service_name}</span>`).join('');

            let zoneBadges = zones.length === 0 ? '<span class="text-muted small">No service zones assigned.</span>' :
                zones.map(z => `<span class="badge bg-primary-subtle text-primary border border-primary-subtle p-2 me-1 mb-1"><i class="bi bi-geo-alt me-1"></i>${z.zone_name} (${z.city})</span>`).join('');

            let jobsRows = jobs.length === 0 ? '<tr><td colspan="4" class="text-muted small text-center py-2">No jobs fulfilled yet.</td></tr>' :
                jobs.map(j => `
                    <tr>
                        <td class="fw-bold text-primary">${j.booking_code}</td>
                        <td class="small">${j.scheduled_date}</td>
                        <td class="fw-semibold">₹${Number(j.pro_earning).toFixed(2)}</td>
                        <td><span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">${j.status.toUpperCase()}</span></td>
                    </tr>
                `).join('');

            body.innerHTML = `
                <div class="row g-4">
                    <!-- Column Left: Profile & Verification Documents -->
                    <div class="col-12 col-lg-7 border-end">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-file-earmark-lock text-primary me-2"></i>KYC Documentation Workbench
                        </h6>

                        ${d.kyc_status === 'rejected' ? `
                            <div class="alert alert-danger p-3 mb-3 small">
                                <strong>Rejection Reason on File:</strong> ${d.kyc_rejected_reason || 'Incomplete proofs.'}
                            </div>
                        ` : ''}

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="text-muted small">Primary Identification (${d.id_proof_type})</div>
                                    <div class="fw-semibold text-truncate mb-2">${d.id_proof_file ? d.id_proof_file.split('/').pop() : 'Not Uploaded'}</div>
                                    <a href="${appBaseUrl}/admin/kyc-verifications/document?pro_id=${encodeURIComponent(d.pro_id)}&kind=id" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-pill px-3 ${!d.id_proof_file ? 'disabled' : ''}">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> View Document
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="text-muted small">Address Proof Document</div>
                                    <div class="fw-semibold text-truncate mb-2">${d.address_proof_file ? d.address_proof_file.split('/').pop() : 'Not Uploaded'}</div>
                                    <a href="${appBaseUrl}/admin/kyc-verifications/document?pro_id=${encodeURIComponent(d.pro_id)}&kind=address" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark rounded-pill px-3 ${!d.address_proof_file ? 'disabled' : ''}">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> View Document
                                    </a>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-muted small">Trade Certificate / Professional License</div>
                                        <div class="fw-semibold text-truncate">${d.license_file ? d.license_file.split('/').pop() : 'No Trade License Submitted'}</div>
                                    </div>
                                    <a href="${appBaseUrl}/admin/kyc-verifications/document?pro_id=${encodeURIComponent(d.pro_id)}&kind=license" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3 ${!d.license_file ? 'disabled' : ''}">
                                        <i class="bi bi-award me-1"></i> Inspect License
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Professional Biography</label>
                            <p class="small text-muted p-2 rounded bg-light border">${d.bio || 'No biography written.'}</p>
                        </div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-2">Assigned Services & Coverage</h6>
                        <div class="mb-2">${serviceBadges}</div>
                        <div class="mb-3">${zoneBadges}</div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-2">Recent Order Dispatch Ledger</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted" style="font-size: 0.72rem;">
                                        <th>CODE</th>
                                        <th>DATE</th>
                                        <th>EARNING</th>
                                        <th>STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>${jobsRows}</tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Column Right: Financials, Commission & Banking -->
                    <div class="col-12 col-lg-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="bi bi-bank text-primary me-2"></i>Payout & Commission Configuration
                        </h6>

                        <form method="POST" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications/update-financials" class="p-3 border rounded-3 bg-light">
                            <input type="hidden" name="pro_id" value="${d.pro_id}">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Platform Commission Rate (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" name="commission_rate" class="form-control" value="${d.commission_rate}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <span class="text-muted" style="font-size: 0.72rem;">Base rate is 15.00%. Lower this for top performers.</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control" value="${d.bank_name || ''}" placeholder="e.g. HDFC Bank">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Account Number</label>
                                <input type="text" name="bank_account_no" class="form-control" value="${d.bank_account_no || ''}" placeholder="e.g. 50100234918231">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Bank IFSC Code</label>
                                <input type="text" name="bank_ifsc" class="form-control" value="${d.bank_ifsc || ''}" placeholder="e.g. HDFC0000240">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Emergency Contact Phone</label>
                                <input type="text" class="form-control bg-white" readonly value="${d.emergency_contact || 'None'}">
                            </div>

                            <button type="submit" class="btn btn-dark w-100 rounded-pill fw-semibold mt-2">
                                <i class="bi bi-save me-1"></i> Save Financial Terms
                            </button>
                        </form>
                    </div>
                </div>

                <section class="card-custom border p-3 mt-4" aria-labelledby="admin-pro-chat-heading">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <div>
                            <h6 id="admin-pro-chat-heading" class="fw-bold mb-0"><i class="bi bi-chat-square-text-fill text-primary me-2"></i>Direct Technician Conversation</h6>
                            <span class="small text-muted">Messages appear on the technician dashboard and refresh automatically.</span>
                        </div>
                        <span class="badge text-bg-primary rounded-pill">Admin ↔ Technician</span>
                    </div>
                    <div id="adminProChatMessages" class="d-flex flex-column gap-2 mb-3" style="max-height:220px;overflow-y:auto" aria-live="polite">
                        <p class="text-muted small mb-0">Loading conversation…</p>
                    </div>
                    <form onsubmit="sendAdminProfessionalMessage(event)" class="d-flex gap-2">
                        <label class="visually-hidden" for="admin-pro-chat-input">Message technician</label>
                        <input id="admin-pro-chat-input" name="message" class="form-control" maxlength="4000" placeholder="Write a message to the technician…" required>
                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-send-fill me-1"></i>Send</button>
                    </form>
                </section>
            `;
            refreshAdminProfessionalChat();
        })
        .catch(() => {
            body.innerHTML = `<div class="alert alert-danger">Failed to load technician dossier.</div>`;
        });
}

function showKycNotification(message, type = 'success') {
    let container = document.getElementById('kycToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'kycToastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0`;
    toast.setAttribute('role', type === 'danger' ? 'alert' : 'status');
    toast.setAttribute('aria-live', type === 'danger' ? 'assertive' : 'polite');
    toast.setAttribute('aria-atomic', 'true');

    const row = document.createElement('div');
    row.className = 'd-flex';
    const body = document.createElement('div');
    body.className = 'toast-body fw-semibold';
    body.textContent = message;
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'btn-close btn-close-white me-2 m-auto';
    close.setAttribute('data-bs-dismiss', 'toast');
    close.setAttribute('aria-label', 'Close');
    row.append(body, close);
    toast.appendChild(row);
    container.appendChild(toast);

    if (window.bootstrap && bootstrap.Toast) {
        const toastInstance = new bootstrap.Toast(toast, { delay: 5000 });
        toast.addEventListener('hidden.bs.toast', () => toast.remove(), { once: true });
        toastInstance.show();
    } else {
        window.alert(message);
        toast.remove();
    }
}

async function refreshAdminProfessionalChat() {
    const box = document.getElementById('adminProChatMessages');
    if (!box || currentProId <= 0) return;
    try {
        const response = await fetch(`${appBaseUrl}/admin/kyc-verifications/chat?pro_id=${encodeURIComponent(currentProId)}`, {
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load conversation.');
        const atBottom = box.scrollHeight - box.scrollTop - box.clientHeight < 40;
        box.replaceChildren();
        if (!result.messages.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted small mb-0';
            empty.textContent = 'No messages yet. Start a direct conversation with this technician.';
            box.appendChild(empty);
            return;
        }
        result.messages.forEach(message => {
            const own = Number(message.sender_user_id) === <?= \App\Core\Auth::id() ?>;
            const bubble = document.createElement('div');
            bubble.className = `align-self-${own ? 'end' : 'start'} p-2 px-3 rounded-3 ${own ? 'bg-primary text-white' : 'bg-light border'}`;
            bubble.style.maxWidth = '90%';
            const sender = document.createElement('strong');
            sender.className = 'd-block small mb-1';
            sender.textContent = own ? 'You' : `${message.sender_name} · Technician`;
            const text = document.createElement('div');
            text.className = 'small';
            text.style.whiteSpace = 'pre-wrap';
            text.textContent = message.message;
            const time = document.createElement('time');
            time.className = `d-block text-end mt-1 ${own ? 'text-white-50' : 'text-muted'}`;
            time.style.fontSize = '.68rem';
            time.textContent = new Date(message.created_at.replace(' ', 'T')).toLocaleString();
            bubble.append(sender, text, time);
            box.appendChild(bubble);
        });
        if (atBottom) box.scrollTop = box.scrollHeight;
    } catch (error) {
        box.textContent = error.message;
    }
}

async function sendAdminProfessionalMessage(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const input = form.elements.message;
    if (!input.value.trim()) return;
    const data = new FormData();
    data.append('pro_id', String(currentProId));
    data.append('message', input.value.trim());
    const submit = form.querySelector('button[type="submit"]');
    submit.disabled = true;
    try {
        const response = await fetch(`${appBaseUrl}/admin/kyc-verifications/chat/send`, { method: 'POST', body: data });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to send message.');
        input.value = '';
        showKycNotification(result.message || 'Message sent to technician.');
        await refreshAdminProfessionalChat();
    } catch (error) {
        showKycNotification(error.message, 'danger');
    } finally {
        submit.disabled = false;
    }
}

window.setInterval(refreshAdminProfessionalChat, 7000);

function submitKycDecision(status, reason = null) {
    const formData = new FormData();
    formData.append('pro_id', currentProId);
    formData.append('status', status);
    if (reason) {
        formData.append('reason', reason);
    }

    fetch(`${appBaseUrl}/admin/kyc-verifications/process-kyc`, {
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
            throw new Error(result.message || `KYC update failed (HTTP ${response.status}).`);
        }
        showKycNotification(result.message || `Technician KYC marked as ${status}.`);
        const modalElement = document.getElementById('kycWorkbenchModal');
        if (modalElement && window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalElement).hide();
        }
        window.setTimeout(() => location.reload(), 1800);
    })
    .catch(error => showKycNotification(`KYC decision failed: ${error.message}`, 'danger'));
}

function promptRejectKyc() {
    const reason = prompt('Please specify the rejection reason for the technician: (e.g. Aadhaar blur, name mismatch on bank passbook)');
    if (reason === null) return;
    if (reason.trim() === '') {
        alert('Rejection reason cannot be blank.');
        return;
    }
    submitKycDecision('rejected', reason.trim());
}
</script>