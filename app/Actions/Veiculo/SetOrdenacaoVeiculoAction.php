<?php

namespace App\Actions\Veiculo;

use Illuminate\Http\Request;

class SetOrdenacaoVeiculoAction
{
    public function handle(Request $request, string $nomeFiltro)
    {
        $campo = $request->input('orderBy', 'id');
        $dir = $request->input('orderDir', 'asc');
        if (! in_array($campo, ['id', 'nome', 'placa_licenca', 'renavam', 'ano_fabricacao', 'kms', 'created_at'])) $campo = 'id';
        if (! in_array($dir, ['asc', 'desc'])) $dir = 'asc';
        session(["{$nomeFiltro}.orderBy" => $campo, "{$nomeFiltro}.orderDir" => $dir]);
        return redirect()->route($nomeFiltro.'.index');
    }
}
