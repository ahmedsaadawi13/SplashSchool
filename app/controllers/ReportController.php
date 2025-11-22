<?php
// FILE: /app/controllers/ReportController.php

class ReportController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin', 'teacher', 'accountant']);
    }

    public function index() {
        $this->view('reports/index');
    }

    public function students() {
        $studentModel = $this->model('Student');
        $classId = isset($_GET['class_id']) ? $_GET['class_id'] : null;
        $status = isset($_GET['status']) ? $_GET['status'] : 'active';

        if ($classId) {
            $students = $studentModel->getByClass($classId);
        } else {
            $students = $studentModel->where(['status' => $status]);
        }

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportCSV('students', $students);
        }

        $classModel = $this->model('ClassModel');
        $data = [
            'students' => $students,
            'classes' => $classModel->getActive()
        ];

        $this->view('reports/students', $data);
    }

    public function attendance() {
        $attendanceModel = $this->model('Attendance');
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        $classId = isset($_GET['class_id']) ? $_GET['class_id'] : null;

        $sql = "SELECT a.*, s.full_name, s.student_code, c.name as class_name
                FROM attendance a
                INNER JOIN students s ON a.student_id = s.id
                INNER JOIN classes c ON a.class_id = c.id
                WHERE a.tenant_id = :tenant_id
                AND a.date BETWEEN :start_date AND :end_date";

        $params = [
            ':tenant_id' => $this->tenantId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];

        if ($classId) {
            $sql .= " AND a.class_id = :class_id";
            $params[':class_id'] = $classId;
        }

        $sql .= " ORDER BY a.date DESC, s.full_name ASC";

        $attendance = $attendanceModel->query($sql, $params);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportCSV('attendance', $attendance);
        }

        $classModel = $this->model('ClassModel');
        $data = [
            'attendance' => $attendance,
            'classes' => $classModel->getActive(),
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        $this->view('reports/attendance', $data);
    }

    public function fees() {
        $this->requireRole(['school_admin', 'accountant']);

        $invoiceModel = $this->model('Invoice');
        $status = isset($_GET['status']) ? $_GET['status'] : 'unpaid';

        $sql = "SELECT i.*, s.full_name as student_name, s.student_code
                FROM invoices i
                INNER JOIN students s ON i.student_id = s.id
                WHERE i.tenant_id = :tenant_id AND i.status = :status
                ORDER BY i.due_date ASC";

        $invoices = $invoiceModel->query($sql, [
            ':tenant_id' => $this->tenantId,
            ':status' => $status
        ]);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $this->exportCSV('fees', $invoices);
        }

        $this->view('reports/fees', ['invoices' => $invoices, 'status' => $status]);
    }

    private function exportCSV($filename, $data) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));

            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        fclose($output);
        exit;
    }
}
