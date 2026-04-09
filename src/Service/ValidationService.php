<?php

namespace App\Service;

class ValidationService
{
    private $errors = [];

    public function validate($data, $rules)
    {
        $this->errors = [];
        foreach ($rules as $field => $ruleSet) {
            $fieldRules = explode('|', $ruleSet);
            foreach ($fieldRules as $rule) {
                $params = [];
                if (strpos($rule, ':') !== false) {
                    list($rule, $paramStr) = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }
                $value = isset($data[$field]) ? $data[$field] : null;
                $method = 'validate'.ucfirst($rule);
                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $params);
                }
            }
        }
        return   empty($this->errors);
    }

    private function validateRequired($field, $value, $params)
    {
        if ($value === null || $value === '') {
            $this->errors[$field][] = "The {$field} field is required.";
        }
    }

    private function validateMin($field, $value, $params)
    {
        $min = (int)$params[0];
        if (is_string($value) && strlen($value) < $min) {
            $this->errors[$field][] = "The {$field} must be at least {$min} characters.";
        } elseif (is_numeric($value) && $value < $min) {
            $this->errors[$field][] = "The {$field} must be at least {$min}.";
        }
    }

    private function validateMax($field, $value, $params)
    {
        $max = (int)$params[0];
        if (is_string($value) && strlen($value) > $max) {
            $this->errors[$field][] = "The {$field} must not exceed {$max} characters.";
        } elseif (is_numeric($value) && $value > $max) {
            $this->errors[$field][] = "The {$field} must not exceed {$max}.";
        }
    }

    private function validateEmail($field, $value, $params)
    {
        if ($value !== null && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "The {$field} must be a valid email address.";
        }
    }

    private function validateNumeric($field, $value, $params)
    {
        if ($value !== null  &&  !is_numeric($value)) {
            $this->errors[$field][] = "The {$field} must be numeric.";
        }
    }

    private function validateIn($field, $value, $params)
    {
        if ($value !== null && !in_array($value, $params)) {
            $this->errors[$field][] = "The {$field} must be one of: ".implode(', ', $params).".";
        }
    }

    public function getErrors()
    {
        return    $this->errors;
    }

    public function getFirstError($field)
    {
        return isset($this->errors[$field]) ? $this->errors[$field][0] : null;
    }
}
