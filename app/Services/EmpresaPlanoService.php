<?php

namespace App\Services;

class EmpresaPlanoService
{
    public function contratar($empresa, $plano, $metodo)
    {
        return new \App\Models\EmpresaPlano();
    }
}
