<?php

namespace App\Http\Requests\Aluno;

use App\Models\AlunoDocumento;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAlunoDocumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(array_keys(AlunoDocumento::TIPOS))],
            'titulo' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'arquivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
        ];
    }
}
