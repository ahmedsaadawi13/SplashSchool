<?php
// FILE: /app/helpers/Validator.php

class Validator {
    protected $errors = [];
    protected $data = [];

    public function __construct($data) {
        $this->data = $data;
    }

    public function validate($rules) {
        foreach ($rules as $field => $ruleSet) {
            $ruleList = explode('|', $ruleSet);

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    protected function applyRule($field, $rule) {
        $value = isset($this->data[$field]) ? $this->data[$field] : null;

        // Parse rule and parameter
        $param = null;
        if (strpos($rule, ':') !== false) {
            list($rule, $param) = explode(':', $rule, 2);
        }

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, ucfirst($field) . ' is required.');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, ucfirst($field) . ' must be a valid email address.');
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $param) {
                    $this->addError($field, ucfirst($field) . " must be at least $param characters.");
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $param) {
                    $this->addError($field, ucfirst($field) . " must not exceed $param characters.");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, ucfirst($field) . ' must be numeric.');
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters.');
                }
                break;

            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, ucfirst($field) . ' must contain only letters and numbers.');
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $d = DateTime::createFromFormat('Y-m-d', $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, ucfirst($field) . ' must be a valid date (YYYY-MM-DD).');
                    }
                }
                break;

            case 'in':
                if (!empty($value)) {
                    $allowedValues = explode(',', $param);
                    if (!in_array($value, $allowedValues)) {
                        $this->addError($field, ucfirst($field) . ' must be one of: ' . implode(', ', $allowedValues));
                    }
                }
                break;

            case 'unique':
                // Format: unique:table,column
                list($table, $column) = explode(',', $param);
                if (!empty($value)) {
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table WHERE $column = :value");
                    $stmt->execute([':value' => $value]);
                    $result = $stmt->fetch();

                    if ($result['count'] > 0) {
                        $this->addError($field, ucfirst($field) . ' already exists.');
                    }
                }
                break;
        }
    }

    protected function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError($field = null) {
        if ($field) {
            return isset($this->errors[$field]) ? $this->errors[$field][0] : null;
        }

        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0];
        }

        return null;
    }

    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }

        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
