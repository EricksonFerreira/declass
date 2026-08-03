<?php

namespace App\Http\Controllers;

use App\Actions\Veiculo\SincronizarDocumentacaoVeiculoAction;
use App\Models\CategoriaFinanceira;
use App\Models\DocumentacaoVeiculo;
use App\Models\DocumentacaoVeiculoParcela;
use App\Models\LancamentoFinanceiro;
use App\Models\Veiculo;
use App\Http\Requests\DocumentacaoVeiculo\AtualizarPagamentoParcelaDocumentacaoVeiculoRequest;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DocumentacaoVeiculoController extends Controller
{
    private function getEmpresaId(): ?int
    {
        return app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;
    }

    public function index()
    {
        $empresaId = $this->getEmpresaId();
        $filtroTipo = request()->string('tipo')->toString();
        $filtroStatus = request()->string('status')->toString();
        $filtroBusca = trim((string) request()->query('q', ''));
        $filtroVeiculo = trim((string) request()->query('veiculo', ''));
        $filtroPlaca = trim((string) request()->query('placa', ''));
        $filtroSomenteVencidas = request()->boolean('somente_vencidas');
        $filtroValorMin = request()->query('valor_min');
        $filtroValorMax = request()->query('valor_max');

        $documentacoes = DocumentacaoVeiculo::query()
            ->with(['veiculo', 'categoriaFinanceira', 'parcelas'])
            ->where('empresa_id', $empresaId)
            ->when(in_array($filtroTipo, ['ipva', 'licenciamento', 'seguro'], true), function ($query) use ($filtroTipo) {
                $query->where('tipo', $filtroTipo);
            })
            ->when(in_array($filtroStatus, ['ativo', 'inativo'], true), function ($query) use ($filtroStatus) {
                $query->where('status', $filtroStatus);
            })
            ->when($filtroBusca !== '', function ($query) use ($filtroBusca) {
                $query->where(function ($subQuery) use ($filtroBusca) {
                    $subQuery->where('descricao', 'like', '%' . $filtroBusca . '%')
                        ->orWhere('nome', 'like', '%' . $filtroBusca . '%')
                        ->orWhereHas('veiculo', function ($veiculoQuery) use ($filtroBusca) {
                            $veiculoQuery->where('nome', 'like', '%' . $filtroBusca . '%')
                                ->orWhere('placa_licenca', 'like', '%' . $filtroBusca . '%');
                        });
                });
            })
            ->when($filtroVeiculo !== '', function ($query) use ($filtroVeiculo) {
                $query->whereHas('veiculo', function ($veiculoQuery) use ($filtroVeiculo) {
                    $veiculoQuery->where('nome', 'like', '%' . $filtroVeiculo . '%');
                });
            })
            ->when($filtroPlaca !== '', function ($query) use ($filtroPlaca) {
                $query->whereHas('veiculo', function ($veiculoQuery) use ($filtroPlaca) {
                    $veiculoQuery->where('placa_licenca', 'like', '%' . $filtroPlaca . '%');
                });
            })
            ->when($filtroValorMin !== null && $filtroValorMin !== '', function ($query) use ($filtroValorMin) {
                $query->where('valor_parcela', '>=', (float) str_replace(',', '.', (string) $filtroValorMin));
            })
            ->when($filtroValorMax !== null && $filtroValorMax !== '', function ($query) use ($filtroValorMax) {
                $query->where('valor_parcela', '<=', (float) str_replace(',', '.', (string) $filtroValorMax));
            })
            ->when($filtroSomenteVencidas, function ($query) {
                $query->whereHas('parcelas', function ($parcelaQuery) {
                    $parcelaQuery->whereIn('status', ['pendente', 'atrasado'])
                        ->whereDate('data_vencimento', '<', now()->toDateString());
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->appends(request()->query());

        return view('veiculo.documentacao', compact(
            'documentacoes',
            'filtroTipo',
            'filtroStatus',
            'filtroBusca',
            'filtroVeiculo',
            'filtroPlaca',
            'filtroSomenteVencidas',
            'filtroValorMin',
            'filtroValorMax'
        ))
            ->with('titulo', 'Documentacao de Veiculos');
    }

    public function create()
    {
        $veiculoId = (int) request()->query('veiculo_id', 0);
        $veiculoSelecionado = $veiculoId > 0 ? Veiculo::query()->find($veiculoId) : null;
        $veiculos = Veiculo::query()->orderBy('nome')->get();

        return view('veiculo.documentacao-form', compact('veiculos', 'veiculoSelecionado', 'veiculoId'))
            ->with('titulo', 'Nova Documentacao');
    }

    public function edit(DocumentacaoVeiculo $documentacao)
    {
        $documentacao->load('veiculo', 'parcelas');
        $veiculoSelecionado = $documentacao->veiculo;
        $veiculoId = (int) $documentacao->veiculo_id;
        $veiculos = Veiculo::query()->orderBy('nome')->get();

        return view('veiculo.documentacao-form', compact('veiculos', 'veiculoSelecionado', 'veiculoId', 'documentacao'))
            ->with('titulo', 'Editar Documentacao');
    }

    public function store(Request $request)
    {
        $empresaId = $this->getEmpresaId();
        $data = $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'tipo' => ['required', 'in:ipva,licenciamento,seguro'],
            'descricao' => ['nullable', 'string'],
            'parcelas_total' => ['required', 'integer', 'min:1'],
            'valor_parcela' => ['required', 'numeric', 'min:0'],
            'data_primeiro_vencimento' => ['required', 'date'],
            'parcelas' => ['nullable', 'array'],
            'parcelas.*.data_vencimento' => ['nullable', 'date'],
            'parcelas.*.data_pagamento' => ['nullable', 'date'],
            'parcelas.*.status' => ['nullable', 'in:pendente,pago'],
        ]);

        foreach (($data['parcelas'] ?? []) as $indice => $parcelaValidada) {
            if (($parcelaValidada['status'] ?? 'pendente') === 'pago' && empty($parcelaValidada['data_pagamento'])) {
                return back()
                    ->withErrors([
                        "parcelas.$indice.data_pagamento" => 'A data de pagamento é obrigatória quando o status estiver como pago.',
                    ])
                    ->withInput();
            }
        }

        $documentacao = DocumentacaoVeiculo::create([
            'empresa_id' => $empresaId,
            'veiculo_id' => $data['veiculo_id'],
            'tipo' => $data['tipo'],
            'nome' => strtoupper($data['tipo']) . ' - ' . ($data['descricao'] ?: 'Documento'),
            'descricao' => $data['descricao'] ?? null,
            'parcelas_total' => $data['parcelas_total'],
            'valor_parcela' => $data['valor_parcela'],
            'status' => 'ativo',
            'categoria_financeira_id' => null,
        ]);

        app(SincronizarDocumentacaoVeiculoAction::class)->handle(
            $documentacao,
            $data['parcelas'] ?? [],
            $data['data_primeiro_vencimento']
        );

        return redirect()
            ->route('veiculo.documentacao')
            ->with('success', 'Documentacao cadastrada com parcelas e lancamentos pendentes no fluxo de caixa.');
    }

    public function update(Request $request, DocumentacaoVeiculo $documentacao)
    {
        $empresaId = $this->getEmpresaId();
        $data = $request->validate([
            'veiculo_id' => ['required', 'integer', 'exists:veiculos,id'],
            'tipo' => ['required', 'in:ipva,licenciamento,seguro'],
            'descricao' => ['nullable', 'string'],
            'parcelas_total' => ['required', 'integer', 'min:1'],
            'valor_parcela' => ['required', 'numeric', 'min:0'],
            'data_primeiro_vencimento' => ['required', 'date'],
            'parcelas' => ['nullable', 'array'],
            'parcelas.*.data_vencimento' => ['nullable', 'date'],
            'parcelas.*.data_pagamento' => ['nullable', 'date'],
            'parcelas.*.status' => ['nullable', 'in:pendente,pago'],
        ]);

        foreach (($data['parcelas'] ?? []) as $indice => $parcelaValidada) {
            if (($parcelaValidada['status'] ?? 'pendente') === 'pago' && empty($parcelaValidada['data_pagamento'])) {
                return back()
                    ->withErrors([
                        "parcelas.$indice.data_pagamento" => 'A data de pagamento é obrigatória quando o status estiver como pago.',
                    ])
                    ->withInput();
            }
        }

        $documentacao->update([
            'empresa_id' => $empresaId,
            'veiculo_id' => $data['veiculo_id'],
            'tipo' => $data['tipo'],
            'nome' => strtoupper($data['tipo']) . ' - ' . ($data['descricao'] ?: 'Documento'),
            'descricao' => $data['descricao'] ?? null,
            'parcelas_total' => $data['parcelas_total'],
            'valor_parcela' => $data['valor_parcela'],
            'status' => 'ativo',
        ]);

        app(SincronizarDocumentacaoVeiculoAction::class)->handle(
            $documentacao,
            $data['parcelas'] ?? [],
            $data['data_primeiro_vencimento']
        );

        return redirect()
            ->route('veiculo.documentacao')
            ->with('success', 'Documentacao atualizada.');
    }

    public function pagarParcela(
        AtualizarPagamentoParcelaDocumentacaoVeiculoRequest $request,
        DocumentacaoVeiculoParcela $parcela,
        SincronizarDocumentacaoVeiculoAction $action
    ) {
        $empresaId = $this->getEmpresaId();
        $parcela->loadMissing('documentacaoVeiculo.veiculo');

        abort_unless(
            $parcela->documentacaoVeiculo
                && (int) $parcela->documentacaoVeiculo->empresa_id === (int) $empresaId,
            403
        );

        $parcela = $action->pagarParcela($parcela, $request->validated()['data_pagamento']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento registrado com sucesso.',
                'parcela' => [
                    'id' => $parcela->id,
                    'status' => $parcela->status,
                    'data_pagamento' => optional($parcela->data_pagamento)->format('d/m/Y'),
                ],
            ]);
        }

        return redirect()
            ->back()
            ->with('success', 'Pagamento registrado com sucesso.');
    }
}
