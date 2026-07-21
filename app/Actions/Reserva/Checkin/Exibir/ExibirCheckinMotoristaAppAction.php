<?php

namespace App\Actions\Reserva\Checkin\Exibir;

use App\DTO\BreadCrumbItem;
use App\DTO\Reserva\ReservaResolvida;
use App\Services\BreadcrumbService;
use App\Services\VeiculoService;
use App\Services\ModeloService;
use App\Services\MarcaService;
use App\Services\CategoriaDanoService;
use App\Models\Modelo;
use App\Models\Marca;
use App\Models\CategoriaDano;
use App\Models\ContratoCheckin;
use App\Http\Controllers\Reserva\ReservaHelpersTrait;
use Illuminate\Http\Request;

class ExibirCheckinMotoristaAppAction
{
    use ReservaHelpersTrait;

    public function __construct(
        private BreadcrumbService $breadcrumbService,
        private VeiculoService $veiculoService,
        private ModeloService $modeloService,
        private MarcaService $marcaService,
        private CategoriaDanoService $categoriaDanoService,
    ) {}

    public function handle(ReservaResolvida $reserva, Request $request): mixed
    {
        $registro = $reserva->registro;
        $codigo = $reserva->codigo;

        if (!$registro->pagamento_efetuado) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'Confirme o pagamento antes de abrir o check-in.');
        }

        if (empty(data_get($registro->documentos_json, 'termo_confirmacao.aceito_em'))) {
            return redirect()->route('reserva.show', $codigo)
                ->with('error', 'Aceite o termo de confirmacao antes de abrir o check-in.');
        }

        $contratoId = (int) data_get($registro->documentos_json, 'checkin.contrato_checkin_id', 0);
        $contratoExistente = $contratoId > 0
            ? ContratoCheckin::with('veiculo')->find($contratoId)
            : ContratoCheckin::with('veiculo')->where('reserva_id', $registro->id)->first();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Reservas', route('reserva.index')),
            new BreadCrumbItem('Reserva motorista APP #' . $registro->id, route('reserva.show', $codigo)),
            new BreadCrumbItem('Check-in'),
        ]);

        $veiculosDisponiveis = $this->veiculoService->listarAtivosParaOrdemServico(300);

        // Modelo IDs elegíveis para o plano/período de Motorista App
        $modeloIds = $this->modeloIdsDisponiveisMotoristaApp($registro);

        if (!empty($modeloIds)) {
            $modelos = Modelo::whereIn('id', $modeloIds)->where('ativo', 'S')->with('marcas')->orderBy('nome')->get();
        } else {
            $modeloIdsDoPlano = $veiculosDisponiveis->pluck('modelo_id')->filter()->unique()->all();
            $modelos = !empty($modeloIdsDoPlano)
                ? Modelo::whereIn('id', $modeloIdsDoPlano)->where('ativo', 'S')->with('marcas')->orderBy('nome')->get()
                : Modelo::where('ativo', 'S')->with('marcas')->orderBy('nome')->get();
        }

        $marcas = Marca::where('ativo', 'S')->orderBy('nome')->get();
        $categoriaDanos = CategoriaDano::where('ativo', 'S')->orderBy('nome')->get();

        return view('reserva.checkin-motorista-app', compact(
            'registro', 'breadcrumb', 'veiculosDisponiveis', 'modelos', 'marcas', 'categoriaDanos', 'contratoExistente'
        ) + [
            'reserva' => $registro,
            'codigoBreadcrumb' => $codigo,
            'isMotoristaAppCheckin' => true,
        ])->with('titulo', 'Check-in da reserva motorista APP #' . $registro->id);
    }
}
