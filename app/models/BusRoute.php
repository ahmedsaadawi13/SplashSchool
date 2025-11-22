<?php
// FILE: /app/models/BusRoute.php

class BusRoute extends Model {
    protected $table = 'bus_routes';
    protected $tenantScoped = true;

    public function getActive() {
        return $this->where(['status' => 'active'], 'route_name', 'ASC');
    }

    public function getStudents($routeId) {
        $sql = "SELECT s.*, sba.pickup_point
                FROM students s
                INNER JOIN student_bus_assignments sba ON s.id = sba.student_id
                WHERE sba.route_id = :route_id AND s.status = 'active'";

        if ($this->tenantId) {
            $sql .= " AND s.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY s.full_name ASC";

        $stmt = $this->db->prepare($sql);
        $params = [':route_id' => $routeId];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
