<div class="container py-3">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Raise Dispute Form -->
        <div class="col-12 col-md-5">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-life-preserver text-danger me-2"></i>Report Issue or Dispute</h5>
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/support/raise-dispute" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Disputed Booking</label>
                        <select name="booking_id" class="form-select" required>
                            <?php foreach ($bookings as $b): ?>
                                <option value="<?= $b['id'] ?>">#<?= $b['booking_code'] ?> - <?= $b['scheduled_date'] ?> (₹<?= $b['total_amount'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Grievance Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Technician delayed / Incomplete work / Overcharging" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detailed Description</label>
                        <textarea name="details" class="form-control" rows="3" placeholder="Provide complete context..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 rounded-pill">Submit to Arbitration Desk</button>
                </form>
            </div>
        </div>

        <!-- Disputes List -->
        <div class="col-12 col-md-7">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3">My Dispute Claims</h5>
                <?php if (empty($disputes)): ?>
                    <div class="text-center py-4 text-muted">No grievances or active dispute claims recorded.</div>
                <?php else: ?>
                    <?php foreach ($disputes as $d): ?>
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold m-0 text-dark"><?= htmlspecialchars($d['reason']) ?></h6>
                                <span class="badge bg-secondary rounded-pill"><?= strtoupper($d['status']) ?></span>
                            </div>
                            <div class="text-muted small mt-1"><?= htmlspecialchars($d['details']) ?></div>
                            <?php if ($d['resolution_notes']): ?>
                                <div class="mt-2 p-2 bg-white rounded border small text-success">
                                    <strong>Arbitration Resolution:</strong> <?= htmlspecialchars($d['resolution_notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>