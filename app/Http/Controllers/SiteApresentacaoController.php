<?php

namespace App\Http\Controllers;

use App\Enums\StatusReservaEnum;
use App\Models\Empresa;
use App\Models\Estacao;
use App\Models\MotoristaAppSolicitacao;
use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use App\Models\Opcional;
use App\Models\Reserva;
use App\Models\PlanoMotoristaApp;
use App\Services\ReservaCodigoService;
use App\Services\EstacaoService;
use App\Support\ArquivoAnexoHelper;
use App\Support\EmpresaContext;
use App\Support\DomainNormalizer;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;


class SiteApresentacaoController extends Controller
{
    private const MODELO_1 = 'modelo_1';
    private const MODELO_2 = 'modelo_2';
    private const MODELO_3 = 'modelo_3';
    protected $estacaoService;

    public function __construct(EstacaoService $estacaoService)
    {
        $this->estacaoService = $estacaoService;
    }

    public function sobre(): View
    {
        return view('sobre', [
            'anoAtual' => (int) now()->format('Y'),
        ]);
    }

    public function index(Request $request): View|RedirectResponse|\Illuminate\Http\Response
    {
        $host = DomainNormalizer::normalize($request->getHost());
        $empresa = $this->resolveEmpresaByHost($host);

        if (!$empresa || ($empresa->site_apresentacao_ativo ?? 'S') !== 'S' || ($empresa->aluguel_app_ativo ?? 'N') !== 'S') {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $modelo = $this->resolveModelo((string) ($empresa->site_apresentacao_modelo ?? self::MODELO_1));
        $textos = is_array($empresa->site_apresentacao_textos) ? $empresa->site_apresentacao_textos : [];
        $imagens = is_array($empresa->site_apresentacao_imagens) ? $empresa->site_apresentacao_imagens : [];
        $cores = is_array($empresa->site_apresentacao_cores) ? $empresa->site_apresentacao_cores : [];
        $corPrimaria = $this->colorValue($cores, 'brand_primaria', $empresa->cor_primaria ?: '#2F6F3F');
        $corSecundaria = $this->colorValue($cores, 'brand_secundaria', $empresa->cor_secundaria ?: '#193C68');
        $corTerciaria = $this->colorValue($cores, 'brand_terciaria', $empresa->cor_terciaria ?: '#E7B93A');
        $imagensConfig = is_array($empresa->site_apresentacao_imagens_config) ? $empresa->site_apresentacao_imagens_config : [];
        $estacoesRetirada = $this->estacaoService->getEstacoesParaRetiradaSelect();
        $estacoesDevolucao = $this->estacaoService->getEstacoesParaDevolucaoSelect();
        $frotaDestaques = $this->frotaDestaques($textos, $imagens, $imagensConfig);
        $urlReserva = Route::has('site.reserva.index')
            ? route('site.reserva.index')
            : $this->resolveButtonUrl($empresa->site_apresentacao_botao_url);
        $urlAdminEntrada = Route::has('site.admin.entry')
            ? route('site.admin.entry')
            : (Route::has('login') ? route('login') : $this->adminLoginFallbackUrl());
        $heroTrustChips = [];

        if (!empty($empresa->telefone)) {
            $heroTrustChips[] = $this->formatTelefone($empresa->telefone);
        }

        if (!empty($empresa->email)) {
            $heroTrustChips[] = $empresa->email;
        }

        if (!empty($empresa->cnpj)) {
            $heroTrustChips[] = $this->formatCnpj($empresa->cnpj);
        }

        $heroTrustChips[] = 'Reserva sem burocracia';
        $heroTrustChips[] = 'Pagamento seguro';

        if (($empresa->aluguel_app_ativo ?? 'N') === 'S') {
            $heroTrustChips[] = 'Aluguel para motorista de app';
        }

        return view($this->resolveTemplate($modelo), [
            'empresa' => $empresa,
            'modelo' => $modelo,
            'aluguelAppAtivo' => ($empresa->aluguel_app_ativo ?? 'N') === 'S',
            'heroTag' => $this->textValue($textos, 'hero_tag', ''),
            'titulo' => $this->textValue($textos, 'hero_titulo', $empresa->site_apresentacao_titulo ?: 'Aluguel de veÃ­culos com praticidade e seguranÃ§a'),
            'subtitulo' => $this->textValue($textos, 'hero_subtitulo', $empresa->site_apresentacao_subtitulo ?: 'Reserve online, retire rÃ¡pido e viaje com cobertura nacional.'),
            'descricao' => $this->textValue($textos, 'hero_descricao', $empresa->site_apresentacao_descricao ?: 'Planos diÃ¡rios, semanais e mensais para pessoas fÃ­sicas e empresas.'),
            'botaoTexto' => $empresa->site_apresentacao_botao_texto ?: 'Reservar agora',
            'botaoUrl' => $urlReserva,
            'botaoLoginUrl' => $urlAdminEntrada,
            'heroTrustChips' => $heroTrustChips,
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
            'corFrotaEtiquetaTexto' => $this->colorValue($cores, 'frota_etiqueta_texto', '#334155'),
            'corFrotaTituloTexto' => $this->colorValue($cores, 'frota_titulo_texto', '#0B1220'),
            'corFrotaDescricaoTexto' => $this->colorValue($cores, 'frota_descricao_texto', '#5E6E85'),
            'corFrotaCardTituloTexto' => $this->colorValue($cores, 'frota_card_titulo_texto', '#0B1220'),
            'corFrotaCardDescricaoTexto' => $this->colorValue($cores, 'frota_card_descricao_texto', '#5E6E85'),
            'corFrotaCardFundo' => $this->colorValue($cores, 'frota_card_fundo', '#FFFFFF'),
            'corVantagensFundo' => $this->colorValue($cores, 'vantagens_fundo', '#FFFFFF'),
            'corVantagensEtiquetaTexto' => $this->colorValue($cores, 'vantagens_etiqueta_texto', '#334155'),
            'corVantagensTituloTexto' => $this->colorValue($cores, 'vantagens_titulo_texto', '#0B1220'),
            'corVantagensDescricaoTexto' => $this->colorValue($cores, 'vantagens_descricao_texto', '#5E6E85'),
            'corVantagensItemTexto' => $this->colorValue($cores, 'vantagens_item_texto', '#2D3A4F'),
            'corEtapasFundo' => $this->colorValue($cores, 'etapas_fundo', '#FFFFFF'),
            'corEtapasEtiquetaTexto' => $this->colorValue($cores, 'etapas_etiqueta_texto', '#334155'),
            'corEtapasTituloTexto' => $this->colorValue($cores, 'etapas_titulo_texto', '#0B1220'),
            'corEtapasDescricaoTexto' => $this->colorValue($cores, 'etapas_descricao_texto', '#5E6E85'),
            'corEtapasPassoTexto' => $this->colorValue($cores, 'etapas_passo_texto', '#0B1220'),
            'corEtapasPassoDescricaoTexto' => $this->colorValue($cores, 'etapas_passo_descricao_texto', '#5E6E85'),
            'corEtapasPassoFundo' => $this->colorValue($cores, 'etapas_passo_fundo', '#FFFFFF'),
            'corContatoFundo' => $this->colorValue($cores, 'contato_fundo', '#FFFFFF'),
            'corContatoEtiquetaTexto' => $this->colorValue($cores, 'contato_etiqueta_texto', '#FFFFFF'),
            'corContatoTituloTexto' => $this->colorValue($cores, 'contato_titulo_texto', '#FFFFFF'),
            'corContatoInfoTexto' => $this->colorValue($cores, 'contato_info_texto', '#FFFFFF'),
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
            'menuItemPlanos' => $this->textValue($textos, 'menu_item_planos', 'Planos'),
            'menuItemVantagens' => $this->textValue($textos, 'menu_item_vantagens', 'Vantagens'),
            'menuItemEtapas' => $this->textValue($textos, 'menu_item_etapas', 'Como funciona'),
            'menuItemContato' => $this->textValue($textos, 'menu_item_contato', 'Contato'),
            'frotaTitulo' => $this->textValue($textos, 'frota_titulo', 'Categorias para cada tipo de viagem'),
            'frotaLabelSuperior' => $this->textValue($textos, 'frota_label_superior', 'Frota em destaque'),
            'frotaDescricao' => $this->textValue($textos, 'frota_descricao', 'Selecione o grupo ideal e reserve com condiÃ§Ãµes ajustadas ao seu perfil, seja turismo, trabalho ou locaÃ§Ã£o corporativa.'),
            'frotaDestaques' => $frotaDestaques,
            'planos' => \App\Models\Plano::ativos()->get(),
            'vantagensTitulo' => $this->textValue($textos, 'vantagens_titulo', 'Por que escolher ' . $empresa->nome),
            'vantagensLabelSuperior' => $this->textValue($textos, 'vantagens_label_superior', 'Diferenciais'),
            'vantagensDescricao' => $this->textValue($textos, 'vantagens_descricao', 'Fluxo inspirado nas melhores locadoras do mercado, com foco em conversÃ£o, credibilidade e rapidez no atendimento.'),
            'beneficios' => $this->beneficios($textos),
            'etapasTitulo' => $this->textValue($textos, 'etapas_titulo', 'Como funciona a locaÃ§Ã£o'),
            'etapasLabelSuperior' => $this->textValue($textos, 'etapas_label_superior', 'Jornada do cliente'),
            'etapasReserva' => $this->etapasReserva($textos),
            'contatoTitulo' => $this->textValue($textos, 'contato_titulo', 'InformaÃ§Ãµes da locadora'),
            'contatoLabelSuperior' => $this->textValue($textos, 'contato_label_superior', 'Contato direto'),
            'heroFormTitulo' => $this->textValue($textos, 'hero_form_titulo', 'Simule sua reserva'),
            'heroFormDescricao' => $this->textValue($textos, 'hero_form_descricao', 'Preencha os dados da viagem e siga para a reserva completa em passos.'),
            'heroFormLabelRetirada' => $this->textValue($textos, 'hero_form_label_retirada', 'Local de retirada'),
            'heroFormLabelDevolucao' => $this->textValue($textos, 'hero_form_label_devolucao', 'Local de devoluÃ§Ã£o'),
            'heroFormLabelDataRetirada' => $this->textValue($textos, 'hero_form_label_data_retirada', 'Data de retirada'),
            'heroFormLabelHoraRetirada' => $this->textValue($textos, 'hero_form_label_hora_retirada', 'Hora de retirada'),
            'heroFormLabelDataDevolucao' => $this->textValue($textos, 'hero_form_label_data_devolucao', 'Data de devoluÃ§Ã£o'),
            'heroFormLabelHoraDevolucao' => $this->textValue($textos, 'hero_form_label_hora_devolucao', 'Hora de devoluÃ§Ã£o'),
            'heroFormTextoBotao' => $this->textValue($textos, 'hero_form_texto_botao', 'Iniciar reserva'),
            'heroFormNota' => $this->textValue($textos, 'hero_form_nota', 'Ao avanÃ§ar, vocÃª segue para o fluxo completo de reserva da locadora.'),
            'botaoAdminTexto' => $this->textValue($textos, 'botao_admin_texto', 'Ãrea administrativa'),
            'footerTexto' => $this->textValue($textos, 'footer_texto', 'Todos os direitos reservados.'),
            'imagemHero' => $this->imageUrl($imagens, 'hero', $this->imageEnabled($imagensConfig, 'hero')),
            'imagemVantagens' => $this->imageUrl($imagens, 'vantagens', $this->imageEnabled($imagensConfig, 'vantagens')),
            'imagemContato' => $this->imageUrl($imagens, 'contato', $this->imageEnabled($imagensConfig, 'contato')),
            'estacoesRetirada' => $estacoesRetirada,
            'estacoesDevolucao' => $estacoesDevolucao,
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

    public function motoristaApp(Request $request): View|RedirectResponse
    {
        $host = DomainNormalizer::normalize($request->getHost());
        $empresa = $this->resolveEmpresaByHost($host);

        if (!$empresa || ($empresa->site_apresentacao_ativo ?? 'S') !== 'S') {
            return $this->defaultResponse();
        }

        $this->activateEmpresaContext($empresa);

        $modelo = $this->resolveModelo((string) ($empresa->site_apresentacao_modelo ?? self::MODELO_1));
        $estacoesRetirada = $this->estacaoService->getEstacoesParaRetiradaSelect();
        $estacoesDevolucao = $this->estacaoService->getEstacoesParaDevolucaoSelect();
        $planos = PlanoMotoristaApp::query()
            ->where('empresa_id', $empresa->id)
            ->where('ativo', 'S')
            ->with('opcionais')
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get();

        $docsObrigatorios = is_array($empresa->aluguel_app_documentos_obrigatorios ?? null)
            ? $empresa->aluguel_app_documentos_obrigatorios
            : [];
        $usuarioLogado = $request->user();
        $documentosSelecionados = $this->motoristaAppDocumentosConfigurados(
            $empresa,
            $docsObrigatorios,
            (string) ($empresa->aluguel_app_documento_adicional_label ?? '')
        );
        $solicitacaoExistente = $this->motoristaAppJaEnviouDocumentos($empresa, $usuarioLogado)
            ? MotoristaAppSolicitacao::query()
                ->where('empresa_id', $empresa->id)
                ->where('user_id', $usuarioLogado?->id)
                ->whereIn('status', ['pendente', 'em_analise', 'aprovado'])
                ->latest()
                ->first()
            : null;
        $usuarioTemDocumentos = $solicitacaoExistente !== null;
        $loginSuccess = $request->boolean('login_success');
        $analiseConcluida = $request->boolean('analise');
        $solicitacaoFinalizada = $request->boolean('finalizado')
            || (($solicitacaoExistente?->status === 'aprovado') && (($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') !== 'S'));
        $iniciarEmDocumentos = $loginSuccess || $analiseConcluida || $usuarioTemDocumentos || ($request->boolean('step_4') && !$planos->isEmpty());

        return view('site-apresentacao.motorista-app', [
            'empresa' => $empresa,
            'modelo' => $modelo,
            'planos' => $planos,
            'estacoesRetirada' => $estacoesRetirada,
            'estacoesDevolucao' => $estacoesDevolucao,
            'isAuth' => auth()->check(),
            'aluguelAppAtivo' => ($empresa->aluguel_app_ativo ?? 'N') === 'S',
            'exigirAprovacaoDocumentos' => ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S',
            'docsObrigatorios' => $docsObrigatorios,
            'docsSelecionados' => $documentosSelecionados,
            'documentoAdicionalLabel' => (string) ($empresa->aluguel_app_documento_adicional_label ?? ''),
            'usuarioTemDocumentos' => $usuarioTemDocumentos,
            'solicitacaoStatus' => $solicitacaoExistente?->status,
            'solicitacaoFinalizada' => $solicitacaoFinalizada,
            'loginSuccess' => $loginSuccess,
            'analiseConcluida' => $analiseConcluida,
            'iniciarEmDocumentos' => $iniciarEmDocumentos,
            'selecionados' => [
                'estacao_retirada' => (string) $request->query('estacao_retirada', $request->query('estação_retirada', '')),
                'data_retirada' => (string) $request->query('data_retirada', ''),
                'hora_retirada' => (string) $request->query('hora_retirada', ''),
                'estacao_devolucao' => (string) $request->query('estacao_devolucao', ''),
                'data_devolucao' => (string) $request->query('data_devolucao', ''),
                'hora_devolucao' => (string) $request->query('hora_devolucao', ''),
            ],
            'botaoLoginUrl' => Route::has('site.admin.entry')
                ? route('site.admin.entry')
                : (Route::has('login') ? route('login') : $this->adminLoginFallbackUrl()),
            'loginMotoristaUrl' => Route::has('site.reserva.login')
                ? route('site.reserva.login', [
                    'context' => 'motorista-app',
                    'return_to' => $request->fullUrl(),
                ])
                : (Route::has('login') ? route('login') : $this->adminLoginFallbackUrl()),
        ]);
    }

    public function storeMotoristaApp(Request $request): View|RedirectResponse|\Illuminate\Http\Response
    {
        $host = DomainNormalizer::normalize($request->getHost());
        $empresa = $this->resolveEmpresaByHost($host);

        if (!$empresa || ($empresa->site_apresentacao_ativo ?? 'S') !== 'S' || ($empresa->aluguel_app_ativo ?? 'N') !== 'S') {
            return $this->defaultResponse();
        }

        if (!auth()->check()) {
            return Route::has('site.reserva.login')
                ? redirect()->route('site.reserva.login', [
                    'context' => 'motorista-app',
                    'return_to' => $request->fullUrl(),
                ])
                : redirect()->route('login');
        }

        $usuarioLogado = $request->user();
        $docsObrigatorios = is_array($empresa->aluguel_app_documentos_obrigatorios ?? null)
            ? $empresa->aluguel_app_documentos_obrigatorios
            : [];
        $documentosSelecionados = $this->motoristaAppDocumentosConfigurados(
            $empresa,
            $docsObrigatorios,
            (string) ($empresa->aluguel_app_documento_adicional_label ?? '')
        );
        $solicitacaoExistente = $this->motoristaAppJaEnviouDocumentos($empresa, $usuarioLogado)
            ? MotoristaAppSolicitacao::query()
                ->where('empresa_id', $empresa->id)
                ->where('user_id', $usuarioLogado?->id)
                ->whereIn('status', ['pendente', 'em_analise', 'aprovado'])
                ->latest()
                ->first()
            : null;

        $regras = [
            'plano_id' => ['required', 'integer', Rule::exists('planos_motorista_app', 'id')->where('empresa_id', $empresa->id)],
            'estacao_id' => ['nullable', 'integer'],
            'data_inicio' => ['nullable', 'date'],
            'opcionais_payload' => ['nullable', 'string'],
            'possui_ear' => ['nullable', Rule::in(['S', 'N'])],
            'aplicativos' => ['nullable', 'string', 'max:255'],
            'documentos' => ['nullable', 'array'],
        ];

        foreach ($documentosSelecionados as $documento) {
            $chave = (string) ($documento['key'] ?? '');
            if ($chave === '') {
                continue;
            }

            if (!$this->motoristaAppDocumentoEstaVisivel($documento, $request)) {
                continue;
            }

            $tipo = (string) ($documento['type'] ?? 'arquivo');
            $obrigatorio = (bool) ($documento['obrigatorio'] ?? false);
            $isRequired = $obrigatorio && !$solicitacaoExistente;
            $multiple = (bool) ($documento['multiple'] ?? false);

            if ($tipo === 'arquivo') {
                $regras['documentos.' . $chave] = $isRequired
                    ? ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120']
                    : ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'];
                continue;
            }

            if ($tipo === 'data') {
                $regras['documentos.' . $chave] = $isRequired
                    ? ['required', 'date_format:Y-m-d']
                    : ['nullable', 'date_format:Y-m-d'];
                continue;
            }

            if ($tipo === 'lista' && $multiple) {
                $regras['documentos.' . $chave] = $isRequired
                    ? ['required', 'array', 'min:1']
                    : ['nullable', 'array'];
                $regras['documentos.' . $chave . '.*'] = ['nullable', 'string', Rule::in(array_values(array_filter(array_map(
                    fn ($option) => is_string($option) ? trim($option) : '',
                    (array) ($documento['options'] ?? [])
                ))))];
                continue;
            }

            $rule = $isRequired
                ? ['required', 'string', 'max:4000']
                : ['nullable', 'string', 'max:4000'];

            $opcoes = array_values(array_filter(array_map(
                fn ($option) => is_string($option) ? trim($option) : '',
                (array) ($documento['options'] ?? [])
            )));

            if ($tipo === 'lista' && !empty($opcoes)) {
                $rule[] = Rule::in($opcoes);
            }

            $regras['documentos.' . $chave] = $rule;
        }

        $validated = $request->validate($regras, [
            'plano_id.required' => 'Selecione um plano para continuar.',
            'documentos.*.required' => 'Preencha todos os documentos solicitados pela empresa.',
        ]);

        $planoMotoristaApp = PlanoMotoristaApp::query()
            ->with('opcionais')
            ->where('empresa_id', $empresa->id)
            ->findOrFail((int) $validated['plano_id']);

        $opcionaisPayload = (string) ($validated['opcionais_payload'] ?? '[]');
        $opcionaisSelecionados = json_decode($opcionaisPayload, true);
        $opcionaisSelecionados = is_array($opcionaisSelecionados) ? $opcionaisSelecionados : [];
        $opcionaisSelecionados = array_values(array_filter(array_map(function ($item): array {
            return [
                'id' => (int) ($item['id'] ?? 0),
                'quantidade' => max(0, (int) ($item['quantidade'] ?? 0)),
            ];
        }, $opcionaisSelecionados), fn (array $item): bool => $item['id'] > 0 && $item['quantidade'] > 0));

        $diasPlano = $this->motoristaAppPeriodoEmDias((string) $planoMotoristaApp->periodo);
        $valorPlano = (float) $planoMotoristaApp->valor_periodo;
        $valorOpcionais = $this->motoristaAppCalcularValorOpcionais($planoMotoristaApp, $opcionaisSelecionados, $diasPlano);
        $valorTotal = round($valorPlano + $valorOpcionais, 2);

        $dataInicioTexto = trim((string) ($validated['data_inicio'] ?? ''));
        $horaRetirada = trim((string) $request->query('hora_retirada', $request->input('hora_retirada', '')));
        $dataInicio = null;
        if ($dataInicioTexto !== '') {
            $dataInicio = $horaRetirada !== ''
                ? Carbon::parse($dataInicioTexto . ' ' . $horaRetirada)
                : Carbon::parse($dataInicioTexto);
        }

        $dataFim = $dataInicio ? $dataInicio->copy()->addDays($diasPlano) : null;

        $dadosJson = [
            'plano_id' => (int) $validated['plano_id'],
            'estacao_id' => isset($validated['estacao_id']) ? (int) $validated['estacao_id'] : null,
            'data_inicio' => $validated['data_inicio'] ?? null,
            'opcionais_payload' => $validated['opcionais_payload'] ?? '[]',
            'possui_ear' => $validated['possui_ear'] ?? null,
            'aplicativos' => $validated['aplicativos'] ?? null,
        ];

        $documentosJson = is_array($solicitacaoExistente?->documentos_json) ? $solicitacaoExistente->documentos_json : [];

        foreach ($documentosSelecionados as $documento) {
            $chave = (string) ($documento['key'] ?? '');
            if ($chave === '') {
                continue;
            }

            if (!$this->motoristaAppDocumentoEstaVisivel($documento, $request)) {
                continue;
            }

            $tipo = (string) ($documento['type'] ?? 'arquivo');
            $multiple = (bool) ($documento['multiple'] ?? false);

            if ($tipo === 'arquivo') {
                $arquivo = $request->file('documentos.' . $chave);
                if ($arquivo instanceof \Illuminate\Http\UploadedFile && $arquivo->isValid()) {
                    $antigo = is_array($documentosJson[$chave] ?? null) ? ($documentosJson[$chave]['path'] ?? null) : null;
                    $documentosJson[$chave] = [
                        'type' => 'arquivo',
                        'label' => (string) ($documento['label'] ?? $chave),
                        'path' => ArquivoAnexoHelper::armazenar($arquivo, 'motorista-app/documentos', is_string($antigo) ? $antigo : null),
                    ];
                } elseif (!isset($documentosJson[$chave]) && !$solicitacaoExistente) {
                    $documentosJson[$chave] = [
                        'type' => 'arquivo',
                        'label' => (string) ($documento['label'] ?? $chave),
                        'path' => null,
                    ];
                }

                continue;
            }

            if ($tipo === 'data') {
                $entrada = $request->input('documentos.' . $chave);
                if (is_string($entrada) && trim($entrada) !== '') {
                    $documentosJson[$chave] = [
                        'type' => $tipo,
                        'label' => (string) ($documento['label'] ?? $chave),
                        'value' => trim($entrada),
                    ];
                } elseif (!isset($documentosJson[$chave]) && !$solicitacaoExistente) {
                    $documentosJson[$chave] = [
                        'type' => $tipo,
                        'label' => (string) ($documento['label'] ?? $chave),
                        'value' => null,
                    ];
                }

                continue;
            }

            if ($tipo === 'lista' && $multiple) {
                $entrada = $request->input('documentos.' . $chave);
                $valores = array_values(array_filter(array_map(
                    fn ($item) => is_string($item) ? trim($item) : '',
                    is_array($entrada) ? $entrada : []
                )));

                if (!empty($valores)) {
                    $documentosJson[$chave] = [
                        'type' => $tipo,
                        'multiple' => true,
                        'label' => (string) ($documento['label'] ?? $chave),
                        'value' => $valores,
                    ];
                } elseif (!isset($documentosJson[$chave]) && !$solicitacaoExistente) {
                    $documentosJson[$chave] = [
                        'type' => $tipo,
                        'multiple' => true,
                        'label' => (string) ($documento['label'] ?? $chave),
                        'value' => [],
                    ];
                }

                continue;
            }

            $entrada = $request->input('documentos.' . $chave);
            if (is_string($entrada) && trim($entrada) !== '') {
                $documentosJson[$chave] = [
                    'type' => $tipo,
                    'label' => (string) ($documento['label'] ?? $chave),
                    'value' => trim($entrada),
                ];
            } elseif (!isset($documentosJson[$chave]) && !$solicitacaoExistente) {
                $documentosJson[$chave] = [
                    'type' => $tipo,
                    'label' => (string) ($documento['label'] ?? $chave),
                    'value' => null,
                ];
            }
        }

        $solicitacao = $solicitacaoExistente ?: new MotoristaAppSolicitacao([
            'empresa_id' => $empresa->id,
            'user_id' => $usuarioLogado?->id,
        ]);

        $statusSolicitacao = ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S'
            ? ($solicitacao->exists ? $solicitacao->status : 'pendente')
            : 'aprovado';

        $solicitacao->fill([
            'plano_motorista_app_id' => (int) $validated['plano_id'],
            'status' => $statusSolicitacao,
            'dados_json' => $dadosJson,
            'documentos_json' => $documentosJson,
        ]);
        $solicitacao->save();

        $reservaMotoristaApp = ReservaMotoristaApp::create([
            'empresa_id' => (int) $empresa->id,
            'user_id' => (int) $usuarioLogado->id,
            'motorista_app_solicitacao_id' => (int) $solicitacao->id,
            'plano_motorista_app_id' => (int) $planoMotoristaApp->id,
            'status' => ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S'
                ? 'pendente_analise'
                : 'pendente_pagamento',
            'status_pagamento' => 'pendente',
            'pagamento_efetuado' => false,
            'estacao_retirada_id' => isset($validated['estacao_id']) ? (int) $validated['estacao_id'] : null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'quantidade_dias' => $diasPlano,
            'valor_plano' => $valorPlano,
            'valor_periodo_contrato' => $valorPlano,
            'periodo_contrato' => (string) ($planoMotoristaApp->periodo ?? 'semana'),
            'valor_opcionais' => $valorOpcionais,
            'valor_caucao' => (float) ($planoMotoristaApp->calcao ?? 0),
            'valor_total_final' => $valorTotal,
            'opcionais_json' => $opcionaisSelecionados,
            'dados_json' => $dadosJson,
            'documentos_json' => $documentosJson,
        ]);

        if ($reservaMotoristaApp) {
            ReservaMotoristaAppPagamento::firstOrCreate(
                [
                    'reserva_motorista_app_id' => (int) $reservaMotoristaApp->id,
                    'periodos' => 1,
                    'data_inicio_prevista' => $dataInicio,
                ],
                [
                    'empresa_id' => (int) $empresa->id,
                    'user_id' => (int) $usuarioLogado->id,
                    'periodo_unidade' => $this->rotuloPeriodoMotoristaApp((string) ($planoMotoristaApp->periodo ?? 'semana')),
                    'valor_unitario' => $valorPlano,
                    'valor_total' => $valorPlano,
                    'data_fim_prevista' => $dataFim,
                    'status' => 'pendente',
                    'observacoes' => 'Primeiro ciclo criado automaticamente na reserva.',
                    'metadados' => [
                        'tipo' => 'primeiro_ciclo',
                        'plano_id' => (int) $planoMotoristaApp->id,
                        'valor_contrato' => $valorPlano,
                    ],
                ]
            );

            app(ReservaCodigoService::class)->sincronizar($reservaMotoristaApp, 'motorista_app', [
                'empresa_id' => $empresa->id,
                'status_label' => $reservaMotoristaApp->status,
                'data_inicio' => $reservaMotoristaApp->data_inicio,
                'data_fim' => $reservaMotoristaApp->data_fim,
                'valor_total' => $reservaMotoristaApp->valor_total_final,
            ]);
        }

        return redirect()
            ->route('reserva.minhas')
            ->with('success', ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S'
                ? 'Sua contratação de motorista de app foi criada e aguarda análise.'
                : 'Sua reserva de motorista de app foi criada e está pendente de pagamento.');

        return redirect()
            ->route('site.apresentacao.motorista-app', array_filter([
                'estacao_retirada' => $request->query('estacao_retirada', $request->query('estação_retirada')),
                'data_retirada' => $request->query('data_retirada'),
                'hora_retirada' => $request->query('hora_retirada'),
                'analise' => ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S' ? 1 : null,
                'finalizado' => ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S' ? null : 1,
            ], fn ($value) => $value !== null && $value !== ''))
            ->with('success', ($empresa->aluguel_app_exigir_aprovacao_documentos ?? 'N') === 'S'
                ? 'Sua solicitaÃ§Ã£o foi salva com sucesso e seguirÃ¡ para anÃ¡lise.'
                : 'Sua solicitaÃ§Ã£o foi concluÃ­da com sucesso.');
    }

    public function motoristaAppReservaShow(Request $request, int $reserva): View|RedirectResponse
    {
        $host = DomainNormalizer::normalize($request->getHost());
        $empresa = $this->resolveEmpresaByHost($host);

        if (!$empresa || ($empresa->site_apresentacao_ativo ?? 'S') !== 'S') {
            return $this->defaultResponse();
        }

        if (!auth()->check()) {
            return Route::has('site.reserva.login')
                ? redirect()->route('site.reserva.login', [
                    'context' => 'motorista-app',
                    'return_to' => $request->fullUrl(),
                ])
                : redirect()->route('login');
        }

        $registro = ReservaMotoristaApp::query()
            ->with(['planoMotoristaApp', 'estacaoRetirada', 'solicitacao', 'codigoReserva'])
            ->where('empresa_id', $empresa->id)
            ->where('user_id', auth()->id())
            ->find($reserva);

        if (!$registro) {
            return redirect()
                ->route('reserva.minhas')
                ->with('error', 'Reserva de motorista de app não encontrada.');
        }

        return view('site-apresentacao.motorista-app-reserva', [
            'empresa' => $empresa,
            'registro' => $registro,
        ]);
    }

    private function rotuloPeriodoMotoristaApp(?string $periodo): string
    {
        return match ($periodo) {
            'quinze' => 'Quinze dias',
            'mes' => 'Mes',
            'trimestre' => 'Trimestre',
            'semestre' => 'Semestre',
            'ano' => 'Ano',
            default => 'Semana',
        };
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

    private function motoristaAppDocumentosConfigurados(Empresa $empresa, array $docsObrigatorios, string $documentoAdicionalLabel = ''): array
    {
        $documentos = [];

        foreach ($docsObrigatorios as $doc) {
            $item = $this->motoristaAppDocumentoConfigurado((string) $doc, $documentoAdicionalLabel);
            if ($item !== null) {
                $documentos[] = $item;
            }
        }

        foreach ($this->motoristaAppCamposPersonalizadosConfigurados($empresa) as $item) {
            $documentos[] = $item;
        }

        return array_values($documentos);
    }

    private function motoristaAppCamposPersonalizadosConfigurados(Empresa $empresa): array
    {
        $campos = is_array($empresa->aluguel_app_campos_personalizados ?? null)
            ? $empresa->aluguel_app_campos_personalizados
            : [];
        $documentos = [];

        foreach ($campos as $campo) {
            if (!is_array($campo) || empty($campo['selected'])) {
                continue;
            }

            $key = trim((string) ($campo['key'] ?? ''));
            $label = trim((string) ($campo['label'] ?? ''));
            $type = trim((string) ($campo['type'] ?? 'arquivo'));
            $obrigatorio = (bool) ($campo['obrigatorio'] ?? false);
            $options = array_values(array_filter(array_map(
                fn ($option) => is_string($option) ? trim($option) : '',
                (array) ($campo['options'] ?? [])
            )));
            $multiple = (bool) ($campo['multiple'] ?? false);
            $condicao = $this->motoristaAppNormalizarCondicao($campo['condicao'] ?? null);
            $ordem = (int) ($campo['ordem'] ?? 0);

            if ($key === '' || $label === '') {
                continue;
            }

            if (!in_array($type, ['arquivo', 'texto', 'texto_longo', 'lista', 'data'], true)) {
                $type = 'arquivo';
            }

            $documentos[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'options' => $options,
                'multiple' => $type === 'lista' ? $multiple : false,
                'obrigatorio' => $obrigatorio,
                'condicao' => $condicao,
                'ordem' => $ordem > 0 ? $ordem : 999,
                'selected' => true,
                'origin' => 'custom',
            ];
        }

        usort($documentos, fn (array $a, array $b) => ($a['ordem'] ?? 999) <=> ($b['ordem'] ?? 999));

        return $documentos;
    }

    private function motoristaAppDocumentoConfigurado(string $doc, string $documentoAdicionalLabel = ''): ?array
    {
        return match ($doc) {
            default => null,
        };
    }

    private function motoristaAppDocumentoEstaVisivel(array $documento, Request $request): bool
    {
        $condicao = $this->motoristaAppNormalizarCondicao($documento['condicao'] ?? null);

        if ($condicao === null) {
            return true;
        }

        $operador = $condicao['operador'];
        $valorReferencia = $this->motoristaAppValorDocumentoNoRequest($request, (string) $condicao['campo_key']);
        $valorEsperado = $condicao['valor'] ?? '';

        return match ($operador) {
            'igual' => $this->motoristaAppCondicaoIgual($valorReferencia, $valorEsperado),
            'diferente' => $this->motoristaAppCondicaoDiferente($valorReferencia, $valorEsperado),
            'preenchido' => $this->motoristaAppCondicaoPreenchida($valorReferencia),
            'vazio' => $this->motoristaAppCondicaoVazia($valorReferencia),
            default => true,
        };
    }

    private function motoristaAppPeriodoEmDias(string $periodo): int
    {
        return match ($periodo) {
            'semana' => 7,
            'quinze' => 15,
            'mes' => 30,
            'trimestre' => 90,
            'semestre' => 180,
            'ano' => 365,
            default => 1,
        };
    }

    private function motoristaAppCalcularValorOpcionais(PlanoMotoristaApp $plano, array $selecionados, int $diasPlano): float
    {
        if ($plano->relationLoaded('opcionais')) {
            $opcionais = $plano->opcionais;
        } else {
            $opcionais = $plano->opcionais()->get();
        }

        $total = 0.0;
        foreach ($selecionados as $item) {
            $opcionalId = (int) ($item['id'] ?? 0);
            $quantidade = max(0, (int) ($item['quantidade'] ?? 0));
            if ($opcionalId <= 0 || $quantidade <= 0) {
                continue;
            }

            $opcional = $opcionais->firstWhere('id', $opcionalId);
            if (!$opcional) {
                continue;
            }

            $tipoCobranca = (string) ($opcional->tipo_cobranca ?? '');
            $valorUnitario = $tipoCobranca === 'por_dia'
                ? (float) ($opcional->preco_por_dia ?? 0)
                : (float) ($opcional->preco_integral ?? 0);

            $total += $tipoCobranca === 'por_dia'
                ? ($valorUnitario * $quantidade * max(1, $diasPlano))
                : ($valorUnitario * $quantidade);
        }

        return round($total, 2);
    }

    private function motoristaAppValorDocumentoNoRequest(Request $request, string $campoKey): mixed
    {
        $campoKey = trim($campoKey);

        if ($campoKey === '') {
            return '';
        }

        if ($request->hasFile('documentos.' . $campoKey)) {
            $file = $request->file('documentos.' . $campoKey);
            return $file instanceof \Illuminate\Http\UploadedFile && $file->isValid() ? '__file__' : '';
        }

        $valor = $request->input('documentos.' . $campoKey);
        if (is_array($valor)) {
            return array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : '',
                $valor
            )));
        }

        return trim((string) $valor);
    }

    private function motoristaAppCondicaoIgual(mixed $valorReferencia, mixed $valorEsperado): bool
    {
        $referencia = is_array($valorReferencia)
            ? array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : '',
                $valorReferencia
            )))
            : [trim((string) $valorReferencia)];
        $esperado = is_array($valorEsperado)
            ? array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : '',
                $valorEsperado
            )))
            : [trim((string) $valorEsperado)];

