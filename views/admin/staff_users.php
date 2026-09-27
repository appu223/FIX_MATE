<div class="container-fluid p-0">

    <!-- Flash Notifications -->
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

    <!-- Header & Action Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Staff & User Directory</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Manage administrative privileges, permissions, and customer 360 dossiers.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#staffModal" onclick="resetStaffModal()">
            <i class="bi bi-person-plus-fill me-1"></i> Add Internal Staff
        </button>
    </div>

    <!-- Main Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="userTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 py-2 rounded-pill fw-semibold" id="customers-tab" data-bs-toggle="pill" data-bs-target="#customers-pane" type="button" role="tab">
                <i class="bi bi-people-fill me-2"></i>Customer 360 Directory (<?= count($customers) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 rounded-pill fw-semibold" id="staff-tab" data-bs-toggle="pill" data-bs-target="#staff-pane" type="button" role="tab">
                <i class="bi bi-shield-lock-fill me-2"></i>Internal Staff & RBAC (<?= count($staffMembers) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 rounded-pill fw-semibold" id="technicians-tab" data-bs-toggle="pill" data-bs-target="#technicians-pane" type="button" role="tab">
                <i class="bi bi-tools me-2"></i>Technicians & Verification (<?= count($professionals) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="userTabsContent">

        <!-- TAB 1: CUSTOMER 360 DIRECTORY -->
        <div class="tab-pane fade show active" id="customers-pane" role="tabpanel">
            <div class="card-custom p-4 mb-4">
                <!-- Search & Filters -->
                <form method="GET" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/staff-users" class="row g-2 mb-3">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search customer by name, email, or phone..." value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Account Statuses</option>
                            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active Only</option>
                            <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Suspended Only</option>
                            <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive Only</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-medium">Apply Filter</button>
                        <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/staff-users" class="btn btn-outline-secondary rounded-pill px-3 fw-medium">Reset</a>
                    </div>
                </form>

                <!-- Customer Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                            <tr>
                                <th class="border-0">Customer ID</th>
                                <th class="border-0">Customer Info</th>
                                <th class="border-0">Phone Contact</th>
                                <th class="border-0 text-center">Orders</th>
                                <th class="border-0">Lifetime GMV</th>
                                <th class="border-0">Account State</th>
                                <th class="border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customers)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No customers matched your filter parameters.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($customers as $c): ?>
                                    <tr>
                                        <td class="fw-bold text-muted">#CUS-<?= str_pad((string)$c['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                    <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($c['name']) ?></div>
                                                    <div class="text-muted" style="font-size: 0.74rem;"><?= htmlspecialchars($c['email']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-dark fw-medium"><?= htmlspecialchars($c['phone']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1 rounded-pill fw-semibold">
                                                <?= $c['total_bookings'] ?> Bookings
                                            </span>
                                        </td>
                                        <td class="fw-bold text-success">
                                            ₹<?= number_format((float)$c['lifetime_spend'], 2) ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $custBadge = match($c['status']) {
                                                    'active'    => 'badge-pill-green',
                                                    'suspended' => 'badge-pill-red',
                                                    default     => 'badge-pill-amber'
                                                };
                                            ?>
                                            <span class="badge <?= $custBadge ?>">
                                                <?= strtoupper($c['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="openCustomer360(<?= $c['id'] ?>)">
                                                    <i class="bi bi-eye-fill me-1"></i> Dossier 360
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split rounded-pill ms-1" data-bs-toggle="dropdown"></button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <?php if ($c['status'] === 'active'): ?>
                                                        <li><button class="dropdown-item text-danger" onclick="toggleStatus(<?= $c['id'] ?>, 'suspended')"><i class="bi bi-slash-circle me-2"></i>Suspend Account</button></li>
                                                    <?php else: ?>
                                                        <li><button class="dropdown-item text-success" onclick="toggleStatus(<?= $c['id'] ?>, 'active')"><i class="bi bi-check2-circle me-2"></i>Re-activate Customer</button></li>
                                                    <?php endif; ?>
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

        <!-- TAB 2: INTERNAL STAFF & RBAC -->
        <div class="tab-pane fade" id="staff-pane" role="tabpanel">
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">FixMate Control Room Personnel</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Employees possessing access to administrative commands</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                            <tr>
                                <th class="border-0">Staff Name</th>
                                <th class="border-0">Email / Login ID</th>
                                <th class="border-0">Phone</th>
                                <th class="border-0">Role & Authority</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Created Date</th>
                                <th class="border-0 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($staffMembers as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($s['name']) ?></div>
                                    </td>
                                    <td class="text-muted"><?= htmlspecialchars($s['email']) ?></td>
                                    <td class="text-dark fw-medium"><?= htmlspecialchars($s['phone']) ?></td>
                                    <td>
                                        <?php if ($s['role'] === 'admin'): ?>
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold">Super Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-semibold">Operations Staff</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $s['status'] === 'active' ? 'badge-pill-green' : 'badge-pill-red' ?>">
                                            <?= strtoupper($s['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted" style="font-size: 0.78rem;"><?= date('d M Y', strtotime($s['created_at'])) ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" 
                                                onclick='editStaff(<?= json_encode($s) ?>)'>
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: TECHNICIAN INTERNAL DIRECTORY -->
        <div class="tab-pane fade" id="technicians-pane" role="tabpanel">
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Technician Directory & KYC Status</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Technician contact, verification and earnings summaries. Only an administrator can approve identity documents.</span>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications" class="btn btn-outline-primary btn-sm rounded-pill px-3">Open KYC Workbench</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:.88rem">
                        <thead class="table-light text-muted text-uppercase" style="font-size:.72rem">
                            <tr><th>Technician</th><th>Contact</th><th>KYC</th><th>Account</th><th>Work Status</th><th>Jobs / Proof Queue</th><th>Wallet</th><th class="text-end">Action</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($professionals)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No technician profiles found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($professionals as $pro): ?>
                                    <?php $proKycBadge = match ($pro['kyc_status']) { 'verified' => 'badge-pill-green', 'rejected' => 'badge-pill-red', default => 'badge-pill-amber' }; ?>
                                    <tr>
                                        <td><div class="fw-semibold"><?= htmlspecialchars($pro['name'], ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted">PRO-<?= str_pad((string)$pro['pro_id'], 4, '0', STR_PAD_LEFT) ?> · <?= (int)$pro['experience_years'] ?> yrs</div></td>
                                        <td><div><?= htmlspecialchars($pro['email'], ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted"><?= htmlspecialchars($pro['phone'], ENT_QUOTES, 'UTF-8') ?></div></td>
                                        <td><span class="badge <?= $proKycBadge ?>"><?= htmlspecialchars(strtoupper($pro['kyc_status']), ENT_QUOTES, 'UTF-8') ?></span><?php if ($pro['kyc_status'] === 'rejected' && !empty($pro['kyc_rejected_reason'])): ?><div class="small text-danger mt-1"><?= htmlspecialchars($pro['kyc_rejected_reason'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></td>
                                        <td><span class="badge <?= $pro['account_status'] === 'active' ? 'badge-pill-green' : 'badge-pill-amber' ?>"><?= htmlspecialchars(strtoupper($pro['account_status']), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><span class="badge <?= (int)$pro['active_jobs'] > 0 ? 'badge-pill-blue' : 'bg-light text-secondary border' ?>"><?= (int)$pro['active_jobs'] > 0 ? 'ON ACTIVE JOB' : 'NO ACTIVE JOB' ?></span></td>
                                        <td><?= (int)$pro['total_jobs'] ?> total<?php if ((int)$pro['pending_proof_reviews'] > 0): ?><div class="small text-warning fw-semibold"><i class="bi bi-camera-fill me-1"></i><?= (int)$pro['pending_proof_reviews'] ?> proof(s) need review</div><?php else: ?><div class="small text-muted">No pending proof reviews</div><?php endif; ?></td>
                                        <td class="fw-bold text-success">₹<?= number_format((float)$pro['wallet_balance'], 2) ?></td>
                                        <td class="text-end"><a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/kyc-verifications?search=<?= rawurlencode($pro['email']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Inspect dossier</a></td>
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

<!-- MODAL: ADD / EDIT STAFF -->
<div class="modal fade" id="staffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/staff-users/save-staff" method="POST" class="modal-content card-custom border-0 shadow">
            <input type="hidden" name="id" id="staff_id" value="">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="staffModalTitle">Add Internal Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Legal Name</label>
                    <input type="text" name="name" id="staff_name" class="form-control" required placeholder="e.g. Rahul Sharma">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Official Email</label>
                    <input type="email" name="email" id="staff_email" class="form-control" required placeholder="e.g. rahul@fixmate.in">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="text" name="phone" id="staff_phone" class="form-control" required placeholder="e.g. +919876543210">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Role Authority</label>
                        <select name="role" id="staff_role" class="form-select">
                            <option value="staff">Operations Staff</option>
                            <option value="admin">Super Admin</option>
                            <option value="professional">Technician (Pending KYC)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" id="staff_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Account Password</label>
                    <input type="password" name="password" id="staff_password" class="form-control" placeholder="Leave blank to keep existing password">
                    <span class="text-muted" id="staff_password_help" style="font-size: 0.72rem;">Minimum 8 chars. Defaults to 'Password@123' if creating fresh.</span>
                </div>
                <div class="alert alert-info small d-none mb-0" id="technician_role_note"><i class="bi bi-info-circle me-1"></i>Creates a technician profile in the pending KYC queue. The technician cannot access the partner panel until verified.</div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Staff Account</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CUSTOMER 360 DOSSIER -->
<div class="modal fade" id="customer360Modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-bottom bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="c360_name">Customer Dossier</h5>
                        <span class="text-muted" id="c360_id_tag" style="font-size: 0.75rem;">FixMate 360 View</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="c360_body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="text-muted mt-2">Loading Customer 360 profile...</div>
                </div>
            </div>
            <div class="modal-footer border-top bg-white">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function syncStaffRoleForm(isEditing = false) {
    const role = document.getElementById('staff_role');
    const technicianOption = role.querySelector('option[value="professional"]');
    const isTechnician = role.value === 'professional';
    const password = document.getElementById('staff_password');
    const passwordHelp = document.getElementById('staff_password_help');

    technicianOption.disabled = isEditing;
    document.getElementById('technician_role_note').classList.toggle('d-none', !isTechnician);
    password.required = isTechnician;
    password.placeholder = isTechnician ? 'Set temporary technician password' : 'Leave blank to keep existing password';
    passwordHelp.textContent = isTechnician
        ? 'Required, at least 8 characters. Share it securely; KYC approval is required before sign-in.'
        : "Minimum 8 chars. Defaults to 'Password@123' if creating fresh.";
    document.querySelector('#staffModal button[type="submit"]').textContent = isTechnician
        ? 'Create Technician & Send to KYC'
        : 'Save Staff Account';
}

function resetStaffModal() {
    document.getElementById('staffModalTitle').innerText = 'Add Internal Staff';
    document.getElementById('staff_id').value = '';
    document.getElementById('staff_name').value = '';
    document.getElementById('staff_email').value = '';
    document.getElementById('staff_phone').value = '';
    document.getElementById('staff_role').value = 'staff';
    document.getElementById('staff_status').value = 'active';
    document.getElementById('staff_password').value = '';
    syncStaffRoleForm(false);
}

function editStaff(staff) {
    document.getElementById('staffModalTitle').innerText = 'Edit Staff Member';
    document.getElementById('staff_id').value = staff.id;
    document.getElementById('staff_name').value = staff.name;
    document.getElementById('staff_email').value = staff.email;
    document.getElementById('staff_phone').value = staff.phone;
    document.getElementById('staff_role').value = staff.role;
    document.getElementById('staff_status').value = staff.status;
    document.getElementById('staff_password').value = '';
    syncStaffRoleForm(true);

    const modal = new bootstrap.Modal(document.getElementById('staffModal'));
    modal.show();
}

document.getElementById('staff_role').addEventListener('change', () => syncStaffRoleForm(false));

function toggleStatus(userId, targetStatus) {
    if (!confirm(`Are you sure you want to mark this account as ${targetStatus}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', targetStatus);

    fetch(`${appBaseUrl}/admin/staff-users/toggle-status`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Action Failed: ' + data.message);
        }
    })
    .catch(() => alert('Network error occurred while updating status.'));
}

function openCustomer360(customerId) {
    const modalEl = document.getElementById('customer360Modal');
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('c360_body');
    modal.show();

    fetch(`${appBaseUrl}/admin/staff-users/customer-360?customer_id=${customerId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                body.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                return;
            }
            const data = res.data;
            document.getElementById('c360_name').innerText = data.profile.name;
            document.getElementById('c360_id_tag').innerText = `Customer ID #CUS-${String(data.profile.id).padStart(4, '0')} | Joined ${new Date(data.profile.created_at).toLocaleDateString()}`;

            let addressesHtml = '';
            if (data.addresses.length === 0) {
                addressesHtml = '<div class="text-muted small">No saved addresses found.</div>';
            } else {
                addressesHtml = data.addresses.map(a => `
                    <div class="p-2 border rounded-3 mb-2 bg-light">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-secondary mb-1">${a.label} ${a.is_default ? '(Default)' : ''}</span>
                        </div>
                        <div class="text-dark small">${a.address_line1}, ${a.address_line2 || ''}</div>
                        <div class="text-muted small">${a.city}, ${a.state} - ${a.postal_code}</div>
                    </div>
                `).join('');
            }

            let bookingsHtml = '';
            if (data.bookings.length === 0) {
                bookingsHtml = '<tr><td colspan="5" class="text-center text-muted small py-3">No bookings placed yet.</td></tr>';
            } else {
                bookingsHtml = data.bookings.map(b => `
                    <tr>
                        <td class="fw-bold text-primary">${b.booking_code}</td>
                        <td class="small">${b.scheduled_date}</td>
                        <td class="small">${b.pro_name}</td>
                        <td class="fw-semibold">₹${Number(b.total_amount).toFixed(2)}</td>
                        <td><span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">${b.status.toUpperCase()}</span></td>
                    </tr>
                `).join('');
            }

            body.innerHTML = `
                <!-- Quick KPI Metrics -->
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="card-custom p-3 kpi-green">
                            <div class="text-muted small">Total GMV</div>
                            <h4 class="fw-bold m-0 text-dark">₹${Number(data.finances.total_spent).toFixed(2)}</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card-custom p-3 kpi-blue">
                            <div class="text-muted small">Total Bookings</div>
                            <h4 class="fw-bold m-0 text-dark">${data.finances.total_orders}</h4>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card-custom p-3 kpi-purple">
                            <div class="text-muted small">Account State</div>
                            <h5 class="fw-bold m-0 text-uppercase text-dark">${data.profile.status}</h5>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-5">
                        <h6 class="fw-bold text-dark border-bottom pb-2">Customer Profile</h6>
                        <div class="small mb-1"><strong>Email:</strong> ${data.profile.email}</div>
                        <div class="small mb-3"><strong>Phone:</strong> ${data.profile.phone}</div>

                        <h6 class="fw-bold text-dark border-bottom pb-2 mt-3">Address Book (${data.addresses.length})</h6>
                        ${addressesHtml}
                    </div>

                    <div class="col-md-7">
                        <h6 class="fw-bold text-dark border-bottom pb-2">Order History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle">
                                <thead>
                                    <tr class="text-muted" style="font-size: 0.72rem;">
                                        <th>CODE</th>
                                        <th>DATE</th>
                                        <th>PRO</th>
                                        <th>AMOUNT</th>
                                        <th>STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${bookingsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(() => {
            body.innerHTML = `<div class="alert alert-danger">Failed to fetch customer 360 data.</div>`;
        });
}
</script>