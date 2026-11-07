<?php

namespace App\Http\Requests\Turma;

use App\Models\Turma;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTurmaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = $this->user()?->empresa_id;
        $escolaId = $this->input('escola_id');

        return [
            'escola_id' => [
                'required', 'integer',
                Rule::exists('escolas', 'id')->where('empresa_id', $empresaId),
            ],
            'ano_letivo_id' => [
                'required', 'integer',
                Rule::exists('anos_letivos', 'id')->where('empresa_id', $empresaId),
                function (string $attribute, $value, \Closure $fail) use ($escolaId) {
                    $anoLetivoEscola = \App\Models\AnoLetivo::query()->whereKey($value)->value('escola_id');
                    if ($anoLetivoEscola !== null && (int) $anoLetivoEscola !== (int) $escolaId) {
                        $fail('O ano letivo selecionado não pertence à escola informada.');
                    }
                },
            ],
            'etapa_id' => [
                'required', 'integer',
                Rule::exists('etapas', 'id')->where('empresa_id', $empresaId),
            ],
            'nome' => ['required', 'string', 'max:120'],
            'turno' => ['nullable', Rule::in(array_keys(Turma::TURNOS))],
            'horario_id' => [
                'nullable', 'integer',
                Rule::exists('horarios', 'id')->where('empresa_id', $empresaId),
                function (string $attribute, $value, \Closure $fail) use ($escolaId) {
                    $horarioEscola = \App\Models\Horario::query()->whereKey($value)->value('escola_id');
                    if ($horarioEscola !== null && (int) $horarioEscola !== (int) $escolaId) {
                        $fail('O horário selecionado não pertence à escola informada.');
                    }
                },
            ],
            'sala' => ['nullable', 'string', 'max:40'],
            'capacidade_maxima' => ['nullable', 'integer', 'min:1', 'max:500'],
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
        ];
    }
}
