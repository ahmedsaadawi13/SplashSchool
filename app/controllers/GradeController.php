<?php
// FILE: /app/controllers/GradeController.php

class GradeController extends Controller {
    private $gradeModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin', 'teacher']);
        $this->gradeModel = $this->model('Grade');
    }

    public function index() {
        $examModel = $this->model('Exam');
        $exams = $examModel->all();
        $this->view('grades/index', ['exams' => $exams]);
    }

    public function submit() {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('grades/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'student_id' => $_POST['student_id'] ?? null,
                'exam_id' => $_POST['exam_id'] ?? null,
                'subject_id' => $_POST['subject_id'] ?? null,
                'marks_obtained' => $_POST['marks_obtained'] ?? 0,
                'teacher_id' => $this->userId,
                'remarks' => Validator::sanitize($_POST['remarks'] ?? '')
            ];

            // Calculate grade
            $maxMarks = $_POST['max_marks'] ?? 100;
            $data['grade'] = $this->gradeModel->calculateGrade($data['marks_obtained'], $maxMarks);

            if ($this->gradeModel->submitGrade($data)) {
                $_SESSION['success'] = 'Grade submitted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to submit grade.';
            }

            $this->redirect('grades/index');
        }
    }

    public function studentReport($studentId) {
        $studentModel = $this->model('Student');
        $student = $studentModel->find($studentId);

        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            $this->redirect('grades/index');
        }

        $examModel = $this->model('Exam');
        $exams = $examModel->all();

        $data = [
            'student' => $student,
            'exams' => $exams,
            'grades' => []
        ];

        if (!empty($exams)) {
            foreach ($exams as $exam) {
                $data['grades'][$exam['id']] = $this->gradeModel->getByStudentAndExam($studentId, $exam['id']);
            }
        }

        $this->view('grades/student_report', $data);
    }
}
