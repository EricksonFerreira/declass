<?php

namespace App\Http\Requests\TempoEntreEntacao;

use App\Services\TempoEntreEntacaoService;
use Illuminate\Foundation\Http\FormRequest;

class StoreTempoEntreEntacaoRequest extends FormRequest
{
    protected $tempoEntreEstacaoService;

    public function __construct(TempoEntreEntacaoService $tempoEntreEstacaoService)
    {
        $this->tempoEntreEstacaoService = $tempoEntreEstacaoService;
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tempo_deslocamento' => ['required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'estacao_origem_id' => [
                'required',
                'exists:estacoes,id',
                function ($attribute, $value, $fail) {
                    if ($this->tempoEntreEstacaoService->hasDuplicateEstacao($value, $this->input('estacao_destino_id'))) {
                        $fail('Já existe um registro com a mesma estação de origem e destino.');
                    }
                },
            ],
            'estacao_destino_id' => [
                'required',
                'exists:estacoes,id',
                function ($attribute, $value, $fail) {
                    if ($this->tempoEntreEstacaoService->hasDuplicateEstacao($this->input('estacao_origem_id'), $value)) {
                        $fail('Já existe um registro com a mesma estação de origem e destino.');
                    }
                },
            ],
            'ativo' => ['required', 'max:1'],
        ];
    }

    public function messages()
    {
        return [
            'tempo_deslocamento.required' => 'O campo tempo é obrigatório.',
            'tempo_deslocamento.regex' => 'O campo tempo deve ser uma hora válida.',
            'estacao_origem_id.required' => 'O campo estação de origem é obrigatório.',
            'estacao_origem_id.exists' => 'A estação de origem informada não existe.',
            'estacao_destino_id.required' => 'O campo estação de destino é obrigatório.',
            'estacao_destino_id.exists' => 'A estação de destino informada não existe.',
            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere.',
        ];
    }
}
