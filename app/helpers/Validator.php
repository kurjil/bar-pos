<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Exceptions\ValidationException;
use PDO;

class Validator
{
    public static function validate(array $data, array $rules, ?PDO $db = null): array
    {
        $errors = [];
        $validated = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $error = self::applyRule($field, $value, $rule, $params, $data, $db);
                if ($error !== null) {
                    $errors[$field][] = $error;
                    break;
                }
            }

            if (!isset($errors[$field])) {
                $validated[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $validated;
    }

    private static function applyRule(
        string $field,
        mixed $value,
        string $rule,
        array $params,
        array $data,
        ?PDO $db
    ): ?string {
        if ($rule !== 'required' && ($value === null || $value === '')) {
            return null;
        }

        return match ($rule) {
            'required' => ($value === null || $value === '') ? ucfirst($field) . ' is required.' : null,
            'email' => (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL))
                ? 'A valid email is required.' : null,
            'string' => !is_string($value) ? ucfirst($field) . ' must be a string.' : null,
            'numeric' => !is_numeric($value) ? ucfirst($field) . ' must be numeric.' : null,
            'integer' => filter_var($value, FILTER_VALIDATE_INT) === false
                ? ucfirst($field) . ' must be an integer.' : null,
            'min' => self::checkMin($field, $value, $params),
            'max' => self::checkMax($field, $value, $params),
            'confirmed' => ($data[$field . '_confirmation'] ?? null) !== $value
                ? ucfirst($field) . ' confirmation does not match.' : null,
            'unique' => self::checkUnique($field, $value, $params, $db),
            default => null,
        };
    }

    private static function checkMin(string $field, mixed $value, array $params): ?string
    {
        $min = (float) ($params[0] ?? 0);
        if (is_string($value) && strlen($value) < $min) {
            return ucfirst($field) . " must be at least {$min} characters.";
        }
        if (is_numeric($value) && (float) $value < $min) {
            return ucfirst($field) . " must be at least {$min}.";
        }
        return null;
    }

    private static function checkMax(string $field, mixed $value, array $params): ?string
    {
        $max = (int) ($params[0] ?? 0);
        if (is_string($value) && strlen($value) > $max) {
            return ucfirst($field) . " must not exceed {$max} characters.";
        }
        return null;
    }

    private static function checkUnique(string $field, mixed $value, array $params, ?PDO $db): ?string
    {
        if ($db === null || $value === null || $value === '') {
            return null;
        }

        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        $sql = "SELECT id FROM {$table} WHERE {$column} = ? AND deleted_at IS NULL";
        $bindings = [$value];

        if ($exceptId !== null) {
            $sql .= ' AND id != ?';
            $bindings[] = $exceptId;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetch() ? ucfirst($field) . ' already exists.' : null;
    }
}
