<?php
declare(strict_types=1);

// Keep each role in its own session cookie so multiple workspace tabs can be
// signed in at the same time without overwriting one another's identity.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$workspace = strtolower(trim((string)($_GET['workspace'] ?? $_POST['target_role'] ?? '')));
if (!in_array($workspace, ['customer', 'professional', 'admin'], true)) {
    if (preg_match('#/customer(?:/|$)#', $requestPath)) {
        $workspace = 'customer';
    } elseif (preg_match('#/pro(?:/|$)#', $requestPath)) {
        $workspace = 'professional';
    } elseif (preg_match('#/admin(?:/|$)#', $requestPath)) {
        $workspace = 'admin';
    } elseif (str_ends_with($requestPath, '/api/auth/register')) {
        $workspace = 'customer';
    } else {
        $workspace = 'public';
    }
}
$sessionSuffix = match ($workspace) {
    'customer' => 'CUSTOMER',
    'professional' => 'TECHNICIAN',
    'admin' => 'ADMIN',
    default => 'PUBLIC',
};
session_name('FIXMATE_' . $sessionSuffix);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

// -------------------------------------------------------------
// Base Path Determination
// -------------------------------------------------------------
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$basePath = rtrim($scriptDirectory, '/');
if (str_ends_with($basePath, '/public')) {
    $basePath = substr($basePath, 0, -7);
}
define('APP_BASE_PATH', $basePath);

// -------------------------------------------------------------
// Environment Variable Fallbacks (Default Local Database)
// -------------------------------------------------------------
$_ENV['DB_HOST']     = $_ENV['DB_HOST']     ?? '127.0.0.1';
$_ENV['DB_PORT']     = $_ENV['DB_PORT']     ?? '3306';
$_ENV['DB_DATABASE'] = $_ENV['DB_DATABASE'] ?? 'fixmate_db';
$_ENV['DB_USERNAME'] = $_ENV['DB_USERNAME'] ?? 'root';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? '';

