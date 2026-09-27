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

    <!-- Title & Action -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Service Territories & Technician Mapping</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Geographic zone boundaries, dynamic multipliers, and technician rate card overrides.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#zoneModal" onclick="resetZoneModal()">
                <i class="bi bi-geo-alt-fill me-1"></i> Add Service Zone
            </button>
        </div>
    </div>

    <!-- Active Service Zones Grid -->
    <div class="row g-3 mb-4">
        <?php foreach ($zones as $z): ?>
            <?php 
                $pins = json_decode($z['postal_codes_json'], true) ?: [];
                $surgeColor = ((float)$z['surge_multiplier'] > 1.0) ? 'kpi-amber' : 'kpi-blue';
            ?>
            <div class="col-12 col-md-4">
                <div class="card-custom p-4 h-100 <?= $surgeColor ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill small mb-1">
                                <?= htmlspecialchars($z['city']) ?>, <?= htmlspecialchars($z['state']) ?>
                            </span>
                            <h5 class="fw-bold m-0 text-dark"><?= htmlspecialchars($z['name']) ?></h5>
                        </div>
                        <button class="btn btn-sm btn-light border rounded-pill px-2" onclick='editZone(<?= json_encode($z) ?>, <?= json_encode($pins) ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>

                    <div class="d-flex gap-3 my-3">
                        <div>
                            <div class="text-muted" style="font-size: 0.72rem;">SURGE MULTIPLIER</div>
                            <div class="fw-bold fs-5 text-dark"><?= number_format((float)$z['surge_multiplier'], 2) ?>x</div>
                        </div>
                        <div class="border-start ps-3">
                            <div class="text-muted" style="font-size: 0.72rem;">ACTIVE PROS</div>
                            <div class="fw-bold fs-5 text-primary"><?= $z['assigned_pros_count'] ?> Pros</div>
                        </div>
                        <div class="border-start ps-3">
                            <div class="text-muted" style="font-size: 0.72rem;">LIFETIME BOOKINGS</div>
                            <div class="fw-bold fs-5 text-success"><?= $z['total_zone_bookings'] ?></div>
                        </div>
                    </div>

                    <div class="border-top pt-2">
                        <span class="text-muted small fw-semibold">Covered Postal PIN Codes:</span>
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            <?php foreach ($pins as $pin): ?>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small"><?= htmlspecialchars($pin) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Technician Mapping Workbench -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold m-0 text-dark">Verified Technicians & Territorial Roster</h6>
                <span class="text-muted" style="font-size: 0.78rem;">Configure which zones each technician is dispatched to and manage custom price overrides.</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th class="border-0">Technician Details</th>
                        <th class="border-0">Experience & Rating</th>
                        <th class="border-0">Commission Rate</th>
                        <th class="border-0">Wallet</th>
                        <th class="border-0 text-end">Mapping Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pros as $p): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-dark text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.8rem;">
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
                                <div class="text-warning small"><i class="bi bi-star-fill"></i> <?= number_format((float)$p['rating_avg'], 2) ?></div>
                            </td>
                            <td class="fw-bold text-primary"><?= number_format((float)$p['commission_rate'], 2) ?>%</td>
                            <td class="fw-bold text-dark">₹<?= number_format((float)$p['wallet_balance'], 2) ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="openProMappingModal(<?= $p['pro_id'] ?>, '<?= htmlspecialchars(addslashes($p['name'])) ?>')">
                                    <i class="bi bi-pin-map-fill me-1"></i> Zones & Rate Card
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: CREATE / EDIT SERVICE ZONE -->
<div class="modal fade" id="zoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/zones/save-zone" method="POST" class="modal-content card-custom border-0 shadow">
            <input type="hidden" name="id" id="zone_id" value="">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="zoneModalTitle">Add Service Zone</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Zone Territory Name</label>
                    <input type="text" name="name" id="zone_name" class="form-control" required placeholder="e.g. Bengaluru North (Hebbal / Yelahanka)">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" id="zone_city" class="form-control" value="Bengaluru" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">State</label>
                        <input type="text" name="state" id="zone_state" class="form-control" value="Karnataka" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Surge Multiplier</label>
                        <input type="number" step="0.05" name="surge_multiplier" id="zone_surge" class="form-control" value="1.00">
                        <span class="text-muted" style="font-size: 0.72rem;">1.00 = standard base pricing.</span>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" id="zone_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold">Covered Postal PIN Codes (Comma separated)</label>
                    <textarea name="postal_codes" id="zone_postal_codes" rows="3" class="form-control" placeholder="560001, 560025, 560038" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Territory</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: PRO ZONE & RATE CARD OVERRIDE WORKBENCH -->
<div class="modal fade" id="proMappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-bottom bg-light">
                <div>
                    <h5 class="modal-title fw-bold m-0" id="pmm_title">Technician Mapping & Rates</h5>
                    <span class="text-muted" style="font-size: 0.75rem;">Territory coverage assignment and per-service custom pricing</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="pmm_body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer border-top bg-white">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<script>
