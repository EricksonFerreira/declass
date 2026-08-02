<?php

namespace App\Actions\Veiculo;

use App\Http\Requests\Veiculo\UpdateVeiculoRequest;
use App\Services\VeiculoService;

class UpdateVeiculoAction
{
    public function __construct(private readonly VeiculoService $veiculoService) {}

    public function handle(UpdateVeiculoRequest $request, $id, string $nomeFiltro)
    {
        $this->veiculoService->update($id, $request->all());

        return redirect()->route($nomeFiltro.'.index');
    }
}
