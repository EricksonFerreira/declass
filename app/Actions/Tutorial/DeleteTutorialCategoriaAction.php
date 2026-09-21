<?php

namespace App\Actions\Tutorial;

use App\Models\TutorialCategoria;

class DeleteTutorialCategoriaAction
{
    public function handle(TutorialCategoria $categoria): void
    {
        // FK nullOnDelete mantem os tutoriais, apenas desvinculando-os
        $categoria->delete();
    }
}
