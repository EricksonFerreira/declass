<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Estacao;
use App\Support\DomainNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteApresentacaoController extends Controller
{
    private const MODELO_1 = 'modelo_1';

    public function index(Request $request): View|RedirectResponse
    {
        $host = DomainNormalizer::normalize($request->getHost());
        $empresa = $this->resolveEmpresaByHost($host);

        if (!$empresa || ($empresa->site_apresentacao_ativo ?? 'S') !== 'S') {
            return $this->defaultResponse();
        }

        $modelo = $this->resolveModelo((string) ($empresa->site_apresentacao_modelo ?? self::MODELO_1));
        $corPrimaria = $empresa->cor_primaria ?: '#2F6F3F';
        $corSecundaria = $empresa->cor_secundaria ?: '#193C68';
        $corTerciaria = $empresa->cor_terciaria ?: '#E7B93A';
        $textos = is_array($empresa->site_apresentacao_textos) ? $empresa->site_apresentacao_textos : [];
        $imagens = is_array($empresa->site_apresentacao_imagens) ? $empresa->site_apresentacao_imagens : [];
        $cores = is_array($empresa->site_apresentacao_cores) ? $empresa->site_apresentacao_cores : [];
        $imagensConfig = is_array($empresa->site_apresentacao_imagens_config) ? $empresa->site_apresentacao_imagens_config : [];
        $estacoesSelect = $this->estacoesForEmpresa($empresa);
        $frotaDestaques = $this->frotaDestaques($textos, $imagens, $imagensConfig);
        $urlReserva = Route::has('site.reserva.index')
            ? route('site.reserva.index')
            : $this->resolveButtonUrl($empresa->site_apresentacao_botao_url);
        $urlAdminEntrada = Route::has('site.admin.entry')
            ? route('site.admin.entry')
            : (Route::has('login') ? route('login') : $this->adminLoginFallbackUrl());

        return view($this->resolveTemplate($modelo), [
            'empresa' => $empresa,
            'modelo' => $modelo,
            'heroTag' => $this->textValue($textos, 'hero_tag', 'Modelo 1 - Premium Locadora'),
            'titulo' => $this->textValue($textos, 'hero_titulo', $empresa->site_apresentacao_titulo ?: 'Aluguel de veiculos com praticidade e seguranca'),
            'subtitulo' => $this->textValue($textos, 'hero_subtitulo', $empresa->site_apresentacao_subtitulo ?: 'Reserve online, retire rapido e viaje com cobertura nacional.'),
            'descricao' => $this->textValue($textos, 'hero_descricao', $empresa->site_apresentacao_descricao ?: 'Planos diarios, semanais e mensais para pessoas fisicas e empresas.'),
            'botaoTexto' => $empresa->site_apresentacao_botao_texto ?: 'Reservar agora',
            'botaoUrl' => $urlReserva,
            'botaoLoginUrl' => $urlAdminEntrada,
            'corPrimaria' => $corPrimaria,
            'corSecundaria' => $corSecundaria,
            'corTerciaria' => $corTerciaria,
            'corTextoPrimario' => $this->contrastTextColor($corPrimaria),
            'corTopbarFundo' => $this->colorValue($cores, 'topbar_fundo', '#FFFFFF'),
            'corTopbarTexto' => $this->colorValue($cores, 'topbar_texto', '#0F172A'),
            'corTopbarLinkHover' => $this->colorValue($cores, 'topbar_link_hover', $corPrimaria),
            'corHeroFundo' => $this->colorValue($cores, 'hero_fundo', $corPrimaria),
            'corHeroTexto' => $this->colorValue($cores, 'hero_texto', '#FFFFFF'),
            'corFormFundo' => $this->colorValue($cores, 'form_fundo', '#FFFFFF'),
            'corFormTexto' => $this->colorValue($cores, 'form_texto', '#0F172A'),
            'corFrotaFundo' => $this->colorValue($cores, 'frota_fundo', '#FFFFFF'),
            'corVantagensFundo' => $this->colorValue($cores, 'vantagens_fundo', '#FFFFFF'),
            'corEtapasFundo' => $this->colorValue($cores, 'etapas_fundo', '#FFFFFF'),
            'corContatoFundo' => $this->colorValue($cores, 'contato_fundo', '#FFFFFF'),
            'corFooterFundo' => $this->colorValue($cores, 'footer_fundo', '#FFFFFF'),
            'corFooterTexto' => $this->colorValue($cores, 'footer_texto', '#64748B'),
            'corBotaoPrimarioFundo' => $this->colorValue($cores, 'botao_primario_fundo', $corTerciaria),
            'corBotaoPrimarioTexto' => $this->colorValue($cores, 'botao_primario_texto', '#111827'),
            'corBotaoSecundarioFundo' => $this->colorValue($cores, 'botao_secundario_fundo', 'transparent'),
            'corBotaoSecundarioTexto' => $this->colorValue($cores, 'botao_secundario_texto', '#FFFFFF'),
            'contatoTelefone' => $this->formatTelefone($empresa->telefone),
            'contatoEmail' => $empresa->email ?: 'contato@' . ($empresa->dominio ?: 'empresa.com.br'),
            'contatoCnpj' => $this->formatCnpj($empresa->cnpj),
            'anoAtual' => (int) now()->format('Y'),
            'menuItemFrota' => $this->textValue($textos, 'menu_item_frota', 'Frota'),
            'menuItemVantagens' => $this->textValue($textos, 'menu_item_vantagens', 'Vantagens'),
            'menuItemEtapas' => $this->textValue($textos, 'menu_item_etapas', 'Como funciona'),
            'menuItemContato' => $this->textValue($textos, 'menu_item_contato', 'Contato'),
            'frotaTitulo' => $this->textValue($textos, 'frota_titulo', 'Categorias para cada tipo de viagem'),
            'frotaLabelSuperior' => $this->textValue($textos, 'frota_label_superior', 'Frota em destaque'),
            'frotaDescricao' => $this->textValue($textos, 'frota_descricao', 'Selecione o grupo ideal e reserve com condicoes ajustadas ao seu perfil, seja turismo, trabalho ou locacao corporativa.'),
            'frotaDestaques' => $frotaDestaques,
            'vantagensTitulo' => $this->textValue($textos, 'vantagens_titulo', 'Por que escolher ' . $empresa->nome),
            'vantagensLabelSuperior' => $this->textValue($textos, 'vantagens_label_superior', 'Diferenciais'),
            'vantagensDescricao' => $this->textValue($textos, 'vantagens_descricao', 'Fluxo inspirado nas melhores locadoras do mercado, com foco em conversao, credibilidade e rapidez no atendimento.'),
            'beneficios' => $this->beneficios($textos),
            'etapasTitulo' => $this->textValue($textos, 'etapas_titulo', 'Como funciona a locacao'),
            'etapasLabelSuperior' => $this->textValue($textos, 'etapas_label_superior', 'Jornada do cliente'),
            'etapasReserva' => $this->etapasReserva($textos),
            'contatoTitulo' => $this->textValue($textos, 'contato_titulo', 'Informacoes da locadora'),
            'contatoLabelSuperior' => $this->textValue($textos, 'contato_label_superior', 'Contato direto'),
            'heroFormTitulo' => $this->textValue($textos, 'hero_form_titulo', 'Simule sua reserva'),
            'heroFormDescricao' => $this->textValue($textos, 'hero_form_descricao', 'Preencha os dados da viagem e siga para a reserva completa em passos.'),
            'heroFormLabelRetirada' => $this->textValue($textos, 'hero_form_label_retirada', 'Local de retirada'),
            'heroFormLabelDataRetirada' => $this->textValue($textos, 'hero_form_label_data_retirada', 'Data de retirada'),
            'heroFormLabelHoraRetirada' => $this->textValue($textos, 'hero_form_label_hora_retirada', 'Hora de retirada'),
            'heroFormLabelDataDevolucao' => $this->textValue($textos, 'hero_form_label_data_devolucao', 'Data de devolucao'),
            'heroFormLabelHoraDevolucao' => $this->textValue($textos, 'hero_form_label_hora_devolucao', 'Hora de devolucao'),
            'heroFormTextoBotao' => $this->textValue($textos, 'hero_form_texto_botao', 'Iniciar reserva'),
            'heroFormNota' => $this->textValue($textos, 'hero_form_nota', 'Ao avancar, voce segue para o fluxo completo de reserva da locadora.'),
            'botaoAdminTexto' => $this->textValue($textos, 'botao_admin_texto', 'Area administrativa'),
            'footerTexto' => $this->textValue($textos, 'footer_texto', 'Todos os direitos reservados.'),
            'imagemHero' => $this->imageUrl($imagens, 'hero', $this->imageEnabled($imagensConfig, 'hero')),
            'imagemVantagens' => $this->imageUrl($imagens, 'vantagens', $this->imageEnabled($imagensConfig, 'vantagens')),
            'imagemContato' => $this->imageUrl($imagens, 'contato', $this->imageEnabled($imagensConfig, 'contato')),
            'estacoesSelect' => $estacoesSelect,
        ]);
    }

    public function estacoesOptions(Request $request): JsonResponse
    {
        $host = DomainNormalizer::normalize($request->getHost());
        $empresa = $this->resolveEmpresaByHost($host);
        if (!$empresa) {
            return response()->json(['estacoes' => []]);
        }

        return response()->json([
            'estacoes' => $this->estacoesForEmpresa($empresa),
        ]);
    }

    private function resolveEmpresaByHost(?string $host): ?Empresa
    {
        if ($host === null || $host === '') {
            return null;
        }

        return Empresa::query()
            ->withoutGlobalScopes()
            ->where('ativo', 'S')
            ->whereNotNull('dominio')
            ->get()
            ->first(function (Empresa $empresa) use ($host): bool {
                return DomainNormalizer::normalize($empresa->dominio) === $host;
            });
    }

    private function resolveButtonUrl(?string $buttonUrl): string
    {
        $defaultUrl = Route::has('login') ? route('login') : $this->adminLoginFallbackUrl();

        if ($buttonUrl === null || trim($buttonUrl) === '') {
            return $defaultUrl;
        }

        $url = trim($buttonUrl);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return 'https://' . ltrim($url, '/');
    }

    private function textValue(array $textos, string $key, string $default = ''): string
    {
        $value = $textos[$key] ?? null;
        if (!is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? $default : $trimmed;
    }

    private function imageUrl(array $imagens, string $key, bool $enabled = true): ?string
    {
        if (!$enabled) {
            return null;
        }

        $path = $imagens[$key] ?? null;
        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        return Storage::url($path);
    }

    private function frotaDestaques(array $textos, array $imagens, array $imagensConfig): array
    {
        return [
            [
                'titulo' => $this->textValue($textos, 'frota_card_1_titulo', 'Economicos Urbanos'),
                'descricao' => $this->textValue($textos, 'frota_card_1_descricao', 'Carros compactos com baixo consumo para o dia a dia e viagens curtas.'),
                'imagem' => $this->imageUrl($imagens, 'frota_1', $this->imageEnabled($imagensConfig, 'frota_1')),
            ],
            [
                'titulo' => $this->textValue($textos, 'frota_card_2_titulo', 'SUV e Familia'),
                'descricao' => $this->textValue($textos, 'frota_card_2_descricao', 'Mais espaco, conforto e seguranca para viagens com bagagens e familia.'),
                'imagem' => $this->imageUrl($imagens, 'frota_2', $this->imageEnabled($imagensConfig, 'frota_2')),
            ],
            [
                'titulo' => $this->textValue($textos, 'frota_card_3_titulo', 'Executivo e Premium'),
                'descricao' => $this->textValue($textos, 'frota_card_3_descricao', 'Modelos com acabamento superior para compromissos corporativos e eventos.'),
                'imagem' => $this->imageUrl($imagens, 'frota_3', $this->imageEnabled($imagensConfig, 'frota_3')),
            ],
        ];
    }

    private function imageEnabled(array $imagensConfig, string $key): bool
    {
        if (!array_key_exists($key, $imagensConfig)) {
            return true;
        }

        return (bool) $imagensConfig[$key];
    }

    private function colorValue(array $cores, string $key, string $default): string
    {
        $value = $cores[$key] ?? null;
        if (!is_string($value)) {
            return $default;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return $default;
        }

        if ($trimmed === 'transparent') {
            return $trimmed;
        }

        return preg_match('/^#[A-Fa-f0-9]{6}$/', $trimmed) ? strtoupper($trimmed) : $default;
    }

    private function beneficios(array $textos): array
    {
        return [
            $this->textValue($textos, 'beneficio_1', 'Retirada agil com pre-cadastro online'),
            $this->textValue($textos, 'beneficio_2', 'Protecoes e adicionais sob medida'),
            $this->textValue($textos, 'beneficio_3', 'Atendimento para pessoa fisica e empresas'),
            $this->textValue($textos, 'beneficio_4', 'Frota revisada e higienizada regularmente'),
        ];
    }

    private function etapasReserva(array $textos): array
    {
        return [
            [
                'titulo' => $this->textValue($textos, 'etapa_1_titulo', 'Escolha local e datas'),
                'descricao' => $this->textValue($textos, 'etapa_1_descricao', 'Informe onde deseja retirar e devolver o veiculo no formulario de reserva.'),
            ],
            [
                'titulo' => $this->textValue($textos, 'etapa_2_titulo', 'Selecione o grupo ideal'),
                'descricao' => $this->textValue($textos, 'etapa_2_descricao', 'Compare categorias e escolha o carro mais adequado para sua necessidade.'),
            ],
            [
                'titulo' => $this->textValue($textos, 'etapa_3_titulo', 'Finalize e retire'),
                'descricao' => $this->textValue($textos, 'etapa_3_descricao', 'Conclua a reserva e retire seu carro com rapidez na unidade selecionada.'),
            ],
        ];
    }

    private function estacoesForEmpresa(Empresa $empresa): array
    {
        if (!Schema::hasTable('estacoes')) {
            return [];
        }

        $query = Estacao::query()->withoutGlobalScopes();
        $table = (new Estacao())->getTable();

        if (Schema::hasColumn($table, 'empresa_id')) {
            $query->where($table . '.empresa_id', (int) $empresa->id);
        }

        if (Schema::hasColumn($table, 'ativo')) {
            $query->where($table . '.ativo', 'S');
        }

        return $query
            ->select('id', 'nome', 'cidade')
            ->orderBy('nome')
            ->get()
            ->map(function (Estacao $estacao): array {
                return [
                    'id' => (int) $estacao->id,
                    'nome' => (string) $estacao->nome,
                    'cidade' => (string) ($estacao->cidade ?? ''),
                ];
            })
            ->all();
    }

    private function resolveModelo(string $modelo): string
    {
        $normalized = strtolower(trim($modelo));

        return $normalized === self::MODELO_1
            ? self::MODELO_1
            : self::MODELO_1;
    }

    private function resolveTemplate(string $modelo): string
    {
        return match ($modelo) {
            self::MODELO_1 => 'site-apresentacao.templates.locadora-modelo-1',
            default => 'site-apresentacao.templates.locadora-modelo-1',
        };
    }

    private function contrastTextColor(string $hexColor): string
    {
        $hex = ltrim(trim($hexColor), '#');
        if (!preg_match('/^[A-Fa-f0-9]{6}$/', $hex)) {
            return '#FFFFFF';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $brightness >= 155 ? '#0F172A' : '#FFFFFF';
    }

    private function formatTelefone(?string $rawPhone): ?string
    {
        if ($rawPhone === null || trim($rawPhone) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits) ?: $rawPhone;
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits) ?: $rawPhone;
        }

        return $rawPhone;
    }

    private function formatCnpj(?string $rawCnpj): ?string
    {
        if ($rawCnpj === null || trim($rawCnpj) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $rawCnpj) ?? '';
        if (strlen($digits) !== 14) {
            return $rawCnpj;
        }

        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits) ?: $rawCnpj;
    }

    private function defaultResponse(): RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        if (Route::has('login')) {
            return redirect()->route('login');
        }

        return redirect($this->adminHomeFallbackUrl());
    }

    private function adminLoginFallbackUrl(): string
    {
        $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
        $path = $prefix === '' ? '/login' : '/' . $prefix . '/login';

        return url($path);
    }

    private function adminHomeFallbackUrl(): string
    {
        $prefix = trim((string) config('app.admin_prefix', 'admin'), '/');
        $path = $prefix === '' ? '/home' : '/' . $prefix . '/home';

        return url($path);
    }
}
