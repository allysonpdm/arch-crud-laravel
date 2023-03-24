<?php

namespace ArchCrudLaravel\App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CelularPhoneNumberValidationRule implements Rule
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
        $value = preg_replace('/\D/', '', $value); // remove caracteres não numéricos

        // Verifica se o telefone celular tem 11 dígitos e começa com o dígito 9
        if (preg_match('/^9\d{9}$/', $value)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'O telefone celular informado não é válido.';
    }
}
