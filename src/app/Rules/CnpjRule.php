<?php

namespace ArchCrudLaravel\App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CnpjRule implements Rule
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
        $cnpj = preg_replace('/[^0-9]/', '', $value);
        $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);

        // Verifica se o numero de digitos informados é igual a 14
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se nenhuma das sequências invalidas abaixo foi digitada. Caso afirmativo, retorna falso
        switch ($cnpj) {
            case '00000000000000':
            case '11111111111111':
            case '22222222222222':
            case '33333333333333':
            case '44444444444444':
            case '55555555555555':
            case '66666666666666':
            case '77777777777777':
            case '88888888888888':
            case '99999999999999':
                return false;
            default:
                // Calcula os dígitos verificadores para verificar se o CNPJ é válido
                $j = 5;
                $k = 6;
                $soma1 = '';
                $soma2 = '';

                for ($i = 0; $i < 13; $i++) {

                    $j = $j == 1 ? 9 : $j;
                    $k = $k == 1 ? 9 : $k;

                    $soma2 += ($cnpj[$i] * $k);

                    if ($i < 12) {
                        $soma1 += ($cnpj[$i] * $j);
                    }

                    $k--;
                    $j--;
                }

                $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
                $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
        }

        return (($cnpj[12] == $digito1) && ($cnpj[13] == $digito2));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.cnpj');
    }
}
