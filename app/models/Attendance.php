<?php
// FILE: /app/models/Attendance.php

class Attendance extends Model {
    protected $table = 'attendance';
    protected $tenantScoped = true;

    public function markAttendance($data) {
        // Check if attendance already exists for this student on this date
        $existing = $this->getByStudentAndDate($data['student_id'], $data['date']);

        if ($existing) {
            // Update existing record
            return $this->update($existing['id'], $data);
        } else {
            // Create new record
            return $this->create($data);
        }
    }

    public function getByStudentAndDate($studentId, $date) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = :student_id AND date = :date";
        $params = [':student_id' => $studentId, ':date' => $date];
        $this->addTenantScope($sql, $params);
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getByClassAndDate($classId, $sectionId, $date) {
        $sql = "SELECT a.*, s.full_name, s.student_code FROM {$this->table} a
                INNER JOIN students s ON a.student_id = s.id
                WHERE a.class_id = :class_id AND a.section_id = :section_id AND a.date = :date";

        if ($this->tenantId) {
            $sql .= " AND a.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY s.full_name ASC";

        $stmt = $this->db->prepare($sql);
        $params = [
            ':class_id' => $classId,
            ':section_id' => $sectionId,
            ':date' => $date
        ];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getTodaySummary($date) {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
                FROM {$this->table}
                WHERE date = :date";

        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $stmt = $this->db->prepare($sql);
        $params = [':date' => $date];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function getStudentAttendance($studentId, $startDate = null, $endDate = null) {
        $sql = "SELECT * FROM {$this->table} WHERE student_id = :student_id";

        if ($startDate) {
            $sql .= " AND date >= :start_date";
        }
        if ($endDate) {
            $sql .= " AND date <= :end_date";
        }

        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY date DESC";

        $stmt = $this->db->prepare($sql);
        $params = [':student_id' => $studentId];
        if ($startDate) {
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $params[':end_date'] = $endDate;
        }
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getPendingCountForTeacher($teacherId) {
        // This is a simplified version - in real implementation,
        // you'd check which classes the teacher is responsible for
        return 0;
    }
}
