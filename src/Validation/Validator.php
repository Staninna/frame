<?php

namespace Validation;

// TODO: Add support for custom validators (global?)
// TODO: Add support for custom error messages (global?)
// TODO: Add support for nested validation rules
// TODO: If we have models implemented, let users automatically validate them using the rules defined in the model????
/*
 * Validator
 *
 * Validates data based on rules.
 *
 * ## Rules
 *
 * - `required`: Checks if the field is required.
 * - `min`: Checks if the field value is at least a certain length.
 * - `max`: Checks if the field value is at most a certain length.
 * - `email`: Checks if the field value is a valid email address.
 * - `numeric`: Checks if the field value is a number.
 * - `alpha`: Checks if the field value only contains letters.
 * - `alphanumeric`: Checks if the field value only contains letters and numbers.
 * - `regex`: Checks if the field value matches a regular expression.
 *
 * ## Example
 *
 * ```php
 * $validator = new Validator();
 * $isValid = $validator->validate($data, [
 *     'name' => ['required', 'min:3', 'max:255'],
 *     'email' => ['required', 'email'],
 *     'age' => ['required', 'numeric', 'min:18', 'max:100'],
 *     'password' => ['required', 'regex:/^[a-zA-Z0-9]{8,}$/'],
 * ]);
 * ```
 */
class Validator implements ValidatorInterface
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            if (!isset($data[$field]) && !in_array('nullable', $fieldRules)) {
                $this->errors[$field][] = "The {$field} field is required.";
                continue;
            }

            if (isset($data[$field])) {
                $value = $data[$field];

                foreach ($fieldRules as $rule) {
                    $this->applyRule($field, $value, $rule);
                }
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, $value, string $rule): void
    {
        [$ruleName, $ruleValue] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "The {$field} field is required.";
                }
                break;
            case 'min':
                if (is_string($value) && strlen($value) < $ruleValue) {
                    $this->errors[$field][] = "The {$field} must be at least {$ruleValue} characters.";
                } elseif (is_numeric($value) && $value < $ruleValue) {
                    $this->errors[$field][] = "The {$field} must be at least {$ruleValue}.";
                }
                break;
            case 'max':
                if (is_string($value) && strlen($value) > $ruleValue) {
                    $this->errors[$field][] = "The {$field} may not be greater than {$ruleValue} characters.";
                } elseif (is_numeric($value) && $value > $ruleValue) {
                    $this->errors[$field][] = "The {$field} may not be greater than {$ruleValue}.";
                }
                break;
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "The {$field} must be a valid email address.";
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    $this->errors[$field][] = "The {$field} must be a number.";
                }
                break;
            case 'alpha':
                if (!ctype_alpha($value)) {
                    $this->errors[$field][] = "The {$field} must only contain letters.";
                }
                break;
            case 'alphanumeric':
                if (!ctype_alnum($value)) {
                    $this->errors[$field][] = "The {$field} must only contain letters and numbers.";
                }
                break;
            case 'regex':
                if (!preg_match($ruleValue, $value)) {
                    $this->errors[$field][] = "The {$field} does not match the specified pattern.";
                }
                break;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
