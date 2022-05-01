<?php

namespace App\Rules;

use App\Http\Requests\BaseRequest;
use App\Models\Pessoas;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class HasPermissionRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($niveisAutorizados, $pessoaId = null)
    {
        $this->niveisAutorizados = $niveisAutorizados;
        $this->pessoaId = $pessoaId;
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
        return self::isPermit($this->niveisAutorizados, $this->pessoaId);
    }

    public static function isPermit(array $niveisAutorizados, int|string|null $pessoaId = null): bool
    {
        if(in_array('Proprietário', $niveisAutorizados) && $pessoaId == Auth::user()->id){
            return true;
        }

        $permissoes = Pessoas::Find(Auth::user()->id)
            ->with('niveisAcessos')
            ->first()
            ->niveisAcessos
            ->filter(function($item) use ($niveisAutorizados){
                return in_array($item->descricao, $niveisAutorizados);
            });

        return $permissoes->count() >= 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('auth.user.nao_autorizado');
    }
}
