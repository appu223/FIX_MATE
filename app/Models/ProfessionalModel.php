<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class ProfessionalModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getProProfileByUserId(int $userId): ?array
    {
        $sql = "SELECT pp.*, u.name, u.email, u.phone 
                FROM professional_profiles pp
                JOIN users u ON pp.user_id = u.id
                WHERE pp.user_id = :uid";
        return $this->db->fetch($sql, ['uid' => $userId]);
    }

    // --- PRO-01: KPIs ---
    public function getWorkbenchMetrics(int $proId): array
    {
        $metrics = $this->db->fetch("SELECT 
                wallet_balance, rating_avg, rating_count,
                (SELECT COUNT(*) FROM bookings WHERE professional_id = :pid AND status = 'completed') AS completed_jobs,
                (SELECT COUNT(*) FROM bookings WHERE professional_id = :pid AND status IN ('assigned', 'accepted', 'in_progress')) AS active_jobs,
                (SELECT COUNT(*) FROM quotation_bids WHERE professional_id = :pid AND status = 'pending') AS pending_bids
            FROM professional_profiles WHERE id = :pid", ['pid' => $proId]);

        return $metrics ?: [];
    }

    public function getDashboardNotifications(int $userId): array
    {
        $unread = $this->db->fetch(
            "SELECT COUNT(*) AS unread_count FROM notifications WHERE user_id = :uid AND is_read = 0",
            ['uid' => $userId]
        );
        $notifications = $this->db->fetchAll(
            "SELECT id, title, message, link, is_read, created_at
             FROM notifications WHERE user_id = :uid
             ORDER BY id DESC LIMIT 8",
            ['uid' => $userId]
        );

        foreach ($notifications as $notification) {
            if (empty($notification['is_read'])) {
                $this->db->run(
                    "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid",
                    ['id' => (int)$notification['id'], 'uid' => $userId]
                );
            }
        }

        return ['items' => $notifications, 'unread_count' => (int)($unread['unread_count'] ?? 0)];
    }

    public function getActiveJobs(int $proId): array
    {
        $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone,
                       addr.address_line1, addr.address_line2, addr.city, addr.postal_code, addr.landmark,
                       (SELECT bp.review_status FROM booking_proofs bp
                        WHERE bp.booking_id = b.id AND bp.proof_type = 'after'
                        ORDER BY bp.id DESC LIMIT 1) AS after_proof_status,
                       (SELECT otp.expires_at FROM booking_completion_otps otp
                        WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_expires_at,
                       (SELECT otp.verified_at FROM booking_completion_otps otp
                        WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_verified_at,
                       (SELECT (otp.verified_at IS NULL AND otp.expires_at > NOW()) FROM booking_completion_otps otp
                        WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_active
                FROM bookings b
                JOIN users u ON b.customer_id = u.id
                JOIN user_addresses addr ON b.address_id = addr.id
                WHERE b.professional_id = :pid AND b.status IN ('assigned', 'accepted', 'in_progress')
                ORDER BY b.scheduled_date ASC, b.id ASC";
        return $this->db->fetchAll($sql, ['pid' => $proId]);
    }

    public function getFulfillmentUpdates(int $proId): array
    {
        $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone,
                       addr.address_line1, addr.address_line2, addr.city, addr.postal_code, addr.landmark,
                       (SELECT bp.review_status FROM booking_proofs bp
                        WHERE bp.booking_id = b.id AND bp.proof_type = 'after'
                        ORDER BY bp.id DESC LIMIT 1) AS after_proof_status,
                       (SELECT otp.expires_at FROM booking_completion_otps otp
                        WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_expires_at,
                       (SELECT otp.verified_at FROM booking_completion_otps otp
                        WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_verified_at,
                       (SELECT (otp.verified_at IS NULL AND otp.expires_at > NOW()) FROM booking_completion_otps otp
                        WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_active
                FROM bookings b
                JOIN users u ON b.customer_id = u.id
                JOIN user_addresses addr ON b.address_id = addr.id
                WHERE b.professional_id = :pid
                  AND (b.status IN ('assigned', 'accepted', 'in_progress')
                       OR (b.status = 'completed' AND b.updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)))
                ORDER BY (b.status = 'completed') ASC, b.scheduled_date ASC, b.id ASC
                LIMIT 60";
        return $this->db->fetchAll($sql, ['pid' => $proId]);
    }

    public function ownsBooking(int $bookingId, int $proId): bool
    {
        return (bool)$this->db->fetch(
            "SELECT id FROM bookings WHERE id = :bid AND professional_id = :pid LIMIT 1",
            ['bid' => $bookingId, 'pid' => $proId]
        );
    }

    // --- PRO-02: KYC Submission ---
    public function saveKycDocuments(int $proId, string $proofType, string $idFile, ?string $addrFile, ?string $licenseFile): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->run(
                "UPDATE professional_profiles
                 SET id_proof_type = :ptype, id_proof_file = :idfile,
                     address_proof_file = COALESCE(:addrfile, address_proof_file),
                     license_file = COALESCE(:licfile, license_file),
                     kyc_status = 'pending', kyc_rejected_reason = NULL
                 WHERE id = :pid",
                [
                    'ptype' => $proofType,
                    'idfile' => $idFile,
                    'addrfile' => $addrFile,
                    'licfile' => $licenseFile,
                    'pid' => $proId,
                ]
            );
            $this->db->run(
                "UPDATE users u JOIN professional_profiles pp ON pp.user_id = u.id
                 SET u.status = 'pending_verification' WHERE pp.id = :pid",
                ['pid' => $proId]
            );
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getProDocumentPath(int $proId, string $kind): ?string
    {
        $columns = [
            'id' => 'id_proof_file',
            'address' => 'address_proof_file',
            'license' => 'license_file',
        ];
        if (!isset($columns[$kind])) {
            return null;
        }
        $column = $columns[$kind];
        $row = $this->db->fetch("SELECT {$column} AS file_path FROM professional_profiles WHERE id = :pid", ['pid' => $proId]);
        return $row['file_path'] ?? null;
    }

    // --- PRO-03: Rates & Coverage ---
    public function getProRatesAndZones(int $proId): array
    {
        $zones = $this->db->fetchAll("SELECT sz.*, 
                    CASE WHEN psz.professional_id IS NOT NULL THEN 1 ELSE 0 END AS is_covered
                FROM service_zones sz
                LEFT JOIN professional_service_zones psz ON sz.id = psz.zone_id AND psz.professional_id = :pid", ['pid' => $proId]);

        $services = $this->db->fetchAll("SELECT s.*, c.name AS category_name, ps.custom_price,
                    CASE WHEN ps.professional_id IS NOT NULL THEN 1 ELSE 0 END AS is_offered
                FROM services s
                JOIN categories c ON s.category_id = c.id
                LEFT JOIN professional_services ps ON s.id = ps.service_id AND ps.professional_id = :pid", ['pid' => $proId]);

        return ['zones' => $zones, 'services' => $services];
    }

    public function syncRates(int $proId, array $servicePrices): bool
    {
        foreach ($servicePrices as $svcId => $price) {
            if ($price !== '' && (float)$price > 0) {
                $sql = "INSERT INTO professional_services (professional_id, service_id, custom_price, status) 
                        VALUES (:pid, :sid, :price, 'active')
                        ON DUPLICATE KEY UPDATE custom_price = :pupdate";
                $this->db->run($sql, ['pid' => $proId, 'sid' => (int)$svcId, 'price' => (float)$price, 'pupdate' => (float)$price]);
            }
        }
        return true;
    }

    // --- PRO-04: Schedules & Leaves ---
    public function getWeeklySchedule(int $proId): array
    {
        return $this->db->fetchAll("SELECT * FROM professional_schedules WHERE professional_id = :pid ORDER BY day_of_week ASC", ['pid' => $proId]);
    }

    public function saveShiftTimes(int $proId, array $shifts): bool
    {
        foreach ($shifts as $day => $data) {
            $sql = "INSERT INTO professional_schedules (professional_id, day_of_week, start_time, end_time, is_active)
                    VALUES (:pid, :day, :start, :end, :active)
                    ON DUPLICATE KEY UPDATE start_time = :supdate, end_time = :eupdate, is_active = :aupdate";
            $this->db->run($sql, [
                'pid'     => $proId,
                'day'     => $day,
                'start'   => $data['start'],
                'end'     => $data['end'],
                'active'  => !empty($data['is_active']) ? 1 : 0,
                'supdate' => $data['start'],
                'eupdate' => $data['end'],
                'aupdate' => !empty($data['is_active']) ? 1 : 0
            ]);
        }
        return true;
    }

    public function logLeave(int $proId, string $date, string $reason): bool
    {
        $sql = "INSERT INTO professional_leaves (professional_id, leave_date, reason, status) VALUES (:pid, :ldate, :reason, 'approved')";
        $this->db->run($sql, ['pid' => $proId, 'ldate' => $date, 'reason' => $reason]);
        return true;
    }

    public function getLeaves(int $proId): array
    {
        return $this->db->fetchAll("SELECT * FROM professional_leaves WHERE professional_id = :pid ORDER BY leave_date DESC", ['pid' => $proId]);
    }

    // --- PRO-05: Leads & Bidding ---
    public function getAvailableLeads(): array
    {
        $sql = "SELECT qr.*, c.name AS category_name, u.name AS customer_name,
                       (SELECT COUNT(*) FROM quotation_bids WHERE quotation_id = qr.id) AS bids_count
                FROM quotation_requests qr
                JOIN categories c ON qr.category_id = c.id
                JOIN users u ON qr.customer_id = u.id
                WHERE qr.status = 'open'
                ORDER BY qr.id DESC";
        return $this->db->fetchAll($sql);
    }

    public function submitQuotationBid(int $quotationId, int $proId, float $price, string $notes): bool
    {
        $sql = "INSERT INTO quotation_bids (quotation_id, professional_id, estimated_price, notes, status)
                VALUES (:qid, :pid, :price, :notes, 'pending')";
        $this->db->run($sql, ['qid' => $quotationId, 'pid' => $proId, 'price' => $price, 'notes' => $notes]);
        return true;
    }

    // --- PRO-06: Status Fulfillment ---
    public function setBookingStatus(int $bookingId, int $proId, string $status): bool
    {
        if (!in_array($status, ['accepted', 'in_progress'], true)) {
            return false;
        }
        $fromStatus = $status === 'accepted' ? 'assigned' : 'accepted';
        $this->db->run(
            "UPDATE bookings SET status = :status
             WHERE id = :bid AND professional_id = :pid AND status = :from_status",
            ['status' => $status, 'bid' => $bookingId, 'pid' => $proId, 'from_status' => $fromStatus]
        );
        return $this->db->fetch(
            "SELECT id FROM bookings WHERE id = :bid AND professional_id = :pid AND status = :status LIMIT 1",
            ['bid' => $bookingId, 'pid' => $proId, 'status' => $status]
        ) !== null;
    }

    // --- PRO-08: Proof of Work ---
    public function saveProof(int $bookingId, int $proId, string $type, string $filePath, string $desc): bool
    {
        $booking = $this->db->fetch(
            "SELECT id FROM bookings
             WHERE id = :bid AND professional_id = :pid AND status IN ('assigned', 'accepted', 'in_progress')",
            ['bid' => $bookingId, 'pid' => $proId]
        );
        if (!$booking || !in_array($type, ['before', 'after', 'material_receipt'], true)) {
            return false;
        }
        $sql = "INSERT INTO booking_proofs (booking_id, proof_type, file_path, description) VALUES (:bid, :type, :path, :desc)";
        $this->db->run($sql, ['bid' => $bookingId, 'type' => $type, 'path' => $filePath, 'desc' => $desc]);
        return true;
    }

    public function requestCompletionOtp(int $bookingId, int $proId): bool
    {
        $this->db->beginTransaction();
        try {
            $booking = $this->db->fetch(
                "SELECT id, customer_id, booking_code FROM bookings
                 WHERE id = :booking_id AND professional_id = :professional_id
                   AND status = 'in_progress' FOR UPDATE",
                ['booking_id' => $bookingId, 'professional_id' => $proId]
            );
            if (!$booking) {
                $this->db->rollBack();
                return false;
            }
            $afterProof = $this->db->fetch(
                "SELECT id, review_status FROM booking_proofs
                 WHERE booking_id = :booking_id AND proof_type = 'after'
                 ORDER BY id DESC LIMIT 1",
                ['booking_id' => $bookingId]
            );
            if (!$afterProof || $afterProof['review_status'] === 'rejected') {
                $this->db->rollBack();
                return false;
            }
            $recent = $this->db->fetch(
                "SELECT COUNT(*) AS total FROM booking_completion_otps
                 WHERE booking_id = :booking_id AND requested_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                ['booking_id' => $bookingId]
            );
            $lastSentWithinMinute = $this->db->fetch(
                "SELECT id FROM booking_completion_otps
                 WHERE booking_id = :booking_id AND requested_at >= DATE_SUB(NOW(), INTERVAL 60 SECOND)
                 ORDER BY id DESC LIMIT 1",
                ['booking_id' => $bookingId]
            );
            if ((int)($recent['total'] ?? 0) >= 5 || $lastSentWithinMinute) {
                $this->db->rollBack();
                return false;
            }

            $code = (string)random_int(1000, 9999);
            $this->db->run(
                "INSERT INTO booking_completion_otps (booking_id, otp_hash, expires_at)
                 VALUES (:booking_id, :otp_hash, DATE_ADD(NOW(), INTERVAL 10 MINUTE))",
                ['booking_id' => $bookingId, 'otp_hash' => password_hash($code, PASSWORD_DEFAULT)]
            );
            $this->db->run(
                "INSERT INTO notifications (user_id, title, message, link)
                 VALUES (:customer_id, 'Service completion code', :message, :link)",
                [
                    'customer_id' => $booking['customer_id'],
                    'message' => 'Your Fixmate completion code for booking ' . $booking['booking_code'] . ' is ' . $code . '. Share this 4-digit code with your technician only after the work is complete. It expires in 10 minutes.',
                    'link' => '/customer/my-bookings?booking_id=' . $bookingId
                ]
            );
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function verifyCompletionOtp(int $bookingId, int $proId, string $code, bool $codReceived): array
    {
        if (!preg_match('/^\d{4}$/', $code)) {
            return ['success' => false, 'message' => 'Enter the four-digit code shown in the customer booking panel.'];
        }
        $this->db->beginTransaction();
        try {
            $booking = $this->db->fetch(
                "SELECT id, booking_code, customer_id, professional_id, status, payment_method, payment_status
                 FROM bookings WHERE id = :booking_id AND professional_id = :professional_id FOR UPDATE",
                ['booking_id' => $bookingId, 'professional_id' => $proId]
            );
            if (!$booking || $booking['status'] !== 'in_progress') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'This job is not available for completion confirmation.'];
            }
            $otp = $this->db->fetch(
                "SELECT *, (expires_at <= NOW()) AS is_expired FROM booking_completion_otps WHERE booking_id = :booking_id
                 ORDER BY id DESC LIMIT 1 FOR UPDATE",
                ['booking_id' => $bookingId]
            );
            if (!$otp) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No customer code was issued for this booking. Send a new code and try again.'];
            }
            if ($otp['verified_at'] !== null) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'This customer code was already used. Send a fresh code only if the customer still needs to confirm.'];
            }
            if ((int)$otp['is_expired'] === 1) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'This customer code expired. Send a fresh code and ask the customer to read the latest one.'];
            }
            if ((int)$otp['attempts'] >= 5) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Too many incorrect attempts. Request a fresh customer code later.'];
            }
            if (!password_verify($code, (string)$otp['otp_hash'])) {
                $this->db->run("UPDATE booking_completion_otps SET attempts = attempts + 1 WHERE id = :id", ['id' => $otp['id']]);
                $this->db->commit();
                return ['success' => false, 'message' => 'That code is incorrect. Check the customer booking panel and try again.'];
            }
            if ($booking['payment_method'] === 'cod' && $booking['payment_status'] !== 'paid' && !$codReceived) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Confirm that the cash payment was received before completing this COD job.'];
            }

            $this->db->run(
                "UPDATE booking_completion_otps SET verified_at = CURRENT_TIMESTAMP, cod_received = :cod_received
                 WHERE id = :id AND verified_at IS NULL",
                ['cod_received' => $booking['payment_method'] === 'cod' && ($codReceived || $booking['payment_status'] === 'paid') ? 1 : 0, 'id' => $otp['id']]
            );
            $completed = $this->completeBookingIfReady($booking);
            $afterProofApproved = $this->db->fetch(
                                "SELECT proof.id FROM booking_proofs proof
                                 WHERE proof.booking_id = :booking_id AND proof.proof_type = 'after'
                                     AND proof.id = (SELECT MAX(latest.id) FROM booking_proofs latest
                                                                     WHERE latest.booking_id = proof.booking_id AND latest.proof_type = 'after')
                                       AND proof.review_status = 'approved' LIMIT 1",
                ['booking_id' => $bookingId]
            );
            $this->db->commit();
            $waitingForPayment = $booking['payment_method'] === 'online'
                && $booking['payment_status'] !== 'paid'
                && $afterProofApproved !== null;
            return [
                'success' => true,
                'completed' => $completed,
                'message' => $completed
                    ? 'Customer confirmed. Job completed and technician earnings updated.'
                    : ($waitingForPayment
                        ? 'Customer confirmed and proof approved. Completion is waiting for online payment confirmation.'
                        : 'Customer confirmed. Completion is waiting for administrator approval of the after-work proof.')
            ];
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getProofPathForProfessional(int $proofId, int $proId): ?string
    {
        $row = $this->db->fetch(
            "SELECT bp.file_path FROM booking_proofs bp
             JOIN bookings b ON b.id = bp.booking_id
             WHERE bp.id = :proof_id AND b.professional_id = :pro_id",
            ['proof_id' => $proofId, 'pro_id' => $proId]
        );
        return $row['file_path'] ?? null;
    }

    public function reviewWorkProof(int $proofId, int $adminId, string $decision, bool $confirmCodPayment = false): bool
    {
        if ($proofId <= 0 || $adminId <= 0 || !in_array($decision, ['approved', 'rejected'], true)) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $proof = $this->db->fetch(
                "SELECT bp.id, bp.proof_type, bp.review_status, b.id AS booking_id, b.booking_code,
                        b.professional_id, b.status AS booking_status, b.payment_status,
                        b.payment_method, b.customer_id, b.total_amount, b.pro_earning, b.pro_earning_credited, pp.user_id
                 FROM booking_proofs bp
                 JOIN bookings b ON b.id = bp.booking_id
                 JOIN professional_profiles pp ON pp.id = b.professional_id
                 WHERE bp.id = :proof_id FOR UPDATE",
                ['proof_id' => $proofId]
            );
            if (!$proof || $proof['review_status'] !== 'pending') {
                $this->db->rollBack();
                return false;
            }

            if (in_array($proof['booking_status'], ['cancelled', 'disputed'], true)) {
                $this->db->rollBack();
                return false;
            }

            $this->db->run(
                "UPDATE booking_proofs SET review_status = :status, reviewed_by = :admin_id,
                    reviewed_at = CURRENT_TIMESTAMP WHERE id = :proof_id",
                ['status' => $decision, 'admin_id' => $adminId, 'proof_id' => $proofId]
            );

            if ($decision === 'approved' && $proof['proof_type'] === 'after') {
                $this->completeBookingIfReady(['id' => (int)$proof['booking_id']]);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    private function completeBookingIfReady(array $booking): bool
    {
        $ready = $this->db->fetch(
            "SELECT b.id, b.booking_code, b.customer_id, b.professional_id, b.payment_method,
                    b.payment_status, b.total_amount, b.pro_earning, b.pro_earning_credited,
                    pp.user_id AS professional_user_id
             FROM bookings b
             JOIN professional_profiles pp ON pp.id = b.professional_id
             WHERE b.id = :booking_id AND b.status = 'in_progress'
               AND EXISTS (SELECT 1 FROM booking_completion_otps otp
                           WHERE otp.booking_id = b.id AND otp.verified_at IS NOT NULL)
               AND EXISTS (SELECT 1 FROM booking_proofs proof
                                                     WHERE proof.booking_id = b.id AND proof.proof_type = 'after'
                                                         AND proof.id = (SELECT MAX(latest.id) FROM booking_proofs latest
                                                                                         WHERE latest.booking_id = b.id AND latest.proof_type = 'after')
                                                         AND proof.review_status = 'approved')
             LIMIT 1 FOR UPDATE",
            ['booking_id' => (int)$booking['id']]
        );
        if (!$ready) {
            return false;
        }
        if ($ready['payment_method'] === 'online' && $ready['payment_status'] !== 'paid') {
            return false;
        }
        if ($ready['payment_method'] === 'cod') {
            $codOtp = $this->db->fetch(
                "SELECT id FROM booking_completion_otps
                 WHERE booking_id = :booking_id AND verified_at IS NOT NULL AND cod_received = 1
                 ORDER BY id DESC LIMIT 1",
                ['booking_id' => $ready['id']]
            );
            if (!$codOtp) {
                return false;
            }
            if ($ready['payment_status'] !== 'paid') {
                $this->db->run("UPDATE bookings SET payment_status = 'paid' WHERE id = :booking_id AND payment_status = 'pending'", ['booking_id' => $ready['id']]);
                $exists = $this->db->fetch("SELECT id FROM transactions WHERE booking_id = :booking_id AND type = 'payment' AND payment_gateway = 'Cash on Delivery' LIMIT 1", ['booking_id' => $ready['id']]);
                if (!$exists) {
                    $this->db->run(
                        "INSERT INTO transactions (booking_id, user_id, txn_reference, amount, type, payment_gateway, status)
                         VALUES (:booking_id, :customer_id, :reference, :amount, 'payment', 'Cash on Delivery', 'success')",
                        ['booking_id' => $ready['id'], 'customer_id' => $ready['customer_id'], 'reference' => 'COD-' . $ready['booking_code'], 'amount' => $ready['total_amount']]
                    );
                }
            }
        }

        if ((int)$ready['pro_earning_credited'] === 1) {
            $closed = $this->db->run(
                "UPDATE bookings SET status = 'completed'
                 WHERE id = :booking_id AND status = 'in_progress' AND pro_earning_credited = 1",
                ['booking_id' => $ready['id']]
            );
            return $closed->rowCount() === 1;
        }

        if ((int)$ready['pro_earning_credited'] === 0) {
            $updated = $this->db->run(
                "UPDATE bookings SET status = 'completed', pro_earning_credited = 1
                 WHERE id = :booking_id AND status = 'in_progress' AND pro_earning_credited = 0",
                ['booking_id' => $ready['id']]
            );
            if ($updated->rowCount() !== 1) {
                return false;
            }
            $this->db->run(
                "UPDATE professional_profiles SET wallet_balance = wallet_balance + :earning WHERE id = :pro_id",
                ['earning' => $ready['pro_earning'], 'pro_id' => $ready['professional_id']]
            );
            $this->db->run(
                "INSERT INTO notifications (user_id, title, message, link)
                 VALUES (:uid, 'Work approved and earnings credited', :message, '/pro/earnings')",
                ['uid' => $ready['professional_user_id'], 'message' => 'Customer confirmed completion for booking ' . $ready['booking_code'] . '. ₹' . number_format((float)$ready['pro_earning'], 2) . ' has been added to your Fixmate wallet.']
            );
            return true;
        }
        return false;
    }

    public function completeBookingAfterPayment(int $bookingId): bool
    {
        $this->db->beginTransaction();
        try {
            $completed = $this->completeBookingIfReady(['id' => $bookingId]);
            $this->db->commit();
            return $completed;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getBookingProofs(int $bookingId): array
    {
        return $this->db->fetchAll("SELECT * FROM booking_proofs WHERE booking_id = :bid", ['bid' => $bookingId]);
    }

    // --- PRO-09: Earnings & Payout Requests ---
    public function getEarningsLedger(int $proId): array
    {
        $jobs = $this->db->fetchAll("SELECT id, booking_code, scheduled_date, total_amount, commission_amount, pro_earning, payment_status, pro_earning_credited, created_at
                                    FROM bookings 
                                    WHERE professional_id = :pid AND status = 'completed' 
                                    ORDER BY id DESC", ['pid' => $proId]);

        $payouts = $this->db->fetchAll("SELECT * FROM payout_requests WHERE professional_id = :pid ORDER BY id DESC", ['pid' => $proId]);

        return ['jobs' => $jobs, 'payouts' => $payouts];
    }

    public function requestPayout(int $proId, float $amount): bool
    {
        $sql = "INSERT INTO payout_requests (professional_id, amount, status) VALUES (:pid, :amount, 'pending')";
        $this->db->run($sql, ['pid' => $proId, 'amount' => $amount]);
        return true;
    }

    // --- PRO-10: Reviews & Grievance Responses ---
    public function getProReviews(int $proId): array
    {
        $sql = "SELECT r.*, b.booking_code, u.name AS customer_name
                FROM reviews r
                JOIN bookings b ON r.booking_id = b.id
                JOIN users u ON r.customer_id = u.id
                WHERE r.professional_id = :pid ORDER BY r.id DESC";
        return $this->db->fetchAll($sql, ['pid' => $proId]);
    }
}