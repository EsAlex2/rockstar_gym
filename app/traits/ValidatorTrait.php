<?php

namespace App\Traits;

/**
 * Trait ValidatorTrait
 * Proporciona métodos reutilizables de validación, sanitización y normalización de datos.
 */
trait ValidatorTrait
{
    /**
     * Valida que una lista de campos obligatorios no estén vacíos.
     * @param array $data Arreglo asociativo con los datos
     * @param array $requiredFields Lista de nombres de campos requeridos
     * @return array|null Retorna array con mensaje de error si falla, o null si es válido
     */
    public function validateRequiredFields(array $data, array $requiredFields): ?array
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim((string)$data[$field]) === '') {
                return [
                    "success" => false,
                    "message" => "El campo '{$field}' es estrictamente obligatorio."
                ];
            }
        }
        return null;
    }

    /**
     * Valida y sanitiza una dirección de correo electrónico.
     * @param string $email
     * @return string|null Retorna el email sanitizado o null si es inválido
     */
    public function validateAndSanitizeEmail(string $email): ?string
    {
        $cleanEmail = strtolower(trim($email));
        if (filter_var($cleanEmail, FILTER_VALIDATE_EMAIL)) {
            return $cleanEmail;
        }
        return null;
    }

    /**
     * Sanitiza cadenas de texto eliminando caracteres especiales y acentos para bases de datos heredadas.
     * @param string $text
     * @return string
     */
    public function sanitizeText(string $text): string
    {
        $caracteresEspeciales = ['ñ', 'Ñ', 'á', 'Á', 'é', 'É', 'í', 'Í', 'ó', 'Ó', 'ú', 'Ú'];
        $reemplazos = ['n', 'N', 'a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U'];
        return trim(str_replace($caracteresEspeciales, $reemplazos, $text));
    }

    /**
     * Limpia un número de cédula o documento dejando solo dígitos.
     * @param string $cedula
     * @return string
     */
    public function cleanCedula(string $cedula): string
    {
        return preg_replace('/[^0-9]/', '', $cedula);
    }

    /**
     * Valida que un número sea un entero positivo mayor a cero.
     * @param mixed $value
     * @return bool
     */
    public function isPositiveInteger(mixed $value): bool
    {
        return is_numeric($value) && (int)$value > 0;
    }

    /**
     * Valida que un monto monetario sea mayor a cero.
     * @param mixed $amount
     * @return bool
     */
    public function isPositiveAmount(mixed $amount): bool
    {
        return is_numeric($amount) && (float)$amount > 0;
    }
}
