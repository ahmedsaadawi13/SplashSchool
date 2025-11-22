<?php
// FILE: /app/models/ClassModel.php

class ClassModel extends Model {
    protected $table = 'classes';
    protected $tenantScoped = true;

    public function getByAcademicYear($year) {
        return $this->where(['academic_year' => $year, 'status' => 'active'], 'name', 'ASC');
    }

    public function getActive() {
        return $this->where(['status' => 'active'], 'name', 'ASC');
    }

    public function getSections($classId) {
        $sql = "SELECT * FROM sections WHERE class_id = :class_id AND status = 'active'";
        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
        }
        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $params = [':class_id' => $classId];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
