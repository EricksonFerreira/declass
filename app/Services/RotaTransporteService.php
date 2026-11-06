<?php

namespace App\Services;

use App\Models\ParadaTransporte;
use App\Models\RotaTransporte;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RotaTransporteService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(RotaTransporte $rota)
    {
        $this->model = $rota;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof RotaTransporte) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\RotaTransporte');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $paradas = $data['paradas'] ?? null;
        unset($data['paradas']);

        return DB::transaction(function () use ($data, $paradas) {
            $rota = parent::create($data);
            $this->sincronizarParadas($rota, $paradas ?? []);

            return $rota;
        });
    }

    public function update($id, array $data)
    {
        $rota = $this->getById($id);
        abort_if(! $rota, 403, 'Sem acesso a esta rota.');

        $paradas = $data['paradas'] ?? null;
        unset($data['paradas']);

        return DB::transaction(function () use ($rota, $data, $paradas) {
            $rota = parent::update($rota->id, $data);
            $this->sincronizarParadas($rota, $paradas ?? []);

            return $rota;
        });
    }

    /**
     * Sincroniza as paradas da rota: atualiza as existentes,
     * cria as novas e remove as que sairam do formulario.
     */
    private function sincronizarParadas(RotaTransporte $rota, array $paradas): void
    {
        $manterIds = [];

        foreach ($paradas as $i => $parada) {
            if (empty($parada['nome'])) {
                continue;
            }

            $dados = [
                'empresa_id' => $rota->empresa_id,
                'rota_id' => $rota->id,
                'nome' => $parada['nome'],
                'ordem' => $parada['ordem'] ?? ($i + 1),
                'horario_previsto' => $parada['horario_previsto'] ?? null,
            ];

            if (! empty($parada['id'])) {
                ParadaTransporte::query()
                    ->where('id', $parada['id'])
                    ->where('rota_id', $rota->id)
                    ->update($dados);
                $manterIds[] = (int) $parada['id'];
            } else {
                $manterIds[] = ParadaTransporte::create($dados)->id;
            }
        }

        ParadaTransporte::query()
            ->where('rota_id', $rota->id)
            ->whereNotIn('id', $manterIds)
            ->delete();
    }
}
