<?php

namespace App\Http\Requests\Empresa;

use App\Models\Empresa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'site_whatsapp' => $this->onlyDigits($this->input('site_whatsapp')),
        ]);
    }

    public function rules(): array
    {
        return [
            'site_ativo' => ['required', Rule::in(['S', 'N'])],
            'site_modelo' => ['required', Rule::in(array_keys(Empresa::SITE_MODELOS))],
            'site_titulo' => ['nullable', 'string', 'max:255'],
            'site_descricao' => ['nullable', 'string', 'max:2000'],
            'site_aviso' => ['nullable', 'string', 'max:255'],
            'site_whatsapp' => ['nullable', 'string', 'max:30'],
            'site_instagram' => ['nullable', 'string', 'max:100'],
            'site_unidades' => ['nullable', Rule::in(['S', 'N'])],
            'site_matricula' => ['nullable', Rule::in(['S', 'F', 'N'])],
            'site_matricula_ano' => ['nullable', 'string', 'max:10'],
            'site_contato' => ['nullable', Rule::in(['S', 'N'])],
            'site_banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'site_imagem_sobre' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'site_galeria' => ['nullable', 'array', 'max:12'],
            'site_galeria.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'galeria_remover' => ['nullable', 'array'],
            'galeria_remover.*' => ['string', 'max:255'],
            'site_depoimentos' => ['nullable', 'string', 'max:4000'],
        ];
    }

    private function onlyDigits(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/\D+/', '', $value);
    }
}
