<?php
// FILE: /app/controllers/StudentController.php

class StudentController extends Controller {
    private $studentModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin', 'receptionist', 'teacher']);
        $this->studentModel = $this->model('Student');
    }

    public function index() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';

        if ($search) {
            $students = $this->studentModel->search($search);
            $total = count($students);
        } else {
            $total = $this->studentModel->count(['status' => 'active']);
            $paginator = new Paginator($total, ITEMS_PER_PAGE, $page);

            $sql = "SELECT s.*, c.name as class_name, sec.name as section_name
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE s.tenant_id = :tenant_id AND s.status = 'active'
                    ORDER BY s.full_name ASC
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->studentModel->query($sql, [
                ':tenant_id' => $this->tenantId,
                ':limit' => $paginator->getLimit(),
                ':offset' => $paginator->getOffset()
            ]);

            $students = $stmt;
        }

        $data = [
            'students' => $students,
            'total' => $total,
            'search' => $search
        ];

        $this->view('students/index', $data);
    }

    public function add() {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired. Cannot add students.';
            $this->redirect('students/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'student_code' => Validator::sanitize($_POST['student_code'] ?? ''),
                'first_name' => Validator::sanitize($_POST['first_name'] ?? ''),
                'last_name' => Validator::sanitize($_POST['last_name'] ?? ''),
                'gender' => $_POST['gender'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'nationality' => Validator::sanitize($_POST['nationality'] ?? ''),
                'class_id' => $_POST['class_id'] ?? null,
                'section_id' => $_POST['section_id'] ?? null,
                'parent_id' => $_POST['parent_id'] ?? null,
                'address' => Validator::sanitize($_POST['address'] ?? ''),
                'phone' => Validator::sanitize($_POST['phone'] ?? ''),
                'email' => Validator::sanitize($_POST['email'] ?? ''),
                'admission_date' => $_POST['admission_date'] ?? date('Y-m-d'),
                'status' => 'active'
            ];

            $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];

            // Validation
            $validator = new Validator($data);
            if (!$validator->validate([
                'student_code' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'gender' => 'required|in:male,female',
                'date_of_birth' => 'required|date',
                'admission_date' => 'required|date'
            ])) {
                $_SESSION['error'] = $validator->getFirstError();
                $_SESSION['form_data'] = $data;
                $this->redirect('students/add');
            }

            // Check if student code is unique
            if ($this->studentModel->findByCode($data['student_code'])) {
                $_SESSION['error'] = 'Student code already exists.';
                $_SESSION['form_data'] = $data;
                $this->redirect('students/add');
            }

            // Handle photo upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload = new Upload($_FILES['photo']);
                $upload->setAllowedTypes(ALLOWED_IMAGE_TYPES)->setMaxSize(MAX_UPLOAD_SIZE);
                $result = $upload->upload('students');

                if ($result) {
                    $data['photo'] = $result['filename'];
                } else {
                    $_SESSION['error'] = $upload->getFirstError();
                    $_SESSION['form_data'] = $data;
                    $this->redirect('students/add');
                }
            }

            $studentId = $this->studentModel->create($data);

            if ($studentId) {
                $_SESSION['success'] = 'Student added successfully.';
                $this->redirect('students/view/' . $studentId);
            } else {
                $_SESSION['error'] = 'Failed to add student.';
                $this->redirect('students/add');
            }
        }

        $classModel = $this->model('ClassModel');
        $parentModel = $this->model('ParentModel');

        $data = [
            'classes' => $classModel->getActive(),
            'parents' => $parentModel->all(),
            'form_data' => Session::flash('form_data') ?? []
        ];

        $this->view('students/add', $data);
    }

    public function view($id) {
        $student = $this->studentModel->find($id);

        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            $this->redirect('students/index');
        }

        // Get additional data
        $attendanceModel = $this->model('Attendance');
        $gradeModel = $this->model('Grade');
        $invoiceModel = $this->model('Invoice');

        $data = [
            'student' => $student,
            'recent_attendance' => $attendanceModel->getStudentAttendance($id, null, null),
            'invoices' => $invoiceModel->getByStudent($id)
        ];

        $this->view('students/view', $data);
    }

    public function edit($id) {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired. Cannot edit students.';
            $this->redirect('students/view/' . $id);
        }

        $student = $this->studentModel->find($id);

        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            $this->redirect('students/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'first_name' => Validator::sanitize($_POST['first_name'] ?? ''),
                'last_name' => Validator::sanitize($_POST['last_name'] ?? ''),
                'gender' => $_POST['gender'] ?? '',
                'date_of_birth' => $_POST['date_of_birth'] ?? '',
                'nationality' => Validator::sanitize($_POST['nationality'] ?? ''),
                'class_id' => $_POST['class_id'] ?? null,
                'section_id' => $_POST['section_id'] ?? null,
                'address' => Validator::sanitize($_POST['address'] ?? ''),
                'phone' => Validator::sanitize($_POST['phone'] ?? ''),
                'email' => Validator::sanitize($_POST['email'] ?? ''),
                'status' => $_POST['status'] ?? 'active'
            ];

            $data['full_name'] = $data['first_name'] . ' ' . $data['last_name'];

            // Handle photo upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $upload = new Upload($_FILES['photo']);
                $upload->setAllowedTypes(ALLOWED_IMAGE_TYPES)->setMaxSize(MAX_UPLOAD_SIZE);
                $result = $upload->upload('students');

                if ($result) {
                    // Delete old photo
                    if ($student['photo']) {
                        Upload::delete(UPLOADS . '/students/' . $student['photo']);
                    }
                    $data['photo'] = $result['filename'];
                }
            }

            if ($this->studentModel->update($id, $data)) {
                $_SESSION['success'] = 'Student updated successfully.';
                $this->redirect('students/view/' . $id);
            } else {
                $_SESSION['error'] = 'Failed to update student.';
            }
        }

        $classModel = $this->model('ClassModel');

        $data = [
            'student' => $student,
            'classes' => $classModel->getActive()
        ];

        $this->view('students/edit', $data);
    }

    public function delete($id) {
        $this->requireRole(['school_admin']);

        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired. Cannot delete students.';
            $this->redirect('students/index');
        }

        $student = $this->studentModel->find($id);

        if (!$student) {
            $_SESSION['error'] = 'Student not found.';
            $this->redirect('students/index');
        }

        // Soft delete - just update status
        if ($this->studentModel->update($id, ['status' => 'inactive'])) {
            $_SESSION['success'] = 'Student deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete student.';
        }

        $this->redirect('students/index');
    }
}
