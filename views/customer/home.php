<div class="container customer-discovery-page">

    <!-- Hero Search Section -->
    <div class="hero-banner mb-5 shadow-sm">
        <div class="row align-items-center">
            <div class="col-12 col-lg-7">
                <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-bold mb-3">
                    <i class="bi bi-shield-fill-check me-1"></i> FixMate Guaranteed 60-Min Dispatch
                </span>
                <h1 class="fw-bold display-6 mb-3">Expert Home Repairs & Maintenance, Delivered.</h1>
                <p class="lead opacity-75 mb-4" style="font-size: 1.05rem;">
                    Electricians, Plumbers, AC Specialists & Deep Cleaners at upfront rates. Zero hidden charges.
                </p>

                <!-- Search Input Form -->
                <form action="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer" method="GET" class="bg-white p-2 rounded-pill shadow-lg d-flex align-items-center">
                    <?php if (!empty($selectedProId)): ?><input type="hidden" name="pro_id" value="<?= (int)$selectedProId ?>"><?php endif; ?>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0 ps-3">
                            <i class="bi bi-search text-muted fs-5"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 bg-transparent text-dark" placeholder="Search 'AC servicing', 'switchboard', 'tap leak'..." value="<?= htmlspecialchars($query ?? '') ?>">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold me-1">
                            Search Services
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-12 col-lg-5 d-none d-lg-flex justify-content-center align-items-center" aria-hidden="true">
                <div class="text-center p-4 rounded-5 bg-white bg-opacity-10 border border-white border-opacity-25">
                    <i class="bi bi-tools" style="font-size: 6rem;"></i>
                    <div class="fw-semibold mt-2">Trusted local professionals</div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($selectionError)): ?>
        <div class="alert alert-warning d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2" role="alert">
            <span><i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($selectionError, ENT_QUOTES, 'UTF-8') ?></span>
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/pros" class="btn btn-sm btn-outline-primary flex-shrink-0">Choose another</a>
        </div>
    <?php elseif (!empty($selectedProfessional)): ?>
        <section class="customer-selection-banner mb-5" aria-labelledby="selected-professional-title">
            <div class="selection-banner-icon"><i class="bi bi-person-check-fill"></i></div>
            <div class="flex-grow-1">
                <span class="selection-eyebrow">Technician selected</span>
                <h2 id="selected-professional-title" class="h5 fw-bold mb-1"><?= htmlspecialchars($selectedProfessional['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                <p class="small mb-0">The services below are offered by your selected professional. Their listed rates will carry through to checkout.</p>
            </div>
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/pros" class="btn btn-sm btn-outline-primary flex-shrink-0">Change professional</a>
        </section>
    <?php endif; ?>

    <!-- Trade Categories Grid -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <h4 class="fw-bold text-dark m-0">Explore Categories</h4>
            <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer<?= !empty($selectedProId) ? '?pro_id=' . (int)$selectedProId : '' ?>" class="text-primary text-decoration-none small fw-semibold">View All</a>
        </div>

        <div class="row g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col-6 col-sm-4 col-lg-2">
                    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer?cat=<?= (int)$cat['id'] ?><?= !empty($selectedProId) ? '&amp;pro_id=' . (int)$selectedProId : '' ?>" class="category-pill-card <?= ($selectedCat ?? 0) === (int)$cat['id'] ? 'border-primary bg-primary-subtle' : '' ?>">
                        <div class="category-icon-box">
                            <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                        </div>
                        <h6 class="fw-bold m-0 text-truncate" style="font-size: 0.9rem;"><?= htmlspecialchars($cat['name']) ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Services Grid: Popular or Search Results -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <div>
                <h4 class="fw-bold text-dark m-0">
                    <?= !empty($selectedProfessional) ? 'Services from ' . htmlspecialchars($selectedProfessional['name'], ENT_QUOTES, 'UTF-8') : (($isSearch ?? false) ? 'Search Results (' . count($services) . ')' : 'Most Booked Services in Bengaluru') ?>
                </h4>
                <p class="text-muted small m-0">Transparent hourly & job-based pricing by certified technicians</p>
            </div>
            <?php if ($isSearch ?? false): ?>
                <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer<?= !empty($selectedProId) ? '?pro_id=' . (int)$selectedProId : '' ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Clear Search</a>
            <?php endif; ?>
        </div>

        <?php if (empty($services)): ?>
            <div class="card-custom p-5 text-center my-4">
                <i class="bi bi-search text-muted fs-1 mb-2"></i>
                <h5 class="fw-bold text-dark"><?= !empty($selectedProfessional) ? 'No matching services for this professional' : 'No services found matching your query' ?></h5>
                <p class="text-muted small"><?= !empty($selectedProfessional) ? 'Try another service category or choose a different verified technician.' : 'Try searching for generic terms like "fan", "AC", "tap", or request a custom quotation.' ?></p>
                <div class="mt-2">
                    <?php if (!empty($selectedProfessional)): ?><a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/pros" class="btn btn-outline-primary rounded-pill px-4 me-2">Choose another professional</a><?php endif; ?>
                    <a href="<?= htmlspecialchars($baseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>/customer/custom-quote" class="btn btn-primary rounded-pill px-4">Request Custom Quote</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($services as $svc): ?>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card-custom service-card h-100 p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bi <?= htmlspecialchars($svc['category_icon']) ?> me-1"></i><?= htmlspecialchars($svc['category_name']) ?>
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.75rem;">
                                        <i class="bi bi-clock me-1"></i><?= $svc['duration_minutes'] ?>m
                                    </span>
                                </div>

                                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.95rem; line-height: 1.35;">
                                    <?= htmlspecialchars($svc['name']) ?>
                                </h6>
                                <p class="text-muted small mb-3 text-truncate-2" style="font-size: 0.8rem; min-height: 38px;">
                                    <?= htmlspecialchars($svc['description'] ?? 'Standard diagnostic, installation and quality inspection.') ?>
                                </p>
                            </div>

                            <div class="border-top pt-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted" style="font-size: 0.72rem;">FIXMATE RATE</span>
                                    <div class="fw-bold text-dark fs-5">₹<?= number_format((float)$svc['base_price'], 2) ?></div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" onclick='addToCart(<?= json_encode($svc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>, <?= (int)($selectedProfessional['pro_id'] ?? 0) ?>, <?= json_encode($selectedProfessional['name'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>)'>
                                    <i class="bi bi-plus-lg me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Trust Feature Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 text-center">
                <div class="fs-1 text-primary mb-2"><i class="bi bi-person-check-fill"></i></div>
                <h6 class="fw-bold mb-1">Police & KYC Verified</h6>
                <p class="text-muted small m-0">Aadhaar card and background checks verified on every active technician.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 text-center">
                <div class="fs-1 text-success mb-2"><i class="bi bi-shield-lock-fill"></i></div>
                <h6 class="fw-bold mb-1">Fixed Upfront Pricing</h6>
                <p class="text-muted small m-0">No surprise surcharges at the door. Pay online or via cash on completion.</p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card-custom p-4 text-center">
                <div class="fs-1 text-warning mb-2"><i class="bi bi-arrow-repeat"></i></div>
                <h6 class="fw-bold mb-1">30-Day Re-work Guarantee</h6>
                <p class="text-muted small m-0">Any issue within 30 days of completion is addressed by FixMate free of cost.</p>
            </div>
        </div>
    </div>
</div>

<script>
const selectedProfessionalId = <?= (int)($selectedProfessional['pro_id'] ?? 0) ?>;

function addToCart(service, professionalId = 0, professionalName = '') {
    let cart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
    const savedSelection = getSavedProfessional();

    if (professionalId > 0 && cart.length > 0 && (!savedSelection || Number(savedSelection.id) !== Number(professionalId))) {
        if (!window.confirm('Your cart belongs to another technician. Clear it and start a new booking with this professional?')) return;
        cart = [];
        localStorage.setItem('fixmate_cart', JSON.stringify(cart));
        refreshCartBadge();
    }
    if (professionalId > 0) {
        localStorage.setItem('fixmate_selected_professional', JSON.stringify({ id: Number(professionalId), name: professionalName }));
    } else if (savedSelection) {
        if (cart.length > 0 && !window.confirm('Your cart is for a selected technician. Clear it to add a general service instead?')) return;
        cart = cart.length > 0 ? [] : cart;
        localStorage.setItem('fixmate_cart', JSON.stringify(cart));
        localStorage.removeItem('fixmate_selected_professional');
    }
    
    // Check if already in cart
    const existingIndex = cart.findIndex(item => item.id === service.id);
    if (existingIndex > -1) {
        cart[existingIndex].quantity = (cart[existingIndex].quantity || 1) + 1;
    } else {
        cart.push({
            id: service.id,
            name: service.name,
            base_price: Number(service.base_price),
            duration_minutes: service.duration_minutes,
            category_name: service.category_name,
            quantity: 1
        });
    }

    localStorage.setItem('fixmate_cart', JSON.stringify(cart));
    refreshCartBadge();

    // Visual feedback
    alert(`"${service.name}" added to cart! Total items: ${cart.length}`);
}

function getSavedProfessional() {
    try {
        return JSON.parse(localStorage.getItem('fixmate_selected_professional') || 'null');
    } catch (error) {
        localStorage.removeItem('fixmate_selected_professional');
        return null;
    }
}

if (selectedProfessionalId > 0) {
    const savedSelection = getSavedProfessional();
    const savedCart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
    if (savedCart.length === 0 || (savedSelection && Number(savedSelection.id) === selectedProfessionalId)) {
        localStorage.setItem('fixmate_selected_professional', JSON.stringify({
            id: selectedProfessionalId,
            name: <?= json_encode($selectedProfessional['name'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
        }));
    }
}
</script>