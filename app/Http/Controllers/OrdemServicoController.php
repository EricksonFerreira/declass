<?php

namespace App\Http\Controllers;

use App\DTO\BreadCrumbItem;
use App\Http\Requests\OrdemServico\AtualizarStatusOrdemServicoRequest;
use App\Http\Requests\OrdemServico\ConcluirOrdemServicoRequest;
use App\Http\Requests\OrdemServico\StoreOrdemServicoRequest;
use App\Models\OrdemServico;
use App\Models\VeiculoDanos;
use App\Models\Empresa;
use App\Services\BreadcrumbService;
use App\Services\OrdemServicoService;
use App\Services\PdfService;
use App\Services\VeiculoService;
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

    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = OrdemServico::query()
            ->with(['veiculo', 'usuarioAbertura'])
            ->orderByDesc('id');

        if ($status && in_array($status, [
            OrdemServico::STATUS_ABERTA,
            OrdemServico::STATUS_EM_ANDAMENTO,
            OrdemServico::STATUS_CONCLUIDA,
            OrdemServico::STATUS_CANCELADA,
        ], true)) {
            $query->where('status', $status);
        }

        $ordens = $query->paginate(20)->withQueryString();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Ordens de serviço', route('ordem-servico.index')),
        ]);

        return view('ordem-servico.index', compact('ordens', 'breadcrumb', 'status'))
            ->with('titulo', 'Ordens de serviço (OS)');
    }

    public function create(Request $request)
    {
        $veiculoId = (int) $request->query('veiculo_id', 0);
        $danoIds = $request->query('dano_ids', []);
        if (! is_array($danoIds)) {
            $danoIds = $danoIds ? [(int) $danoIds] : [];
        }
        $danoIds = array_map('intval', $danoIds);

        $veiculos = $this->veiculoService->listarAtivosParaOrdemServico(800);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Ordens de serviço', route('ordem-servico.index')),
            new BreadCrumbItem('Nova OS'),
        ]);

        return view('ordem-servico.create', compact('veiculos', 'breadcrumb', 'veiculoId', 'danoIds'))
            ->with('titulo', 'Nova ordem de serviço');
    }

    public function store(StoreOrdemServicoRequest $request)
    {
        $dados = $request->validated();
        $linhas = preg_split('/\r\n|\r|\n/', (string) ($dados['servicos_livres'] ?? ''));
        $servicosLivres = $linhas ? array_values(array_filter(array_map('trim', $linhas))) : [];

        $empresaId = (int) $request->session()->get('empresa_context_id', 0);
        if ($empresaId === 0) {
            $empresaId = (int) (Auth::user()->empresa_id ?? 0);
        }
        $empresaId = $empresaId > 0 ? $empresaId : null;

        try {
            $os = $this->ordemServicoService->criar(
                (int) $dados['veiculo_id'],
                isset($dados['reserva_id']) ? (int) $dados['reserva_id'] : null,
                $empresaId ?: null,
                $dados['observacoes'] ?? null,
                array_map('intval', $dados['veiculo_dano_ids'] ?? []),
                $servicosLivres
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
        $ordem_servico->load(['itens.veiculoDano', 'veiculo', 'reserva', 'usuarioAbertura', 'usuarioEncerramento']);

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
            $empresaId = (int) (Auth::user()->empresa_id ?? 0);
        }
        $empresa = $empresaId > 0 ? Empresa::find($empresaId) : null;

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
                    'logo_url' => $empresa?->logo_url ?? null,
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
            return response()->json([]);
        }

        $danos = VeiculoDanos::query()
            ->where('veiculo_id', $veiculoId)
            ->whereNull('correcao_data')
            ->orderByDesc('id')
            ->get();

        $out = $danos->filter(function (VeiculoDanos $d) {
            return ! $this->ordemServicoService->danoEmOsAberta((int) $d->id);
        })->values()->map(function (VeiculoDanos $d) {
            return [
                'id' => $d->id,
                'label' => '#' . $d->id . ' — ' . strtoupper((string) $d->local_dano) . ' — ' . \Illuminate\Support\Str::limit((string) $d->observacoes, 80),
            ];
        });

        return response()->json($out);
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
        $map = [];
        foreach ($dados['resultados'] ?? [] as $itemId => $texto) {
            $map[(int) $itemId] = (string) $texto;
        }

        try {
            $this->ordemServicoService->concluir($ordem_servico, $dados['relatorio_geral'], $map);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['os' => $e->getMessage()]);
        }

        return redirect()->route('ordem-servico.show', $ordem_servico)->with('success', 'OS concluída. Avarias vinculadas foram atualizadas com o resultado do serviço.');
    }
}
