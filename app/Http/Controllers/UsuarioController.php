<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Usuario\StoreUsuarioRequest;
use App\Http\Requests\Usuario\UpdatePasswordUsuarioRequest;
use App\Http\Requests\Usuario\UpdateUsuarioRequest;
use App\Models\Pais;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\PaisService;
use App\Services\RegraService;
use App\Services\UsuarioService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class UsuarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $usuarioService;
    protected $breadcrumbService;
    protected $regraService;
    protected $paisService;

    public function __construct(
        UsuarioService $usuarioService,
        BreadcrumbService $breadcrumbService,
        RegraService $regraService,
        PaisService $paisService
    ) {
        $this->titulo = "Usuários";
        $this->nomeFiltro = 'usuario';
        $this->usuarioService = $usuarioService;
        $this->breadcrumbService = $breadcrumbService;
        $this->regraService = $regraService;
        $this->paisService = $paisService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        // session()->forget("$this->nomeFiltro.filtro");
        $usuarios = $this->usuarioService->getAllWithFiltersAndPagination($itensFiltered, $perPage,['tipoUsuario']);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Usuarios');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('usuario.index', compact(
            'usuarios',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $sexo = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Usuarios', 'usuario.index', 'Criar');

        $paises = $this->paisService->getAllForSelect();
        $tiposUsuario = $this->regraService->getAllTiposUsuarios();
        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('usuario.add-edit', compact('breadcrumb', 'sexo', 'metodo', 'tiposUsuario','paises', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreUsuarioRequest $request)
    {
        $data = $request->except('confirmacao_senha');
        $this->usuarioService->createUser($data);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $usuario = $this->usuarioService->getById($id);
        $sexo = [
            'M' => 'Masculino',
            'F' => 'Feminino',
            'O' => 'Outro'
        ];

        $tiposUsuario = $this->regraService->getAllTiposUsuarios();

        $paises = $this->paisService->getAllForSelect();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuarios', 'usuario.index', "Editar: $usuario->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($usuario);

        return view('usuario.add-edit', compact('usuario', 'breadcrumb', 'tiposUsuario', 'sexo','paises', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $this->usuarioService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function alterarSenha($id)
    {
        $usuario = $this->usuarioService->getById($id);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Usuarios', 'usuario.index', "Alterar Senha: $usuario->nome");
        $metodo = 'PUT';
        $rota = route('usuario.salvarNovaSenha', $usuario->id);

        return view('usuario.alterar-senha', compact('usuario', 'breadcrumb', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function salvarNovaSenha(UpdatePasswordUsuarioRequest $request, $id)
    {
        $this->usuarioService->updatePassword($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->usuarioService->delete($id);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $atributo = $request->input('atributo') ?? null;
        $this->removeSessao($this->nomeFiltro, $atributo);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        // Salva o valor de paginação na sessão
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a página de listagem
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'usuario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'usuario.index');
    }

    public function obterFiltrosPrincipais()
    {
        $tiposUsuarios = $this->regraService->getAllTiposUsuarios();
        $dataTiposUsuarios = [];
        $dataTiposUsuarios[] = ['value' => '', 'label' => 'Selecione o status', 'selected' => false];

        foreach($tiposUsuarios as $key => $value) {
            $dataTiposUsuarios[] = ['value' => $key, 'label' => $value, 'selected' => false];
        }
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'celular', 'label' => 'Celular', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cidade', 'label' => 'Cidade', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'cidade', 'label' => 'Cidade', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'tipo_usuario_id', 'label' => 'Regra de Usuário', 'type' => 'select', 'typeWhere' => 'where', 'options' => $dataTiposUsuarios],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundários aqui, se necessário
        ];
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  User|null  $usuario
     * @return array
     */
    private function getFormMethodAndRoute($usuario = null)
    {
        if ($usuario) {
            return [
                'PUT',
                route('usuario.update', $usuario->id),
            ];
        }

        return [
            'POST',
            route('usuario.store'),
        ];
    }
}
