<?php
// FILE: /app/models/Section.php

class Section extends Model {
    protected $table = 'sections';
    protected $tenantScoped = true;

    public function getByClass($classId) {
        return $this->where(['class_id' => $classId, 'status' => 'active'], 'name', 'ASC');
    }
}
