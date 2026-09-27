<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <!-- Balance Card -->
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 kpi-green">
                <div class="text-muted text-uppercase small fw-bold">Available Wallet Balance</div>
                <h2 class="fw-bold text-dark mt-2">₹<?= number_format((float)($profile['wallet_balance'] ?? 0), 2) ?></h2>
                
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/earnings/request-payout" method="POST" class="mt-4">
                    <label class="form-label small fw-bold">Withdraw to Registered Bank</label>
                    <div class="input-group mb-2">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="10.00" name="amount" class="form-control" placeholder="1000.00" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-pill">Transfer to Bank Account</button>
                </form>
            </div>
        </div>

        <!-- Payout Requests History -->
        <div class="col-12 col-md-8">
            <div class="card-custom p-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3">Withdrawal Payout Requests</h5>
                <?php if (empty($payouts)): ?>
                    <div class="text-muted small text-center py-4">No payout withdrawal requests on file.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small earnings-table earnings-table--payout">
                            <thead>
                                <tr class="text-muted">
                                    <th>DATE</th>
                                    <th>AMOUNT</th>
                                    <th>REF NO</th>
                                    <th>STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payouts as $p): ?>
                                    <tr>
                                        <td class="text-nowrap"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                                        <td class="fw-bold text-nowrap">₹<?= number_format((float)$p['amount'], 2) ?></td>
                                        <td class="font-monospace text-break"><?= htmlspecialchars($p['payout_reference'] ?? 'PENDING') ?></td>
                                        <td>
                                            <?php
                                                $payoutStatus = strtolower((string)($p['status'] ?? 'pending'));
                                                [$payoutLabel, $payoutClass, $payoutIcon] = match ($payoutStatus) {
                                                    'approved' => ['Approved', 'text-bg-success', 'bi-check-circle-fill'],
                                                    'processed' => ['Paid', 'text-bg-success', 'bi-check-circle-fill'],
                                                    'rejected' => ['Rejected', 'text-bg-danger', 'bi-x-circle-fill'],
                                                    'pending' => ['Pending', 'text-bg-warning', 'bi-hourglass-split'],
                                                    default => [ucfirst(str_replace('_', ' ', $payoutStatus)), 'text-bg-secondary', 'bi-info-circle-fill'],
                                                };
                                            ?>
                                            <span class="badge rounded-pill earnings-status <?= $payoutClass ?>" role="status">
                                                <i class="bi <?= $payoutIcon ?>" aria-hidden="true"></i><?= htmlspecialchars($payoutLabel, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Completed Work Order Settlements -->
    <div class="card-custom p-4">
        <h5 class="fw-bold border-bottom pb-2 mb-3">Completed Job Settlements</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle small earnings-table earnings-table--jobs">
                <thead class="text-muted">
                    <tr>
                        <th>BOOKING REF</th>
                        <th>JOB DATE</th>
                        <th>GROSS TOTAL</th>
                        <th>COMMISSION</th>
                        <th>YOUR NET EARNING</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr><td colspan="6" class="text-center py-3 text-muted">No completed jobs yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $j): ?>
                            <tr>
                                <td class="fw-bold text-primary text-break"><?= htmlspecialchars($j['booking_code']) ?></td>
                                <td><?= date('d M Y', strtotime($j['scheduled_date'])) ?></td>
                                <td>₹<?= number_format((float)$j['total_amount'], 2) ?></td>
                                <td class="text-danger">- ₹<?= number_format((float)$j['commission_amount'], 2) ?></td>
                                <td class="fw-bold text-success">₹<?= number_format((float)$j['pro_earning'], 2) ?></td>
                                <td>
                                    <?php $isCredited = (int)($j['pro_earning_credited'] ?? 0) === 1; ?>
                                    <span class="badge rounded-pill earnings-status <?= $isCredited ? 'text-bg-success' : 'text-bg-warning' ?>" role="status">
                                        <i class="bi <?= $isCredited ? 'bi-check-circle-fill' : 'bi-hourglass-split' ?>" aria-hidden="true"></i><?= $isCredited ? 'Confirmed' : 'Pending' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>