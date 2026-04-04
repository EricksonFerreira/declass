<?php

namespace App\Http\Requests\GrupoFrota;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGrupoFrotaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $grupoFrota = $this->route('grupo_frotum') ?? $this->route('grupo_frota') ?? $this->id;
        $grupoFrotaId = is_object($grupoFrota) ? $grupoFrota->id : $grupoFrota;
        
        $empresaId =  session('empresa_context_id') 
        ?? auth()->user()?->empresa_id
        ?? request()->input('empresa_id');
        
        return [
            'codigo' => ['required', 'string', 'max:50'],
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('grupos_frota', 'nome')
                    ->where(fn ($query) => $query->where('empresa_id', $empresaId))
                    ->ignore($grupoFrotaId),
            ],
            'codigo_sipp' => ['required', 'string', 'max:50'],
            'tipo_frota_id' => ['required', 'integer', 'exists:tipo_frota,id'],
            'valor_diario' => ['required', 'string'],
            'ativo' => ['required', 'string', 'size:1'],

            'imagem' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'imagens' => ['nullable', 'array'],
            'imagens.*' => ['file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],

            'caracteristicas' => ['nullable', 'array'],
            'caracteristicas.*' => ['integer', 'exists:caracteristicas_frotas,id'],

            'caracteristicaValor' => ['nullable', 'array'],

            'opcionais' => ['nullable', 'array'],
            'opcionais.*' => ['integer', 'exists:opcionais,id'],

            'modelos' => ['nullable', 'array'],
            'modelos.*' => ['integer', 'exists:modelos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'O campo código é obrigatório.',
            'codigo.string' => 'O campo código deve ser uma string.',
            'codigo.max' => 'O campo código deve ter no máximo 50 caracteres.',

            'nome.required' => 'O campo nome é obrigatório.',
            'nome.string' => 'O campo nome deve ser uma string.',
            'nome.max' => 'O campo nome deve ter no máximo 255 caracteres.',
            'nome.unique' => 'Já existe um grupo de frota com este nome para esta empresa.',

            'codigo_sipp.required' => 'O campo código SIPP é obrigatório.',
            'codigo_sipp.string' => 'O campo código SIPP deve ser uma string.',
            'codigo_sipp.max' => 'O campo código SIPP deve ter no máximo 50 caracteres.',

            'tipo_frota_id.required' => 'O campo tipo de frota é obrigatório.',
            'tipo_frota_id.integer' => 'O campo tipo de frota deve ser um inteiro.',
            'tipo_frota_id.exists' => 'O tipo de frota informado não existe.',

            'valor_diario.required' => 'O campo valor diário é obrigatório.',

            'ativo.required' => 'O campo ativo é obrigatório.',
            'ativo.string' => 'O campo ativo deve ser uma string.',
            'ativo.size' => 'O campo ativo deve ter exatamente 1 caractere.',

            'imagem.file' => 'O arquivo enviado é inválido.',
            'imagem.mimes' => 'A imagem deve ser do tipo: jpg, jpeg, png, svg ou webp.',
            'imagem.max' => 'O arquivo deve ter no máximo 2048 kilobytes.',
            'imagens.array' => 'As imagens devem ser enviadas em formato de lista.',
            'imagens.*.file' => 'Um dos arquivos enviados é inválido.',
            'imagens.*.mimes' => 'As imagens devem ser do tipo: jpg, jpeg, png, svg ou webp.',
            'imagens.*.max' => 'Cada imagem deve ter no máximo 2048 kilobytes.',

            'caracteristicas.array' => 'As características devem ser enviadas em formato de lista.',
            'caracteristicas.*.integer' => 'Cada característica deve ser um número inteiro.',
            'caracteristicas.*.exists' => 'Uma das características informadas não existe.',

            'caracteristicaValor.array' => 'Os valores das características estão inválidos.',

            'opcionais.array' => 'Os opcionais devem ser enviados em formato de lista.',
            'opcionais.*.integer' => 'Cada opcional deve ser um número inteiro.',
            'opcionais.*.exists' => 'Um dos opcionais informados não existe.',

            'modelos.array' => 'Os modelos devem ser enviados em formato de lista.',
            'modelos.*.integer' => 'Cada modelo deve ser um número inteiro.',
            'modelos.*.exists' => 'Um dos modelos informados não existe.',
        ];
    }
}
