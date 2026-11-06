<?php

namespace App\Http\Controllers;

use App\Http\Requests\Aluno\StoreAlunoDocumentoRequest;
use App\Http\Requests\Aluno\UpdateAlunoDocumentoRequest;
use App\Models\Aluno;
use App\Models\AlunoDocumento;
use App\Services\AlunoDocumentoService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlunoDocumentoController extends BaseController
{
    public function __construct(
        protected AlunoDocumentoService $documentoService,
    ) {
        $this->middleware('can:aluno.documento.store')->only('store');
        $this->middleware('can:aluno.documento.update')->only('update');
        $this->middleware('can:aluno.documento.destroy')->only('destroy');
        $this->middleware('can:aluno.documento.arquivo')->only('arquivo');
    }

    public function store(StoreAlunoDocumentoRequest $request, $alunoId)
    {
        $aluno = Aluno::query()->findOrFail($alunoId);

        $this->documentoService->createParaAluno(
            $aluno,
            $request->safe()->except('arquivo'),
            $request->file('arquivo')
        );

        return redirect()->route('aluno.show', $aluno->id)
            ->with('success', 'Documento anexado com sucesso.');
    }

    public function update(UpdateAlunoDocumentoRequest $request, $alunoId, $documentoId)
    {
        [$aluno, $documento] = $this->resolver($alunoId, $documentoId);

        $this->documentoService->updateDocumento(
            $documento,
            $request->safe()->except('arquivo'),
            $request->file('arquivo')
        );

        return redirect()->route('aluno.show', $aluno->id)
            ->with('success', 'Documento atualizado com sucesso.');
    }

    public function destroy($alunoId, $documentoId)
    {
        [$aluno, $documento] = $this->resolver($alunoId, $documentoId);

        $this->documentoService->deleteDocumento($documento);

        return redirect()->route('aluno.show', $aluno->id)
            ->with('success', 'Documento removido com sucesso.');
    }

    public function arquivo($alunoId, $documentoId): StreamedResponse
    {
        [, $documento] = $this->resolver($alunoId, $documentoId);

        abort_unless($documento->arquivo_path && Storage::exists($documento->arquivo_path), 404);

        return Storage::download(
            $documento->arquivo_path,
            \Illuminate\Support\Str::slug($documento->titulo).'.'.pathinfo($documento->arquivo_path, PATHINFO_EXTENSION)
        );
    }

    /** @return array{0: Aluno, 1: AlunoDocumento} */
    private function resolver($alunoId, $documentoId): array
    {
        $aluno = Aluno::query()->findOrFail($alunoId);
        $documento = AlunoDocumento::query()->findOrFail($documentoId);

        abort_unless($this->documentoService->pertenceAoAluno($documento, $aluno), 404);

        return [$aluno, $documento];
    }
}
