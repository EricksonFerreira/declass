<?php

namespace App\Actions\Tutorial;

use App\Models\Tutorial;
use Illuminate\Support\Facades\Storage;

class DeleteTutorialAction
{
    public function handle(Tutorial $tutorial): void
    {
        foreach ([$tutorial->video_path, $tutorial->capa_path] as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $tutorial->delete();
    }
}
