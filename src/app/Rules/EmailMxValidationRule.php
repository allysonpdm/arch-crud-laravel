<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class EmailMxValidationRule implements Rule
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
        $parts = explode('@', $value);

        if (count($parts) !== 2) {
            return false;
        }

        $domain = trim($parts[1]);

        return checkdnsrr($domain, 'MX');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'O domínio do e-mail não possui um servidor MX válido.';
    }
}
