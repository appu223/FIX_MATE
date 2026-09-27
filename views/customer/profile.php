<?php
$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<div class="row g-4">
    <div class="col-12">
        <h1 class="h3 fw-bold mb-1">Profile &amp; addresses</h1>
        <p class="text-secondary">Keep your contact details and service locations up to date.</p>
        <?php if (!empty($flashMessage)): ?><div class="alert alert-success"><?= $e($flashMessage) ?></div><?php endif; ?>
        <?php if (!empty($flashError)): ?><div class="alert alert-danger"><?= $e($flashError) ?></div><?php endif; ?>
    </div>
    <div class="col-12 col-lg-5">
        <section class="card-custom p-4">
            <h2 class="h5 fw-bold mb-3">Personal details</h2>
            <form method="post" action="<?= $e($baseUrl ?? '') ?>/customer/profile/save-profile">
                <div class="mb-3"><label class="form-label" for="profile-name">Full name</label><input class="form-control" id="profile-name" name="name" value="<?= $e($profile['name'] ?? '') ?>" required maxlength="150"></div>
                <div class="mb-3"><label class="form-label" for="profile-email">Email</label><input class="form-control" id="profile-email" value="<?= $e($profile['email'] ?? '') ?>" disabled></div>
                <div class="mb-3"><label class="form-label" for="profile-phone">Phone</label><input class="form-control" id="profile-phone" name="phone" value="<?= $e($profile['phone'] ?? '') ?>" required maxlength="20"></div>
                <button class="btn btn-primary rounded-pill px-4" type="submit">Save profile</button>
            </form>
        </section>
    </div>
    <div class="col-12 col-lg-7">
        <section class="card-custom p-4">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-3"><h2 class="h5 fw-bold mb-0">Saved addresses</h2><span class="badge text-bg-light border"><?= count($addresses) ?> saved</span></div>
            <?php if (empty($addresses)): ?><p class="text-secondary">No service addresses saved. Add your first address below.</p><?php else: ?>
                <div class="d-grid gap-2 mb-4">
                    <?php foreach ($addresses as $address): ?>
                        <article class="border rounded-3 p-3 d-flex justify-content-between align-items-start gap-3">
                            <div><strong><?= $e($address['label']) ?></strong> <?php if (!empty($address['is_default'])): ?><span class="badge text-bg-primary ms-1">Default</span><?php endif; ?><div class="small text-secondary mt-1"><?= $e($address['address_line1']) ?><?= !empty($address['address_line2']) ? ', ' . $e($address['address_line2']) : '' ?><?= !empty($address['landmark']) ? ', ' . $e($address['landmark']) : '' ?><br><?= $e($address['city']) ?>, <?= $e($address['state']) ?> <?= $e($address['postal_code']) ?></div></div>
                            <form method="post" action="<?= $e($baseUrl ?? '') ?>/customer/profile/delete-address" onsubmit="return confirm('Remove this address?')"><input type="hidden" name="address_id" value="<?= (int)$address['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Remove address"><i class="bi bi-trash"></i></button></form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <h3 class="h6 fw-bold mb-3">Add an address</h3>
            <form method="post" action="<?= $e($baseUrl ?? '') ?>/customer/profile/save-address" class="row g-3">
                <div class="col-sm-4"><label class="form-label" for="address-label">Label</label><input class="form-control" id="address-label" name="label" value="Home" required></div>
                <div class="col-sm-8"><label class="form-label" for="address-line1">Address line</label><input class="form-control" id="address-line1" name="address_line1" required></div>
                <div class="col-12"><label class="form-label" for="address-line2">Apartment / extra details</label><input class="form-control" id="address-line2" name="address_line2"></div>
                <div class="col-sm-4"><label class="form-label" for="address-landmark">Landmark</label><input class="form-control" id="address-landmark" name="landmark"></div>
                <div class="col-sm-4"><label class="form-label" for="address-city">City</label><input class="form-control" id="address-city" name="city" value="Bengaluru" required></div>
                <div class="col-sm-4"><label class="form-label" for="address-state">State</label><input class="form-control" id="address-state" name="state" value="Karnataka" required></div>
                <div class="col-sm-5"><label class="form-label" for="address-postal">Postal code</label><input class="form-control" id="address-postal" name="postal_code" value="560034" required></div>
                <div class="col-sm-7 d-flex align-items-end"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="is_default" id="address-default" value="1"><label class="form-check-label" for="address-default">Make default address</label></div></div>
                <div class="col-12"><button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-plus-lg me-1"></i>Save address</button></div>
            </form>
        </section>
    </div>
</div>