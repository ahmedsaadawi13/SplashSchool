<?php
// FILE: /app/controllers/FeeController.php

class FeeController extends Controller {
    private $invoiceModel;
    private $paymentModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth();
        $this->requireTenant();
        $this->requireRole(['school_admin', 'accountant']);
        $this->invoiceModel = $this->model('Invoice');
        $this->paymentModel = $this->model('Payment');
    }

    public function index() {
        $invoices = $this->invoiceModel->all('created_at', 'DESC');
        $this->view('fees/index', ['invoices' => $invoices]);
    }

    public function createInvoice() {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('fees/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'invoice_number' => $this->invoiceModel->generateInvoiceNumber(),
                'student_id' => $_POST['student_id'] ?? null,
                'class_id' => $_POST['class_id'] ?? null,
                'academic_year' => $_POST['academic_year'] ?? date('Y'),
                'due_date' => $_POST['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                'total_amount' => $_POST['total_amount'] ?? 0,
                'paid_amount' => 0,
                'balance' => $_POST['total_amount'] ?? 0,
                'status' => 'unpaid'
            ];

            if ($this->invoiceModel->create($data)) {
                $_SESSION['success'] = 'Invoice created successfully.';
                $this->redirect('fees/index');
            } else {
                $_SESSION['error'] = 'Failed to create invoice.';
            }
        }

        $studentModel = $this->model('Student');
        $this->view('fees/create_invoice', ['students' => $studentModel->all()]);
    }

    public function recordPayment($invoiceId) {
        if ($this->isReadOnly()) {
            $_SESSION['error'] = 'Subscription expired.';
            $this->redirect('fees/index');
        }

        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            $_SESSION['error'] = 'Invoice not found.';
            $this->redirect('fees/index');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::verify();

            $data = [
                'invoice_id' => $invoiceId,
                'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
                'amount_paid' => $_POST['amount_paid'] ?? 0,
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'notes' => Validator::sanitize($_POST['notes'] ?? ''),
                'received_by' => $this->userId
            ];

            if ($this->paymentModel->recordPayment($data)) {
                $_SESSION['success'] = 'Payment recorded successfully.';
                $this->redirect('fees/view/' . $invoiceId);
            } else {
                $_SESSION['error'] = 'Failed to record payment.';
            }
        }

        $this->view('fees/record_payment', ['invoice' => $invoice]);
    }

    public function view($invoiceId) {
        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) {
            $_SESSION['error'] = 'Invoice not found.';
            $this->redirect('fees/index');
        }

        $payments = $this->paymentModel->getByInvoice($invoiceId);
        $this->view('fees/view', ['invoice' => $invoice, 'payments' => $payments]);
    }
}
