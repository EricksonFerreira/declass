<?php

namespace App\Services;

use App\Models\Estacao;
use App\Models\Horario;
use App\Models\HorarioDiario;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\DB;

class EstacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Estacao $estacao)
    {
        $this->model = $estacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Estacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Estacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function salvarHorariosDiarios($estacao, $horariosDiarios)
    {
        $estacao->horariosDiarios()->sync($horariosDiarios);
    }

    /**
     * Create a new Estacao and attach active HorarioDiario.
     *
     * @override
     * @param array $data
     * @return \App\Models\Estacao
     */
    public function create(array $data)
    {
        $estacao = $this->model->create($data);

        $this->adicionarHorarios($data, $estacao->id);
        $this->sincronizarDiasUteis($data['diasUteis'] ?? [], $estacao->id);
        return $estacao;
    }

    /**
     * Update Estacao and attach active HorarioDiario.
     *
     * @override
     * @param $id
     * @param array $data
     */
    public function update($id, array $data)
    {
        $estacao = $this->model->find($id);
        if ($estacao) {
            $estacao->update($data);
        }
        $this->adicionarHorarios($data, $id);
        $this->sincronizarDiasUteis($data['diasUteis'] ?? [], $id);
    }

    public function delete($id)
    {
        $this->ensureEmpresaSelectedForWrite();

        $estacao = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))->find($id);
        if (!$estacao) {
            return false;
        }

        return DB::transaction(function () use ($estacao, $id) {
            if (SchemaCache::hasTable('horarios')) {
                Horario::where('estacao_id', $id)->delete();
            }

            if (SchemaCache::hasTable('estacoes_dias_uteis')) {
                DB::table('estacoes_dias_uteis')->where('estacao_id', $id)->delete();
            }

            return (bool) $estacao->delete();
        });
    }


    public function salvarNovosHorariosDiarios($estacao)
    {
        $horarios_diarios = HorarioDiario::where('ativo', 'S')->get();

        $horariosSyncData = [];
        foreach ($horarios_diarios as $horario_diario) {
            $horariosSyncData[$horario_diario->id] = ['created_at' => Carbon::now()];
        }

        $estacao->horarios()->sync($horariosSyncData);
    }

    public function sincronizarDiasUteis($diasUteis, $estacaoId): void
    {
        $estacao = Estacao::find($estacaoId);

        if (!$estacao) {
            throw new \Exception("Estação não encontrada");
        }

        $diasUteisIds = collect(is_array($diasUteis) ? $diasUteis : [$diasUteis])
            ->filter(fn ($valor) => $valor !== null && $valor !== '')
            ->map(fn ($valor) => (int) $valor)
            ->unique()
            ->values()
            ->all();

        $estacao->diasUteis()->sync($diasUteisIds);
    }


    public function adicionaDiasUteis($diasUteis, $estacaoId)
    {
        // Encontrar a estação pelo ID
        $estacao = Estacao::find($estacaoId);

        // Deletar todos os dias úteis da estação
        $estacao->diasUteis()->detach();

        if ($estacao) {
            // Adicionar os dias úteis na tabela pivot
            $estacao->diasUteis()->attach($diasUteis);
        } else {
            // Lidar com o caso em que a estação não é encontrada
            throw new \Exception("Estação não encontrada");
        }
    }

    public function adicionarHorarios($horarios, $estacaoId)
    {
        DB::beginTransaction();

        try {
            // Deletar todos os horários da estação
            Horario::where('estacao_id', $estacaoId)->delete();

            // Iterar sobre cada índice do array de horários
            for ($i = 0; $i < count($horarios['hora_abertura']); $i++) {
                // Filtrar os horários para incluir apenas as chaves desejadas para o índice atual
                $funciona24h = isset($horarios['funciona_24h'][$i]) ? $horarios['funciona_24h'][$i] : 'N';
                $funciona = isset($horarios['funciona'][$i]) ? $horarios['funciona'][$i] : 'S';
                $temIntervaloAlmoco = isset($horarios['tem_intervalo_almoco'][$i]) ? $horarios['tem_intervalo_almoco'][$i] : 'S';

                if ($funciona === 'N') {
                    $abertura  = '00:00';
                    $fechamento= '00:00';
                    $iniAlmoco = '00:00';
                    $fimAlmoco = '00:00';
                    $iniForaEx = '00:00';
                    $fimForaEx = '00:00';
                    $funciona24h = 'N';
                    $temIntervaloAlmoco = 'N';
                } elseif ($funciona24h === 'S') {
                    $abertura  = '00:00';
                    $fechamento= '23:59';
                    $iniAlmoco = '00:00';
                    $fimAlmoco = '00:00';
                    $iniForaEx = '00:00';
                    $fimForaEx = '00:00';
                } else {
                    $abertura  = $horarios["hora_abertura"][$i];
                    $fechamento= $horarios["hora_fechamento"][$i];
                    $iniAlmoco = $temIntervaloAlmoco === 'S' ? $horarios["hora_inicio_almoco"][$i] : '00:00';
                    $fimAlmoco = $temIntervaloAlmoco === 'S' ? $horarios["hora_fim_almoco"][$i] : '00:00';
                    $iniForaEx = $horarios["hora_inicio_fora_expediente"][$i];
                    $fimForaEx = $horarios["hora_fim_fora_expediente"][$i];
                }

                $horarioDiarioId = $horarios['horario_diario_id'][$i] ?? null;
                if ($horarioDiarioId === null || $horarioDiarioId === '') {
                    throw new \Exception('Horario diario invalido ou ausente ao salvar a estacao.');
                }

                $horariosFiltrados = [
                    "estacao_id"                  => $estacaoId,
                    "horario_diario_id"           => (int) $horarioDiarioId,
                    "funciona_24h"                => $funciona24h,
                    "funciona"                    => $funciona,
                    "tem_intervalo_almoco"        => $temIntervaloAlmoco,
                    "hora_abertura"               => $abertura,
                    "hora_fechamento"             => $fechamento,
                    "hora_inicio_almoco"          => $iniAlmoco,
                    "hora_fim_almoco"             => $fimAlmoco,
                    "hora_inicio_fora_expediente" => $iniForaEx,
                    "hora_fim_fora_expediente"    => $fimForaEx,
                ];

                // Inserir os horários na tabela 'horarios'
                Horario::create($horariosFiltrados);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function  getEstacoesDisponiveis($estacaoOrigem)
    {
        $estacoes = $this->model->where('id', '!=', $estacaoOrigem)->get();
        return $estacoes;
    }

    public function getEstacoesParaRetiradaSelect(): array
    {
        return $this->getEstacoesPorFlagOperacao('checkin');
    }

    public function getEstacoesParaDevolucaoSelect(): array
    {
        return $this->getEstacoesPorFlagOperacao('checkout');
    }

    private function getEstacoesPorFlagOperacao(string $flag): array
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->select(['id', 'nome'])
            ->distinct();

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }

        if (SchemaCache::hasColumn($this->model->getTable(), $flag)) {
            $query->where($flag, 'S');
        }

        $query->orderBy('nome', 'asc');

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            return [];
        }

        $data = $query->distinct()->where('empresa_id', $empresaId)->get();

        return $this->formatArraySelect($data, 'id', 'nome');
    }

    /**
     * Lista estações ativas em formato de coleção (para modais/listagens).
     * Diferente de getAllAtivos(), que retorna array id => nome (para selects).
     */
    public function listarAtivasParaModal(array $columns = ['id', 'nome', 'cidade']): Collection
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->select($columns)
            ->distinct();

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }

        if (in_array('nome', $columns, true)) {
            $query->orderBy('nome', 'asc');
        }

        return $query->get();
    }


}
