<?php
// FILE: /app/controllers/AuthController.php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    public function login() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $username = Validator::sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation
            $validator = new Validator($_POST);
            if (!$validator->validate([
                'username' => 'required',
                'password' => 'required'
            ])) {
                $_SESSION['error'] = $validator->getFirstError();
                $this->redirect('auth/login');
            }

            // Attempt login
            $user = $this->userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if user is active
                if ($user['status'] !== 'active') {
                    $_SESSION['error'] = 'Your account is inactive. Please contact administrator.';
                    $this->redirect('auth/login');
                }

                // Check subscription for non-platform admins
                if ($user['role'] !== 'platform_admin' && $user['tenant_id']) {
                    $tenantModel = $this->model('Tenant');
                    $tenant = $tenantModel->find($user['tenant_id']);

                    if (!$tenant || !in_array($tenant['subscription_status'], ['trialing', 'active'])) {
                        $_SESSION['error'] = 'School subscription is not active. Please contact administrator.';
                        $this->redirect('auth/login');
                    }
                }

                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['tenant_id'] = $user['tenant_id'];

                // Update last login
                $this->userModel->updateLastLogin($user['id']);

                // Redirect based on role
                $this->redirect('dashboard/index');
            } else {
                $_SESSION['error'] = 'Invalid username or password.';
                $this->redirect('auth/login');
            }
        }

        $this->view('auth/login');
    }

    public function logout() {
        Session::destroy();
        $this->redirect('auth/login');
    }

    public function register() {
        // Registration is only for creating new schools (tenants)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'school_name' => Validator::sanitize($_POST['school_name'] ?? ''),
                'subdomain' => Validator::sanitize($_POST['subdomain'] ?? ''),
                'email' => Validator::sanitize($_POST['email'] ?? ''),
                'phone' => Validator::sanitize($_POST['phone'] ?? ''),
                'admin_name' => Validator::sanitize($_POST['admin_name'] ?? ''),
                'admin_email' => Validator::sanitize($_POST['admin_email'] ?? ''),
                'admin_username' => Validator::sanitize($_POST['admin_username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? ''
            ];

            // Validation
            $validator = new Validator($data);
            if (!$validator->validate([
                'school_name' => 'required|min:3',
                'subdomain' => 'required|min:3|alphanumeric',
                'email' => 'required|email',
                'phone' => 'required',
                'admin_name' => 'required',
                'admin_email' => 'required|email',
                'admin_username' => 'required|min:4',
                'password' => 'required|min:6'
            ])) {
                $_SESSION['error'] = $validator->getFirstError();
                $_SESSION['form_data'] = $data;
                $this->redirect('auth/register');
            }

            // Check password confirmation
            if ($data['password'] !== $data['confirm_password']) {
                $_SESSION['error'] = 'Passwords do not match.';
                $_SESSION['form_data'] = $data;
                $this->redirect('auth/register');
            }

            // Check subdomain uniqueness
            $tenantModel = $this->model('Tenant');
            if ($tenantModel->findBySubdomain($data['subdomain'])) {
                $_SESSION['error'] = 'Subdomain already exists. Please choose another.';
                $_SESSION['form_data'] = $data;
                $this->redirect('auth/register');
            }

            // Check username uniqueness
            if ($this->userModel->findByUsername($data['admin_username'])) {
                $_SESSION['error'] = 'Username already exists. Please choose another.';
                $_SESSION['form_data'] = $data;
                $this->redirect('auth/register');
            }

            // Create tenant
            $apiKey = hash('sha256', $data['subdomain'] . time());
            $trialEndsAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $tenantId = $tenantModel->create([
                'name' => $data['school_name'],
                'subdomain' => $data['subdomain'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'subscription_plan_id' => 1, // Free trial
                'subscription_status' => 'trialing',
                'trial_ends_at' => $trialEndsAt,
                'api_key' => $apiKey,
                'status' => 'active'
            ]);

            if ($tenantId) {
                // Create admin user
                $userId = $this->userModel->createUser([
                    'tenant_id' => $tenantId,
                    'username' => $data['admin_username'],
                    'email' => $data['admin_email'],
                    'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                    'role' => 'school_admin',
                    'full_name' => $data['admin_name'],
                    'status' => 'active'
                ]);

                // Initialize tenant usage
                $usageModel = $this->model('TenantUsage');
                $usageModel->initialize($tenantId);

                $_SESSION['success'] = 'School registered successfully! You can now login.';
                $this->redirect('auth/login');
            } else {
                $_SESSION['error'] = 'Failed to register school. Please try again.';
                $this->redirect('auth/register');
            }
        }

        $data = [
            'form_data' => Session::flash('form_data') ?? []
        ];

        $this->view('auth/register', $data);
    }
}
