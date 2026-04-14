<?php

namespace App\Http\Requests\HorarioDiario;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreHorarioDiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        return [
            'nome' => [
                'required', 'string', 'max:255',
                Rule::unique('horarios_diarios', 'nome')->where('empresa_id', $empresaId),
            ],
            'ativo' => 'required|string|max:1',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'nome.unique' => 'Já existe um horário diário com esse nome nesta empresa',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
        ];
    }
}
