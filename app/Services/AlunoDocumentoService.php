<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\AlunoDocumento;
use App\Support\UploadStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AlunoDocumentoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(AlunoDocumento $documento)
    {
        $this->model = $documento;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof AlunoDocumento) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\AlunoDocumento');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /** Garante que o documento pertence ao aluno da rota. */
    public function pertenceAoAluno(AlunoDocumento $documento, Aluno $aluno): bool
    {
        return (int) $documento->aluno_id === (int) $aluno->id;
    }

    public function createParaAluno(Aluno $aluno, array $data, ?UploadedFile $arquivo): AlunoDocumento
    {
        if ($arquivo) {
            $data['arquivo_path'] = $this->guardarArquivo($aluno, $arquivo);
        }

        return $aluno->documentos()->create($data);
    }

    public function updateDocumento(AlunoDocumento $documento, array $data, ?UploadedFile $arquivo): AlunoDocumento
    {
        if ($arquivo) {
            if ($documento->arquivo_path) {
                Storage::delete($documento->arquivo_path);
            }
            $data['arquivo_path'] = $this->guardarArquivo($documento->aluno, $arquivo);
        }

        $documento->update($data);

        return $documento;
    }

    public function deleteDocumento(AlunoDocumento $documento): bool
    {
        if ($documento->arquivo_path) {
            Storage::delete($documento->arquivo_path);
        }

        return (bool) $documento->delete();
    }

    private function guardarArquivo(Aluno $aluno, UploadedFile $arquivo): ?string
    {
        return UploadStorage::putFile(
            $arquivo,
            "aluno_documentos/{$aluno->empresa_id}/{$aluno->id}",
            uniqid('doc-').'.'.$arquivo->getClientOriginalExtension()
        );
    }
}
