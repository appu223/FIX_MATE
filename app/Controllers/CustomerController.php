<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\CustomerModel;

class CustomerController extends Controller
{
    protected CustomerModel $customerModel;

    public function __construct()
    {
        if (!Auth::check() || Auth::role() !== 'customer') {
            $this->redirect('/landing');
        }

        $this->customerModel = new CustomerModel();
    }

    // CUS-01: Discovery Hub
    public function home(): void
    {
        $query       = (string)$this->get('q', '');
        $categoryId  = (int)$this->get('cat', 0);
        $selectedProId = (int)$this->get('pro_id', 0);
        $categories  = $this->customerModel->getActiveCategories();
        $selectedProfessional = null;
        $selectionError = null;
        if ($selectedProId > 0) {
            $selectedProfessional = $this->customerModel->getActiveProfessionalForBooking($selectedProId);
            if ($selectedProfessional) {
                $services = $this->customerModel->getProfessionalServices($selectedProId, $query, $categoryId);
            } else {
                $selectionError = 'That technician is no longer available for new bookings. Please select another professional.';
                $services = [];
            }
        } else {
            $services = ($query !== '' || $categoryId > 0)
                ? $this->customerModel->searchCatalog($query, $categoryId)
                : $this->customerModel->getPopularServices(8);
        }

        $this->view('customer/home', [
            'pageTitle'   => 'Expert Home Services & Repair',
            'categories'  => $categories,
            'services'    => $services,
            'query'       => $query,
            'selectedCat' => $categoryId,
            'isSearch'    => ($query !== '' || $categoryId > 0),
            'selectedProfessional' => $selectedProfessional,
            'selectedProId' => $selectedProId,
            'selectionError' => $selectionError
        ], 'customer');
    }

    // CUS-02: Profile & Addresses
    public function profileAndAddresses(): void
    {
        $userId    = Auth::id();
        $profile   = $this->customerModel->getCustomerProfile($userId);
        $addresses = $this->customerModel->getCustomerAddresses($userId);

        $this->view('customer/profile', [
            'pageTitle'    => 'My Profile & Address Book',
            'profile'      => $profile,
            'addresses'    => $addresses,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_err'] ?? null
        ], 'customer');
        unset($_SESSION['flash_msg'], $_SESSION['flash_err']);
    }

    public function saveProfile(): void
    {
        $name  = (string)$this->post('name', '');
        $phone = (string)$this->post('phone', '');
        $this->customerModel->updateCustomerProfile(Auth::id(), $name, $phone);
        $_SESSION['flash_msg'] = 'Profile details updated.';
        $this->redirect('/customer/profile');
    }

    public function saveAddress(): void
    {
        $data = [
            'id'            => (int)$this->post('id', 0) ?: null,
            'label'         => (string)$this->post('label', 'Home'),
            'address_line1' => (string)$this->post('address_line1', ''),
            'address_line2' => (string)$this->post('address_line2', ''),
            'landmark'      => (string)$this->post('landmark', ''),
            'city'          => (string)$this->post('city', 'Bengaluru'),
            'state'         => (string)$this->post('state', 'Karnataka'),
            'postal_code'   => (string)$this->post('postal_code', '560034'),
            'is_default'    => isset($_POST['is_default']) ? 1 : 0
        ];
        $this->customerModel->saveCustomerAddress(Auth::id(), $data);
        $_SESSION['flash_msg'] = 'Address saved successfully.';
        $this->redirect('/customer/profile');
    }

    public function deleteAddress(): void
    {
        $aid = (int)$this->post('address_id', 0);
        $this->customerModel->deleteAddress(Auth::id(), $aid);
        $_SESSION['flash_msg'] = 'Address removed.';
        $this->redirect('/customer/profile');
    }

    // CUS-03: Professional Search & Profiles
    public function searchPros(): void
    {
        $q    = (string)$this->get('q', '');
        $pros = $this->customerModel->getActiveProsDirectory($q !== '' ? $q : null);

        $this->view('customer/pro_search', [
            'pageTitle' => 'Verified Service Professionals',
            'pros'      => $pros,
            'search'    => $q
        ], 'customer');
    }

    // CUS-04 & CUS-08: Cart & Checkout
    public function cartView(): void
    {
        $userId    = Auth::id();
        $addresses = $this->customerModel->getCustomerAddresses($userId);

        $this->view('customer/checkout', [
            'pageTitle' => 'Cart & Express Checkout',
            'addresses' => $addresses,
            'razorpayKeyId' => getenv('RAZORPAY_KEY_ID') ?: '',
            'onlinePaymentsEnabled' => (getenv('RAZORPAY_KEY_ID') ?: '') !== '' && (getenv('RAZORPAY_KEY_SECRET') ?: '') !== ''
        ], 'customer');
    }

