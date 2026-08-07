<?php

namespace App\Http\Requests\Opcional;

use App\Support\EmpresaContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOpcionalRequest extends FormRequest
{
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
        $empresaId = app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id;

        return [
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('opcionais', 'nome')->where(fn ($query) => $query->where('empresa_id', $empresaId)),
            ],
            'ativo' => 'required|string|max:1',
            'codigo_servico_municipal' => 'nullable|string|max:20',
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O campo nome é obrigatório',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres',
            'nome.unique' => 'O nome informado já está em uso para esta empresa',
            'ativo.required' => 'O campo ativo é obrigatório',
            'ativo.max' => 'O campo ativo deve ter no máximo 1 caractere',
        ];
    }
}
