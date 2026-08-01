<?php

namespace App\Services;

use App\Models\Multa;
use App\Models\MultaHistorico;
use App\Models\Reserva;
use App\Models\Veiculo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Support\ArquivoAnexoHelper;

class MultaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Multa $multa)
    {
        $this->model = $multa;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Multa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Multa');
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
     * Cria uma multa e registra histórico inicial.
     */
    public function criar(array $dados): Multa
    {
        $dados['responsavel_lancamento_id'] = Auth::id();
        $this->injectEmpresaId($dados);

        // Auto-vincular reserva: se não informada mas há veículo e data de infração
        if (empty($dados['reserva_id']) && !empty($dados['veiculo_id']) && !empty($dados['data_hora_infracao'])) {
            $reservaDetectada = $this->detectarReserva((int) $dados['veiculo_id'], $dados['data_hora_infracao']);
            if ($reservaDetectada) {
                $dados['reserva_id'] = $reservaDetectada->id;
                // Preencher cliente/condutor da reserva se não vieram no form
                if (empty($dados['cliente_id'])) {
                    $dados['cliente_id'] = $reservaDetectada->user_id;
                }
            }
        }

        /** @var Multa $multa */
        $multa = $this->model->create($dados);

        $msg = 'Multa criada.';
        if (!empty($dados['reserva_id'])) {
            $msg .= " Vinculada automaticamente à Reserva #{$dados['reserva_id']}.";
        }
        $this->registrarHistorico($multa, null, null, null, $msg);

        return $multa;
    }

    /**
     * Detecta se há uma reserva ativa para o veículo na data/hora informada.
     */
    private function detectarReserva(int $veiculoId, string $dataHora): ?Reserva
    {
        $veiculo = Veiculo::find($veiculoId);
        if (!$veiculo || !$veiculo->modelo_id) {
            return null;
        }

        try {
            $dt = Carbon::parse($dataHora);
        } catch (\Throwable) {
            return null;
        }

        return Reserva::where('modelo_id', $veiculo->modelo_id)
            ->where('data_inicio', '<=', $dt->toDateTimeString())
            ->where('data_fim', '>=', $dt->toDateString())
            ->orderByDesc('data_inicio')
            ->first();
    }

    /**
     * Atualiza uma multa e audita campos alterados.
     */
    public function atualizar(int $id, array $dados): ?Multa
    {
        /** @var Multa|null $multa */
        $multa = $this->applyEmpresaScope($this->model->newQuery())->find($id);

        if (!$multa) {
            return null;
        }

        $camposAuditados = [
            'status_multa'    => 'Status da Multa',
            'status_desconto' => 'Status de Desconto',
            'valor_desconto'  => 'Valor de Desconto',
            'valor_final'     => 'Valor Final',
            'observacao'      => 'Observacao',
        ];

        foreach ($camposAuditados as $campo => $label) {
            if (isset($dados[$campo]) && (string) $multa->$campo !== (string) $dados[$campo]) {
                $this->registrarHistorico(
                    $multa,
                    $campo,
                    (string) ($multa->$campo ?? ''),
                    (string) ($dados[$campo] ?? ''),
                    "Campo '{$label}' alterado."
                );
            }
        }

        $multa->update($dados);

        return $multa->fresh();
    }

    /**
     * Salva anexo e retorna o path armazenado.
     */
    public function salvarAnexo(\Illuminate\Http\UploadedFile $arquivo, int $multaId): string
    {
        return (string) (ArquivoAnexoHelper::armazenarRastreado($arquivo, "multas/{$multaId}/anexos", [
            'modulo' => 'multa',
            'entidade' => Multa::class,
            'entidade_id' => $multaId,
            'campo' => 'anexo',
            'contexto' => 'multa.anexo',
            'meta' => [
                'tipo_registro' => 'multa',
            ],
        ])['path'] ?? '');
    }

    /**
     * Exporta multas com filtros como CSV (stream).
     */
    public function exportarCsv(array $filtros): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['reserva', 'veiculo', 'cliente', 'empresa'])
            ->orderBy('id', 'desc');

        $this->aplicarFiltros($query, $filtros);

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="multas_' . now()->format('Ymd_His') . '.csv"',
        ];

        $colunas = [
            'ID', 'Reserva', 'Placa', 'Cliente', 'Orgao Autuador', 'Numero Auto',
            'Data Infracao', 'Vencimento', 'Valor Original', 'Valor Desconto',
            'Valor Final', 'Pontos', 'Status Multa', 'Status Desconto',
        ];

        return response()->stream(function () use ($query, $colunas) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $colunas, ';');

            $query->chunk(200, function ($multas) use ($handle) {
                foreach ($multas as $multa) {
                    fputcsv($handle, [
                        $multa->id,
                        $multa->reserva_id ?? '',
                        $multa->placa_momento ?? '',
                        $multa->cliente->nome ?? '',
                        $multa->orgao_autuador ?? '',
                        $multa->numero_auto ?? '',
                        $multa->data_hora_infracao?->format('d/m/Y H:i') ?? '',
                        $multa->data_vencimento?->format('d/m/Y') ?? '',
                        number_format((float) $multa->valor_original, 2, ',', '.'),
                        $multa->valor_desconto !== null ? number_format((float) $multa->valor_desconto, 2, ',', '.') : '',
                        number_format((float) $multa->valor_final, 2, ',', '.'),
                        $multa->pontos ?? '',
                        $multa->label_status_multa,
                        $multa->label_status_desconto,
                    ], ';');
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    private function aplicarFiltros($query, array $filtros): void
    {
        if (!empty($filtros['data_inicio'])) {
            $query->where('data_hora_infracao', '>=', $filtros['data_inicio']);
        }
        if (!empty($filtros['data_fim'])) {
            $query->where('data_hora_infracao', '<=', $filtros['data_fim'] . ' 23:59:59');
        }
        if (!empty($filtros['reserva_id'])) {
            $query->where('reserva_id', $filtros['reserva_id']);
        }
        if (!empty($filtros['placa'])) {
            $query->where('placa_momento', 'like', '%' . $filtros['placa'] . '%');
        }
        if (!empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }
        if (!empty($filtros['status_multa'])) {
            $query->where('status_multa', $filtros['status_multa']);
        }
        if (!empty($filtros['status_desconto'])) {
            $query->where('status_desconto', $filtros['status_desconto']);
        }
        if (!empty($filtros['vencimento_ate'])) {
            $query->where('data_vencimento', '<=', $filtros['vencimento_ate']);
        }
        if (!empty($filtros['veiculo_id'])) {
            $query->where('veiculo_id', $filtros['veiculo_id']);
        }
    }

    private function registrarHistorico(
        Multa $multa,
        ?string $campo,
        ?string $anterior,
        ?string $novo,
        string $descricao = ''
    ): void {
        MultaHistorico::create([
            'multa_id'       => $multa->id,
            'user_id'        => Auth::id(),
            'campo_alterado' => $campo,
            'valor_anterior' => $anterior,
            'valor_novo'     => $novo,
            'descricao'      => $descricao,
        ]);
    }
}
