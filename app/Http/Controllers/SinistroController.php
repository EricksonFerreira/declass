<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sinistro\StoreSinistroRequest;
use App\Http\Requests\Sinistro\UpdateSinistroRequest;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\Reserva;
use App\Models\Sinistro;
use App\Models\StatusVeiculo;
use App\Models\TipoFrota;
use App\Models\Veiculo;
use App\Services\BreadcrumbService;
use App\Services\ReservaService;
use App\Services\SinistroService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SinistroController extends BaseController
{
    public function __construct(
        private readonly SinistroService   $sinistroService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly ReservaService    $reservaService,
        private readonly UsuarioService    $usuarioService,
        private readonly VeiculoService    $veiculoService,
    ) {
        $this->titulo = 'Sinistros';
    }

    public function index(Request $request)
    {
        $this->authorize('sinistro.index');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'placa', 'tipo_sinistro', 'status', 'reserva_id', 'veiculo_id',
        ]);

        $perPage = $request->input('per_page', 15);

        $query = $this->sinistroService->queryScoped()
            ->with(['veiculo', 'cliente', 'reserva', 'responsavel'])
            ->orderBy('id', 'desc');

        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_ocorrido', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_ocorrido', '<=', $filtros['data_fim'] . ' 23:59:59');
        }
        if (!empty($filtros['placa'])) {
            $query->where('placa_momento', 'like', '%' . $filtros['placa'] . '%');
        }
        if (!empty($filtros['tipo_sinistro'])) {
            $query->where('tipo_sinistro', $filtros['tipo_sinistro']);
        }
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }
        if (!empty($filtros['reserva_id'])) {
            $query->where('reserva_id', $filtros['reserva_id']);
        }
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }

        $sinistros  = $query->paginate($perPage)->withQueryString();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Sinistros');

        return view('sinistro.index', compact('sinistros', 'breadcrumb', 'filtros'))
            ->with('titulo', $this->titulo);
    }

    private function dadosFiltroVeiculo(): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $scope = fn ($q) => $empresaId ? $q->where('empresa_id', $empresaId) : $q;

        return [
            'filtroMarcas'  => $scope(Marca::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroGrupos'  => $scope(GrupoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroTipos'   => $scope(TipoFrota::query())->orderBy('nome')->pluck('nome', 'id'),
            'filtroStatus'  => $scope(StatusVeiculo::query())->orderBy('nome')->pluck('nome', 'id'),
        ];
    }

    public function create(Request $request)
    {
        $this->authorize('sinistro.create');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Sinistros', 'sinistro.index', 'Novo Sinistro');
        $reservaId  = $request->query('reserva_id');
        $reserva    = $reservaId ? $this->reservaService->getById($reservaId, ['usuario', 'grupoFrota']) : null;

        $veiculoId = $request->query('veiculo_id');
        $veiculo   = $veiculoId ? $this->veiculoService->getById($veiculoId, ['marca', 'modelo']) : null;

        return view('sinistro.add-edit', array_merge($this->dadosFiltroVeiculo(), [
            'sinistro'  => null,
            'reserva'   => $reserva,
            'veiculo'   => $veiculo,
            'tipos'     => Sinistro::TIPOS,
            'statusOpts'=> Sinistro::STATUS,
            'breadcrumb'=> $breadcrumb,
            'titulo'    => 'Novo Sinistro',
            'metodo'    => 'POST',
            'rota'      => route('sinistro.store'),
        ]));
    }

    public function store(StoreSinistroRequest $request)
    {
        $this->authorize('sinistro.create');

        $dados = $request->except(['anexo_bo', 'anexo_foto1', 'anexo_foto2', 'anexo_foto3', 'anexo_laudo']);
        $dados['terceiros_envolvidos'] = $request->boolean('terceiros_envolvidos');
        $dados['vitimas']              = $request->boolean('vitimas');

        $sinistro = $this->sinistroService->criar($dados);

        foreach (['bo', 'foto1', 'foto2', 'foto3', 'laudo'] as $tipo) {
            $campo = "anexo_{$tipo}";
            if ($request->hasFile($campo)) {
                $path = $this->sinistroService->salvarAnexo($request->file($campo), $sinistro->id, $tipo);
                $sinistro->update(["{$campo}_path" => $path]);
            }
        }

        return redirect()->route('sinistro.show', $sinistro->id)
            ->with('success', 'Sinistro registrado com sucesso.');
    }

    public function show(int $id)
    {
        $this->authorize('sinistro.index');

        $sinistro = $this->sinistroService->queryScoped()->with([
            'veiculo.marca', 'veiculo.modelo',
            'reserva.usuario',
            'cliente', 'condutor', 'responsavel',
            'pagoEmpresaPor', 'pagoClientePor',
            'historico.usuario',
        ])->findOrFail($id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Sinistros', 'sinistro.index', 'Detalhes');

        return view('sinistro.show', compact('sinistro', 'breadcrumb'))
            ->with('titulo', 'Sinistro #' . $sinistro->id);
    }

    public function edit(int $id)
    {
        $this->authorize('sinistro.edit');

        $sinistro   = $this->sinistroService->queryScoped()->with(['reserva', 'veiculo.marca', 'veiculo.modelo'])->findOrFail($id);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Sinistros', 'sinistro.index', 'Editar Sinistro');

        return view('sinistro.add-edit', array_merge($this->dadosFiltroVeiculo(), [
            'sinistro'  => $sinistro,
            'reserva'   => $sinistro->reserva,
            'veiculo'   => $sinistro->veiculo,
            'tipos'     => Sinistro::TIPOS,
            'statusOpts'=> Sinistro::STATUS,
            'breadcrumb'=> $breadcrumb,
            'titulo'    => 'Editar Sinistro #' . $sinistro->id,
            'metodo'    => 'PUT',
            'rota'      => route('sinistro.update', $sinistro->id),
        ]));
    }

    public function update(UpdateSinistroRequest $request, int $id)
    {
        $this->authorize('sinistro.edit');

        $dados = $request->except(['anexo_bo', 'anexo_foto1', 'anexo_foto2', 'anexo_foto3', 'anexo_laudo']);
        $dados['terceiros_envolvidos'] = $request->boolean('terceiros_envolvidos');
        $dados['vitimas']              = $request->boolean('vitimas');

        $sinistro = $this->sinistroService->atualizar($id, $dados);
        if (!$sinistro) {
            return redirect()->route('sinistro.index')->with('error', 'Sinistro não encontrado.');
        }

        foreach (['bo', 'foto1', 'foto2', 'foto3', 'laudo'] as $tipo) {
            $campo = "anexo_{$tipo}";
            if ($request->hasFile($campo)) {
                $path = $this->sinistroService->salvarAnexo($request->file($campo), $sinistro->id, $tipo);
                $sinistro->update(["{$campo}_path" => $path]);
            }
        }

        return redirect()->route('sinistro.show', $sinistro->id)
            ->with('success', 'Sinistro atualizado com sucesso.');
    }

    /**
     * AJAX: busca a reserva ativa de um veículo em uma data/hora específica.
     */
    public function buscarReservaPorVeiculoData(Request $request): JsonResponse
    {
        $veiculoId     = $request->query('veiculo_id');
        $dataHora      = $request->query('data_hora');

        if (!$veiculoId || !$dataHora) {
            return response()->json(['reserva' => null]);
        }

        try {
            $dt = Carbon::parse($dataHora);
        } catch (\Throwable) {
            return response()->json(['reserva' => null]);
        }

        $veiculo = Veiculo::find($veiculoId);
        if (!$veiculo) {
            return response()->json(['reserva' => null]);
        }

        // Busca reserva pelo modelo do veículo que contenha essa data/hora
        $reserva = Reserva::where('modelo_id', $veiculo->modelo_id)
            ->where('data_inicio', '<=', $dt->toDateTimeString())
            ->where('data_fim', '>=', $dt->toDateString())
            ->with(['usuario'])
            ->orderByDesc('data_inicio')
            ->first();

        if (!$reserva) {
            return response()->json(['reserva' => null]);
        }

        return response()->json([
            'reserva' => [
                'id'          => $reserva->id,
                'cliente'     => $reserva->usuario?->nome ?? 'Cliente #' . $reserva->user_id,
                'data_inicio' => $reserva->data_inicio,
                'data_fim'    => $reserva->data_fim,
            ],
        ]);
    }

    public function confirmarPagamentoEmpresa(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('sinistro.edit');

        $sinistro = $this->sinistroService->queryScoped()->findOrFail($id);

        if ($sinistro->pago_empresa_em) {
            return response()->json(['error' => 'Pagamento da empresa já registrado.'], 422);
        }

        $request->validate([
            'forma_pagamento'      => 'required|in:dinheiro,cartao_credito,cartao_debito,pix,boleto,transferencia',
            'referencia_pagamento' => 'nullable|string|max:100',
        ]);

        $sinistro->update([
            'forma_pagamento_empresa'       => $request->input('forma_pagamento'),
            'referencia_pagamento_empresa'  => $request->input('referencia_pagamento'),
            'pago_empresa_em'               => now(),
            'pago_empresa_por_user_id'      => Auth::id(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Pagamento (empresa) confirmado com sucesso.',
            'pago_em'  => now()->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);
    }

    public function confirmarPagamentoCliente(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('sinistro.edit');

        $sinistro = $this->sinistroService->queryScoped()->findOrFail($id);

        if ($sinistro->pago_cliente_em) {
            return response()->json(['error' => 'Pagamento do cliente já registrado.'], 422);
        }

        $request->validate([
            'forma_pagamento'      => 'required|in:dinheiro,cartao_credito,cartao_debito,pix,boleto',
            'referencia_pagamento' => 'nullable|string|max:100',
        ]);

        $sinistro->update([
            'forma_pagamento_cliente'      => $request->input('forma_pagamento'),
            'referencia_pagamento_cliente' => $request->input('referencia_pagamento'),
            'pago_cliente_em'              => now(),
            'pago_cliente_por_user_id'     => Auth::id(),
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Pagamento (cliente) confirmado com sucesso.',
            'pago_em'  => now()->format('d/m/Y H:i'),
            'pago_por' => Auth::user()->nome ?? Auth::user()->name,
        ]);
    }

    public function destroy(int $id)
    {
        $this->authorize('sinistro.delete');

        $sinistro = $this->sinistroService->queryScoped()->findOrFail($id);
        $sinistro->delete();

        return redirect()->route('sinistro.index')
            ->with('success', 'Sinistro removido com sucesso.');
    }

    public function export(Request $request)
    {
        $this->authorize('sinistro.export');

        $filtros = $request->only([
            'data_inicio', 'data_fim', 'placa', 'tipo_sinistro', 'status', 'reserva_id', 'veiculo_id',
        ]);

        return $this->sinistroService->exportarCsv($filtros);
    }
}
