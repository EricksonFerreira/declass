<?php

namespace App\Services;

use App\Exceptions\TipoEStatusMovimentacaoException;
use App\Models\Movimentacao;
use App\Models\MovimentacaoHistorico;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;

class MovimentacaoService extends AbstractBaseService
{
    protected Model $model;

    const USUARIO_PADRAO = 6;
    private $mapaMovimentacoes = [
        'adicionarAoEstoque' => ['tipo' => 1, 'status' => 1],
        'realizarCheckIn' => ['tipo' => 2, 'status' => 2],
        'realizarCheckOut' => ['tipo' => 3, 'status' => 1],
        'registrarProblemaMecanico' => ['tipo' => 4, 'status' => 8],
        'transportarParaOficina' => ['tipo' => 5, 'status' => 2],
        'registrarChegadaNaOficina' => ['tipo' => 6, 'status' => 5],
        'registrarRetornoDaOficina' => ['tipo' => 7, 'status' => 2],
        'registrarChegadaNaEstacao' => ['tipo' => 8, 'status' => 1],
        'marcarVeiculoIndisponivel' => ['tipo' => 9, 'status' => 6],
        'transferirEntreEstacoes' => ['tipo' => 10, 'status' => 2],
        'marcarForaDeServico' => ['tipo' => 11, 'status' => 7],
        'registrarVendaDeVeiculo' => ['tipo' => 14, 'status' => 1],
    ];
    public VeiculoService $veiculoService;

