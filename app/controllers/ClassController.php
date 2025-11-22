<?php
// FILE: /app/controllers/ClassController.php

class ClassController extends Controller {
    private $classModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin']);
        $this->classModel = $this->model('ClassModel');
    }

    public function index() {
        $classes = $this->classModel->getActive();
        $this->view('classes/index', ['classes' => $classes]);
    }

    public function add() {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('classes/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'name' => Validator::sanitize($_POST['name'] ?? ''),
                'academic_year' => Validator::sanitize($_POST['academic_year'] ?? date('Y')),
                'description' => Validator::sanitize($_POST['description'] ?? ''),
                'status' => 'active'
            ];

            $validator = new Validator($data);
            if (!$validator->validate(['name' => 'required', 'academic_year' => 'required'])) {
                $_SESSION['error'] = $validator->getFirstError();
                $this->redirect('classes/add');
            }

            if ($this->classModel->create($data)) {
                $_SESSION['success'] = 'Class added successfully.';
                $this->redirect('classes/index');
            } else {
                $_SESSION['error'] = 'Failed to add class.';
            }
        }

        $this->view('classes/add');
    }

    public function sections($classId) {
        $class = $this->classModel->find($classId);
        if (!$class) {
            $_SESSION['error'] = 'Class not found.';
            $this->redirect('classes/index');
        }

        $sections = $this->classModel->getSections($classId);
        $this->view('classes/sections', ['class' => $class, 'sections' => $sections]);
    }

    public function addSection($classId) {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('classes/sections/' . $classId);
        }

        $class = $this->classModel->find($classId);
        if (!$class) {
            $_SESSION['error'] = 'Class not found.';
            $this->redirect('classes/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $sectionModel = $this->model('Section');
            $data = [
                'class_id' => $classId,
                'name' => Validator::sanitize($_POST['name'] ?? ''),
                'capacity' => $_POST['capacity'] ?? 30,
                'status' => 'active'
            ];

            if ($sectionModel->create($data)) {
                $_SESSION['success'] = 'Section added successfully.';
                $this->redirect('classes/sections/' . $classId);
            } else {
                $_SESSION['error'] = 'Failed to add section.';
            }
        }

        $this->view('classes/add_section', ['class' => $class]);
    }
}
