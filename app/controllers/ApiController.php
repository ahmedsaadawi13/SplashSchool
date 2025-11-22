<?php
// FILE: /app/controllers/ApiController.php

class ApiController extends Controller {
    protected $tenantId = null;

    public function __construct() {
        parent::__construct();
        $this->authenticateAPI();
    }

    protected function authenticateAPI() {
        $apiKey = $this->getAPIKey();

        if (!$apiKey) {
            $this->jsonError('API key required', 401);
        }

        // Validate API key and get tenant
        $tenantModel = $this->model('Tenant');
        $sql = "SELECT * FROM tenants WHERE api_key = :api_key AND status = 'active' LIMIT 1";
        $stmt = $tenantModel->query($sql, [':api_key' => $apiKey]);
        $tenant = !empty($stmt) ? $stmt[0] : null;

        if (!$tenant) {
            $this->jsonError('Invalid API key', 401);
        }

        // Check subscription status
        if (!in_array($tenant['subscription_status'], ['trialing', 'active'])) {
            $this->jsonError('Subscription not active', 403);
        }

        $this->tenantId = $tenant['id'];
        $_SESSION['tenant_id'] = $tenant['id'];
    }

    protected function getAPIKey() {
        // Check X-API-KEY header
        $headers = getallheaders();
        if (isset($headers['X-API-KEY'])) {
            return $headers['X-API-KEY'];
        }

        // Check query parameter
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }

        return null;
    }

    protected function jsonSuccess($data, $message = 'Success', $statusCode = 200) {
        $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    protected function jsonError($message, $statusCode = 400) {
        $this->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $statusCode);
    }

    public function index() {
        $this->jsonSuccess([
            'version' => '1.0',
            'endpoints' => [
                '/api/attendance/add' => 'Add attendance entry (POST)',
                '/api/students/get' => 'Get student info (GET)',
                '/api/grades/add' => 'Submit grade (POST)'
            ]
        ], 'SplashSchool API v1.0');
    }
}
