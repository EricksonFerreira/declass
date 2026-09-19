<?php

namespace App\Http\Controllers;

use App\Actions\Empresa\DeleteDocumentoAction;
use App\Actions\Empresa\SaveDocumentoAction;
use App\Models\EmpresaDocumento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmpresaDocumentoController extends Controller
{
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

    public function index(): View
    {
        return view('empresa.documentos', [
            'titulo' => 'Documentos da Empresa',
            'documentos' => EmpresaDocumento::orderBy('tipo')->orderByDesc('data_emissao')->paginate(20),
            'tipos' => EmpresaDocumento::TIPOS,
            'situacoes' => EmpresaDocumento::SITUACOES,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Documentos da Empresa', 'active' => true],
            ],
        ]);
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
