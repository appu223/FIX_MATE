<?php
$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$customer = \App\Core\Auth::user() ?? ['name' => 'Customer'];
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($pageTitle ?? 'Fixmate Services') ?> | Fixmate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        @media(max-width:767.98px) {
            .customer-header .container { display:block !important; }
            .customer-brand { display:inline-block; padding:.25rem 0 .5rem; }
            .customer-nav { width:100%; padding-bottom:.45rem; }
        }
        body.customer-nav-closed .customer-nav { display:none; }
    </style>
    <link href="<?= $e($baseUrl) ?>/css/custom.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header class="customer-header">
    <div class="container d-flex align-items-center justify-content-between py-2 gap-3">
        <a class="customer-brand" href="<?= $e($baseUrl) ?>/customer"><span class="customer-logo"><i class="bi bi-wrench-adjustable"></i></span>fixmate</a>
        <button class="btn btn-sm btn-outline-secondary" type="button" data-sidebar-toggle="customer-nav-closed" aria-controls="customerNavigation" aria-expanded="true" aria-label="Close customer navigation">
            <i class="bi bi-layout-sidebar-inset me-1" aria-hidden="true"></i><span>Close menu</span>
        </button>
        <nav id="customerNavigation" class="customer-nav" aria-label="Customer navigation">
            <a class="<?= str_contains($currentPath, '/customer/pros') ? 'active' : '' ?>" href="<?= $e($baseUrl) ?>/customer/pros">Find Pros</a>
            <a class="<?= str_contains($currentPath, '/customer/cart') ? 'active' : '' ?>" href="<?= $e($baseUrl) ?>/customer/cart"><i class="bi bi-bag me-1"></i>Cart <span id="cartBadge" class="badge rounded-pill text-bg-primary ms-1">0</span></a>
            <a class="<?= str_contains($currentPath, '/customer/my-bookings') ? 'active' : '' ?>" href="<?= $e($baseUrl) ?>/customer/my-bookings"><i class="bi bi-check2-circle me-1"></i>Bookings &amp; completion</a>
            <a href="<?= $e($baseUrl) ?>/customer/my-bookings#billing"><i class="bi bi-receipt me-1"></i>Bills &amp; downloads</a>
            <a class="<?= str_contains($currentPath, '/customer/custom-quote') ? 'active' : '' ?>" href="<?= $e($baseUrl) ?>/customer/custom-quote">Get a quote</a>
            <a class="<?= str_contains($currentPath, '/customer/support') ? 'active' : '' ?>" href="<?= $e($baseUrl) ?>/customer/support">Support</a>
            <a class="<?= str_contains($currentPath, '/customer/profile') ? 'active' : '' ?>" href="<?= $e($baseUrl) ?>/customer/profile"><i class="bi bi-person-circle me-1"></i><?= $e($customer['name']) ?></a>
            <a href="<?= $e($baseUrl) ?>/logout?workspace=customer"><i class="bi bi-box-arrow-right me-1"></i>Sign out</a>
        </nav>
    </div>
</header>
<main class="customer-main">
    <div class="container">
        <?= $viewContent ?>
    </div>
</main>
<footer class="bg-white border-top py-3 text-center text-secondary small">&copy; <?= date('Y') ?> Fixmate · Home services made dependable.</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script>if (window.gsap && window.ScrollTrigger) gsap.registerPlugin(ScrollTrigger);</script>
<script>
window.fixmateBasePath = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
function refreshCartBadge() {
    try {
        const cart = JSON.parse(localStorage.getItem('fixmate_cart') || '[]');
        const count = cart.reduce((total, item) => total + Number(item.quantity || 1), 0);
        const badge = document.getElementById('cartBadge');
        if (badge) badge.textContent = String(count);
    } catch (error) {
        const badge = document.getElementById('cartBadge');
        if (badge) badge.textContent = '0';
    }
}
refreshCartBadge();
</script>
<script src="<?= $e($baseUrl) ?>/js/fixmate.js"></script>
<script src="<?= $e($baseUrl) ?>/js/customer-payments.js"></script>
</body>
</html>