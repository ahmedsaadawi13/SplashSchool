<?php
// FILE: /app/models/FeeType.php

class FeeType extends Model {
    protected $table = 'fee_types';
    protected $tenantScoped = true;

    public function getActive() {
        return $this->where(['is_active' => 1], 'name', 'ASC');
    }
}
