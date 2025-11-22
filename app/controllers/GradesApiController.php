<?php
// FILE: /app/controllers/GradesApiController.php

class GradesApiController extends ApiController {
    private $gradeModel;
    private $studentModel;
    private $subjectModel;

    public function __construct() {
        parent::__construct();
        $this->gradeModel = $this->model('Grade');
        $this->gradeModel->setTenantId($this->tenantId);
        $this->studentModel = $this->model('Student');
        $this->studentModel->setTenantId($this->tenantId);
        $this->subjectModel = $this->model('Subject');
        $this->subjectModel->setTenantId($this->tenantId);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $studentCode = $input['student_code'] ?? '';
        $subjectCode = $input['subject_code'] ?? '';
        $examId = $input['exam_id'] ?? null;
        $marksObtained = $input['marks'] ?? 0;
        $maxMarks = $input['max_marks'] ?? 100;

        // Validate
        if (empty($studentCode) || empty($subjectCode) || empty($examId)) {
            $this->jsonError('student_code, subject_code, and exam_id are required');
        }

        // Find student
        $student = $this->studentModel->findByCode($studentCode);
        if (!$student) {
            $this->jsonError('Student not found', 404);
        }

        // Find subject
        $subject = $this->subjectModel->findByCode($subjectCode);
        if (!$subject) {
            $this->jsonError('Subject not found', 404);
        }

        // Calculate grade
        $grade = $this->gradeModel->calculateGrade($marksObtained, $maxMarks);

        // Submit grade
        $data = [
            'student_id' => $student['id'],
            'exam_id' => $examId,
            'subject_id' => $subject['id'],
            'marks_obtained' => $marksObtained,
            'grade' => $grade
        ];

        $result = $this->gradeModel->submitGrade($data);

        if ($result) {
            $this->jsonSuccess([
                'student_code' => $studentCode,
                'subject_code' => $subjectCode,
                'exam_id' => $examId,
                'marks_obtained' => $marksObtained,
                'grade' => $grade
            ], 'Grade submitted successfully');
        } else {
            $this->jsonError('Failed to submit grade', 500);
        }
    }
}
