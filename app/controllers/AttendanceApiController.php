<?php
// FILE: /app/controllers/AttendanceApiController.php

class AttendanceApiController extends ApiController {
    private $attendanceModel;
    private $studentModel;

    public function __construct() {
        parent::__construct();
        $this->attendanceModel = $this->model('Attendance');
        $this->attendanceModel->setTenantId($this->tenantId);
        $this->studentModel = $this->model('Student');
        $this->studentModel->setTenantId($this->tenantId);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $studentCode = $input['student_code'] ?? '';
        $date = $input['date'] ?? date('Y-m-d');
        $status = $input['status'] ?? 'present';

        // Validate input
        if (empty($studentCode)) {
            $this->jsonError('student_code is required');
        }

        if (!in_array($status, ['present', 'absent', 'late', 'excused'])) {
            $this->jsonError('Invalid status. Must be: present, absent, late, or excused');
        }

        // Find student
        $student = $this->studentModel->findByCode($studentCode);
        if (!$student) {
            $this->jsonError('Student not found', 404);
        }

        // Mark attendance
        $data = [
            'student_id' => $student['id'],
            'class_id' => $student['class_id'],
            'section_id' => $student['section_id'],
            'date' => $date,
            'status' => $status,
            'marked_by' => null
        ];

        $result = $this->attendanceModel->markAttendance($data);

        if ($result) {
            $this->jsonSuccess([
                'student_code' => $studentCode,
                'date' => $date,
                'status' => $status
            ], 'Attendance recorded successfully');
        } else {
            $this->jsonError('Failed to record attendance', 500);
        }
    }
}
