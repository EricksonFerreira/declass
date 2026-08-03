<?php

namespace App\Http\Controllers;

use App\Actions\Veiculo\PagarParcelaDocumentacaoVeiculoAction;
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
        $documentacoes = DocumentacaoVeiculo::query()
            ->with(['veiculo', 'categoriaFinanceira', 'parcelas'])
            ->where('empresa_id', $empresaId)
            ->orderByDesc('id')
            ->paginate(15);

        return view('veiculo.documentacao', compact('documentacoes'))
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

        $categoria = CategoriaFinanceira::firstOrCreate(
            ['empresa_id' => $empresaId, 'nome' => 'Documentacao do veiculo', 'tipo' => 'despesa'],
            [
                'codigo' => CategoriaFinanceira::sugerirCodigo($empresaId, 'despesa', null),
                'status' => 'ativo',
                'permite_lancamento' => true,
                'origem' => 'manual',
            ]
        );

        $documentacao = DocumentacaoVeiculo::create([
            'empresa_id' => $empresaId,
            'veiculo_id' => $data['veiculo_id'],
            'tipo' => $data['tipo'],
            'nome' => strtoupper($data['tipo']) . ' - ' . ($data['descricao'] ?: 'Documento'),
            'descricao' => $data['descricao'] ?? null,
            'parcelas_total' => $data['parcelas_total'],
            'valor_parcela' => $data['valor_parcela'],
            'status' => 'ativo',
            'categoria_financeira_id' => $categoria->id,
        ]);

        $primeiroVencimento = Carbon::parse($data['data_primeiro_vencimento'])->startOfDay();
        $parcelasInformadas = collect($data['parcelas'] ?? [])->values();

        for ($parcela = 1; $parcela <= (int) $data['parcelas_total']; $parcela++) {
            $parcelaInformada = $parcelasInformadas->get($parcela - 1, []);
            $dataVencimento = !empty($parcelaInformada['data_vencimento'])
                ? Carbon::parse($parcelaInformada['data_vencimento'])->toDateString()
                : $primeiroVencimento->copy()->addMonthsNoOverflow($parcela - 1)->toDateString();
            $statusParcela = $parcelaInformada['status'] ?? 'pendente';
            $dataPagamentoParcela = !empty($parcelaInformada['data_pagamento'])
                ? Carbon::parse($parcelaInformada['data_pagamento'])->toDateString()
                : ($statusParcela === 'pago' ? $dataVencimento : null);

            LancamentoFinanceiro::create([
                'empresa_id' => $empresaId,
                'descricao' => strtoupper($data['tipo']) . ' - Parcela ' . $parcela . '/' . $data['parcelas_total'],
                'valor' => $data['valor_parcela'],
                'tipo' => 'despesa',
                'categoria_financeira_id' => $categoria->id,
                'data_vencimento' => $dataVencimento,
                'status' => $statusParcela,
                'veiculo_id' => $data['veiculo_id'],
                'documentacao_veiculo_id' => $documentacao->id,
                'observacoes' => 'Lancamento automatico da documentacao do veiculo.',
                'recorrente' => false,
                'recorrencia' => null,
                'parcelas_totais' => $data['parcelas_total'],
                'parcela_atual' => $parcela,
                'grupo_recorrencia_id' => null,
                'user_id' => Auth::id(),
            ]);

            DocumentacaoVeiculoParcela::create([
                'documentacao_veiculo_id' => $documentacao->id,
                'numero_parcela' => $parcela,
                'data_vencimento' => $dataVencimento,
                'data_pagamento' => $dataPagamentoParcela,
                'valor' => $data['valor_parcela'],
                'status' => $statusParcela,
            ]);
        }

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

        return redirect()
            ->route('veiculo.documentacao')
            ->with('success', 'Documentacao atualizada.');
    }

    public function pagarParcela(
        AtualizarPagamentoParcelaDocumentacaoVeiculoRequest $request,
        DocumentacaoVeiculoParcela $parcela,
        PagarParcelaDocumentacaoVeiculoAction $action
    ) {
        $empresaId = $this->getEmpresaId();
        $parcela->loadMissing('documentacaoVeiculo.veiculo');

        abort_unless(
            $parcela->documentacaoVeiculo
                && (int) $parcela->documentacaoVeiculo->empresa_id === (int) $empresaId,
            403
        );

        $parcela = $action->handle($parcela, $request->validated()['data_pagamento']);

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
