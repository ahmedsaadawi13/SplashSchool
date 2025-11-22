<?php
// FILE: /app/models/Payment.php

class Payment extends Model {
    protected $table = 'payments';
    protected $tenantScoped = true;

    public function recordPayment($data) {
        $paymentId = $this->create($data);

        if ($paymentId) {
            // Update invoice paid amount
            $sql = "UPDATE invoices
                    SET paid_amount = paid_amount + :amount
                    WHERE id = :invoice_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':amount' => $data['amount_paid'],
                ':invoice_id' => $data['invoice_id']
            ]);

            // Update invoice balance and status
            $invoiceModel = new Invoice();
            $invoiceModel->setTenantId($this->tenantId);
            $invoiceModel->updateBalance($data['invoice_id']);

            return $paymentId;
        }

        return false;
    }

    public function getByInvoice($invoiceId) {
        return $this->where(['invoice_id' => $invoiceId], 'payment_date', 'DESC');
    }

    public function getTotalCollected($startDate = null, $endDate = null) {
        $sql = "SELECT SUM(amount_paid) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if ($startDate) {
            $sql .= " AND payment_date >= :start_date";
            $params[':start_date'] = $startDate;
        }

        if ($endDate) {
            $sql .= " AND payment_date <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $this->addTenantScope($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT p.*, i.invoice_number, s.full_name as student_name
                FROM {$this->table} p
                INNER JOIN invoices i ON p.invoice_id = i.id
                INNER JOIN students s ON i.student_id = s.id";

        $params = [];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
