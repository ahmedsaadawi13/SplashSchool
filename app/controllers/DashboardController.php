<?php
// FILE: /app/controllers/DashboardController.php

class DashboardController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
    }

    public function index() {
        $data = [];

        // Load role-specific dashboard
        switch ($this->userRole) {
            case 'platform_admin':
                $data = $this->platformAdminDashboard();
                break;
            case 'school_admin':
                $data = $this->schoolAdminDashboard();
                break;
            case 'teacher':
                $data = $this->teacherDashboard();
                break;
            case 'parent':
                $data = $this->parentDashboard();
                break;
            case 'accountant':
                $data = $this->accountantDashboard();
                break;
            case 'receptionist':
                $data = $this->receptionistDashboard();
                break;
            case 'student_portal':
                $data = $this->studentDashboard();
                break;
            default:
                $data['error'] = 'Unknown user role';
        }

        $this->view('dashboard/' . $this->userRole, $data);
    }

    private function platformAdminDashboard() {
        $tenantModel = $this->model('Tenant');

        return [
            'total_tenants' => $tenantModel->count(),
            'active_tenants' => $tenantModel->countByStatus('active'),
            'trialing_tenants' => $tenantModel->countBySubscriptionStatus('trialing'),
            'recent_tenants' => $tenantModel->getRecent(5)
        ];
    }

    private function schoolAdminDashboard() {
        $studentModel = $this->model('Student');
        $teacherModel = $this->model('Teacher');
        $attendanceModel = $this->model('Attendance');
        $invoiceModel = $this->model('Invoice');

        $today = date('Y-m-d');

        return [
            'total_students' => $studentModel->count(),
            'total_teachers' => $teacherModel->count(),
            'attendance_today' => $attendanceModel->getTodaySummary($today),
            'unpaid_invoices' => $invoiceModel->countByStatus('unpaid'),
            'total_unpaid_amount' => $invoiceModel->getTotalUnpaidAmount()
        ];
    }

    private function teacherDashboard() {
        $teacherModel = $this->model('Teacher');
        $homeworkModel = $this->model('Homework');
        $attendanceModel = $this->model('Attendance');

        // Get teacher record
        $teacher = $teacherModel->findByUserId($this->userId);

        $data = [
            'teacher' => $teacher,
            'pending_attendance' => 0,
            'homework_assigned' => 0
        ];

        if ($teacher) {
            $data['homework_assigned'] = $homeworkModel->countByTeacher($teacher['id']);
            $data['pending_attendance'] = $attendanceModel->getPendingCountForTeacher($teacher['id']);
        }

        return $data;
    }

    private function parentDashboard() {
        $parentModel = $this->model('ParentModel');
        $studentModel = $this->model('Student');
        $invoiceModel = $this->model('Invoice');

        // Get parent record
        $parent = $parentModel->findByUserId($this->userId);
        $data = ['children' => []];

        if ($parent) {
            $children = $parentModel->getChildren($parent['id']);
            $data['children'] = $children;

            // Get unpaid invoices for children
            $unpaidInvoices = [];
            foreach ($children as $child) {
                $invoices = $invoiceModel->getUnpaidByStudent($child['id']);
                $unpaidInvoices = array_merge($unpaidInvoices, $invoices);
            }
            $data['unpaid_invoices'] = $unpaidInvoices;
        }

        return $data;
    }

    private function accountantDashboard() {
        $invoiceModel = $this->model('Invoice');
        $paymentModel = $this->model('Payment');

        return [
            'total_invoices' => $invoiceModel->count(),
            'unpaid_invoices' => $invoiceModel->countByStatus('unpaid'),
            'paid_invoices' => $invoiceModel->countByStatus('paid'),
            'total_collected' => $paymentModel->getTotalCollected(),
            'recent_payments' => $paymentModel->getRecent(10)
        ];
    }

    private function receptionistDashboard() {
        $studentModel = $this->model('Student');

        return [
            'total_students' => $studentModel->count(),
            'active_students' => $studentModel->countByStatus('active'),
            'recent_admissions' => $studentModel->getRecentAdmissions(10)
        ];
    }

    private function studentDashboard() {
        // For student portal
        return [
            'message' => 'Student dashboard - view only access'
        ];
    }
}
