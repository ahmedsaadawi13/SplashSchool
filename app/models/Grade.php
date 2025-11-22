<?php
// FILE: /app/models/Grade.php

class Grade extends Model {
    protected $table = 'grades';
    protected $tenantScoped = true;

    public function getByStudentAndExam($studentId, $examId) {
        $sql = "SELECT g.*, s.name as subject_name, s.code as subject_code
                FROM {$this->table} g
                INNER JOIN subjects s ON g.subject_id = s.id
                WHERE g.student_id = :student_id AND g.exam_id = :exam_id";

        if ($this->tenantId) {
            $sql .= " AND g.tenant_id = :tenant_id";
        }

        $sql .= " ORDER BY s.name ASC";

        $stmt = $this->db->prepare($sql);
        $params = [
            ':student_id' => $studentId,
            ':exam_id' => $examId
        ];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function submitGrade($data) {
        // Check if grade already exists
        $sql = "SELECT id FROM {$this->table}
                WHERE student_id = :student_id AND exam_id = :exam_id AND subject_id = :subject_id";

        if ($this->tenantId) {
            $sql .= " AND tenant_id = :tenant_id";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $params = [
            ':student_id' => $data['student_id'],
            ':exam_id' => $data['exam_id'],
            ':subject_id' => $data['subject_id']
        ];
        if ($this->tenantId) {
            $params[':tenant_id'] = $this->tenantId;
        }
        $stmt->execute($params);
        $existing = $stmt->fetch();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }

    public function calculateGrade($marks, $maxMarks) {
        $percentage = ($marks / $maxMarks) * 100;

        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 50) return 'D';
        return 'F';
    }
}
