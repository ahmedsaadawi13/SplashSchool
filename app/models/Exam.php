<?php
// FILE: /app/models/Exam.php

class Exam extends Model {
    protected $table = 'exams';
    protected $tenantScoped = true;

    public function getByAcademicYear($year) {
        return $this->where(['academic_year' => $year], 'exam_date', 'DESC');
    }

    public function getUpcoming() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'upcoming'";
        $params = [];
        $this->addTenantScope($sql, $params);
        $sql .= " ORDER BY exam_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
