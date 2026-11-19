<?php

namespace App\Http\Requests\Turma;

use App\Models\Turma;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Criacao em lote manual: campos compartilhados (escola, ano letivo,
 * etapa, turno, sala, capacidade, status) + linhas de turmas, cada uma
 * com nome e grade de horario proprios.
 */
class StoreTurmasLoteRequest extends FormRequest
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
            'turno' => ['nullable', Rule::in(array_keys(Turma::TURNOS))],
            'sala' => ['nullable', 'string', 'max:40'],
            'capacidade_maxima' => ['nullable', 'integer', 'min:1', 'max:500'],
            'ativo' => ['required', Rule::in(['S', 'N'])],
            'turmas' => ['required', 'array', 'min:1', 'max:50'],
            'turmas.*.nome' => ['required', 'string', 'max:120', 'distinct'],
            'turmas.*.horario_id' => [
                'nullable', 'integer',
                Rule::exists('horarios', 'id')->where('empresa_id', $empresaId),
                function (string $attribute, $value, \Closure $fail) use ($escolaId) {
                    if ($value === null) {
                        return;
                    }
                    $horarioEscola = \App\Models\Horario::query()->whereKey($value)->value('escola_id');
                    if ($horarioEscola !== null && (int) $horarioEscola !== (int) $escolaId) {
                        $fail('A grade de horário selecionada não pertence à escola informada.');
                    }
                },
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'turmas.*.nome' => 'nome da turma',
            'turmas.*.horario_id' => 'grade de horário',
        ];
    }
}
