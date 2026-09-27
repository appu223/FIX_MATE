<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class AdminModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDashboardOverview(): array
    {
        $kpis = $this->db->fetch("SELECT
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) AS gross_revenue,
            COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN commission_amount ELSE 0 END), 0) AS net_commission,
            SUM(CASE WHEN status IN ('assigned', 'accepted', 'in_progress') THEN 1 ELSE 0 END) AS active_bookings
            FROM bookings") ?? [];

        $proCounts = $this->db->fetch("SELECT
            SUM(CASE WHEN kyc_status = 'pending' THEN 1 ELSE 0 END) AS kyc_pending,
            SUM(CASE WHEN kyc_status = 'verified' THEN 1 ELSE 0 END) AS total_pros
            FROM professional_profiles") ?? [];

        $categoryBreakdown = $this->db->fetchAll("SELECT
            c.name AS category_name,
            COUNT(DISTINCT b.id) AS bookings_count,
            COALESCE(SUM(bi.total_price), 0) AS total_sales
            FROM categories c
            JOIN services s ON s.category_id = c.id
            LEFT JOIN booking_items bi ON bi.service_id = s.id
            LEFT JOIN bookings b ON b.id = bi.booking_id AND b.payment_status = 'paid'
            GROUP BY c.id, c.name
            ORDER BY bookings_count DESC, c.name ASC
            LIMIT 8");

        $recentBookings = $this->db->fetchAll("SELECT
            b.booking_code,
            b.scheduled_date,
            b.scheduled_time_slot,
            b.total_amount,
            b.payment_status,
            b.status,
            customer.name AS customer_name,
            customer.phone AS customer_phone,
            COALESCE(pro.name, 'Unassigned') AS pro_name
            FROM bookings b
            JOIN users customer ON customer.id = b.customer_id
            LEFT JOIN professional_profiles pp ON pp.id = b.professional_id
            LEFT JOIN users pro ON pro.id = pp.user_id
            ORDER BY b.created_at DESC, b.id DESC
            LIMIT 8");

        return [
            'kpis' => array_merge($kpis, $proCounts),
            'categoryBreakdown' => $categoryBreakdown,
            'recentBookings' => $recentBookings,
        ];
    }

    public function getRevenueChartData(): array
    {
        $rows = $this->db->fetchAll("SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month_key,
            COALESCE(SUM(total_amount), 0) AS revenue,
            COALESCE(SUM(commission_amount), 0) AS profit
            FROM bookings
            WHERE payment_status = 'paid'
              AND created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month_key ASC");

        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[$row['month_key']] = $row;
        }

        $labels = [];
        $revenue = [];
        $profit = [];
        $currentMonth = new \DateTimeImmutable('first day of this month');
        for ($offset = 5; $offset >= 0; $offset--) {
            $month = $currentMonth->modify("-{$offset} months");
            $key = $month->format('Y-m');
            $labels[] = $month->format('M Y');
            $revenue[] = (float)($byMonth[$key]['revenue'] ?? 0);
            $profit[] = (float)($byMonth[$key]['profit'] ?? 0);
        }

        return ['labels' => $labels, 'revenue' => $revenue, 'profit' => $profit];
    }

// ========================================================
    // ADM-04: SERVICE CATALOG & CATEGORIES
    // ========================================================

    public function getAllCategoriesWithCounts(): array
    {
        $sql = "SELECT 
                    c.id,
                    c.name,
                    c.slug,
                    c.icon,
                    c.description,
                    c.status,
                    c.sort_order,
                    COUNT(s.id) AS services_count
                FROM categories c
                LEFT JOIN services s ON c.id = s.category_id
                GROUP BY c.id, c.name, c.slug, c.icon, c.description, c.status, c.sort_order
                ORDER BY c.sort_order ASC, c.id ASC";

        return $this->db->fetchAll($sql);
    }

    public function getServicesList(?int $categoryId = null, ?string $search = null): array
    {
        $sql = "SELECT 
                    s.id,
                    s.category_id,
                    s.name,
                    s.slug,
                    s.description,
                    s.base_price,
                    s.duration_minutes,
                    s.is_popular,
                    s.status,
                    s.created_at,
                    c.name AS category_name,
                    c.icon AS category_icon,
                    COUNT(DISTINCT ps.professional_id) AS active_pros_count,
                    COUNT(DISTINCT bi.id) AS times_booked
                FROM services s
                JOIN categories c ON s.category_id = c.id
                LEFT JOIN professional_services ps ON s.id = ps.service_id AND ps.status = 'active'
                LEFT JOIN booking_items bi ON s.id = bi.service_id
                WHERE 1=1";

        $params = [];

        if ($categoryId && $categoryId > 0) {
            $sql .= " AND s.category_id = :cat_id";
            $params['cat_id'] = $categoryId;
        }

        if (!empty($search)) {
            $sql .= " AND (s.name LIKE :search OR s.description LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $sql .= " GROUP BY s.id, s.category_id, s.name, s.slug, s.description, 
                           s.base_price, s.duration_minutes, s.is_popular, s.status, 
                           s.created_at, c.name, c.icon
                  ORDER BY c.sort_order ASC, s.name ASC";

        return $this->db->fetchAll($sql, $params);
    }

    public function saveCategoryRecord(array $data): bool
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($data['name'])));

        if (!empty($data['id'])) {
            $sql = "UPDATE categories 
                    SET name = :name, slug = :slug, icon = :icon, description = :description, 
                        sort_order = :sort_order, status = :status 
                    WHERE id = :id";
            $this->db->run($sql, [
                'name'        => $data['name'],
                'slug'        => $slug,
                'icon'        => $data['icon'] ?? 'bi-tools',
                'description' => $data['description'] ?? null,
                'sort_order'  => (int)($data['sort_order'] ?? 0),
                'status'      => $data['status'] ?? 'active',
                'id'          => $data['id']
            ]);

            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_CATEGORY', 'categories', (int)$data['id'], null, $data);
            return true;
        }

        $sql = "INSERT INTO categories (name, slug, icon, description, sort_order, status) 
                VALUES (:name, :slug, :icon, :description, :sort_order, :status)";
        $this->db->run($sql, [
            'name'        => $data['name'],
            'slug'        => $slug,
            'icon'        => $data['icon'] ?? 'bi-tools',
            'description' => $data['description'] ?? null,
            'sort_order'  => (int)($data['sort_order'] ?? 0),
            'status'      => $data['status'] ?? 'active'
        ]);

        $newId = (int)$this->db->lastInsertId();
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CREATE_CATEGORY', 'categories', $newId, null, $data);
        return true;
    }

    public function saveServiceRecord(array $data): bool
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', trim(strtolower($data['name'])));

        if (!empty($data['id'])) {
            $sql = "UPDATE services 
                    SET category_id = :category_id, name = :name, slug = :slug, 
                        description = :description, base_price = :base_price, 
                        duration_minutes = :duration_minutes, is_popular = :is_popular, 
                        status = :status 
                    WHERE id = :id";
            $this->db->run($sql, [
                'category_id'      => (int)$data['category_id'],
                'name'             => $data['name'],
                'slug'             => $slug,
                'description'      => $data['description'] ?? null,
                'base_price'       => (float)$data['base_price'],
                'duration_minutes' => (int)($data['duration_minutes'] ?? 60),
                'is_popular'       => (int)($data['is_popular'] ?? 0),
                'status'           => $data['status'] ?? 'active',
                'id'               => $data['id']
            ]);

            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_SERVICE', 'services', (int)$data['id'], null, $data);
            return true;
        }

        $sql = "INSERT INTO services (category_id, name, slug, description, base_price, duration_minutes, is_popular, status) 
                VALUES (:category_id, :name, :slug, :description, :base_price, :duration_minutes, :is_popular, :status)";
        $this->db->run($sql, [
            'category_id'      => (int)$data['category_id'],
            'name'             => $data['name'],
            'slug'             => $slug,
            'description'      => $data['description'] ?? null,
            'base_price'       => (float)$data['base_price'],
            'duration_minutes' => (int)($data['duration_minutes'] ?? 60),
            'is_popular'       => (int)($data['is_popular'] ?? 0),
            'status'           => $data['status'] ?? 'active'
        ]);

        $newId = (int)$this->db->lastInsertId();
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CREATE_SERVICE', 'services', $newId, null, $data);
        return true;
    }

    public function setCategoryStatus(int $id, string $status): bool
    {
        $sql = "UPDATE categories SET status = :status WHERE id = :id";
        $this->db->run($sql, ['status' => $status, 'id' => $id]);
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'TOGGLE_CATEGORY_STATUS', 'categories', $id, null, ['status' => $status]);
        return true;
    }

    public function setServiceStatus(int $id, string $status): bool
    {
        $sql = "UPDATE services SET status = :status WHERE id = :id";
        $this->db->run($sql, ['status' => $status, 'id' => $id]);
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'TOGGLE_SERVICE_STATUS', 'services', $id, null, ['status' => $status]);
        return true;
    }
    // ========================================================
    // ADM-03: PROFESSIONAL & KYC VERIFICATION WORKBENCH
    // ========================================================

    public function getProfessionalsList(?string $kycFilter = null, ?string $search = null): array
    {
        $sql = "SELECT 
                    pp.id AS pro_id,
                    u.id AS user_id,
                    u.name,
                    u.email,
                    u.phone,
                    u.status AS account_status,
                    pp.experience_years,
                    pp.id_proof_type,
                    pp.id_proof_file,
                    pp.address_proof_file,
                    pp.license_file,
                    pp.kyc_status,
                    pp.kyc_rejected_reason,
                    pp.rating_avg,
                    pp.rating_count,
                    pp.commission_rate,
                    pp.wallet_balance,
                    pp.bank_name,
                    pp.bank_account_no,
                    pp.bank_ifsc,
                    pp.created_at,
                    COUNT(b.id) AS total_jobs,
                                        COALESCE(SUM(CASE WHEN b.status = 'completed' THEN b.pro_earning ELSE 0 END), 0) AS total_earned,
                                        (SELECT COUNT(*) FROM bookings active_b
                                         WHERE active_b.professional_id = pp.id
                                             AND active_b.status IN ('assigned', 'accepted', 'in_progress')) AS active_jobs,
                                        (SELECT COUNT(*) FROM booking_proofs bp
                                         JOIN bookings proof_b ON proof_b.id = bp.booking_id
                                         WHERE proof_b.professional_id = pp.id AND bp.review_status = 'pending') AS pending_proof_reviews
                FROM professional_profiles pp
                JOIN users u ON pp.user_id = u.id
                LEFT JOIN bookings b ON pp.id = b.professional_id
                WHERE 1=1";

        $params = [];

        if (!empty($kycFilter)) {
            $sql .= " AND pp.kyc_status = :kyc_status";
            $params['kyc_status'] = $kycFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        $sql .= " GROUP BY pp.id, u.id, u.name, u.email, u.phone, u.status, pp.experience_years, 
                           pp.id_proof_type, pp.id_proof_file, pp.address_proof_file, pp.license_file, 
                           pp.kyc_status, pp.kyc_rejected_reason, pp.rating_avg, pp.rating_count, 
                           pp.commission_rate, pp.wallet_balance, pp.bank_name, pp.bank_account_no, 
                           pp.bank_ifsc, pp.created_at
                  ORDER BY (pp.kyc_status = 'pending') DESC, pp.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function createProfessionalAccount(array $data): int
    {
        $this->db->beginTransaction();

        try {
            $this->db->run("INSERT INTO users (role, name, email, phone, password_hash, status)
                VALUES ('professional', :name, :email, :phone, :password_hash, 'pending_verification')", [
                'name' => $data['name'],
                'email' => strtolower(trim($data['email'])),
                'phone' => $data['phone'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ]);
            $userId = (int)$this->db->lastInsertId();

            $this->db->run("INSERT INTO professional_profiles (user_id, experience_years, id_proof_type, kyc_status)
                VALUES (:user_id, :experience_years, :id_proof_type, 'pending')", [
                'user_id' => $userId,
                'experience_years' => max(0, (int)($data['experience_years'] ?? 1)),
                'id_proof_type' => trim((string)($data['id_proof_type'] ?? 'Aadhaar Card')) ?: 'Aadhaar Card',
            ]);
            $proId = (int)$this->db->lastInsertId();

            $actorId = (int)($_SESSION['fixmate_user_id'] ?? 0);
            if ($actorId > 0) {
                $this->logAudit($actorId, 'CREATE_PROFESSIONAL', 'professional_profiles', $proId, null, [
                    'user_id' => $userId,
                    'email' => $data['email'],
                    'kyc_status' => 'pending',
                ]);
            }

            $this->db->commit();
            return $proId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getProDossierDetails(int $proId): ?array
    {
        $sql = "SELECT 
                    pp.id AS pro_id,
                    u.id AS user_id,
                    u.name,
                    u.email,
                    u.phone,
                    u.status AS account_status,
                    pp.bio,
                    pp.experience_years,
                    pp.id_proof_type,
                    pp.id_proof_file,
                    pp.address_proof_file,
                    pp.license_file,
                    pp.kyc_status,
                    pp.kyc_rejected_reason,
                    pp.rating_avg,
                    pp.rating_count,
                    pp.commission_rate,
                    pp.emergency_contact,
                    pp.bank_name,
                    pp.bank_account_no,
                    pp.bank_ifsc,
                    pp.wallet_balance,
                    pp.created_at
                FROM professional_profiles pp
                JOIN users u ON pp.user_id = u.id
                WHERE pp.id = :pro_id";

        $dossier = $this->db->fetch($sql, ['pro_id' => $proId]);
        if (!$dossier) {
            return null;
        }

        // Assigned Services
        $svcSql = "SELECT s.name AS service_name, c.name AS category_name, ps.custom_price, ps.status
                   FROM professional_services ps
                   JOIN services s ON ps.service_id = s.id
                   JOIN categories c ON s.category_id = c.id
                   WHERE ps.professional_id = :pro_id";
        $services = $this->db->fetchAll($svcSql, ['pro_id' => $proId]);

        // Servicing Zones
        $zoneSql = "SELECT sz.name AS zone_name, sz.city, sz.surge_multiplier
                    FROM professional_service_zones psz
                    JOIN service_zones sz ON psz.zone_id = sz.id
                    WHERE psz.professional_id = :pro_id";
        $zones = $this->db->fetchAll($zoneSql, ['pro_id' => $proId]);

        // Recent Work Orders
        $jobSql = "SELECT b.booking_code, b.scheduled_date, b.total_amount, b.pro_earning, b.status
                   FROM bookings b
                   WHERE b.professional_id = :pro_id
                   ORDER BY b.id DESC LIMIT 5";
        $jobs = $this->db->fetchAll($jobSql, ['pro_id' => $proId]);

        return [
            'dossier'  => $dossier,
            'services' => $services,
            'zones'    => $zones,
            'jobs'     => $jobs
        ];
    }

    public function updateKycStatus(int $proId, string $status, ?string $reason): bool
    {
        $allowed = ['pending', 'verified', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $pro = $this->db->fetch(
            "SELECT pp.user_id, pp.kyc_status AS old_status, u.name
             FROM professional_profiles pp JOIN users u ON u.id = pp.user_id
             WHERE pp.id = :id",
            ['id' => $proId]
        );
        if (!$pro) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            $this->db->run(
                "UPDATE professional_profiles
                 SET kyc_status = :status, kyc_rejected_reason = :reason
                 WHERE id = :id",
                [
                    'status' => $status,
                    'reason' => $status === 'rejected' ? $reason : null,
                    'id' => $proId,
                ]
            );

            if ($status === 'verified') {
                $this->db->run("UPDATE users SET status = 'active' WHERE id = :user_id", ['user_id' => $pro['user_id']]);
            } elseif ($status === 'rejected') {
                $this->db->run("UPDATE users SET status = 'pending_verification' WHERE id = :user_id", ['user_id' => $pro['user_id']]);
            }

            $statusLabel = ucfirst($status);
            $notificationMessage = match ($status) {
                'verified' => 'Your Fixmate technician account has been verified. You can now access technician services.',
                'rejected' => 'Your KYC documents need attention. Reason: ' . (trim((string)$reason) ?: 'Please contact Fixmate support.'),
                default => 'Your KYC verification is pending. We will notify you when the review is complete.',
            };
            $this->db->run(
                "INSERT INTO notifications (user_id, title, message, link)
                 VALUES (:uid, :title, :message, :link)",
                [
                    'uid' => (int)$pro['user_id'],
                    'title' => 'KYC ' . $statusLabel,
                    'message' => $notificationMessage,
                    'link' => '/pro/dashboard',
                ]
            );

            $this->logAudit((int)($_SESSION['fixmate_user_id'] ?? 0), 'KYC_STATUS_UPDATE', 'professional_profiles', $proId, [
                'status' => $pro['old_status'],
            ], [
                'status' => $status,
                'reason' => $status === 'rejected' ? $reason : null,
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function updateProFinancialTerms(int $proId, float $commissionRate, string $bankName, string $accountNo, string $ifsc): bool
    {
        $sql = "UPDATE professional_profiles 
                SET commission_rate = :commission_rate, 
                    bank_name = :bank_name, 
                    bank_account_no = :account_no, 
                    bank_ifsc = :ifsc 
                WHERE id = :id";

        $this->db->run($sql, [
            'commission_rate' => $commissionRate,
            'bank_name'       => $bankName,
            'account_no'      => $accountNo,
            'ifsc'            => $ifsc,
            'id'              => $proId
        ]);

        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_PRO_FINANCIALS', 'professional_profiles', $proId, null, [
            'commission_rate' => $commissionRate,
            'bank'            => $bankName
        ]);

        return true;
    }
    // ========================================================
    // ADM-02: STAFF & USER MANAGEMENT (RBAC + CUSTOMER 360)
    // ========================================================

    public function getStaffMembers(): array
    {
        $sql = "SELECT id, name, email, phone, role, status, created_at 
                FROM users 
                WHERE role IN ('admin', 'staff') 
                ORDER BY role ASC, id DESC";
        return $this->db->fetchAll($sql);
    }

    public function getCustomersList(?string $search = null, ?string $status = null): array
    {
        $sql = "SELECT 
                    u.id, 
                    u.name, 
                    u.email, 
                    u.phone, 
                    u.status, 
                    u.created_at,
                    COUNT(b.id) AS total_bookings,
                    COALESCE(SUM(CASE WHEN b.payment_status = 'paid' THEN b.total_amount ELSE 0 END), 0) AS lifetime_spend
                FROM users u
                LEFT JOIN bookings b ON u.id = b.customer_id
                WHERE u.role = 'customer'";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        if (!empty($status)) {
            $sql .= " AND u.status = :status";
            $params['status'] = $status;
        }

        $sql .= " GROUP BY u.id, u.name, u.email, u.phone, u.status, u.created_at
                  ORDER BY u.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function createOrUpdateStaff(array $data): bool
    {
        if (!empty($data['id'])) {
            // Update Existing Staff
            if (!empty($data['password'])) {
                $hash = password_hash($data['password'], PASSWORD_BCRYPT);
                $sql = "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status, password_hash = :hash WHERE id = :id";
                $params = [
                    'id'     => $data['id'],
                    'name'   => $data['name'],
                    'email'  => $data['email'],
                    'phone'  => $data['phone'],
                    'role'   => $data['role'],
                    'status' => $data['status'],
                    'hash'   => $hash,
                ];
            } else {
                $sql = "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status WHERE id = :id";
                $params = [
                    'id'     => $data['id'],
                    'name'   => $data['name'],
                    'email'  => $data['email'],
                    'phone'  => $data['phone'],
                    'role'   => $data['role'],
                    'status' => $data['status'],
                ];
            }
            $this->db->run($sql, $params);
            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_STAFF', 'users', (int)$data['id'], null, $params);
            return true;
        }

        // Create New Staff Member
        $hash = password_hash($data['password'] ?? 'Password@123', PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (name, email, phone, role, status, password_hash) 
                VALUES (:name, :email, :phone, :role, :status, :hash)";
        
        $params = [
            'name'   => $data['name'],
            'email'  => $data['email'],
            'phone'  => $data['phone'],
            'role'   => $data['role'] ?? 'staff',
            'status' => $data['status'] ?? 'active',
            'hash'   => $hash
        ];

        $this->db->run($sql, $params);
        $newId = (int)$this->db->lastInsertId();
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CREATE_STAFF', 'users', $newId, null, ['email' => $data['email'], 'role' => $data['role']]);
        return true;
    }

    public function updateUserStatus(int $userId, string $newStatus): bool
    {
        $allowedStatuses = ['active', 'inactive', 'suspended'];
        if (!in_array($newStatus, $allowedStatuses, true)) {
            return false;
        }

        // Disallow self-suspension for safety
        if ($userId === (int)($_SESSION['fixmate_user_id'] ?? 0)) {
            return false;
        }

        $sql = "UPDATE users SET status = :status WHERE id = :id";
        $this->db->run($sql, ['status' => $newStatus, 'id' => $userId]);

        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CHANGE_STATUS', 'users', $userId, null, ['status' => $newStatus]);
        return true;
    }

    public function getCustomer360Details(int $customerId): ?array
    {
        // Customer Profile Base
        $custSql = "SELECT id, name, email, phone, status, created_at FROM users WHERE id = :id AND role = 'customer'";
        $customer = $this->db->fetch($custSql, ['id' => $customerId]);

        if (!$customer) {
            return null;
        }

        // Stored Addresses
        $addrSql = "SELECT id, label, address_line1, address_line2, landmark, city, state, postal_code, is_default 
                    FROM user_addresses 
                    WHERE user_id = :id ORDER BY is_default DESC, id DESC";
        $addresses = $this->db->fetchAll($addrSql, ['id' => $customerId]);

        // Historical Bookings
        $bookSql = "SELECT 
                        b.id,
                        b.booking_code,
                        b.scheduled_date,
                        b.scheduled_time_slot,
                        b.status,
                        b.payment_status,
                        b.total_amount,
                        COALESCE(u_pro.name, 'Unassigned') as pro_name
                    FROM bookings b
                    LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
                    LEFT JOIN users u_pro ON pp.user_id = u_pro.id
                    WHERE b.customer_id = :id
                    ORDER BY b.id DESC LIMIT 10";
        $bookings = $this->db->fetchAll($bookSql, ['id' => $customerId]);

        // Financial totals
        $finSql = "SELECT 
                        COUNT(id) AS total_orders,
                        COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) AS total_spent
                   FROM bookings 
                   WHERE customer_id = :id";
        $finances = $this->db->fetch($finSql, ['id' => $customerId]);

        return [
            'profile'   => $customer,
            'addresses' => $addresses,
            'bookings'  => $bookings,
            'finances'  => $finances
        ];
    }

    public function logAudit(int $userId, string $action, string $entityType, ?int $entityId, ?array $oldValues, ?array $newValues): void
    {
        $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address) 
                VALUES (:user_id, :action, :entity_type, :entity_id, :old_vals, :new_vals, :ip)";
        
        $this->db->run($sql, [
            'user_id'     => $userId > 0 ? $userId : null,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_vals'    => $oldValues ? json_encode($oldValues) : null,
            'new_vals'    => $newValues ? json_encode($newValues) : null,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);
    }

    // ========================================================
    // ADM-05: SERVICE AREAS & PRO MAPPING
    // ========================================================

    public function getAllServiceZones(): array
    {
        $sql = "SELECT 
                    sz.id,
                    sz.name,
                    sz.city,
                    sz.state,
                    sz.postal_codes_json,
                    sz.surge_multiplier,
                    sz.status,
                    sz.created_at,
                    COUNT(DISTINCT psz.professional_id) AS assigned_pros_count,
                    COUNT(DISTINCT b.id) AS total_zone_bookings
                FROM service_zones sz
                LEFT JOIN professional_service_zones psz ON sz.id = psz.zone_id
                LEFT JOIN bookings b ON sz.id = b.zone_id
                GROUP BY sz.id, sz.name, sz.city, sz.state, sz.postal_codes_json, 
                         sz.surge_multiplier, sz.status, sz.created_at
                ORDER BY sz.id ASC";

        return $this->db->fetchAll($sql);
    }

    public function saveZone(array $data): bool
    {
        // Format postal codes array into JSON
        $codesRaw = explode(',', (string)($data['postal_codes'] ?? ''));
        $cleanedCodes = [];
        foreach ($codesRaw as $c) {
            $code = trim($c);
            if (!empty($code)) {
                $cleanedCodes[] = $code;
            }
        }
        $jsonCodes = json_encode(array_values(array_unique($cleanedCodes)));

        if (!empty($data['id'])) {
            $sql = "UPDATE service_zones 
                    SET name = :name, city = :city, state = :state, 
                        postal_codes_json = :postal_codes, surge_multiplier = :surge, 
                        status = :status 
                    WHERE id = :id";
            $this->db->run($sql, [
                'name'         => $data['name'],
                'city'         => $data['city'],
                'state'        => $data['state'],
                'postal_codes' => $jsonCodes,
                'surge'        => (float)($data['surge_multiplier'] ?? 1.00),
                'status'       => $data['status'] ?? 'active',
                'id'           => $data['id']
            ]);

            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_ZONE', 'service_zones', (int)$data['id'], null, $data);
            return true;
        }

        $sql = "INSERT INTO service_zones (name, city, state, postal_codes_json, surge_multiplier, status) 
                VALUES (:name, :city, :state, :postal_codes, :surge, :status)";
        $this->db->run($sql, [
            'name'         => $data['name'],
            'city'         => $data['city'],
            'state'        => $data['state'],
            'postal_codes' => $jsonCodes,
            'surge'        => (float)($data['surge_multiplier'] ?? 1.00),
            'status'       => $data['status'] ?? 'active'
        ]);

        $newId = (int)$this->db->lastInsertId();
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CREATE_ZONE', 'service_zones', $newId, null, $data);
        return true;
    }

    public function getProMappingData(int $proId): array
    {
        // 1. All Zones and active assignments
        $zonesSql = "SELECT sz.id, sz.name, sz.city,
                            CASE WHEN psz.professional_id IS NOT NULL THEN 1 ELSE 0 END AS is_assigned
                     FROM service_zones sz
                     LEFT JOIN professional_service_zones psz 
                            ON sz.id = psz.zone_id AND psz.professional_id = :pro_id
                     ORDER BY sz.id ASC";
        $zones = $this->db->fetchAll($zonesSql, ['pro_id' => $proId]);

        // 2. All Services, Base Price & Pro Custom Overrides
        $svcSql = "SELECT s.id AS service_id, s.name AS service_name, c.name AS category_name, s.base_price,
                          ps.custom_price, ps.status AS pro_svc_status,
                          CASE WHEN ps.professional_id IS NOT NULL THEN 1 ELSE 0 END AS is_offered
                   FROM services s
                   JOIN categories c ON s.category_id = c.id
                   LEFT JOIN professional_services ps 
                          ON s.id = ps.service_id AND ps.professional_id = :pro_id
                   ORDER BY c.sort_order ASC, s.name ASC";
        $services = $this->db->fetchAll($svcSql, ['pro_id' => $proId]);

        return [
            'zones'    => $zones,
            'services' => $services
        ];
    }

    public function syncProZones(int $proId, array $zoneIds): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->run("DELETE FROM professional_service_zones WHERE professional_id = :pro_id", ['pro_id' => $proId]);

            $stmt = $this->db->getConnection()->prepare("INSERT INTO professional_service_zones (professional_id, zone_id) VALUES (:pro_id, :zone_id)");
            foreach ($zoneIds as $zId) {
                $stmt->execute(['pro_id' => $proId, 'zone_id' => (int)$zId]);
            }

            $this->db->commit();
            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'SYNC_PRO_ZONES', 'professional_service_zones', $proId, null, ['zones' => $zoneIds]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateProServiceRate(int $proId, int $serviceId, bool $isOffered, ?float $customPrice): bool
    {
        if (!$isOffered) {
            $this->db->run("DELETE FROM professional_services WHERE professional_id = :pro_id AND service_id = :svc_id", [
                'pro_id' => $proId,
                'svc_id' => $serviceId
            ]);
            return true;
        }

        $sql = "INSERT INTO professional_services (professional_id, service_id, custom_price, status) 
                VALUES (:pro_id, :svc_id, :price, 'active')
                ON DUPLICATE KEY UPDATE custom_price = :price_update, status = 'active'";

        $this->db->run($sql, [
            'pro_id'       => $proId,
            'svc_id'       => $serviceId,
            'price'        => $customPrice,
            'price_update' => $customPrice
        ]);

        return true;
    }

    // ========================================================
    // ADM-06: BOOKING DISPATCH & ASSIGNMENT
    // ========================================================

    public function getDispatchBookings(array $filters = []): array
    {
        $sql = "SELECT 
                    b.id,
                    b.booking_code,
                    b.scheduled_date,
                    b.scheduled_time_slot,
                    b.status,
                    b.payment_status,
                    b.payment_method,
                    b.subtotal,
                    b.surge_amount,
                    b.discount_amount,
                    b.tax_amount,
                    b.total_amount,
                    b.commission_amount,
                    b.pro_earning,
                    b.created_at,
                    u_cust.name AS customer_name,
                    u_cust.phone AS customer_phone,
                    addr.city AS service_city,
                    addr.postal_code,
                    sz.name AS zone_name,
                    pp.id AS pro_profile_id,
                    u_pro.name AS pro_name,
                    u_pro.phone AS pro_phone
                FROM bookings b
                JOIN users u_cust ON b.customer_id = u_cust.id
                JOIN user_addresses addr ON b.address_id = addr.id
                LEFT JOIN service_zones sz ON b.zone_id = sz.id
                LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
                LEFT JOIN users u_pro ON pp.user_id = u_pro.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['zone_id'])) {
            $sql .= " AND b.zone_id = :zone_id";
            $params['zone_id'] = (int)$filters['zone_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (b.booking_code LIKE :search OR u_cust.name LIKE :search OR u_cust.phone LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY (b.status = 'pending') DESC, b.scheduled_date DESC, b.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getBookingFullDetails(int $bookingId): ?array
    {
        // 1. Booking & Parties
        $sql = "SELECT 
                    b.*,
                    u_cust.name AS customer_name,
                    u_cust.email AS customer_email,
                    u_cust.phone AS customer_phone,
                    addr.label AS addr_label,
                    addr.address_line1,
                    addr.address_line2,
                    addr.landmark,
                    addr.city,
                    addr.state,
                    addr.postal_code,
                    sz.name AS zone_name,
                    c.code AS coupon_code,
                    pp.id AS pro_profile_id,
                    u_pro.name AS pro_name,
                    u_pro.phone AS pro_phone,
                    pp.rating_avg AS pro_rating
                FROM bookings b
                JOIN users u_cust ON b.customer_id = u_cust.id
                JOIN user_addresses addr ON b.address_id = addr.id
                LEFT JOIN service_zones sz ON b.zone_id = sz.id
                LEFT JOIN coupons c ON b.coupon_id = c.id
                LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
                LEFT JOIN users u_pro ON pp.user_id = u_pro.id
                WHERE b.id = :id";

        $booking = $this->db->fetch($sql, ['id' => $bookingId]);
        if (!$booking) {
            return null;
        }

        // 2. Booking Line Items
        $itemsSql = "SELECT * FROM booking_items WHERE booking_id = :id";
        $items = $this->db->fetchAll($itemsSql, ['id' => $bookingId]);

        // 3. Technician work evidence for the admin dispatch dossier.
        $proofs = $this->db->fetchAll(
            "SELECT id, proof_type, file_path, description, review_status, created_at
             FROM booking_proofs WHERE booking_id = :id ORDER BY id DESC",
            ['id' => $bookingId]
        );

        // 4. Eligible Technicians for this zone
        $prosSql = "SELECT 
                        pp.id AS pro_id,
                        u.name,
                        u.phone,
                        pp.rating_avg,
                        pp.rating_count,
                        COUNT(b_active.id) AS active_jobs
                    FROM professional_profiles pp
                    JOIN users u ON pp.user_id = u.id
                    JOIN professional_service_zones psz ON pp.id = psz.professional_id
                    LEFT JOIN bookings b_active ON pp.id = b_active.professional_id 
                         AND b_active.status IN ('assigned', 'accepted', 'in_progress')
                    WHERE pp.kyc_status = 'verified' 
                      AND u.status = 'active'
                      AND psz.zone_id = :zone_id
                    GROUP BY pp.id, u.name, u.phone, pp.rating_avg, pp.rating_count
                    ORDER BY active_jobs ASC, pp.rating_avg DESC";

        $eligiblePros = $booking['zone_id'] 
            ? $this->db->fetchAll($prosSql, ['zone_id' => $booking['zone_id']]) 
            : [];

        return [
            'booking'      => $booking,
            'items'        => $items,
            'proofs'       => $proofs,
            'eligiblePros' => $eligiblePros
        ];
    }

    public function getBookingCompletionSummary(int $bookingId): ?array
    {
        return $this->db->fetch(
            "SELECT b.id, b.booking_code, b.status, b.payment_method, b.payment_status,
                    b.total_amount, b.commission_amount, b.pro_earning, b.pro_earning_credited,
                    (SELECT proof.review_status FROM booking_proofs proof
                     WHERE proof.booking_id = b.id AND proof.proof_type = 'after'
                     ORDER BY proof.id DESC LIMIT 1) AS after_proof_status,
                    (SELECT otp.verified_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS completion_otp_verified_at
             FROM bookings b WHERE b.id = :booking_id LIMIT 1",
            ['booking_id' => $bookingId]
        );
    }

    public function getKycDocumentPath(int $proId, string $kind): ?string
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
        $row = $this->db->fetch("SELECT {$column} AS file_path FROM professional_profiles WHERE id = :id", ['id' => $proId]);
        return $row['file_path'] ?? null;
    }

    public function getWorkProofPath(int $proofId): ?string
    {
        $row = $this->db->fetch("SELECT file_path FROM booking_proofs WHERE id = :id", ['id' => $proofId]);
        return $row['file_path'] ?? null;
    }

    public function assignProToBooking(int $bookingId, int $proProfileId): bool
    {
        // Calculate Pro Earnings & Platform Commission based on Pro's rate
        $booking = $this->db->fetch("SELECT subtotal, total_amount, zone_id FROM bookings WHERE id = :id", ['id' => $bookingId]);
        $pro = $this->db->fetch("SELECT commission_rate FROM professional_profiles WHERE id = :id", ['id' => $proProfileId]);

        if (!$booking || !$pro) {
            return false;
        }

        $commPct = (float)$pro['commission_rate'];
        $commissionAmount = round(((float)$booking['subtotal'] * ($commPct / 100)), 2);
        $proEarning = (float)$booking['total_amount'] - $commissionAmount;

        $sql = "UPDATE bookings 
                SET professional_id = :pro_id,
                    status = 'assigned',
                    commission_amount = :commission,
                    pro_earning = :earning
                WHERE id = :id";

        $this->db->run($sql, [
            'pro_id'     => $proProfileId,
            'commission' => $commissionAmount,
            'earning'    => $proEarning,
            'id'         => $bookingId
        ]);

        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'DISPATCH_ASSIGN', 'bookings', $bookingId, null, [
            'pro_id'     => $proProfileId,
            'commission' => $commissionAmount,
            'earning'    => $proEarning
        ]);

        return true;
    }

    public function autoDispatchMatch(int $bookingId): ?int
    {
        $booking = $this->db->fetch("SELECT zone_id FROM bookings WHERE id = :id", ['id' => $bookingId]);
        if (!$booking || !$booking['zone_id']) {
            return null;
        }

        // Find verified pro in zone with lowest active load and highest rating
        $sql = "SELECT pp.id AS pro_id, COUNT(b.id) AS active_load
                FROM professional_profiles pp
                JOIN users u ON pp.user_id = u.id
                JOIN professional_service_zones psz ON pp.id = psz.zone_id
                LEFT JOIN bookings b ON pp.id = b.professional_id AND b.status IN ('assigned', 'accepted', 'in_progress')
                WHERE psz.zone_id = :zone_id
                  AND pp.kyc_status = 'verified'
                  AND u.status = 'active'
                GROUP BY pp.id, pp.rating_avg
                ORDER BY active_load ASC, pp.rating_avg DESC
                LIMIT 1";

        $match = $this->db->fetch($sql, ['zone_id' => $booking['zone_id']]);

        if (!$match) {
            return null;
        }

        $assigned = $this->assignProToBooking($bookingId, (int)$match['pro_id']);
        return $assigned ? (int)$match['pro_id'] : null;
    }

    public function updateBookingLifecycleStatus(int $bookingId, string $status, ?string $cancellationReason = null): bool
    {
        $allowed = ['pending', 'assigned', 'accepted', 'in_progress', 'completed', 'cancelled', 'disputed'];
        if (!in_array($status, $allowed, true) || $status === 'completed') {
            return false;
        }

        $sql = "UPDATE bookings SET status = :status, cancellation_reason = :reason WHERE id = :id";
        $this->db->run($sql, [
            'status' => $status,
            'reason' => ($status === 'cancelled') ? $cancellationReason : null,
            'id'     => $bookingId
        ]);

        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'BOOKING_STATUS_CHANGE', 'bookings', $bookingId, null, [
            'status' => $status,
            'reason' => $cancellationReason
        ]);

        return true;
    }

    public function cancelHelpdeskBooking(int $bookingId, string $reason): bool
    {
        if ($bookingId <= 0 || trim($reason) === '') {
            return false;
        }
        $this->db->beginTransaction();
        try {
            $booking = $this->db->fetch(
                "SELECT id, status, payment_status, pro_earning_credited FROM bookings WHERE id = :id FOR UPDATE",
                ['id' => $bookingId]
            );
            if (!$booking || !in_array($booking['status'], ['pending', 'assigned', 'accepted', 'in_progress'], true)
                || $booking['payment_status'] === 'paid'
                || (int)$booking['pro_earning_credited'] === 1) {
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
                "UPDATE bookings SET status = 'cancelled', cancellation_reason = :reason
                 WHERE id = :id AND status = :current_status",
                ['reason' => trim($reason), 'id' => $bookingId, 'current_status' => $booking['status']]
            );
            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CANCEL_BOOKING', 'bookings', $bookingId, null, [
                'previous_status' => $booking['status'],
                'reason' => trim($reason),
            ]);
            $this->db->commit();
            return true;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }
    // ========================================================
    // ADM-07: PRICING RULES & PROMOTIONS
    // ========================================================

    public function getAllCoupons(): array
    {
        $sql = "SELECT 
                    c.*,
                    COUNT(b.id) AS actual_redemptions,
                    COALESCE(SUM(b.discount_amount), 0) AS total_discount_granted
                FROM coupons c
                LEFT JOIN bookings b ON c.id = b.coupon_id
                GROUP BY c.id
                ORDER BY c.status ASC, c.id DESC";

        return $this->db->fetchAll($sql);
    }

    public function saveCouponRecord(array $data): bool
    {
        $code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', (string)$data['code']));

        if (!empty($data['id'])) {
            $sql = "UPDATE coupons 
                    SET code = :code, discount_type = :discount_type, discount_value = :discount_value, 
                        min_booking_value = :min_val, max_discount = :max_disc, usage_limit = :limit, 
                        valid_from = :valid_from, valid_until = :valid_until, status = :status 
                    WHERE id = :id";
            $this->db->run($sql, [
                'code'           => $code,
                'discount_type'  => $data['discount_type'],
                'discount_value' => (float)$data['discount_value'],
                'min_val'        => (float)($data['min_booking_value'] ?? 0.00),
                'max_disc'       => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
                'limit'          => (int)($data['usage_limit'] ?? 1000),
                'valid_from'     => $data['valid_from'],
                'valid_until'    => $data['valid_until'],
                'status'         => $data['status'] ?? 'active',
                'id'             => $data['id']
            ]);

            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_COUPON', 'coupons', (int)$data['id'], null, $data);
            return true;
        }

        $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_booking_value, max_discount, usage_limit, valid_from, valid_until, status) 
                VALUES (:code, :discount_type, :discount_value, :min_val, :max_disc, :limit, :valid_from, :valid_until, :status)";
        
        $this->db->run($sql, [
            'code'           => $code,
            'discount_type'  => $data['discount_type'],
            'discount_value' => (float)$data['discount_value'],
            'min_val'        => (float)($data['min_booking_value'] ?? 0.00),
            'max_disc'       => !empty($data['max_discount']) ? (float)$data['max_discount'] : null,
            'limit'          => (int)($data['usage_limit'] ?? 1000),
            'valid_from'     => $data['valid_from'],
            'valid_until'    => $data['valid_until'],
            'status'         => $data['status'] ?? 'active'
        ]);

        $newId = (int)$this->db->lastInsertId();
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'CREATE_COUPON', 'coupons', $newId, null, $data);
        return true;
    }

    public function setCouponStatus(int $id, string $status): bool
    {
        $sql = "UPDATE coupons SET status = :status WHERE id = :id";
        $this->db->run($sql, ['status' => $status, 'id' => $id]);
        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'TOGGLE_COUPON_STATUS', 'coupons', $id, null, ['status' => $status]);
        return true;
    }

    public function getGlobalPricingSettings(): array
    {
        $keys = ['min_booking_fee', 'weekend_surge_multiplier', 'platform_convenience_fee', 'emergency_booking_multiplier'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        
        $sql = "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ($placeholders)";
        $rows = $this->db->fetchAll($sql, $keys);

        $settings = [
            'min_booking_fee'              => '199.00',
            'weekend_surge_multiplier'     => '1.10',
            'platform_convenience_fee'     => '49.00',
            'emergency_booking_multiplier' => '1.25'
        ];

        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }

        return $settings;
    }

    public function saveGlobalPricingSettings(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            $sql = "INSERT INTO system_settings (setting_key, setting_value, setting_group, description) 
                    VALUES (:key, :val, 'pricing', 'Dynamic pricing and fee configuration')
                    ON DUPLICATE KEY UPDATE setting_value = :val_update";
            $this->db->run($sql, [
                'key'        => $key,
                'val'        => (string)$value,
                'val_update' => (string)$value
            ]);
        }

        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_GLOBAL_PRICING', 'system_settings', null, null, $settings);
        return true;
    }
    // ========================================================
    // ADM-08: FINANCIALS, INVOICES & PAYOUTS
    // ========================================================

    public function getFinancialLedger(array $filters = []): array
    {
        $sql = "SELECT 
                    t.id,
                    t.booking_id,
                    t.txn_reference,
                    t.amount,
                    t.type,
                    t.payment_gateway,
                    t.status,
                    t.created_at,
                    u.name AS user_name,
                    u.role AS user_role,
                    b.booking_code
                FROM transactions t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN bookings b ON t.booking_id = b.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.txn_reference LIKE :search OR u.name LIKE :search OR b.booking_code LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY t.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getPendingPayoutRequests(): array
    {
        $sql = "SELECT 
                    pr.id,
                    pr.professional_id,
                    pr.amount,
                    pr.status,
                    pr.payout_reference,
                    pr.created_at,
                    u.name AS pro_name,
                    u.phone AS pro_phone,
                    pp.bank_name,
                    pp.bank_account_no,
                    pp.bank_ifsc,
                    pp.wallet_balance
                FROM payout_requests pr
                JOIN professional_profiles pp ON pr.professional_id = pp.id
                JOIN users u ON pp.user_id = u.id
                ORDER BY (pr.status = 'pending') DESC, pr.id DESC";

        return $this->db->fetchAll($sql);
    }

    public function executePayoutDecision(int $payoutId, string $status, ?string $reference = null): bool
    {
        $payout = $this->db->fetch("SELECT * FROM payout_requests WHERE id = :id", ['id' => $payoutId]);
        if (!$payout || $payout['status'] !== 'pending') {
            return false;
        }

        $this->db->beginTransaction();
        try {
            if ($status === 'approved' || $status === 'processed') {
                $ref = $reference ?: 'PAYOUT-NEFT-' . strtoupper(substr(uniqid(), -8));

                // 1. Update payout request
                $this->db->run("UPDATE payout_requests 
                                SET status = :status, payout_reference = :ref, processed_at = CURRENT_TIMESTAMP 
                                WHERE id = :id", [
                    'status' => $status,
                    'ref'    => $ref,
                    'id'     => $payoutId
                ]);

                // 2. Deduct technician wallet balance
                $this->db->run("UPDATE professional_profiles 
                                SET wallet_balance = GREATEST(0, wallet_balance - :amount) 
                                WHERE id = :pro_id", [
                    'amount' => $payout['amount'],
                    'pro_id' => $payout['professional_id']
                ]);

                // 3. Log a payout transaction
                $proUser = $this->db->fetch("SELECT user_id FROM professional_profiles WHERE id = :id", ['id' => $payout['professional_id']]);
                $this->db->run("INSERT INTO transactions (user_id, txn_reference, amount, type, payment_gateway, status) 
                                VALUES (:user_id, :ref, :amount, 'payout', 'Bank Transfer', 'success')", [
                    'user_id' => $proUser['user_id'],
                    'ref'     => $ref,
                    'amount'  => $payout['amount']
                ]);
            } else {
                // Rejected payout
                $this->db->run("UPDATE payout_requests SET status = 'rejected' WHERE id = :id", ['id' => $payoutId]);
            }

            $this->db->commit();
            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'PROCESS_PAYOUT', 'payout_requests', $payoutId, null, [
                'status' => $status,
                'amount' => $payout['amount']
            ]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getInvoiceDetails(int $bookingId): ?array
    {
        return $this->getBookingFullDetails($bookingId);
    }
    // ========================================================
    // ADM-09: REVIEWS, DISPUTES & HELPDESK
    // ========================================================

    public function getAllDisputes(?string $statusFilter = null): array
    {
        $sql = "SELECT 
                    d.id,
                    d.booking_id,
                    d.reason,
                    d.details,
                    d.status,
                    d.resolution_notes,
                    d.resolved_at,
                    d.created_at,
                    b.booking_code,
                    b.total_amount,
                    u_raised.name AS raised_by_name,
                    u_raised.phone AS raised_by_phone,
                    u_raised.role AS raised_by_role,
                    u_cust.name AS customer_name,
                    u_pro.name AS pro_name
                FROM disputes d
                JOIN bookings b ON d.booking_id = b.id
                JOIN users u_raised ON d.raised_by_user_id = u_raised.id
                JOIN users u_cust ON b.customer_id = u_cust.id
                LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
                LEFT JOIN users u_pro ON pp.user_id = u_pro.id
                WHERE 1=1";

        $params = [];

        if (!empty($statusFilter)) {
            $sql .= " AND d.status = :status";
            $params['status'] = $statusFilter;
        }

        $sql .= " ORDER BY (d.status = 'open') DESC, (d.status = 'under_review') DESC, d.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getHelpdeskWorkflowItems(): array
    {
        return $this->db->fetchAll(
                "SELECT b.id AS booking_id, b.booking_code, b.status AS booking_status,
                    b.payment_status, b.payment_method, b.total_amount, b.pro_earning_credited,
                    u_cust.name AS customer_name, u_pro.name AS pro_name,
                    bp.id AS proof_id, bp.proof_type, bp.description AS proof_description,
                    bp.review_status AS proof_status, bp.created_at AS proof_submitted_at,
                    (SELECT otp.requested_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS pro_confirmation_requested_at,
                    (SELECT otp.verified_at FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS customer_confirmed_at,
                    (SELECT otp.cod_received FROM booking_completion_otps otp
                     WHERE otp.booking_id = b.id ORDER BY otp.id DESC LIMIT 1) AS cod_received,
                    (SELECT d.id FROM disputes d WHERE d.booking_id = b.id
                     AND d.status IN ('open', 'under_review') ORDER BY d.id DESC LIMIT 1) AS dispute_id,
                    (SELECT d.status FROM disputes d WHERE d.booking_id = b.id
                     AND d.status IN ('open', 'under_review') ORDER BY d.id DESC LIMIT 1) AS dispute_status,
                    (SELECT d.reason FROM disputes d WHERE d.booking_id = b.id
                     AND d.status IN ('open', 'under_review') ORDER BY d.id DESC LIMIT 1) AS dispute_reason
             FROM bookings b
             JOIN users u_cust ON u_cust.id = b.customer_id
             LEFT JOIN professional_profiles pp ON pp.id = b.professional_id
             LEFT JOIN users u_pro ON u_pro.id = pp.user_id
             LEFT JOIN booking_proofs bp ON bp.id = (
                 SELECT MAX(p.id) FROM booking_proofs p
                 WHERE p.booking_id = b.id AND p.proof_type = 'after'
             )
             WHERE b.status IN ('in_progress', 'disputed')
               AND (bp.id IS NOT NULL OR b.status = 'disputed')
             ORDER BY (bp.review_status = 'pending') DESC,
                      (b.status = 'disputed') DESC, b.updated_at DESC, b.id DESC"
        );
    }

    public function getAllReviews(?string $statusFilter = null): array
    {
        $sql = "SELECT 
                    r.id,
                    r.booking_id,
                    r.rating,
                    r.comment,
                    r.status,
                    r.admin_moderated,
                    r.created_at,
                    b.booking_code,
                    u_cust.name AS customer_name,
                    u_pro.name AS pro_name
                FROM reviews r
                JOIN bookings b ON r.booking_id = b.id
                JOIN users u_cust ON r.customer_id = u_cust.id
                JOIN professional_profiles pp ON r.professional_id = pp.id
                JOIN users u_pro ON pp.user_id = u_pro.id
                WHERE 1=1";

        $params = [];

        if (!empty($statusFilter)) {
            $sql .= " AND r.status = :status";
            $params['status'] = $statusFilter;
        }

        $sql .= " ORDER BY r.id DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function getDisputeDetail(int $disputeId): ?array
    {
        $sql = "SELECT 
                    d.*,
                    b.booking_code,
                    b.scheduled_date,
                    b.total_amount,
                    b.payment_status,
                    u_raised.name AS raised_by_name,
                    u_raised.email AS raised_by_email,
                    u_raised.phone AS raised_by_phone,
                    u_cust.name AS customer_name,
                    u_cust.phone AS customer_phone,
                    u_pro.name AS pro_name,
                    u_pro.phone AS pro_phone
                FROM disputes d
                JOIN bookings b ON d.booking_id = b.id
                JOIN users u_raised ON d.raised_by_user_id = u_raised.id
                JOIN users u_cust ON b.customer_id = u_cust.id
                LEFT JOIN professional_profiles pp ON b.professional_id = pp.id
                LEFT JOIN users u_pro ON pp.user_id = u_pro.id
                WHERE d.id = :id";

        $dispute = $this->db->fetch($sql, ['id' => $disputeId]);
        if (!$dispute) {
            return null;
        }

        // Job proofs (if any attached to booking)
        $proofsSql = "SELECT * FROM booking_proofs WHERE booking_id = :booking_id";
        $proofs = $this->db->fetchAll($proofsSql, ['booking_id' => $dispute['booking_id']]);

        return [
            'dispute' => $dispute,
            'proofs'  => $proofs
        ];
    }

    public function resolveDispute(int $disputeId, string $status, string $resolutionNotes): bool
    {
        $allowed = ['under_review', 'resolved', 'dismissed'];
        if ($disputeId <= 0 || !in_array($status, $allowed, true) || trim($resolutionNotes) === '') {
            return false;
        }
        $bookingId = 0;
        $shouldAttemptCompletion = false;
        $this->db->beginTransaction();
        try {
            $dispute = $this->db->fetch(
                "SELECT d.id, d.booking_id, d.status AS dispute_status,
                    b.status AS booking_status, b.professional_id,
                    b.pro_earning_credited
                 FROM disputes d JOIN bookings b ON b.id = d.booking_id
                 WHERE d.id = :id FOR UPDATE",
                ['id' => $disputeId]
            );
            if (!$dispute || !in_array($dispute['dispute_status'], ['open', 'under_review'], true)
                || $dispute['booking_status'] === 'cancelled') {
                $this->db->rollBack();
                return false;
            }

            $resolvedAt = $status === 'under_review' ? 'NULL' : 'CURRENT_TIMESTAMP';
            $this->db->run(
                "UPDATE disputes SET status = :status, resolution_notes = :notes,
                    resolved_at = {$resolvedAt} WHERE id = :id",
                [
                    'status' => $status,
                    'notes' => trim($resolutionNotes),
                    'id' => $disputeId,
                ]
            );

            if ($status !== 'under_review' && $dispute['booking_status'] === 'disputed') {
                $bookingId = (int)$dispute['booking_id'];
                if ((int)$dispute['pro_earning_credited'] === 1) {
                    $restoredStatus = 'completed';
                } elseif (empty($dispute['professional_id'])) {
                    $restoredStatus = 'pending';
                } else {
                    $restoredStatus = 'in_progress';
                    $shouldAttemptCompletion = true;
                }
                $this->db->run(
                    "UPDATE bookings SET status = :status WHERE id = :booking_id AND status = 'disputed'",
                    ['status' => $restoredStatus, 'booking_id' => $bookingId]
                );
            }

            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'RESOLVE_DISPUTE', 'disputes', $disputeId, null, [
                'status' => $status,
                'notes' => trim($resolutionNotes),
                'booking_status_restored' => $status === 'under_review' ? null : ($restoredStatus ?? null),
            ]);
            $this->db->commit();
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }

        if ($shouldAttemptCompletion && $bookingId > 0) {
            (new ProfessionalModel())->completeBookingAfterPayment($bookingId);
        }
        return true;
    }

    public function moderateReview(int $reviewId, string $status): bool
    {
        $allowed = ['published', 'hidden', 'flagged'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $sql = "UPDATE reviews SET status = :status, admin_moderated = 1 WHERE id = :id";
        $this->db->run($sql, ['status' => $status, 'id' => $reviewId]);

        $this->logAudit((int)$_SESSION['fixmate_user_id'], 'MODERATE_REVIEW', 'reviews', $reviewId, null, ['status' => $status]);
        return true;
    }
    // ========================================================
    // ADM-10: SYSTEM SETTINGS & AUDIT LOGS
    // ========================================================

    public function getAllSystemSettings(): array
    {
        $sql = "SELECT setting_key, setting_value, setting_group, description, updated_at 
                FROM system_settings 
                ORDER BY setting_group ASC, setting_key ASC";
        
        $rows = $this->db->fetchAll($sql);
        $settingsMap = [];
        foreach ($rows as $r) {
            $settingsMap[$r['setting_key']] = $r['setting_value'];
        }
        return $settingsMap;
    }

    public function getSystemAuditLogs(int $limit = 50): array
    {
        $sql = "SELECT 
                    a.id,
                    a.action,
                    a.entity_type,
                    a.entity_id,
                    a.old_values,
                    a.new_values,
                    a.ip_address,
                    a.created_at,
                    COALESCE(u.name, 'System Automatic') AS actor_name,
                    COALESCE(u.role, 'system') AS actor_role
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY a.id DESC
                LIMIT :limit";

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateBulkSettings(array $settings, string $group = 'general'): bool
    {
        $this->db->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $sql = "INSERT INTO system_settings (setting_key, setting_value, setting_group, description) 
                        VALUES (:key, :val, :grp, 'Platform configuration parameter')
                        ON DUPLICATE KEY UPDATE setting_value = :val_update, setting_group = :grp_update";
                
                $this->db->run($sql, [
                    'key'        => $key,
                    'val'        => (string)$value,
                    'grp'        => $group,
                    'val_update' => (string)$value,
                    'grp_update' => $group
                ]);
            }

            $this->db->commit();
            $this->logAudit((int)$_SESSION['fixmate_user_id'], 'UPDATE_SETTINGS', 'system_settings', null, null, $settings);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}