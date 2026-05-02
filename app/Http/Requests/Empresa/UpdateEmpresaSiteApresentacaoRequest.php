<?php

namespace App\Http\Requests\Empresa;

use App\Support\DomainNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UpdateEmpresaSiteApresentacaoRequest extends FormRequest
{
    private const COLOR_FIELDS = [
        'cor_primaria',
        'cor_secundaria',
        'cor_terciaria',
        'cor_topbar_fundo',
        'cor_topbar_texto',
        'cor_topbar_link_hover',
        'cor_hero_fundo',
        'cor_hero_texto',
        'cor_form_fundo',
        'cor_form_texto',
        'cor_frota_fundo',
        'cor_vantagens_fundo',
        'cor_etapas_fundo',
        'cor_contato_fundo',
        'cor_footer_fundo',
        'cor_footer_texto',
        'cor_botao_primario_fundo',
        'cor_botao_primario_texto',
        'cor_botao_secundario_fundo',
        'cor_botao_secundario_texto',
        'cor_admin_fundo',
        'cor_admin_texto',
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = [
            'dominio' => DomainNormalizer::normalize($this->input('dominio')),
            'site_apresentacao_modelo' => $this->normalizeModelo($this->input('site_apresentacao_modelo')),
            'site_apresentacao_botao_url' => $this->normalizeButtonUrl($this->input('site_apresentacao_botao_url')),
        ];

        $imageFields = [
            'logo',
            'imagem_hero',
            'imagem_frota_1',
            'imagem_frota_2',
            'imagem_frota_3',
            'imagem_vantagens',
            'imagem_contato',
        ];

        foreach ($imageFields as $field) {
            $file = $this->file($field);

            if ($file === null) {
                continue;
            }

            if (!$file->isValid() || ($file->getPathname() === '') || $file->getSize() === 0) {
                $this->files->remove($field);
            }
        }

        foreach (self::COLOR_FIELDS as $field) {
            $payload[$field] = $this->normalizeColor($this->input($field));
        }

        $this->merge($payload);
    }

    public function rules(): array
    {
        $empresaId = $this->route('empresa');
        $dominioRules = ['nullable', 'string', 'max:255'];

        if (Schema::hasTable('empresas')) {
            $dominioRules[] = Rule::unique('empresas', 'dominio')->ignore($empresaId);
        }

        return [
            'dominio' => $dominioRules,
            'site_apresentacao_ativo' => ['nullable', Rule::in(['S', 'N'])],
            'site_apresentacao_modelo' => ['nullable', Rule::in(['modelo_1', 'modelo_2', 'modelo_3'])],
            'site_apresentacao_titulo' => ['nullable', 'string', 'max:120'],
            'site_apresentacao_subtitulo' => ['nullable', 'string', 'max:180'],
            'site_apresentacao_descricao' => ['nullable', 'string', 'max:2000'],
            'site_apresentacao_botao_texto' => ['nullable', 'string', 'max:60'],
            'site_apresentacao_botao_url' => ['nullable', 'string', 'max:255'],
            'logo' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'hero_tag' => ['nullable', 'string', 'max:120'],
            'hero_titulo' => ['nullable', 'string', 'max:180'],
            'hero_subtitulo' => ['nullable', 'string', 'max:220'],
            'hero_descricao' => ['nullable', 'string', 'max:2000'],
            'frota_titulo' => ['nullable', 'string', 'max:160'],
            'frota_descricao' => ['nullable', 'string', 'max:1000'],
            'frota_card_1_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_1_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_2_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_2_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_3_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_3_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_4_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_4_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_5_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_5_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_6_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_6_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_7_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_7_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_8_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_8_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_9_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_9_descricao' => ['nullable', 'string', 'max:400'],
            'frota_card_10_titulo' => ['nullable', 'string', 'max:120'],
            'frota_card_10_descricao' => ['nullable', 'string', 'max:400'],
            'vantagens_titulo' => ['nullable', 'string', 'max:160'],
            'vantagens_descricao' => ['nullable', 'string', 'max:1000'],
            'beneficio_1' => ['nullable', 'string', 'max:220'],
            'beneficio_2' => ['nullable', 'string', 'max:220'],
            'beneficio_3' => ['nullable', 'string', 'max:220'],
            'beneficio_4' => ['nullable', 'string', 'max:220'],
            'beneficio_5' => ['nullable', 'string', 'max:220'],
            'beneficio_6' => ['nullable', 'string', 'max:220'],
            'beneficio_7' => ['nullable', 'string', 'max:220'],
            'beneficio_8' => ['nullable', 'string', 'max:220'],
            'beneficio_9' => ['nullable', 'string', 'max:220'],
            'beneficio_10' => ['nullable', 'string', 'max:220'],
            'etapas_titulo' => ['nullable', 'string', 'max:160'],
            'etapa_1_titulo' => ['nullable', 'string', 'max:120'],
            'etapa_1_descricao' => ['nullable', 'string', 'max:300'],
            'etapa_2_titulo' => ['nullable', 'string', 'max:120'],
            'etapa_2_descricao' => ['nullable', 'string', 'max:300'],
            'etapa_3_titulo' => ['nullable', 'string', 'max:120'],
            'etapa_3_descricao' => ['nullable', 'string', 'max:300'],
            'contato_titulo' => ['nullable', 'string', 'max:160'],
            'menu_item_frota' => ['nullable', 'string', 'max:50'],
            'menu_item_vantagens' => ['nullable', 'string', 'max:50'],
            'menu_item_etapas' => ['nullable', 'string', 'max:50'],
            'menu_item_contato' => ['nullable', 'string', 'max:50'],
            'hero_form_titulo' => ['nullable', 'string', 'max:120'],
            'hero_form_descricao' => ['nullable', 'string', 'max:500'],
            'hero_form_label_retirada' => ['nullable', 'string', 'max:80'],
            'hero_form_label_devolucao' => ['nullable', 'string', 'max:80'],
            'hero_form_label_data_retirada' => ['nullable', 'string', 'max:80'],
            'hero_form_label_hora_retirada' => ['nullable', 'string', 'max:80'],
            'hero_form_label_data_devolucao' => ['nullable', 'string', 'max:80'],
            'hero_form_label_hora_devolucao' => ['nullable', 'string', 'max:80'],
            'hero_form_texto_botao' => ['nullable', 'string', 'max:80'],
            'hero_form_nota' => ['nullable', 'string', 'max:280'],
            'frota_label_superior' => ['nullable', 'string', 'max:60'],
            'vantagens_label_superior' => ['nullable', 'string', 'max:60'],
            'etapas_label_superior' => ['nullable', 'string', 'max:60'],
            'contato_label_superior' => ['nullable', 'string', 'max:60'],
            'botao_admin_texto' => ['nullable', 'string', 'max:80'],
            'footer_texto' => ['nullable', 'string', 'max:200'],
            'usar_imagem_hero' => ['nullable', 'boolean'],
            'usar_imagem_frota_1' => ['nullable', 'boolean'],
            'usar_imagem_frota_2' => ['nullable', 'boolean'],
            'usar_imagem_frota_3' => ['nullable', 'boolean'],
            'usar_imagem_vantagens' => ['nullable', 'boolean'],
            'usar_imagem_contato' => ['nullable', 'boolean'],
            'imagem_hero' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'imagem_frota_1' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'imagem_frota_2' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'imagem_frota_3' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'imagem_frota_4' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_frota_5' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_frota_6' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_frota_7' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_frota_8' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_frota_9' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_frota_10' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'imagem_vantagens' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'imagem_contato' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
            'cor_primaria' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_secundaria' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_terciaria' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_topbar_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_topbar_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_topbar_link_hover' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_hero_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_hero_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_form_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_form_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_etiqueta_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_titulo_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_descricao_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_card_titulo_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_card_descricao_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_frota_card_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_vantagens_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_vantagens_etiqueta_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_vantagens_titulo_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_vantagens_descricao_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_vantagens_item_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_etiqueta_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_titulo_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_descricao_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_passo_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_passo_descricao_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_etapas_passo_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_contato_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_contato_etiqueta_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_contato_titulo_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_contato_info_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_footer_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_footer_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_botao_primario_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_botao_primario_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_botao_secundario_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_botao_secundario_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_admin_fundo' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
            'cor_admin_texto' => ['nullable', 'regex:/^#([A-F0-9]{6}|[A-F0-9]{3})$/i'],
        ];
    }

    public function messages(): array
    {
        return [
            'dominio.unique' => 'Este dominio ja esta vinculado a outra empresa.',
            'site_apresentacao_ativo.in' => 'O status do site de apresentacao deve ser S ou N.',
            'site_apresentacao_modelo.in' => 'Modelo invalido. Escolha Modelo 1 ou Modelo 2.',
            'logo.image' => 'A logo deve ser uma imagem valida.',
            'logo.mimes' => 'A logo deve estar em um formato valido: jpg, jpeg, png, webp ou svg.',
            'logo.max' => 'A logo deve ter no maximo 2MB.',
            'imagem_hero.image' => 'A imagem do hero deve ser uma imagem valida.',
            'imagem_hero.mimes' => 'A imagem do hero deve ser JPG, JPEG, PNG ou WEBP.',
            'imagem_hero.max' => 'A imagem do hero deve ter no maximo 4MB.',
            'cor_primaria.regex' => 'A cor primaria deve estar no formato hexadecimal. Ex: #1F2937.',
            'cor_secundaria.regex' => 'A cor secundaria deve estar no formato hexadecimal. Ex: #334155.',
            'cor_terciaria.regex' => 'A cor terciaria deve estar no formato hexadecimal. Ex: #64748B.',
            'cor_topbar_fundo.regex' => 'A cor do topo deve estar no formato hexadecimal. Ex: #1F2937.',
            'cor_topbar_texto.regex' => 'A cor do texto do topo deve estar no formato hexadecimal. Ex: #FFFFFF.',
            'cor_topbar_link_hover.regex' => 'A cor hover do menu deve estar no formato hexadecimal. Ex: #E2E8F0.',
            'cor_hero_fundo.regex' => 'A cor do bloco Hero deve estar no formato hexadecimal.',
            'cor_hero_texto.regex' => 'A cor do texto Hero deve estar no formato hexadecimal.',
            'cor_form_fundo.regex' => 'A cor do formulario deve estar no formato hexadecimal.',
            'cor_form_texto.regex' => 'A cor dos textos do formulario deve estar no formato hexadecimal.',
            'cor_frota_fundo.regex' => 'A cor da secao Frota deve estar no formato hexadecimal.',
            'cor_frota_etiqueta_texto.regex' => 'A cor do texto da etiqueta da Frota deve estar no formato hexadecimal.',
            'cor_frota_titulo_texto.regex' => 'A cor do texto do titulo da Frota deve estar no formato hexadecimal.',
            'cor_frota_descricao_texto.regex' => 'A cor do texto da descricao da Frota deve estar no formato hexadecimal.',
            'cor_frota_card_titulo_texto.regex' => 'A cor do texto do titulo do card da Frota deve estar no formato hexadecimal.',
            'cor_frota_card_descricao_texto.regex' => 'A cor do texto da descricao do card da Frota deve estar no formato hexadecimal.',
            'cor_frota_card_fundo.regex' => 'A cor do fundo do card da Frota deve estar no formato hexadecimal.',
            'cor_vantagens_fundo.regex' => 'A cor da secao Vantagens deve estar no formato hexadecimal.',
            'cor_vantagens_etiqueta_texto.regex' => 'A cor do texto da etiqueta das Vantagens deve estar no formato hexadecimal.',
            'cor_vantagens_titulo_texto.regex' => 'A cor do texto do titulo das Vantagens deve estar no formato hexadecimal.',
            'cor_vantagens_descricao_texto.regex' => 'A cor do texto da descricao das Vantagens deve estar no formato hexadecimal.',
            'cor_vantagens_item_texto.regex' => 'A cor do texto do item das Vantagens deve estar no formato hexadecimal.',
            'cor_etapas_fundo.regex' => 'A cor da secao Etapas deve estar no formato hexadecimal.',
            'cor_etapas_etiqueta_texto.regex' => 'A cor do texto da etiqueta das Etapas deve estar no formato hexadecimal.',
            'cor_etapas_titulo_texto.regex' => 'A cor do texto do titulo das Etapas deve estar no formato hexadecimal.',
            'cor_etapas_descricao_texto.regex' => 'A cor do texto da descricao das Etapas deve estar no formato hexadecimal.',
            'cor_etapas_passo_texto.regex' => 'A cor do texto do passo deve estar no formato hexadecimal.',
            'cor_etapas_passo_descricao_texto.regex' => 'A cor do texto da descricao do passo deve estar no formato hexadecimal.',
            'cor_etapas_passo_fundo.regex' => 'A cor do fundo do passo deve estar no formato hexadecimal.',
            'cor_contato_fundo.regex' => 'A cor da secao Contato deve estar no formato hexadecimal.',
            'cor_contato_etiqueta_texto.regex' => 'A cor do texto da etiqueta de Contato deve estar no formato hexadecimal.',
            'cor_contato_titulo_texto.regex' => 'A cor do texto do titulo de Contato deve estar no formato hexadecimal.',
            'cor_contato_info_texto.regex' => 'A cor do texto das informacoes de Contato deve estar no formato hexadecimal.',
            'cor_footer_fundo.regex' => 'A cor do rodape deve estar no formato hexadecimal.',
            'cor_footer_texto.regex' => 'A cor do texto do rodape deve estar no formato hexadecimal.',
            'cor_botao_primario_fundo.regex' => 'A cor de fundo do botao principal deve estar no formato hexadecimal.',
            'cor_botao_primario_texto.regex' => 'A cor do texto do botao principal deve estar no formato hexadecimal.',
            'cor_botao_secundario_fundo.regex' => 'A cor de fundo do botao secundario deve estar no formato hexadecimal.',
            'cor_botao_secundario_texto.regex' => 'A cor do texto do botao secundario deve estar no formato hexadecimal.',
            'cor_admin_fundo.regex' => 'A cor de fundo da tela administrativa deve estar no formato hexadecimal.',
            'cor_admin_texto.regex' => 'A cor do texto da tela administrativa deve estar no formato hexadecimal.',
        ];
    }

    private function normalizeButtonUrl(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    private function normalizeModelo(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return 'modelo_1';
        }

        return trim(strtolower($value));
    }

    private function normalizeColor(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $color = strtoupper(trim($value));

        if (!str_starts_with($color, '#')) {
            $color = '#' . $color;
        }

        return $color;
    }
}
