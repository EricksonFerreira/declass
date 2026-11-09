<?php

namespace App\Http\Controllers;

use App\Http\Requests\SolicitacaoTipo\StoreTipoSolicitacaoRequest;
use App\Http\Requests\SolicitacaoTipo\UpdateTipoSolicitacaoRequest;
use App\Models\Escola;
use App\Models\SolicitacaoTipo;
use App\Services\BreadcrumbService;
use Illuminate\Http\Request;

/**
 * Tipos de requerimento que a familia escolhe no portal:
 * cada tipo pode exigir anexo e definir campos extras de
 * preenchimento (texto, numero, data, lista de opcoes).
 */
class TipoSolicitacaoController extends BaseController
{
    public function __construct(
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Tipos de Solicitação';
        $this->nomeFiltro = 'tipo_solicitacao';

        $this->middleware('can:tipo_solicitacao.index')->only('index');
        $this->middleware('can:tipo_solicitacao.create')->only(['create', 'store']);
        $this->middleware('can:tipo_solicitacao.edit')->only(['edit', 'update']);
        $this->middleware('can:tipo_solicitacao.destroy')->only('destroy');
    }

    public function index()
    {
        $tipos = SolicitacaoTipo::query()
            ->with('escola:id,nome')
            ->withCount('solicitacoes')
            ->orderBy('nome')
            ->paginate(20);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tipos de Solicitação', 'Alunos e Família');

        return view('tipo_solicitacao.index', [
            'tipos' => $tipos,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate(
            'Tipos de Solicitação', 'tipo_solicitacao.index', 'Novo Tipo', 'Alunos e Família'
        );

        return view('tipo_solicitacao.add-edit', $this->opcoesFormulario() + compact('breadcrumb'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreTipoSolicitacaoRequest $request)
    {
        SolicitacaoTipo::create($this->dadosNormalizados($request));

        return redirect()->route('tipo_solicitacao.index')
            ->with('success', 'Tipo de solicitação cadastrado.');
    }

    public function edit($id)
    {
        $tipo = SolicitacaoTipo::findOrFail($id);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Tipos de Solicitação', 'tipo_solicitacao.index', "Editar: {$tipo->nome}", 'Alunos e Família'
        );

        return view('tipo_solicitacao.add-edit', $this->opcoesFormulario() + compact('tipo', 'breadcrumb'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateTipoSolicitacaoRequest $request, $id)
    {
        $tipo = SolicitacaoTipo::findOrFail($id);
        $tipo->update($this->dadosNormalizados($request));

        return redirect()->route('tipo_solicitacao.index')
            ->with('success', 'Tipo de solicitação atualizado.');
    }

    public function destroy($id)
    {
        SolicitacaoTipo::findOrFail($id)->delete();

        return redirect()->route('tipo_solicitacao.index')
            ->with('success', 'Tipo de solicitação removido.');
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'tiposCampo' => SolicitacaoTipo::CAMPO_TIPOS,
        ];
    }

    /**
     * Converte o formulario (S/N, opcoes em texto) na estrutura
     * normalizada salva em campos JSON.
     */
    private function dadosNormalizados(Request $request): array
    {
        $dados = $request->validated();

        $campos = collect($dados['campos'] ?? [])
            ->filter(fn ($campo) => filled($campo['rotulo'] ?? null))
            ->map(fn ($campo) => [
                'rotulo' => trim($campo['rotulo']),
                'tipo' => $campo['tipo'],
                'obrigatoria' => ($campo['obrigatoria'] ?? 'N') === 'S',
                'opcoes' => ($campo['tipo'] ?? '') === 'selecao'
                    ? array_values(array_filter(array_map('trim', explode(',', (string) ($campo['opcoes'] ?? '')))))
                    : [],
            ])
            ->values()
            ->all();

        return [
            'escola_id' => $dados['escola_id'] ?? null,
            'nome' => $dados['nome'],
            'descricao' => $dados['descricao'] ?? null,
            'exige_anexo' => ($dados['exige_anexo'] ?? 'N') === 'S',
            'ativo' => ($dados['ativo'] ?? 'S') === 'S',
            'campos' => $campos ?: null,
        ];
    }
}
