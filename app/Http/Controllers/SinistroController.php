<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sinistro\StoreSinistroRequest;
use App\Http\Requests\Sinistro\UpdateSinistroRequest;
use App\Models\GrupoFrota;
use App\Models\Marca;
use App\Models\Sinistro;
use App\Models\StatusVeiculo;
use App\Models\TipoFrota;
use App\Services\BreadcrumbService;
use App\Services\ReservaService;
use App\Services\SinistroService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Support\EmpresaContext;
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
