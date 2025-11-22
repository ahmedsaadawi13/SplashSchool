<?php
// FILE: /app/models/Timetable.php

class Timetable extends Model {
    protected $table = 'timetables';
    protected $tenantScoped = true;

    public function getByClassAndSection($classId, $sectionId) {
        $sql = "SELECT t.*, s.name as subject_name, s.code as subject_code,
                       te.name as teacher_name
                FROM {$this->table} t
                INNER JOIN subjects s ON t.subject_id = s.id
                LEFT JOIN teachers te ON t.teacher_id = te.id
                WHERE t.class_id = :class_id AND t.section_id = :section_id";

        if ($this->tenantId) {
            $sql .= " AND t.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY
                  FIELD(t.weekday, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                  t.period_number ASC";

        $stmt = $this->db->prepare($sql);
        $params = [
            ':class_id' => $classId,
            ':section_id' => $sectionId
        ];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getByTeacher($teacherId) {
        $sql = "SELECT t.*, c.name as class_name, sec.name as section_name,
                       s.name as subject_name, s.code as subject_code
                FROM {$this->table} t
                INNER JOIN classes c ON t.class_id = c.id
                INNER JOIN sections sec ON t.section_id = sec.id
                INNER JOIN subjects s ON t.subject_id = s.id
                WHERE t.teacher_id = :teacher_id";

        if ($this->tenantId) {
            $sql .= " AND t.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY
                  FIELD(t.weekday, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                  t.period_number ASC";

        $stmt = $this->db->prepare($sql);
        $params = [':teacher_id' => $teacherId];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
