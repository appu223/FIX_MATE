<div class="container py-4">
    <div class="card-custom p-4 mx-auto shadow-sm" style="max-width: 650px;">
        <div class="text-center mb-4">
            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 60px; height: 60px;">
                <i class="bi bi-patch-check-fill fs-3"></i>
            </div>
            <h4 class="fw-bold mb-1">Job Completion Sign-off</h4>
            <p class="text-muted small">Verify work fulfillment, rate technician craftsmanship, and authorize warranty release.</p>
        </div>

        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/bookings/submit-review" method="POST">
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($_GET['booking_id'] ?? '1') ?>">
            <input type="hidden" name="pro_id" value="<?= htmlspecialchars($_GET['pro_id'] ?? '1') ?>">

            <!-- Proof of Work Verification -->
            <div class="p-3 border rounded-3 bg-light mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold small text-dark"><i class="bi bi-shield-check text-success me-1"></i>30-Day FixMate Warranty Activated</div>
                        <div class="text-muted small">Any recurrence of the issue will be repaired at zero extra charge.</div>
                    </div>
                </div>
            </div>

            <!-- Rating Select -->
            <div class="mb-3 text-center">
                <label class="form-label small fw-bold d-block">Overall Satisfaction</label>
                <select name="rating" class="form-select form-select-lg text-center fw-bold text-warning border-warning">
                    <option value="5" selected>★★★★★ (5 Stars - Exceptional)</option>
                    <option value="4">★★★★☆ (4 Stars - Good Job)</option>
                    <option value="3">★★★☆☆ (3 Stars - Average)</option>
                    <option value="2">★★☆☆☆ (2 Stars - Disappointed)</option>
                    <option value="1">★☆☆☆☆ (1 Star - Poor Work)</option>
                </select>
            </div>

            <!-- Review Feedback -->
            <div class="mb-4">
                <label class="form-label small fw-bold">Your Review & Technician Feedback</label>
                <textarea name="comment" rows="4" class="form-control" placeholder="Did the technician arrive on time? Did they keep the work area tidy? Any specific appreciation?" required></textarea>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill py-2 fw-semibold">
                    <i class="bi bi-check2-circle me-1"></i> Sign-off & Publish Review
                </button>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/my-bookings" class="btn btn-light rounded-pill py-2 text-secondary">
                    Review Later
                </a>
            </div>
        </form>
    </div>
</div>