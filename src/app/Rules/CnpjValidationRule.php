<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CnpjValidationRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Remove todos os caracteres que não são dígitos do valor do CNPJ;
        $value = preg_replace('/[^0-9]/', '', $value);

        // Verifica se o CNPJ tem 14 dígitos;
        if (strlen($value) != 14) {
            return false;
        }

        // Verifica se todos os dígitos do CNPJ são iguais;
        if (preg_match('/(\d)\1{13}/', $value)) {
            return false;
        }

        // Verifica o primeiro dígito verificador do CNPJ;
        $sum = 0;
        $multiplier = 5;

        for ($i = 0; $i < 12; $i++) {
            $sum += $value[$i] * $multiplier;
            $multiplier = $multiplier == 2 ? 9 : $multiplier - 1;
        }

        $remainder = $sum % 11;
        $digit = $remainder < 2 ? 0 : 11 - $remainder;

        if ($value[12] != $digit) {
            return false;
        }

        // Verifica o segundo dígito verificador do CNPJ;
        $sum = 0;
        $multiplier = 6;

        for ($i = 0; $i < 13; $i++) {
            $sum += $value[$i] * $multiplier;
            $multiplier = $multiplier == 2 ? 9 : $multiplier - 1;
        }

        $remainder = $sum % 11;
        $digit = $remainder < 2 ? 0 : 11 - $remainder;

        if ($value[13] != $digit) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'O :attribute é inválido.';
    }
}
