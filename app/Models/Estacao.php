<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

class Estacao extends Model
{
    use HasFactory;

    protected $table = 'estacoes';

    protected $fillable = [
        'codigo',
        'nome',
        'email',
        'telefone',
        'celular',
        'cidade',
        'nfse_municipio_codigo',
        'nfse_inscricao_municipal',
        'numero',
        'zip_code',
        'latitude',
        'endereco',
        'pais_id',
        'longitude',
        'ativo',
        'checkin',
        'checkout',
        'cobrar_na_coleta',
        'valor_taxa_coleta',
        'cobrar_na_entrega',
        'valor_taxa_entrega',
        'valido_no_intervalo',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }

    public function horarios()
    {
        return $this->belongsToMany(HorarioDiario::class, 'horarios', 'estacao_id', 'horario_diario_id')
            ->withPivot([
                'horario_diario_id',
                'hora_abertura',
                'hora_fechamento',
                'hora_inicio_almoco',
                'hora_fim_almoco',
                'hora_inicio_fora_expediente',
                'hora_fim_fora_expediente',
                'funciona_24h',
                'funciona',
                'tem_intervalo_almoco',
            ]);
    }

    public function diasUteis()
    {
        return $this->belongsToMany(DiaUtil::class, 'estacoes_dias_uteis', 'estacao_id', 'dia_util_id');
    }

    public function veiculos()
    {
        return $this->hasMany(Veiculo::class, 'local_disponibilidade');
    }

    public function getHorariosComPivot()
    {
        return $this->horarios->map(function ($horario) {
            foreach ($horario->pivot->getAttributes() as $key => $value) {
                $horario->$key = $value;
            }

            return $horario;
        });
    }

    public function reservasRetirada()
    {
        return $this->hasMany(Reserva::class, 'estacao_retirada_id');
    }

    public function reservasDevolucao()
    {
        return $this->hasMany(Reserva::class, 'estacao_devolucao_id');
    }

    public function temposOrigem()
    {
        return $this->hasMany(TempoEntreEntacao::class, 'estacao_origem_id');
    }

    public function temposDestino()
    {
        return $this->hasMany(TempoEntreEntacao::class, 'estacao_destino_id');
    }

    /**
     * Retorna um array fixo com os dias da semana em formato do Carbon:
     * 0 => domingo, 1 => segunda, ..., 6 => sábado
     */
    public function diasSemanaFuncionamento(): array
    {
        $dias = [
            0 => false,
            1 => false,
            2 => false,
            3 => false,
            4 => false,
            5 => false,
            6 => false,
        ];

        foreach ($this->diasUteis as $diaUtil) {
            $diaBanco = (int) $diaUtil->dia; // banco: 0=domingo ... 6=sábado

            if (array_key_exists($diaBanco, $dias)) {
                $dias[$diaBanco] = true;
            }
        }

        // Se nenhum dia útil foi configurado, assume seg–sáb como padrão
        // para não bloquear reservas em estações sem configuração de agenda.
        $algumDiaAtivo = in_array(true, $dias, true);
        if (!$algumDiaAtivo) {
            foreach ([1, 2, 3, 4, 5, 6] as $d) {
                $dias[$d] = true;
            }
        }

        return $dias;
    }

    /**
     * Retorna os próximos dias em que a estação funciona.
     *
     * Ex:
     * [
     *   ['data' => '2026-04-13', 'dia_semana' => 1],
     *   ['data' => '2026-04-14', 'dia_semana' => 2],
     * ]
     */
    public function proximosDiasFuncionamento(int $dias = 90, ?Carbon $dataBase = null): array
    {
        $dataBase = $dataBase ?: Carbon::today();
        $diasSemana = $this->diasSemanaFuncionamento();
        $retorno = [];

        for ($i = 0; $i < $dias; $i++) {
            $dia = $dataBase->copy()->addDays($i);
            $diaSemana = $dia->dayOfWeek; // 0=domingo ... 6=sábado

            if (empty($diasSemana[$diaSemana])) {
                continue;
            }

            $retorno[] = [
                'data' => $dia->toDateString(),
                'dia_semana' => $diaSemana,
            ];
        }

        return $retorno;
    }

    /**
     * Verifica se a estação possui ao menos 1 veículo disponível na data.
     */
    public function temVeiculoDisponivelNaData(string $data): bool
    {
        $dia = Carbon::parse($data)->toDateString();

        return $this->obterDiasComVeiculoDisponivel([
            ['data' => $dia, 'dia_semana' => Carbon::parse($dia)->dayOfWeek],
        ]) !== [];
    }

    /**
     * Recebe um array de datas e devolve somente as que possuem veículo disponível.
     *
     * Entrada:
     * [
     *   ['data' => '2026-04-13', 'dia_semana' => 1],
     *   ...
     * ]
     */
    public function filtrarDiasComVeiculoDisponivel(array $dias): array
    {
        return $this->obterDiasComVeiculoDisponivel($dias);
    }

