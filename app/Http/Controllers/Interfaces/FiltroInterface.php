<?php

namespace App\Http\Controllers\Interfaces;

interface FiltroInterface
{
    public function configurarSessao();

    public function obterFiltrosPrincipais();

    public function obterFiltrosSecundarios();
}
