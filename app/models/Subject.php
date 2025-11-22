<?php
// FILE: /app/models/Subject.php

class Subject extends Model {
    protected $table = 'subjects';
    protected $tenantScoped = true;

    public function findByCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE code = :code";
        $params = [':code' => $code];
        $this->addTenantScope($sql, $params);
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getActive() {
        return $this->where(['status' => 'active'], 'name', 'ASC');
    }

    public function getByClass($classId) {
        $sql = "SELECT s.*, cs.teacher_id FROM subjects s
                INNER JOIN class_subjects cs ON s.id = cs.subject_id
                WHERE cs.class_id = :class_id";

        if ($this->tenantId) {
            $sql .= " AND s.tenant_id = :tenant_id";
        }

        $sql .= " AND s.status = 'active' ORDER BY s.name ASC";

        $stmt = $this->db->prepare($sql);
        $params = [':class_id' => $classId];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