    public function applyCouponApi(): void
    {
        $code  = (string)$this->post('code', '');
        $total = (float)$this->post('subtotal', 0.0);
        $res   = $this->customerModel->validateCoupon($code, $total);

        if (!$res) {
            $this->json(['success' => false, 'message' => 'Invalid or expired coupon code.'], 400);
        }
        if (isset($res['error'])) {
            $this->json(['success' => false, 'message' => $res['error']], 400);
        }

        $this->json([
            'success'         => true,
            'coupon_id'       => $res['coupon']['id'],
            'code'            => $res['coupon']['code'],
            'discount_amount' => $res['discount_amount']
        ]);
    }

    public function placeOrder(): void
    {
        $userId = Auth::id();
        $raw    = file_get_contents('php://input');
        $body   = json_decode($raw, true);

        if (!is_array($body) || empty($body['items']) || !is_array($body['items']) || empty($body['order']) || !is_array($body['order'])) {
            $this->json(['success' => false, 'message' => 'Your cart is empty.'], 400);
        }
        if (($body['order']['payment_method'] ?? '') === 'online'
            && (!(getenv('RAZORPAY_KEY_ID') ?: '') || !(getenv('RAZORPAY_KEY_SECRET') ?: ''))) {
            $this->json(['success' => false, 'message' => 'Online payments are not configured yet. Please choose cash on service completion or contact Fixmate support.'], 503);
        }

        try {
            $code = $this->customerModel->createCustomerBooking($userId, $body['order'], $body['items']);
            $this->json(['success' => true, 'booking_code' => $code]);
        } catch (\InvalidArgumentException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            error_log((string)$e);
            $this->json(['success' => false, 'message' => 'We could not place the booking right now. Please try again.'], 500);
        }
    }

    public function createOnlinePaymentOrder(): void
    {
        $keyId = getenv('RAZORPAY_KEY_ID') ?: '';
        $keySecret = getenv('RAZORPAY_KEY_SECRET') ?: '';
        if ($keyId === '' || $keySecret === '') {
            $this->json(['success' => false, 'message' => 'Razorpay is not configured on this server.'], 503);
        }
        $request = json_decode((string)file_get_contents('php://input'), true);
        $bookingCode = is_array($request) ? trim((string)($request['booking_code'] ?? '')) : '';
        $booking = $this->customerModel->getPendingOnlineBooking(Auth::id(), $bookingCode);
        if (!$booking) {
            $this->json(['success' => false, 'message' => 'Pending online booking not found.'], 404);
        }

        if (!empty($booking['gateway_order_id'])) {
            $orderId = (string)$booking['gateway_order_id'];
        } else {
            $gatewayOrder = $this->razorpayRequest('POST', '/orders', [
                'amount' => (int)round((float)$booking['total_amount'] * 100),
                'currency' => 'INR',
                'receipt' => substr((string)$booking['booking_code'], 0, 40),
                'notes' => ['booking_code' => (string)$booking['booking_code']]
            ], $keyId, $keySecret);
            if (!$gatewayOrder || empty($gatewayOrder['id'])) {
                $this->json(['success' => false, 'message' => 'Razorpay could not start checkout. Please retry or use cash on service completion.'], 502);
            }
            $orderId = (string)$gatewayOrder['id'];
            $this->customerModel->saveRazorpayOrder(Auth::id(), (int)$booking['id'], $orderId);
        }

        $this->json([
            'success' => true,
            'key_id' => $keyId,
            'order_id' => $orderId,
            'amount' => (int)round((float)$booking['total_amount'] * 100),
            'currency' => 'INR',
            'booking_code' => $booking['booking_code']
        ]);
    }