const appBaseUrl = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function resetZoneModal() {
    document.getElementById('zoneModalTitle').innerText = 'Add Service Zone';
    document.getElementById('zone_id').value = '';
    document.getElementById('zone_name').value = '';
    document.getElementById('zone_city').value = 'Bengaluru';
    document.getElementById('zone_state').value = 'Karnataka';
    document.getElementById('zone_surge').value = '1.00';
    document.getElementById('zone_status').value = 'active';
    document.getElementById('zone_postal_codes').value = '';
}

function editZone(zone, pinArray) {
    document.getElementById('zoneModalTitle').innerText = 'Edit Service Zone';
    document.getElementById('zone_id').value = zone.id;
    document.getElementById('zone_name').value = zone.name;
    document.getElementById('zone_city').value = zone.city;
    document.getElementById('zone_state').value = zone.state;
    document.getElementById('zone_surge').value = zone.surge_multiplier;
    document.getElementById('zone_status').value = zone.status;
    document.getElementById('zone_postal_codes').value = pinArray.join(', ');

    const modal = new bootstrap.Modal(document.getElementById('zoneModal'));
    modal.show();
}

function openProMappingModal(proId, proName) {
    const modalEl = document.getElementById('proMappingModal');
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('pmm_body');
    document.getElementById('pmm_title').innerText = `${proName} - Territory & Rate Override`;
    modal.show();

    fetch(`${appBaseUrl}/admin/zones/pro-mappings?pro_id=${proId}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                body.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                return;
            }

            const zones = res.data.zones;
            const services = res.data.services;

            let zoneCheckboxes = zones.map(z => `
                <div class="col-md-6 mb-2">
                    <div class="form-check p-2 border rounded-3 bg-light">
                        <input class="form-check-input ms-1" type="checkbox" name="zone_ids[]" value="${z.id}" id="zone_chk_${z.id}" ${z.is_assigned ? 'checked' : ''}>
                        <label class="form-check-label fw-semibold text-dark ms-2" for="zone_chk_${z.id}">
                            ${z.name} <span class="text-muted small">(${z.city})</span>
                        </label>
                    </div>
                </div>
            `).join('');

            let serviceRows = services.map(s => `
                <tr>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="svc_chk_${s.service_id}" ${s.is_offered ? 'checked' : ''} onchange="updateProRate(${proId}, ${s.service_id})">
                            <label class="form-check-label fw-semibold text-dark" for="svc_chk_${s.service_id}">
                                ${s.service_name}
                            </label>
                        </div>
                        <span class="text-muted small ms-4">${s.category_name}</span>
                    </td>
                    <td class="text-muted">₹${Number(s.base_price).toFixed(2)}</td>
                    <td style="width: 170px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="1.00" class="form-control" id="pro_price_${s.service_id}" value="${s.custom_price || s.base_price}">
                            <button class="btn btn-outline-primary" type="button" onclick="updateProRate(${proId}, ${s.service_id})">Set</button>
                        </div>
                    </td>
                </tr>
            `).join('');

            body.innerHTML = `
                <!-- Form 1: Zone Assignments -->
                <form action="${appBaseUrl}/admin/zones/save-mapping" method="POST" class="mb-4 pb-3 border-bottom">
                    <input type="hidden" name="pro_id" value="${proId}">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-geo-alt me-1 text-primary"></i> Territorial Coverage Zones</h6>
                    <div class="row g-2 mb-3">
                        ${zoneCheckboxes}
                    </div>
                    <button type="submit" class="btn btn-sm btn-dark rounded-pill px-4">Save Zone Assignments</button>
                </form>

                <!-- Section 2: Rate Card Overrides -->
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-tags me-1 text-primary"></i> Service Rate Card Overrides</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="text-muted small">
                            <tr>
                                <th>SERVICE TITLE</th>
                                <th>DEFAULT RATE</th>
                                <th>PRO CUSTOM RATE (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${serviceRows}
                        </tbody>
                    </table>
                </div>
            `;
        })
        .catch(() => {
            body.innerHTML = `<div class="alert alert-danger">Error retrieving technician mapping.</div>`;
        });
}

function updateProRate(proId, serviceId) {
    const isOffered = document.getElementById(`svc_chk_${serviceId}`).checked ? 1 : 0;
    const customPrice = document.getElementById(`pro_price_${serviceId}`).value;

    const formData = new FormData();
    formData.append('pro_id', proId);
    formData.append('service_id', serviceId);
    formData.append('is_offered', isOffered);
    formData.append('custom_price', customPrice);

    fetch(`${appBaseUrl}/admin/zones/save-rate-override`, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (!res.success) {
            alert('Failed to update rate: ' + res.message);
        }
    })
    .catch(() => alert('Network error updating pro rate.'));
}
</script>