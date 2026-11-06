<?php

namespace App\Http\Requests\HorarioMatriz;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHorarioMatrizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['integer', Rule::in(array_keys(\App\Models\HorarioSlot::DIAS))],
            'aulas' => ['required', 'array', 'min:1', 'max:12'],
            'aulas.*.inicio' => ['required', 'date_format:H:i'],
            'aulas.*.fim' => ['required', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ((array) $this->input('aulas', []) as $i => $aula) {
                $inicio = $aula['inicio'] ?? null;
                $fim = $aula['fim'] ?? null;
                if ($inicio && $fim && $fim <= $inicio) {
                    $v->errors()->add("aulas.{$i}.fim", 'O fim deve ser posterior ao início.');
                }
            }

            $aulas = collect($this->input('aulas', []))
                ->map(fn ($a) => [$a['inicio'] ?? null, $a['fim'] ?? null])
                ->filter(fn ($a) => $a[0] && $a[1])
                ->sortBy(fn ($a) => $a[0])
                ->values();

            for ($i = 1; $i < $aulas->count(); $i++) {
                if ($aulas[$i][0] < $aulas[$i - 1][1]) {
                    $v->errors()->add('aulas', 'Há horários sobrepostos: cada aula deve começar após o fim da anterior.');
                    break;
                }
            }
        });
    }
}
