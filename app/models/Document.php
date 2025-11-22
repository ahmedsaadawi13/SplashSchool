<?php
// FILE: /app/models/Document.php

class Document extends Model {
    protected $table = 'documents';
    protected $tenantScoped = true;

    public function getByModule($moduleType, $moduleId) {
        return $this->where([
            'module_type' => $moduleType,
            'module_id' => $moduleId
        ], 'created_at', 'DESC');
    }

    public function getTotalSize() {
        $sql = "SELECT SUM(file_size) as total FROM {$this->table}";
        $params = [];
        $this->addTenantScope($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}
