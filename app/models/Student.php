<?php
// FILE: /app/models/Student.php

class Student extends Model {
    protected $table = 'students';
    protected $tenantScoped = true;

    public function findByCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE student_code = :code";
        $params = [':code' => $code];
        $this->addTenantScope($sql, $params);
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getByClass($classId, $sectionId = null) {
        $conditions = ['class_id' => $classId, 'status' => 'active'];
        if ($sectionId) {
            $conditions['section_id'] = $sectionId;
        }
        return $this->where($conditions, 'full_name', 'ASC');
    }

    public function countByStatus($status) {
        return $this->count(['status' => $status]);
    }

    public function getRecentAdmissions($limit = 10) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY admission_date DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function promote($studentId, $newClassId, $newSectionId) {
        return $this->update($studentId, [
            'class_id' => $newClassId,
            'section_id' => $newSectionId
        ]);
    }

    public function search($keyword) {
        $sql = "SELECT * FROM {$this->table} WHERE (student_code LIKE :keyword OR full_name LIKE :keyword OR email LIKE :keyword)";
        $params = [':keyword' => "%$keyword%"];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY full_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
