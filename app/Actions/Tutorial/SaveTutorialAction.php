<?php

namespace App\Actions\Tutorial;

use App\Models\Tutorial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveTutorialAction
{
    public function handle(
        array $data,
        ?Tutorial $tutorial = null,
        ?UploadedFile $video = null,
        ?UploadedFile $capa = null
    ): Tutorial {
        if ($video) {
            $this->removerArquivo($tutorial?->video_path);
            $data['video_path'] = $video->storeAs(
                'tutoriais/videos',
                uniqid('video-').'.'.$video->getClientOriginalExtension(),
                'public'
            );
            $data['video_url'] = null;
        } elseif (($data['video_tipo'] ?? 'link') === 'link' && ! empty($data['video_url'])) {
            // Link substitui o arquivo anteriormente enviado
            if ($tutorial?->video_path) {
                $this->removerArquivo($tutorial->video_path);
            }
            $data['video_path'] = null;
        }
        unset($data['video_tipo']);

        if ($capa) {
            $this->removerArquivo($tutorial?->capa_path);
            $data['capa_path'] = $capa->storeAs(
                'tutoriais/capas',
                uniqid('capa-').'.'.$capa->getClientOriginalExtension(),
                'public'
            );
        }

        if ($tutorial) {
            $tutorial->update($data);

            return $tutorial;
        }

        return Tutorial::create($data);
    }

    private function removerArquivo(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
