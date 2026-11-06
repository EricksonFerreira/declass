<?php

namespace App\Http\Requests\Material;

class UpdateMaterialRequest extends StoreMaterialRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        // escola_id imutavel apos a criacao (mesmo padrao dos demais modulos)
        unset($rules['escola_id']);

        return $rules;
    }
}
