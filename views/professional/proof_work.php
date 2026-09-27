<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger border-0 rounded-pill px-4"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="card-custom p-4 mb-4" style="max-width: 700px;">
        <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-camera-fill text-primary me-2"></i>Upload Proof of Work & Receipts</h5>
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/proof-of-work/upload" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small fw-bold">Proof Type</label>
                    <select name="proof_type" class="form-select">
                        <option value="before">Before Repair (Damage Photo)</option>
                        <option value="after">After Completion (Fixed Photo)</option>
                        <option value="material_receipt">Material Bill Receipt</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Select Photo</label>
                    <input type="file" name="proof_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Description / Notes</label>
                <input type="text" name="description" class="form-control" placeholder="e.g. Replaced 10uf capacitor on master bedroom fan">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Upload Proof Attachment</button>
        </form>
    </div>

    <!-- Uploaded Proofs Gallery -->
    <div class="card-custom p-4">
        <h6 class="fw-bold mb-3">Attached Records for Booking #<?= $bookingId ?></h6>
        <?php if (empty($proofs)): ?>
            <div class="text-muted small text-center py-4">No before/after proofs uploaded yet.</div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($proofs as $pr): ?>
                    <div class="col-6 col-md-3">
                        <div class="p-2 border rounded-3 bg-light text-center">
                            <span class="badge bg-secondary mb-1"><?= strtoupper($pr['proof_type']) ?></span>
                            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/proof-of-work/file?proof_id=<?= (int)$pr['id'] ?>" target="_blank" rel="noopener" class="d-block text-decoration-none">
                                <i class="bi bi-paperclip me-1"></i>Open attachment
                            </a>
                            <div class="small text-muted"><?= htmlspecialchars($pr['description'] ?? 'Image attached') ?></div>
                            <div class="small mt-1">Admin review: <strong><?= htmlspecialchars(strtoupper($pr['review_status'] ?? 'pending')) ?></strong></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>