<div class="container py-3">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-12 col-md-5">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3">Request Custom Project Quote</h5>
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/custom-quote/submit" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Service Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Job Scope / Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Complete 3BHK Wall Repainting" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Budget Min (₹)</label>
                            <input type="number" name="budget_min" class="form-control" placeholder="2000">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Budget Max (₹)</label>
                            <input type="number" name="budget_max" class="form-control" placeholder="10000">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detailed Requirements</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Describe the materials needed, square footage, issues..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Submit Quotation Request</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-md-7">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3">My Quote Inquiries</h5>
                <?php if (empty($quotes)): ?>
                    <div class="text-center py-4 text-muted">No custom quotation requests submitted yet.</div>
                <?php else: ?>
                    <?php foreach ($quotes as $q): ?>
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="fw-bold m-0"><?= htmlspecialchars($q['title']) ?></h6>
                                <span class="badge bg-primary rounded-pill"><?= strtoupper($q['status']) ?></span>
                            </div>
                            <div class="text-muted small my-1"><?= htmlspecialchars($q['description']) ?></div>
                            <div class="d-flex justify-content-between small text-secondary border-top pt-2 mt-2">
                                <span>Budget: ₹<?= $q['budget_min'] ?> - ₹<?= $q['budget_max'] ?></span>
                                <span class="fw-bold text-success"><?= $q['bids_count'] ?> Bids Received</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>