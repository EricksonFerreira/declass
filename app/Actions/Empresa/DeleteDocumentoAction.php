<?php

namespace App\Actions\Empresa;

use App\Models\EmpresaDocumento;
use Illuminate\Support\Facades\Storage;

class DeleteDocumentoAction
{
    public function handle(EmpresaDocumento $documento): void
    {
        if ($documento->arquivo_path) {
            Storage::delete($documento->arquivo_path);
        }

        $documento->delete();
    }
}
