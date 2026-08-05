<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Http\Requests\OrdemServico\AtualizarStatusOrdemServicoRequest;
use App\Http\Requests\OrdemServico\ConcluirOrdemServicoRequest;
use App\Http\Requests\OrdemServico\StoreOrdemServicoRequest;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\OrdemServico;
use App\Models\StatusVeiculo;
use App\Models\TipoFrota;
use App\Models\VeiculoDanos;
use App\Models\Empresa;
use App\Models\Sinistro;
use App\Services\BreadcrumbService;
use App\Services\OrdemServicoService;
use App\Services\PdfService;
use App\Services\VeiculoService;
use App\Support\ArquivoAnexoHelper;
use App\Support\EmpresaBranding;
use App\Support\EmpresaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdemServicoController extends Controller
{
    public function __construct(
        protected OrdemServicoService $ordemServicoService,
        protected BreadcrumbService $breadcrumbService,
        protected VeiculoService $veiculoService,
        protected PdfService $pdfService
    ) {
    }

    private function dadosFiltroVeiculo(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $scope = fn ($q) => $empresaId ? $q->where('empresa_id', $empresaId) : $q;

        return [
            'filtroMarcas' => $scope(Marca::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroGrupos' => $scope(GrupoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroTipos'  => $scope(TipoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroStatus' => StatusVeiculo::query()->orderBy('nome')->pluck('nome', 'id'),
        ];
    }

    public function index(Request $request)
    {
        $status = $request->query('status');
        $veiculoId = $request->query('veiculo_id');
        $sinistroId = $request->query('sinistro_id');
        $reservaId = $request->query('reserva_id');
        $avariaId = $request->query('avaria_id');

        $query = OrdemServico::query()
            ->with(['veiculo', 'usuarioAbertura', 'itens', 'categoria', 'subcategoria'])
            ->orderByDesc('id');

        if ($status && in_array($status, [
            OrdemServico::STATUS_ABERTA,
            OrdemServico::STATUS_EM_ANDAMENTO,
            OrdemServico::STATUS_CONCLUIDA,
            OrdemServico::STATUS_CANCELADA,
        ], true)) {
            $query->where('status', $status);
        }

        if ($veiculoId) {
            $query->where('veiculo_id', $veiculoId);
        }

        if ($sinistroId) {
            $query->where('sinistro_id', $sinistroId);
        }

        if ($reservaId) {
            $query->where('reserva_id', $reservaId);
        }

        if ($avariaId) {
            $query->whereHas('itens', function ($q) use ($avariaId) {
                $q->where('veiculo_dano_id', $avariaId);
            });
        }

        $ordens = $query->paginate(20)->withQueryString();
        $veiculos = \App\Models\Veiculo::query()->orderBy('nome')->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Ordens de serviço', route('ordem-servico.index')),
        ]);

        return view('ordem-servico.index', compact('ordens', 'breadcrumb', 'status', 'veiculos', 'veiculoId', 'sinistroId', 'reservaId', 'avariaId'))
            ->with('titulo', 'Ordens de serviço (OS)');
    }

    public function create(Request $request)
    {
        $veiculoId = (int) $request->query('veiculo_id', 0);
        $reservaId = $request->query('reserva_id');
        $sinistroId = $request->query('sinistro_id');
        $danoIds = $request->query('dano_ids', []);
        if (! is_array($danoIds)) {
            $danoIds = $danoIds ? [(int) $danoIds] : [];
        }
        $danoIds = array_map('intval', $danoIds);

        $veiculos = $this->veiculoService->listarAtivosParaOrdemServico(800);
        $sinistros = Sinistro::query()->orderByDesc('id')->get();
        $categorias = \App\Models\OsCategoria::with('subcategorias.campos')->where('is_active', true)->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Ordens de serviço', route('ordem-servico.index')),
            new BreadCrumbItem('Nova OS'),
        ]);

        return view('ordem-servico.create', array_merge($this->dadosFiltroVeiculo(), compact('veiculos', 'breadcrumb', 'veiculoId', 'danoIds', 'sinistros', 'reservaId', 'sinistroId', 'categorias')))
            ->with('titulo', 'Nova ordem de serviço');
    }

    public function store(StoreOrdemServicoRequest $request)
    {
        $dados = $request->validated();
        $servicosPrevistos = [];
        foreach ($request->input('servicos_previstos', []) as $key => $desc) {
            $desc = trim((string) $desc);
            if ($desc !== '') {
                $servicosPrevistos[] = [
                    'descricao' => $desc,
                    'observacao' => trim((string) ($request->input('servicos_previstos_observacoes')[$key] ?? ''))
                ];
            }
        }

        $empresaId = (int) $request->session()->get('empresa_context_id', 0);
        if ($empresaId === 0) {
            $empresaId = (int) (app(EmpresaContext::class)->empresaId() ?? Auth::user()->empresa_id ?? 0);
        }
        $empresaId = $empresaId > 0 ? $empresaId : null;

        try {
            $os = $this->ordemServicoService->criar(
                (int) $dados['veiculo_id'],
                isset($dados['reserva_id']) ? (int) $dados['reserva_id'] : null,
                $empresaId ?: null,
                $dados['observacoes'] ?? null,
                array_map('intval', $dados['veiculo_dano_ids'] ?? []),
                $servicosPrevistos,
                isset($dados['sinistro_id']) ? (int) $dados['sinistro_id'] : null,
                isset($dados['os_categoria_id']) ? (int) $dados['os_categoria_id'] : null,
                isset($dados['os_subcategoria_id']) ? (int) $dados['os_subcategoria_id'] : null,
                array_map('intval', $dados['pendencia_preventiva_ids'] ?? []),
                $request->input('campos_dinamicos', [])
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->withErrors(['os' => $e->getMessage()]);
        }

        return redirect()
            ->route('ordem-servico.show', $os)
            ->with('success', 'Ordem de serviço ' . ($os->numero ?? '#' . $os->id) . ' criada.');
    }

    public function show(OrdemServico $ordem_servico)
    {
        $ordem_servico->load(['itens.veiculoDano', 'anexos.usuario', 'veiculo', 'reserva', 'usuarioAbertura', 'usuarioEncerramento', 'valoresCampos.campo']);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Ordens de serviço', route('ordem-servico.index')),
            new BreadCrumbItem($ordem_servico->numero ?? 'OS #' . $ordem_servico->id),
        ]);
        
        return view('ordem-servico.show', compact('ordem_servico', 'breadcrumb'))
            ->with('titulo', 'OS ' . ($ordem_servico->numero ?? '#' . $ordem_servico->id));
    }

    public function pdf(Request $request, OrdemServico $ordem_servico)
    {
        $ordem_servico->load([
            'veiculo',
            'reserva',
            'usuarioAbertura',
            'usuarioEncerramento',
            'itens.veiculoDano',
        ]);

        $empresaId = (int) ($ordem_servico->empresa_id ?? 0);
        if ($empresaId === 0) {
            $empresaId = (int) $request->session()->get('empresa_context_id', 0);
        }
        if ($empresaId === 0) {
            $empresaId = (int) (app(EmpresaContext::class)->empresaId() ?? Auth::user()->empresa_id ?? 0);
        }
        $empresa = $empresaId > 0 ? Empresa::find($empresaId) : null;
        $branding = app(EmpresaBranding::class);

        $itens = $ordem_servico->itens->map(function ($item) {
            $dano = $item->veiculoDano;

            return [
                'descricao' => (string) ($item->descricao_servico ?? ''),
                'resultado' => (string) ($item->resultado ?? ''),
                'dano' => $dano ? [
                    'id' => $dano->id,
                    'local' => (string) ($dano->local_dano ?? ''),
                    'tipo' => (string) ($dano->tipo_dano ?? ''),
                    'observacoes' => (string) ($dano->observacoes ?? ''),
                    'status' => (string) ($dano->status ?? ''),
                    'correcao' => (string) ($dano->correcao_descricao ?? ''),
                    'correcao_data' => $dano->correcao_data ? $dano->correcao_data->format('d/m/Y H:i') : null,
                ] : null,
            ];
        })->values()->all();

        $danos = collect($itens)
            ->pluck('dano')
            ->filter()
            ->unique('id')
            ->values()
            ->all();

        $data = [
            'documento' => [
                'data_emissao' => now()->format('d/m/Y H:i'),
                'empresa' => [
                    'nome' => $empresa?->nome ?? config('app.name'),
                    'logo_url' => $branding->logoDataUri($empresa) ?? $branding->logoUrl($empresa),
                ],
            ],
            'os' => [
                'numero' => $ordem_servico->numero ?? ('#' . $ordem_servico->id),
                'status' => (string) ($ordem_servico->status ?? ''),
                'aberta_em' => $ordem_servico->created_at?->format('d/m/Y H:i'),
                'concluida_em' => $ordem_servico->data_conclusao?->format('d/m/Y H:i'),
                'aberta_por' => $ordem_servico->usuarioAbertura?->name ?? null,
                'concluida_por' => $ordem_servico->usuarioEncerramento?->name ?? null,
                'observacoes' => (string) ($ordem_servico->observacoes ?? ''),
                'relatorio_conclusao' => (string) ($ordem_servico->relatorio_conclusao ?? ''),
                'valor_pecas' => (float) ($ordem_servico->valor_pecas ?? 0),
                'valor_mao_obra' => (float) ($ordem_servico->valor_mao_obra ?? 0),
                'valor_total' => (float) ($ordem_servico->valor_total ?? 0),
                'itens_concluidos' => $ordem_servico->itens->whereNotNull('resultado')->count(),
            ],
            'veiculo' => [
                'nome' => $ordem_servico->veiculo?->nome ?? null,
                'placa' => $ordem_servico->veiculo?->placa_licenca ?? null,
            ],
            'reserva' => [
                'id' => $ordem_servico->reserva_id ? (int) $ordem_servico->reserva_id : null,
            ],
            'itens' => $itens,
            'danos' => $danos,
        ];

        return $this->pdfService->gerarOrdemServicoPdf($data, 'os-' . ($ordem_servico->numero ?? $ordem_servico->id) . '.pdf');
    }

    public function danosPendentes(Request $request)
    {
        $veiculoId = (int) $request->query('veiculo_id', 0);
        if ($veiculoId <= 0) {
            return response()->json([
                'danos' => [],
                'reservas' => [],
                'sinistros' => [],
                'preventivas' => [],
            ]);
        }

        $danos = VeiculoDanos::query()
            ->where('veiculo_id', $veiculoId)
            ->whereNull('correcao_data')
            ->orderByDesc('id')
            ->get();

        $danosOut = $danos->filter(function (VeiculoDanos $d) {
            return ! $this->ordemServicoService->danoEmOsAberta((int) $d->id);
        })->values()->map(function (VeiculoDanos $d) {
            return [
                'id' => $d->id,
                'label' => '#' . $d->id . ' — ' . strtoupper((string) $d->local_dano) . ' — ' . \Illuminate\Support\Str::limit((string) $d->observacoes, 80),
            ];
        });

        $reservas = \App\Models\Reserva::query()
            ->where('veiculo_id', $veiculoId)
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(function ($r) {
                $clienteNome = $r->cliente?->nome ?? $r->usuario?->nome ?? 'Cliente não informado';
                return [
                    'id' => $r->id,
                    'label' => '#' . $r->id . ' — ' . $clienteNome . ' (' . ($r->data_retirada?->format('d/m/Y') ?? 'Sem data') . ')',
                ];
            });

        $sinistros = \App\Models\Sinistro::query()
            ->where('veiculo_id', $veiculoId)
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'label' => '#' . $s->id . ' — ' . ucfirst($s->tipo_sinistro) . ' (' . ($s->data_hora_ocorrido?->format('d/m/Y') ?? 'Sem data') . ')',
                ];
            });

        $preventivas = \App\Models\PendenciaManutencaoPreventiva::query()
            ->with(['item', 'veiculo:id,kms'])
            ->where('veiculo_id', $veiculoId)
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($p) {
                $vencidaPorData = !empty($p->data_prevista) && \Carbon\Carbon::parse($p->data_prevista)->isPast();
                $vencidaPorKm = $p->km_previsto !== null && (int) ($p->veiculo?->kms ?? 0) >= (int) $p->km_previsto;
                $vencida = in_array((string) $p->status, ['pendente', 'atrasado'], true) && ($vencidaPorData || $vencidaPorKm);

                return [
                    'id' => $p->id,
                    'label' => $p->item?->nome ?? ('Preventiva #' . $p->id),
                    'data_prevista' => $p->data_prevista ? \Carbon\Carbon::parse($p->data_prevista)->format('d/m/Y') : null,
                    'km_previsto' => $p->km_previsto !== null ? number_format((float) $p->km_previsto, 0, ',', '.') : null,
                    'status' => (string) $p->status,
                    'vencida' => $vencida,
                ];
            });

        return response()->json([
            'danos' => $danosOut,
            'reservas' => $reservas,
            'sinistros' => $sinistros,
            'preventivas' => $preventivas,
        ]);
    }

    public function atualizarStatus(AtualizarStatusOrdemServicoRequest $request, OrdemServico $ordem_servico)
    {
        try {
            $this->ordemServicoService->atualizarStatus($ordem_servico, $request->validated()['status']);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['os' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Status da OS atualizado.');
    }

    public function concluir(ConcluirOrdemServicoRequest $request, OrdemServico $ordem_servico)
    {
        $dados = $request->validated();

        try {
            $dir = 'ordem-servico/os_' . (int) $ordem_servico->id . '/anexos';
            $anexosMeta = [];
            foreach (($request->file('anexos') ?? []) as $file) {
                if (! $file) {
                    continue;
                }
                $armazenado = ArquivoAnexoHelper::armazenarRastreado($file, $dir, [
                    'empresa_id' => (int) ($ordem_servico->empresa_id ?? $request->session()->get('empresa_context_id', 0) ?? 0),
                    'user_id' => Auth::id(),
                    'modulo' => 'ordem_servico',
                    'entidade' => OrdemServico::class,
                    'entidade_id' => $ordem_servico->id,
                    'campo' => 'anexo',
                    'contexto' => 'ordem-servico.conclusao',
                    'meta' => [
                        'tipo_anexo' => 'conclusao',
                    ],
                ]);
                $path = $armazenado['path'] ?? null;
                if (! $path) {
                    continue;
                }
                $anexosMeta[] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                    'user_id' => Auth::id(),
                ];
            }

            // Map avarias resolutions
            $avariasResolutions = [];
            foreach ($request->input('avarias_status', []) as $danoId => $st) {
                $avariasResolutions[(int) $danoId] = [
                    'status' => $st,
                    'obs' => (string) ($request->input('avarias_obs')[$danoId] ?? '')
                ];
            }

            // Map executed pieces
            $pecasExecutadas = [];
            foreach ($request->input('pecas_exec_nome', []) as $k => $nome) {
                $nome = trim((string) $nome);
                if ($nome !== '') {
                    $pecasExecutadas[] = [
                        'nome' => $nome,
                        'qtd' => (int) ($request->input('pecas_exec_qtd')[$k] ?? 1),
                        'valor' => $request->input('pecas_exec_valor')[$k] ?? '0,00'
                    ];
                }
            }

            // Map executed labor
            $servicosExecutados = [];
            foreach ($request->input('servicos_exec_nome', []) as $k => $nome) {
                $nome = trim((string) $nome);
                if ($nome !== '') {
                    $servicosExecutados[] = [
                        'nome' => $nome,
                        'qtd' => (int) ($request->input('servicos_exec_qtd')[$k] ?? 1),
                        'valor' => $request->input('servicos_exec_valor')[$k] ?? '0,00'
                    ];
                }
            }

            // Map completed items
            $itensCompletados = array_map('intval', $request->input('itens_completados', []));

            $this->ordemServicoService->concluir(
                $ordem_servico,
                $dados['relatorio_geral'],
                $anexosMeta,
                $avariasResolutions,
                $pecasExecutadas,
                $servicosExecutados,
                $itensCompletados
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['os' => $e->getMessage()]);
        }

        return redirect()->route('ordem-servico.show', $ordem_servico)->with('success', 'OS concluída. Avarias vinculadas foram atualizadas com o resultado do serviço.');
    }

    public function removerDano(int $danoId)
    {
        $dano = \App\Models\VeiculoDanos::findOrFail($danoId);

        // Achar o item em OS aberta ou em andamento
        $item = \App\Models\OrdemServicoItem::where('veiculo_dano_id', $danoId)
            ->whereHas('ordemServico', function($q) {
                $q->whereIn('status', ['aberta', 'em_andamento']);
            })->first();

        if ($item) {
            $item->delete();
        }

        $dano->update([
            'status' => 'pendente',
            'correcao_data' => null,
            'correcao_descricao' => null,
            'correcao_user_id' => null
        ]);

        return response()->json(['success' => true]);
    }
}
