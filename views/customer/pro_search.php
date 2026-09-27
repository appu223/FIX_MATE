<div class="container py-3 pro-directory-page">
    <div class="directory-heading d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <span class="directory-eyebrow"><i class="bi bi-patch-check-fill me-1"></i>Verified local experts</span>
            <h1 class="h3 fw-bold mt-2 mb-1">Choose your professional</h1>
            <p class="text-muted small m-0">Select a technician to see their services and rates on the next step.</p>
        </div>
        <form method="GET" action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/pros" class="directory-search d-flex gap-2">
            <label class="visually-hidden" for="pro-directory-search">Search professionals</label>
            <input id="pro-directory-search" type="search" name="q" class="form-control" placeholder="Name or specialty" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-primary px-3"><i class="bi bi-search me-1"></i>Search</button>
        </form>
    </div>

    <div class="row g-4">
        <?php foreach ($pros as $p): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <article class="card-custom pro-directory-card p-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="pro-directory-avatar rounded-circle fw-bold d-flex align-items-center justify-content-center" aria-hidden="true">
                                <?= htmlspecialchars(strtoupper(substr($p['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div>
                                <h2 class="h6 fw-bold m-0"><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?> <i class="bi bi-patch-check-fill text-primary" title="Verified"></i></h2>
                                <div class="text-warning small"><i class="bi bi-star-fill"></i> <?= number_format((float)$p['rating_avg'], 1) ?> <span class="text-muted">(<?= (int)$p['rating_count'] ?> reviews)</span></div>
                            </div>
                        </div>
                        <p class="text-muted small mb-3"><?= htmlspecialchars($p['bio'] ?: 'Experienced certified technician.', ENT_QUOTES, 'UTF-8') ?></p>
                        <span class="badge pro-experience-badge"><i class="bi bi-briefcase me-1"></i><?= (int)$p['experience_years'] ?> years experience</span>
                    </div>
                    <div class="border-top pt-3 mt-3 d-flex align-items-center justify-content-between gap-2">
                        <span class="small text-muted"><i class="bi bi-arrow-right-circle me-1"></i>View available services</span>
                        <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer?pro_id=<?= (int)$p['pro_id'] ?>" class="btn btn-sm btn-primary px-3">Select</a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
        <?php if (empty($pros)): ?>
            <div class="col-12"><div class="card-custom p-5 text-center"><i class="bi bi-person-search fs-1 text-primary"></i><h2 class="h5 fw-bold mt-3">No professionals found</h2><p class="text-muted mb-0">Try a different name or specialty.</p></div></div>
        <?php endif; ?>
    </div>
</div>