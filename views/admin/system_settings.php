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
            <h3 class="fw-bold mb-1">System Configuration & Audit Logs</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Configure global platform defaults, manage system banners, and monitor immutable security logs.</p>
        </div>
    </div>

    <!-- Navigation Pills Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="settingsTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active px-4 py-2 rounded-pill fw-semibold" data-bs-toggle="pill" data-bs-target="#config-tab-pane" type="button">
                <i class="bi bi-sliders me-2"></i>Platform Parameters
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link px-4 py-2 rounded-pill fw-semibold" data-bs-toggle="pill" data-bs-target="#announcements-tab-pane" type="button">
                <i class="bi bi-megaphone-fill me-2"></i>System Announcement Banner
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link px-4 py-2 rounded-pill fw-semibold" data-bs-toggle="pill" data-bs-target="#audit-tab-pane" type="button">
                <i class="bi bi-journal-text me-2"></i>Immutable Audit Trail (<?= count($auditLogs) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- TAB 1: PLATFORM PARAMETERS -->
        <div class="tab-pane fade show active" id="config-tab-pane">
            <div class="card-custom p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Core Platform & Financial Configuration
                </h6>

                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/settings/save-config" method="POST">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Platform Public Title</label>
                            <input type="text" name="platform_name" class="form-control" value="<?= htmlspecialchars($settings['platform_name'] ?? 'FixMate India') ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">GST Tax Rate (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.5" name="tax_gst_pct" class="form-control" value="<?= htmlspecialchars($settings['tax_gst_pct'] ?? '18.00') ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Official Support Email</label>
                            <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? 'support@fixmate.in') ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Customer Helpline / Toll-Free</label>
                            <input type="text" name="contact_phone" class="form-control" value="<?= htmlspecialchars($settings['contact_phone'] ?? '+91 80 4920 1800') ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Base Platform Commission (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="base_commission_pct" class="form-control" value="<?= htmlspecialchars($settings['base_commission_pct'] ?? '15.00') ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Minimum Payout Withdrawal (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="10.00" name="payout_min_limit" class="form-control" value="<?= htmlspecialchars($settings['payout_min_limit'] ?? '1000.00') ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Default Dispatch Routing Mode</label>
                            <select name="emergency_dispatch_mode" class="form-select">
                                <option value="manual" <?= ($settings['emergency_dispatch_mode'] ?? '') === 'manual' ? 'selected' : '' ?>>Manual Staff Dispatch</option>
                                <option value="auto" <?= ($settings['emergency_dispatch_mode'] ?? '') === 'auto' ? 'selected' : '' ?>>Automated Nearest-Pro Match</option>
                            </select>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            <i class="bi bi-save me-1"></i> Save Platform Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: SYSTEM ANNOUNCEMENT BANNER -->
        <div class="tab-pane fade" id="announcements-tab-pane">
            <div class="card-custom p-4 mb-4">
                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="bi bi-broadcast text-primary me-2"></i>Global Banner Announcement
                </h6>
                <p class="text-muted small">Broadcast urgent maintenance alerts or seasonal holiday greetings to all platform web users.</p>

                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/settings/save-announcement" method="POST">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="announcement_enabled" id="ann_toggle" <?= ($settings['announcement_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold small" for="ann_toggle">Enable Live Banner on Topbar</label>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Alert Banner Style</label>
                            <select name="announcement_type" class="form-select">
                                <option value="info" <?= ($settings['announcement_type'] ?? '') === 'info' ? 'selected' : '' ?>>Info (Blue Notice)</option>
                                <option value="warning" <?= ($settings['announcement_type'] ?? '') === 'warning' ? 'selected' : '' ?>>Warning (Amber Maintenance)</option>
                                <option value="danger" <?= ($settings['announcement_type'] ?? '') === 'danger' ? 'selected' : '' ?>>Danger (Red Emergency Notice)</option>
                                <option value="success" <?= ($settings['announcement_type'] ?? '') === 'success' ? 'selected' : '' ?>>Success (Green Festive Promotion)</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small fw-bold">Announcement Headline & Text</label>
                            <input type="text" name="announcement_message" class="form-control" value="<?= htmlspecialchars($settings['announcement_message'] ?? 'Festive Special: Get flat 20% off on all deep cleaning services with code FIX20!') ?>" placeholder="Type message here...">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">
                            <i class="bi bi-megaphone me-1"></i> Update Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 3: IMMUTABLE AUDIT TRAIL -->
        <div class="tab-pane fade" id="audit-tab-pane">
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold m-0 text-dark">Security Activity & Audit Trail</h6>
                        <span class="text-muted" style="font-size: 0.78rem;">Immutable event log capturing every administrative decision, status change, and financial override.</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                            <tr>
                                <th class="border-0">Timestamp</th>
                                <th class="border-0">Actor</th>
                                <th class="border-0">Action Event</th>
                                <th class="border-0">Entity Target</th>
                                <th class="border-0">IP Address</th>
                                <th class="border-0">Parameters Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No audit events recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($auditLogs as $log): ?>
                                    <tr>
                                        <td class="text-muted small">
                                            <?= date('d M Y, h:i:s A', strtotime($log['created_at'])) ?>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($log['actor_name']) ?></div>
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill small"><?= strtoupper($log['actor_role']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark-subtle text-dark border rounded-pill px-2 py-1 font-monospace">
                                                <?= htmlspecialchars($log['action']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-medium"><?= htmlspecialchars($log['entity_type']) ?></span>
                                            <?= $log['entity_id'] ? "<span class='text-muted'>(#{$log['entity_id']})</span>" : '' ?>
                                        </td>
                                        <td class="font-monospace text-muted small">
                                            <?= htmlspecialchars($log['ip_address']) ?>
                                        </td>
                                        <td>
                                            <?php if ($log['new_values']): ?>
                                                <code class="text-dark small" style="font-size: 0.75rem;">
                                                    <?= htmlspecialchars(substr($log['new_values'], 0, 100)) ?><?= strlen($log['new_values']) > 100 ? '...' : '' ?>
                                                </code>
                                            <?php else: ?>
                                                <span class="text-muted small">None</span>
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
    </div>
</div>