        if (empty($referencia) || empty($esperado)) {
            return false;
        }

        return count(array_intersect($referencia, $esperado)) > 0;
    }

    private function motoristaAppCondicaoDiferente(mixed $valorReferencia, mixed $valorEsperado): bool
    {
        $referencia = is_array($valorReferencia)
            ? array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : '',
                $valorReferencia
            )))
            : [trim((string) $valorReferencia)];
        $esperado = is_array($valorEsperado)
            ? array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : '',
                $valorEsperado
            )))
            : [trim((string) $valorEsperado)];

        if (empty($referencia)) {
            return false;
        }

        return count(array_intersect($referencia, $esperado)) === 0;
    }

    private function motoristaAppCondicaoPreenchida(mixed $valorReferencia): bool
    {
        return is_array($valorReferencia) ? !empty($valorReferencia) : trim((string) $valorReferencia) !== '';
    }

    private function motoristaAppCondicaoVazia(mixed $valorReferencia): bool
    {
        return is_array($valorReferencia) ? empty($valorReferencia) : trim((string) $valorReferencia) === '';
    }

    private function motoristaAppNormalizarCondicao(mixed $condicao): ?array
    {
        if (!is_array($condicao)) {
            return null;
        }

        $campoKey = trim((string) ($condicao['campo_key'] ?? $condicao['campo'] ?? ''));
        $operador = trim((string) ($condicao['operador'] ?? ''));
        $rawValor = $condicao['valor'] ?? [];
        $valor = is_array($rawValor)
            ? array_values(array_filter(array_map(
                fn ($item) => is_string($item) ? trim($item) : '',
                $rawValor
            )))
            : trim((string) $rawValor);

        if ($campoKey === '' || !in_array($operador, ['igual', 'diferente', 'preenchido', 'vazio'], true)) {
            return null;
        }

        if (in_array($operador, ['igual', 'diferente'], true) && (is_array($valor) ? empty($valor) : $valor === '')) {
            return null;
        }

        return [
            'campo_key' => $campoKey,
            'operador' => $operador,
            'valor' => in_array($operador, ['igual', 'diferente'], true) ? $valor : '',
        ];
    }

    private function motoristaAppJaEnviouDocumentos(Empresa $empresa, ?\App\Models\User $user): bool
    {
        if (!$user) {
            return false;
        }

        return MotoristaAppSolicitacao::query()
            ->where('empresa_id', $empresa->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pendente', 'em_analise', 'aprovado'])
            ->exists();
    }

    private function motoristaAppUsuarioTemDocumentos(\App\Models\User $user): bool
    {
        $camposObrigatorios = [
            'numero_carta_conducao',
            'local_emissao_carta_conducao',
            'pais_emissao_carta_conducao',
            'data_emissao_carta_conducao',
        ];

        foreach ($camposObrigatorios as $campo) {
            $valor = trim((string) ($user->{$campo} ?? ''));
            if ($valor === '') {
                return false;
            }
        }

        return true;
    }

    private function motoristaAppDocumentosSelecionados(array $docsObrigatorios, string $documentoAdicionalLabel = ''): array
    {
        $labels = [];

        foreach ($docsObrigatorios as $doc) {
            $label = $this->motoristaAppDocumentoLabel((string) $doc, $documentoAdicionalLabel);
            if ($label !== null) {
                $labels[] = $label;
            }
        }

        return array_values(array_unique($labels));
    }

    private function motoristaAppDocumentoLabel(string $doc, string $documentoAdicionalLabel = ''): ?string
    {
        return match ($doc) {
            'cnh_definitiva' => 'CNH definitiva',
            'cnh_ear' => 'CNH com EAR',
            'comprovante_residencia' => 'Comprovante de residÃªncia',
            'comprovante_cadastro_app' => 'Comprovante de cadastro ativo no aplicativo',
            'documento_adicional' => trim($documentoAdicionalLabel) !== '' ? $documentoAdicionalLabel : 'Documento adicional',
            'nenhum' => null,
            default => null,
        };
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
        $defaults = [
            1 => ['titulo' => 'EconÃ´micos Urbanos', 'descricao' => 'Carros compactos com baixo consumo para o dia a dia e viagens curtas.'],
            2 => ['titulo' => 'SUV e FamÃ­lia', 'descricao' => 'Mais espaÃ§o, conforto e seguranÃ§a para viagens com bagagens e famÃ­lia.'],
            3 => ['titulo' => 'Executivo e Premium', 'descricao' => 'Modelos com acabamento superior para compromissos corporativos e eventos.'],
        ];
        $cards = [];
        for ($i = 1; $i <= 10; $i++) {
            $titulo = $this->textValue($textos, "frota_card_{$i}_titulo", $defaults[$i]['titulo'] ?? '');
            if (empty($titulo)) continue;
            $cards[] = [
                'titulo' => $titulo,
                'descricao' => $this->textValue($textos, "frota_card_{$i}_descricao", $defaults[$i]['descricao'] ?? ''),
                'imagem' => $this->imageUrl($imagens, "frota_{$i}", $this->imageEnabled($imagensConfig, "frota_{$i}")),
            ];
        }
        return $cards;
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
        $defaults = [
            'Retirada Ã¡gil com prÃ©-cadastro online',
            'ProteÃ§Ãµes e adicionais sob medida',
            'Atendimento para pessoa fÃ­sica e empresas',
            'Frota revisada e higienizada regularmente',
        ];
        $items = [];
        for ($i = 1; $i <= 10; $i++) {
            $val = $this->textValue($textos, "beneficio_{$i}", $defaults[$i - 1] ?? '');
            if ($val !== '') $items[] = $val;
        }
        return $items;
    }

    private function etapasReserva(array $textos): array
    {
        return [
            [
                'titulo' => $this->textValue($textos, 'etapa_1_titulo', 'Escolha local e datas'),
                'descricao' => $this->textValue($textos, 'etapa_1_descricao', 'Informe onde deseja retirar e devolver o veÃ­culo no formulÃ¡rio de reserva.'),
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
        if (!SchemaCache::hasTable('estacoes')) {
            return [];
        }

        $query = Estacao::query()->withoutGlobalScopes();
        $table = (new Estacao())->getTable();

        if (SchemaCache::hasColumn($table, 'empresa_id')) {
            $query->where($table . '.empresa_id', (int) $empresa->id);
        }

        if (SchemaCache::hasColumn($table, 'ativo')) {
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

    private function activateEmpresaContext(Empresa $empresa): void
    {
        app(EmpresaContext::class)->setEmpresaId((int) $empresa->id);
    }

    private function resolveModelo(string $modelo): string
    {
        $normalized = strtolower(trim($modelo));

        return match ($normalized) {
            self::MODELO_2 => self::MODELO_2,
            self::MODELO_3 => self::MODELO_3,
            default => self::MODELO_1,
        };
    }

    private function resolveTemplate(string $modelo): string
    {
        return match ($modelo) {
            self::MODELO_2 => 'site-apresentacao.templates.locadora-modelo-2',
            self::MODELO_3 => 'site-apresentacao.templates.locadora-modelo-3',
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

    private function defaultResponse(): \Illuminate\Http\Response|RedirectResponse
    {
        return response()->view('welcome');
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
