<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CustomerModel;
use App\Models\ProfessionalModel;

class PaymentWebhookController extends Controller
{
    public function razorpay(): void
    {
        $webhookSecret = getenv('RAZORPAY_WEBHOOK_SECRET') ?: '';
        if ($webhookSecret === '') {
            $this->json(['success' => false, 'message' => 'Webhook is not configured.'], 503);
        }

        $body = (string)file_get_contents('php://input');
        $signature = (string)($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');
        if ($signature === '' || !hash_equals(hash_hmac('sha256', $body, $webhookSecret), $signature)) {
            $this->json(['success' => false, 'message' => 'Invalid webhook signature.'], 403);
        }

        $event = json_decode($body, true);
        if (!is_array($event) || ($event['event'] ?? '') !== 'payment.captured') {
            $this->json(['success' => true, 'ignored' => true]);
        }
        $payment = $event['payload']['payment']['entity'] ?? [];
        $orderId = (string)($payment['order_id'] ?? '');
        $paymentId = (string)($payment['id'] ?? '');
        $amount = (int)($payment['amount'] ?? 0);
        if ($orderId === '' || $paymentId === '' || $amount <= 0 || ($payment['currency'] ?? '') !== 'INR') {
            $this->json(['success' => false, 'message' => 'Captured payment payload is incomplete.'], 422);
        }

        $customerModel = new CustomerModel();
        $booking = $customerModel->confirmRazorpayWebhookPayment($orderId, $paymentId, $amount);
        if (!$booking) {
            $this->json(['success' => false, 'message' => 'Captured payment does not match a pending Fixmate booking.'], 409);
        }
        (new ProfessionalModel())->completeBookingAfterPayment((int)$booking['booking_id']);
        $this->json(['success' => true]);
    }
}
