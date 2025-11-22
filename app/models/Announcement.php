<?php
// FILE: /app/models/Announcement.php

class Announcement extends Model {
    protected $table = 'announcements';
    protected $tenantScoped = true;

    public function getPublished($limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'published'";
        $params = [];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByRole($role, $classId = null) {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'published'
                AND (targeted_role = 'all' OR targeted_role = :role)";

        if ($classId) {
            $sql .= " AND (targeted_class_id IS NULL OR targeted_class_id = :class_id)";
        } else {
            $sql .= " AND targeted_class_id IS NULL";
        }

        $params = [':role' => $role];
        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $this->tenantId;
        }

        if ($classId) {
            $params[':class_id'] = $classId;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
