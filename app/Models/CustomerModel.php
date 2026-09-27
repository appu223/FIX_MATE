<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class CustomerModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // --- Public: Customer Self Registration ---
    /**
     * Creates a customer account. Returns ['error' => string] on failure,
     * or ['user' => array, 'address_id' => int] on success.
     */
    public function registerCustomer(string $name, string $email, string $phone, string $password, string $city, string $address): array
    {
        // Uniqueness guards (users has UNIQUE keys on email and phone)
        if ($this->db->fetch("SELECT id FROM users WHERE email = :email LIMIT 1", ['email' => $email])) {
            return ['error' => 'An account with this email already exists. Please sign in instead.'];
        }
        if ($this->db->fetch("SELECT id FROM users WHERE phone = :phone LIMIT 1", ['phone' => $phone])) {
            return ['error' => 'An account with this mobile number already exists. Please sign in instead.'];
        }

        $this->db->beginTransaction();
        try {
            $this->db->run(
                "INSERT INTO users (role, name, email, phone, password_hash, status)
                 VALUES ('customer', :name, :email, :phone, :hash, 'active')",
                [
                    'name'  => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'hash'  => password_hash($password, PASSWORD_DEFAULT)
                ]
            );
            $userId = (int)$this->db->lastInsertId();

            // Seed the default address so checkout has a destination immediately.
            $this->db->run(
                "INSERT INTO user_addresses (user_id, label, address_line1, city, state, postal_code, is_default)
                 VALUES (:uid, 'Home', :a1, :city, :state, '560034', 1)",
                [
                    'uid'   => $userId,
                    'a1'    => $address !== '' ? $address : 'Primary Address',
                    'city'  => $city,
                    'state' => 'Karnataka'
                ]
            );
            $addressId = (int)$this->db->lastInsertId();

            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            error_log((string)$exception);
            return ['error' => 'We could not create your account right now. Please try again.'];
        }

        return [
            'user' => ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => 'customer'],
            'address_id' => $addressId
        ];
    }

    // --- CUS-01: Discovery ---
    public function getActiveCategories(): array
    {
        return $this->db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
    }

    public function getPopularServices(int $limit = 8): array
    {
        $sql = "SELECT s.*, c.name AS category_name, c.icon AS category_icon
                FROM services s
                JOIN categories c ON s.category_id = c.id
                WHERE s.status = 'active' AND c.status = 'active'
                ORDER BY s.is_popular DESC, s.id ASC LIMIT :limit";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchCatalog(string $query, ?int $categoryId = null): array
    {
        $sql = "SELECT s.*, c.name AS category_name, c.icon AS category_icon
                FROM services s
                JOIN categories c ON s.category_id = c.id
                WHERE s.status = 'active' AND c.status = 'active'";
        $params = [];
        if (!empty($query)) {
            $sql .= " AND (s.name LIKE :q OR s.description LIKE :q OR c.name LIKE :q)";
            $params['q'] = "%{$query}%";
        }
        if ($categoryId && $categoryId > 0) {
            $sql .= " AND s.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }
        $sql .= " ORDER BY s.is_popular DESC, s.name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    // --- CUS-02: Profile & Address Book ---
    public function getCustomerProfile(int $userId): ?array
    {
        return $this->db->fetch("SELECT id, name, email, phone, status, created_at FROM users WHERE id = :id", ['id' => $userId]);
    }

    public function updateCustomerProfile(int $userId, string $name, string $phone): bool
    {
        $sql = "UPDATE users SET name = :name, phone = :phone WHERE id = :id";
        $this->db->run($sql, ['name' => $name, 'phone' => $phone, 'id' => $userId]);
        return true;
    }

    public function getCustomerAddresses(int $userId): array
    {
        return $this->db->fetchAll("SELECT * FROM user_addresses WHERE user_id = :id ORDER BY is_default DESC, id DESC", ['id' => $userId]);
    }

    public function saveCustomerAddress(int $userId, array $data): bool
    {
        if (!empty($data['is_default'])) {
            $this->db->run("UPDATE user_addresses SET is_default = 0 WHERE user_id = :uid", ['uid' => $userId]);
        }

        if (!empty($data['id'])) {
            $sql = "UPDATE user_addresses SET label = :label, address_line1 = :a1, address_line2 = :a2, 
                    landmark = :landmark, city = :city, state = :state, postal_code = :zip, is_default = :def 
                    WHERE id = :id AND user_id = :uid";
            $this->db->run($sql, [
                'label'    => $data['label'],
                'a1'       => $data['address_line1'],
                'a2'       => $data['address_line2'] ?? null,
                'landmark' => $data['landmark'] ?? null,
                'city'     => $data['city'],
                'state'    => $data['state'],
                'zip'      => $data['postal_code'],
                'def'      => !empty($data['is_default']) ? 1 : 0,
                'id'       => $data['id'],
                'uid'      => $userId
            ]);
            return true;
        }

        $sql = "INSERT INTO user_addresses (user_id, label, address_line1, address_line2, landmark, city, state, postal_code, is_default)
                VALUES (:uid, :label, :a1, :a2, :landmark, :city, :state, :zip, :def)";
        $this->db->run($sql, [
            'uid'      => $userId,
            'label'    => $data['label'],
            'a1'       => $data['address_line1'],
            'a2'       => $data['address_line2'] ?? null,
            'landmark' => $data['landmark'] ?? null,
            'city'     => $data['city'],
            'state'    => $data['state'],
            'zip'      => $data['postal_code'],
            'def'      => !empty($data['is_default']) ? 1 : 0
        ]);
        return true;
    }

    public function deleteAddress(int $userId, int $addressId): bool
    {
        $this->db->run("DELETE FROM user_addresses WHERE id = :id AND user_id = :uid", ['id' => $addressId, 'uid' => $userId]);
        return true;
    }

    // --- CUS-03: Professional Search & Profiles ---
    public function getActiveProsDirectory(?string $search = null, ?int $catId = null): array
    {
        $sql = "SELECT pp.id AS pro_id, u.name, u.phone, pp.bio, pp.experience_years, pp.rating_avg, pp.rating_count
                FROM professional_profiles pp
                JOIN users u ON pp.user_id = u.id
                                WHERE pp.kyc_status = 'verified' AND u.status = 'active'
                                    AND EXISTS (SELECT 1 FROM professional_services ps
                                                            JOIN services s ON s.id = ps.service_id AND s.status = 'active'
                                                            WHERE ps.professional_id = pp.id AND ps.status = 'active')";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :s OR pp.bio LIKE :s)";
            $params['s'] = "%{$search}%";
        }
        $sql .= " ORDER BY pp.rating_avg DESC, pp.rating_count DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getActiveProfessionalForBooking(int $professionalId): ?array
    {
        return $this->db->fetch(
                "SELECT pp.id AS pro_id, u.name, pp.bio, pp.experience_years, pp.rating_avg,
                    pp.rating_count, pp.commission_rate
             FROM professional_profiles pp
             JOIN users u ON u.id = pp.user_id
             WHERE pp.id = :id AND pp.kyc_status = 'verified' AND u.status = 'active'
               AND EXISTS (SELECT 1 FROM professional_services ps
                           JOIN services s ON s.id = ps.service_id AND s.status = 'active'
                           WHERE ps.professional_id = pp.id AND ps.status = 'active')
             LIMIT 1",
            ['id' => $professionalId]
        );
    }

    public function getProfessionalServices(int $professionalId, string $search = '', int $categoryId = 0): array
    {
        $sql = "SELECT s.id, s.name, s.description, s.duration_minutes,
                       COALESCE(ps.custom_price, s.base_price) AS base_price,
                       c.name AS category_name, c.icon AS category_icon
                FROM professional_services ps
                JOIN services s ON s.id = ps.service_id AND s.status = 'active'
                JOIN categories c ON c.id = s.category_id AND c.status = 'active'
                WHERE ps.professional_id = :professional_id AND ps.status = 'active'";
        $params = ['professional_id' => $professionalId];
        if ($search !== '') {
            $sql .= " AND (s.name LIKE :search OR s.description LIKE :search OR c.name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        if ($categoryId > 0) {
            $sql .= " AND s.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        $sql .= " ORDER BY s.is_popular DESC, s.name ASC";
        return $this->db->fetchAll($sql, $params);
    }

    // --- CUS-04 & CUS-08: Cart & Checkout Engine ---
    public function validateCoupon(string $code, float $cartTotal): ?array
    {
        $coupon = $this->db->fetch("SELECT * FROM coupons WHERE code = :code AND status = 'active' AND NOW() BETWEEN valid_from AND valid_until AND used_count < usage_limit", ['code' => strtoupper(trim($code))]);
        if (!$coupon) {
            return null;
        }
        if ($cartTotal < (float)$coupon['min_booking_value']) {
            return ['error' => "Minimum order of ₹{$coupon['min_booking_value']} required for this coupon."];
        }

        $discount = 0.0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = round(($cartTotal * ((float)$coupon['discount_value'] / 100)), 2);
            if (!empty($coupon['max_discount']) && $discount > (float)$coupon['max_discount']) {
                $discount = (float)$coupon['max_discount'];
            }
        } else {
            $discount = (float)$coupon['discount_value'];
        }
        return ['coupon' => $coupon, 'discount_amount' => $discount];
    }

    public function createCustomerBooking(int $userId, array $data, array $items): string
    {
        $professionalId = (int)($data['professional_id'] ?? 0);
        $commissionAmount = 0.0;
        $professionalEarning = 0.0;
        $selectedProfessional = null;
        $paymentMethod = (string)($data['payment_method'] ?? '');
        if (!in_array($paymentMethod, ['online', 'cod'], true)) {
            throw new \InvalidArgumentException('Choose online payment or cash on service completion.');
        }
        if (empty($items) || count($items) > 30) {
            throw new \InvalidArgumentException('Your cart must contain between 1 and 30 service items.');
        }
        $address = $this->db->fetch(
            "SELECT postal_code FROM user_addresses WHERE id = :address_id AND user_id = :user_id LIMIT 1",
            ['address_id' => (int)($data['address_id'] ?? 0), 'user_id' => $userId]
        );
        if (!$address) {
            throw new \InvalidArgumentException('Please select one of your saved service addresses.');
        }
        $zone = $this->db->fetch(
            "SELECT id FROM service_zones
             WHERE status = 'active' AND JSON_CONTAINS(postal_codes_json, JSON_QUOTE(:postal_code))
             ORDER BY id ASC LIMIT 1",
            ['postal_code' => (string)$address['postal_code']]
        );
        if (!$zone) {
            throw new \InvalidArgumentException('Fixmate does not currently serve the selected address. Please choose another address.');
        }

        $requestedQuantities = [];
        foreach ($items as $item) {
            $serviceId = (int)($item['id'] ?? 0);
            $quantity = (int)($item['quantity'] ?? 0);
            if ($serviceId <= 0 || $quantity < 1 || $quantity > 50) {
                throw new \InvalidArgumentException('The cart contains an invalid service or quantity.');
            }
            $requestedQuantities[$serviceId] = ($requestedQuantities[$serviceId] ?? 0) + $quantity;
            if ($requestedQuantities[$serviceId] > 50) {
                throw new \InvalidArgumentException('Each service quantity is limited to 50.');
            }
        }
        $serviceIds = array_keys($requestedQuantities);
        $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
        if ($professionalId > 0) {
            $selectedProfessional = $this->getActiveProfessionalForBooking($professionalId);
            if (!$selectedProfessional) {
                throw new \InvalidArgumentException('The selected technician is no longer available. Please choose another professional.');
            }
            $professionalCoversZone = $this->db->fetch(
                "SELECT id FROM professional_service_zones WHERE professional_id = :professional_id AND zone_id = :zone_id LIMIT 1",
                ['professional_id' => $professionalId, 'zone_id' => (int)$zone['id']]
            );
            if (!$professionalCoversZone) {
                throw new \InvalidArgumentException('The selected technician does not cover this address. Choose another address or technician.');
            }
            $serviceRows = $this->db->fetchAll(
                "SELECT s.id, s.name, COALESCE(ps.custom_price, s.base_price) AS unit_price
                 FROM professional_services ps
                 JOIN services s ON s.id = ps.service_id AND s.status = 'active'
                 JOIN categories c ON c.id = s.category_id AND c.status = 'active'
                 WHERE ps.professional_id = ? AND ps.status = 'active' AND s.id IN ($placeholders)",
                array_merge([$professionalId], $serviceIds)
            );
        } else {
            $serviceRows = $this->db->fetchAll(
                "SELECT s.id, s.name, s.base_price AS unit_price
                 FROM services s JOIN categories c ON c.id = s.category_id AND c.status = 'active'
                 WHERE s.status = 'active' AND s.id IN ($placeholders)",
                $serviceIds
            );
        }
        $servicesById = [];
        foreach ($serviceRows as $serviceRow) {
            $servicesById[(int)$serviceRow['id']] = $serviceRow;
        }
        if (count($servicesById) !== count($serviceIds)) {
            throw new \InvalidArgumentException($professionalId > 0
                ? 'One or more cart services are not offered by the selected technician. Please review your cart.'
                : 'One or more cart services are no longer available. Please refresh your cart.');
        }
        $pricedItems = [];
        $subtotal = 0.0;
        foreach ($requestedQuantities as $serviceId => $quantity) {
            $service = $servicesById[$serviceId];
            $unitPrice = (float)$service['unit_price'];
            $lineTotal = round($unitPrice * $quantity, 2);
            $subtotal += $lineTotal;
            $pricedItems[] = ['id' => $serviceId, 'name' => $service['name'], 'base_price' => $unitPrice, 'quantity' => $quantity, 'total_price' => $lineTotal];
        }

        $this->db->beginTransaction();
        try {
            $couponId = (int)($data['coupon_id'] ?? 0);
            $discount = 0.0;
            if ($couponId > 0) {
                $coupon = $this->db->fetch(
                    "SELECT * FROM coupons WHERE id = :id AND status = 'active'
                     AND NOW() BETWEEN valid_from AND valid_until AND used_count < usage_limit FOR UPDATE",
                    ['id' => $couponId]
                );
                if (!$coupon) {
                    throw new \InvalidArgumentException('That coupon is expired, inactive, or has reached its usage limit.');
                }
                if ($subtotal < (float)$coupon['min_booking_value']) {
                    throw new \InvalidArgumentException('This coupon requires a minimum service subtotal of ₹' . number_format((float)$coupon['min_booking_value'], 2) . '.');
                }
                $discount = $coupon['discount_type'] === 'percentage'
                    ? round($subtotal * (float)$coupon['discount_value'] / 100, 2)
                    : (float)$coupon['discount_value'];
                if ($coupon['max_discount'] !== null) {
                    $discount = min($discount, (float)$coupon['max_discount']);
                }
                $discount = min($discount, $subtotal);
            }
            $tax = round(max(0, $subtotal - $discount) * 0.18);
            $total = max(0, $subtotal - $discount + $tax);
            if ($selectedProfessional) {
                $commissionAmount = round($subtotal * ((float)$selectedProfessional['commission_rate'] / 100), 2);
                $professionalEarning = $total - $commissionAmount;
            }
            $zoneId = (int)$zone['id'];
            $code = 'FX-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
            $sql = "INSERT INTO bookings (booking_code, customer_id, professional_id, address_id, zone_id, coupon_id, scheduled_date,
                             scheduled_time_slot, status, notes, subtotal, discount_amount, surge_amount,
                             tax_amount, total_amount, commission_amount, pro_earning, payment_status, payment_method)
                    VALUES (:code, :uid, :professional_id, :aid, :zid, :cid, :sdate, :slot, 'pending', :notes, :subtotal, :discount,
                        0, :tax, :total, :commission, :pro_earning, 'pending', :pmethod)";
            
            $this->db->run($sql, [
                'code'      => $code,
                'uid'       => $userId,
                'professional_id' => $professionalId > 0 ? $professionalId : null,
                'aid'       => $data['address_id'],
                'zid'       => $zoneId,
                'cid'       => $couponId > 0 ? $couponId : null,
                'sdate'     => $data['scheduled_date'],
                'slot'      => $data['scheduled_time_slot'],
                'notes'     => $data['notes'] ?? null,
                'subtotal'  => $subtotal,
                'discount'  => $discount,
                'tax'       => $tax,
                'total'     => $total,
                'commission' => $commissionAmount,
                'pro_earning' => $professionalEarning,
                'pmethod'   => $paymentMethod
            ]);

            $bookingId = (int)$this->db->lastInsertId();
            if ($couponId > 0) {
                $this->db->run("UPDATE coupons SET used_count = used_count + 1 WHERE id = :coupon_id", ['coupon_id' => $couponId]);
            }

            // Insert Items
            $stmtItem = $this->db->getConnection()->prepare("INSERT INTO booking_items (booking_id, service_id, service_name, unit_price, quantity, total_price) VALUES (:bid, :sid, :sname, :uprice, :qty, :tprice)");
            foreach ($pricedItems as $item) {
                $stmtItem->execute([
                    'bid'    => $bookingId,
                    'sid'    => $item['id'],
                    'sname'  => $item['name'],
                    'uprice' => $item['base_price'],
                    'qty'    => $item['quantity'],
                    'tprice' => (float)$item['base_price'] * (int)$item['quantity']
                ]);
            }

            // Online charges remain pending until Razorpay's signed callback is verified server-side.
            if ($paymentMethod === 'online') {
                $this->db->run("INSERT INTO transactions (booking_id, user_id, txn_reference, amount, type, payment_gateway, status)
                                VALUES (:bid, :uid, :ref, :amount, 'payment', 'Razorpay', 'pending')", [
                    'bid'    => $bookingId,
                    'uid'    => $userId,
                    'ref'    => 'PENDING-' . $code,
                    'amount' => $total
                ]);
            }

            $this->db->commit();
            return $code;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getPendingOnlineBooking(int $userId, string $bookingCode): ?array
    {
        return $this->db->fetch(
            "SELECT b.id, b.booking_code, b.total_amount, t.txn_reference, t.gateway_order_id,
                    t.status AS transaction_status
             FROM bookings b JOIN transactions t ON t.booking_id = b.id AND t.type = 'payment'
             WHERE b.booking_code = :code AND b.customer_id = :user_id
               AND b.payment_method = 'online' AND b.payment_status = 'pending'
               AND b.status NOT IN ('cancelled', 'disputed') AND t.status = 'pending'
             ORDER BY t.id DESC LIMIT 1",
            ['code' => $bookingCode, 'user_id' => $userId]
        );
    }

    public function saveRazorpayOrder(int $userId, int $bookingId, string $orderId): bool
    {
        $this->db->run(
            "UPDATE transactions t JOIN bookings b ON b.id = t.booking_id
             SET t.gateway_order_id = :order_id
             WHERE b.id = :booking_id AND b.customer_id = :user_id AND b.payment_method = 'online'
               AND b.payment_status = 'pending' AND t.type = 'payment' AND t.status = 'pending'",
            ['order_id' => $orderId, 'booking_id' => $bookingId, 'user_id' => $userId]
        );
        return true;
    }

    public function confirmRazorpayPayment(int $userId, string $orderId, string $paymentId): ?array
    {
        $this->db->beginTransaction();
        try {
            $transaction = $this->db->fetch(
                                "SELECT t.id AS transaction_id, t.status AS transaction_status,
                        b.id AS booking_id, b.booking_code, b.total_amount, b.payment_status
                 FROM transactions t JOIN bookings b ON b.id = t.booking_id
                                 WHERE t.gateway_order_id = :order_id AND t.user_id = :user_id
                   AND t.type = 'payment' AND t.payment_gateway = 'Razorpay' FOR UPDATE",
                ['order_id' => $orderId, 'user_id' => $userId]
            );
            if (!$transaction) {
                $this->db->rollBack();
                return null;
            }
            if ($transaction['payment_status'] === 'paid' && $transaction['transaction_status'] === 'success') {
                $this->db->commit();
                return ['booking_code' => $transaction['booking_code'], 'already_paid' => true];
            }
            $this->db->run(
                "UPDATE transactions SET txn_reference = :payment_id, gateway_payment_id = :payment_id, status = 'success'
                 WHERE id = :transaction_id AND status = 'pending'",
                ['payment_id' => $paymentId, 'transaction_id' => $transaction['transaction_id']]
            );
            $this->db->run(
                "UPDATE bookings SET payment_status = 'paid' WHERE id = :booking_id AND payment_status = 'pending'",
                ['booking_id' => $transaction['booking_id']]
            );
            $this->db->commit();
            return ['booking_code' => $transaction['booking_code'], 'already_paid' => false];
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getPendingOnlineBookingByOrderId(int $userId, string $orderId): ?array
    {
        return $this->db->fetch(
            "SELECT b.id, b.booking_code, b.total_amount, b.payment_status, t.gateway_order_id
             FROM bookings b JOIN transactions t ON t.booking_id = b.id AND t.type = 'payment'
             WHERE t.gateway_order_id = :order_id AND t.user_id = :user_id
               AND b.payment_method = 'online'
             LIMIT 1",
            ['order_id' => $orderId, 'user_id' => $userId]
        );
    }

    public function confirmRazorpayWebhookPayment(string $orderId, string $paymentId, int $amountPaise): ?array
    {
        $this->db->beginTransaction();
        try {
            $transaction = $this->db->fetch(
                "SELECT t.id AS transaction_id, t.status AS transaction_status, t.amount,
                        b.id AS booking_id, b.booking_code, b.payment_status
                 FROM transactions t JOIN bookings b ON b.id = t.booking_id
                 WHERE t.gateway_order_id = :order_id AND t.payment_gateway = 'Razorpay'
                   AND t.type = 'payment' AND b.payment_method = 'online' FOR UPDATE",
                ['order_id' => $orderId]
            );
            if (!$transaction || (int)round((float)$transaction['amount'] * 100) !== $amountPaise) {
                $this->db->rollBack();
                return null;
            }
            if ($transaction['payment_status'] !== 'paid') {
                $this->db->run(
                    "UPDATE transactions SET txn_reference = :payment_id, gateway_payment_id = :payment_id, status = 'success'
                     WHERE id = :transaction_id AND status = 'pending'",
                    ['payment_id' => $paymentId, 'transaction_id' => $transaction['transaction_id']]
                );
                $this->db->run(
                    "UPDATE bookings SET payment_status = 'paid' WHERE id = :booking_id AND payment_status = 'pending'",
                    ['booking_id' => $transaction['booking_id']]
                );
            }
            $this->db->commit();
            return ['booking_id' => (int)$transaction['booking_id'], 'booking_code' => $transaction['booking_code']];
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getLatestCustomerCompletionOtp(int $userId, int $bookingId): ?array
    {
        return $this->db->fetch(
            "SELECT otp.expires_at, otp.verified_at, n.message
             FROM booking_completion_otps otp
             JOIN bookings b ON b.id = otp.booking_id AND b.customer_id = :user_id
             LEFT JOIN notifications n ON n.user_id = b.customer_id
                  AND n.title = 'Service completion code'
                  AND n.link = CONCAT('/customer/my-bookings?booking_id=', b.id)
                  AND n.created_at >= otp.requested_at
             WHERE b.id = :booking_id AND otp.id = (
                 SELECT MAX(latest.id) FROM booking_completion_otps latest WHERE latest.booking_id = b.id
             ) AND otp.verified_at IS NULL AND otp.expires_at > NOW()
             ORDER BY n.id DESC LIMIT 1",
            ['user_id' => $userId, 'booking_id' => $bookingId]
        );
    }

    public function getCustomerBookingProgress(int $userId, int $bookingId): ?array
    {
        return $this->db->fetch(
            "SELECT b.id, b.booking_code, b.status, b.payment_method, b.payment_status,
                    b.total_amount, b.updated_at,
                    (SELECT proof.review_status FROM booking_proofs proof
                     WHERE proof.booking_id = b.id AND proof.proof_type = 'after'
                     ORDER BY proof.id DESC LIMIT 1) AS after_proof_status,
                    (SELECT otp.verified_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_verified_at,
                    (SELECT otp.expires_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_expires_at
             FROM bookings b
             WHERE b.id = :booking_id AND b.customer_id = :customer_id
             LIMIT 1",
            ['booking_id' => $bookingId, 'customer_id' => $userId]
        );
    }

    // --- CUS-05: Custom Quotations ---
    public function submitCustomQuote(int $userId, int $catId, string $title, string $desc, float $bMin, float $bMax): bool
    {
        $sql = "INSERT INTO quotation_requests (customer_id, category_id, title, description, budget_min, budget_max, status)
                VALUES (:uid, :cid, :title, :desc, :bmin, :bmax, 'open')";
        $this->db->run($sql, [
            'uid'   => $userId,
            'cid'   => $catId,
            'title' => $title,
            'desc'  => $desc,
            'bmin'  => $bMin,
            'bmax'  => $bMax
        ]);
        return true;
    }

    public function getCustomerQuotes(int $userId): array
    {
        $sql = "SELECT qr.*, c.name AS category_name, COUNT(qb.id) AS bids_count
                FROM quotation_requests qr
                JOIN categories c ON qr.category_id = c.id
                LEFT JOIN quotation_bids qb ON qr.id = qb.quotation_id
                WHERE qr.customer_id = :uid
                GROUP BY qr.id ORDER BY qr.id DESC";
        return $this->db->fetchAll($sql, ['uid' => $userId]);
    }

    // --- CUS-06: Booking Tracker & Rescheduling ---
    public function getCustomerBookings(int $userId): array
    {
        $sql = "SELECT b.*, u_pro.name AS pro_name, u_pro.phone AS pro_phone, pp.rating_avg,
                   r.id AS review_id, r.rating AS review_rating, r.comment AS review_comment,
                   d.id AS dispute_id
                FROM bookings b
                LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
                LEFT JOIN users u_pro ON pp.user_id = u_pro.id
                LEFT JOIN reviews r ON b.id = r.booking_id
                LEFT JOIN disputes d ON b.id = d.booking_id
                WHERE b.customer_id = :uid
                ORDER BY b.id DESC";
        return $this->db->fetchAll($sql, ['uid' => $userId]);
    }

    public function getCustomerInvoiceDetails(int $userId, int $bookingId): ?array
    {
        $booking = $this->db->fetch(
            "SELECT b.*, u_cust.name AS customer_name, u_cust.email AS customer_email,
                    u_cust.phone AS customer_phone, addr.label AS addr_label,
                    addr.address_line1, addr.address_line2, addr.landmark, addr.city,
                    addr.state, addr.postal_code, sz.name AS zone_name,
                    c.code AS coupon_code, u_pro.name AS pro_name
             FROM bookings b
             JOIN users u_cust ON b.customer_id = u_cust.id
             JOIN user_addresses addr ON b.address_id = addr.id
             LEFT JOIN service_zones sz ON b.zone_id = sz.id
             LEFT JOIN coupons c ON b.coupon_id = c.id
             LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
             LEFT JOIN users u_pro ON pp.user_id = u_pro.id
             WHERE b.id = :booking_id AND b.customer_id = :customer_id
             LIMIT 1",
            ['booking_id' => $bookingId, 'customer_id' => $userId]
        );

        if (!$booking) {
            return null;
        }

        return [
            'booking' => $booking,
            'items' => $this->db->fetchAll(
                "SELECT service_name, unit_price, quantity, total_price
                 FROM booking_items WHERE booking_id = :booking_id ORDER BY id ASC",
                ['booking_id' => $bookingId]
            )
        ];
    }

    public function reschedule(int $userId, int $bookingId, string $newDate, string $newSlot): bool
    {
        $sql = "UPDATE bookings SET scheduled_date = :sdate, scheduled_time_slot = :slot 
                WHERE id = :id AND customer_id = :uid AND status IN ('pending', 'assigned', 'accepted')";
        $this->db->run($sql, ['sdate' => $newDate, 'slot' => $newSlot, 'id' => $bookingId, 'uid' => $userId]);
        return true;
    }

    public function cancelBooking(int $userId, int $bookingId, string $reason): bool
    {
        $sql = "UPDATE bookings SET status = 'cancelled', cancellation_reason = :reason 
                WHERE id = :id AND customer_id = :uid AND status IN ('pending', 'assigned', 'accepted')";
        $this->db->run($sql, ['reason' => $reason, 'id' => $bookingId, 'uid' => $userId]);
        return true;
    }

    // --- CUS-07: In-App Chat ---
    public function getChatMessages(int $bookingId, int $userId): array
    {
        $sql = "SELECT cm.*, u.name AS sender_name 
                FROM chat_messages cm
                JOIN users u ON cm.sender_id = u.id
                WHERE cm.booking_id = :bid AND (cm.sender_id = :uid OR cm.receiver_id = :uid)
                ORDER BY cm.id ASC";
        return $this->db->fetchAll($sql, ['bid' => $bookingId, 'uid' => $userId]);
    }

    public function getChatBookingDetails(int $bookingId): ?array
    {
        return $this->db->fetch(
            "SELECT b.id, b.booking_code, b.customer_id, b.professional_id, b.status,
                    b.payment_method, b.payment_status, u_customer.name AS customer_name,
                    pp.user_id AS professional_user_id, u_professional.name AS professional_name,
                    addr.address_line1, addr.address_line2, addr.city, addr.state, addr.postal_code,
                    addr.landmark, addr.latitude, addr.longitude,
                    (SELECT proof.review_status FROM booking_proofs proof
                     WHERE proof.booking_id = b.id AND proof.proof_type = 'after'
                     ORDER BY proof.id DESC LIMIT 1) AS after_proof_status,
                    (SELECT otp.verified_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_verified_at,
                    (SELECT otp.expires_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_expires_at
             FROM bookings b
             JOIN users u_customer ON u_customer.id = b.customer_id
             JOIN user_addresses addr ON addr.id = b.address_id
             LEFT JOIN professional_profiles pp ON pp.id = b.professional_id
             LEFT JOIN users u_professional ON u_professional.id = pp.user_id
             WHERE b.id = :booking_id LIMIT 1",
            ['booking_id' => $bookingId]
        );
    }

    public function postChatMessage(int $bookingId, int $senderId, int $receiverId, string $msg): bool
    {
        $sql = "INSERT INTO chat_messages (booking_id, sender_id, receiver_id, message) VALUES (:bid, :sid, :rid, :msg)";
        $this->db->run($sql, ['bid' => $bookingId, 'sid' => $senderId, 'rid' => $receiverId, 'msg' => $msg]);
        return true;
    }

    // --- CUS-09: Reviews ---
    public function addReview(int $bookingId, int $customerId, int $proProfileId, int $rating, string $comment): bool
    {
        if ($bookingId <= 0 || $customerId <= 0 || $proProfileId <= 0 || $rating < 1 || $rating > 5) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $booking = $this->db->fetch(
                "SELECT id FROM bookings
                 WHERE id = :bid AND customer_id = :cid AND professional_id = :pid AND status = 'completed'
                 LIMIT 1",
                ['bid' => $bookingId, 'cid' => $customerId, 'pid' => $proProfileId]
            );
            $existingReview = $this->db->fetch(
                "SELECT id FROM reviews WHERE booking_id = :bid LIMIT 1",
                ['bid' => $bookingId]
            );

            if (!$booking || $existingReview) {
                $this->db->rollBack();
                return false;
            }

            $this->db->run(
                "INSERT INTO reviews (booking_id, customer_id, professional_id, rating, comment, status)
                 VALUES (:bid, :cid, :pid, :rating, :comment, 'published')",
                [
                    'bid' => $bookingId,
                    'cid' => $customerId,
                    'pid' => $proProfileId,
                    'rating' => $rating,
                    'comment' => $comment
                ]
            );

            $this->db->run(
                "UPDATE professional_profiles SET
                    rating_avg = (SELECT AVG(rating) FROM reviews WHERE professional_id = :pid AND status = 'published'),
                    rating_count = (SELECT COUNT(id) FROM reviews WHERE professional_id = :pid AND status = 'published')
                 WHERE id = :pid",
                ['pid' => $proProfileId]
            );
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    // --- CUS-10: Support & Disputes ---
    public function createDispute(int $bookingId, int $userId, string $reason, string $details): bool
    {
        if ($bookingId <= 0 || $userId <= 0 || trim($reason) === '' || trim($details) === '') {
            return false;
        }
        $this->db->beginTransaction();
        try {
            $booking = $this->db->fetch(
                "SELECT id, status FROM bookings
                 WHERE id = :booking_id AND customer_id = :customer_id FOR UPDATE",
                ['booking_id' => $bookingId, 'customer_id' => $userId]
            );
            if (!$booking || !in_array($booking['status'], ['in_progress', 'completed'], true)) {
                $this->db->rollBack();
                return false;
            }
            $activeDispute = $this->db->fetch(
                "SELECT id FROM disputes WHERE booking_id = :booking_id
                 AND status IN ('open', 'under_review') LIMIT 1",
                ['booking_id' => $bookingId]
            );
            if ($activeDispute) {
                $this->db->rollBack();
                return false;
            }
            $this->db->run(
                "INSERT INTO disputes (booking_id, raised_by_user_id, reason, details, status)
                 VALUES (:bid, :uid, :reason, :details, 'open')",
                ['bid' => $bookingId, 'uid' => $userId, 'reason' => trim($reason), 'details' => trim($details)]
            );
            $this->db->run("UPDATE bookings SET status = 'disputed' WHERE id = :bid", ['bid' => $bookingId]);
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function getCustomerDisputes(int $userId): array
    {
        $sql = "SELECT d.*, b.booking_code 
                FROM disputes d 
                JOIN bookings b ON d.booking_id = b.id 
                WHERE d.raised_by_user_id = :uid 
                ORDER BY d.id DESC";
        return $this->db->fetchAll($sql, ['uid' => $userId]);
    }
}