    public function __construct(
        Movimentacao $movimentacao,
    ) {
        $this->model = $movimentacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Movimentacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Movimentacao");
        }
        $this->model = $model;
    }


    /**
     * Define o serviço de veículos.
     *
     * @param VeiculoService $veiculoService
     */
    public function setVeiculoService(VeiculoService $veiculoService): void
    {
        $this->veiculoService = $veiculoService;
    }


    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        if (!SchemaCache::hasColumn('movimentacoes', 'gera_disponibilidade')) {
            unset($data['gera_disponibilidade']);
        } elseif (!array_key_exists('gera_disponibilidade', $data)) {
            $data['gera_disponibilidade'] = 'N';
        }

        $movimentacao = parent::create($data);

        if ($movimentacao instanceof Movimentacao) {
            $this->syncVeiculoFromMovimentacao($movimentacao);
            $this->registrarHistorico($movimentacao, null, 'create', 'Movimentação criada');
        }

        return $movimentacao;
    }

    public function update($id, array $data)
    {
        $original = $this->getById($id);
        $movimentacao = parent::update($id, $data);

        if ($movimentacao instanceof Movimentacao && $original) {
            $this->syncVeiculoFromMovimentacao($movimentacao);
            $this->registrarAlteracoes($original, $movimentacao);
        }

        return $movimentacao;
    }

    /**
     * Conclui a movimentação: define status=Concluído e salva chegada_real=agora.
     */
    public function concluir(int $id, ?string $chegadaReal = null): Movimentacao
    {
        $movimentacao = $this->getById($id);
        if (!$movimentacao) {
            abort(404);
        }

        $chegadaReal = $chegadaReal ?? now()->format('Y-m-d H:i:s');

        // ID 1 = Concluído (empresa-scoped: busca pelo nome)
        $statusConcluido = \App\Models\StatusMovimentacao::where('nome', 'Concluído')
            ->where('empresa_id', $movimentacao->empresa_id)
            ->first();

        $movimentacao->update([
            'status_movimentacao_id' => $statusConcluido?->id ?? 1,
            'data_chegada_real'      => $chegadaReal,
        ]);

        $this->syncVeiculoFromMovimentacao($movimentacao->fresh());

        MovimentacaoHistorico::create([
            'movimentacao_id' => $movimentacao->id,
            'user_id'         => Auth::id(),
            'acao'            => 'concluir',
            'observacao'      => 'Movimentação concluída. Chegada real registrada: ' . Carbon::parse($chegadaReal)->format('d/m/Y H:i'),
            'empresa_id'      => $movimentacao->empresa_id,
        ]);

        return $movimentacao->fresh();
    }

    private function registrarHistorico(Movimentacao $mov, ?array $campos, string $acao, ?string $observacao = null): void
    {
        MovimentacaoHistorico::create([
            'movimentacao_id' => $mov->id,
            'user_id'         => Auth::id(),
            'acao'            => $acao,
            'campo'           => $campos ? implode(', ', array_keys($campos)) : null,
            'observacao'      => $observacao,
            'empresa_id'      => $mov->empresa_id,
        ]);
    }

    private function registrarAlteracoes(Movimentacao $original, Movimentacao $novo): void
    {
        $camposMonitorados = [
            'tipo_movimentacao_id'  => 'Tipo',
            'status_movimentacao_id'=> 'Status',
            'veiculo_id'            => 'Veículo',
            'local_origem_id'       => 'Estação de Origem',
            'local_destino_id'      => 'Estação de Destino',
            'km_saida'              => 'KM Saída',
            'km_chegada'            => 'KM Chegada',
            'data_saida'            => 'Data de Saída',
            'data_chegada_prevista' => 'Chegada Prevista',
            'data_chegada_real'     => 'Chegada Real',
            'user_id'               => 'Usuário',
            'reserva_id'            => 'Reserva',
            'ordem_servico_id'      => 'Ordem de Serviço',
            'observacoes'           => 'Observações',
        ];

        $alteracoes = [];
        foreach ($camposMonitorados as $campo => $label) {
            $antes  = (string) ($original->$campo ?? '');
            $depois = (string) ($novo->$campo ?? '');
            if ($antes !== $depois) {
                $alteracoes[] = $label;
            }
        }

        if (!empty($alteracoes)) {
            MovimentacaoHistorico::create([
                'movimentacao_id' => $novo->id,
                'user_id'         => Auth::id(),
                'acao'            => 'update',
                'campo'           => implode(', ', $alteracoes),
                'observacao'      => 'Campos alterados: ' . implode(', ', $alteracoes),
                'empresa_id'      => $novo->empresa_id,
            ]);
        }
    }

    /**
     * Registra movimentacao inicial ao cadastrar veiculo.
     *
     * Mantido para compatibilidade com chamadas existentes em VeiculoService.
     */
    public function registraMovimentacaoCadastroVeiculo(array $data, int $veiculo_id): Model
    {
        $dataHoraDisponibilidade = $data['data_hora_disponibilidade'] ?? null;
        $statusMovimentacao = 1; // concluido

        // Quando o veículo já nasce vinculado a uma estação, a movimentação
        // inicial deve entrar como disponível imediatamente.
        if (!empty($data['local_disponibilidade'])) {
            $statusMovimentacao = 1;
        } elseif (!empty($dataHoraDisponibilidade)) {
            try {
                $statusMovimentacao = Carbon::parse($dataHoraDisponibilidade)->gt(Carbon::now()) ? 6 : 1;
            } catch (\Throwable $e) {
                $statusMovimentacao = 1;
            }
        }

        $dataHoraCarbon = null;
        if (!empty($dataHoraDisponibilidade)) {
            try {
                $dataHoraCarbon = Carbon::parse($dataHoraDisponibilidade);
            } catch (\Throwable $e) {
                $dataHoraCarbon = null;
            }
        }

        $valor = [
            'veiculo_id' => $veiculo_id,
            'tipo_movimentacao_id' => 1, // adicao de veiculo
            'status_movimentacao_id' => $statusMovimentacao,
            'local_destino_id' => $data['local_disponibilidade'] ?? null,
            'gera_disponibilidade' => 'S',
            'data_registro' => Carbon::now(),
            'data_saida' => null,
            'data_chegada_prevista' => $dataHoraCarbon ?? Carbon::now(),
            'data_chegada_real' => ($statusMovimentacao === 1) ? ($dataHoraCarbon ?? Carbon::now()) : null,
            'observacoes' => $data['observacoes'] ?? 'Cadastro inicial do veiculo',
            'user_id' => Auth::id() ?? self::USUARIO_PADRAO,
        ];

        return $this->create($valor);
    }

    /**
     * Alias para compatibilidade de chamadas legadas.
     */
    public function registrarMovimentacaoCadastroVeiculo(array $data, int $veiculo_id): Model
    {
        return $this->registraMovimentacaoCadastroVeiculo($data, $veiculo_id);
    }

    // /**
    //  * Registra uma movimentação para uma reserva.
    //  *
    //  * @param array $data Os dados da movimentação.
    //  * @param int $reservaId O ID da reserva.
    //  * @param int $tipoMovimentacao O tipo de movimentação (padrão: 11 é efetuar reserva).
    //  * @param int $statusMovimentacao O status da movimentação (padrão: 6 é agendada).
    //  * @return void
    //  */
    // public function registrarMovimentacaoReserva(array $data, int $reservaId,$tipoMovimentacao = 11, $statusMovimentacao = 6)
    // {
    //     $veiculo_id = null;
    //     if(isset($data['modelo_id']) && isset($data['estacao_retirada_id'])){
    //         $veiculo_id = $this->veiculoService->obterVeiculoDisponivelPorModeloEstacao($data['modelo_id'], $data['estacao_retirada_id']);
    //     }
    //     $valor = [
    //         'veiculo_id' => $veiculo_id,
    //         'tipo_movimentacao_id' => $tipoMovimentacao,
    //         'status_movimentacao_id' => $statusMovimentacao,
    //         'local_origem_id' => $data['estacao_retirada_id'] ?? null,
    //         'local_destino_id' => $data['estacao_devolucao_id'] ?? null,
    //         'data_saida' => $data['data_inicio'],
    //         'data_chegada_estimada' => $data['data_fim'],
    //         'reserva_id' => $reservaId ?? null,
    //         'modelo_id' => $data['modelo_id'] ?? null,
    //         'observacoes' => $data['observacoes'] ?? '',
    //         'user_id' => Auth::id() ?? 6
    //     ];

    //      $this->model->create($valor);
    // }

    // public function registraMovimentacaoCadastroVeiculo(array $data, $veiculo_id)
    // {

    //     $statusMovimentacao = Carbon::parse($data['data_hora_disponibilidade'])->gt(Carbon::now()) ? 6 : 1;

    //     $valor = [
    //         'veiculo_id' => $veiculo_id,
    //         'tipo_movimentacao_id' => 1, // adição veiculo
    //         'status_movimentacao_id' => 1, // concluido
    //         'local_destino_id' => $data['local_disponibilidade'] ?? null,
    //         'status_movimentacao_id' => $statusMovimentacao,
    //         'observacoes' => $data['observacoes'] ?? '',
    //         'user_id' => Auth::id() ?? 6
    //     ];

    //     $this->model->create($valor);
    // }

    // public function registraMovimentacaoCancelamentoReserva($reservaId)
    // {
    //     $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

    //     if (!$movimentacao) {
    //         return null;
    //     }

    //     $valor = [
    //         'tipo_movimentacao_id' => 12, // Cancelamento Reserva
    //         'status_movimentacao_id' => 1, // concluido
    //         'observacoes' => 'Cancelamento de Reserva',
    //         'user_id' => Auth::id()
    //     ];

    //     $movimentacao->update($valor);
    // }

    // public function modificaMovimentacaoReserva(array $data, int $reservaId)
    // {
    //     $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

    //     if (!$movimentacao) {
    //         return null;
    //     }

    //     $valor = [
    //         'modelo_id' => $data['modelo'] ?? null,
    //         // 'tipo_movimentacao_id' => '1',
    //         'local_origem_id' => $data['estacao_retirada_id'] ?? null,
    //         'local_destino_id' => $data['estacao_devolucao_id'] ?? null,
    //         // 'status_movimentacao_id' => '6',
    //         // 'reserva_id' => $reservaId ?? null,
    //         'observacoes' => $data['observacoes'] ?? '',
    //         'user_id' => Auth::id()
    //     ];

    //     $movimentacao->update($valor);
    // }

    // public function desativarMovimentacaoReserva($reservaId)
    // {
    //     $movimentacao = $this->model->where('reserva_id', $reservaId)->first();

    //     if (!$movimentacao) {
    //         return null;
    //     }

    //     $valor = [
    //         'status_movimentacao_id' => 3, // cancelado
    //     ];

    //     $movimentacao->update($valor);
    // }

