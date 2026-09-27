<?php
$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<div class="container-fluid p-0">
    <header class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <div class="text-primary fw-bold small text-uppercase mb-1">ADM-04 · Service operations</div>
            <h1 class="h3 fw-bold mb-1">Service catalog</h1>
            <p class="text-secondary mb-0">Manage service categories, base pricing, and availability.</p>
        </div>
        <span class="badge rounded-pill text-bg-light border px-3 py-2"><?= count($categories) ?> categories · <?= count($services) ?> services</span>
    </header>

    <?php if (!empty($flashMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert"><?= $e($flashMessage) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert"><?= $e($flashError) ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-5">
            <section class="card-custom p-4 h-100">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-folder-plus text-primary me-2"></i>Add category</h2>
                <form method="post" action="<?= $e($baseUrl ?? '') ?>/admin/catalog/save-category" class="row g-3">
                    <div class="col-12"><label class="form-label" for="category-name">Category name</label><input id="category-name" class="form-control" name="name" maxlength="120" required></div>
                    <div class="col-md-6"><label class="form-label" for="category-icon">Icon class</label><input id="category-icon" class="form-control" name="icon" value="bi-tools" placeholder="bi-tools"></div>
                    <div class="col-md-6"><label class="form-label" for="category-sort">Sort order</label><input id="category-sort" class="form-control" type="number" name="sort_order" value="0"></div>
                    <div class="col-12"><label class="form-label" for="category-description">Description</label><textarea id="category-description" class="form-control" name="description" rows="2"></textarea></div>
                    <div class="col-12"><button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-plus-lg me-1"></i>Save category</button></div>
                </form>
            </section>
        </div>
        <div class="col-12 col-xl-7">
            <section class="card-custom p-4 h-100">
                <h2 class="h5 fw-bold mb-3"><i class="bi bi-tools text-primary me-2"></i>Add service</h2>
                <form method="post" action="<?= $e($baseUrl ?? '') ?>/admin/catalog/save-service" class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="service-name">Service name</label><input id="service-name" class="form-control" name="name" maxlength="160" required></div>
                    <div class="col-md-6"><label class="form-label" for="service-category">Category</label><select id="service-category" class="form-select" name="category_id" required><option value="">Choose a category</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>"><?= $e($category['name']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label" for="service-price">Base price (₹)</label><input id="service-price" class="form-control" type="number" min="0" step="0.01" name="base_price" value="0" required></div>
                    <div class="col-md-4"><label class="form-label" for="service-duration">Duration (minutes)</label><input id="service-duration" class="form-control" type="number" min="1" name="duration_minutes" value="60" required></div>
                    <div class="col-md-4"><label class="form-label" for="service-status">Status</label><select id="service-status" class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <div class="col-12"><label class="form-label" for="service-description">Description</label><textarea id="service-description" class="form-control" name="description" rows="2"></textarea></div>
                    <div class="col-12 form-check ms-2"><input class="form-check-input" type="checkbox" name="is_popular" id="service-popular"><label class="form-check-label" for="service-popular">Mark as popular</label></div>
                    <div class="col-12"><button class="btn btn-primary rounded-pill px-4" type="submit"><i class="bi bi-plus-lg me-1"></i>Save service</button></div>
                </form>
            </section>
        </div>
    </div>

    <section class="card-custom p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h5 fw-bold mb-0">Categories</h2>
            <span class="text-secondary small"><?= count($categories) ?> total</span>
        </div>
        <?php if (!$categories): ?>
            <p class="text-secondary mb-0">No categories yet. Add one above to begin.</p>
        <?php else: ?>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Category</th><th>Description</th><th>Services</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody><?php foreach ($categories as $category): ?>
                    <tr>
                        <td><i class="bi <?= $e($category['icon'] ?? 'bi-tools') ?> text-primary me-2"></i><strong><?= $e($category['name']) ?></strong><div class="small text-secondary">Order <?= (int)$category['sort_order'] ?></div></td>
                        <td class="text-secondary"><?= $e($category['description'] ?? '') ?></td>
                        <td><?= (int)$category['services_count'] ?></td>
                        <td><span class="badge <?= $category['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $e(ucfirst($category['status'])) ?></span></td>
                        <td class="text-end"><form class="catalog-toggle-form" method="post" action="<?= $e($baseUrl ?? '') ?>/admin/catalog/toggle-category-status"><input type="hidden" name="id" value="<?= (int)$category['id'] ?>"><input type="hidden" name="status" value="<?= $category['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-sm btn-outline-secondary rounded-pill" type="submit">Set <?= $category['status'] === 'active' ? 'inactive' : 'active' ?></button></form></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php endif; ?>
    </section>

    <section class="card-custom p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h5 fw-bold mb-0">Services</h2>
            <form method="get" action="<?= $e($baseUrl ?? '') ?>/admin/catalog" class="d-flex flex-wrap gap-2">
                <select class="form-select form-select-sm" name="category_id" aria-label="Filter by category"><option value="0">All categories</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$catFilter === (int)$category['id'] ? 'selected' : '' ?>><?= $e($category['name']) ?></option><?php endforeach; ?></select>
                <input class="form-control form-control-sm" type="search" name="search" placeholder="Search services" value="<?= $e($search) ?>">
                <button class="btn btn-sm btn-dark rounded-pill px-3" type="submit">Filter</button>
            </form>
        </div>
        <?php if (!$services): ?>
            <p class="text-secondary mb-0">No services match this filter.</p>
        <?php else: ?>
            <div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Service</th><th>Category</th><th>Base price</th><th>Duration</th><th>Pros / bookings</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                <tbody><?php foreach ($services as $service): ?>
                    <tr>
                        <td><strong><?= $e($service['name']) ?></strong><?php if (!empty($service['is_popular'])): ?> <span class="badge text-bg-warning">Popular</span><?php endif; ?><div class="small text-secondary"><?= $e($service['description'] ?? '') ?></div></td>
                        <td><?= $e($service['category_name']) ?></td>
                        <td>₹<?= number_format((float)$service['base_price'], 2) ?></td>
                        <td><?= (int)$service['duration_minutes'] ?> min</td>
                        <td><?= (int)$service['active_pros_count'] ?> / <?= (int)$service['times_booked'] ?></td>
                        <td><span class="badge <?= $service['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $e(ucfirst($service['status'])) ?></span></td>
                        <td class="text-end"><form class="catalog-toggle-form" method="post" action="<?= $e($baseUrl ?? '') ?>/admin/catalog/toggle-service-status"><input type="hidden" name="id" value="<?= (int)$service['id'] ?>"><input type="hidden" name="status" value="<?= $service['status'] === 'active' ? 'inactive' : 'active' ?>"><button class="btn btn-sm btn-outline-secondary rounded-pill" type="submit">Set <?= $service['status'] === 'active' ? 'inactive' : 'active' ?></button></form></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php endif; ?>
    </section>
</div>
<script>
document.querySelectorAll('.catalog-toggle-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        try {
            const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Could not update status.');
            window.location.reload();
        } catch (error) {
            window.alert(error.message || 'Could not update status.');
        }
    });
});
</script>
