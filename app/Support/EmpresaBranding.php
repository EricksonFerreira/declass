<?php

namespace App\Support;

use App\Models\Empresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmpresaBranding
{
    public function empresaAtual(): ?Empresa
    {
        $empresaId = (int) session('empresa_context_id', 0);

        if ($empresaId <= 0) {
            $empresaId = (int) (Auth::user()?->empresa_id ?? 0);
        }

        if ($empresaId <= 0) {
            return null;
        }

        return Empresa::query()
            ->select(['id', 'nome', 'logo_path'])
            ->find($empresaId);
    }

    public function logoUrl(?Empresa $empresa = null): ?string
    {
        $empresa ??= $this->empresaAtual();

        $logoPath = is_string($empresa?->logo_path ?? null) ? trim((string) $empresa->logo_path) : '';
        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            return Storage::url($logoPath);
        }

        return asset('assets/imgs/logo-sem-texto.png');
    }

    public function logoDataUri(?Empresa $empresa = null): ?string
    {
        $empresa ??= $this->empresaAtual();

        $logoPath = is_string($empresa?->logo_path ?? null) ? trim((string) $empresa->logo_path) : '';
        if ($logoPath !== '' && Storage::disk('public')->exists($logoPath)) {
            $disk = Storage::disk('public');
            $mimeType = $disk->mimeType($logoPath) ?: 'image/png';

            return 'data:' . $mimeType . ';base64,' . base64_encode($disk->get($logoPath));
        }

        $fallbackPath = public_path('assets/imgs/logo-sem-texto.png');
        if (is_file($fallbackPath)) {
            $mimeType = mime_content_type($fallbackPath) ?: 'image/png';

            return 'data:' . $mimeType . ';base64,' . base64_encode((string) file_get_contents($fallbackPath));
        }

        return null;
    }
}
