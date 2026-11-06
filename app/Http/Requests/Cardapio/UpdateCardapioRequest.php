<?php

namespace App\Http\Requests\Cardapio;

class UpdateCardapioRequest extends StoreCardapioRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // escola_id e imutavel apos a criacao (mesmo padrao dos demais modulos)
        unset($rules['escola_id']);

        return $rules;
    }
}
