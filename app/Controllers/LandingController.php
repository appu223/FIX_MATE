<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\CustomerModel;

class LandingController extends Controller
{
    private Database $db;
    private CustomerModel $customerModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->customerModel = new CustomerModel();
    }

    /**
     * Public self-service customer registration (landing page sign-up modal).
     * Returns JSON so the front-end can render inline feedback.
     */
    public function registerCustomer(): void
    {
        $name     = trim((string)$this->post('name', ''));
        $email    = strtolower(trim((string)$this->post('email', '')));
        $phone    = trim((string)$this->post('phone', ''));
        $password = (string)$this->post('password', '');
        $city     = trim((string)$this->post('city', 'Bengaluru'));
        $address  = trim((string)$this->post('address', ''));

        if ($name === '' || mb_strlen($name) < 2) {
            $this->json(['success' => false, 'message' => 'Please enter your full name.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 400);
        }
        if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid 10-digit Indian mobile number.'], 400);
        }
        if (strlen($password) < 6) {
            $this->json(['success' => false, 'message' => 'Password must be at least 6 characters long.'], 400);
        }

        $result = $this->customerModel->registerCustomer($name, $email, $phone, $password, $city, $address);
        if (isset($result['error'])) {
            $this->json(['success' => false, 'message' => $result['error']], 400);
        }

        // Log the new customer straight in.
        Auth::login($result['user']);

        $this->json([
            'success'  => true,
            'message'  => 'Welcome aboard, ' . $name . '! Your account is ready.',
            'redirect' => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/customer'
        ]);
    }

    public function index(): void
    {
        // 1. Fetch Categories for Dynamic Display
        $categories = $this->db->fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6");

        // 2. Fetch Popular Services
        $popularServices = $this->db->fetchAll("
            SELECT s.*, c.name AS category_name, c.icon AS category_icon
            FROM services s
            JOIN categories c ON s.category_id = c.id
            WHERE s.status = 'active' AND s.is_popular = 1
            LIMIT 4
        ");

        // 3. Fetch Top Verified Pros
        $topPros = $this->db->fetchAll("
            SELECT pp.*, u.name, u.avatar
            FROM professional_profiles pp
            JOIN users u ON pp.user_id = u.id
            WHERE pp.kyc_status = 'verified' AND u.status = 'active'
            ORDER BY pp.rating_avg DESC LIMIT 4
        ");

        // 4. Platform Counters
        $stats = [
            'happy_customers'  => '42,500+',
            'verified_pros'    => '3,800+',
            'completed_repairs'=> '98,400+',
            'cities_active'    => '14+'
        ];

        $this->view('landing/index', [
            'pageTitle'       => 'FixMate - India’s Premier On-Demand Home Repair Network',
            'categories'      => $categories,
            'popularServices' => $popularServices,
            'topPros'         => $topPros,
            'stats'           => $stats,
            'defaultWorkspaceRole' => in_array((string)$this->get('workspace', ''), ['customer', 'professional', 'admin'], true)
                ? (string)$this->get('workspace', '')
                : ''
        ], 'landing');
    }

    public function workspaces(): void
    {
        $this->view('landing/workspaces', [
            'pageTitle' => 'Open Fixmate Workspaces'
        ], 'plain');
    }

    public function processLogin(): void
    {
        $email    = trim((string)$this->post('email', ''));
        $password = trim((string)$this->post('password', ''));
        $role     = trim((string)$this->post('target_role', ''));

        if (empty($email) || empty($password)) {
            $this->json(['success' => false, 'message' => 'Please provide email and password.'], 400);
        }

        // Demo fallback accounts for zero friction
        $demoAccounts = [
            'admin' => [
                'id'    => 1,
                'name'  => 'Rajesh Sharma',
                'email' => 'admin@fixmate.in',
                'role'  => 'admin',
                'redirect' => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/admin/dashboard'
            ],
            'customer' => [
                'id'    => 7,
                'name'  => 'Priya Patel',
                'email' => 'priya@gmail.com',
                'role'  => 'customer',
                'redirect' => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/customer'
            ],
            'professional' => [
                'id'    => 3,
                'name'  => 'Amit Kumar',
                'email' => 'amit.electric@fixmate.in',
                'role'  => 'professional',
                'redirect' => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/pro/dashboard'
            ]
        ];

        // Check if demo user
        if ($password === 'Password@123' && isset($demoAccounts[$role])) {
            Auth::login($demoAccounts[$role]);
            $this->json([
                'success'  => true,
                'message'  => 'Authenticated successfully!',
                'redirect' => $demoAccounts[$role]['redirect']
            ]);
        }

        // Query Database
        $user = $this->db->fetch("SELECT id, name, email, role, password_hash, status FROM users WHERE email = :email", ['email' => $email]);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if ($user['status'] === 'suspended') {
            $this->json(['success' => false, 'message' => 'Your account is suspended. Contact support.'], 403);
        }

        $requestedRole = $role === 'admin' ? ['admin', 'staff'] : [$role];
        if ($role !== '' && !in_array($user['role'], $requestedRole, true)) {
            $this->json(['success' => false, 'message' => 'These credentials belong to a different Fixmate workspace. Choose the matching panel and try again.'], 403);
        }

        Auth::login([
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role']
        ]);

        $redirectUrl = match($user['role']) {
            'admin', 'staff' => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/admin/dashboard',
            'professional'   => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/pro/dashboard',
            default          => (defined('APP_BASE_PATH') ? APP_BASE_PATH : '') . '/customer'
        };

        $this->json([
            'success'  => true,
            'message'  => 'Welcome back, ' . $user['name'],
            'redirect' => $redirectUrl
        ]);
    }
}