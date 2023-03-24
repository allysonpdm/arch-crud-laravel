<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FixedPhoneNumberValidationRule implements Rule
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
        // remove todos os caracteres não numéricos
        $number = preg_replace('/[^0-9]/', '', $value);

        // verifica se o número tem o tamanho correto
        if (strlen($number) !== 10) {
            return false;
        }

        // verifica se o código de área é válido (11-19 ou 21-91)
        $areaCode = substr($number, 0, 2);
        if (!preg_match('/^(1[1-9]|2[1-9]|[3-8][0-9]|9[1-9])$/', $areaCode)) {
            return false;
        }

        // verifica se o número do telefone é válido (8 dígitos)
        $phoneNumber = substr($number, 2);
        if (!preg_match('/^[0-9]{8}$/', $phoneNumber)) {
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
        return 'O número de telefone fixo informado não é válido.';
    }
}
