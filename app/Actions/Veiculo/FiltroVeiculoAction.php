<?php

namespace App\Actions\Veiculo;

use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class FiltroVeiculoAction
{
    use FiltroTrait;

    public function handle(Request $request, string $nomeFiltro): void
    {
        $this->criarFiltros($request, $nomeFiltro);
    }
}
