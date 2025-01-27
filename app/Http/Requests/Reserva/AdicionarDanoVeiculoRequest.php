<?php

namespace App\Http\Requests\Reserva;

use Illuminate\Foundation\Http\FormRequest;

class AdicionarDanoVeiculoRequest extends FormRequest
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
            'categoria_id' => 'required|integer|exists:categoria_danos,id',
            'localizacao_problema' => 'required|string|max:255',
            'dano_id' => 'required|integer|exists:danos,id',
            'tipo_dano' => 'required|string|in:ausente,amolgadela_leve,amolgadela_grave,risco_leve,risco_grave',
            'local_dano' => 'required|string|in:direita,esquerda,frente,atras,cima',
            'custo_estimado' => 'required|numeric',
            'quilometragem_veiculo' => 'required|numeric',
            'observacoes' => 'nullable|string|max:255',
            'fotos' => 'nullable|array',
            'fotos.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'veiculo_id' => 'required|exists:veiculos,id',
        ];
    }

    public function messages()
    {
        return [
            'categoria_id.required' => 'A categoria é obrigatória.',
            'categoria_id.integer' => 'A categoria deve ser um número inteiro.',
            'categoria_id.exists' => 'A categoria selecionada é inválida.',
            'localizacao_problema.required' => 'A localização é obrigatória.',
            'localizacao_problema.string' => 'A localização deve ser uma string.',
            'localizacao_problema.max' => 'A localização deve ter no máximo 255 caracteres.',
            'dano_id.required' => 'O dano é obrigatório.',
            'dano_id.string' => 'O dano deve ser uma string.',
            'dano_id.max' => 'O dano deve ter no máximo 255 caracteres.',
            'tipo_dano.required' => 'O tipo é obrigatório.',
            'tipo_dano.string' => 'O tipo deve ser uma string.',
            'tipo_dano.in' => 'O tipo selecionado é inválido.',
            'local_dano.required' => 'O local do dano é obrigatório.',
            'local_dano.string' => 'O local do dano deve ser uma string.',
            'local_dano.in' => 'O local do dano selecionado é inválido.',
            'custo_estimado.required' => 'O custo estimado é obrigatório.',
            'custo_estimado.numeric' => 'O custo estimado deve ser um número.',
            'quilometragem_veiculo.required' => 'A quilometragem é obrigatória.',
            'quilometragem_veiculo.numeric' => 'A quilometragem deve ser um número.',
            'observacoes.string' => 'As observações devem ser uma string.',
            'observacoes.max' => 'As observações não podem ter mais de 255 caracteres.',
            'fotos.array' => 'As fotos devem ser um array.',
            'fotos.*.image' => 'Cada foto deve ser uma imagem.',
            'fotos.*.mimes' => 'Cada foto deve ser do tipo jpeg, png, jpg, gif ou svg.',
            'fotos.*.max' => 'Cada foto não pode ter mais de 2048 kilobytes.',
            'veiculo_id.required' => 'Você não selecionou um veículo.',
            'veiculo_id.integer' => 'O veículo deve ser um número inteiro.',
            'veiculo_id.exists' => 'O veículo não existe.',
        ];
    }
}
