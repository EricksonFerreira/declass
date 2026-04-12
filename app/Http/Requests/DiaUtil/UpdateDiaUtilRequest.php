<?php

namespace App\Http\Requests\DiaUtil;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDiaUtilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dia' => 'required|integer|max:6|min:0|unique:dias_uteis,dia,' . $this->route('dia_util'),
            'nome' => 'required|string|max:255|unique:dias_uteis,nome,' . $this->route('dia_util'),
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'dia.required' => 'O campo dia é obrigatório',
            'dia.integer' => 'O campo dia deve ser um número inteiro',
            'dia.max' => 'O campo dia deve ter o valor máximo 6',
            'dia.min' => 'O campo dia deve ter o valor mínimo 0',
            'dia.unique' => 'Já existe um dia útil com esse valor',
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'nome.unique' => 'Já existe um nome com esse nome',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
        ];
    }
}
