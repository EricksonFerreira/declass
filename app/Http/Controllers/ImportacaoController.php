<?php

namespace App\Http\Controllers;

use App\Actions\Importacao\ImportarProjetosAction;
use App\Actions\Importacao\ImportarUsuariosAction;
use App\Support\EmpresaContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Importacao em massa de colaboradores e projetos via CSV.
 * Fluxo: upload → preview (validacao por linha) → confirmar → resumo.
 */
class ImportacaoController extends Controller
{
    private const TIPOS = ['usuarios', 'projetos'];

    public function index(): View
    {
        return view('importacao.index', [
            'titulo' => 'Importar dados',
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Importar dados', 'active' => true],
            ],
        ]);
    }

    public function preview(Request $request, string $tipo)
    {
        abort_unless(in_array($tipo, self::TIPOS, true), 404);

        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:5120',
            'senha_inicial' => $tipo === 'usuarios' ? 'required|string|min:6' : 'nullable',
        ]);

        $token = (string) str()->uuid();
        $request->file('arquivo')->storeAs('imports', $token.'.csv');

        try {
            $analise = $this->acao($tipo)->analisar($this->caminho($token), $this->empresaId());
        } catch (\RuntimeException $e) {
            Storage::delete('imports/'.$token.'.csv');

            return redirect()->route('importacao.index')->with('error', $e->getMessage());
        }

        return view('importacao.preview', [
            'titulo' => 'Importar '.$tipo.' — conferir antes de gravar',
            'tipo' => $tipo,
            'token' => $token,
            'linhas' => $analise,
            'senhaInicial' => $request->input('senha_inicial'),
            'arquivoNome' => $request->file('arquivo')->getClientOriginalName(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Importar dados', 'url' => route('importacao.index')],
                ['name' => 'Prévia', 'active' => true],
            ],
        ]);
    }

    public function importar(Request $request, string $tipo): RedirectResponse
    {
        abort_unless(in_array($tipo, self::TIPOS, true), 404);

        $request->validate([
            'token' => 'required|string|size:36',
            'senha_inicial' => $tipo === 'usuarios' ? 'required|string|min:6' : 'nullable',
        ]);

        $token = $request->input('token');
        if (! Storage::exists('imports/'.$token.'.csv')) {
            return redirect()->route('importacao.index')
                ->with('error', 'Arquivo da prévia expirou. Envie o CSV novamente.');
        }

        try {
            $resumo = $tipo === 'usuarios'
                ? $this->acao($tipo)->handle($this->caminho($token), $this->empresaId(), (string) $request->input('senha_inicial'))
                : $this->acao($tipo)->handle($this->caminho($token), $this->empresaId(), (int) $request->user()->id);
        } finally {
            Storage::delete('imports/'.$token.'.csv');
        }

        return redirect()->route('importacao.index')->with('importacao_resumo', [
            'tipo' => $tipo,
            'resumo' => $resumo,
        ]);
    }

    private function acao(string $tipo): ImportarUsuariosAction|ImportarProjetosAction
    {
        return $tipo === 'usuarios'
            ? app(ImportarUsuariosAction::class)
            : app(ImportarProjetosAction::class);
    }

    private function caminho(string $token): string
    {
        return Storage::path('imports/'.$token.'.csv');
    }

    private function empresaId(): int
    {
        return (int) (app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id);
    }
}
