<?php

namespace App\Http\Requests\Tutorial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTutorialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tutorialId = $this->route('tutorial')?->id;

        return [
            'titulo' => [
                'required', 'string', 'max:255',
                Rule::unique('tutoriais', 'titulo')->ignore($tutorialId),
            ],
            'tutorial_categoria_id' => ['nullable', 'exists:tutorial_categorias,id'],
            'ordem' => ['nullable', 'integer', 'min:0'],
            'ativo' => ['nullable', 'boolean'],
            'visibilidade' => ['required', Rule::in(['todos', 'superadmin'])],
            'principal' => ['nullable', 'boolean'],
            'descricao' => ['nullable', 'string'],
            'video_tipo' => ['required', Rule::in(['arquivo', 'link'])],
            'video' => [
                'nullable', 'file', 'max:512000',
                'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime',
                'required_if:video_tipo,arquivo',
            ],
            'video_url' => [
                'nullable', 'string', 'max:500',
                'required_if:video_tipo,link',
            ],
            'capa' => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O campo título é obrigatório',
            'titulo.unique' => 'Já existe um tutorial com este título',
            'visibilidade.required' => 'Selecione a visibilidade',
            'visibilidade.in' => 'Visibilidade inválida',
            'video.required_if' => 'Envie o arquivo de vídeo',
            'video.mimetypes' => 'O vídeo deve ser MP4, WebM, OGG ou MOV',
            'video.max' => 'O vídeo não pode exceder 500MB',
            'video_url.required_if' => 'Informe o link do vídeo',
            'video_url.max' => 'O link do vídeo é muito longo',
            'capa.image' => 'A capa deve ser uma imagem',
            'capa.max' => 'A capa não pode exceder 5MB',
            'tutorial_categoria_id.exists' => 'Categoria inválida',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->boolean('ativo'),
            'principal' => $this->boolean('principal'),
            'ordem' => (int) $this->input('ordem', 0),
        ]);
    }
}
