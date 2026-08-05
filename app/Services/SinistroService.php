<?php

namespace App\Services;

use App\Models\Reserva;
use App\Models\Sinistro;
use App\Models\SinistroHistorico;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use App\Support\ArquivoAnexoHelper;

class SinistroService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Sinistro $sinistro)
    {
        $this->model = $sinistro;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Sinistro) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Sinistro');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function queryScoped(): Builder
    {
        return $this->applyEmpresaScope($this->model->newQuery());
    }

    /**
     * Cria um sinistro e registra histórico inicial.
     */
    public function criar(array $dados): Sinistro
    {
        $dados['responsavel_id'] = Auth::id();
        $this->injectEmpresaId($dados);

        // Auto-vincular reserva: se não informada mas há veículo e data do ocorrido
        if (empty($dados['reserva_id']) && !empty($dados['veiculo_id']) && !empty($dados['data_hora_ocorrido'])) {
            $reservaDetectada = $this->detectarReserva((int) $dados['veiculo_id'], $dados['data_hora_ocorrido']);
            if ($reservaDetectada) {
                $dados['reserva_id'] = $reservaDetectada->id;
                if (empty($dados['cliente_id'])) {
                    $dados['cliente_id'] = $reservaDetectada->user_id;
                }
            }
        }

        /** @var Sinistro $sinistro */
        $sinistro = $this->model->create($dados);

        $msg = 'Sinistro registrado.';
        if (!empty($dados['reserva_id'])) {
            $msg .= " Vinculado automaticamente à Reserva #{$dados['reserva_id']}.";
        }
        $this->registrarHistorico($sinistro, null, null, null, $msg);

        return $sinistro;
    }

    /**
     * Detecta reserva ativa para o veículo na data/hora do ocorrido.
     */
    private function detectarReserva(int $veiculoId, string $dataHora): ?Reserva
    {
        try {
            $dt = Carbon::parse($dataHora);
        } catch (\Throwable) {
            return null;
        }

        $reserva = Reserva::query()
            ->with(['usuario', 'veiculo', 'codigoReserva'])
            ->where('veiculo_id', $veiculoId)
            ->where(function ($query) use ($dt) {
                $query
                    ->where(function ($q) use ($dt) {
                        $q->whereNotNull('checkin_realizado_em')
                            ->whereNotNull('checkout_realizado_em')
                            ->where('checkin_realizado_em', '<=', $dt)
                            ->where('checkout_realizado_em', '>=', $dt);
                    })
                    ->orWhere(function ($q) use ($dt) {
                        $q->whereNotNull('data_inicio')
                            ->whereNotNull('data_fim')
                            ->where('data_inicio', '<=', $dt)
                            ->where('data_fim', '>=', $dt);
                    });
            })
            ->orderByDesc('checkin_realizado_em')
            ->orderByDesc('data_inicio')
            ->first();

        if (! $reserva) {
            return null;
        }

        return $reserva;
    }

    /**
     * Atualiza um sinistro e audita campos alterados.
     */
    public function atualizar(int $id, array $dados): ?Sinistro
    {
        /** @var Sinistro|null $sinistro */
        $sinistro = $this->applyEmpresaScope($this->model->newQuery())->find($id);

        if (!$sinistro) {
            return null;
        }

        $camposAuditados = [
            'status'                         => 'Status',
            'tipo_sinistro'                  => 'Tipo',
            'valor_prejuizo_estimado'        => 'Valor Prejuízo Estimado',
            'valor_aprovado_seguradora'      => 'Valor Aprovado pela Seguradora',
            'valor_responsabilidade_cliente' => 'Valor Responsabilidade do Cliente',
            'numero_sinistro_seguradora'     => 'Nº Sinistro Seguradora',
            'observacoes'                    => 'Observações',
        ];

        foreach ($camposAuditados as $campo => $label) {
            if (isset($dados[$campo]) && (string) $sinistro->$campo !== (string) $dados[$campo]) {
                $this->registrarHistorico(
                    $sinistro,
                    $campo,
                    (string) ($sinistro->$campo ?? ''),
                    (string) ($dados[$campo] ?? ''),
                    "Campo '{$label}' alterado."
                );
            }
        }

        $sinistro->update($dados);

        return $sinistro->fresh();
    }

    /**
     * Salva um anexo e retorna o path.
     */
    public function salvarAnexo(UploadedFile $arquivo, int $sinistroId, string $tipo = 'anexo'): string
    {
        return (string) (ArquivoAnexoHelper::armazenarRastreado($arquivo, "sinistros/{$sinistroId}/{$tipo}", [
            'modulo' => 'sinistro',
            'entidade' => Sinistro::class,
            'entidade_id' => $sinistroId,
            'campo' => $tipo,
            'contexto' => 'sinistro.anexo',
            'meta' => [
                'tipo_anexo' => $tipo,
            ],
        ])['path'] ?? '');
    }

    /**
     * Exporta sinistros como CSV.
     */
    public function exportarCsv(array $filtros): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['veiculo', 'cliente', 'reserva'])
            ->orderBy('id', 'desc');

        $this->aplicarFiltros($query, $filtros);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="sinistros_' . now()->format('Ymd_His') . '.csv"',
        ];

        $colunas = [
            'ID',
            'Tipo',
            'Placa',
            'Veiculo',
            'Cliente',
            'Reserva',
            'Data Ocorrido',
            'Local',
            'Valor Cliente',
            'Valor Empresa',
            'Status',
        ];

        return response()->stream(function () use ($query, $colunas) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $colunas, ';');

            $query->chunk(200, function ($sinistros) use ($handle) {
                foreach ($sinistros as $s) {
                    fputcsv($handle, [
                        $s->id,
                        $s->label_tipo,
                        $s->placa_momento ?? '',
                        $s->veiculo?->nome ?? '',
                        $s->cliente?->nome ?? '',
                        $s->reserva_id ?? '',
                        $s->data_hora_ocorrido?->format('d/m/Y H:i') ?? '',
                        $s->local_ocorrido ?? '',
                        $s->valor_responsabilidade_cliente !== null ? number_format((float) $s->valor_responsabilidade_cliente, 2, ',', '.') : '',
                        $s->valor_custo_empresa !== null ? number_format((float) $s->valor_custo_empresa, 2, ',', '.') : '',
                        $s->label_status,
                    ], ';');
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_ocorrido', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_ocorrido', '<=', $filtros['data_fim'] . ' 23:59:59');
        }
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }
        if (!empty($filtros['placa'])) {
            $query->where('placa_momento', 'like', '%' . $filtros['placa'] . '%');
        }
        if (!empty($filtros['tipo_sinistro'])) {
            $query->where('tipo_sinistro', $filtros['tipo_sinistro']);
        }
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }
        if (!empty($filtros['reserva_id'])) {
            $query->where('reserva_id', $filtros['reserva_id']);
        }
    }

    private function registrarHistorico(
        Sinistro $sinistro,
        ?string $campo,
        ?string $anterior,
        ?string $novo,
        string $descricao = ''
    ): void {
        SinistroHistorico::create([
            'sinistro_id'    => $sinistro->id,
            'user_id'        => Auth::id(),
            'campo_alterado' => $campo,
            'valor_anterior' => $anterior,
            'valor_novo'     => $novo,
            'descricao'      => $descricao,
        ]);
    }
}