// -------------------------------------------------------------
// Universal Autoloader (Supports Core & App namespaces)
// -------------------------------------------------------------
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\Core\\'        => dirname(__DIR__) . '/core/',
        'Core\\'            => dirname(__DIR__) . '/core/',
        'App\\Controllers\\' => dirname(__DIR__) . '/app/Controllers/',
        'App\\Models\\'      => dirname(__DIR__) . '/app/Models/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';
            if (is_file($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// Resolve App class regardless of namespace used
$appClass = class_exists(\App\Core\App::class) ? \App\Core\App::class : \Core\App::class;
$app = new $appClass();

// Default Root Redirect
// Universal Landing Page & Unified Auth Gateway
$app->get('/', [\App\Controllers\LandingController::class, 'index']);
$app->get('/landing', [\App\Controllers\LandingController::class, 'index']);
$app->get('/workspaces', [\App\Controllers\LandingController::class, 'workspaces']);
$app->post('/api/auth/login', [\App\Controllers\LandingController::class, 'processLogin']);
$app->post('/api/auth/register', [\App\Controllers\LandingController::class, 'registerCustomer']);
$app->get('/logout', static function (): void {
    \App\Core\Auth::logout();
    header('Location: ' . APP_BASE_PATH . '/landing?workspace=' . rawurlencode((string)($_GET['workspace'] ?? '')), true, 303);
    exit;
});
$app->post('/webhooks/razorpay', [\App\Controllers\PaymentWebhookController::class, 'razorpay']);

// ========================================================
// 1. ADMIN SUITE ROUTES (ADM-01 TO ADM-10)
// ========================================================

// ADM-01: Admin Hub & Analytics
$app->get('/admin', [\App\Controllers\AdminController::class, 'dashboard']);
$app->get('/admin/dashboard', [\App\Controllers\AdminController::class, 'dashboard']);
$app->get('/admin/api/revenue-analytics', [\App\Controllers\AdminController::class, 'revenueChartApi']);

// ADM-02: Staff & User Management (RBAC & Customer 360)
$app->get('/admin/staff-users', [\App\Controllers\AdminController::class, 'staffUsers']);
$app->post('/admin/staff-users/save-staff', [\App\Controllers\AdminController::class, 'saveStaff']);
$app->post('/admin/staff-users/toggle-status', [\App\Controllers\AdminController::class, 'toggleUserStatus']);
$app->get('/admin/staff-users/customer-360', [\App\Controllers\AdminController::class, 'customer360Api']);

// ADM-03: Professional & KYC Verification Workbench
$app->get('/admin/kyc-verifications', [\App\Controllers\AdminController::class, 'kycWorkbench']);
$app->post('/admin/kyc-verifications/create-technician', [\App\Controllers\AdminController::class, 'createProfessional']);
$app->get('/admin/kyc-verifications/pro-dossier', [\App\Controllers\AdminController::class, 'proDossierApi']);
$app->post('/admin/kyc-verifications/process-kyc', [\App\Controllers\AdminController::class, 'processKycAction']);
$app->get('/admin/kyc-verifications/document', [\App\Controllers\AdminController::class, 'viewKycDocument']);
$app->get('/admin/kyc-verifications/chat', [\App\Controllers\AdminController::class, 'professionalChatMessages']);
$app->post('/admin/kyc-verifications/chat/send', [\App\Controllers\AdminController::class, 'sendProfessionalChatMessage']);
$app->post('/admin/kyc-verifications/update-financials', [\App\Controllers\AdminController::class, 'updateProFinancials']);

// ADM-04: Service Catalog & Categories
$app->get('/admin/catalog', [\App\Controllers\AdminController::class, 'catalogManager']);
$app->post('/admin/catalog/save-category', [\App\Controllers\AdminController::class, 'saveCategory']);
$app->post('/admin/catalog/save-service', [\App\Controllers\AdminController::class, 'saveService']);
$app->post('/admin/catalog/toggle-service-status', [\App\Controllers\AdminController::class, 'toggleServiceStatus']);
$app->post('/admin/catalog/toggle-category-status', [\App\Controllers\AdminController::class, 'toggleCategoryStatus']);

// ADM-05: Service Areas & Pro Mapping
$app->get('/admin/zones', [\App\Controllers\AdminController::class, 'serviceZones']);
$app->post('/admin/zones/save-zone', [\App\Controllers\AdminController::class, 'saveServiceZone']);
$app->get('/admin/zones/pro-mappings', [\App\Controllers\AdminController::class, 'proMappingApi']);
$app->post('/admin/zones/save-mapping', [\App\Controllers\AdminController::class, 'saveProMapping']);
$app->post('/admin/zones/save-rate-override', [\App\Controllers\AdminController::class, 'saveProRateOverride']);

// ADM-06: Booking Dispatch & Assignment Board
$app->get('/admin/dispatch', [\App\Controllers\AdminController::class, 'dispatchBoard']);
$app->get('/admin/dispatch/booking-details', [\App\Controllers\AdminController::class, 'bookingDetailsApi']);
$app->get('/admin/dispatch/completion-status', [\App\Controllers\AdminController::class, 'bookingCompletionStatus']);
$app->post('/admin/dispatch/manual-assign', [\App\Controllers\AdminController::class, 'manualAssignBooking']);
$app->post('/admin/dispatch/auto-assign', [\App\Controllers\AdminController::class, 'autoAssignBooking']);
$app->post('/admin/dispatch/update-status', [\App\Controllers\AdminController::class, 'updateBookingStatus']);
$app->get('/admin/dispatch/work-proof', [\App\Controllers\AdminController::class, 'viewWorkProof']);
$app->post('/admin/dispatch/review-work-proof', [\App\Controllers\AdminController::class, 'reviewWorkProof']);

// ADM-07: Pricing Rules, Surge & Coupon Promotions
$app->get('/admin/pricing-rules', [\App\Controllers\AdminController::class, 'pricingAndCoupons']);
$app->post('/admin/pricing-rules/save-coupon', [\App\Controllers\AdminController::class, 'saveCoupon']);
$app->post('/admin/pricing-rules/toggle-coupon-status', [\App\Controllers\AdminController::class, 'toggleCouponStatus']);
$app->post('/admin/pricing-rules/update-global-rules', [\App\Controllers\AdminController::class, 'updateGlobalPricingRules']);

// ADM-08: Financials, Invoices & Payouts
$app->get('/admin/financial', [\App\Controllers\AdminController::class, 'financialsLedger']);
$app->get('/admin/financials', [\App\Controllers\AdminController::class, 'financialsLedger']);
$app->get('/admin/financials/invoice', [\App\Controllers\AdminController::class, 'generateInvoiceView']);
$app->post('/admin/financials/process-payout', [\App\Controllers\AdminController::class, 'processProPayout']);

// ADM-09: Reviews, Disputes & Helpdesk
$app->get('/admin/helpdesk', [\App\Controllers\AdminController::class, 'helpdeskAndDisputes']);
$app->get('/admin/helpdesk/workflow-status', [\App\Controllers\AdminController::class, 'helpdeskWorkflowApi']);
$app->post('/admin/helpdesk/complete-booking', [\App\Controllers\AdminController::class, 'completeHelpdeskBooking']);
$app->post('/admin/helpdesk/cancel-booking', [\App\Controllers\AdminController::class, 'cancelHelpdeskBooking']);
$app->get('/admin/helpdesk/dispute-details', [\App\Controllers\AdminController::class, 'disputeDetailsApi']);
$app->post('/admin/helpdesk/resolve-dispute', [\App\Controllers\AdminController::class, 'resolveDisputeAction']);
$app->post('/admin/helpdesk/review-work-proof', [\App\Controllers\AdminController::class, 'reviewWorkProof']);
$app->post('/admin/helpdesk/moderate-review', [\App\Controllers\AdminController::class, 'moderateReviewAction']);

// ADM-10: System Settings & Audit Logs
$app->get('/admin/settings', [\App\Controllers\AdminController::class, 'systemSettingsAndLogs']);
$app->post('/admin/settings/save-config', [\App\Controllers\AdminController::class, 'saveSystemConfig']);
$app->post('/admin/settings/save-announcement', [\App\Controllers\AdminController::class, 'saveAnnouncement']);


// ========================================================
// 2. CUSTOMER SUITE ROUTES (CUS-01 TO CUS-10)
// ========================================================

// CUS-01: Customer Hub & Service Discovery
$app->get('/customer', [\App\Controllers\CustomerController::class, 'home']);

// CUS-02: Auth, Profile & Address Book
$app->get('/customer/profile', [\App\Controllers\CustomerController::class, 'profileAndAddresses']);
$app->post('/customer/profile/save-profile', [\App\Controllers\CustomerController::class, 'saveProfile']);
$app->post('/customer/profile/save-address', [\App\Controllers\CustomerController::class, 'saveAddress']);
$app->post('/customer/profile/delete-address', [\App\Controllers\CustomerController::class, 'deleteAddress']);

// CUS-03: Professional Search & Profiles
$app->get('/customer/pros', [\App\Controllers\CustomerController::class, 'searchPros']);

// CUS-04 & CUS-08: Cart, Checkout, Payments & Coupons
$app->get('/customer/cart', [\App\Controllers\CustomerController::class, 'cartView']);
$app->post('/customer/checkout/apply-coupon', [\App\Controllers\CustomerController::class, 'applyCouponApi']);
$app->post('/customer/checkout/place-order', [\App\Controllers\CustomerController::class, 'placeOrder']);
$app->post('/customer/checkout/payment-order', [\App\Controllers\CustomerController::class, 'createOnlinePaymentOrder']);
$app->post('/customer/checkout/payment-verify', [\App\Controllers\CustomerController::class, 'verifyOnlinePayment']);

// CUS-05: Custom Quotations
$app->get('/customer/custom-quote', [\App\Controllers\CustomerController::class, 'quotationView']);
$app->post('/customer/custom-quote/submit', [\App\Controllers\CustomerController::class, 'submitQuotationRequest']);

// CUS-06: Booking Tracker & Rescheduling
$app->get('/customer/my-bookings', [\App\Controllers\CustomerController::class, 'bookingsTracker']);
$app->get('/customer/bookings/invoice', [\App\Controllers\CustomerController::class, 'bookingInvoice']);
$app->get('/customer/bookings/completion-code', [\App\Controllers\CustomerController::class, 'completionOtpApi']);
$app->post('/customer/bookings/reschedule', [\App\Controllers\CustomerController::class, 'rescheduleBooking']);
$app->post('/customer/bookings/cancel', [\App\Controllers\CustomerController::class, 'cancelBooking']);

// CUS-07: In-App Chat & Messaging
$app->get('/customer/chat', [\App\Controllers\CustomerController::class, 'chatView']);
$app->get('/customer/chat/messages', [\App\Controllers\CustomerController::class, 'chatMessagesApi']);
$app->post('/customer/chat/send', [\App\Controllers\CustomerController::class, 'sendChatMessage']);

// CUS-09: Completion Sign-off & Reviews
$app->post('/customer/bookings/submit-review', [\App\Controllers\CustomerController::class, 'submitReview']);

// CUS-10: Customer Support & Dispute Grievances
$app->get('/customer/support', [\App\Controllers\CustomerController::class, 'supportAndDisputes']);
$app->post('/customer/support/raise-dispute', [\App\Controllers\CustomerController::class, 'raiseDispute']);


// ========================================================
// 3. PROFESSIONAL SUITE ROUTES (PRO-01 TO PRO-10)
// ========================================================

// PRO-01: Technician Workbench & Daily KPIs
$app->get('/pro', [\App\Controllers\ProfessionalController::class, 'dashboard']);
$app->get('/pro/dashboard', [\App\Controllers\ProfessionalController::class, 'dashboard']);
$app->post('/pro/admin-chat/send', [\App\Controllers\ProfessionalController::class, 'sendAdminMessage']);
$app->get('/pro/admin-chat/messages', [\App\Controllers\ProfessionalController::class, 'adminChatMessages']);
$app->get('/pro/dashboard/updates', [\App\Controllers\ProfessionalController::class, 'dashboardUpdates']);

// PRO-02: Onboarding & KYC Submission
$app->get('/pro/kyc', [\App\Controllers\ProfessionalController::class, 'kycView']);
$app->post('/pro/kyc/upload', [\App\Controllers\ProfessionalController::class, 'uploadKyc']);

// PRO-03: Services, Pricing & Service Areas
$app->get('/pro/rates-zones', [\App\Controllers\ProfessionalController::class, 'ratesAndZones']);
$app->post('/pro/rates-zones/save-rates', [\App\Controllers\ProfessionalController::class, 'saveRates']);

// PRO-04: Schedule & Leave Management
$app->get('/pro/schedule', [\App\Controllers\ProfessionalController::class, 'scheduleView']);
$app->post('/pro/schedule/save', [\App\Controllers\ProfessionalController::class, 'saveSchedule']);
$app->post('/pro/schedule/request-leave', [\App\Controllers\ProfessionalController::class, 'requestLeave']);

// PRO-05: Job Requests & Quotation Builder
$app->get('/pro/leads', [\App\Controllers\ProfessionalController::class, 'jobLeads']);
$app->post('/pro/leads/submit-bid', [\App\Controllers\ProfessionalController::class, 'submitBid']);

// PRO-06: Job Fulfillment & Status Dispatch
$app->get('/pro/fulfillment', [\App\Controllers\ProfessionalController::class, 'fulfillmentView']);
$app->get('/pro/fulfillment/updates', [\App\Controllers\ProfessionalController::class, 'fulfillmentUpdates']);
$app->post('/pro/fulfillment/update-status', [\App\Controllers\ProfessionalController::class, 'updateJobStatus']);
$app->post('/pro/fulfillment/request-completion-otp', [\App\Controllers\ProfessionalController::class, 'requestCompletionOtp']);
$app->post('/pro/fulfillment/verify-completion-otp', [\App\Controllers\ProfessionalController::class, 'verifyCompletionOtp']);

// PRO-07: Customer Navigation & Direct Chat
$app->get('/pro/nav-chat', [\App\Controllers\ProfessionalController::class, 'navChatView']);
$app->get('/pro/nav-chat/messages', [\App\Controllers\ProfessionalController::class, 'chatMessagesApi']);
$app->get('/pro/nav-chat/status', [\App\Controllers\ProfessionalController::class, 'chatBookingStatusApi']);
$app->post('/pro/nav-chat/send', [\App\Controllers\ProfessionalController::class, 'sendMessage']);

// PRO-08: Extra Materials & Proof of Work
$app->get('/pro/proof-of-work', [\App\Controllers\ProfessionalController::class, 'proofOfWorkView']);
$app->post('/pro/proof-of-work/upload', [\App\Controllers\ProfessionalController::class, 'uploadProof']);
$app->get('/pro/proof-of-work/file', [\App\Controllers\ProfessionalController::class, 'viewWorkProofFile']);

// PRO-09: Earnings Ledger & Payout Requests
$app->get('/pro/earnings', [\App\Controllers\ProfessionalController::class, 'earningsView']);
$app->post('/pro/earnings/request-payout', [\App\Controllers\ProfessionalController::class, 'requestPayout']);

// PRO-10: Reviews, Dispute Response & Helpdesk
$app->get('/pro/feedback', [\App\Controllers\ProfessionalController::class, 'feedbackView']);


// -------------------------------------------------------------
// Dispatch Request Lifecycle (Executed after ALL routes are set)
// -------------------------------------------------------------
$app->run();