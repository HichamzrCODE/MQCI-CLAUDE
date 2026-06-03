<?php

class Validator {

    private array $errors = [];
    private array $data   = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function required(string $field, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value === '') {
            $this->errors[$field] = "Le champ « {$label} » est obligatoire.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] = "Le champ « {$label} » doit contenir au moins {$min} caractères.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if (mb_strlen($value) > $max) {
            $this->errors[$field] = "Le champ « {$label} » ne peut pas dépasser {$max} caractères.";
        }
        return $this;
    }

    public function numeric(string $field, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "Le champ « {$label} » doit être un nombre.";
        }
        return $this;
    }

    public function positiveNumber(string $field, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && (!is_numeric($value) || (float)$value <= 0)) {
            $this->errors[$field] = "Le champ « {$label} » doit être un nombre positif.";
        }
        return $this;
    }

    public function nonNegativeNumber(string $field, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && (!is_numeric($value) || (float)$value < 0)) {
            $this->errors[$field] = "Le champ « {$label} » doit être un nombre supérieur ou égal à zéro.";
        }
        return $this;
    }

    public function inList(string $field, array $list, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !in_array($value, $list, true)) {
            $this->errors[$field] = "La valeur du champ « {$label} » est invalide.";
        }
        return $this;
    }

    public function integer(string $field, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->errors[$field] = "Le champ « {$label} » doit être un entier.";
        }
        return $this;
    }

    public function email(string $field, string $label = ''): self {
        $label = $label ?: $field;
        $value = trim($this->data[$field] ?? '');
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "Le champ « {$label} » doit être une adresse e-mail valide.";
        }
        return $this;
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function getFirstError(): string {
        return reset($this->errors) ?: '';
    }

    public function get(string $field, $default = ''): string {
        return trim($this->data[$field] ?? $default);
    }
}
