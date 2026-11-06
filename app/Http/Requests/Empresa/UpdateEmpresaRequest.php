<?php

namespace App\Http\Requests\Empresa;

use App\Support\SchemaCache;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cnpj' => $this->onlyDigits($this->input('cnpj')),
            'telefone' => $this->onlyDigits($this->input('telefone')),
            'cor_primaria' => $this->normalizeColor($this->input('cor_primaria')),
            'cor_secundaria' => $this->normalizeColor($this->input('cor_secundaria')),
            'cor_terciaria' => $this->normalizeColor($this->input('cor_terciaria')),
        ]);
    }

    public function rules(): array
    {
        $empresaId = $this->route('empresa');
        $slugRules = ['required', 'string', 'max:255'];

        if (SchemaCache::hasTable('empresas')) {
            $slugRules[] = Rule::unique('empresas', 'slug')->ignore($empresaId);
        }

        return [
            'nome' => ['required', 'string', 'max:255'],
            'slug' => $slugRules,
            'ativo' => ['nullable', Rule::in(['S', 'N'])],
            'cnpj' => ['nullable', 'digits:14'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'telefone' => ['nullable', 'digits_between:10,11'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'dominio' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'cor_primaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'cor_secundaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
            'cor_terciaria' => ['nullable', 'regex:/^#[A-F0-9]{6}$/'],
        ];
    }

    private function onlyDigits(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/\D+/', '', $value);
    }

    private function normalizeColor(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $color = strtoupper(trim($value));

        if (! str_starts_with($color, '#')) {
            $color = '#'.$color;
        }

        return $color;
    }
}
