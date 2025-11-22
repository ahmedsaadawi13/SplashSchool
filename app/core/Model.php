<?php
// FILE: /app/core/Model.php

class Model {
    protected $db;
    protected $table;
    protected $tenantScoped = true;
    protected $tenantId = null;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();

        // Get tenant ID from session if available
        if (isset($_SESSION['tenant_id'])) {
            $this->tenantId = $_SESSION['tenant_id'];
        }
    }

    public function setTenantId($tenantId) {
        $this->tenantId = $tenantId;
    }

    protected function addTenantScope(&$sql, &$params) {
        if ($this->tenantScoped && $this->tenantId !== null) {
            if (stripos($sql, 'WHERE') !== false) {
                $sql .= " AND tenant_id = :tenant_id";
            } else {
                $sql .= " WHERE tenant_id = :tenant_id";
            }
            $params[':tenant_id'] = $this->tenantId;
        }
    }

    public function all($orderBy = 'id', $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        $this->addTenantScope($sql, $params);

        $sql .= " ORDER BY $orderBy $order";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];

        $this->addTenantScope($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch();
    }

    public function where($conditions, $orderBy = 'id', $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table} WHERE ";
        $params = [];
        $whereClauses = [];

        foreach ($conditions as $key => $value) {
            $whereClauses[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql .= implode(' AND ', $whereClauses);

        if ($this->tenantScoped && $this->tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $this->tenantId;
        }

        $sql .= " ORDER BY $orderBy $order";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function create($data) {
        // Add tenant_id if tenant scoped
        if ($this->tenantScoped && $this->tenantId !== null) {
            $data['tenant_id'] = $this->tenantId;
        }

        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');

        $columns = array_keys($data);
        $values = ':' . implode(', :', $columns);
        $columns = implode(', ', $columns);

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($values)";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    public function update($id, $data) {
        // Add updated_at timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');

        $setClauses = [];
        foreach ($data as $key => $value) {
            $setClauses[] = "$key = :$key";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $data[':id'] = $id;

        // Add tenant scope for security
        $params = $data;
        if ($this->tenantScoped && $this->tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $this->tenantId;
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];

        // Add tenant scope for security
        if ($this->tenantScoped && $this->tenantId !== null) {
            $sql .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $this->tenantId;
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $whereClauses = [];

            foreach ($conditions as $key => $value) {
                $whereClauses[] = "$key = :$key";
                $params[":$key"] = $value;
            }

            $sql .= implode(' AND ', $whereClauses);
        }

        if ($this->tenantScoped && $this->tenantId !== null) {
            if (!empty($conditions)) {
                $sql .= " AND tenant_id = :tenant_id";
            } else {
                $sql .= " WHERE tenant_id = :tenant_id";
            }
            $params[':tenant_id'] = $this->tenantId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();
        return $result['total'];
    }

    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function execute($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
