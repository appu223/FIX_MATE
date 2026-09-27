<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\AdminModel;

final class AdminController extends Controller
{
    private AdminModel $adminModel;

    public function __construct()
    {
		if (!Auth::check() || !in_array(Auth::role(), ['admin', 'staff'], true)) {
			$this->redirect('/landing');
		}

        $this->adminModel = new AdminModel();
    }

    public function dashboard(): void
    {
        $overview = $this->adminModel->getDashboardOverview();
        $this->view('admin/dashboard', [
            'pageTitle' => 'Administrative Overview',
            'kpis' => $overview['kpis'],
            'categoryBreakdown' => $overview['categoryBreakdown'],
            'recentBookings' => $overview['recentBookings'],
        ], 'admin');
    }

    public function revenueChartApi(): void
    {
        $this->json($this->adminModel->getRevenueChartData());
    }

// ========================================================
    // ADM-04 ACTIONS: SERVICE CATALOG & CATEGORIES
    // ========================================================

    public function catalogManager(): void
    {
        $catFilter = (int)$this->get('category_id', 0);
        $search    = (string)$this->get('search', '');

        $categories = $this->adminModel->getAllCategoriesWithCounts();
        $services   = $this->adminModel->getServicesList($catFilter > 0 ? $catFilter : null, $search !== '' ? $search : null);

        $this->view('admin/catalog', [
            'pageTitle'    => 'Service Catalog & Category Engine',
            'categories'   => $categories,
            'services'     => $services,
            'catFilter'    => $catFilter,
            'search'       => $search,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function saveCategory(): void
    {
        $id          = (int)$this->post('id', 0);
        $name        = (string)$this->post('name', '');
        $icon        = (string)$this->post('icon', 'bi-tools');
        $description = (string)$this->post('description', '');
        $sortOrder   = (int)$this->post('sort_order', 0);
        $status      = (string)$this->post('status', 'active');

        if (empty(trim($name))) {
            $_SESSION['flash_err'] = 'Category name cannot be blank.';
            $this->redirect('/admin/catalog');
        }

        try {
            $this->adminModel->saveCategoryRecord([
                'id'          => $id > 0 ? $id : null,
                'name'        => trim($name),
                'icon'        => trim($icon),
                'description' => trim($description),
                'sort_order'  => $sortOrder,
                'status'      => $status,
            ]);
            $_SESSION['flash_msg'] = 'Service category updated successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Failed to save category: ' . $e->getMessage();
        }

        $this->redirect('/admin/catalog');
    }

    public function saveService(): void
    {
        $id          = (int)$this->post('id', 0);
        $categoryId  = (int)$this->post('category_id', 0);
        $name        = (string)$this->post('name', '');
        $description = (string)$this->post('description', '');
        $basePrice   = (float)$this->post('base_price', 0.00);
        $duration    = (int)$this->post('duration_minutes', 60);
        $isPopular   = isset($_POST['is_popular']) ? 1 : 0;
        $status      = (string)$this->post('status', 'active');

        if ($categoryId <= 0 || empty(trim($name)) || $basePrice < 0) {
            $_SESSION['flash_err'] = 'Category, valid Service Name, and a valid Base Price (₹) are required.';
            $this->redirect('/admin/catalog');
        }

        try {
            $this->adminModel->saveServiceRecord([
                'id'               => $id > 0 ? $id : null,
                'category_id'      => $categoryId,
                'name'             => trim($name),
                'description'      => trim($description),
                'base_price'       => $basePrice,
                'duration_minutes' => $duration,
                'is_popular'       => $isPopular,
                'status'           => $status,
            ]);
            $_SESSION['flash_msg'] = 'Service offering saved successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Failed to save service: ' . $e->getMessage();
        }

        $this->redirect('/admin/catalog');
    }

    public function toggleCategoryStatus(): void
    {
        $id     = (int)$this->post('id', 0);
        $status = (string)$this->post('status', 'active');

        if ($id <= 0 || !in_array($status, ['active', 'inactive'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $this->adminModel->setCategoryStatus($id, $status);
        $this->json(['success' => true, 'message' => "Category status set to {$status}"]);
    }

    public function toggleServiceStatus(): void
    {
        $id     = (int)$this->post('id', 0);
        $status = (string)$this->post('status', 'active');

        if ($id <= 0 || !in_array($status, ['active', 'inactive'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $this->adminModel->setServiceStatus($id, $status);
        $this->json(['success' => true, 'message' => "Service status set to {$status}"]);
    }
    // ========================================================
    // ADM-03 ACTIONS: PRO & KYC VERIFICATION WORKBENCH
    // ========================================================

    public function kycWorkbench(): void
    {
        $kycFilter = $this->get('kyc_status', '');
        $search    = $this->get('search', '');

        $pros = $this->adminModel->getProfessionalsList(
            $kycFilter !== '' ? $kycFilter : null, 
            $search !== '' ? $search : null
        );

        $this->view('admin/kyc_workbench', [
            'pageTitle'    => 'Pro Dossiers & KYC Workbench',
            'pros'         => $pros,
            'kycFilter'    => $kycFilter,
            'search'       => $search,
            'canVerify'    => Auth::role() === 'admin',
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function createProfessional(): void
    {
        $name = trim((string)$this->post('name', ''));
        $email = trim((string)$this->post('email', ''));
        $phone = trim((string)$this->post('phone', ''));
        $password = (string)$this->post('password', '');
        $experience = (int)$this->post('experience_years', 1);

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '' || strlen($password) < 8) {
            $_SESSION['flash_err'] = 'Enter a name, valid email, phone number, and a password with at least 8 characters.';
            $this->redirect('/admin/kyc-verifications');
        }

        try {
            $this->adminModel->createProfessionalAccount([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password,
                'experience_years' => max(0, $experience),
                'id_proof_type' => (string)$this->post('id_proof_type', 'Aadhaar Card'),
            ]);
            $_SESSION['flash_msg'] = 'Technician account created. It is now in the pending KYC queue.';
        } catch (\Throwable $e) {
            $_SESSION['flash_err'] = 'Unable to create technician: ' . $e->getMessage();
        }

        $this->redirect('/admin/kyc-verifications?kyc_status=pending');
    }

    public function proDossierApi(): void
    {
        $proId = (int)$this->get('pro_id', 0);
        if ($proId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Professional ID required.'], 400);
        }

        $data = $this->adminModel->getProDossierDetails($proId);
        if (!$data) {
            $this->json(['success' => false, 'message' => 'Technician dossier not found.'], 404);
        }

        $this->json(['success' => true, 'data' => $data]);
    }

    public function processKycAction(): void
    {
        if (Auth::role() !== 'admin') {
            $this->json(['success' => false, 'message' => 'Only a Fixmate administrator can verify KYC documents.'], 403);
        }

        $proId  = (int)$this->post('pro_id', 0);
        $status = (string)$this->post('status', '');
        $reason = (string)$this->post('reason', '');

        if ($proId <= 0 || !in_array($status, ['verified', 'rejected', 'pending'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid parameters supplied.'], 400);
        }

        if ($status === 'rejected' && empty(trim($reason))) {
            $this->json(['success' => false, 'message' => 'A rejection reason is strictly required.'], 422);
        }

        $success = $this->adminModel->updateKycStatus($proId, $status, $reason);

        if ($success) {
            $this->json(['success' => true, 'message' => "Technician KYC has been marked as {$status}."]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to update KYC status.'], 500);
        }
    }

    public function viewKycDocument(): void
    {
        if (Auth::role() !== 'admin') {
            http_response_code(403);
            exit('Only an administrator can view KYC documents.');
        }
        $proId = (int)$this->get('pro_id', 0);
        $kind = (string)$this->get('kind', '');
        $path = $this->adminModel->getKycDocumentPath($proId, $kind);
        $this->streamPrivateUpload($path, 'kyc');
    }

    public function viewWorkProof(): void
    {
        if (!in_array(Auth::role(), ['admin', 'staff'], true)) {
            http_response_code(403);
            exit('You are not allowed to view this work proof.');
        }
        $proofId = (int)$this->get('proof_id', 0);
        $path = $this->adminModel->getWorkProofPath($proofId);
        $this->streamPrivateUpload($path, 'proof');
    }

    public function reviewWorkProof(): void
    {
        if (Auth::role() !== 'admin') {
            $this->json(['success' => false, 'message' => 'Only an administrator can approve work and earnings.'], 403);
        }
        $proofId = (int)$this->post('proof_id', 0);
        $decision = (string)$this->post('decision', '');
        $confirmCodPayment = (string)$this->post('confirm_cod_payment', '0') === '1';
        $proModel = new \App\Models\ProfessionalModel();
        $updated = $proModel->reviewWorkProof($proofId, Auth::id(), $decision, $confirmCodPayment);
        if (!$updated) {
            $this->json(['success' => false, 'message' => 'Review failed. The proof may already be reviewed or the booking may be closed.'], 409);
        }
        $this->json(['success' => true, 'message' => $decision === 'approved'
            ? 'Work evidence approved. Job completion still requires the customer code and payment confirmation.'
            : 'Work proof rejected.']);
    }

    private function streamPrivateUpload(?string $storedPath, string $type): never
    {
        if (!$storedPath) {
            http_response_code(404);
            exit('The uploaded file is not available. Ask the technician to upload it again.');
        }

        $fileName = basename(str_replace('\\', '/', $storedPath));
        if ($fileName === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $fileName)) {
            http_response_code(404);
            exit('The uploaded file is not available.');
        }
        $root = dirname(__DIR__, 2);
        if (str_starts_with($storedPath, 'kyc/')) {
            $file = $root . '/storage/kyc/' . $fileName;
        } elseif (str_starts_with($storedPath, 'proofs/')) {
            $file = $root . '/storage/proofs/' . $fileName;
        } elseif ($type === 'kyc' && str_starts_with($storedPath, 'uploads/kyc/')) {
            $file = $root . '/public/uploads/kyc/' . $fileName;
        } elseif ($type === 'proof' && str_starts_with($storedPath, 'uploads/proofs/')) {
            $file = $root . '/public/uploads/proofs/' . $fileName;
        } else {
            http_response_code(404);
            exit('The uploaded file is not available.');
        }
        if (!is_file($file) || !is_readable($file)) {
            http_response_code(404);
            exit('The uploaded file is missing. Ask the technician to upload it again.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($file));
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($file);
        exit;
    }

    public function professionalChatMessages(): void
    {
        $proId = (int)$this->get('pro_id', 0);
        if ($proId <= 0) {
            $this->json(['success' => false, 'message' => 'Select a valid technician.'], 400);
        }

        $chat = new \App\Models\AdminProfessionalChatModel();
        $this->json(['success' => true, 'messages' => $chat->getMessages($proId, Auth::id())]);
    }

    public function sendProfessionalChatMessage(): void
    {
        $proId = (int)$this->post('pro_id', 0);
        $message = trim((string)$this->post('message', ''));
        $chat = new \App\Models\AdminProfessionalChatModel();
        if (!$chat->sendMessage($proId, Auth::id(), $message)) {
            $this->json(['success' => false, 'message' => 'Message could not be sent. Check the technician and message text.'], 422);
        }

        $this->json(['success' => true, 'message' => 'Message sent to technician.']);
    }

    public function updateProFinancials(): void
    {
        $proId          = (int)$this->post('pro_id', 0);
        $commissionRate = (float)$this->post('commission_rate', 15.00);
        $bankName       = (string)$this->post('bank_name', '');
        $accountNo      = (string)$this->post('bank_account_no', '');
        $ifsc           = (string)$this->post('bank_ifsc', '');

        if ($proId <= 0 || $commissionRate < 0 || $commissionRate > 100) {
            $_SESSION['flash_err'] = 'Invalid Commission Rate. Must be between 0% and 100%.';
            $this->redirect('/admin/kyc-verifications');
        }

        try {
            $this->adminModel->updateProFinancialTerms($proId, $commissionRate, $bankName, $accountNo, $ifsc);
            $_SESSION['flash_msg'] = 'Professional commission override and banking credentials saved.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Database update failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/kyc-verifications');
    }
    // ========================================================
    // ADM-02 ACTIONS: STAFF & USER MANAGEMENT
    // ========================================================

    public function staffUsers(): void
    {
        $search = $this->get('search', '');
        $status = $this->get('status', '');

        $staffMembers = $this->adminModel->getStaffMembers();
        $customers    = $this->adminModel->getCustomersList($search !== '' ? $search : null, $status !== '' ? $status : null);
        $professionals = $this->adminModel->getProfessionalsList(null, $search !== '' ? $search : null);

        $this->view('admin/staff_users', [
            'pageTitle'    => 'Staff & Customer Directory',
            'staffMembers' => $staffMembers,
            'professionals' => $professionals,
            'customers'    => $customers,
            'search'       => $search,
            'statusFilter' => $status,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function saveStaff(): void
    {
        $id       = (int)$this->post('id', 0);
        $name     = (string)$this->post('name', '');
        $email    = (string)$this->post('email', '');
        $phone    = (string)$this->post('phone', '');
        $role     = (string)$this->post('role', 'staff');
        $status   = (string)$this->post('status', 'active');
        $password = (string)$this->post('password', '');

        if (!in_array($role, ['staff', 'admin', 'professional'], true)) {
            $_SESSION['flash_err'] = 'Select a valid account role.';
            $this->redirect('/admin/staff-users');
        }

        if (empty($name) || empty($email) || empty($phone)) {
            $_SESSION['flash_err'] = 'Name, Email, and Phone number are strictly required.';
            $this->redirect('/admin/staff-users');
        }

        if ($role === 'professional') {
            if ($id > 0) {
                $_SESSION['flash_err'] = 'Technician accounts are managed in the Pro & KYC workbench; create a new technician account there.';
                $this->redirect('/admin/staff-users');
            }
            if (strlen($password) < 8) {
                $_SESSION['flash_err'] = 'A technician password must be at least 8 characters.';
                $this->redirect('/admin/staff-users');
            }

            try {
                $this->adminModel->createProfessionalAccount([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => $password,
                    'experience_years' => 1,
                    'id_proof_type' => 'Aadhaar Card',
                ]);
                $_SESSION['flash_msg'] = 'Technician account created and added to the pending KYC queue.';
            } catch (\Throwable $e) {
                $_SESSION['flash_err'] = 'Unable to create technician: ' . $e->getMessage();
            }

            $this->redirect('/admin/kyc-verifications?kyc_status=pending');
        }

        try {
            $this->adminModel->createOrUpdateStaff([
                'id'       => $id > 0 ? $id : null,
                'name'     => $name,
                'email'    => $email,
                'phone'    => $phone,
                'role'     => $role,
                'status'   => $status,
                'password' => $password !== '' ? $password : null
            ]);
            $_SESSION['flash_msg'] = 'Staff account record successfully saved.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Operation failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/staff-users');
    }

    public function toggleUserStatus(): void
    {
        $userId    = (int)$this->post('user_id', 0);
        $newStatus = (string)$this->post('status', 'active');

        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid user ID'], 400);
        }

        $result = $this->adminModel->updateUserStatus($userId, $newStatus);

        if ($result) {
            $this->json(['success' => true, 'message' => "Account status changed to {$newStatus}"]);
        } else {
            $this->json(['success' => false, 'message' => 'Unable to change status or action restricted'], 400);
        }
    }

    public function customer360Api(): void
    {
        $customerId = (int)$this->get('customer_id', 0);
        if ($customerId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Customer ID required'], 400);
        }

        $data = $this->adminModel->getCustomer360Details($customerId);

        if (!$data) {
            $this->json(['success' => false, 'message' => 'Customer profile not found'], 404);
        }

        $this->json(['success' => true, 'data' => $data]);
    }
    // ========================================================
    // ADM-05 ACTIONS: SERVICE AREAS & PRO MAPPING
    // ========================================================

    public function serviceZones(): void
    {
        $zones = $this->adminModel->getAllServiceZones();
        $pros  = $this->adminModel->getProfessionalsList('verified'); // Only verified pros can be mapped

        $this->view('admin/service_zones', [
            'pageTitle'    => 'Service Territories & Pro Mapping',
            'zones'        => $zones,
            'pros'         => $pros,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function saveServiceZone(): void
    {
        $id          = (int)$this->post('id', 0);
        $name        = (string)$this->post('name', '');
        $city        = (string)$this->post('city', 'Bengaluru');
        $state       = (string)$this->post('state', 'Karnataka');
        $postalCodes = (string)$this->post('postal_codes', '');
        $surge       = (float)$this->post('surge_multiplier', 1.00);
        $status      = (string)$this->post('status', 'active');

        if (empty(trim($name)) || empty(trim($postalCodes))) {
            $_SESSION['flash_err'] = 'Zone territory title and at least one postal pin code are required.';
            $this->redirect('/admin/zones');
        }

        try {
            $this->adminModel->saveZone([
                'id'               => $id > 0 ? $id : null,
                'name'             => trim($name),
                'city'             => trim($city),
                'state'            => trim($state),
                'postal_codes'     => $postalCodes,
                'surge_multiplier' => $surge,
                'status'           => $status
            ]);
            $_SESSION['flash_msg'] = 'Geographical service territory updated.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Operation failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/zones');
    }

    public function proMappingApi(): void
    {
        $proId = (int)$this->get('pro_id', 0);
        if ($proId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Professional ID required.'], 400);
        }

        $data = $this->adminModel->getProMappingData($proId);
        $this->json(['success' => true, 'data' => $data]);
    }

    public function saveProMapping(): void
    {
        $proId   = (int)$this->post('pro_id', 0);
        $zoneIds = $_POST['zone_ids'] ?? [];

        if ($proId <= 0) {
            $_SESSION['flash_err'] = 'Invalid technician specified.';
            $this->redirect('/admin/zones');
        }

        try {
            $this->adminModel->syncProZones($proId, (array)$zoneIds);
            $_SESSION['flash_msg'] = 'Technician territorial service zones synchronized.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Mapping failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/zones');
    }

    public function saveProRateOverride(): void
    {
        $proId       = (int)$this->post('pro_id', 0);
        $serviceId   = (int)$this->post('service_id', 0);
        $isOffered   = (int)$this->post('is_offered', 0) === 1;
        $customPrice = $this->post('custom_price') !== '' ? (float)$this->post('custom_price') : null;

        if ($proId <= 0 || $serviceId <= 0) {
            $this->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $this->adminModel->updateProServiceRate($proId, $serviceId, $isOffered, $customPrice);
        $this->json(['success' => true, 'message' => 'Technician custom rate card updated.']);
    }
    // ========================================================
    // ADM-06 ACTIONS: BOOKING DISPATCH & ASSIGNMENT
    // ========================================================

    public function dispatchBoard(): void
    {
        $status = (string)$this->get('status', '');
        $zoneId = (int)$this->get('zone_id', 0);
        $search = (string)$this->get('search', '');

        $bookings = $this->adminModel->getDispatchBookings([
            'status'  => $status !== '' ? $status : null,
            'zone_id' => $zoneId > 0 ? $zoneId : null,
            'search'  => $search !== '' ? $search : null,
        ]);

        $zones = $this->adminModel->getAllServiceZones();

        $this->view('admin/dispatch', [
            'pageTitle'    => 'Master Dispatch Board',
            'bookings'     => $bookings,
            'zones'        => $zones,
            'statusFilter' => $status,
            'zoneFilter'   => $zoneId,
            'search'       => $search,
            'canApproveProof' => Auth::role() === 'admin',
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function bookingDetailsApi(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        if ($bookingId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Booking ID required.'], 400);
        }

        $data = $this->adminModel->getBookingFullDetails($bookingId);
        if (!$data) {
            $this->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }

        $this->json(['success' => true, 'data' => $data]);
    }

    public function bookingCompletionStatus(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        if ($bookingId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid booking ID required.'], 400);
        }
        $summary = $this->adminModel->getBookingCompletionSummary($bookingId);
        if (!$summary) {
            $this->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }
        $this->json(['success' => true, 'booking' => $summary]);
    }

    public function manualAssignBooking(): void
    {
        $bookingId    = (int)$this->post('booking_id', 0);
        $proProfileId = (int)$this->post('pro_profile_id', 0);

        if ($bookingId <= 0 || $proProfileId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid booking and technician must be chosen.'], 400);
        }

        $assigned = $this->adminModel->assignProToBooking($bookingId, $proProfileId);

        if ($assigned) {
            $this->json(['success' => true, 'message' => 'Technician successfully assigned and dispatched.']);
        } else {
            $this->json(['success' => false, 'message' => 'Assignment operation failed.'], 500);
        }
    }

    public function autoAssignBooking(): void
    {
        $bookingId = (int)$this->post('booking_id', 0);
        if ($bookingId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Booking ID required.'], 400);
        }

        $matchedProId = $this->adminModel->autoDispatchMatch($bookingId);

        if ($matchedProId) {
            $this->json(['success' => true, 'message' => 'Optimal technician matched and dispatched automatically!']);
        } else {
            $this->json(['success' => false, 'message' => 'No active verified technician available in this zone.'], 404);
        }
    }

    public function updateBookingStatus(): void
    {
        $bookingId = (int)$this->post('booking_id', 0);
        $status    = (string)$this->post('status', '');
        $reason    = (string)$this->post('reason', '');

        if ($bookingId <= 0 || empty($status)) {
            $this->json(['success' => false, 'message' => 'Invalid parameters provided.'], 400);
        }

        $updated = $this->adminModel->updateBookingLifecycleStatus($bookingId, $status, $reason);

        if ($updated) {
            $this->json(['success' => true, 'message' => "Booking status updated to {$status}."]);
        } else {
            $this->json(['success' => false, 'message' => 'Unable to update status.'], 400);
        }
    }
    // ========================================================
    // ADM-07 ACTIONS: PRICING RULES & PROMOTIONS
    // ========================================================

    public function pricingAndCoupons(): void
    {
        $coupons = $this->adminModel->getAllCoupons();
        $globalRules = $this->adminModel->getGlobalPricingSettings();

        $this->view('admin/pricing_coupons', [
            'pageTitle'    => 'Pricing Rules & Promotions Engine',
            'coupons'      => $coupons,
            'rules'        => $globalRules,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function saveCoupon(): void
    {
        $id          = (int)$this->post('id', 0);
        $code        = (string)$this->post('code', '');
        $type        = (string)$this->post('discount_type', 'percentage');
        $value       = (float)$this->post('discount_value', 0.00);
        $minVal      = (float)$this->post('min_booking_value', 0.00);
        $maxDisc     = $this->post('max_discount') !== '' ? (float)$this->post('max_discount') : null;
        $usageLimit  = (int)$this->post('usage_limit', 1000);
        $validFrom   = (string)$this->post('valid_from', date('Y-m-d 00:00:00'));
        $validUntil  = (string)$this->post('valid_until', date('Y-12-31 23:59:59'));
        $status      = (string)$this->post('status', 'active');

        if (empty(trim($code)) || $value <= 0) {
            $_SESSION['flash_err'] = 'Promo code and a positive discount value are required.';
            $this->redirect('/admin/pricing-rules');
        }

        try {
            $this->adminModel->saveCouponRecord([
                'id'                => $id > 0 ? $id : null,
                'code'              => trim($code),
                'discount_type'     => $type,
                'discount_value'    => $value,
                'min_booking_value' => $minVal,
                'max_discount'      => $maxDisc,
                'usage_limit'       => $usageLimit,
                'valid_from'        => $validFrom,
                'valid_until'       => $validUntil,
                'status'            => $status
            ]);
            $_SESSION['flash_msg'] = 'Coupon promotion code successfully saved.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Operation failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/pricing-rules');
    }

    public function toggleCouponStatus(): void
    {
        $id     = (int)$this->post('id', 0);
        $status = (string)$this->post('status', 'active');

        if ($id <= 0 || !in_array($status, ['active', 'inactive'], true)) {
            $this->json(['success' => false, 'message' => 'Invalid parameters'], 400);
        }

        $this->adminModel->setCouponStatus($id, $status);
        $this->json(['success' => true, 'message' => "Coupon marked as {$status}."]);
    }

    public function updateGlobalPricingRules(): void
    {
        $minFee         = (float)$this->post('min_booking_fee', 199.00);
        $weekendSurge   = (float)$this->post('weekend_surge_multiplier', 1.10);
        $convFee        = (float)$this->post('platform_convenience_fee', 49.00);
        $emergencySurge = (float)$this->post('emergency_booking_multiplier', 1.25);

        try {
            $this->adminModel->saveGlobalPricingSettings([
                'min_booking_fee'              => $minFee,
                'weekend_surge_multiplier'     => $weekendSurge,
                'platform_convenience_fee'     => $convFee,
                'emergency_booking_multiplier' => $emergencySurge,
            ]);
            $_SESSION['flash_msg'] = 'Global pricing rules and fee thresholds updated.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Failed to update pricing rules: ' . $e->getMessage();
        }

        $this->redirect('/admin/pricing-rules');
    }
    // ========================================================
    // ADM-08 ACTIONS: FINANCIALS, INVOICES & PAYOUTS
    // ========================================================

    public function financialsLedger(): void
    {
        $type   = (string)$this->get('type', '');
        $search = (string)$this->get('search', '');

        $transactions = $this->adminModel->getFinancialLedger([
            'type'   => $type !== '' ? $type : null,
            'search' => $search !== '' ? $search : null
        ]);

        $payouts = $this->adminModel->getPendingPayoutRequests();

        $this->view('admin/financials', [
            'pageTitle'    => 'Financials, Invoicing & Payouts',
            'transactions' => $transactions,
            'payouts'      => $payouts,
            'typeFilter'   => $type,
            'search'       => $search,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function generateInvoiceView(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        if ($bookingId <= 0) {
            die('Invalid Booking Reference for Tax Invoice.');
        }

        $invoiceData = $this->adminModel->getInvoiceDetails($bookingId);
        if (!$invoiceData || empty($invoiceData['booking'])) {
            $financialsUrl = htmlspecialchars((defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/admin/financials', ENT_QUOTES, 'UTF-8');
            die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'><h3>Invoice Error</h3><p>Booking #{$bookingId} could not be found or has no records.</p><a href='{$financialsUrl}'>Back to Financials</a></div>");
        }

        // Pass the invoice both as direct variables and nested data for view compatibility.
        $this->view('admin/invoice_print', [
            'pageTitle' => 'Tax Invoice - ' . ($invoiceData['booking']['booking_code'] ?? 'FixMate'),
            'booking'   => $invoiceData['booking'],
            'items'     => $invoiceData['items'] ?? [],
            'data'      => $invoiceData
        ], 'plain');
    }

    public function processProPayout(): void
    {
        $payoutId  = (int)$this->post('payout_id', 0);
        $decision  = (string)$this->post('decision', 'approved');
        $reference = (string)$this->post('reference', '');

        if ($payoutId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
            $_SESSION['flash_err'] = 'Invalid payout request parameters.';
            $this->redirect('/admin/financials');
        }

        try {
            $this->adminModel->executePayoutDecision($payoutId, $decision, $reference !== '' ? $reference : null);
            $_SESSION['flash_msg'] = "Technician payout request marked as {$decision}.";
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Payout processing failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/financials');
    }
    // ========================================================
    // ADM-09 ACTIONS: REVIEWS, DISPUTES & HELPDESK
    // ========================================================

    public function helpdeskAndDisputes(): void
    {
        $disputeStatus = (string)$this->get('dispute_status', '');
        $reviewStatus  = (string)$this->get('review_status', '');

        $disputes = $this->adminModel->getAllDisputes($disputeStatus !== '' ? $disputeStatus : null);
        $reviews  = $this->adminModel->getAllReviews($reviewStatus !== '' ? $reviewStatus : null);

        $this->view('admin/disputes_helpdesk', [
            'pageTitle'     => 'Reviews, Disputes & Helpdesk Center',
            'disputes'      => $disputes,
            'reviews'       => $reviews,
            'canApproveProof' => Auth::role() === 'admin',
            'canManageHelpdeskBookings' => Auth::role() === 'admin',
            'disputeStatus' => $disputeStatus,
            'reviewStatus'  => $reviewStatus,
            'flashMessage'  => $_SESSION['flash_msg'] ?? null,
            'flashError'    => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function helpdeskWorkflowApi(): void
    {
        $this->json(['success' => true, 'items' => $this->adminModel->getHelpdeskWorkflowItems()]);
    }

    public function completeHelpdeskBooking(): void
    {
        if (Auth::role() !== 'admin') {
            $this->json(['success' => false, 'message' => 'Only an administrator can trigger booking completion.'], 403);
        }
        $bookingId = (int)$this->post('booking_id', 0);
        if ($bookingId <= 0) {
            $this->json(['success' => false, 'message' => 'A valid booking is required.'], 400);
        }
        try {
            $completed = (new \App\Models\ProfessionalModel())->completeBookingAfterPayment($bookingId);
        } catch (\Throwable $exception) {
            $this->json(['success' => false, 'message' => 'Completion could not be processed. Please retry after refreshing the workflow.'], 500);
        }
        if (!$completed) {
            $this->json(['success' => false, 'message' => 'Completion requirements are not all met: the latest proof must be approved, the customer code verified, and payment/COD receipt confirmed.'], 409);
        }
        $this->json(['success' => true, 'message' => 'Booking completed through the shared workflow. Pro earnings and customer status are synchronized.']);
    }

    public function cancelHelpdeskBooking(): void
    {
        if (Auth::role() !== 'admin') {
            $this->json(['success' => false, 'message' => 'Only an administrator can cancel a booking from Helpdesk.'], 403);
        }
        $bookingId = (int)$this->post('booking_id', 0);
        $reason = trim((string)$this->post('reason', ''));
        if ($bookingId <= 0 || strlen($reason) < 5) {
            $this->json(['success' => false, 'message' => 'A valid booking and cancellation reason (at least 5 characters) are required.'], 422);
        }
        try {
            $cancelled = $this->adminModel->cancelHelpdeskBooking($bookingId, $reason);
        } catch (\Throwable $exception) {
            $this->json(['success' => false, 'message' => 'Cancellation failed. Please refresh and try again.'], 500);
        }
        if (!$cancelled) {
            $this->json(['success' => false, 'message' => 'This booking cannot be cancelled here. It may already be completed, cancelled, or under active dispute.'], 409);
        }
        $this->json(['success' => true, 'message' => 'Booking cancelled. Customer and professional panels will now show the updated status.']);
    }

    public function disputeDetailsApi(): void
    {
        $disputeId = (int)$this->get('dispute_id', 0);
        if ($disputeId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Dispute ID required.'], 400);
        }

        $data = $this->adminModel->getDisputeDetail($disputeId);
        if (!$data) {
            $this->json(['success' => false, 'message' => 'Dispute not found.'], 404);
        }

        $this->json(['success' => true, 'data' => $data]);
    }

    public function resolveDisputeAction(): void
    {
        if (Auth::role() !== 'admin') {
            $this->json(['success' => false, 'message' => 'Only an administrator can resolve disputes.'], 403);
        }
        $disputeId = (int)$this->post('dispute_id', 0);
        $status    = (string)$this->post('status', 'resolved');
        $notes     = (string)$this->post('resolution_notes', '');

        if ($disputeId <= 0 || empty(trim($notes))) {
            $this->json(['success' => false, 'message' => 'Dispute ID and written official resolution notes are required.'], 422);
        }

        try {
            $updated = $this->adminModel->resolveDispute($disputeId, $status, trim($notes));
            if (!$updated) {
                $this->json(['success' => false, 'message' => 'Dispute could not be updated. Refresh the page and verify its current state.'], 409);
            }
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Resolution failed. Please try again.'], 500);
        }

        $this->json(['success' => true, 'message' => "Dispute registered as '{$status}'. Booking and completion state have been synchronized."]);
    }

    public function moderateReviewAction(): void
    {
        $reviewId = (int)$this->post('review_id', 0);
        $status   = (string)$this->post('status', 'published');

        if ($reviewId <= 0) {
            $this->json(['success' => false, 'message' => 'Valid Review ID required.'], 400);
        }

        $this->adminModel->moderateReview($reviewId, $status);
        $this->json(['success' => true, 'message' => "Review visibility set to {$status}."]);
    }
    // ========================================================
    // ADM-10 ACTIONS: SYSTEM SETTINGS & AUDIT LOGS
    // ========================================================

    public function systemSettingsAndLogs(): void
    {
        $settings  = $this->adminModel->getAllSystemSettings();
        $auditLogs = $this->adminModel->getSystemAuditLogs(50);

        $this->view('admin/system_settings', [
            'pageTitle'    => 'System Configuration & Audit Logs',
            'settings'     => $settings,
            'auditLogs'    => $auditLogs,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'admin');

        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function saveSystemConfig(): void
    {
        $platformName = (string)$this->post('platform_name', 'FixMate India');
        $contactEmail = (string)$this->post('contact_email', 'support@fixmate.in');
        $contactPhone = (string)$this->post('contact_phone', '+91 80 4920 1800');
        $currency     = (string)$this->post('currency_symbol', '₹');
        $baseComm     = (float)$this->post('base_commission_pct', 15.00);
        $taxGst       = (float)$this->post('tax_gst_pct', 18.00);
        $payoutMin    = (float)$this->post('payout_min_limit', 1000.00);
        $dispatchMode = (string)$this->post('emergency_dispatch_mode', 'manual');

        try {
            $this->adminModel->updateBulkSettings([
                'platform_name'           => $platformName,
                'contact_email'           => $contactEmail,
                'contact_phone'           => $contactPhone,
                'currency_symbol'         => $currency,
                'base_commission_pct'     => (string)$baseComm,
                'tax_gst_pct'             => (string)$taxGst,
                'payout_min_limit'        => (string)$payoutMin,
                'emergency_dispatch_mode' => $dispatchMode
            ], 'general');

            $_SESSION['flash_msg'] = 'Platform configuration settings saved successfully.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Failed to update system settings: ' . $e->getMessage();
        }

        $this->redirect('/admin/settings');
    }

    public function saveAnnouncement(): void
    {
        $enabled = isset($_POST['announcement_enabled']) ? '1' : '0';
        $message = (string)$this->post('announcement_message', '');
        $type    = (string)$this->post('announcement_type', 'info');

        try {
            $this->adminModel->updateBulkSettings([
                'announcement_enabled' => $enabled,
                'announcement_message' => $message,
                'announcement_type'    => $type
            ], 'announcements');

            $_SESSION['flash_msg'] = 'System announcement status updated.';
        } catch (\Exception $e) {
            $_SESSION['flash_err'] = 'Failed to save announcement: ' . $e->getMessage();
        }

        $this->redirect('/admin/settings');
    }
}