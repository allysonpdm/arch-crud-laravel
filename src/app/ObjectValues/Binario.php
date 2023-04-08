<?php

namespace ArchCrudLaravel\App\ObjectValues;

use ArchCrudLaravel\App\ObjectValues\Contracts\ObjectValue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stringable;

class Binario extends ObjectValue implements Stringable
{
    protected bool $showTrueValue;
    protected bool $showFalseValue;

    const SIM_NAO = [true => 'Sim', false => 'Não'];
    const LIGADO_DESLIGADO = [true => 'Ligado', false => 'Desligado'];
    const POSITIVO_NEGATIVO = [true => 'Positivo', false => 'Negativo'];
    const PRESENTE_AUSENTE = [true => 'Presente', false => 'Ausente'];
    const MOSTRAR_OCULTAR = [true => 'Mostrar', false => 'Ocultar'];
    const VERDADEIRO_FALSO = [true => 'Verdadeiro', false => 'Falso'];
    const AUTORIZADO_DESAUTORIZADO = [true => 'Autorizado', false => 'Desautorizado'];

    protected array $options;

    public function __construct(mixed $value, protected string $optionType = 'sim_nao')
    {
        parent::__construct((bool) $value);
        $this->validateOptionType()
            ->setOptions();
    }

    protected function validate(mixed $value): void
    {
        $validator = Validator::make(
            ['value' => $value],
            ['value' => 'required|boolean']
        );

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }

    protected function validateOptionType(): Binario
    {
        $validOptions = [
            'sim_nao',
            'ligado_desligado',
            'positivo_negativo',
            'presente_ausente',
            'mostrar_ocultar',
            'verdadeiro_falso',
            'autorizado_desautorizado',
        ];

        $validator = Validator::make(
            ['value' => $this->optionType],
            [
                'value' => [
                    'required',
                    Rule::In($validOptions)
                ]
            ]
        );

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }

        return $this;
    }

    protected function setOptions(): void
    {
        switch ($this->optionType) {
            case 'sim_nao':
                $this->options = self::SIM_NAO;
                break;
            case 'ligado_desligado':
                $this->options = self::LIGADO_DESLIGADO;
                break;
            case 'positivo_negativo':
                $this->options = self::POSITIVO_NEGATIVO;
                break;
            case 'presente_ausente':
                $this->options = self::PRESENTE_AUSENTE;
                break;
            case 'mostrar_ocultar':
                $this->options = self::MOSTRAR_OCULTAR;
                break;
            case 'autorizado_desautorizado':
                $this->options = self::AUTORIZADO_DESAUTORIZADO;
                break;
            case 'verdadeiro_falso':
            default:
                $this->options = self::VERDADEIRO_FALSO;
                break;
        }
    }

    public function __toString(): string
    {
        return $this->options[($this->value === true)];
    }
}
