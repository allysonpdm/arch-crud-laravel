<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use InvalidArgumentException;

class DecimalRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(protected int $decimalPlaces = 2)
    {
        if(!is_int($this->decimalPlaces) || $this->decimalPlaces < 0){
            throw new InvalidArgumentException('Decimal places deve ser um numero inteiro');
        }
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
        // Verifica se o valor é numérico e se está no formato correto
        if (
            !is_numeric($value) ||
            !preg_match("/^-?\d+(\.\d{0," . $this->decimalPlaces . "})?$/", $value)
        ) {
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
        return trans('validation.decimal', [
            'attribute' => ':attribute',
            'decimals' => $this->decimalPlaces
        ]);
    }
}
