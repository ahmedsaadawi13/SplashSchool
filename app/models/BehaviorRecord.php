<?php
// FILE: /app/models/BehaviorRecord.php

class BehaviorRecord extends Model {
    protected $table = 'behavior_records';
    protected $tenantScoped = true;

    public function getByStudent($studentId, $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = :student_id";
        $params = [':student_id' => $studentId];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY date DESC";

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

    public function getPointsSummary($studentId) {
        $sql = "SELECT
                    SUM(CASE WHEN type = 'positive' THEN points ELSE 0 END) as positive_points,
                    SUM(CASE WHEN type = 'negative' THEN points ELSE 0 END) as negative_points,
                    SUM(points) as total_points
                FROM {$this->table}
                WHERE student_id = :student_id";

        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':student_id' => $studentId];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
