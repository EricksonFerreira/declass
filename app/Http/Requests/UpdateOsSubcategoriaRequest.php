<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOsSubcategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'os_categoria_id' => ['required', 'exists:os_categorias,id'],
            'nome' => ['required', 'string', 'max:255'],
            'show_sinistro' => ['boolean'],
            'show_reserva' => ['boolean'],
            'show_avarias' => ['boolean'],
            'show_checklist_prevencao' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }
}
