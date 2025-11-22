<?php
// FILE: /app/controllers/AttendanceController.php

class AttendanceController extends Controller {
    private $attendanceModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin', 'teacher']);
        $this->attendanceModel = $this->model('Attendance');
    }

    public function index() {
        $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
        $classId = isset($_GET['class_id']) ? $_GET['class_id'] : null;
        $sectionId = isset($_GET['section_id']) ? $_GET['section_id'] : null;

        $classModel = $this->model('ClassModel');
        $data = [
            'classes' => $classModel->getActive(),
            'date' => $date,
            'selected_class' => $classId,
            'selected_section' => $sectionId,
            'attendance' => []
        ];

        if ($classId && $sectionId) {
            $data['attendance'] = $this->attendanceModel->getByClassAndDate($classId, $sectionId, $date);
        }

        $this->view('attendance/index', $data);
    }

    public function mark() {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('attendance/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $date = $_POST['date'] ?? date('Y-m-d');
            $classId = $_POST['class_id'] ?? null;
            $sectionId = $_POST['section_id'] ?? null;
            $attendance = $_POST['attendance'] ?? [];

            if (empty($attendance)) {
                $_SESSION['error'] = 'No attendance data submitted.';
                $this->redirect('attendance/index');
            }

            $count = 0;
            foreach ($attendance as $studentId => $status) {
                $data = [
                    'student_id' => $studentId,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'date' => $date,
                    'status' => $status,
                    'marked_by' => $this->userId
                ];

                if ($this->attendanceModel->markAttendance($data)) {
                    $count++;
                }
            }

            $_SESSION['success'] = "Attendance marked for $count students.";
            $this->redirect('attendance/index?date=' . $date . '&class_id=' . $classId . '&section_id=' . $sectionId);
        }

        $this->redirect('attendance/index');
    }

    public function report() {
        $studentModel = $this->model('Student');
        $studentId = isset($_GET['student_id']) ? $_GET['student_id'] : null;
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

        $data = [
            'students' => $studentModel->all(),
            'student_id' => $studentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'attendance' => []
        ];

        if ($studentId) {
            $data['attendance'] = $this->attendanceModel->getStudentAttendance($studentId, $startDate, $endDate);
        }

        $this->view('attendance/report', $data);
    }
}
