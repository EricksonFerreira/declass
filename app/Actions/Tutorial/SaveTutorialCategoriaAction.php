<?php

namespace App\Actions\Tutorial;

use App\Models\TutorialCategoria;

class SaveTutorialCategoriaAction
{
    public function handle(array $data, ?TutorialCategoria $categoria = null): TutorialCategoria
    {
        if ($categoria) {
            $categoria->update($data);

            return $categoria;
        }

        return TutorialCategoria::create($data);
    }
}
