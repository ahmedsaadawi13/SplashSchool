<?php
// FILE: /app/models/TenantUsage.php

class TenantUsage extends Model {
    protected $table = 'tenant_usage';
    protected $tenantScoped = true;

    public function initialize($tenantId) {
        $sql = "INSERT INTO {$this->table} (tenant_id, last_updated) VALUES (:tenant_id, :last_updated)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':last_updated' => date('Y-m-d H:i:s')
        ]);
    }

    public function updateCounts($tenantId) {
        // Count students
        $sql = "UPDATE {$this->table} SET
                students_count = (SELECT COUNT(*) FROM students WHERE tenant_id = :tid AND status = 'active'),
                teachers_count = (SELECT COUNT(*) FROM teachers WHERE tenant_id = :tid AND status = 'active'),
                classes_count = (SELECT COUNT(*) FROM classes WHERE tenant_id = :tid AND status = 'active'),
                parents_count = (SELECT COUNT(*) FROM parents WHERE tenant_id = :tid),
                last_updated = :last_updated
                WHERE tenant_id = :tenant_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tid' => $tenantId,
            ':tenant_id' => $tenantId,
            ':last_updated' => date('Y-m-d H:i:s')
        ]);
    }

    public function getByTenant($tenantId) {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetch();
    }
}
