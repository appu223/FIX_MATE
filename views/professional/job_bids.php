<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger border-0 rounded-pill px-4"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <h4 class="fw-bold mb-4"><i class="bi bi-inbox-fill text-primary me-2"></i>Open Custom Job Leads</h4>

    <div class="row g-4">
        <?php if (empty($leads)): ?>
            <div class="col-12 text-center py-5 text-muted">No custom inquiry leads in your zones right now.</div>
        <?php else: ?>
            <?php foreach ($leads as $l): ?>
                <div class="col-12 col-lg-6">
                    <div class="card-custom p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-secondary-subtle text-secondary rounded-pill"><?= htmlspecialchars($l['category_name']) ?></span>
                            <span class="badge bg-light text-dark border"><?= $l['bids_count'] ?> Bids Submitted</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($l['title']) ?></h5>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($l['description']) ?></p>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="fw-bold text-primary">Budget: ₹<?= $l['budget_min'] ?> - ₹<?= $l['budget_max'] ?></span>
                            <button class="btn btn-sm btn-dark rounded-pill px-3" onclick="openBidModal(<?= $l['id'] ?>, '<?= htmlspecialchars(addslashes($l['title'])) ?>')">
                                Submit Quotation Bid
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Submit Bid -->
<div class="modal fade" id="bidModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/leads/submit-bid" method="POST" class="modal-content card-custom border-0 shadow">
            <input type="hidden" name="quotation_id" id="bid_qid">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="bidModalTitle">Submit Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Your Estimated Fixed Price (₹)</label>
                    <input type="number" step="10.00" name="estimated_price" class="form-control" required placeholder="e.g. 4500.00">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Material Inclusions & Quotation Notes</label>
                    <textarea name="notes" rows="3" class="form-control" placeholder="Explain labor timeline, material specs..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top bg-light">
                <button type="submit" class="btn btn-primary rounded-pill px-4">Dispatch Quote Bid</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBidModal(qid, title) {
    document.getElementById('bid_qid').value = qid;
    document.getElementById('bidModalTitle').innerText = 'Quote for: ' + title;
    new bootstrap.Modal(document.getElementById('bidModal')).show();
}
</script>