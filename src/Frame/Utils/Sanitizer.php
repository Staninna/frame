<?php

namespace Frame\Utils;

// TODO: Add support for custom sanitizers (global?)

/**
 * Sanitizer
 *
 * Sanitizes data based on rules.
 *
 * ## Rules
 *
 * - `trim`: Removes any leading or trailing whitespace from the field value.
 * - `lowercase`: Converts the field value to lowercase.
 * - `uppercase`: Converts the field value to uppercase.
 * - `escape`: Escapes any HTML characters in the field value.
 *
 * ## Example
 *
 * ```php
 * $sanitizer = new Sanitizer();
 * $sanitizedData = $sanitizer->sanitize($data, [
 *     'name' => ['trim', 'lowercase'],
 *     'email' => ['trim', 'lowercase', 'escape'],
 * ]);
 * ```
 */
class Sanitizer
{
    public function sanitize(array $data, array $rules): array
    {
        $sanitized = [];

        foreach ($rules as $field => $fieldRules) {
            if (isset($data[$field])) {
                $value = $data[$field];
                $sanitized[$field] = $this->sanitizeValue($value, $fieldRules);
            }
        }

        return $sanitized;
    }

    private function sanitizeValue($value, array $rules): string
    {
        foreach ($rules as $rule) {
            [$ruleName] = explode(':', $rule, 2);

            switch ($ruleName) {
                case 'trim':
                    $value = trim($value);
                    break;
                case 'lowercase':
                    $value = strtolower($value);
                    break;
                case 'uppercase':
                    $value = strtoupper($value);
                    break;
                case 'escape':
                    $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    break;
            }
        }

        return $value;
    }
}
