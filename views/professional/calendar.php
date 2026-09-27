<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/schedule/save" method="POST" class="card-custom p-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-clock-fill text-primary me-2"></i>Weekly Working Hours</h5>
                <?php 
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    foreach ($days as $idx => $dayName): 
                ?>
                    <div class="row align-items-center py-2 border-bottom">
                        <div class="col-3 fw-bold small text-dark"><?= $dayName ?></div>
                        <div class="col-4">
                            <input type="time" name="shifts[<?= $idx ?>][start]" class="form-control form-control-sm" value="09:00">
                        </div>
                        <div class="col-4">
                            <input type="time" name="shifts[<?= $idx ?>][end]" class="form-control form-control-sm" value="18:00">
                        </div>
                        <div class="col-1">
                            <input type="checkbox" name="shifts[<?= $idx ?>][is_active]" value="1" class="form-check-input" checked>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Update Shifts</button>
                </div>
            </form>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card-custom p-4 mb-4">
                <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-calendar-x text-danger me-2"></i>Request Planned Time-off</h5>
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/schedule/request-leave" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Leave Date</label>
                        <input type="date" name="leave_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Festival, Family emergency" required>
                    </div>
                    <button type="submit" class="btn btn-outline-danger w-100 rounded-pill">Block Calendar</button>
                </form>
            </div>

            <div class="card-custom p-4">
                <h6 class="fw-bold mb-3">Approved Upcoming Leaves</h6>
                <?php if (empty($leaves)): ?>
                    <div class="text-muted small text-center py-2">No leaves booked.</div>
                <?php else: ?>
                    <?php foreach ($leaves as $l): ?>
                        <div class="d-flex justify-content-between py-2 border-bottom small">
                            <span class="fw-bold"><?= date('d M Y', strtotime($l['leave_date'])) ?></span>
                            <span class="text-muted"><?= htmlspecialchars($l['reason']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>