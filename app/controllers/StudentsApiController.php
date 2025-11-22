<?php
// FILE: /app/controllers/StudentsApiController.php

class StudentsApiController extends ApiController {
    private $studentModel;

    public function __construct() {
        parent::__construct();
        $this->studentModel = $this->model('Student');
        $this->studentModel->setTenantId($this->tenantId);
    }

    public function get() {
        $studentCode = $_GET['student_code'] ?? '';

        if (empty($studentCode)) {
            $this->jsonError('student_code parameter is required');
        }

        $student = $this->studentModel->findByCode($studentCode);

        if (!$student) {
            $this->jsonError('Student not found', 404);
        }

        // Remove sensitive data
        unset($student['tenant_id']);

        $this->jsonSuccess($student, 'Student retrieved successfully');
    }

    public function list() {
        $classId = $_GET['class_id'] ?? null;
        $status = $_GET['status'] ?? 'active';

        if ($classId) {
            $students = $this->studentModel->getByClass($classId);
        } else {
            $students = $this->studentModel->where(['status' => $status]);
        }

        $this->jsonSuccess([
            'count' => count($students),
            'students' => $students
        ], 'Students retrieved successfully');
    }
}
