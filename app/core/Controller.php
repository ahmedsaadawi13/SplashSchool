<?php
// FILE: /app/core/Controller.php

class Controller {
    protected $tenantId = null;
    protected $userId = null;
    protected $userRole = null;

    public function __construct() {
        // Initialize tenant and user context from session
        if (isset($_SESSION['tenant_id'])) {
            $this->tenantId = $_SESSION['tenant_id'];
        }
        if (isset($_SESSION['user_id'])) {
            $this->userId = $_SESSION['user_id'];
        }
        if (isset($_SESSION['user_role'])) {
            $this->userRole = $_SESSION['user_role'];
        }
    }

    protected function view($view, $data = []) {
        extract($data);

        $viewFile = APP . '/views/' . $view . '.php';

        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View not found: $view");
        }
    }

    protected function model($model) {
        $modelFile = APP . '/models/' . $model . '.php';

        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Model not found: $model");
        }
    }

    protected function redirect($url) {
        header('Location: ' . BASE_URL . '/' . $url);
        exit;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    protected function requireRole($allowedRoles) {
        $this->requireAuth();

        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }

        if (!in_array($this->userRole, $allowedRoles)) {
            $_SESSION['error'] = 'Access denied. Insufficient permissions.';
            $this->redirect('dashboard/index');
        }
    }

    protected function requireTenant() {
        $this->requireAuth();

        if (empty($this->tenantId)) {
            $_SESSION['error'] = 'Tenant context required.';
            $this->redirect('dashboard/index');
        }
    }

    protected function checkSubscription() {
        if ($this->userRole === 'platform_admin') {
            return true;
        }

        if (empty($this->tenantId)) {
            return false;
        }

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->find($this->tenantId);

        if (!$tenant) {
            return false;
        }

        // Allow read-only if subscription is not active
        if (!in_array($tenant['subscription_status'], ['trialing', 'active'])) {
            return false;
        }

        return true;
    }

    protected function isReadOnly() {
        if ($this->userRole === 'platform_admin') {
            return false;
        }

        return !$this->checkSubscription();
    }
}
