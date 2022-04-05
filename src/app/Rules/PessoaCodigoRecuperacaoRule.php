<?php

namespace App\Rules;

use App\Models\Pessoas;
use Illuminate\Contracts\Validation\Rule;

class PessoaCodigoRecuperacaoRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(?string $login)
    {
        $this->login = $login;
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
        if(empty($this->login)){
            return false;
        }

        $user = Pessoas::where(
            function($query) {
                return $query->where('cpf', $this->login)
                    ->orWhere('cnpj', $this->login);
            })
            ->where(function($query) use ($value) {
                return $query->where('codigoRecuperacao', $value);
            })
            ->get();
        if($user->count() != 1){
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
        return __('validation.pessoa_codigo_recuperacao');
    }

}
