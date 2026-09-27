<?php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
$activePath = $uri;

if (!empty($baseUrl) && str_starts_with($activePath, $baseUrl)) {
    $activePath = substr($activePath, strlen($baseUrl)) ?: '/';
}

$e = static fn (mixed $value): string =>
    htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= $e($pageTitle ?? 'Fixmate Admin') ?> | Fixmate</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --fm-ink: #172033;
            --fm-muted: #6b7280;
            --fm-primary: #3157d5;
            --fm-bg: #f4f6fb;
        }

        body {
            height: 100vh;
            overflow: hidden;
            background: var(--fm-bg);
            color: var(--fm-ink);
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .fm-topbar {
            position: relative;
            z-index: 1030;
            height: 66px;
            flex: 0 0 66px;
            background: #fff;
            border-bottom: 1px solid #e8ebf2;
        }

        .fm-brand {
            color: var(--fm-ink);
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -.04em;
            text-decoration: none;
        }

        .fm-brand-mark {
            display: inline-grid;
            place-items: center;
            width: 34px;
            height: 34px;
            margin-right: .55rem;
            border-radius: 11px;
            background: var(--fm-primary);
            color: #fff;
        }

        .fm-sidebar {
            width: 260px;
            height: calc(100vh - 66px);
            flex: 0 0 260px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            background: #fff;
            border-right: 1px solid #e8ebf2;
        }

        .fm-mobile-nav-toggle { display: none; }
        body.admin-sidebar-closed .fm-sidebar { display: none; }

        .fm-nav-label {
            color: #929aab;
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin: .25rem 0;
            padding: .75rem .85rem;
            border-radius: 12px;
            color: #596174;
            font-size: .9rem;
            font-weight: 650;
            text-decoration: none;
            transition:
                background-color .2s ease,
                color .2s ease,
                transform .2s ease;
        }

        .nav-link-custom:hover {
            background: #edf1ff;
            color: var(--fm-primary);
            transform: translateX(2px);
        }

        .nav-link-custom.active {
            background: #edf1ff;
            color: var(--fm-primary);
        }

        .nav-link-custom i {
            font-size: 1.05rem;
            min-width: 20px;
            text-align: center;
        }

        .nav-link-workflow-shortcut {
            position: relative;
            margin-left: .35rem;
            padding-top: .6rem;
            padding-bottom: .6rem;
            border: 1px solid #dce7ff;
            background: #f7f9ff;
            font-size: .84rem;
        }

        .workflow-shortcut-badge {
            margin-left: auto;
            padding: .16rem .38rem;
            border-radius: 999px;
            color: #147d5a;
            background: #e6f6ee;
            font-size: .58rem;
            font-weight: 800;
            letter-spacing: .05em;
        }

        .fm-main {
            min-width: 0;
            flex: 1;
            height: calc(100vh - 66px);
            overflow-y: auto;
            overscroll-behavior: contain;
            padding: clamp(1rem, 2.4vw, 2.25rem);
        }

        .card-custom {
            background: #fff;
            border: 1px solid #e8ebf2;
            border-radius: 16px;
            box-shadow: 0 5px 18px rgba(25, 39, 75, .035);
        }

        .fm-sidebar::-webkit-scrollbar,
        .fm-main::-webkit-scrollbar { width: 6px; }
        .fm-sidebar::-webkit-scrollbar-thumb,
        .fm-main::-webkit-scrollbar-thumb { background: #d9deea; border-radius: 10px; }
        .fm-sidebar::-webkit-scrollbar-track,
        .fm-main::-webkit-scrollbar-track { background: transparent; }

        @media (max-width: 767.98px) {
            body {
                height: auto;
                min-height: 100vh;
                overflow-x: hidden;
                overflow-y: auto;
            }

            .fm-topbar {
                position: sticky;
                top: 0;
            }

            .fm-shell {
                display: block !important;
                height: auto;
                overflow: visible;
            }

            .fm-sidebar {
                position: sticky;
                top: 60px;
                z-index: 1020;
                width: 100%;
                height: auto;
                padding: .5rem .75rem !important;
                overflow: visible;
                border-right: 0;
                border-bottom: 1px solid #e8ebf2;
            }

            .fm-mobile-nav-toggle {
                display: flex;
                width: 100%;
                align-items: center;
                justify-content: space-between;
                padding: .55rem .7rem;
                border: 1px solid #e8ebf2;
                border-radius: 10px;
                background: #fff;
                color: var(--fm-ink);
                font-size: .88rem;
                font-weight: 700;
            }

            .fm-sidebar-content {
                max-height: min(65vh, 560px);
                margin-top: .5rem;
                overflow-y: auto;
                overscroll-behavior: contain;
            }

            .fm-sidebar-nav .nav-link-custom {
                white-space: nowrap;
            }

            .fm-sidebar-footer {
                display: none;
            }

            .fm-main {
                height: auto;
                overflow: visible;
                padding: 1rem;
            }

            .fm-main .d-flex.justify-content-between {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 575.98px) {
            .fm-topbar .text-secondary.small { display: none; }
            .fm-topbar > div { gap: .5rem !important; }
            .fm-topbar [data-sidebar-toggle] span { display: none; }
        }
    </style>
    <link href="<?= $e($baseUrl) ?>/css/custom.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

<!-- =========================
     TOP BAR
========================= -->
<header class="fm-topbar d-flex align-items-center px-3 px-lg-4">

    <a
        class="fm-brand"
        href="<?= $e($baseUrl) ?>/admin/dashboard"
    >
        <span class="fm-brand-mark">
            <i class="bi bi-wrench-adjustable"></i>
        </span>

        fixmate
    </a>

    <div class="ms-auto d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary" type="button" data-sidebar-toggle="admin-sidebar-closed" aria-controls="adminSidebar" aria-expanded="true" aria-label="Close admin sidebar">
            <i class="bi bi-layout-sidebar-inset me-1" aria-hidden="true"></i><span>Close menu</span>
        </button>
        <span class="text-secondary small"><i class="bi bi-shield-check me-1"></i>Administration</span>
        <a class="btn btn-sm btn-outline-secondary" href="<?= $e($baseUrl) ?>/logout?workspace=admin">
            <i class="bi bi-box-arrow-right me-1"></i>Sign out
        </a>
    </div>

</header>


<!-- =========================
     ADMIN SHELL
========================= -->
<div class="fm-shell d-flex">

    <!-- =========================
         SIDEBAR
    ========================= -->
    <aside id="adminSidebar" class="fm-sidebar p-3 p-lg-4">

        <button
            class="fm-mobile-nav-toggle"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#fmAdminNav"
            aria-expanded="false"
            aria-controls="fmAdminNav"
        >
            <span><i class="bi bi-list me-2"></i>Admin menu</span>
            <i class="bi bi-chevron-down"></i>
        </button>

        <div id="fmAdminNav" class="fm-sidebar-content collapse d-md-block">
        <div class="fm-sidebar-nav">

            <!-- Navigation Heading -->
            <div class="px-2 pb-2 text-uppercase text-muted"
                 style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.8px;">
                Core Administration
            </div>


            <!-- =========================
                 ADM-01
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/dashboard"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/dashboard') ||
                    $activePath === '/' ||
                    $activePath === '/admin'
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-grid-1x2-fill"></i>
                <span>ADM-01: Admin Hub</span>
            </a>


            <!-- =========================
                 ADM-02
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/staff-users"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/staff-users')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-people-fill"></i>
                <span>ADM-02: Staff &amp; Users</span>
            </a>


            <!-- =========================
                 ADM-03
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/kyc-verifications"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/kyc-verifications')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-patch-check-fill"></i>
                <span>ADM-03: Pro &amp; KYC</span>
            </a>


            <!-- =========================
                 ADM-04
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/catalog"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/catalog')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-layers-fill"></i>
                <span>ADM-04: Services Catalog</span>
            </a>


            <!-- =========================
                 ADM-05
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/zones"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/zones')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-geo-alt-fill"></i>
                <span>ADM-05: Service Areas</span>
            </a>


            <!-- =========================
                 ADM-06
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/dispatch"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/dispatch')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-send-check-fill"></i>
                <span>ADM-06: Dispatch &amp; Completion</span>
            </a>


            <!-- =========================
                 ADM-07
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/pricing-rules"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/pricing-rules')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-tags-fill"></i>
                <span>ADM-07: Pricing &amp; Surge</span>
            </a>


            <!-- =========================
                 ADM-08
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/financials"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/financials')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-cash-stack"></i>
                <span>ADM-08: Financials &amp; Technician payouts</span>
            </a>


            <a
                href="<?= $e($baseUrl) ?>/admin/helpdesk#approval-workflow"
                class="nav-link-custom nav-link-workflow-shortcut <?= str_contains($activePath, '/admin/helpdesk') ? 'active' : '' ?>"
                aria-label="Open the live approval and completion queue"
            >
                <i class="bi bi-check2-square"></i>
                <span>Approvals &amp; Completion</span>
                <span class="workflow-shortcut-badge">LIVE</span>
            </a>


            <!-- =========================
                 ADM-10
            ========================= -->
            <a
                href="<?= $e($baseUrl) ?>/admin/settings"
                class="nav-link-custom <?= 
                    str_contains($activePath, '/admin/settings')
                        ? 'active'
                        : ''
                ?>"
            >
                <i class="bi bi-sliders"></i>
                <span>ADM-10: System Config</span>
            </a>

        </div>


        <!-- Sidebar Footer -->
        <div class="fm-sidebar-footer mt-4 px-2 text-secondary small">
            <i class="bi bi-building me-1"></i>
            Fixmate operations portal
        </div>

        </div>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================= -->
    <main class="fm-main">

        <?= $viewContent ?>

        <footer class="pt-4 mt-4 border-top text-secondary small">
            &copy; <?= date('Y') ?> Fixmate
        </footer>

    </main>

</div>


<!-- =========================
     BOOTSTRAP JS
========================= -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script>if (window.gsap && window.ScrollTrigger) gsap.registerPlugin(ScrollTrigger);</script>
<script src="<?= $e($baseUrl) ?>/js/fixmate.js"></script>

</body>
</html>