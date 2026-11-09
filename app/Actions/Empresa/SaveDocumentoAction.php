<?php

namespace App\Actions\Empresa;

use App\Models\EmpresaDocumento;
use App\Support\EmpresaContext;
use App\Support\UploadStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveDocumentoAction
{
    public function handle(array $data, ?EmpresaDocumento $documento = null, ?UploadedFile $arquivo = null): EmpresaDocumento
    {
        if ($arquivo) {
            if ($documento?->arquivo_path) {
                Storage::delete($documento->arquivo_path);
            }
            $empresaId = $documento?->empresa_id ?? app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id;
            $data['arquivo_path'] = UploadStorage::putFile(
                $arquivo,
                "empresa_documentos/{$empresaId}",
                uniqid('doc-').'.'.$arquivo->getClientOriginalExtension()
            );
        }

        if ($documento) {
            $documento->update(array_merge($data, ['usuatu' => auth()->id(), 'datatu' => now()]));

            return $documento;
        }

        return EmpresaDocumento::create(array_merge($data, [
            'empresa_id' => app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id,
            'usuinc' => auth()->id(),
            'datainc' => now(),
        ]));
    }
}
