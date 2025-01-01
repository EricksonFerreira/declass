<?php

namespace App\Http\Requests\ConfiguracaoChecklist;

use Illuminate\Foundation\Http\FormRequest;

class StoreConfiguracaoChecklistRequest extends FormRequest
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
        return [
            'tipo_checklist_id' => 'required|exists:tipo_checklist,id',
            'item_checklist_id' => 'array',
            'item_checklist_id.*' => 'exists:item_checklist,id',
        ];
    }

    public function messages()
    {
        return [
            'tipo_checklist_id.required' => 'O campo tipo de checklist é obrigatório',
            'tipo_checklist_id.exists' => 'O campo tipo de checklist é inválido',
            'item_checklist_id.array' => 'O campo item do checklist deve ser um array',
            'item_checklist_id.*.exists' => 'O campo item do checklist é inválido',
        ];
    }
}
