<?php
// FILE: /app/models/Teacher.php

class Teacher extends Model {
    protected $table = 'teachers';
    protected $tenantScoped = true;

    public function findByCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE teacher_code = :code";
        $params = [':code' => $code];
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

    public function getActive() {
        return $this->where(['status' => 'active'], 'name', 'ASC');
    }

    public function countByStatus($status) {
        return $this->count(['status' => $status]);
    }

    public function search($keyword) {
        $sql = "SELECT * FROM {$this->table} WHERE (teacher_code LIKE :keyword OR name LIKE :keyword OR email LIKE :keyword)";
        $params = [':keyword' => "%$keyword%"];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
