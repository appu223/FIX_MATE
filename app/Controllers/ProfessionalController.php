<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\ProfessionalModel;

class ProfessionalController extends Controller
{
    private ProfessionalModel $proModel;
    private array $proProfile;

    public function __construct()
    {
        if (!Auth::check() || Auth::role() !== 'professional') {
            $this->redirect('/landing');
        }

        $this->proModel = new ProfessionalModel();
        $profile = $this->proModel->getProProfileByUserId(Auth::id());
        if (!$profile) {
            $this->redirect('/landing');
        }
        $this->proProfile = $profile;
    }

    // PRO-01: Workbench
    public function dashboard(): void
    {
        $proId   = (int)$this->proProfile['id'];
        $metrics = $this->proModel->getWorkbenchMetrics($proId);
        $jobs    = $this->proModel->getActiveJobs($proId);
        $notifications = $this->proModel->getDashboardNotifications(Auth::id());
        $chatModel = new \App\Models\AdminProfessionalChatModel();
        $adminUnreadCount = $chatModel->getUnreadCount($proId, Auth::id());
        $adminMessages = $chatModel->getMessages($proId, Auth::id());

        $this->view('professional/dashboard', [
            'pageTitle' => 'Technician Command Workbench',
            'metrics'   => $metrics,
            'jobs'      => $jobs,
            'profile'   => $this->proProfile,
            'notifications' => $notifications,
            'adminMessages' => $adminMessages,
            'adminUnreadCount' => $adminUnreadCount,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError' => $_SESSION['flash_err'] ?? null,
        ], 'professional');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function sendAdminMessage(): void
    {
        $message = trim((string)$this->post('message', ''));
        $chatModel = new \App\Models\AdminProfessionalChatModel();
        if ($chatModel->sendMessage((int)$this->proProfile['id'], Auth::id(), $message)) {
            $_SESSION['flash_msg'] = 'Your message was sent to Fixmate administration.';
        } else {
            $_SESSION['flash_err'] = 'Message could not be sent. Please check that it is not empty and try again.';
        }
        $this->redirect('/pro/dashboard#admin-support-chat');
    }

    public function adminChatMessages(): void
    {
        $chatModel = new \App\Models\AdminProfessionalChatModel();
        $proId = (int)$this->proProfile['id'];
        $messages = $chatModel->getMessages($proId, Auth::id());
        $this->json(['success' => true, 'messages' => $messages]);
    }

    public function dashboardUpdates(): void
    {
        $profile = $this->proModel->getProProfileByUserId(Auth::id()) ?? [];
        $notifications = $this->proModel->getDashboardNotifications(Auth::id());
        $chatModel = new \App\Models\AdminProfessionalChatModel();
        $this->json([
            'success' => true,
            'kyc_status' => $profile['kyc_status'] ?? 'pending',
            'kyc_rejected_reason' => $profile['kyc_rejected_reason'] ?? '',
            'notifications' => $notifications,
            'admin_unread_count' => $chatModel->getUnreadCount((int)($profile['id'] ?? 0), Auth::id()),
        ]);
    }

    // PRO-02: KYC Submission
    public function kycView(): void
    {
        $this->view('professional/kyc_submit', [
            'pageTitle' => 'KYC Compliance & Verification Dossier',
            'profile'   => $this->proProfile,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError' => $_SESSION['flash_err'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function uploadKyc(): void
    {
        $type = (string)$this->post('id_proof_type', 'Aadhaar Card');
        if (!in_array($type, ['Aadhaar Card', 'PAN Card', 'Voter ID', 'Driving License'], true)) {
            $_SESSION['flash_err'] = 'Choose a valid government ID type.';
            $this->redirect('/pro/kyc');
        }
        $idFile = $this->storePrivateUpload($_FILES['id_file'] ?? [], 'kyc', ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], 8 * 1024 * 1024);
        if (!$idFile) {
            $_SESSION['flash_err'] = 'Choose a valid ID document (PDF, JPG, PNG, or WebP; maximum 8 MB).';
            $this->redirect('/pro/kyc');
        }
        $addressFile = $this->storePrivateUpload($_FILES['address_file'] ?? [], 'kyc', ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], 8 * 1024 * 1024);
        if (!$addressFile) {
            @unlink(dirname(__DIR__, 2) . '/storage/' . $idFile);
            $_SESSION['flash_err'] = 'Choose a valid address proof (PDF, JPG, PNG, or WebP; maximum 8 MB).';
            $this->redirect('/pro/kyc');
        }
        $licenseFile = null;
        if (isset($_FILES['license_file']) && (int)($_FILES['license_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $licenseFile = $this->storePrivateUpload($_FILES['license_file'], 'kyc', ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], 8 * 1024 * 1024);
            if (!$licenseFile) {
                @unlink(dirname(__DIR__, 2) . '/storage/' . $idFile);
                @unlink(dirname(__DIR__, 2) . '/storage/' . $addressFile);
                $_SESSION['flash_err'] = 'The optional license file is invalid. Use PDF, JPG, PNG, or WebP under 8 MB.';
                $this->redirect('/pro/kyc');
            }
        }

        $this->proModel->saveKycDocuments((int)$this->proProfile['id'], $type, $idFile, $addressFile, $licenseFile);
        $_SESSION['flash_msg'] = 'Documents uploaded successfully. Your account is pending administrator verification.';
        $this->redirect('/pro/kyc');
    }

    public function viewWorkProofFile(): void
    {
        $proofId = (int)$this->get('proof_id', 0);
        $path = $this->proModel->getProofPathForProfessional($proofId, (int)$this->proProfile['id']);
        $this->streamPrivateUpload($path);
    }

    private function storePrivateUpload(array $upload, string $folder, array $allowedMimes, int $maxBytes): ?string
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || empty($upload['tmp_name'])
            || (int)($upload['size'] ?? 0) <= 0
            || (int)$upload['size'] > $maxBytes
            || !is_uploaded_file($upload['tmp_name'])) {
            return null;
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
        $extensions = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!in_array($mime, $allowedMimes, true) || !isset($extensions[$mime])) {
            return null;
        }
        $directory = dirname(__DIR__, 2) . '/storage/' . $folder;
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            return null;
        }
        $name = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
        if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $name)) {
            return null;
        }
        return $folder . '/' . $name;
    }

    private function streamPrivateUpload(?string $storedPath): never
    {
        if (!$storedPath) {
            http_response_code(404);
            exit('The work attachment is not available.');
        }
        $fileName = basename(str_replace('\\', '/', $storedPath));
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $fileName)) {
            http_response_code(404);
            exit('The work attachment is not available.');
        }
        if (str_starts_with($storedPath, 'proofs/')) {
            $file = dirname(__DIR__, 2) . '/storage/proofs/' . $fileName;
        } elseif (str_starts_with($storedPath, 'uploads/proofs/')) {
            $file = dirname(__DIR__, 2) . '/public/uploads/proofs/' . $fileName;
        } else {
            http_response_code(404);
            exit('The work attachment is not available.');
        }
        if (!is_file($file) || !is_readable($file)) {
            http_response_code(404);
            exit('The work attachment is missing.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($file));
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('X-Content-Type-Options: nosniff');
        readfile($file);
        exit;
    }

    // PRO-03: Rates & Coverage
    public function ratesAndZones(): void
    {
        $data = $this->proModel->getProRatesAndZones((int)$this->proProfile['id']);
        $this->view('professional/service_rates', [
            'pageTitle' => 'Service Rates & Territory Coverage',
            'zones'     => $data['zones'],
            'services'  => $data['services'],
            'flashMessage' => $_SESSION['flash_msg'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg']);
    }

    public function saveRates(): void
    {
        $prices = $_POST['prices'] ?? [];
        $this->proModel->syncRates((int)$this->proProfile['id'], (array)$prices);
        $_SESSION['flash_msg'] = 'Custom service rate card updated successfully.';
        $this->redirect('/pro/rates-zones');
    }

    // PRO-04: Schedule & Leaves
    public function scheduleView(): void
    {
        $proId    = (int)$this->proProfile['id'];
        $schedule = $this->proModel->getWeeklySchedule($proId);
        $leaves   = $this->proModel->getLeaves($proId);

        $this->view('professional/calendar', [
            'pageTitle' => 'Working Shifts & Time-off Management',
            'schedule'  => $schedule,
            'leaves'    => $leaves,
            'flashMessage' => $_SESSION['flash_msg'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg']);
    }

    public function saveSchedule(): void
    {
        $shifts = $_POST['shifts'] ?? [];
        $this->proModel->saveShiftTimes((int)$this->proProfile['id'], (array)$shifts);
        $_SESSION['flash_msg'] = 'Working hours updated.';
        $this->redirect('/pro/schedule');
    }

    public function requestLeave(): void
    {
        $date   = (string)$this->post('leave_date', '');
        $reason = (string)$this->post('reason', '');
        $this->proModel->logLeave((int)$this->proProfile['id'], $date, $reason);
        $_SESSION['flash_msg'] = 'Time-off registered for ' . date('d M Y', strtotime($date));
        $this->redirect('/pro/schedule');
    }

    // PRO-05: Leads & Bidding
    public function jobLeads(): void
    {
        $leads = $this->proModel->getAvailableLeads();
        $this->view('professional/job_bids', [
            'pageTitle' => 'Custom Job Leads & Quotation Engine',
            'leads'     => $leads,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function submitBid(): void
    {
        $qid   = (int)$this->post('quotation_id', 0);
        $price = (float)$this->post('estimated_price', 0.0);
        $notes = trim((string)$this->post('notes', ''));

        if ($qid <= 0) {
            $_SESSION['flash_err'] = 'Invalid quotation selected. Please reopen the lead and try again.';
            $this->redirect('/pro/leads');
        }
        if ($price <= 0) {
            $_SESSION['flash_err'] = 'Please enter a valid estimated price greater than zero.';
            $this->redirect('/pro/leads');
        }

        try {
            $this->proModel->submitQuotationBid($qid, (int)$this->proProfile['id'], $price, $notes);
            $_SESSION['flash_msg'] = 'Quotation estimate proposal dispatched to customer.';
        } catch (\Throwable $exception) {
            error_log((string)$exception);
            $_SESSION['flash_err'] = 'This lead is no longer available for bidding. Please refresh and try again.';
        }
        $this->redirect('/pro/leads');
    }

    // PRO-06: Active Jobs & Fulfillment
    public function fulfillmentView(): void
    {
        $jobs = $this->proModel->getActiveJobs((int)$this->proProfile['id']);
        $this->view('professional/fulfillment', [
            'pageTitle' => 'Live Job Fulfillment & Status Dispatch',
            'jobs'      => $jobs,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError' => $_SESSION['flash_err'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function fulfillmentUpdates(): void
    {
        $jobs = $this->proModel->getFulfillmentUpdates((int)$this->proProfile['id']);
        $this->json(['success' => true, 'jobs' => $jobs]);
    }

    public function updateJobStatus(): void
    {
        $bid    = (int)$this->post('booking_id', 0);
        $status = (string)$this->post('status', 'completed');
        $updated = $this->proModel->setBookingStatus($bid, (int)$this->proProfile['id'], $status);
        if ($updated) {
            $_SESSION['flash_msg'] = "Job updated to {$status}.";
        } else {
            $_SESSION['flash_err'] = 'Job progress could not be updated. Submit after-work proof and wait for administrator approval to complete the job.';
        }
        $this->redirect('/pro/fulfillment');
    }

    public function requestCompletionOtp(): void
    {
        $bookingId = (int)$this->post('booking_id', 0);
        try {
            $sent = $this->proModel->requestCompletionOtp($bookingId, (int)$this->proProfile['id']);
            $_SESSION[$sent ? 'flash_msg' : 'flash_err'] = $sent
                ? 'A 4-digit completion code was sent to the customer booking panel. Ask the customer to read it to you.'
                : 'A completion code could not be sent. Check that the job is in progress and an after-work proof is uploaded; codes are limited to one per minute and five per day.';
        } catch (\Throwable $exception) {
            error_log((string)$exception);
            $_SESSION['flash_err'] = 'Could not send a customer completion code right now.';
        }
        $returnTo = (string)$this->post('return_to', '');
        $this->redirect($returnTo === 'chat' ? '/pro/nav-chat?booking_id=' . $bookingId : '/pro/fulfillment');
    }

    public function verifyCompletionOtp(): void
    {
        $bookingId = (int)$this->post('booking_id', 0);
        $code = trim((string)$this->post('completion_code', ''));
        $codReceived = (string)$this->post('cod_received', '0') === '1';
        try {
            $result = $this->proModel->verifyCompletionOtp($bookingId, (int)$this->proProfile['id'], $code, $codReceived);
            $_SESSION[$result['success'] ? 'flash_msg' : 'flash_err'] = $result['message'];
        } catch (\Throwable $exception) {
            error_log((string)$exception);
            $_SESSION['flash_err'] = 'Could not verify the completion code. Please try again.';
        }
        $this->redirect('/pro/fulfillment');
    }

    // PRO-07: Chat & Navigation
    public function navChatView(): void
    {
        $bid = (int)$this->get('booking_id', 0);
        if ($bid <= 0) {
            $jobs = $this->proModel->getActiveJobs((int)$this->proProfile['id']);
            $bid = (int)($jobs[0]['id'] ?? 0);
            if ($bid <= 0) {
                $_SESSION['flash_err'] = 'There are no active assigned bookings to chat about yet.';
                $this->redirect('/pro/fulfillment');
            }
        }
        $chatModel = new \App\Models\CustomerModel();
        $chatBooking = $bid > 0 ? $chatModel->getChatBookingDetails($bid) : null;
        if (!$chatBooking || (int)$chatBooking['professional_user_id'] !== Auth::id()) {
            http_response_code(404);
            echo 'Booking chat not found.';
            return;
        }
        $messages = $chatModel->getChatMessages($bid, Auth::id());

        $this->view('professional/nav_chat', [
            'pageTitle' => 'Customer Navigation & Direct Chat',
            'bookingId' => $bid,
            'chatBooking' => $chatBooking,
            'messages' => $messages,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError' => $_SESSION['flash_err'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function chatMessagesApi(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        $chatModel = new \App\Models\CustomerModel();
        $booking = $chatModel->getChatBookingDetails($bookingId);
        if (!$booking || (int)$booking['professional_user_id'] !== Auth::id()) {
            $this->json(['success' => false, 'message' => 'Booking chat not found.'], 404);
        }
        $this->json(['success' => true, 'messages' => $chatModel->getChatMessages($bookingId, Auth::id())]);
    }

    public function chatBookingStatusApi(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        $chatModel = new \App\Models\CustomerModel();
        $booking = $chatModel->getChatBookingDetails($bookingId);
        if (!$booking || (int)$booking['professional_user_id'] !== Auth::id()) {
            $this->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }
        $this->json(['success' => true, 'booking' => [
            'status' => $booking['status'],
            'payment_method' => $booking['payment_method'],
            'payment_status' => $booking['payment_status'],
            'after_proof_status' => $booking['after_proof_status'],
            'completion_otp_verified' => !empty($booking['completion_otp_verified_at']),
            'completion_otp_expires_at' => $booking['completion_otp_expires_at']
        ]]);
    }

    public function sendMessage(): void
    {
        $bid = (int)$this->post('booking_id', 0);
        $msg = trim((string)$this->post('message', ''));

        $chatModel = new \App\Models\CustomerModel();
        $booking = $bid > 0 ? $chatModel->getChatBookingDetails($bid) : null;
        if ($booking && (int)$booking['professional_user_id'] === Auth::id()
            && $msg !== '' && mb_strlen($msg) <= 4000) {
            $chatModel->postChatMessage($bid, Auth::id(), (int)$booking['customer_id'], $msg);
        }
        $this->redirect('/pro/nav-chat?booking_id=' . $bid);
    }

    // PRO-08: Proof of Work
    public function proofOfWorkView(): void
    {
        $bid = (int)$this->get('booking_id', 0);
        if (!$this->proModel->ownsBooking($bid, (int)$this->proProfile['id'])) {
            $_SESSION['flash_err'] = 'You can only view proof records for your own assigned bookings.';
            $this->redirect('/pro/fulfillment');
        }
        $proofs = $this->proModel->getBookingProofs($bid);

        $this->view('professional/proof_work', [
            'pageTitle' => 'Proof of Work & Material Receipts',
            'bookingId' => $bid,
            'proofs'    => $proofs,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError' => $_SESSION['flash_err'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function uploadProof(): void
    {
        $bid  = (int)$this->post('booking_id', 0);
        $type = (string)$this->post('proof_type', 'after');
        $desc = (string)$this->post('description', '');
        $path = $this->storePrivateUpload($_FILES['proof_file'] ?? [], 'proofs', ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], 10 * 1024 * 1024);
        if (!$path) {
            $_SESSION['flash_err'] = 'Choose a valid proof file (JPG, PNG, WebP, or PDF; maximum 10 MB).';
            $this->redirect('/pro/proof-of-work?booking_id=' . $bid);
        }
        if (!$this->proModel->saveProof($bid, (int)$this->proProfile['id'], $type, $path, mb_substr(trim($desc), 0, 255))) {
            @unlink(dirname(__DIR__, 2) . '/storage/' . $path);
            $_SESSION['flash_err'] = 'You can only upload proof to one of your current assigned jobs.';
            $this->redirect('/pro/proof-of-work?booking_id=' . $bid);
        }
        $_SESSION['flash_msg'] = 'Job proof of work image uploaded.';
        $this->redirect('/pro/proof-of-work?booking_id=' . $bid);
    }

    // PRO-09: Earnings & Payouts
    public function earningsView(): void
    {
        $data = $this->proModel->getEarningsLedger((int)$this->proProfile['id']);
        $this->view('professional/earnings', [
            'pageTitle' => 'Partner Earnings Ledger & Payout Requests',
            'jobs'      => $data['jobs'],
            'payouts'   => $data['payouts'],
            'profile'   => $this->proProfile,
            'flashMessage' => $_SESSION['flash_msg'] ?? null
        ], 'professional');
        unset($_SESSION['flash_msg']);
    }

    public function requestPayout(): void
    {
        $amount = (float)$this->post('amount', 0.0);
        if ($amount < 500) {
            $_SESSION['flash_msg'] = 'Minimum withdrawal threshold is ₹500.00';
        } else {
            $this->proModel->requestPayout((int)$this->proProfile['id'], $amount);
            $_SESSION['flash_msg'] = "Payout transfer request for ₹{$amount} submitted for processing.";
        }
        $this->redirect('/pro/earnings');
    }

    // PRO-10: Feedback & Reviews
    public function feedbackView(): void
    {
        $reviews = $this->proModel->getProReviews((int)$this->proProfile['id']);
        $this->view('professional/feedback', [
            'pageTitle' => 'Customer Feedback, Star Ratings & Disputes',
            'reviews'   => $reviews,
            'profile'   => $this->proProfile
        ], 'professional');
    }
}