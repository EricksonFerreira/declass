<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Aluno\StoreAlunoRequest;
use App\Http\Requests\Aluno\StoreVinculoRequest;
use App\Http\Requests\Aluno\UpdateAlunoRequest;
use App\Http\Requests\Aluno\UpdateVinculoRequest;
use App\Models\Aluno;
use App\Models\Escola;
use App\Models\Responsavel;
use App\Services\AlunoService;
use App\Services\BreadcrumbService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class AlunoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected AlunoService $alunoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Alunos';
        $this->nomeFiltro = 'aluno';

        $this->middleware('can:aluno.index')->only('index');
        $this->middleware('can:aluno.show')->only('show');
        $this->middleware('can:aluno.create')->only('create');
        $this->middleware('can:aluno.store')->only('store');
        $this->middleware('can:aluno.edit')->only('edit');
        $this->middleware('can:aluno.update')->only('update');
        $this->middleware('can:aluno.destroy')->only('destroy');
        $this->middleware('can:aluno.filtro')->only('filtro');
        $this->middleware('can:aluno.removerFiltro')->only('removerFiltro');
        $this->middleware('can:aluno.setPerPage')->only('setPerPage');
        $this->middleware('can:aluno.responsavel.store')->only('storeResponsavel');
        $this->middleware('can:aluno.responsavel.update')->only('updateResponsavel');
        $this->middleware('can:aluno.responsavel.destroy')->only('destroyResponsavel');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $alunos = $this->alunoService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Alunos', 'Acadêmico');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('aluno.index', compact(
            'alunos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Alunos', 'aluno.index', 'Novo Aluno', 'Acadêmico');
        $opcoes = $this->opcoesFormulario();

        return view('aluno.add-edit', array_merge(compact('breadcrumb'), $opcoes))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreAlunoRequest $request)
    {
        [$pessoaData, $alunoData] = $this->separarDados($request);

        $this->alunoService->createWithPessoa($pessoaData, $alunoData);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aluno cadastrado com sucesso.');
    }

    public function show($id)
    {
        $aluno = $this->alunoService->getById($id, ['pessoa', 'escola', 'responsaveis.pessoa', 'documentos']);
        abort_if(! $aluno, 404, 'Aluno não encontrado.');

        $vinculos = $this->alunoService->vinculosDoAluno($aluno);
        $responsaveisOpcoes = $this->responsaveisOpcoes();
        $tiposResponsavel = Aluno::TIPOS_RESPONSAVEL;
        $tiposDocumento = \App\Models\AlunoDocumento::TIPOS;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Alunos', 'aluno.index', $aluno->nome ?? 'Aluno');

        return view('aluno.show', compact('aluno', 'vinculos', 'responsaveisOpcoes', 'tiposResponsavel', 'tiposDocumento', 'breadcrumb'))
            ->with('titulo', $aluno->nome ?? 'Aluno');
    }

    public function edit($id)
    {
        $aluno = $this->alunoService->getById($id, ['pessoa']);
        abort_if(! $aluno, 404, 'Aluno não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Alunos', 'aluno.index', 'Editar: '.($aluno->nome ?? 'Aluno'), 'Acadêmico');
        $opcoes = $this->opcoesFormulario();

        return view('aluno.add-edit', array_merge(compact('aluno', 'breadcrumb'), $opcoes))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateAlunoRequest $request, $id)
    {
        [$pessoaData, $alunoData] = $this->separarDados($request);

        $aluno = $this->alunoService->updateWithPessoa($id, $pessoaData, $alunoData);
        abort_if(! $aluno, 403, 'Sem acesso para editar este aluno.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aluno atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->alunoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este aluno.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function storeResponsavel(StoreVinculoRequest $request, $id)
    {
        $aluno = $this->alunoService->getById($id);
        abort_if(! $aluno, 404, 'Aluno não encontrado.');

        $this->alunoService->vincularResponsavel($aluno, $request->validated());

        return redirect()->route('aluno.show', $aluno->id)
            ->with('success', 'Responsável vinculado com sucesso.');
    }

    public function updateResponsavel(UpdateVinculoRequest $request, $id, $vinculoId)
    {
        $aluno = $this->alunoService->getById($id);
        abort_if(! $aluno, 404, 'Aluno não encontrado.');

        $ok = $this->alunoService->atualizarVinculo($aluno, (int) $vinculoId, $request->validated());
        abort_if(! $ok, 404, 'Vínculo não encontrado.');

        return redirect()->route('aluno.show', $aluno->id)
            ->with('success', 'Vínculo atualizado com sucesso.');
    }

    public function destroyResponsavel($id, $vinculoId)
    {
        $aluno = $this->alunoService->getById($id);
        abort_if(! $aluno, 404, 'Aluno não encontrado.');

        $ok = $this->alunoService->desvincularResponsavel($aluno, (int) $vinculoId);
        abort_if(! $ok, 404, 'Vínculo não encontrado.');

        return redirect()->route('aluno.show', $aluno->id)
            ->with('success', 'Vínculo removido com sucesso.');
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'aluno');
        session()->put("$this->nomeFiltro.rotaRedirect", 'aluno.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'registro_academico', 'label' => 'RA', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where', 'options' => $this->escolasOpcoesFiltro(),
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    /**
     * Separa o payload flat do form em dados de Pessoa e de Aluno.
     *
     * @return array{0: array, 1: array}
     */
    private function separarDados($request): array
    {
        $data = $request->validated();

        $pessoaData = [];
        foreach (StoreAlunoRequest::PESSOA_FIELDS as $campo) {
            if (array_key_exists($campo, $data)) {
                $pessoaData[$campo] = $data[$campo];
            }
        }

        $alunoData = [];
        foreach (StoreAlunoRequest::ALUNO_FIELDS as $campo) {
            if (array_key_exists($campo, $data)) {
                $alunoData[$campo] = $data[$campo];
            }
        }

        return [$pessoaData, $alunoData];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => $this->escolasOpcoes(),
            'turnos' => Aluno::TURNOS,
            'sexos' => \App\Models\Pessoa::SEXOS,
            'usuariosPortal' => $this->usuariosPortalOpcoes('aluno'),
        ];
    }

    /** Contas de usuario com o perfil informado (para vinculo de portal). */
    private function usuariosPortalOpcoes(string $perfil): array
    {
        $empresaId = auth()->user()?->empresa_id;

        $roleIds = \Illuminate\Support\Facades\DB::table('roles')
            ->where('empresa_id', $empresaId)
            ->where('name', $perfil)
            ->where('guard_name', 'web')
            ->pluck('id');

        return \App\Models\User::query()
            ->whereIn('role_id', $roleIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private function escolasOpcoes()
    {
        return Escola::query()->orderBy('nome')->pluck('nome', 'id')->all();
    }

    /**
     * Formato exigido pelo componente x-filter para selects:
     * lista de {value, label} (diferente do mapa id=>nome do x-form-select).
     */
    private function escolasOpcoesFiltro()
    {
        return Escola::query()->orderBy('nome')->get(['id', 'nome'])
            ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
            ->all();
    }

    private function responsaveisOpcoes()
    {
        return Responsavel::query()
            ->with('pessoa')
            ->where('ativo', 'S')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn (Responsavel $r) => [$r->id => $r->nome ?? ('#'.$r->id)])
            ->all();
    }
}
