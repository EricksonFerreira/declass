<?php

namespace App\Http\Controllers;

use App\Actions\Empresa\DeleteDocumentoAction;
use App\Actions\Empresa\ListarDocumentosAction;
use App\Actions\Empresa\SaveDocumentoAction;
use App\Models\EmpresaDocumento;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmpresaDocumentoController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'documentos';

    private const REGRAS = [
        'tipo' => 'required|string|max:60',
        'numero' => 'nullable|string|max:100',
        'orgao_emissor' => 'nullable|string|max:255',
        'descricao' => 'nullable|string|max:2000',
        'valor' => 'nullable|numeric|min:0',
        'data_emissao' => 'nullable|date',
        'data_validade' => 'nullable|date|after_or_equal:data_emissao',
        'arquivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
    ];

    public function index(ListarDocumentosAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $documentos = $action->handle($itensFiltered);

        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        foreach ($itensFiltered as &$item) {
            if (($item['name'] ?? '') === 'tipo') {
                $item['valor'] = EmpresaDocumento::TIPOS[$item['valor']] ?? $item['valor'];
            }
            if (($item['name'] ?? '') === 'situacao') {
                $item['valor'] = EmpresaDocumento::SITUACOES[$item['valor']] ?? $item['valor'];
            }
        }
        unset($item);

        return view('empresa.documentos', [
            'titulo' => 'Documentos da Empresa',
            'documentos' => $documentos,
            'tipos' => EmpresaDocumento::TIPOS,
            'situacoes' => EmpresaDocumento::SITUACOES,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $this->nomeFiltro,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Documentos da Empresa', 'active' => true],
            ],
        ]);
    }

    public function filtro(Request $request): RedirectResponse
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request): RedirectResponse
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    private function configurarSessao(): void
    {
        session()->put("{$this->nomeFiltro}.urlPadrao", 'documentos');
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'documentos.index');
    }

    private function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'typeWhere' => 'where', 'options' => array_merge(
                [['value' => '', 'label' => 'Todos', 'selected' => false]],
                collect(EmpresaDocumento::TIPOS)->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])->values()->all()
            )],
        ];
    }

    private function obterFiltrosSecundarios(): array
    {
        return [
            ['name' => 'numero', 'label' => 'Número', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'orgao_emissor', 'label' => 'Emissor', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'situacao', 'label' => 'Situação', 'type' => 'select', 'typeWhere' => 'where', 'options' => array_merge(
                [['value' => '', 'label' => 'Todas', 'selected' => false]],
                collect(EmpresaDocumento::SITUACOES)->map(fn ($label, $v) => ['value' => $v, 'label' => $label, 'selected' => false])->values()->all()
            )],
        ];
    }

    public function store(Request $request, SaveDocumentoAction $action): RedirectResponse
    {
        $action->handle(
            $request->validate(self::REGRAS),
            arquivo: $request->file('arquivo')
        );

        return redirect()->route('documentos.index')->with('success', 'Documento cadastrado.');
    }

    public function update(Request $request, EmpresaDocumento $documento, SaveDocumentoAction $action): RedirectResponse
    {
        $action->handle(
            $request->validate(self::REGRAS),
            $documento,
            $request->file('arquivo')
        );

        return redirect()->route('documentos.index')->with('success', 'Documento atualizado.');
    }

    public function download(EmpresaDocumento $documento): StreamedResponse
    {
        abort_unless($documento->arquivo_path && Storage::exists($documento->arquivo_path), 404);

        return Storage::download(
            $documento->arquivo_path,
            $documento->tipo.'-'.$documento->id.'.'.pathinfo($documento->arquivo_path, PATHINFO_EXTENSION)
        );
    }

    public function destroy(EmpresaDocumento $documento, DeleteDocumentoAction $action): RedirectResponse
    {
        $action->handle($documento);

        return redirect()->route('documentos.index')->with('success', 'Documento removido.');
    }
}
