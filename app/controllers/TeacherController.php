<?php
// FILE: /app/controllers/TeacherController.php

class TeacherController extends Controller {
    private $teacherModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin']);
        $this->teacherModel = $this->model('Teacher');
    }

    public function index() {
        $teachers = $this->teacherModel->getActive();
        $this->view('teachers/index', ['teachers' => $teachers]);
    }

    public function add() {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('teachers/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'teacher_code' => Validator::sanitize($_POST['teacher_code'] ?? ''),
                'name' => Validator::sanitize($_POST['name'] ?? ''),
                'email' => Validator::sanitize($_POST['email'] ?? ''),
                'phone' => Validator::sanitize($_POST['phone'] ?? ''),
                'hire_date' => $_POST['hire_date'] ?? date('Y-m-d'),
                'specialization' => Validator::sanitize($_POST['specialization'] ?? ''),
                'qualification' => Validator::sanitize($_POST['qualification'] ?? ''),
                'status' => 'active'
            ];

            $validator = new Validator($data);
            if (!$validator->validate([
                'teacher_code' => 'required',
                'name' => 'required',
                'email' => 'required|email',
                'hire_date' => 'required|date'
            ])) {
                $_SESSION['error'] = $validator->getFirstError();
                $this->redirect('teachers/add');
            }

            if ($this->teacherModel->create($data)) {
                $_SESSION['success'] = 'Teacher added successfully.';
                $this->redirect('teachers/index');
            } else {
                $_SESSION['error'] = 'Failed to add teacher.';
            }
        }

        $this->view('teachers/add');
    }

    public function view($id) {
        $teacher = $this->teacherModel->find($id);
        if (!$teacher) {
            $_SESSION['error'] = 'Teacher not found.';
            $this->redirect('teachers/index');
        }

        $this->view('teachers/view', ['teacher' => $teacher]);
    }
}
