<?php
// FILE: /app/models/Tenant.php

class Tenant extends Model {
    protected $table = 'tenants';
    protected $tenantScoped = false; // Tenants are not tenant-scoped

    public function findBySubdomain($subdomain) {
        $sql = "SELECT * FROM {$this->table} WHERE subdomain = :subdomain LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':subdomain' => $subdomain]);
        return $stmt->fetch();
    }

    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $status]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function countBySubscriptionStatus($subscriptionStatus) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE subscription_status = :status";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':status' => $subscriptionStatus]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getRecent($limit = 10) {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateSubscriptionStatus($tenantId, $status) {
        $sql = "UPDATE {$this->table} SET subscription_status = :status, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $tenantId
        ]);
    }
}
