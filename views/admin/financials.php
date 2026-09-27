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

    <!-- Title Row -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Financials, Invoicing & Payouts</h3>
            <p class="text-muted m-0" style="font-size: 0.9rem;">Inspect real-time gateway transactions, audit tax invoices, and disburse technician payouts.</p>
        </div>
    </div>

    <!-- Payout Requests Section -->
    <?php if (!empty($payouts)): ?>
        <div class="card-custom p-4 mb-4 border-warning">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="fw-bold m-0 text-dark"><i class="bi bi-wallet2 text-warning me-2"></i>Technician Payout Clearance Queue</h6>
                    <span class="text-muted" style="font-size: 0.78rem;">Earnings withdrawal requests submitted by active service partners.</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                        <tr>
                            <th class="border-0">Technician</th>
                            <th class="border-0">Bank & IFSC Credentials</th>
                            <th class="border-0">Requested Amount</th>
                            <th class="border-0">Current Wallet</th>
                            <th class="border-0">Request Status</th>
                            <th class="border-0 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payouts as $p): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['pro_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($p['pro_phone']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?= htmlspecialchars($p['bank_name'] ?? 'Not set') ?></div>
                                    <div class="text-muted font-monospace small">A/C: <?= htmlspecialchars($p['bank_account_no'] ?? 'N/A') ?> | IFSC: <?= htmlspecialchars($p['bank_ifsc'] ?? 'N/A') ?></div>
                                </td>
                                <td class="fw-bold text-success fs-6">
                                    ₹<?= number_format((float)$p['amount'], 2) ?>
                                </td>
                                <td class="fw-semibold text-dark">
                                    ₹<?= number_format((float)$p['wallet_balance'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $p['status'] === 'pending' ? 'badge-pill-amber' : ($p['status'] === 'approved' ? 'badge-pill-green' : 'badge-pill-red') ?>">
                                        <?= strtoupper($p['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <?php if ($p['status'] === 'pending'): ?>
                                        <div class="d-flex justify-content-end gap-1">
                                            <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/financials/process-payout" method="POST" class="d-inline" onsubmit="return confirm('Confirm electronic payout transfer?');">
                                                <input type="hidden" name="payout_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="decision" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                    <i class="bi bi-check2-circle me-1"></i> Disburse
                                                </button>
                                            </form>
                                            <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/financials/process-payout" method="POST" class="d-inline" onsubmit="return confirm('Reject this withdrawal request?');">
                                                <input type="hidden" name="payout_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="decision" value="rejected">
                                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted font-monospace small"><?= htmlspecialchars($p['payout_reference'] ?? 'PROCESSED') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Master Transaction Ledger -->
    <div class="card-custom p-4">
        <!-- Filter Toolbar -->
        <form method="GET" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/financials" class="row g-2 mb-3">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="TXN Reference, Booking Code, or User Name..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="type" class="form-select">
                    <option value="">All Transaction Types</option>
                    <option value="payment" <?= $typeFilter === 'payment' ? 'selected' : '' ?>>Customer Inbound Payments</option>
                    <option value="payout" <?= $typeFilter === 'payout' ? 'selected' : '' ?>>Technician Payouts</option>
                    <option value="refund" <?= $typeFilter === 'refund' ? 'selected' : '' ?>>Refunds Reversals</option>
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill px-4 fw-medium">Filter Ledger</button>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/financials" class="btn btn-outline-secondary rounded-pill px-3 fw-medium">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light text-muted" style="font-size: 0.75rem; text-transform: uppercase;">
                    <tr>
                        <th class="border-0">Txn Reference</th>
                        <th class="border-0">Party / User</th>
                        <th class="border-0">Booking Ref</th>
                        <th class="border-0">Transaction Type</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0">Gateway</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Timestamp</th>
                        <th class="border-0 text-end">Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No transactions recorded matching search parameters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="fw-bold font-monospace text-primary">
                                    <?= htmlspecialchars($t['txn_reference']) ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($t['user_name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;"><?= htmlspecialchars($t['user_role']) ?></div>
                                </td>
                                <td>
                                    <?php if ($t['booking_code']): ?>
                                        <span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($t['booking_code']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Direct</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                        $typeBadge = match($t['type']) {
                                            'payment' => 'badge-pill-green',
                                            'payout'  => 'badge-pill-blue',
                                            'refund'  => 'badge-pill-red',
                                            default   => 'bg-secondary text-white'
                                        };
                                    ?>
                                    <span class="badge <?= $typeBadge ?>">
                                        <?= strtoupper($t['type']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold <?= $t['type'] === 'refund' ? 'text-danger' : 'text-dark' ?>">
                                    <?= $t['type'] === 'refund' ? '- ' : '' ?>₹<?= number_format((float)$t['amount'], 2) ?>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($t['payment_gateway']) ?></td>
                                <td>
                                    <span class="badge <?= $t['status'] === 'success' ? 'badge-pill-green' : 'badge-pill-amber' ?>">
                                        <?= strtoupper($t['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= date('d M Y, h:i A', strtotime($t['created_at'])) ?></td>
                                <td class="text-end">
                                    <?php if ($t['booking_id']): ?>
                                        <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/admin/financials/invoice?booking_id=<?= $t['booking_id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                            <i class="bi bi-printer me-1"></i> Tax Invoice
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
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