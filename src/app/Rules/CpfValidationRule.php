<?php

namespace ArchCrudLaravel\App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CpfValidationRule implements Rule
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
        // Verifica se está vazio
        if (empty($value)) {
            return false;
        }

        // Elimina possível mascara
        $cpf = preg_replace('/[^0-9]/', '', $value);
        $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

        // Verifica se o numero de dígitos informados é igual a 11
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se todos os dígitos do CPF são iguais;
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Calcula os dígitos verificadores para verificar se o CPF é válido
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
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
