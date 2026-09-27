<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="bi bi-star-half text-warning me-2"></i>Ratings & Verified Feedback</h4>
            <p class="text-muted small m-0">Inspect customer testimonials and star ratings</p>
        </div>
        <div class="fs-4 fw-bold text-warning">
            ★ <?= number_format((float)($profile['rating_avg'] ?? 5.0), 2) ?>
        </div>
    </div>

    <div class="card-custom p-4">
        <?php if (empty($reviews)): ?>
            <div class="text-center py-5 text-muted">No reviews recorded yet. Complete jobs to receive feedback!</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($reviews as $r): ?>
                    <div class="col-12 col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark"><?= htmlspecialchars($r['customer_name']) ?></span>
                                <span class="text-warning fw-bold">★ <?= $r['rating'] ?>.0</span>
                            </div>
                            <div class="text-muted small fst-italic mb-2">"<?= htmlspecialchars($r['comment']) ?>"</div>
                            <div class="text-muted small border-top pt-2">Order #<?= htmlspecialchars($r['booking_code']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>