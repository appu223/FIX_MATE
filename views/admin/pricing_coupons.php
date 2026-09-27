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
            <h3 class="fw-bold mb-1">Pricing Rules & Promotions Engine</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Manage discount vouchers, minimum booking thresholds, and dynamic surge multipliers.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="resetCouponModal()">
            <i class="bi bi-plus-circle me-1"></i> Create Coupon Promo
        </button>
    </div>

    <!-- Row 1: Global Pricing Threshold Cards & Configuration -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card-custom p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <div>
                        <h6 class="fw-bold m-0 text-dark"><i class="bi bi-sliders text-primary me-2"></i>Platform Fee Thresholds & Dynamic Surge Rules</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Calculated at checkout before taxes and applied across all customer orders.</span>
                    </div>
                </div>

                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/pricing-rules/update-global-rules" method="POST" class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold">Minimum Order Fee (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="1.00" name="min_booking_fee" class="form-control" value="<?= htmlspecialchars($rules['min_booking_fee']) ?>" required>
                        </div>
                        <span class="text-muted" style="font-size: 0.72rem;">Orders below this value are adjusted up to the minimum.</span>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold">Platform Convenience Fee (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="1.00" name="platform_convenience_fee" class="form-control" value="<?= htmlspecialchars($rules['platform_convenience_fee']) ?>" required>
                        </div>
                        <span class="text-muted" style="font-size: 0.72rem;">Flat facilitation fee added to each dispatch request.</span>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold">Weekend Peak Multiplier (Saturday / Sunday)</label>
                        <div class="input-group">
                            <input type="number" step="0.05" name="weekend_surge_multiplier" class="form-control" value="<?= htmlspecialchars($rules['weekend_surge_multiplier']) ?>" required>
                            <span class="input-group-text">x</span>
                        </div>
                        <span class="text-muted" style="font-size: 0.72rem;">e.g., 1.10 = 10% peak surge applied on weekends.</span>
                    </div>

                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold">Emergency Express Multiplier (Under 2 hrs)</label>
                        <div class="input-group">
                            <input type="number" step="0.05" name="emergency_booking_multiplier" class="form-control" value="<?= htmlspecialchars($rules['emergency_booking_multiplier']) ?>" required>
                            <span class="input-group-text">x</span>
                        </div>
                        <span class="text-muted" style="font-size: 0.72rem;">Immediate dispatch surcharge multiplier.</span>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Update Pricing Thresholds
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card-custom p-4 h-100 kpi-purple">
                <div class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">PROMOTIONAL VOLUME</div>
                <?php 
                    $totalDiscounts = array_sum(array_column($coupons, 'total_discount_granted'));
                    $totalUses = array_sum(array_column($coupons, 'actual_redemptions'));
                ?>
                <h3 class="fw-bold mt-2 text-dark">₹<?= number_format((float)$totalDiscounts, 2) ?></h3>
                <div class="text-muted small mb-3">Total discount subsidies granted to customers</div>

                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Total Coupons Configured:</span>
                        <span class="fw-bold text-dark"><?= count($coupons) ?> Promo Codes</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Total Successful Redemptions:</span>
                        <span class="fw-bold text-success"><?= $totalUses ?> Orders</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Promo Codes Table -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="fw-bold m-0 text-dark">Active Coupon Promotions</h6>
                <span class="text-muted" style="font-size: 0.78rem;">Discount codes eligible for entry at customer checkout</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th class="border-0">Promo Code</th>
                        <th class="border-0">Discount Value</th>
                        <th class="border-0">Min Order Required</th>
                        <th class="border-0">Max Discount</th>
                        <th class="border-0 text-center">Usage / Limit</th>
                        <th class="border-0">Subsidies Granted</th>
                        <th class="border-0">Validity Period</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No coupon codes created yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold font-monospace" style="font-size: 0.85rem;">
                                        <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($c['code']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">
                                    <?php if ($c['discount_type'] === 'percentage'): ?>
                                        <?= number_format((float)$c['discount_value'], 1) ?>% OFF
                                    <?php else: ?>
                                        ₹<?= number_format((float)$c['discount_value'], 2) ?> FLAT
                                    <?php endif; ?>
                                </td>
                                <td>₹<?= number_format((float)$c['min_booking_value'], 2) ?></td>
                                <td>
                                    <?= $c['max_discount'] ? '₹' . number_format((float)$c['max_discount'], 2) : '<span class="text-muted">No Cap</span>' ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        <?= $c['actual_redemptions'] ?> / <?= $c['usage_limit'] ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-success">
                                    ₹<?= number_format((float)$c['total_discount_granted'], 2) ?>
                                </td>
                                <td class="small text-muted">
                                    <div><?= date('d M Y', strtotime($c['valid_from'])) ?> to</div>
                                    <div><?= date('d M Y', strtotime($c['valid_until'])) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $c['status'] === 'active' ? 'badge-pill-green' : 'badge-pill-red' ?>">
                                        <?= strtoupper($c['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick='editCoupon(<?= json_encode($c) ?>)'>
                                        <i class="bi bi-pencil-square me-1"></i> Edit
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

<!-- MODAL: CREATE / EDIT COUPON -->
<div class="modal fade" id="couponModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/pricing-rules/save-coupon" method="POST" class="modal-content card-custom border-0 shadow">
            <input type="hidden" name="id" id="cpn_id" value="">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="couponModalTitle">Create Promo Coupon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Promotional Code (e.g. FIX20)</label>
                    <input type="text" name="code" id="cpn_code" class="form-control text-uppercase font-monospace fw-bold" required placeholder="FIX20">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Discount Type</label>
                        <select name="discount_type" id="cpn_discount_type" class="form-select">
                            <option value="percentage">Percentage (%)</option>
                            <option value="flat">Flat Cash (₹)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Discount Value</label>
                        <input type="number" step="0.5" name="discount_value" id="cpn_discount_value" class="form-control" required placeholder="20.00">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Min Order (₹)</label>
                        <input type="number" step="1.00" name="min_booking_value" id="cpn_min_booking_value" class="form-control" value="499.00">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Max Cap (₹, for %)</label>
                        <input type="number" step="1.00" name="max_discount" id="cpn_max_discount" class="form-control" placeholder="250.00">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Total Usage Limit</label>
                        <input type="number" name="usage_limit" id="cpn_usage_limit" class="form-control" value="1000">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" id="cpn_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Valid From</label>
                        <input type="datetime-local" name="valid_from" id="cpn_valid_from" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Valid Until</label>
                        <input type="datetime-local" name="valid_until" id="cpn_valid_until" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetCouponModal() {
    document.getElementById('couponModalTitle').innerText = 'Create Promo Coupon';
    document.getElementById('cpn_id').value = '';
    document.getElementById('cpn_code').value = '';
    document.getElementById('cpn_discount_type').value = 'percentage';
    document.getElementById('cpn_discount_value').value = '20.00';
    document.getElementById('cpn_min_booking_value').value = '499.00';
    document.getElementById('cpn_max_discount').value = '250.00';
    document.getElementById('cpn_usage_limit').value = '1000';
    document.getElementById('cpn_status').value = 'active';

    const now = new Date().toISOString().slice(0, 16);
    const endOfYear = new Date(new Date().getFullYear(), 11, 31, 23, 59).toISOString().slice(0, 16);
    document.getElementById('cpn_valid_from').value = now;
    document.getElementById('cpn_valid_until').value = endOfYear;
}

function editCoupon(c) {
    document.getElementById('couponModalTitle').innerText = 'Edit Promo Coupon';
    document.getElementById('cpn_id').value = c.id;
    document.getElementById('cpn_code').value = c.code;
    document.getElementById('cpn_discount_type').value = c.discount_type;
    document.getElementById('cpn_discount_value').value = c.discount_value;
    document.getElementById('cpn_min_booking_value').value = c.min_booking_value;
    document.getElementById('cpn_max_discount').value = c.max_discount || '';
    document.getElementById('cpn_usage_limit').value = c.usage_limit;
    document.getElementById('cpn_status').value = c.status;
    document.getElementById('cpn_valid_from').value = c.valid_from.replace(' ', 'T').slice(0, 16);
    document.getElementById('cpn_valid_until').value = c.valid_until.replace(' ', 'T').slice(0, 16);

    const modal = new bootstrap.Modal(document.getElementById('couponModal'));
    modal.show();
}
</script>