    public function verifyOnlinePayment(): void
    {
        $keyId = getenv('RAZORPAY_KEY_ID') ?: '';
        $keySecret = getenv('RAZORPAY_KEY_SECRET') ?: '';
        if ($keyId === '' || $keySecret === '') {
            $this->json(['success' => false, 'message' => 'Razorpay is not configured on this server.'], 503);
        }
        $request = json_decode((string)file_get_contents('php://input'), true);
        $request = is_array($request) ? $request : [];
        $orderId = trim((string)($request['razorpay_order_id'] ?? ''));
        $paymentId = trim((string)($request['razorpay_payment_id'] ?? ''));
        $signature = trim((string)($request['razorpay_signature'] ?? ''));
        if ($orderId === '' || $paymentId === '' || $signature === '') {
            $this->json(['success' => false, 'message' => 'Payment confirmation details are incomplete.'], 422);
        }
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);
        if (!hash_equals($expected, $signature)) {
            $this->json(['success' => false, 'message' => 'Payment verification failed. Do not retry from an untrusted page.'], 403);
        }
        $booking = $this->customerModel->getPendingOnlineBookingByOrderId(Auth::id(), $orderId);
        if (!$booking) {
            $this->json(['success' => false, 'message' => 'This payment order does not belong to a pending booking on your account.'], 404);
        }
        $payment = $this->razorpayRequest('GET', '/payments/' . rawurlencode($paymentId), null, $keyId, $keySecret);
        if (!$payment || ($payment['order_id'] ?? '') !== $orderId
            || ($payment['status'] ?? '') !== 'captured'
            || (int)($payment['amount'] ?? 0) !== (int)round((float)$booking['total_amount'] * 100)
            || ($payment['currency'] ?? '') !== 'INR') {
            $this->json(['success' => false, 'message' => 'Payment has not been captured for the expected booking total.'], 409);
        }
        $confirmed = $this->customerModel->confirmRazorpayPayment(Auth::id(), $orderId, $paymentId);
        if (!$confirmed) {
            $this->json(['success' => false, 'message' => 'Payment could not be linked to the booking. Contact Fixmate support.'], 409);
        }
        (new \App\Models\ProfessionalModel())->completeBookingAfterPayment((int)$booking['id']);
        $this->json(['success' => true, 'booking_code' => $confirmed['booking_code']]);
    }

    private function razorpayRequest(string $method, string $path, ?array $payload, string $keyId, string $keySecret): ?array
    {
        $curl = curl_init('https://api.razorpay.com/v1' . $path);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_USERPWD => $keyId . ':' . $keySecret,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload, JSON_THROW_ON_ERROR));
        }
        $response = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($response === false) {
            error_log('Razorpay request failed: ' . curl_error($curl));
            curl_close($curl);
            return null;
        }
        curl_close($curl);
        $data = json_decode((string)$response, true);
        if ($status < 200 || $status >= 300 || !is_array($data)) {
            error_log('Razorpay returned HTTP ' . $status . ': ' . substr((string)$response, 0, 1000));
            return null;
        }
        return $data;
    }

    // CUS-05: Quotations
    public function quotationView(): void
    {
        $userId     = Auth::id();
        $categories = $this->customerModel->getActiveCategories();
        $quotes     = $this->customerModel->getCustomerQuotes($userId);

        $this->view('customer/quotation_request', [
            'pageTitle'    => 'Custom Project Quotations',
            'categories'   => $categories,
            'quotes'       => $quotes,
            'flashMessage' => $_SESSION['flash_msg'] ?? null
        ], 'customer');
        unset($_SESSION['flash_msg']);
    }

    public function submitQuotationRequest(): void
    {
        $cid   = (int)$this->post('category_id', 0);
        $title = (string)$this->post('title', '');
        $desc  = (string)$this->post('description', '');
        $bmin  = (float)$this->post('budget_min', 0.0);
        $bmax  = (float)$this->post('budget_max', 0.0);

        $this->customerModel->submitCustomQuote(Auth::id(), $cid, $title, $desc, $bmin, $bmax);
        $_SESSION['flash_msg'] = 'Quotation request submitted! Verified professionals will bid shortly.';
        $this->redirect('/customer/custom-quote');
    }

    // CUS-06: Booking Tracker
    public function bookingsTracker(): void
    {
        $userId   = Auth::id();
        $bookings = $this->customerModel->getCustomerBookings($userId);

        $this->view('customer/tracker', [
            'pageTitle'    => 'My Bookings & Live Tracker',
            'bookings'     => $bookings,
            'flashMessage' => $_SESSION['flash_msg'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ], 'customer');
        unset($_SESSION['flash_msg']);
        unset($_SESSION['flash_error']);
    }

    public function bookingInvoice(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        $invoiceData = $bookingId > 0
            ? $this->customerModel->getCustomerInvoiceDetails(Auth::id(), $bookingId)
            : null;

        if (!$invoiceData) {
            http_response_code(404);
            echo 'Booking invoice not found.';
            return;
        }

        $this->view('admin/invoice_print', [
            'pageTitle' => 'Booking Cost Summary - ' . ($invoiceData['booking']['booking_code'] ?? 'Fixmate'),
            'booking' => $invoiceData['booking'],
            'items' => $invoiceData['items'] ?? []
        ], 'plain');
    }

    public function completionOtpApi(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        if ($bookingId <= 0) {
            $this->json(['success' => false, 'message' => 'A valid booking is required.'], 400);
        }
        $progress = $this->customerModel->getCustomerBookingProgress(Auth::id(), $bookingId);
        if (!$progress) {
            $this->json(['success' => false, 'message' => 'Booking not found.'], 404);
        }
        $otp = $this->customerModel->getLatestCustomerCompletionOtp(Auth::id(), $bookingId);
        if (!$otp || !preg_match('/ is (\d{4})\./', (string)($otp['message'] ?? ''), $match)) {
            $this->json(['success' => true, 'active' => false, 'progress' => $progress]);
        }
        $this->json(['success' => true, 'active' => true, 'code' => $match[1], 'expires_at' => $otp['expires_at'], 'progress' => $progress]);
    }

    public function rescheduleBooking(): void
    {
        $bid  = (int)$this->post('booking_id', 0);
        $date = (string)$this->post('scheduled_date', '');
        $slot = (string)$this->post('scheduled_time_slot', '');

        $this->customerModel->reschedule(Auth::id(), $bid, $date, $slot);
        $_SESSION['flash_msg'] = 'Booking appointment successfully rescheduled.';
        $this->redirect('/customer/my-bookings');
    }

    public function cancelBooking(): void
    {
        $bid    = (int)$this->post('booking_id', 0);
        $reason = (string)$this->post('reason', 'Customer request');

        $this->customerModel->cancelBooking(Auth::id(), $bid, $reason);
        $_SESSION['flash_msg'] = 'Booking cancelled successfully.';
        $this->redirect('/customer/my-bookings');
    }

    // CUS-07: Chat
    public function chatView(): void
    {
        $bid = (int)$this->get('booking_id', 0);
        $uid = Auth::id();
        $chatBooking = $bid > 0 ? $this->customerModel->getChatBookingDetails($bid) : null;
        if (!$chatBooking || (int)$chatBooking['customer_id'] !== $uid) {
            http_response_code(404);
            echo 'Booking chat not found.';
            return;
        }
        $messages = $this->customerModel->getChatMessages($bid, $uid);

        $this->view('customer/chat', [
            'pageTitle'  => 'Service Direct Chat',
            'bookingId'  => $bid,
            'messages'   => $messages,
            'chatBooking' => $chatBooking
        ], 'customer');
    }

    public function chatMessagesApi(): void
    {
        $bookingId = (int)$this->get('booking_id', 0);
        $booking = $this->customerModel->getChatBookingDetails($bookingId);
        if (!$booking || (int)$booking['customer_id'] !== Auth::id()) {
            $this->json(['success' => false, 'message' => 'Booking chat not found.'], 404);
        }
        $this->json(['success' => true, 'messages' => $this->customerModel->getChatMessages($bookingId, Auth::id())]);
    }

    public function sendChatMessage(): void
    {
        $bid = (int)$this->post('booking_id', 0);
        $msg = trim((string)$this->post('message', ''));
        $booking = $bid > 0 ? $this->customerModel->getChatBookingDetails($bid) : null;

        if ($booking && (int)$booking['customer_id'] === Auth::id()
            && !empty($booking['professional_user_id']) && $msg !== '' && mb_strlen($msg) <= 4000) {
            $this->customerModel->postChatMessage($bid, Auth::id(), (int)$booking['professional_user_id'], $msg);
        }
        $this->redirect('/customer/chat?booking_id=' . $bid);
    }

    // CUS-09: Review Submission
    public function submitReview(): void
    {
        $bid     = (int)$this->post('booking_id', 0);
        $proId   = (int)$this->post('pro_id', 0);
        $rating  = (int)$this->post('rating', 5);
        $comment = (string)$this->post('comment', '');

        if ($rating < 1 || $rating > 5 || $bid <= 0 || $proId <= 0) {
            $_SESSION['flash_error'] = 'Please choose a valid rating for a completed booking.';
            $this->redirect('/customer/my-bookings');
        }

        $saved = $this->customerModel->addReview($bid, Auth::id(), $proId, $rating, trim($comment));
        if ($saved) {
            $_SESSION['flash_msg'] = 'Thank you! Your rating and review have been published.';
        } else {
            $_SESSION['flash_error'] = 'This booking cannot be rated, or it already has a review.';
        }
        $this->redirect('/customer/my-bookings');
    }

    // CUS-10: Support & Disputes
    public function supportAndDisputes(): void
    {
        $userId   = Auth::id();
        $bookings = $this->customerModel->getCustomerBookings($userId);
        $disputes = $this->customerModel->getCustomerDisputes($userId);

        $this->view('customer/history_support', [
            'pageTitle'    => 'Customer Support & Resolution Center',
            'bookings'     => $bookings,
            'disputes'     => $disputes,
            'flashMessage' => $_SESSION['flash_msg'] ?? null
        ], 'customer');
        unset($_SESSION['flash_msg']);
    }

    public function raiseDispute(): void
    {
        $bid     = (int)$this->post('booking_id', 0);
        $reason  = (string)$this->post('reason', '');
        $details = (string)$this->post('details', '');

        $created = $this->customerModel->createDispute($bid, Auth::id(), trim($reason), trim($details));
        $_SESSION['flash_msg'] = $created
            ? 'Dispute raised. Our arbitration desk will review within 24 hours.'
            : 'This booking cannot be disputed, is already under review, or does not belong to your account.';
        $this->redirect('/customer/support');
    }
}