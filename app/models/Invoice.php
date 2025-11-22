<?php
// FILE: /app/models/Invoice.php

class Invoice extends Model {
    protected $table = 'invoices';
    protected $tenantScoped = true;

    public function generateInvoiceNumber() {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get last invoice number
        $sql = "SELECT invoice_number FROM {$this->table}
                WHERE invoice_number LIKE :pattern";

        $params = [':pattern' => "$prefix-$year$month%"];
        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $this->tenantId;
        }

        $sql .= " ORDER BY id DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $last = $stmt->fetch();

        if ($last) {
            $lastNumber = intval(substr($last['invoice_number'], -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "$prefix-$year$month-$newNumber";
    }

    public function getByStudent($studentId) {
        return $this->where(['student_id' => $studentId], 'created_at', 'DESC');
    }

    public function getUnpaidByStudent($studentId) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = :student_id AND status IN ('unpaid', 'partially_paid')";
        $params = [':student_id' => $studentId];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByStatus($status) {
        return $this->count(['status' => $status]);
    }

    public function getTotalUnpaidAmount() {
        $sql = "SELECT SUM(balance) as total FROM {$this->table} WHERE status IN ('unpaid', 'partially_paid')";
        $params = [];
        $this->addTenantScope($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function updateBalance($invoiceId) {
        $sql = "UPDATE {$this->table}
                SET balance = total_amount - paid_amount,
                    status = CASE
                        WHEN paid_amount >= total_amount THEN 'paid'
                        WHEN paid_amount > 0 THEN 'partially_paid'
                        ELSE 'unpaid'
                    END,
                    updated_at = :updated_at
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $invoiceId
        ]);
    }
}