// ------------------------------------------------------------------------------
    // Adição de Veículo
    public function adicionarAoEstoque($veiculo_id, $estacao_id, $observacao = null, $geraDisponibilidade = true)
    {
        $this->registrarMovimentacao($veiculo_id, $estacao_id, null,null,null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Retirada de Veículo (Check-in)
    public function realizarCheckIn($veiculo_id, $estacao_id, $reserva_id, $observacao = null, ?string $dataChegadaPrevista = null, ?int $kmSaida = null, $geraDisponibilidade = false)
    {
        $this->registrarMovimentacao($veiculo_id, $estacao_id, null, null, $reserva_id, $observacao, __FUNCTION__, $dataChegadaPrevista, $kmSaida, null, $geraDisponibilidade);
    }

    // Devolução de Veículo (Check-out)
    public function realizarCheckOut($veiculo_id, $estacao_id, $reserva_id, $observacao = null, ?int $kmChegada = null,  $geraDisponibilidade = false)
    {
        $this->registrarMovimentacao($veiculo_id, null, null, $estacao_id, $reserva_id, $observacao, __FUNCTION__, null, null, $kmChegada, $geraDisponibilidade);

        if ($reserva_id) {
            $tipoCheckIn = \App\Models\TipoMovimentacao::where('nome', 'like', '%Check-in%')->value('id') ?? 2;
            $statusConcluido = \App\Models\StatusMovimentacao::where('nome', 'Concluído')->value('id') ?? 1;

            \App\Models\Movimentacao::query()
                ->where('reserva_id', (int) $reserva_id)
                ->where('tipo_movimentacao_id', $tipoCheckIn)
                ->update([
                    'status_movimentacao_id' => $statusConcluido
                ]);
        }
    }

    // Problema Mecânico
    public function registrarProblemaMecanico($veiculo_id, $observacao = 'Problema mecânico relatado', $geraDisponibilidade = false)
    {
        $this->registrarMovimentacao($veiculo_id, null, null,null, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Transporte para Oficina
    public function transportarParaOficina($veiculo_id, $observacao = 'Veículo transportado para oficina', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id, null, null,null,null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Chegada à Oficina
    public function registrarChegadaNaOficina($veiculo_id, $observacao = 'Veículo chegou à oficina', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id, null, null,null,null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Retorno da Oficina
    public function registrarRetornoDaOficina($veiculo_id,$estacao_id, $observacao = 'Veículo retornou da oficina', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id, null,null,$estacao_id, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Chegada à Estação
    public function registrarChegadaNaEstacao($veiculo_id, $estacao_id, $observacao = 'Veículo chegou à estação', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id, null, null, $estacao_id, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Veículo Indisponível
    public function marcarVeiculoIndisponivel($veiculo_id, $observacao = 'Veículo marcado como indisponível', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id,null,null, null, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Transferência Entre Estações
    public function transferirEntreEstacoes($veiculo_id,$estacao_origem_id, $estacao_destino_id, $observacao = 'Veículo transferido entre estações', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id,null,$estacao_origem_id, $estacao_destino_id, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Fora de Serviço
    public function marcarForaDeServico($veiculo_id, $observacao = 'Veículo marcado como fora de serviço', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id,null,null, null, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }

    // Venda de Veículo
    public function registrarVendaDeVeiculo($veiculo_id, $observacao = 'Veículo vendido', $geraDisponibilidade = false){
        $this->registrarMovimentacao($veiculo_id,null,null, null, null, $observacao, __FUNCTION__, null, null, null, $geraDisponibilidade);
    }


    private function syncVeiculoFromMovimentacao(Movimentacao $movimentacao): void
    {
        if (empty($movimentacao->veiculo_id)) {
            return;
        }

        $query = Veiculo::query();

        if (
            SchemaCache::hasColumn('veiculos', 'empresa_id')
            && SchemaCache::hasColumn('movimentacoes', 'empresa_id')
            && !empty($movimentacao->empresa_id)
        ) {
            $query->where('empresa_id', (int) $movimentacao->empresa_id);
        }

        /** @var Veiculo|null $veiculo */
        $veiculo = $query->find((int) $movimentacao->veiculo_id);
        if (!$veiculo) {
            return;
        }

        $updates = [];

        if ($movimentacao->km_chegada !== null) {
            $kmChegada = (int) $movimentacao->km_chegada;
            $kmSaida = $movimentacao->km_saida !== null ? (int) $movimentacao->km_saida : null;

            if ($kmChegada >= 0 && ($kmSaida === null || $kmChegada >= $kmSaida)) {
                $kmAtualVeiculo = $veiculo->kms !== null ? (int) $veiculo->kms : null;
                if ($kmAtualVeiculo === null || $kmChegada >= $kmAtualVeiculo) {
                    $updates['kms'] = $kmChegada;
                }
            }
        }

        if (!empty($movimentacao->local_destino_id) && !empty($movimentacao->data_chegada_real)) {
            $updates['local_disponibilidade'] = (int) $movimentacao->local_destino_id;

            if (SchemaCache::hasColumn('veiculos', 'data_hora_disponibilidade')) {
                $updates['data_hora_disponibilidade'] = $movimentacao->data_chegada_real;
            }
        }

        if (!empty($movimentacao->data_chegada_real)) {
            $statusDisponivelIds = $this->veiculoService->getStatusVeiculoDisponivelIds();
            if (!empty($statusDisponivelIds)) {
                $updates['status_veiculo_id'] = (int) reset($statusDisponivelIds);
            }
        }

        if (in_array($movimentacao->gera_disponibilidade, ['S', 's', 1, '1', true], true)) {
            $updates['data_hora_disponibilidade'] = $movimentacao->data_chegada_real ?? now();
        }

        if (!empty($updates)) {
            $veiculo->update($updates);
        }
    }

    /**
     * Cancela movimentações vinculadas a uma reserva (ex.: check-in) para não bloquear disponibilidade do veículo.
     * Retorna quantidade de registros afetados.
     */
    public function cancelarMovimentacoesReserva(int $reservaId, ?array $tipoMovimentacaoIds = null, ?string $observacao = null): int
    {
        if ($reservaId <= 0) {
            return 0;
        }

        if (!SchemaCache::hasTable('movimentacoes') || !SchemaCache::hasColumn('movimentacoes', 'reserva_id')) {
            return 0;
        }

        $cancelStatusId = 3;
        $table = null;
        if (SchemaCache::hasTable('status_movimentacao')) {
            $table = 'status_movimentacao';
        } elseif (SchemaCache::hasTable('status_movimentacaos')) {
            $table = 'status_movimentacaos';
        }

        if ($table && SchemaCache::hasColumn($table, 'nome')) {
            $cancelStatusId = (int) (DB::table($table)
                ->whereRaw("LOWER(COALESCE(nome, '')) LIKE ?", ['%cancel%'])
                ->value('id') ?? 3);
        }

        $query = DB::table('movimentacoes')->where('reserva_id', $reservaId);

        if (!empty($tipoMovimentacaoIds) && SchemaCache::hasColumn('movimentacoes', 'tipo_movimentacao_id')) {
            $query->whereIn('tipo_movimentacao_id', array_map('intval', $tipoMovimentacaoIds));
        }

        if (SchemaCache::hasColumn('movimentacoes', 'status_movimentacao_id')) {
            $query->where(function ($sub) use ($cancelStatusId) {
                $sub->whereNull('status_movimentacao_id')->orWhere('status_movimentacao_id', '!=', $cancelStatusId);
            });
        }

        $updates = [];
        if (SchemaCache::hasColumn('movimentacoes', 'status_movimentacao_id')) {
            $updates['status_movimentacao_id'] = $cancelStatusId;
        }
        if (SchemaCache::hasColumn('movimentacoes', 'data_chegada_real')) {
            $updates['data_chegada_real'] = now();
        }
        if ($observacao && SchemaCache::hasColumn('movimentacoes', 'observacoes')) {
            // sobrescreve observação apenas quando ainda vazia, para evitar apagar histórico importante
            $updates['observacoes'] = $observacao;
        }
        if (SchemaCache::hasColumn('movimentacoes', 'updated_at')) {
            $updates['updated_at'] = now();
        }

        if (empty($updates)) {
            return 0;
        }

        return (int) $query->update($updates);
    }

    /**
     * Usado quando um check-in/contrato é removido e a reserva volta ao estado "Confirmada":
     * cancela e tenta desvincular (ou remover) movimentações para não manter o veículo bloqueado
     * na lógica de disponibilidade que cruza movimentações x reservas.
     */
    public function liberarBloqueioDeDisponibilidadePorReserva(int $reservaId, array $tipoMovimentacaoIds = [2, 3], ?string $observacao = null): int
    {
        $afetados = $this->cancelarMovimentacoesReserva($reservaId, $tipoMovimentacaoIds, $observacao);

        if (!SchemaCache::hasTable('movimentacoes') || !SchemaCache::hasColumn('movimentacoes', 'reserva_id')) {
            return $afetados;
        }

        // Primeiro tenta desvincular reserva_id para não participar do join com reservas na disponibilidade.
        try {
            $q = DB::table('movimentacoes')->where('reserva_id', $reservaId);
            if (SchemaCache::hasColumn('movimentacoes', 'tipo_movimentacao_id')) {
                $q->whereIn('tipo_movimentacao_id', array_map('intval', $tipoMovimentacaoIds));
            }

            $update = ['reserva_id' => null];
            if (SchemaCache::hasColumn('movimentacoes', 'updated_at')) {
                $update['updated_at'] = now();
            }

            $afetados += (int) $q->update($update);

            return $afetados;
        } catch (\Throwable $e) {
            // Fallback: remove registros (somente quando não for possível desvincular)
            try {
                $q = DB::table('movimentacoes')->where('reserva_id', $reservaId);
                if (SchemaCache::hasColumn('movimentacoes', 'tipo_movimentacao_id')) {
                    $q->whereIn('tipo_movimentacao_id', array_map('intval', $tipoMovimentacaoIds));
                }
                $afetados += (int) $q->delete();
            } catch (\Throwable $e2) {
                // ignora: cancelamento já foi aplicado
            }

            return $afetados;
        }
    }

    private function registrarMovimentacao(
        $veiculo_id,
        $local_id = null,
        $local_origem_id = null,
        $local_destino_id = null,
        $reserva_id = null,
        $observacao = null,
        ?string $funcao = null,
        ?string $dataChegadaPrevista = null,
        ?int $kmSaida = null,
        ?int $kmChegada = null,
        bool $geraDisponibilidade = false
    )
    {
        if (!$funcao) {
            throw new TipoEStatusMovimentacaoException('Funcao de movimentacao nao informada.', 500);
        }

        $movimentacao = $this->obterTipoEStatusDaMovimentacao($funcao);
        $tipoMovimentacao = $movimentacao['tipo'];
        $statusMovimentacao = $movimentacao['status'];

        $validReservaId = null;
        if ($reserva_id !== null && $reserva_id > 0) {
            if (\App\Models\Reserva::where('id', (int) $reserva_id)->exists()) {
                $validReservaId = (int) $reserva_id;
            }
        }

        $ehChegada = in_array($funcao, [
            'adicionarAoEstoque',
            'realizarCheckOut',
            'registrarChegadaNaEstacao',
            'registrarChegadaNaOficina',
            'registrarRetornoDaOficina'
        ], true);

        $valor = [
            'veiculo_id' => $veiculo_id,
            'tipo_movimentacao_id' => $tipoMovimentacao,
            'status_movimentacao_id' => $statusMovimentacao,
            'local_origem_id' => $local_origem_id ?? $local_id,
            'local_destino_id' => $local_destino_id,
            'data_registro' => Carbon::now(),
            'data_saida' => $ehChegada ? null : Carbon::now(),
            'data_chegada_real' => $ehChegada ? Carbon::now() : null,
            'reserva_id' => $validReservaId,
            'observacoes' => $observacao,
            'user_id' => Auth::id() ?? self::USUARIO_PADRAO,
            'gera_disponibilidade' => $geraDisponibilidade ? 'S' : 'N',
        ];

        if ($kmSaida !== null) {
            $valor['km_saida'] = $kmSaida;
        }

        if ($kmChegada !== null) {
            $valor['km_chegada'] = $kmChegada;
        }

        if (!SchemaCache::hasColumn('movimentacoes', 'gera_disponibilidade')) {
            unset($valor['gera_disponibilidade']);
        } else {
            $geraDisponibilidadeEfetiva = $geraDisponibilidade || in_array($funcao, [
                'adicionarAoEstoque',
                'registrarRetornoDaOficina',
            ], true);

            $valor['gera_disponibilidade'] = $geraDisponibilidadeEfetiva ? 'S' : 'N';
        }

        if ($dataChegadaPrevista !== null) {
            $valor['data_chegada_prevista'] = Carbon::parse($dataChegadaPrevista)->toDateTimeString();
        } elseif ($validReservaId) {
            $reserva = \App\Models\Reserva::find($validReservaId);
            if ($reserva?->data_fim) {
                $valor['data_chegada_prevista'] = Carbon::parse($reserva->data_fim)->toDateTimeString();
            }
        }

        $movimentacao = $this->create($valor);

        if (
            $movimentacao instanceof Movimentacao
            && SchemaCache::hasColumn('movimentacoes', 'gera_disponibilidade')
            && $geraDisponibilidadeEfetiva
        ) {
            $movimentacao->forceFill(['gera_disponibilidade' => 'S'])->save();
        }
    }


    private function obterTipoEStatusDaMovimentacao(string $nomeFuncao): array
    {
        return $this->mapaMovimentacoes[$nomeFuncao] ?? throw new TipoEStatusMovimentacaoException("Função desconhecida: $nomeFuncao", 404);
    }
}
