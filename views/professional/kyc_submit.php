<div class="container-fluid p-0">
    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success border-0 rounded-pill px-4"><?= htmlspecialchars($flashMessage) ?></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger border-0 rounded-pill px-4"><?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <div class="card-custom p-4" style="max-width: 800px;">
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h4 class="fw-bold m-0"><i class="bi bi-shield-check text-primary me-2"></i>KYC & Police Verification Dossier</h4>
                <p class="text-muted small m-0">Mandatory trade licenses and government identification proofs</p>
            </div>
            <?php $kycStatus = (string)($profile['kyc_status'] ?? 'pending'); ?>
            <span class="badge <?= match ($kycStatus) { 'verified' => 'badge-pill-green', 'rejected' => 'badge-pill-red', default => 'badge-pill-amber' } ?>">
                STATUS: <?= htmlspecialchars(strtoupper($kycStatus), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <?php if ($kycStatus === 'rejected'): ?>
            <div class="alert alert-danger" role="alert">
                <div class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Changes required by Fixmate administration</div>
                <div class="small mt-1"><?= nl2br(htmlspecialchars($profile['kyc_rejected_reason'] ?? 'Please contact administration for details.', ENT_QUOTES, 'UTF-8')) ?></div>
                <div class="small mt-2">After correcting your documents, submit them again below. You can also message the admin team from your dashboard.</div>
            </div>
        <?php elseif ($kycStatus === 'verified'): ?>
            <div class="alert alert-success" role="status"><i class="bi bi-check-circle-fill me-2"></i>Your technician identity is verified. Thank you.</div>
        <?php else: ?>
            <div class="alert alert-warning" role="status"><i class="bi bi-hourglass-split me-2"></i>Your dossier is awaiting review. A notification will appear on your dashboard when the status changes.</div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/pro/kyc/upload" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label small fw-bold">Primary Government ID Type</label>
                <select name="id_proof_type" class="form-select">
                    <option value="Aadhaar Card">Aadhaar Card (Front & Back)</option>
                    <option value="PAN Card">PAN Card</option>
                    <option value="Voter ID">Voter ID Card</option>
                    <option value="Driving License">Commercial Driving License</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Attach Scanned ID Proof (PDF/JPG/PNG)</label>
                <input type="file" name="id_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold">Attach Address Proof (PDF/JPG/PNG)</label>
                <input type="file" name="address_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" required>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold">Electrician/HVAC License or ITI Certificate (Optional)</label>
                <input type="file" name="license_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp">
            </div>

            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                Submit Documents for Inspection
            </button>
        </form>
    </div>
</div>