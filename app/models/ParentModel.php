<?php
// FILE: /app/models/ParentModel.php

class ParentModel extends Model {
    protected $table = 'parents';
    protected $tenantScoped = true;

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        $this->addTenantScope($sql, $params);
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function findByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        $params = [':user_id' => $userId];
        $this->addTenantScope($sql, $params);
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getChildren($parentId) {
        $sql = "SELECT s.* FROM students s
                INNER JOIN parent_student ps ON s.id = ps.student_id
                WHERE ps.parent_id = :parent_id AND s.status = 'active'";

        if ($this->tenantId) {
            $sql .= " AND s.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY s.full_name ASC";

        $stmt = $this->db->prepare($sql);
        $params = [':parent_id' => $parentId];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function linkStudent($parentId, $studentId, $relationship = 'Parent') {
        $sql = "INSERT INTO parent_student (parent_id, student_id, relationship) VALUES (:parent_id, :student_id, :relationship)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':parent_id' => $parentId,
            ':student_id' => $studentId,
            ':relationship' => $relationship
        ]);
    }

    public function unlinkStudent($parentId, $studentId) {
        $sql = "DELETE FROM parent_student WHERE parent_id = :parent_id AND student_id = :student_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':parent_id' => $parentId,
            ':student_id' => $studentId
        ]);
    }
}
