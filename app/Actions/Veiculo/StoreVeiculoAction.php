<?php

namespace App\Actions\Veiculo;

use App\Models\GrupoFrota;
use App\Http\Requests\Veiculo\StoreVeiculoRequest;
use App\Support\EmpresaContext;
use App\Services\VeiculoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreVeiculoAction
{
    public function __construct(private readonly VeiculoService $veiculoService) {}

    public function handle(StoreVeiculoRequest $request, string $nomeFiltro)
    {
        $data = $request->validated();

        if (empty($data['grupo_frota_id']) && !empty($data['nome_frota'])) {
            $data['grupo_frota_id'] = $this->obterOuCriarGrupoFrota($data, $request);
        }

        unset($data['nome_frota']);

        $this->veiculoService->create($data);

        return redirect()->route($nomeFiltro . '.index');
    }

    private function obterOuCriarGrupoFrota(array $data, StoreVeiculoRequest $request): int
    {
        $empresaId = app(EmpresaContext::class)->empresaId()
            ?? Auth::user()?->empresa_id;

        $nome = trim((string) ($data['nome_frota'] ?? ''));
        $tipoFrotaId = (int) ($data['tipo_frota_id'] ?? 0);

        $grupoExistente = GrupoFrota::query()
            ->when($empresaId !== null, fn ($query) => $query->where('empresa_id', (int) $empresaId))
            ->whereRaw('LOWER(nome) = ?', [mb_strtolower($nome)])
            ->first();

        if ($grupoExistente) {
            return (int) $grupoExistente->id;
        }

        return DB::transaction(function () use ($empresaId, $nome, $tipoFrotaId) {
            $grupo = new GrupoFrota();
            if ($empresaId !== null) {
                $grupo->empresa_id = (int) $empresaId;
            }
            $grupo->nome = $nome;
            $grupo->codigo = 'GF-' . Str::upper(Str::slug($nome, ''));
            $grupo->codigo_sipp = 'AUTO';
            $grupo->tipo_frota_id = $tipoFrotaId ?: null;
            $grupo->valor_diario = 0;
            $grupo->ativo = 'S';
            $grupo->save();

            return (int) $grupo->id;
        });
    }
}
