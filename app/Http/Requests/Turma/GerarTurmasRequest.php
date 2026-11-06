<?php

namespace App\Http\Requests\Turma;

use App\Models\Turma;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GerarTurmasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // "A, B, C" ou "1, 2, 3" -> array de sufixos para os nomes.
        if (is_string($this->input('sufixos'))) {
            $sufixos = collect(explode(',', $this->input('sufixos')))
                ->map(fn ($s) => trim($s))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $this->merge(['sufixos' => $sufixos]);
        }
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;

        return [
            'ano_letivo_id' => [
                'required', 'integer',
                Rule::exists('anos_letivos', 'id')->where('empresa_id', $empresaId),
            ],
            'etapas' => ['required', 'array', 'min:1'],
            'etapas.*' => [
                'integer',
                Rule::exists('etapas', 'id')->where('empresa_id', $empresaId),
            ],
            'sufixos' => ['required', 'array', 'min:1', 'max:26'],
            'sufixos.*' => ['string', 'max:10'],
            'turnos' => ['nullable', 'array'],
            'turnos.*' => [Rule::in(array_keys(Turma::TURNOS))],
            'sala' => ['nullable', 'string', 'max:40'],
            'capacidade_maxima' => ['nullable', 'integer', 'min:1', 'max:500'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