    /**
     * Recebe um array de datas e devolve somente as que NÃO possuem veículo disponível.
     */
    public function filtrarDiasSemVeiculoDisponivel(array $dias): array
    {
        $datasDisponiveis = array_flip(array_map(
            static fn (array $item): string => $item['data'],
            $this->obterDiasComVeiculoDisponivel($dias)
        ));

        return array_values(array_filter($dias, static fn (array $item): bool => !isset($datasDisponiveis[$item['data']])));
    }

    /**
     * @param array<int, array{data:string,dia_semana:int}> $dias
     * @return array<int, array{data:string,dia_semana:int}>
     */
    private function obterDiasComVeiculoDisponivel(array $dias): array
    {
        if (empty($dias)) {
            return [];
        }

        // Se não existem tabelas de controle, todos os dias com veículo ativo são válidos
        if (!SchemaCache::hasTable('veiculos')) {
            return $dias;
        }

        $totalVeiculos = DB::table('veiculos')
            ->where('local_disponibilidade', $this->id)
            ->where('ativo', 'S')
            ->count();

        // Sem veículos ativos nesta estação: nenhum dia disponível
        if ($totalVeiculos === 0) {
            return [];
        }

        // Se não há tabela de movimentações, todos os dias são livres
        if (!SchemaCache::hasTable('movimentacoes') || !SchemaCache::hasTable('status_movimentacao')) {
            return $dias;
        }

        $dataInicial = Carbon::parse($dias[0]['data'])->startOfDay();
        $dataFinal   = Carbon::parse(end($dias)['data'])->endOfDay();

        // Busca movimentações ativas (saída sem chegada real) que bloqueiam veículos por período
        // Considera bloqueante: reserva em andamento (data_saida preenchida, data_chegada_real nula ou futura)
        $movimentacoes = DB::table('movimentacoes as m')
            ->join('veiculos as v', 'v.id', '=', 'm.veiculo_id')
            ->join('status_movimentacao as sm', 'sm.id', '=', 'm.status_movimentacao_id')
            ->where('v.local_disponibilidade', $this->id)
            ->where('v.ativo', 'S')
            ->where(function ($q) {
                // Status que indicam veículo ocupado/indisponível
                $q->whereRaw('LOWER(sm.nome) LIKE ?', ['%reserv%'])
                  ->orWhereRaw('LOWER(sm.nome) LIKE ?', ['%indispon%'])
                  ->orWhereRaw('LOWER(sm.nome) LIKE ?', ['%transit%'])
                  ->orWhereRaw('LOWER(sm.nome) LIKE ?', ['%manuten%'])
                  ->orWhereRaw('LOWER(sm.nome) LIKE ?', ['%espera%']);
            })
            // Movimentação sobrepõe o período consultado
            ->where(function ($q) use ($dataInicial) {
                $q->whereNull('m.data_chegada_real')
                  ->orWhere('m.data_chegada_real', '>=', $dataInicial);
            })
            ->where(function ($q) use ($dataFinal) {
                $q->whereNull('m.data_saida')
                  ->orWhere('m.data_saida', '<=', $dataFinal);
            })
            ->select(['m.veiculo_id', 'm.data_saida', 'm.data_chegada_real'])
            ->get();

        // Monta mapa: data => quantidade de veículos bloqueados naquele dia
        $bloqueadosPorDia = [];
        foreach ($movimentacoes as $mov) {
            $inicio = !empty($mov->data_saida)
                ? Carbon::parse($mov->data_saida)->startOfDay()
                : $dataInicial->copy();

            // Movimentação sem chegada real: bloqueia até o fim do período
            $fim = !empty($mov->data_chegada_real)
                ? Carbon::parse($mov->data_chegada_real)->startOfDay()
                : $dataFinal->copy()->startOfDay();

            $inicio = $inicio->lt($dataInicial) ? $dataInicial->copy() : $inicio;
            $fim    = $fim->gt($dataFinal)       ? $dataFinal->copy()->startOfDay()  : $fim;

            if ($inicio->gt($fim)) {
                continue;
            }

            $cursor = $inicio->copy();
            while ($cursor->lte($fim)) {
                $ds = $cursor->toDateString();
                $bloqueadosPorDia[$ds] = ($bloqueadosPorDia[$ds] ?? 0) + 1;
                $cursor->addDay();
            }
        }

        // Um dia é disponível se há pelo menos 1 veículo livre (total - bloqueados > 0)
        return array_values(array_filter(
            $dias,
            fn (array $item): bool => ($bloqueadosPorDia[$item['data']] ?? 0) < $totalVeiculos
        ));
    }

}
