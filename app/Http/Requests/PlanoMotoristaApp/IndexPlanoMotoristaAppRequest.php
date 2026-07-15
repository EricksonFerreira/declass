<?php

namespace App\Http\Requests\PlanoMotoristaApp;

use Illuminate\Foundation\Http\FormRequest;

class IndexPlanoMotoristaAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
