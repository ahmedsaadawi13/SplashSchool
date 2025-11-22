<?php
// FILE: /app/models/Homework.php

class Homework extends Model {
    protected $table = 'homework';
    protected $tenantScoped = true;

    public function getByClass($classId, $sectionId = null) {
        $sql = "SELECT h.*, s.name as subject_name, t.name as teacher_name
                FROM {$this->table} h
                INNER JOIN subjects s ON h.subject_id = s.id
                INNER JOIN teachers t ON h.teacher_id = t.id
                WHERE h.class_id = :class_id";

        if ($sectionId) {
            $sql .= " AND h.section_id = :section_id";
        }

        if ($this->tenantId) {
            $sql .= " AND h.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY h.due_date DESC";

        $stmt = $this->db->prepare($sql);
        $params = [':class_id' => $classId];
        if ($sectionId) {
            $params[':section_id'] = $sectionId;
        }
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countByTeacher($teacherId) {
        return $this->count(['teacher_id' => $teacherId]);
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT h.*, s.name as subject_name, c.name as class_name
                FROM {$this->table} h
                INNER JOIN subjects s ON h.subject_id = s.id
                INNER JOIN classes c ON h.class_id = c.id";

        $params = [];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY h.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
