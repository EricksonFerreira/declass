<?php

namespace App\Http\Requests\Aluno;

class UpdateAlunoDocumentoRequest extends StoreAlunoDocumentoRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // arquivo opcional na edicao (so substitui se enviado)
        $rules['arquivo'] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'];

        return $rules;
    }
}
