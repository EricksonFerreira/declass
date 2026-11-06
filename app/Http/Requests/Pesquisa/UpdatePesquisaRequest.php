<?php

namespace App\Http\Requests\Pesquisa;

class UpdatePesquisaRequest extends StorePesquisaRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // escola_id imutavel apos a criacao (mesmo padrao dos demais modulos)
        unset($rules['escola_id']);

        return $rules;
    }
}
