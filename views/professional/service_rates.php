<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/rates-zones/save-rates" method="POST" class="card-custom p-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-tag-fill text-primary me-2"></i>My Service Rate Card Overrides</h5>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">Save Overrides</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="small text-muted">
                            <tr>
                                <th>SERVICE OFFERING</th>
                                <th>BASE RATE</th>
                                <th>YOUR CUSTOM PRICE (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($s['name']) ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($s['category_name']) ?></div>
                                    </td>
                                    <td class="text-muted">₹<?= number_format((float)$s['base_price'], 2) ?></td>
                                    <td style="width: 160px;">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="1.00" name="prices[<?= $s['id'] ?>]" class="form-control" value="<?= $s['custom_price'] ?: $s['base_price'] ?>">
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom p-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Territorial Coverage</h5>
                <?php foreach ($zones as $z): ?>
                    <div class="p-3 border rounded-3 bg-light mb-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($z['name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($z['city']) ?></div>
                        </div>
                        <span class="badge <?= $z['is_covered'] ? 'badge-pill-green' : 'badge-pill-amber' ?>">
                            <?= $z['is_covered'] ? 'COVERED' : 'NOT COVERED' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>