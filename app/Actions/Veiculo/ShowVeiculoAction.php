<?php

namespace App\Actions\Veiculo;

use App\DTO\BreadCrumbItem;
use App\Enums\StatusReservaEnum;
use App\Models\Movimentacao;
use App\Models\Reserva;
use App\Services\BreadcrumbService;
use App\Services\DisponibilidadeVeiculoService;
use App\Services\EstacaoService;
use App\Services\VeiculoService;
use App\Support\SchemaCache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShowVeiculoAction
{
    public function __construct(
        private readonly VeiculoService $veiculoService,
        private readonly BreadcrumbService $breadcrumbService,
        private readonly DisponibilidadeVeiculoService $disponibilidadeVeiculoService,
        private readonly EstacaoService $estacaoService,
    ) {}

    public function handle($id): array
    {
        $dispMes = (int) request()->query('disp_mes', date('n'));
        $dispAno = (int) request()->query('disp_ano', date('Y'));
        $dispEstacaoId = (int) request()->query('disp_estacao_id', 0);
        if ($dispMes < 1 || $dispMes > 12) $dispMes = (int) date('n');
        if ($dispAno < 2024 || $dispAno > ((int) date('Y') + 1)) $dispAno = (int) date('Y');
        if ($dispEstacaoId < 0) $dispEstacaoId = 0;

        $veiculo = $this->veiculoService->getById($id, ['tipoFrota','grupoFrota','marca','modelo','statusVeiculo','localDisponibilidade','reservas.statusReserva','reservas.codigoReserva','veiculoDanos.dano.categoria','veiculoDanos.reservaCodigo','veiculoDanos.reserva.codigoReserva','veiculoDanos.contratoCheckin.reserva.codigoReserva','ordensServico','manutencoesPreventivas' => fn ($q) => $q->with(['item', 'ordemServico:id,numero,status'])->orderByDesc('data_execucao')->orderByDesc('id'),'pendenciasManutencaoPreventiva' => fn ($q) => $q->with('item')->orderByDesc('id'),'documentacoesVeiculo' => fn ($q) => $q->with('categoriaFinanceira')->orderByDesc('id'),'multas' => fn ($q) => $q->with(['reserva.codigoReserva', 'responsavelLancamento:id,nome'])->orderByDesc('id'),'sinistros' => fn ($q) => $q->orderByDesc('id'),'movimentacoes' => fn ($q) => $q->with(['usuario:id,nome','tipoMovimentacao:id,nome','statusMovimentacao:id,nome','localOrigem:id,nome','localDestino:id,nome','reserva.codigoReserva','ordemServico:id,numero,status'])->orderByDesc('id')]);
        if (! $veiculo) abort(404, 'Veiculo nao encontrado.');

        $empresaContext = app(\App\Support\EmpresaContext::class);
        $empresaId = Auth::user()?->isAdministrador() ? $empresaContext->empresaId() : ($empresaContext->empresaId() ?? Auth::user()?->empresa_id);
        $reservaBloqueioHoras = 0;
        if ($empresaId && SchemaCache::hasTable('empresas') && SchemaCache::hasColumn('empresas', 'reserva_bloqueio_pos_reserva_horas')) {
            $reservaBloqueioHoras = (int) (DB::table('empresas')->where('id', (int) $empresaId)->value('reserva_bloqueio_pos_reserva_horas') ?? 0);
        }

        $resultado = $this->disponibilidadeVeiculoService->montarGradeMensal(collect([$veiculo]), $dispAno, $dispMes);
        $dispGrade = $resultado['grade'][$veiculo->id] ?? [];
        $dispResumo = $resultado['resumoVeiculos'][$veiculo->id] ?? ['percentual'=>0,'minutos_total'=>0,'minutos_disponiveis'=>0,'status_mes'=>'off','dias_parciais'=>0,'dias_indisponiveis'=>0];
        $dispStats = ['ok'=>0,'partial'=>0,'reserved'=>0,'overdue'=>0,'off'=>0,'pending'=>0];
        foreach ($dispGrade as $cell) { $tipo = $cell['tipo'] ?? 'off'; $dispStats[$tipo] = ($dispStats[$tipo] ?? 0) + 1; }

        $dispInicioMes = Carbon::create($dispAno, $dispMes, 1)->startOfDay();
        $dispFimMesExclusivo = $dispInicioMes->copy()->addMonth()->startOfDay();
        $dispInicioMesComFolga = $dispInicioMes->copy()->subHours($reservaBloqueioHoras);
        $planoManutencao = $veiculo->planoManutencaoPreventiva;
        $dispReservas = Reserva::query()->select(['id','veiculo_id','data_inicio','data_fim','status_reserva_id','user_id'])->with(['statusReserva:id,nome'])->where('veiculo_id',(int)$veiculo->id)->whereIn('status_reserva_id',[StatusReservaEnum::Pendente->value,StatusReservaEnum::Confirmada->value,StatusReservaEnum::Checkin->value,StatusReservaEnum::Checkout->value,StatusReservaEnum::NoShow->value])->where('data_inicio','<',$dispFimMesExclusivo->toDateTimeString())->where('data_fim','>',$dispInicioMesComFolga->toDateTimeString())->orderBy('data_inicio')->get();
        $dispMovimentacoes = Movimentacao::query()->with(['usuario:id,nome','tipoMovimentacao:id,nome','statusMovimentacao:id,nome,cor','localOrigem:id,nome','localDestino:id,nome','reserva:id','reserva.codigoReserva:id,reserva_id,codigo','ordemServico:id,numero,status'])->select(['id','nome','veiculo_id','tipo_movimentacao_id','local_origem_id','local_destino_id','ordem_servico_id','data_saida','data_registro','data_chegada_prevista','data_chegada_real','km_saida','km_chegada','status_movimentacao_id','reserva_id','observacoes','user_id'])->where('veiculo_id',(int)$veiculo->id)->whereIn('status_movimentacao_id',[1,2,5,6,7])->where(fn($q)=>$q->whereNull('data_saida')->orWhere('data_saida','<',$dispFimMesExclusivo->toDateTimeString()))->where(fn($q)=>$q->whereNull('data_chegada_real')->orWhere('data_chegada_real','>=',$dispInicioMes->toDateTimeString()))->orderByDesc('id')->get();

        $dispEstacoes = $this->estacaoService->getAllAtivos('id','nome','nome','asc');
        $finReceita = (float) $veiculo->reservas->sum('valor_total_final');
        $finCustoMultas = (float) $veiculo->multas->where('destino_cobranca', 'empresa')->sum('valor_final');
        $finCustoSinistros = (float) $veiculo->sinistros->sum('valor_custo_empresa');
        $finLucroLiquido = $finReceita - $finCustoMultas - $finCustoSinistros;
        $finValorCompra = (float) ($veiculo->valor_compra ?? 0);
        $finValorFipe = (float) ($veiculo->valor_fipe ?? 0);
        $finValorAtualEstimado = $veiculo->valor_atual_estimado !== null ? (float) $veiculo->valor_atual_estimado : null;
        $finDepreciacaoAcumulada = $veiculo->valor_depreciacao_acumulada !== null ? (float) $veiculo->valor_depreciacao_acumulada : null;
        $finDiferencaFipeCompra = $veiculo->diferenca_fipe_compra !== null ? (float) $veiculo->diferenca_fipe_compra : null;
        $finPctDepreciacao = $veiculo->percentual_depreciacao_acumulada !== null ? (float) $veiculo->percentual_depreciacao_acumulada : null;
        $finTemProtecaoDepreciacao = (bool) $veiculo->tem_protecao_depreciacao;
        $finValorProtegidoDepreciacao = $veiculo->valor_protegido_depreciacao !== null ? (float) $veiculo->valor_protegido_depreciacao : null;
        $finRentabilidade = ($finValorCompra > 0) ? ($finLucroLiquido / $finValorCompra * 100) : null;
        $finPaybackMeses = null;
        if ($finValorCompra > 0 && ! empty($veiculo->data_compra)) {
            $mesesUso = (int) Carbon::parse($veiculo->data_compra)->diffInMonths(Carbon::now());
            if ($mesesUso > 0 && $finLucroLiquido > 0) $finPaybackMeses = (int) ceil($finValorCompra / ($finLucroLiquido / $mesesUso));
        }

        $this->garantirRegistroInicialManutencaoPreventiva($veiculo);
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([new BreadCrumbItem('Home', route('home')), new BreadCrumbItem('Veiculos', route('veiculo.index')), new BreadCrumbItem($veiculo->nome ?? 'Detalhes')]);
        $titulo = 'Veículo: '.($veiculo->nome ?? '#'.$veiculo->id);

        return compact('veiculo','breadcrumb','titulo','dispMes','dispAno','dispEstacaoId','dispDiasNoMes','dispFirstDow','dispGrade','dispStats','dispResumo','dispReservas','dispMovimentacoes','reservaBloqueioHoras','dispEstacoes','finReceita','finCustoMultas','finCustoSinistros','finLucroLiquido','finValorCompra','finValorFipe','finValorAtualEstimado','finDepreciacaoAcumulada','finDiferencaFipeCompra','finPctDepreciacao','finTemProtecaoDepreciacao','finValorProtegidoDepreciacao','finRentabilidade','finPaybackMeses','planoManutencao');
    }

    private function garantirRegistroInicialManutencaoPreventiva($veiculo): void {}
}
