<?php

namespace App\Services;

use App\Models\AnoLetivo;
use App\Models\PeriodoLetivo;
use Illuminate\Database\Eloquent\Model;

class PeriodoLetivoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(PeriodoLetivo $periodo)
    {
        $this->model = $periodo;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof PeriodoLetivo) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\PeriodoLetivo');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        // escola_id e derivado do ano letivo — o formulario so
        // envia ano_letivo_id.
        $anoLetivo = AnoLetivo::query()->find($data['ano_letivo_id'] ?? 0);
        if ($anoLetivo) {
            $data['escola_id'] = $anoLetivo->escola_id;
        }

        return parent::create($data);
    }

    /**
     * Nomes padrao dos periodos por periodicidade do ano letivo.
     */
    private const NOMES_POR_PERIODICIDADE = [
        'bimestral' => ['1º Bimestre', '2º Bimestre', '3º Bimestre', '4º Bimestre'],
        'trimestral' => ['1º Trimestre', '2º Trimestre', '3º Trimestre'],
        'semestral' => ['1º Semestre', '2º Semestre'],
        'anual' => ['Ano Letivo'],
    ];

    /**
     * Gera os periodos letivos do ano conforme sua periodicidade.
     * So roda quando o ano ainda nao possui periodos — nunca apaga
     * nem duplica registros existentes (avaliacoes podem referencia-los).
     * As datas sao divididas proporcionalmente entre inicio e fim do
     * ano letivo; sem datas informadas, os periodos ficam sem datas.
     *
     * @return int quantidade de periodos criados (0 = nada a gerar)
     */
    public function gerarAutomaticos(AnoLetivo $anoLetivo): int
    {
        $nomes = self::NOMES_POR_PERIODICIDADE[$anoLetivo->periodicidade] ?? null;

        if ($nomes === null || $anoLetivo->periodos()->exists()) {
            return 0;
        }

        $quantidade = count($nomes);
        $inicio = $anoLetivo->data_inicio;
        $fim = $anoLetivo->data_fim;
        $temDatas = $inicio !== null && $fim !== null && $inicio->lt($fim);
        $totalDias = $temDatas ? (int) $inicio->diffInDays($fim) + 1 : 0;

        foreach ($nomes as $i => $nome) {
            $data = [
                'empresa_id' => $anoLetivo->empresa_id,
                'escola_id' => $anoLetivo->escola_id,
                'ano_letivo_id' => $anoLetivo->id,
                'nome' => $nome,
                'ordem' => $i + 1,
                'ativo' => 'S',
            ];

            if ($temDatas) {
                $dataInicio = $inicio->copy()->addDays((int) round($totalDias * $i / $quantidade));
                $dataFim = $i === $quantidade - 1
                    ? $fim->copy()
                    : $inicio->copy()->addDays((int) round($totalDias * ($i + 1) / $quantidade) - 1);

                $data['data_inicio'] = $dataInicio->toDateString();
                $data['data_fim'] = $dataFim->toDateString();
            }

            PeriodoLetivo::create($data);
        }

        return $quantidade;
    }
}
