<?php
$e = static fn (mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$proUser = \App\Core\Auth::user() ?? ['name' => 'Fixmate partner'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($pageTitle ?? 'Partner workbench') ?> | Fixmate Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --pro-ink:#17243a; --pro-muted:#78859a; --pro-primary:#3459d4; --pro-border:#e5e9f1; --pro-bg:#f5f7fb; }
        body { min-height:100vh; color:var(--pro-ink); background:var(--pro-bg); font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; }
        .pro-sidebar { position:fixed; inset:0 auto 0 0; z-index:1020; display:flex; flex-direction:column; width:264px; background:#fff; border-right:1px solid var(--pro-border); }
        .pro-main { min-height:100vh; margin-left:264px; display:flex; flex-direction:column; }
        .pro-topbar { position:sticky; top:0; z-index:1010; min-height:68px; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.75rem 1.75rem; background:rgb(255 255 255 / 94%); border-bottom:1px solid var(--pro-border); backdrop-filter:blur(14px); }
        .pro-content { flex:1; min-width:0; padding:clamp(1rem,2.5vw,2.25rem); }
        .pro-brand { color:var(--pro-ink); text-decoration:none; font-weight:800; letter-spacing:-.04em; }
        .pro-brand-mark { display:grid; place-items:center; width:38px; height:38px; color:#fff; background:#243960; border-radius:12px; }
        .pro-nav { overflow-y:auto; padding:1rem .8rem; }
        .pro-nav-label { padding:.5rem .8rem; color:var(--pro-muted); font-size:.68rem; font-weight:750; letter-spacing:.11em; text-transform:uppercase; }
        .pro-nav .nav-link-custom { box-sizing:border-box; display:grid; grid-template-columns:20px minmax(0,1fr); align-items:center; column-gap:.7rem; width:100%; min-height:46px; margin:.2rem 0; padding:.65rem .85rem; }
        .pro-nav .nav-link-custom > i { display:grid; width:20px; height:20px; place-items:center; margin:0; line-height:1; }
        .pro-nav-link-label { min-width:0; line-height:1.3; overflow-wrap:anywhere; }
        .pro-nav .nav-link-custom.active { box-shadow:inset 3px 0 0 var(--pro-primary) !important; }
        .pro-mobile-menu { display:none; }
        body.pro-sidebar-closed .pro-sidebar { display:none; }
        body.pro-sidebar-closed .pro-main { margin-left:0; }
        .card-custom { background:#fff; border:1px solid var(--pro-border); border-radius:16px; box-shadow:0 1px 2px rgb(23 36 58 / 3%),0 10px 28px rgb(23 36 58 / 4%); }
        .kpi-blue,.kpi-green,.kpi-amber,.kpi-purple { border-left:0!important; }
        .pro-status { display:inline-flex; align-items:center; gap:.5rem; padding:.5rem .75rem; border:1px solid var(--pro-border); border-radius:999px; color:#52627a; background:#fff; font-size:.78rem; font-weight:650; }
        .pro-status-dot { width:8px; height:8px; border-radius:50%; background:#36a77b; box-shadow:0 0 0 3px #e5f5ee; }
        @media(max-width:991.98px) { .pro-sidebar { width:232px; } .pro-main { margin-left:232px; } }
        @media(max-width:767.98px) {
            .pro-sidebar { position:sticky; top:0; width:100%; height:auto; max-height:none; border-right:0; border-bottom:1px solid var(--pro-border); }
            .pro-sidebar-head { min-height:58px!important; height:auto!important; padding:.55rem .9rem!important; }
            .pro-mobile-menu { display:flex; align-items:center; justify-content:space-between; margin:.5rem .75rem; padding:.55rem .7rem; border:1px solid var(--pro-border); border-radius:10px; background:#fff; color:var(--pro-ink); font-weight:700; }
            .pro-nav { max-height:min(62vh,520px); }
            .pro-main { margin-left:0; min-height:0; }
            .pro-topbar { min-height:56px; padding:.6rem .9rem; }
            .pro-content { padding:.9rem; }
        }
        @media(max-width:420px) {
            .pro-topbar .pro-status { display:none; }
            .pro-topbar [data-sidebar-toggle] span { display:none; }
        }
    </style>
    <link href="<?= $e($baseUrl ?? '') ?>/css/custom.css" rel="stylesheet">
</head>
<body>
<aside id="proSidebar" class="pro-sidebar">
    <div class="pro-sidebar-head d-flex align-items-center gap-2 px-3 py-3 border-bottom" style="height:68px">
        <span class="pro-brand-mark"><i class="bi bi-tools"></i></span>
        <div><a href="<?= $e($baseUrl ?? '') ?>/pro/dashboard" class="pro-brand">Fixmate <span class="text-primary">Pro</span></a><div class="small text-secondary" style="font-size:.66rem;letter-spacing:.1em">PARTNER WORKSPACE</div></div>
    </div>
    <button class="pro-mobile-menu" type="button" data-bs-toggle="collapse" data-bs-target="#proNavigation" aria-expanded="false" aria-controls="proNavigation"><span><i class="bi bi-list me-2"></i>Partner menu</span><i class="bi bi-chevron-down"></i></button>
    <nav id="proNavigation" class="pro-nav collapse d-md-block" aria-label="Professional navigation">
        <div class="pro-nav-label">Field operations</div>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/dashboard" class="nav-link-custom <?= str_contains($uri, '/pro/dashboard') || $uri === '/pro' ? 'active' : '' ?>"><i class="bi bi-grid-1x2-fill" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-01: Workbench</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/kyc" class="nav-link-custom <?= str_contains($uri, '/pro/kyc') ? 'active' : '' ?>"><i class="bi bi-shield-check" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-02: KYC Dossier</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/rates-zones" class="nav-link-custom <?= str_contains($uri, '/pro/rates-zones') ? 'active' : '' ?>"><i class="bi bi-geo-alt" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-03: Rates &amp; Coverage</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/schedule" class="nav-link-custom <?= str_contains($uri, '/pro/schedule') ? 'active' : '' ?>"><i class="bi bi-calendar-week" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-04: Shifts &amp; Leaves</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/leads" class="nav-link-custom <?= str_contains($uri, '/pro/leads') ? 'active' : '' ?>"><i class="bi bi-inbox-fill" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-05: Leads &amp; Bidding</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/fulfillment" class="nav-link-custom <?= str_contains($uri, '/pro/fulfillment') ? 'active' : '' ?>"><i class="bi bi-card-checklist" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-06: Jobs &amp; completion</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/nav-chat" class="nav-link-custom <?= str_contains($uri, '/pro/nav-chat') ? 'active' : '' ?>"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-07: Chat &amp; Navigation</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/proof-of-work" class="nav-link-custom <?= str_contains($uri, '/pro/proof-of-work') ? 'active' : '' ?>"><i class="bi bi-camera-fill" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-08: Proof of Work</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/earnings" class="nav-link-custom <?= str_contains($uri, '/pro/earnings') ? 'active' : '' ?>"><i class="bi bi-wallet2" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-09: Wallet &amp; payouts</span></a>
        <a href="<?= $e($baseUrl ?? '') ?>/pro/feedback" class="nav-link-custom <?= str_contains($uri, '/pro/feedback') ? 'active' : '' ?>"><i class="bi bi-star-half" aria-hidden="true"></i><span class="pro-nav-link-label">PRO-10: Reviews &amp; Disputes</span></a>
    </nav>
    <div class="mt-auto p-3 border-top d-flex align-items-center gap-2">
        <span class="rounded-circle bg-primary-subtle text-primary d-grid place-items-center fw-bold" style="width:38px;height:38px;place-items:center"><?= $e(strtoupper(substr($proUser['name'] ?? 'P', 0, 1))) ?></span>
        <div class="min-w-0"><div class="fw-semibold text-truncate small"><?= $e($proUser['name'] ?? 'Partner') ?></div><span class="small text-success">Partner account</span></div>
    </div>
</aside>
<div class="pro-main">
    <header class="pro-topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-sidebar-toggle="pro-sidebar-closed" aria-controls="proSidebar" aria-expanded="true" aria-label="Close partner sidebar">
                <i class="bi bi-layout-sidebar-inset me-1" aria-hidden="true"></i><span>Close menu</span>
            </button>
            <span class="pro-status"><span class="pro-status-dot"></span>Partner workspace</span>
        </div>
        <a href="<?= $e($baseUrl ?? '') ?>/logout?workspace=professional" class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-right me-1"></i>Sign out</a>
    </header>
    <main class="pro-content"><?= $viewContent ?></main>
    <footer class="px-3 py-3 text-center text-secondary border-top bg-white small">&copy; <?= date('Y') ?> Fixmate Partner Platform</footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script>if (window.gsap && window.ScrollTrigger) gsap.registerPlugin(ScrollTrigger);</script>
<script src="<?= $e($baseUrl ?? '') ?>/js/fixmate.js"></script>
</body>
</html>