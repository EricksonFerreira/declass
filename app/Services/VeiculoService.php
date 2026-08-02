<?php

namespace App\Services;

use App\Enums\StatusReservaEnum;
use App\Models\Movimentacao;
use App\Models\Veiculo;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VeiculoService extends AbstractBaseService
{
    protected Model $model;

    public MovimentacaoService $movimentacaoService;

    public DisponibilidadeInicialVeiculoService $disponibilidadeInicialVeiculoService;

    private ?array $statusVeiculoDisponivelIds = null;

    private ?int $reservaBloqueioPosReservaHoras = null;

    private ?array $statusMovimentacaoCanceladoIds = null;

    public function __construct(Veiculo $veiculo, DisponibilidadeInicialVeiculoService $disponibilidadeInicialVeiculoService)
    {
        $this->model = $veiculo;
        $this->disponibilidadeInicialVeiculoService = $disponibilidadeInicialVeiculoService;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Veiculo) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Veiculo');
        }
        $this->model = $model;
    }

    /**
     * Define o serviço de movimentação.
     */
    public function setMovimentacaoService(MovimentacaoService $movimentacaoService): void
    {
        $this->movimentacaoService = $movimentacaoService;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    // overide

    /**
     * Summary of create

     *
     * @override
     *
     * @return Model
     */
    public function create(array $data)
    {
        $data = $this->normalizarDadosDisponibilidadeInicial($data);
        $this->injectEmpresaId($data, true);
        $veiculo = $this->model->create($data);
        $veiculo->nome = 'vei'.str_pad($veiculo->id, 4, '0', STR_PAD_LEFT);
        $veiculo->save();

        $this->atualizarCamposFinanceiros($veiculo, $data);

        $this->movimentacaoService->registraMovimentacaoCadastroVeiculo($data, $veiculo->id);

        if(isset($data['local_disponibilidade'])){
            $dataHoraDisponibilidade =  isset($data['data_hora_disponibilidade']) ? $data['data_hora_disponibilidade'] : date('Y-m-d H:i:s');
            $this->disponibilidadeInicialVeiculoService->create([
                'veiculo_id' => $veiculo->id,
                'estacao_id' => $data['local_disponibilidade'],
                'data_hora' => $dataHoraDisponibilidade,
                ]);
        }

        return $veiculo;
    }

    /**
     * Summary of update
     *
     * @param  mixed  $id
     *
     * @override
     *
     * @return Model
     */
    public function update($id, array $data)
    {
        $data = $this->normalizarDadosDisponibilidadeInicial($data);
        $veiculo = $this->applyEmpresaScope($this->model->newQuery())->find($id);
        if (! $veiculo) {
            return null;
        }

        $this->injectEmpresaId($data, true);
        $naoMovimentouInicialmente = is_null($veiculo->data_hora_disponibilidade) || is_null($veiculo->local_disponibilidade);
        $veiculo->update($data);
        $this->atualizarCamposFinanceiros($veiculo->fresh(), $data);

        if ($naoMovimentouInicialmente) {
            if(isset($data['local_disponibilidade'])){
                $dataHoraDisponibilidade =  isset($data['data_hora_disponibilidade']) ? $data['data_hora_disponibilidade'] : date('Y-m-d H:i:s');

                $this->movimentacaoService->registraMovimentacaoCadastroVeiculo($data, $veiculo->id);
                $this->disponibilidadeInicialVeiculoService->create([
                    'veiculo_id' => $veiculo->id,
                    'estacao_id' => $data['local_disponibilidade'],
                    'data_hora' => $dataHoraDisponibilidade,
                ]);
            }
        }

        return $veiculo;
    }

    /**
     * Veículos ativos no escopo de empresa (sem filtro de estação) para selects em fluxos como ordem de serviço.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Veiculo>
     */
    public function listarAtivosParaOrdemServico(int $limit = 800)
    {
        $query = $this->applyEmpresaScope($this->model->newQuery());

        if (SchemaCache::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        } elseif (SchemaCache::hasColumn($this->model->getTable(), 'status_veiculo_id')) {
            $query = $this->scopeStatusVeiculoDisponivel($query);
        }

        return $query
            ->orderBy('nome')
            ->limit($limit)
            ->get();
    }

    public function getVeiculosDisponibilidade(?string $estacaoFiltro = null, int $perPage = 1000)
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope(
            $this->model->newQuery()
        ))->with([
            'grupoFrota',
            'modelo',
            'localDisponibilidade',
            'statusVeiculo',
        ]);

        if ($estacaoFiltro === 'sem_estacao') {
            $query->whereNull('local_disponibilidade');
        } elseif (is_numeric($estacaoFiltro) && (int) $estacaoFiltro > 0) {
            $query->where('local_disponibilidade', (int) $estacaoFiltro);
        }

        return $query->orderBy('id', 'asc')->paginate($perPage);
    }

    /**
     * Obtém veículos disponíveis por modelo e estação específica.
     *
     * @param  int  $modeloId  O ID do modelo do veículo.
     * @param  int  $estacaoId  O ID da estação onde o veículo está disponível.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos disponíveis.
     */
    public function getVeiculosDisponiveisPorModeloEEstacaoEspecifica($modeloId, $estacaoId)
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', $estacaoId)
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
    }

    /**
     * Obtém veículos disponíveis por modelo, exceto em uma estação específica.
     *
     * @param  int  $modeloId  O ID do modelo do veículo.
     * @param  int  $estacaoId  O ID da estação que deve ser excluída.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos disponíveis.
     */
    public function getVeiculosDisponiveisPorModeloExcetoEstacao($modeloId, $estacaoId)
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', '!=', $estacaoId)
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
    }

    public function obterVeiculoDisponivelPorModeloEstacao($modeloId, $estacaoId)
    {
        $veiculos = $this->getVeiculosDisponiveisPorModeloEEstacaoEspecifica($modeloId, $estacaoId);

        if ($veiculos->isEmpty()) {
            $veiculos = $this->getVeiculosDisponiveisPorModeloExcetoEstacao($modeloId, $estacaoId);
        }

        if ($veiculos->isEmpty()) {
            throw new \Exception('Nenhum veículo disponível para o modelo e estação informados.');
        }

        return $veiculos->first()->id;
    }

    /**
     * Obtém veículos disponíveis por modelo, estação e intervalo de datas.
     *
     * @param  int  $modeloId  O ID do modelo do veículo.
     * @param  int  $estacaoId  O ID da estação onde o veículo está disponível.
     * @param  string  $data_inicio  A data de início do intervalo.
     * @param  string  $data_fim  A data de fim do intervalo.
     * @param  int  $reservaId  O ID da reserva.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos disponíveis.
     */
    public function getVeiculosDisponiveisPorModeloEEstacaoEIntervaloDatas(int $modeloId, int $estacaoId, string $data_inicio, string $data_fim, ?int $reservaId = null)
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('modelo_id', $modeloId)
            ->where('local_disponibilidade', $estacaoId)
            ->where(function ($query) use ($data_inicio) {
                $query->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $data_inicio);
            })
            ->whereDoesntHave('movimentacoes', function ($query) use ($data_inicio, $data_fim, $reservaId) {
                if ($reservaId) {
                    $query->where('movimentacoes.reserva_id', '!=', $reservaId);
                }

                $query->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                    ->whereIn('reservas.status_reserva_id', [StatusReservaEnum::Pendente->value, StatusReservaEnum::Confirmada->value, StatusReservaEnum::NoShow->value])
                    ->where('reservas.data_inicio', '<', $data_fim)
                    ->where('reservas.data_fim', '>', $data_inicio);
            })
            ->whereDoesntHave('movimentacoes', function ($query) use ($data_fim) {
                // Bloqueia Veículo com movimentaÃ§ão ainda não concluÃ­da (ex: transferência entre estações),
                // mesmo quando a movimentaÃ§ão não estÃ¡ vinculada a uma reserva.
                $query->whereNull('data_chegada_real')
                    ->where(function ($sub) use ($data_fim) {
                        $sub->whereNull('data_saida')->orWhere('data_saida', '<', $data_fim);
                    });

                $cancelados = $this->getStatusMovimentacaoCanceladoIds();
                if (! empty($cancelados)) {
                    $query->whereNotIn('status_movimentacao_id', $cancelados);
                }
            });

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
    }

    /**
     * Obtém veículos ativos que não terão movimentações no período especificado.
     *
     * @param  string  $data_inicio  A data de início do intervalo.
     * @param  string  $data_fim  A data de fim do intervalo.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de veículos ativos sem movimentações no período.
     */
    public function getVeiculosAtivosSemMovimentacoesNoPeriodo(string $data_inicio, string $data_fim)
    {
        // Subquery para obter IDs de veículos com movimentações no período
        $veiculosComMovimentacoes = Movimentacao::where(function ($query) use ($data_inicio, $data_fim) {
            $query->whereBetween('data_inicio', [$data_inicio, $data_fim])
                ->orWhereBetween('data_fim', [$data_inicio, $data_fim])
                ->orWhere(function ($query) use ($data_inicio, $data_fim) {
                    $query->where('data_inicio', '<=', $data_inicio)
                        ->where('data_fim', '>=', $data_fim);
                });
        })->pluck('veiculo_id');

        // Obter veículos ativos que não estão na subquery
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->whereNotIn('id', $veiculosComMovimentacoes)
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        return $query->get();
    }

    /**
     * Obtém modelos pelo grupo de frotas que têm veículos disponíveis no período específico e no local informado.
     *
     * @param  int  $idGrupoFrota  O ID do grupo de frotas.
     * @param  int  $idLocal  O ID do local.
     * @param  string  $data_inicio  A data de início do intervalo.
     * @param  string  $data_fim  A data de fim do intervalo.
     * @return \Illuminate\Database\Eloquent\Collection A coleção de modelos com veículos disponíveis.
     */
    public function getModelosComVeiculosDisponiveis(int $idGrupoFrota, int $idLocal, string $data_inicio, string $data_fim)
    {
        $query = $this->baseQueryVeiculosDisponiveisNoPeriodo($data_inicio, $data_fim, null, null, $idLocal)
            ->whereHas('modelo.gruposFrota', function ($query) use ($idGrupoFrota) {
                $query->where('grupos_frota.id', $idGrupoFrota);
            })
            ->with('modelo');

        return $query->get()
            ->pluck('modelo')
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Obtém a quantidade de veículos disponíveis de um grupo de frota em uma estação e período.
     */
    public function getQuantidadeVeiculosDisponiveisPorGrupoEEstacaoEPeriodo(
        int $idGrupoFrota,
        int $idLocal,
        string $data_inicio,
        string $data_fim
    ): int {
        if ($idGrupoFrota <= 0 || $idLocal <= 0 || trim($data_inicio) === '' || trim($data_fim) === '') {
            return 0;
        }

        return (int) $this->baseQueryVeiculosDisponiveisNoPeriodo($data_inicio, $data_fim, $idGrupoFrota, null, $idLocal)->count();
    }

    /**
     * Retorna o total de veiculos ativos por estacao.
     *
     * @param  array<int, int|string>  $estacoesIds
     * @return array<int, int>
     */
    public function getQuantidadeVeiculosAtivosPorEstacao(array $estacoesIds = [], array $grupoFrotaIds = []): array
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->whereNotNull('local_disponibilidade');

        $query = $this->scopeStatusVeiculoDisponivel($query);

        if (! empty($estacoesIds)) {
            $query->whereIn('local_disponibilidade', array_map('intval', $estacoesIds));
        }

        if (! empty($grupoFrotaIds)) {
            $query->whereIn('grupo_frota_id', array_map('intval', $grupoFrotaIds));
        }

        $agrupados = $query
            ->selectRaw('local_disponibilidade, COUNT(*) as total')
            ->groupBy('local_disponibilidade')
            ->pluck('total', 'local_disponibilidade')
            ->toArray();

        $resultado = [];
        if (! empty($estacoesIds)) {
            foreach ($estacoesIds as $estacaoId) {
                $id = (int) $estacaoId;
                $resultado[$id] = (int) ($agrupados[$id] ?? 0);
            }

            return $resultado;
        }

        foreach ($agrupados as $estacaoId => $total) {
            $resultado[(int) $estacaoId] = (int) $total;
        }

        return $resultado;
    }

    public function getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
        int $estacaoId,
        string $dataInicio,
        string $dataFim,
        ?int $reservaId = null
    ): int {
        if ($estacaoId <= 0 || trim($dataInicio) === '' || trim($dataFim) === '') {
            return 0;
        }

        return (int) $this->baseQueryVeiculosDisponiveisNoPeriodo($dataInicio, $dataFim, null, null, $estacaoId, $reservaId)->count();
    }

    public function temVeiculoDisponivelNaEstacaoEmDataHora(
        int $estacaoId,
        string $dataHora,
        ?int $reservaId = null
    ): bool {
        if ($estacaoId <= 0 || trim($dataHora) === '') {
            return false;
        }

        $dataFim = Carbon::parse($dataHora)->addMinute()->format('Y-m-d\TH:i');

        return $this->getQuantidadeVeiculosDisponiveisPorEstacaoEPeriodo(
            $estacaoId,
            $dataHora,
            $dataFim,
            $reservaId
        ) > 0;
    }

    private function normalizarDadosDisponibilidadeInicial(array $data): array
    {
        $estacao = trim((string) ($data['local_disponibilidade'] ?? ''));
        $dataHora = trim((string) ($data['data_hora_disponibilidade'] ?? ''));

        if ($estacao === '' && $dataHora === '') {
            unset($data['local_disponibilidade'], $data['data_hora_disponibilidade']);
            return $data;
        }

        if ($dataHora !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataHora)) {
            $data['data_hora_disponibilidade'] = $dataHora . ' 00:00';
        }

        return $data;
    }

    private function atualizarCamposFinanceiros(Veiculo $veiculo, array $data): void
    {
        if (! $veiculo) {
            return;
        }

        $updates = [];

        foreach (['valor_compra', 'data_compra', 'taxa_depreciacao_anual', 'valor_fipe', 'codigo_fipe', 'referencia_fipe', 'data_consulta_fipe'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $updates[$campo] = $data[$campo];
            }
        }

        if ($updates === []) {
            return;
        }

        $veiculo->forceFill($updates);
        $veiculo->save();
    }

    public function getVeiculosDisponiveisPorUltimaMovimentacao(?string $estacaoFiltro = null, int $perPage = 1000)
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->with([
                'grupoFrota',
                'modelo',
                'localDisponibilidade',
                'statusVeiculo',
            ]);

        $query->whereExists(function ($sub) {
            $sub->selectRaw('1')
                ->from('movimentacoes as m')
                ->whereColumn('m.veiculo_id', 'veiculos.id')
                ->whereColumn('m.id', function ($latest) {
                    $latest->selectRaw('MAX(m2.id)')
                        ->from('movimentacoes as m2')
                        ->whereColumn('m2.veiculo_id', 'veiculos.id');
                })
                ->where('m.gera_disponibilidade', true);
        });

        if ($estacaoFiltro === 'sem_estacao') {
            $query->whereNull('local_disponibilidade');
        } elseif (is_numeric($estacaoFiltro) && (int) $estacaoFiltro > 0) {
            $query->where('local_disponibilidade', (int) $estacaoFiltro);
        }

        return $query->orderBy('id', 'asc')->paginate($perPage);
    }

    /**
     * Retorna os IDs de estações que possuem ao menos 1 veículo disponível no período.
     * Pode filtrar por grupo_frota_id e/ou modelo_id (quando informados).
     *
     * @return array<int, int>
     */
    public function getEstacaoIdsComVeiculosDisponiveisNoPeriodo(
        string $dataInicio,
        string $dataFim,
        ?int $grupoFrotaId = null,
        ?int $modeloId = null,
        ?int $reservaId = null
    ): array {
        if (trim($dataInicio) === '' || trim($dataFim) === '') {
            return [];
        }

        $query = $this->baseQueryVeiculosDisponiveisNoPeriodo($dataInicio, $dataFim, $grupoFrotaId, $modeloId, null, $reservaId);

        return $query->select('local_disponibilidade')
            ->distinct()
            ->pluck('local_disponibilidade')
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Base única de disponibilidade por período. Os métodos públicos aplicam apenas os filtros específicos.
     */
    private function baseQueryVeiculosDisponiveisNoPeriodo(
        string $dataInicio,
        string $dataFim,
        ?int $grupoFrotaId = null,
        ?int $modeloId = null,
        ?int $estacaoId = null,
        ?int $reservaId = null
    ) {
        $dataInicioComFolga = $this->dataInicioComFolgaPosReserva($dataInicio);

        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->whereNotNull('local_disponibilidade')
            ->where(function ($subQuery) use ($dataInicio) {
                $subQuery->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $dataInicio);
            });

        if ($grupoFrotaId) {
            $query->where('grupo_frota_id', (int) $grupoFrotaId);
        }

        if ($modeloId) {
            $query->where('modelo_id', (int) $modeloId);
        }

        if ($estacaoId) {
            $query->where('local_disponibilidade', (int) $estacaoId);
        }

        $query->whereDoesntHave('movimentacoes', function ($subQuery) use ($dataInicioComFolga, $dataFim, $reservaId) {
            if ($reservaId) {
                $subQuery->where('movimentacoes.reserva_id', '!=', $reservaId);
            }

            $subQuery->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                ->whereIn('reservas.status_reserva_id', [StatusReservaEnum::Pendente->value, StatusReservaEnum::Confirmada->value, StatusReservaEnum::NoShow->value])
                ->where('reservas.data_inicio', '<', $dataFim)
                ->where('reservas.data_fim', '>', $dataInicioComFolga);
        });

        $query->whereDoesntHave('movimentacoes', function ($subQuery) use ($dataFim) {
            $subQuery->whereNull('data_chegada_real')
                ->where(function ($sub) use ($dataFim) {
                    $sub->whereNull('data_saida')->orWhere('data_saida', '<', $dataFim);
                });

            $cancelados = $this->getStatusMovimentacaoCanceladoIds();
            if (! empty($cancelados)) {
                $subQuery->whereNotIn('status_movimentacao_id', $cancelados);
            }
        });

        return $this->scopeStatusVeiculoDisponivel($query);
    }

    private function getStatusMovimentacaoCanceladoIds(): array
    {
        if ($this->statusMovimentacaoCanceladoIds !== null) {
            return $this->statusMovimentacaoCanceladoIds;
        }

        $this->statusMovimentacaoCanceladoIds = [3];

        $table = null;
        if (SchemaCache::hasTable('status_movimentacao')) {
            $table = 'status_movimentacao';
        } elseif (SchemaCache::hasTable('status_movimentacaos')) {
            $table = 'status_movimentacaos';
        }

        if ($table && SchemaCache::hasColumn($table, 'nome')) {
            try {
                $ids = DB::table($table)
                    ->select('id')
                    ->whereRaw('LOWER(nome) LIKE ?', ['%cancel%'])
                    ->pluck('id')
                    ->map(fn ($v) => (int) $v)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (! empty($ids)) {
                    $this->statusMovimentacaoCanceladoIds = $ids;
                }
            } catch (\Throwable $e) {
                // MantÃ©m fallback.
            }
        }

        return $this->statusMovimentacaoCanceladoIds;
    }

    private function dataInicioComFolgaPosReserva(string $dataInicio): string
    {
        $horas = $this->getReservaBloqueioPosReservaHoras();
        if ($horas <= 0) {
            return $dataInicio;
        }

        return Carbon::parse($dataInicio)->subHours($horas)->toDateTimeString();
    }

    private function getReservaBloqueioPosReservaHoras(): int
    {
        if ($this->reservaBloqueioPosReservaHoras !== null) {
            return $this->reservaBloqueioPosReservaHoras;
        }

        if (! SchemaCache::hasTable('empresas') || ! SchemaCache::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
            $this->reservaBloqueioPosReservaHoras = 0;

            return 0;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            $this->reservaBloqueioPosReservaHoras = 0;

            return 0;
        }

        $valor = DB::table('empresas')->where('id', $empresaId)->value('reserva_bloqueio_pos_reserva_horas');
        $horas = (int) ($valor ?? 0);
        if ($horas < 0) {
            $horas = 0;
        }

        $this->reservaBloqueioPosReservaHoras = $horas;

        return $horas;
    }

    public function getTotalVeiculos()
    {
        return $this->model->count();
    }

    public function getVeiculosDisponiveis()
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()));
        $query = $this->scopeStatusVeiculoDisponivel($query);

        return (int) $query->count();
    }

    public function getVeiculosReservados()
    {
        return $this->model->where('status_veiculo_id', 2)->count();
    }

    public function getVeiculosIndisponiveis()
    {
        return $this->model->where('status_veiculo_id', 3)->count();
    }

    /**
     * IDs de modelos que possuem pelo menos um veículo cadastrado no grupo de frota informado.
     */
    public function getModeloIdsDeVeiculosNoGrupo(int $grupoFrotaId): array
    {
        if ($grupoFrotaId <= 0) {
            return [];
        }

        return $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->where('grupo_frota_id', $grupoFrotaId)
            ->whereNotNull('modelo_id')
            ->distinct()
            ->pluck('modelo_id')
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Busca veículos disponíveis para o período de uma reserva na estação da reserva,
     * aplicando filtros de texto opcionais (placa, nome, chassi, cor, modelo, etc.).
     *
     * @param  int  $reservaId  excluir movimentações desta reserva
     * @param  array  $filtros  placa|nome|chassi|cor|modelo|marca_id|grupo_frota_id|tipo_frota_id|ativo|ano_de|ano_ate|kms_ate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarDisponiveisCheckin(int $estacaoId, string $dataInicio, string $dataFim, int $reservaId, array $filtros = [])
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->with(['marca', 'modelo', 'grupoFrota', 'tipoFrota', 'statusVeiculo'])
            ->where('local_disponibilidade', $estacaoId)
            ->where(function ($q) use ($dataInicio) {
                $agora = now()->format('Y-m-d H:i:s');
                $limite = $dataInicio > $agora ? $dataInicio : $agora;
                $q->whereNull('data_hora_disponibilidade')
                    ->orWhere('data_hora_disponibilidade', '<=', $limite);
            })
            ->whereDoesntHave('movimentacoes', function ($q) use ($dataInicio, $dataFim, $reservaId) {
                $q->where('movimentacoes.reserva_id', '!=', $reservaId)
                    ->join('reservas', 'reservas.id', '=', 'movimentacoes.reserva_id')
                    ->whereIn('reservas.status_reserva_id', [StatusReservaEnum::Pendente->value, StatusReservaEnum::Confirmada->value, StatusReservaEnum::NoShow->value])
                    ->where('reservas.data_inicio', '<', $dataFim)
                    ->where('reservas.data_fim', '>', $dataInicio);
            })
            ->whereDoesntHave('movimentacoes', function ($q) use ($dataFim) {
                $q->whereNull('data_chegada_real')
                    ->where(function ($s) use ($dataFim) {
                        $s->whereNull('data_saida')->orWhere('data_saida', '<', $dataFim);
                    });
                $cancelados = $this->getStatusMovimentacaoCanceladoIds();
                if (! empty($cancelados)) {
                    $q->whereNotIn('status_movimentacao_id', $cancelados);
                }
            });

        $query = $this->scopeStatusVeiculoDisponivel($query);

        // Filtros de texto/seleção
        if (! empty($filtros['grupo_frota_ids'])) {
            $query->whereIn('grupo_frota_id', (array) $filtros['grupo_frota_ids']);
        }
        if (! empty($filtros['placa'])) {
            $query->where('placa_licenca', 'like', '%'.$filtros['placa'].'%');
        }
        if (! empty($filtros['nome'])) {
            $query->where('nome', 'like', '%'.$filtros['nome'].'%');
        }
        if (! empty($filtros['chassi'])) {
            $query->where('chassi', 'like', '%'.$filtros['chassi'].'%');
        }
        if (! empty($filtros['cor'])) {
            $query->where('cor', 'like', '%'.$filtros['cor'].'%');
        }
        if (! empty($filtros['modelo_id_exato'])) {
            $query->where('modelo_id', (int) $filtros['modelo_id_exato']);
        } elseif (! empty($filtros['modelo_ids']) && is_array($filtros['modelo_ids'])) {
            $modeloIds = array_values(array_filter(array_map('intval', $filtros['modelo_ids'])));
            if (! empty($modeloIds)) {
                $query->whereIn('modelo_id', $modeloIds);
            }
        } elseif (! empty($filtros['modelo'])) {
            $query->whereHas('modelo', fn ($q) => $q->where('nome', 'like', '%'.$filtros['modelo'].'%'));
        }
        if (! empty($filtros['marca_id'])) {
            $query->where('marca_id', (int) $filtros['marca_id']);
        }
        if (! empty($filtros['grupo_frota_id'])) {
            $query->where('grupo_frota_id', (int) $filtros['grupo_frota_id']);
        }
        if (! empty($filtros['tipo_frota_id'])) {
            $query->where('tipo_frota_id', (int) $filtros['tipo_frota_id']);
        }
        if (isset($filtros['ativo']) && $filtros['ativo'] !== '') {
            $query->where('ativo', $filtros['ativo'] === '1' ? 'S' : 'N');
        }
        if (! empty($filtros['ano_de'])) {
            $query->whereHas('modelo', fn ($modelo) => $modelo->where('ano_modelo', '>=', (int) $filtros['ano_de']));
        }
        if (! empty($filtros['ano_ate'])) {
            $query->whereHas('modelo', fn ($modelo) => $modelo->where('ano_modelo', '<=', (int) $filtros['ano_ate']));
        }
        if (! empty($filtros['kms_ate'])) {
            $query->where('kms', '<=', (int) $filtros['kms_ate']);
        }

        return $query->get();
    }

    public function getVeiculosDisponiveisParaCheckin($estacao_id)
    {
        // Falta corrigir essa lógica
        return $this->model->where('local_disponibilidade', $estacao_id)
            ->where('disponivel', 'S')
            ->get();
    }

    private function scopeStatusVeiculoDisponivel($query)
    {
        $idsDisponiveis = $this->getStatusVeiculoDisponivelIds();

        if (empty($idsDisponiveis)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('status_veiculo_id', $idsDisponiveis);
    }

    public function getStatusVeiculoDisponivelIds(): array
    {
        if ($this->statusVeiculoDisponivelIds !== null) {
            return $this->statusVeiculoDisponivelIds;
        }

        $query = DB::table('status_veiculos')->select('id', 'nome');

        if (SchemaCache::hasColumn('status_veiculos', 'ativo')) {
            $query->where('ativo', 'S');
        }

        if (SchemaCache::hasColumn('status_veiculos', 'empresa_id')) {
            $empresaId = $this->currentEmpresaId();
            if ($empresaId !== null) {
                $query->where('empresa_id', $empresaId);
            }
        }

        $status = $query->get();

        $ids = [];
        foreach ($status as $item) {
            $nome = mb_strtolower(trim((string) $item->nome));
            if (in_array($nome, ['ativo', 'disponivel'], true)) {
                $ids[] = (int) $item->id;
            }
        }

        if (empty($ids)) {
            $fallback = DB::table('status_veiculos')->select('id');

            if (SchemaCache::hasColumn('status_veiculos', 'empresa_id')) {
                $empresaId = $this->currentEmpresaId();
                if ($empresaId !== null) {
                    $fallback->where('empresa_id', $empresaId);
                }
            }

            $ids = $fallback
                ->where('id', 1)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->toArray();
        }

        $this->statusVeiculoDisponivelIds = array_values(array_unique($ids));

        return $this->statusVeiculoDisponivelIds;
    }
}
