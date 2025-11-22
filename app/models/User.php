<?php
// FILE: /app/models/User.php

class User extends Model {
    protected $table = 'users';
    protected $tenantScoped = false; // Users can be platform-wide

    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function createUser($data) {
        return $this->create($data);
    }

    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET last_login = :last_login WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':last_login' => date('Y-m-d H:i:s'),
            ':id' => $userId
        ]);
    }

    public function getByRole($role) {
        return $this->where(['role' => $role]);
    }

    public function getByTenant($tenantId) {
        return $this->where(['tenant_id' => $tenantId]);
    }
}
