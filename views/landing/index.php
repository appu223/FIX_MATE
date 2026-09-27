<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'FixMate - Premium Home Services & Architectural Repairs') ?></title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Plus Jakarta Sans & Cabinet / Space Grotesk -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- GSAP 3.12.5 & ScrollTrigger -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

    <!-- Global Base Path Script -->
    <script>
        window.fixmateBasePath = <?= json_encode($baseUrl ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('a[href^="/"]').forEach(function (link) {
                const originalHref = link.getAttribute('href');
                if (!originalHref.startsWith(window.fixmateBasePath)) {
                    link.setAttribute('href', window.fixmateBasePath + originalHref);
                }
            });
        });
    </script>

    <style>
        :root {
            /* Warm Cream, Butter & Gold Palette */
            --fm-cream-bg: #fdfbf7;
            --fm-cream-surface: #ffffff;
            --fm-cream-card: #fdf9ee;
            --fm-ivory-light: #f7f3e8;
            --fm-sand-border: #e8e2d2;
            --fm-sand-dark: #d9cfba;

            --fm-amber-primary: #eab308;
            --fm-amber-deep: #ca8a04;
            --fm-amber-glow: #fef08a;
            --fm-amber-soft: #fefce8;

            --fm-ink-primary: #1e1b18;
            --fm-ink-muted: #6b645b;
            --fm-ink-soft: #9c9488;

            --fm-success: #15803d;
            --fm-success-light: #dcfce7;
            --fm-accent-terracotta: #c2410c;

            --fm-radius-xl: 1.5rem;
            --fm-radius-lg: 1rem;
            --fm-radius-pill: 9999px;
            --fm-shadow-sm: 0 2px 8px rgba(120, 53, 15, 0.04);
            --fm-shadow-md: 0 10px 30px rgba(120, 53, 15, 0.08);
            --fm-shadow-lg: 0 20px 50px rgba(120, 53, 15, 0.12);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--fm-cream-bg);
            color: var(--fm-ink-primary);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.02em;
            color: var(--fm-ink-primary);
        }

        /* Smooth Anchor Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Glassmorphic Cream Navbar */
        .glass-cream-nav {
            background: rgba(253, 251, 247, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--fm-sand-border);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            transition: all 0.3s ease;
        }

        .nav-link-cream {
            color: var(--fm-ink-muted);
            font-weight: 600;
            font-size: 0.92rem;
            padding: 0.5rem 1rem !important;
            transition: all 0.2s ease;
            border-radius: var(--fm-radius-pill);
        }

        .nav-link-cream:hover {
            color: var(--fm-ink-primary);
            background-color: var(--fm-ivory-light);
        }

        /* Gold Gradient Badge Pill */
        .badge-gold {
            background: linear-gradient(135deg, #fef08a 0%, #fef9c3 100%);
            color: #854d0e;
            border: 1px solid #fde047;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-verified-pro {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            font-weight: 700;
        }

        /* Warm Cream Cards */
        .card-cream {
            background-color: var(--fm-cream-surface);
            border: 1px solid var(--fm-sand-border);
            border-radius: var(--fm-radius-xl);
            box-shadow: var(--fm-shadow-sm);
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .card-cream:hover {
            transform: translateY(-6px);
            box-shadow: var(--fm-shadow-lg);
            border-color: #facc15;
        }

        .card-featured {
            background: linear-gradient(180deg, #ffffff 0%, var(--fm-cream-card) 100%);
            border: 1.5px solid #fde047;
            position: relative;
        }

        /* Amber Highlight Accent Border */
        .border-amber-glow {
            border: 2px solid #facc15 !important;
            box-shadow: 0 0 25px rgba(234, 179, 8, 0.18) !important;
        }

        /* Buttons */
        .btn-amber-primary {
            background: linear-gradient(135deg, #facc15 0%, #eab308 100%);
            color: #713f12;
            border: 1px solid #ca8a04;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(234, 179, 8, 0.3);
            transition: all 0.25s ease;
        }

        .btn-amber-primary:hover {
            background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
            color: #ffffff;
            box-shadow: 0 8px 25px rgba(234, 179, 8, 0.45);
            transform: translateY(-2px);
        }

        .btn-amber-dark {
            background: var(--fm-ink-primary);
            color: #ffffff;
            border: 1px solid #332d27;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(30, 27, 24, 0.15);
            transition: all 0.25s ease;
        }

        .btn-amber-dark:hover {
            background: #2e2822;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 27, 24, 0.25);
        }

        .btn-outline-amber {
            border: 1.5px solid var(--fm-sand-dark);
            color: var(--fm-ink-primary);
            background: transparent;
            font-weight: 600;
            transition: all 0.25s ease;
        }

        .btn-outline-amber:hover {
            background: var(--fm-ivory-light);
            border-color: var(--fm-amber-primary);
            color: var(--fm-amber-deep);
        }

        /* Carousel Master Styling */
        .carousel-cream-item {
            height: 82vh;
            min-height: 620px;
            position: relative;
            background-size: cover;
            background-position: center;
        }

        .carousel-cream-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                90deg, 
                rgba(253, 251, 247, 0.96) 0%, 
                rgba(253, 251, 247, 0.88) 50%, 
                rgba(253, 251, 247, 0.3) 100%
            );
        }

        @media (max-width: 991px) {
            .carousel-cream-overlay {
                background: linear-gradient(
                    180deg, 
                    rgba(253, 251, 247, 0.94) 0%, 
                    rgba(253, 251, 247, 0.85) 100%
                );
            }
        }

        /* Carousel Indicators - Amber Bars */
        .carousel-indicators-cream [data-bs-target] {
            width: 14px;
            height: 10px;
            border-radius: var(--fm-radius-pill);
            background-color: var(--fm-sand-dark);
            border: none;
            margin: 0 6px;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .carousel-indicators-cream .active {
            width: 44px;
            background-color: var(--fm-amber-deep);
        }

        /* Double Animated Marquee Rail */
        .marquee-container-warm {
            overflow: hidden;
            user-select: none;
            display: flex;
            gap: 1.5rem;
            padding: 1.1rem 0;
            position: relative;
            background: linear-gradient(90deg, #fefce8, #fef08a, #fffbeb, #fefce8);
            border-top: 1px solid #fde047;
            border-bottom: 1px solid #fde047;
        }

        .marquee-track-forward {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-around;
            gap: 2.5rem;
            min-width: 100%;
            animation: scrollForward 28s linear infinite;
        }

        .marquee-track-reverse {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-around;
            gap: 2.5rem;
            min-width: 100%;
            animation: scrollReverse 28s linear infinite;
        }

        @keyframes scrollForward {
            from { transform: translateX(0); }
            to { transform: translateX(-100%); }
        }

        @keyframes scrollReverse {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }

        .marquee-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.88rem;
            font-weight: 700;
            color: #713f12;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        /* Category Orb & Icons */
        .category-orb-cream {
            width: 68px;
            height: 68px;
            border-radius: 1.25rem;
            background: #fffbeb;
            border: 1.5px solid #fde047;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.85rem;
            color: #ca8a04;
            transition: all 0.35s ease;
        }

        .card-cream:hover .category-orb-cream {
            background: #eab308;
            color: #ffffff;
            transform: scale(1.08) rotate(4deg);
            box-shadow: 0 10px 20px rgba(234, 179, 8, 0.3);
        }

        /* Floating Trust Badge */
        .trust-floating-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid var(--fm-sand-border);
            border-radius: 1.25rem;
            box-shadow: var(--fm-shadow-md);
            padding: 1.25rem 1.5rem;
        }

        /* Estimator Calculator Card */
        .calculator-panel {
            background: #ffffff;
            border: 2px solid var(--fm-sand-border);
            border-radius: var(--fm-radius-xl);
            box-shadow: var(--fm-shadow-md);
        }

        .calculator-receipt {
            background: #fffdfa;
            border: 2px dashed #facc15;
            border-radius: var(--fm-radius-xl);
        }

        /* Custom Form Controls */
        .form-control-cream {
            background-color: #faf7ee;
            border: 1.5px solid var(--fm-sand-border);
            color: var(--fm-ink-primary);
            border-radius: 0.85rem;
            padding: 0.75rem 1.1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .form-control-cream:focus {
            background-color: #ffffff;
            border-color: var(--fm-amber-primary);
            box-shadow: 0 0 0 4px rgba(234, 179, 8, 0.15);
            color: var(--fm-ink-primary);
        }

        /* Footer */
        .footer-cream {
            background-color: #f5efe1;
            border-top: 1px solid var(--fm-sand-border);
            color: var(--fm-ink-muted);
        }

        /* Text Gradient Gold/Bronze */
        .text-gradient-amber {
            background: linear-gradient(135deg, #a16207 0%, #ca8a04 50%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 9px; }
        ::-webkit-scrollbar-track { background: var(--fm-cream-bg); }
        ::-webkit-scrollbar-thumb { background: #d9cfba; border-radius: 5px; }
        ::-webkit-scrollbar-thumb:hover { background: #c2b59b; }
    </style>
</head>
<body>

    <!-- ==========================================
         1. UNIVERSAL GLASS CREAM TOPBAR
         ========================================== -->
    <nav class="navbar navbar-expand-lg glass-cream-nav py-3">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="/">
                <div class="bg-amber text-dark rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; background: linear-gradient(135deg, #fde047 0%, #eab308 100%); border: 1px solid #ca8a04;">
                    <i class="bi bi-wrench-adjustable-circle-fill fs-5 text-dark"></i>
                </div>
                <div class="lh-1">
                    <span class="fs-4 fw-bold text-dark font-heading">FixMate<span class="text-warning">.pro</span></span>
                    <span class="d-block small text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em; font-weight: 700;">Urban Home Care</span>
                </div>
            </a>

            <!-- Navbar Toggler for Mobile -->
            <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navFixmateMenu">
                <i class="bi bi-list fs-2 text-dark"></i>
            </button>

            <!-- Links & Action Buttons -->
            <div class="collapse navbar-collapse" id="navFixmateMenu">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1 text-center">
                    <li class="nav-item"><a class="nav-link nav-link-cream" href="#services"><i class="bi bi-grid-fill text-warning me-1"></i>Services</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-cream" href="#calculator"><i class="bi bi-calculator-fill text-warning me-1"></i>Cost Estimator</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-cream" href="#technicians"><i class="bi bi-shield-check text-warning me-1"></i>Verified Pros</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-cream" href="#warranty"><i class="bi bi-patch-check-fill text-warning me-1"></i>30-Day Guarantee</a></li>
                </ul>

                <div class="d-flex align-items-center justify-content-center gap-2 mt-3 mt-lg-0">
                    <button class="btn btn-outline-amber rounded-pill px-3 py-2 text-dark" onclick="openUniversalAuthModal('customer')">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Customer Login
                    </button>
                    <button class="btn btn-amber-dark rounded-pill px-3 py-2 text-white" onclick="openCustomerRegisterModal()">
                        <i class="bi bi-person-plus-fill me-1"></i> Sign Up
                    </button>
                    <button class="btn btn-amber-primary rounded-pill px-4 py-2" onclick="openUniversalAuthModal('admin')">
                        <i class="bi bi-shield-lock-fill me-1"></i> Portal Gateway
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- ==========================================
         2. MASTER 5-SLIDE FULLSCREEN HERO CAROUSEL
         ========================================== -->
    <section class="position-relative overflow-hidden" style="margin-top: 76px;">
        <div id="masterHeroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6500">
            <!-- Custom Indicators -->
            <div class="carousel-indicators carousel-indicators-cream mb-4">
                <button type="button" data-bs-target="#masterHeroCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#masterHeroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#masterHeroCarousel" data-bs-slide-to="2"></button>
                <button type="button" data-bs-target="#masterHeroCarousel" data-bs-slide-to="3"></button>
                <button type="button" data-bs-target="#masterHeroCarousel" data-bs-slide-to="4"></button>
            </div>

            <div class="carousel-inner">
                <!-- SLIDE 1: Master Electrician & Automation -->
                <div class="carousel-item active">
                    <div class="carousel-cream-item d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=1920&q=80');">
                        <div class="carousel-cream-overlay"></div>
                        <div class="container position-relative z-2">
                            <div class="row align-items-center">
                                <div class="col-lg-8 text-center text-lg-start gs-hero-stagger">
                                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center gap-2">
                                        <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
                                        60-Minute Urban Electrical Express
                                    </span>
                                    <h1 class="display-3 fw-bold tracking-tight text-dark mb-3 font-heading">
                                        Flawless Power. <br><span class="text-gradient-amber">Certified Master Electricians.</span>
                                    </h1>
                                    <p class="lead text-muted mb-4 pe-lg-5" style="max-width: 650px;">
                                        Diagnostic triage for short circuits, modular DB box overhauls, high-load appliance cabling, and automated smart switches at guaranteed flat rates.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                        <a href="/customer" class="btn btn-amber-primary rounded-pill px-4 py-3 fs-6">
                                            <i class="bi bi-cart-plus-fill me-1"></i> Book Electrician (₹399)
                                        </a>
                                        <button class="btn btn-amber-dark rounded-pill px-4 py-3" onclick="openUniversalAuthModal('professional')">
                                            <i class="bi bi-tools me-1"></i> Join Technician Roster
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 2: Precision High-Rise Plumbing -->
                <div class="carousel-item">
                    <div class="carousel-cream-item d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=1920&q=80');">
                        <div class="carousel-cream-overlay"></div>
                        <div class="container position-relative z-2">
                            <div class="row align-items-center">
                                <div class="col-lg-8 text-center text-lg-start gs-hero-stagger">
                                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-water text-primary"></i> Concealed Pipe Diagnostics
                                    </span>
                                    <h1 class="display-3 fw-bold tracking-tight text-dark mb-3 font-heading">
                                        Zero Leakage. <br><span class="text-gradient-amber">Engineered Sanitary Systems.</span>
                                    </h1>
                                    <p class="lead text-muted mb-4 pe-lg-5" style="max-width: 650px;">
                                        High-pressure motorized snake clearing for blocked drain corridors, acoustic wall leak detection, and precision bath fixture mounting.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                        <a href="/customer" class="btn btn-amber-primary rounded-pill px-4 py-3 fs-6">
                                            <i class="bi bi-wrench-adjustable me-1"></i> Dispatch Plumber (₹299)
                                        </a>
                                        <a href="#calculator" class="btn btn-amber-dark rounded-pill px-4 py-3">
                                            <i class="bi bi-calculator me-1"></i> Estimate Repair Cost
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 3: Foam Jet AC Maintenance -->
                <div class="carousel-item">
                    <div class="carousel-cream-item d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=1920&q=80');">
                        <div class="carousel-cream-overlay"></div>
                        <div class="container position-relative z-2">
                            <div class="row align-items-center">
                                <div class="col-lg-8 text-center text-lg-start gs-hero-stagger">
                                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-snow text-info"></i> Anti-Bacterial Coil Care
                                    </span>
                                    <h1 class="display-3 fw-bold tracking-tight text-dark mb-3 font-heading">
                                        Deep Foam Jet AC Wash. <br><span class="text-gradient-amber">Slash Power Bills 25%.</span>
                                    </h1>
                                    <p class="lead text-muted mb-4 pe-lg-5" style="max-width: 650px;">
                                        Dual-turbine high-pressure wash flushes hidden mould and dust from indoor cooling fins. Vacuum test brazing with guaranteed R32 gas recharging.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                        <a href="/customer" class="btn btn-amber-primary rounded-pill px-4 py-3 fs-6">
                                            <i class="bi bi-snow2 me-1"></i> Book Foam Jet AC (₹899)
                                        </a>
                                        <button class="btn btn-amber-dark rounded-pill px-4 py-3" onclick="openUniversalAuthModal('customer')">
                                            Apply Voucher 'FIX20'
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 4: Full Villa Deep Cleaning -->
                <div class="carousel-item">
                    <div class="carousel-cream-item d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1920&q=80');">
                        <div class="carousel-cream-overlay"></div>
                        <div class="container position-relative z-2">
                            <div class="row align-items-center">
                                <div class="col-lg-8 text-center text-lg-start gs-hero-stagger">
                                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-stars text-warning"></i> Single-Disc Machine Scrubbing
                                    </span>
                                    <h1 class="display-3 fw-bold tracking-tight text-dark mb-3 font-heading">
                                        Immaculate Living. <br><span class="text-gradient-amber">Deep Sanitization Squads.</span>
                                    </h1>
                                    <p class="lead text-muted mb-4 pe-lg-5" style="max-width: 650px;">
                                        Floor buffing machines, tile grout descaling, kitchen chimney degreasing, and upholstery steam sanitation by background-checked cleaning squads.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                        <a href="/customer" class="btn btn-amber-primary rounded-pill px-4 py-3 fs-6">
                                            <i class="bi bi-sparkles me-1"></i> Book Deep Scrub (₹3,499)
                                        </a>
                                        <a href="/customer/custom-quote" class="btn btn-amber-dark rounded-pill px-4 py-3">
                                            <i class="bi bi-journal-text me-1"></i> Custom Villa Quote
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SLIDE 5: Custom Woodwork & Millwork -->
                <div class="carousel-item">
                    <div class="carousel-cream-item d-flex align-items-center" style="background-image: url('https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=1920&q=80');">
                        <div class="carousel-cream-overlay"></div>
                        <div class="container position-relative z-2">
                            <div class="row align-items-center">
                                <div class="col-lg-8 text-center text-lg-start gs-hero-stagger">
                                    <span class="badge badge-gold rounded-pill px-3 py-2 mb-3 d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-hammer text-danger"></i> Hardware & Hydraulic Upgrades
                                    </span>
                                    <h1 class="display-3 fw-bold tracking-tight text-dark mb-3 font-heading">
                                        Craftsman Carpentry. <br><span class="text-gradient-amber">Seamless Door & Lock Fits.</span>
                                    </h1>
                                    <p class="lead text-muted mb-4 pe-lg-5" style="max-width: 650px;">
                                        Soft-close hinge retrofits, sliding wardrobe realignments, electronic biometric lock mounting, and furniture repairs by artisan woodworkers.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                                        <a href="/customer" class="btn btn-amber-primary rounded-pill px-4 py-3 fs-6">
                                            <i class="bi bi-door-closed me-1"></i> Book Wood Artisan (₹499)
                                        </a>
                                        <button class="btn btn-amber-dark rounded-pill px-4 py-3" onclick="openUniversalAuthModal('admin')">
                                            <i class="bi bi-speedometer2 me-1"></i> Admin Dispatch Room
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carousel Controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#masterHeroCarousel" data-bs-slide="prev" style="width: 5%;">
                <span class="carousel-control-prev-icon rounded-circle p-3" style="background-color: var(--fm-ink-primary);" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#masterHeroCarousel" data-bs-slide="next" style="width: 5%;">
                <span class="carousel-control-next-icon rounded-circle p-3" style="background-color: var(--fm-ink-primary);" aria-hidden="true"></span>
            </button>
        </div>
    </section>

    <!-- ==========================================
         3. WARM CREAM DUAL MARQUEE RAIL
         ========================================== -->
    <div class="marquee-container-warm shadow-sm">
        <div class="marquee-track-forward">
            <span class="marquee-pill"><i class="bi bi-patch-check-fill text-warning"></i> 100% Aadhaar & Background Verified</span>
            <span class="marquee-pill"><i class="bi bi-shield-check text-success"></i> 30-Day FixMate Free Re-Work Warranty</span>
            <span class="marquee-pill"><i class="bi bi-currency-rupee text-dark"></i> Upfront Pricing & Zero Hidden Charges</span>
            <span class="marquee-pill"><i class="bi bi-lightning-charge-fill text-warning"></i> 60-Minute Urban Express Arrival</span>
            <span class="marquee-pill"><i class="bi bi-star-fill text-warning"></i> 4.92 / 5 Average Client Rating</span>
            <span class="marquee-pill"><i class="bi bi-shield-lock-fill text-success"></i> ₹10,000 Property Cover Protection</span>
        </div>
        <div class="marquee-track-forward" aria-hidden="true">
            <span class="marquee-pill"><i class="bi bi-patch-check-fill text-warning"></i> 100% Aadhaar & Background Verified</span>
            <span class="marquee-pill"><i class="bi bi-shield-check text-success"></i> 30-Day FixMate Free Re-Work Warranty</span>
            <span class="marquee-pill"><i class="bi bi-currency-rupee text-dark"></i> Upfront Pricing & Zero Hidden Charges</span>
            <span class="marquee-pill"><i class="bi bi-lightning-charge-fill text-warning"></i> 60-Minute Urban Express Arrival</span>
            <span class="marquee-pill"><i class="bi bi-star-fill text-warning"></i> 4.92 / 5 Average Client Rating</span>
            <span class="marquee-pill"><i class="bi bi-shield-lock-fill text-success"></i> ₹10,000 Property Cover Protection</span>
        </div>
    </div>

    <!-- ==========================================
         4. STATS COUNTER STRIP
         ========================================== -->
    <section class="py-5" style="background-color: var(--fm-cream-card); border-bottom: 1px solid var(--fm-sand-border);">
        <div class="container">
            <div class="row g-4 text-center">
                <div class="col-6 col-md-3">
                    <h2 class="display-5 fw-bold text-dark mb-1 font-heading count-stat"><?= $stats['happy_customers'] ?? '42,500+' ?></h2>
                    <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.05em;">Homes Served</span>
                </div>
                <div class="col-6 col-md-3">
                    <h2 class="display-5 fw-bold text-dark mb-1 font-heading count-stat"><?= $stats['verified_pros'] ?? '3,800+' ?></h2>
                    <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.05em;">Certified Technicians</span>
                </div>
                <div class="col-6 col-md-3">
                    <h2 class="display-5 fw-bold text-dark mb-1 font-heading count-stat"><?= $stats['completed_repairs'] ?? '98,400+' ?></h2>
                    <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.05em;">Repairs Completed</span>
                </div>
                <div class="col-6 col-md-3">
                    <h2 class="display-5 fw-bold text-dark mb-1 font-heading count-stat"><?= $stats['cities_active'] ?? '14+' ?></h2>
                    <span class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.05em;">Metro Zones Active</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================
         5. SPECIALIZED VERTICAL CATEGORIES
         ========================================== -->
    <section id="services" class="py-5 my-4">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge badge-gold rounded-pill px-3 py-2 text-uppercase mb-2">
                    Verified Craftsmanship
                </span>
                <h2 class="display-5 fw-bold text-dark mt-2 font-heading">What Needs Fixing Today?</h2>
                <p class="text-muted mx-auto" style="max-width: 620px;">
                    Select an engineering vertical below to view verified technicians, fixed transparent rates, and immediate 60-minute dispatch slots.
                </p>
            </div>

            <div class="row g-4">
                <?php foreach ($categories as $cat): ?>
                    <div class="col-12 col-sm-6 col-lg-4 gs-reveal-category">
                        <a href="/customer?cat=<?= $cat['id'] ?>" class="card-cream p-4 d-block text-decoration-none h-100">
                            <div class="category-orb-cream mb-3">
                                <i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2 font-heading"><?= htmlspecialchars($cat['name']) ?></h4>
                            <p class="text-muted small mb-3"><?= htmlspecialchars($cat['description']) ?></p>
                            <div class="d-flex align-items-center text-warning fw-bold small">
                                <span>Browse Catalog</span>
                                <i class="bi bi-arrow-right ms-2"></i>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ==========================================
         6. INTERACTIVE LIVE BOOKING COST ESTIMATOR
         ========================================== -->
    <section id="calculator" class="py-5" style="background-color: var(--fm-ivory-light); border-top: 1px solid var(--fm-sand-border); border-bottom: 1px solid var(--fm-sand-border);">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Left: Controls -->
                <div class="col-lg-6">
                    <span class="badge badge-gold rounded-pill px-3 py-2 text-uppercase mb-3">
                        Transparent Price Matrix
                    </span>
                    <h2 class="display-5 fw-bold text-dark mb-3 font-heading">Instant Cost Estimator</h2>
                    <p class="text-muted mb-4">
                        Calculate exact checkout pricing with zero guesswork. Configure your required service, volume, and urgency level to inspect itemized tax and coupon subsidies in Indian Rupees (₹).
                    </p>

                    <div class="p-4 calculator-panel">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Select Service Task</label>
                            <select id="estimator_service" class="form-select form-control-cream" onchange="calculateLiveEstimate()">
                                <option value="399" selected>Ceiling Fan Installation & Repair (₹399.00)</option>
                                <option value="649">Modular Switchboard Wiring Replacement (₹649.00)</option>
                                <option value="299">Tap & Sink Mixer Leakage Seal (₹299.00)</option>
                                <option value="799">Drainage Clog & Pipe Jet Clear (₹799.00)</option>
                                <option value="899">Split AC Deep Foam Jet Servicing (₹899.00)</option>
                                <option value="2499">AC Gas Leak Check & R32 Recharging (₹2,499.00)</option>
                                <option value="3499">2BHK Complete Deep Cleaning Scrub (₹3,499.00)</option>
                                <option value="499">Door Lock & Cylinder Replacement (₹499.00)</option>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label small text-muted fw-bold">Quantity / Units</label>
                                <input type="number" id="estimator_qty" class="form-control form-control-cream" min="1" max="10" value="1" oninput="calculateLiveEstimate()">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-muted fw-bold">Discount Coupon</label>
                                <input type="text" id="estimator_coupon" class="form-control form-control-cream text-uppercase font-monospace" placeholder="FIX20" value="FIX20" oninput="calculateLiveEstimate()">
                            </div>
                        </div>

                        <div class="p-3 rounded-3 mb-2" style="background-color: var(--fm-cream-card); border: 1px solid var(--fm-sand-border);">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="estimator_urgent" onchange="calculateLiveEstimate()">
                                <label class="form-check-label text-dark small fw-bold" for="estimator_urgent">
                                    Express 60-Minute Emergency Dispatch (+25% Priority Surge)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Itemized Receipt -->
                <div class="col-lg-6">
                    <div class="p-5 calculator-receipt text-center position-relative">
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill small mb-2 text-uppercase fw-bold">
                            Live Checkout Estimate
                        </span>
                        
                        <h1 class="display-3 fw-bold text-dark my-2 font-heading" id="est_total_display">₹442.00</h1>
                        
                        <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold mb-4" id="est_discount_badge">
                            <i class="bi bi-tag-fill me-1"></i> Coupon 'FIX20' Applied (₹80.00 Saved)
                        </div>

                        <div class="border-top pt-3 text-start small" style="border-color: var(--fm-sand-border) !important;">
                            <div class="d-flex justify-content-between text-muted py-1">
                                <span>Base Labor / Task Charges:</span>
                                <span class="fw-bold text-dark" id="est_lbl_base">₹399.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted py-1">
                                <span>Emergency Dispatch Multiplier:</span>
                                <span class="fw-bold text-dark" id="est_lbl_surge">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted py-1">
                                <span>Promotional Discount:</span>
                                <span class="fw-bold text-success" id="est_lbl_discount">- ₹80.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted py-1">
                                <span>GST Tax (18.0%):</span>
                                <span class="fw-bold text-dark" id="est_lbl_tax">₹67.00</span>
                            </div>
                            <div class="d-flex justify-content-between text-dark py-2 border-top fw-bold fs-5 mt-2" style="border-color: var(--fm-sand-border) !important;">
                                <span>Estimated Payable:</span>
                                <span class="text-warning text-dark font-heading" id="est_lbl_grand_total">₹442.00</span>
                            </div>
                        </div>

                        <a href="/customer" class="btn btn-amber-primary w-100 rounded-pill py-3 fw-bold mt-4 fs-6">
                            Proceed to Instant Booking <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================
         7. TOP VERIFIED TECHNICIANS ROSTER
         ========================================== -->
    <section id="technicians" class="py-5 my-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5">
                <div>
                    <span class="badge badge-gold rounded-pill px-3 py-2 text-uppercase mb-2">
                        Certified Tradesmen
                    </span>
                    <h2 class="display-5 fw-bold text-dark mt-2 font-heading">Meet Bengaluru's Top Rated Pros</h2>
                    <p class="text-muted m-0">Police-verified, background-checked master technicians available in your zone.</p>
                </div>
                <a href="/customer/pros" class="btn btn-outline-amber rounded-pill px-4 py-2 mt-3 mt-md-0">
                    View Complete Directory <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php foreach ($topPros as $pro): ?>
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card-cream p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px; font-size: 1.25rem; background: linear-gradient(135deg, #fef08a 0%, #eab308 100%); border: 1.5px solid #ca8a04;">
                                        <?= strtoupper(substr($pro['name'] ?? 'T', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark m-0 font-heading"><?= htmlspecialchars($pro['name']) ?></h5>
                                        <span class="badge badge-verified-pro rounded-pill px-2 py-0 small" style="font-size: 0.68rem;">
                                            <i class="bi bi-shield-check"></i> Verified
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center text-warning fw-bold small mb-2">
                                    <i class="bi bi-star-fill me-1"></i> <?= number_format((float)($pro['rating_avg'] ?? 5.0), 2) ?>
                                    <span class="text-muted ms-1">(<?= $pro['rating_count'] ?? 0 ?> jobs completed)</span>
                                </div>

                                <p class="text-muted small mb-3">
                                    <?= htmlspecialchars($pro['bio'] ?? 'Senior certified technician with 7+ years of experience across high-rise residential installations.') ?>
                                </p>
                            </div>

                            <div class="border-top pt-3 d-flex justify-content-between align-items-center" style="border-color: var(--fm-sand-border) !important;">
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small">
                                    <?= $pro['experience_years'] ?? 5 ?> Yrs Experience
                                </span>
                                <a href="/customer" class="btn btn-sm btn-amber-primary rounded-pill px-3">
                                    Hire Partner
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ==========================================
         8. REVERSE MARQUEE: VERIFIED REVIEWS
         ========================================== -->
    <div class="marquee-container-warm" style="background: linear-gradient(90deg, #fffbeb, #fefce8, #fef08a, #fffbeb);">
        <div class="marquee-track-reverse">
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Amit repaired our AC within 45 mins. Truly lifesaver!" - Priya P.</span>
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Clear upfront pricing. No arguing over bills at doorstep." - Rohan G.</span>
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Cleaned whole 2BHK spotless for housewarming." - Ananya I.</span>
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Prompt, uniformed, polite, and verified by Aadhaar." - Karan M.</span>
        </div>
        <div class="marquee-track-reverse" aria-hidden="true">
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Amit repaired our AC within 45 mins. Truly lifesaver!" - Priya P.</span>
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Clear upfront pricing. No arguing over bills at doorstep." - Rohan G.</span>
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Cleaned whole 2BHK spotless for housewarming." - Ananya I.</span>
            <span class="marquee-pill"><i class="bi bi-chat-quote-fill text-warning"></i> "Prompt, uniformed, polite, and verified by Aadhaar." - Karan M.</span>
        </div>
    </div>

    <!-- ==========================================
         9. 30-DAY WARRANTY & INSURANCE RIBBON
         ========================================== -->
    <section id="warranty" class="py-5" style="background-color: var(--fm-cream-card);">
        <div class="container text-center">
            <div class="row g-4 justify-content-center">
                <div class="col-12 col-md-4">
                    <div class="p-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: #fef08a;">
                            <i class="bi bi-patch-check-fill text-warning display-6"></i>
                        </div>
                        <h4 class="fw-bold text-dark font-heading">30-Day Re-Work Warranty</h4>
                        <p class="text-muted small m-0">If the resolved fitting or appliance falters within 30 days, we dispatch a senior lead to fix it at zero extra charge.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: #dcfce7;">
                            <i class="bi bi-shield-lock-fill text-success display-6"></i>
                        </div>
                        <h4 class="fw-bold text-dark font-heading">₹10,000 Property Cover</h4>
                        <p class="text-muted small m-0">Every booking is insured under FixMate Property Assurance, protecting your fittings and home fixtures throughout the job.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; background: #fee2e2;">
                            <i class="bi bi-stopwatch-fill text-danger display-6"></i>
                        </div>
                        <h4 class="fw-bold text-dark font-heading">Guaranteed Punctuality</h4>
                        <p class="text-muted small m-0">Technicians arrive strictly within your scheduled window, or ₹100 is automatically credited back to your FixMate wallet.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==========================================
         10. LUXURY WARM CREAM FOOTER
         ========================================== -->
    <footer class="footer-cream pt-5 pb-4">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Col 1: Brand Info -->
                <div class="col-12 col-lg-4">
                    <a class="d-flex align-items-center gap-2 text-decoration-none mb-3" href="/">
                        <div class="bg-amber text-dark rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; background: linear-gradient(135deg, #fde047 0%, #eab308 100%);">
                            <i class="bi bi-wrench-adjustable-circle-fill fs-5 text-dark"></i>
                        </div>
                        <span class="fs-4 fw-bold text-dark font-heading">FixMate<span class="text-warning">.pro</span></span>
                    </a>
                    <p class="small text-muted pe-lg-4">
                        India's premier on-demand home repair, HVAC maintenance, electrical engineering, and deep sanitization ecosystem. Trusted by over 40,000+ residences across major metropolitan cities.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-amber rounded-circle"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-amber rounded-circle"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-amber rounded-circle"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-amber rounded-circle"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <!-- Col 2: Services -->
                <div class="col-6 col-lg-2">
                    <h6 class="text-dark fw-bold mb-3 font-heading">Trade Verticals</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><a href="/customer?cat=1" class="text-muted text-decoration-none hover-dark">Electrical Repair</a></li>
                        <li class="mb-2"><a href="/customer?cat=2" class="text-muted text-decoration-none hover-dark">Plumbing Solutions</a></li>
                        <li class="mb-2"><a href="/customer?cat=3" class="text-muted text-decoration-none hover-dark">AC Foam Jet Wash</a></li>
                        <li class="mb-2"><a href="/customer?cat=5" class="text-muted text-decoration-none hover-dark">Deep Sanitization</a></li>
                        <li class="mb-2"><a href="/customer?cat=6" class="text-muted text-decoration-none hover-dark">Woodwork & Carpentry</a></li>
                    </ul>
                </div>

                <!-- Col 3: Portal Direct -->
                <div class="col-6 col-lg-2">
                    <h6 class="text-dark fw-bold mb-3 font-heading">Portals</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><a href="#" onclick="openUniversalAuthModal('customer'); return false;" class="text-muted text-decoration-none">Customer Portal</a></li>
                        <li class="mb-2"><a href="#" onclick="openCustomerRegisterModal(); return false;" class="text-muted text-decoration-none">Customer Registration</a></li>
                        <li class="mb-2"><a href="#" onclick="openUniversalAuthModal('professional'); return false;" class="text-muted text-decoration-none">Technician Hub</a></li>
                        <li class="mb-2"><a href="#" onclick="openUniversalAuthModal('admin'); return false;" class="text-muted text-decoration-none">Control Center</a></li>
                        <li class="mb-2"><a href="/customer/custom-quote" class="text-muted text-decoration-none">Custom Project RFQ</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact -->
                <div class="col-12 col-lg-4">
                    <h6 class="text-dark fw-bold mb-3 font-heading">Helpline Support</h6>
                    <p class="small text-muted mb-2"><i class="bi bi-telephone-fill text-warning me-2"></i> +91 80 4920 1800 (National Toll-Free 8AM - 10PM)</p>
                    <p class="small text-muted mb-3"><i class="bi bi-envelope-fill text-warning me-2"></i> care@fixmate.in</p>
                    <div class="p-3 rounded-3 small" style="background-color: var(--fm-cream-surface); border: 1px solid var(--fm-sand-border);">
                        <strong>Headquarters:</strong> FixMate Technologies Pvt Ltd, 100ft Ring Road, Koramangala 4th Block, Bengaluru, KA - 560034.
                    </div>
                </div>
            </div>

            <div class="border-top pt-3 d-flex flex-column flex-md-row justify-content-between text-muted small" style="border-color: var(--fm-sand-border) !important;">
                <span>&copy; <?= date('Y') ?> FixMate Technologies India Private Limited. All rights reserved.</span>
                <span>GST Registered: 29AAAAA0000A1Z5 | ISO 9001:2015 Certified Operations</span>
            </div>
        </div>
    </footer>

    <!-- ==========================================
         11. UNIFIED AUTHENTICATION GATEWAY MODAL
         ========================================== -->
    <div class="modal fade" id="universalAuthModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="background: var(--fm-cream-surface); border-radius: 1.5rem; border: 1.5px solid var(--fm-sand-border);">
                <!-- Modal Header -->
                <div class="modal-header border-bottom pb-3" style="border-color: var(--fm-sand-border) !important;">
                    <div>
                        <h5 class="modal-title fw-bold text-dark font-heading m-0" id="authGatewayTitle">Portal Access Gateway</h5>
                        <span class="text-muted small" id="authGatewaySubtitle">Select your role to sign into FixMate</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4">
                    <!-- Role Switcher Pills -->
                    <div class="d-flex gap-2 p-1 rounded-pill mb-4" style="background-color: #faf7ee; border: 1px solid var(--fm-sand-border);">
                        <button type="button" class="btn btn-sm rounded-pill flex-grow-1 fw-bold btn-amber-primary" id="btnRoleCustomer" onclick="switchAuthPortal('customer')">Customer</button>
                        <button type="button" class="btn btn-sm rounded-pill flex-grow-1 fw-bold text-muted" id="btnRolePro" onclick="switchAuthPortal('professional')">Technician</button>
                        <button type="button" class="btn btn-sm rounded-pill flex-grow-1 fw-bold text-muted" id="btnRoleAdmin" onclick="switchAuthPortal('admin')">Admin</button>
                    </div>

                    <!-- 1-Click Demo Fill Banner -->
                    <div class="p-2 mb-3 rounded-3 d-flex justify-content-between align-items-center" style="background-color: #fefce8; border: 1px solid #fef08a;">
                        <span class="small text-dark"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Testing Credentials</span>
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-0" style="font-size: 0.75rem;" onclick="fillRoleCredentials()">
                            1-Click Autofill
                        </button>
                    </div>

                    <!-- Login Form -->
                    <form id="universalLoginForm" onsubmit="handleAuthSubmit(event)">
                        <input type="hidden" id="login_role_target" name="target_role" value="customer">

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Registered Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: var(--fm-sand-border);"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="auth_email" name="email" class="form-control form-control-cream border-start-0" placeholder="user@domain.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Account Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: var(--fm-sand-border);"><i class="bi bi-key"></i></span>
                                <input type="password" id="auth_password" name="password" class="form-control form-control-cream border-start-0 border-end-0" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary border-start-0" style="border-color: var(--fm-sand-border);" onclick="togglePasswordVisibility()"><i class="bi bi-eye" id="pwdEyeIcon"></i></button>
                            </div>
                        </div>

                        <div id="authAlertFeedback" class="small mb-3 d-none"></div>

                        <button type="submit" class="btn btn-amber-primary w-100 rounded-pill py-2 fw-bold" id="authSubmitButton">
                            Authenticate & Enter <i class="bi bi-arrow-right ms-1"></i>
                        </button>

                        <div class="text-center mt-3 small" id="loginRegisterSwitch">
                            <span class="text-muted">Don't have a customer account?</span> 
                            <a href="javascript:void(0)" onclick="switchToRegisterModal()" class="fw-bold text-dark text-decoration-none">
                                Create an Account <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         12. DEDICATED CUSTOMER REGISTRATION MODAL
         ========================================== -->
    <div class="modal fade" id="customerRegisterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="background: var(--fm-cream-surface); border-radius: 1.5rem; border: 1.5px solid var(--fm-sand-border);">
                <div class="modal-header border-bottom pb-3" style="border-color: var(--fm-sand-border) !important;">
                    <div>
                        <h5 class="modal-title fw-bold text-dark font-heading m-0">Join FixMate as Customer</h5>
                        <span class="text-muted small">Create an account for 1-click home repairs & tracking</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="customerRegisterForm" onsubmit="handleCustomerRegister(event)">
                        <!-- Full Name -->
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: var(--fm-sand-border);"><i class="bi bi-person"></i></span>
                                <input type="text" id="reg_name" name="name" class="form-control form-control-cream border-start-0" placeholder="e.g. Rahul Verma" required>
                            </div>
                        </div>

                        <!-- Email & Mobile -->
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label small text-muted fw-bold">Email Address</label>
                                <input type="email" id="reg_email" name="email" class="form-control form-control-cream" placeholder="rahul@gmail.com" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label small text-muted fw-bold">10-Digit Mobile</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white small px-2" style="border-color: var(--fm-sand-border); font-size: 0.8rem;">+91</span>
                                    <input type="tel" id="reg_phone" name="phone" class="form-control form-control-cream" placeholder="9876543210" pattern="[0-9]{10}" maxlength="10" required>
                                </div>
                            </div>
                        </div>

                        <!-- City & Address -->
                        <div class="row g-2 mb-3">
                            <div class="col-5">
                                <label class="form-label small text-muted fw-bold">City</label>
                                <select id="reg_city" name="city" class="form-select form-control-cream">
                                    <option value="Bengaluru" selected>Bengaluru</option>
                                    <option value="Mumbai">Mumbai</option>
                                    <option value="Delhi NCR">Delhi NCR</option>
                                    <option value="Hyderabad">Hyderabad</option>
                                </select>
                            </div>
                            <div class="col-7">
                                <label class="form-label small text-muted fw-bold">Primary Address / Area</label>
                                <input type="text" id="reg_address" name="address" class="form-control form-control-cream" placeholder="Flat, Building, Street">
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">Create Security Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: var(--fm-sand-border);"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" id="reg_password" name="password" class="form-control form-control-cream border-start-0 border-end-0" placeholder="Minimum 6 characters" minlength="6" required>
                                <button type="button" class="btn btn-outline-secondary border-start-0" style="border-color: var(--fm-sand-border);" onclick="toggleRegPasswordVisibility()"><i class="bi bi-eye" id="regPwdEyeIcon"></i></button>
                            </div>
                            <span class="text-muted" style="font-size: 0.72rem;">Your data is protected by 256-bit SSL encryption.</span>
                        </div>

                        <div id="regAlertFeedback" class="small mb-3 d-none"></div>

                        <button type="submit" class="btn btn-amber-primary w-100 rounded-pill py-2 fw-bold" id="regSubmitButton">
                            Complete Registration & Enter <i class="bi bi-arrow-right ms-1"></i>
                        </button>

                        <div class="text-center mt-3 small">
                            <span class="text-muted">Already have an account?</span> 
                            <a href="javascript:void(0)" onclick="switchToLoginModal()" class="fw-bold text-dark text-decoration-none">
                                Sign In Instead
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         13. JAVASCRIPT LOGIC & GSAP ANIMATIONS
         ========================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // GSAP Scroll Animations
        document.addEventListener("DOMContentLoaded", () => {
            gsap.registerPlugin(ScrollTrigger);

            // Stagger hero text
            gsap.from(".gs-hero-stagger > *", {
                duration: 1,
                y: 35,
                opacity: 0,
                stagger: 0.14,
                ease: "power3.out"
            });

            // Reveal cards on scroll
            gsap.utils.toArray('.gs-reveal-category').forEach((card, index) => {
                gsap.from(card, {
                    scrollTrigger: {
                        trigger: card,
                        start: "top 88%",
                        toggleActions: "play none none none"
                    },
                    duration: 0.8,
                    y: 40,
                    opacity: 0,
                    delay: (index % 3) * 0.12,
                    ease: "power2.out"
                });
            });

            // Initial calculation run
            calculateLiveEstimate();
        });

        // Interactive Live Cost Estimator
        function calculateLiveEstimate() {
            const basePrice = parseFloat(document.getElementById('estimator_service').value) || 0;
            const qty = parseInt(document.getElementById('estimator_qty').value) || 1;
            const isUrgent = document.getElementById('estimator_urgent').checked;
            const coupon = (document.getElementById('estimator_coupon').value || '').trim().toUpperCase();

            let subtotal = basePrice * qty;
            let surge = isUrgent ? (subtotal * 0.25) : 0;
            let discount = 0;

            if (coupon === 'FIX20') {
                discount = Math.min(250, (subtotal * 0.20));
            } else if (coupon === 'WELCOME100') {
                discount = Math.min(subtotal, 100);
            }

            let taxable = Math.max(0, subtotal + surge - discount);
            let tax = Math.round(taxable * 0.18);
            let grandTotal = taxable + tax;

            document.getElementById('est_lbl_base').innerText = '₹' + subtotal.toFixed(2);
            document.getElementById('est_lbl_surge').innerText = surge > 0 ? '+ ₹' + surge.toFixed(2) : '₹0.00';
            document.getElementById('est_lbl_discount').innerText = discount > 0 ? '- ₹' + discount.toFixed(2) : '₹0.00';
            document.getElementById('est_lbl_tax').innerText = '+ ₹' + tax.toFixed(2);
            document.getElementById('est_total_display').innerText = '₹' + grandTotal.toFixed(2);
            document.getElementById('est_lbl_grand_total').innerText = '₹' + grandTotal.toFixed(2);

            const badge = document.getElementById('est_discount_badge');
            if (discount > 0) {
                badge.className = 'badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold mb-4';
                badge.innerHTML = `<i class="bi bi-tag-fill me-1"></i> Coupon '${coupon}' Applied (₹${discount.toFixed(2)} Saved)`;
            } else {
                badge.className = 'badge bg-secondary-subtle text-muted px-3 py-2 rounded-pill fw-bold mb-4';
                badge.innerHTML = 'No Promotional Subsidies Applied';
            }
        }

        // Authentication Modal Controller
        let activePortalRole = 'customer';
        const requestedWorkspaceRole = <?= json_encode($defaultWorkspaceRole ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

        function openUniversalAuthModal(role = 'customer') {
            const regModalEl = document.getElementById('customerRegisterModal');
            const regModalInstance = bootstrap.Modal.getInstance(regModalEl);
            if (regModalInstance) {
                regModalInstance.hide();
            }
            switchAuthPortal(role);
            new bootstrap.Modal(document.getElementById('universalAuthModal')).show();
        }

        function switchAuthPortal(role) {
            activePortalRole = role;
            document.getElementById('login_role_target').value = role;

            const btnCust = document.getElementById('btnRoleCustomer');
            const btnPro = document.getElementById('btnRolePro');
            const btnAdm = document.getElementById('btnRoleAdmin');
            const title = document.getElementById('authGatewayTitle');
            const sub = document.getElementById('authGatewaySubtitle');

            [btnCust, btnPro, btnAdm].forEach(b => {
                b.className = 'btn btn-sm rounded-pill flex-grow-1 fw-bold text-muted';
            });

            if (role === 'customer') {
                btnCust.className = 'btn btn-sm rounded-pill flex-grow-1 fw-bold btn-amber-primary';
                title.innerText = 'Customer Portal Access';
                sub.innerText = 'Book diagnostics, track dispatches, and manage invoices';
            } else if (role === 'professional') {
                btnPro.className = 'btn btn-sm rounded-pill flex-grow-1 fw-bold btn-amber-primary';
                title.innerText = 'Technician Workbench';
                sub.innerText = 'Inspect assigned work orders, proof uploads, and payouts';
            } else {
                btnAdm.className = 'btn btn-sm rounded-pill flex-grow-1 fw-bold btn-amber-primary';
                title.innerText = 'Admin Control Center';
                sub.innerText = 'Manage dispatch roster, KYC approvals, and audit logs';
            }

            document.getElementById('auth_email').value = '';
            document.getElementById('auth_password').value = '';
        }

        function fillRoleCredentials() {
            const emailInput = document.getElementById('auth_email');
            const pwdInput = document.getElementById('auth_password');

            pwdInput.value = 'Password@123';
            if (activePortalRole === 'admin') {
                emailInput.value = 'admin@fixmate.in';
            } else if (activePortalRole === 'professional') {
                emailInput.value = 'amit.electric@fixmate.in';
            } else {
                emailInput.value = 'priya@gmail.com';
            }
        }

        function togglePasswordVisibility() {
            const pwd = document.getElementById('auth_password');
            const icon = document.getElementById('pwdEyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function handleAuthSubmit(event) {
            event.preventDefault();
            const btn = document.getElementById('authSubmitButton');
            const feedback = document.getElementById('authAlertFeedback');
            const email = document.getElementById('auth_email').value;
            const password = document.getElementById('auth_password').value;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Validating...';
            feedback.className = 'd-none';

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('target_role', activePortalRole);

            fetch(`${window.fixmateBasePath}/api/auth/login`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Authenticate & Enter <i class="bi bi-arrow-right ms-1"></i>';

                if (data.success) {
                    feedback.className = 'alert alert-success p-2 small';
                    feedback.innerText = data.message + ' Redirecting to portal...';
                    setTimeout(() => {
                        window.location.assign(data.redirect);
                    }, 500);
                } else {
                    feedback.className = 'alert alert-danger p-2 small';
                    feedback.innerText = data.message;
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = 'Authenticate & Enter <i class="bi bi-arrow-right ms-1"></i>';
                feedback.className = 'alert alert-danger p-2 small';
                feedback.innerText = 'Network error occurred while connecting to authentication endpoint.';
            });
        }

        if (['customer', 'professional', 'admin'].includes(requestedWorkspaceRole)) {
            window.addEventListener('load', function () {
                openUniversalAuthModal(requestedWorkspaceRole);
            }, { once: true });
        }

        // Dedicated Customer Registration Controller
        function openCustomerRegisterModal() {
            const authModalEl = document.getElementById('universalAuthModal');
            const authModalInstance = bootstrap.Modal.getInstance(authModalEl);
            if (authModalInstance) {
                authModalInstance.hide();
            }
            new bootstrap.Modal(document.getElementById('customerRegisterModal')).show();
        }

        function switchToRegisterModal() {
            openCustomerRegisterModal();
        }

        function switchToLoginModal() {
            openUniversalAuthModal('customer');
        }

        function toggleRegPasswordVisibility() {
            const pwd = document.getElementById('reg_password');
            const icon = document.getElementById('regPwdEyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pwd.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }

        function handleCustomerRegister(event) {
            event.preventDefault();
            const btn = document.getElementById('regSubmitButton');
            const feedback = document.getElementById('regAlertFeedback');

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Account...';
            feedback.className = 'd-none';

            const formData = new FormData();
            formData.append('name', document.getElementById('reg_name').value);
            formData.append('email', document.getElementById('reg_email').value);
            formData.append('phone', document.getElementById('reg_phone').value);
            formData.append('city', document.getElementById('reg_city').value);
            formData.append('address', document.getElementById('reg_address').value);
            formData.append('password', document.getElementById('reg_password').value);

            fetch(`${window.fixmateBasePath}/api/auth/register`, {
                method: 'POST',
                body: formData
            })
            .then(async (res) => {
                // Surface a real server/transport error instead of a misleading "network" message.
                const raw = await res.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (parseError) {
                    throw new Error(res.ok
                        ? 'The server returned an unexpected response. Please try again.'
                        : `Request failed (HTTP ${res.status}). Please try again.`);
                }
                return data;
            })
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Complete Registration & Enter <i class="bi bi-arrow-right ms-1"></i>';

                if (data.success) {
                    feedback.className = 'alert alert-success p-2 small';
                    feedback.innerText = data.message + ' Taking you to your dashboard...';
                    setTimeout(() => {
                        window.location.assign(data.redirect);
                    }, 600);
                } else {
                    feedback.className = 'alert alert-danger p-2 small';
                    feedback.innerText = data.message;
                }
            })
            .catch((error) => {
                btn.disabled = false;
                btn.innerHTML = 'Complete Registration & Enter <i class="bi bi-arrow-right ms-1"></i>';
                feedback.className = 'alert alert-danger p-2 small';
                feedback.innerText = error && error.message
                    ? error.message
                    : 'Network error occurred while creating your account. Please try again.';
            });
        }
    </script>
</body>
</html>