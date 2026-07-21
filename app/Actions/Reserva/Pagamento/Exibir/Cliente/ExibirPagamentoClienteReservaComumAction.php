<?php

namespace App\Actions\Reserva\Pagamento\Exibir\Cliente;

use App\Actions\Reserva\Pagamento\ExibirFormularioPagamentoAction;
use App\DTO\BreadCrumbItem;
use App\Models\Reserva;
use App\Services\BreadcrumbService;
use Illuminate\Support\Facades\Auth;

class ExibirPagamentoClienteReservaComumAction
{
    public function __construct(
        private ExibirFormularioPagamentoAction $exibirFormularioPagamentoAction,
        private BreadcrumbService $breadcrumbService,
    ) {}

    public function handle(Reserva $reserva): mixed
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ((int) $reserva->user_id !== (int) $user->id && ! ($user->isAdministrador() ?? false)) {
            return redirect()->route('reserva.minhas')->with('error', 'Reserva não encontrada.');
        }

        $reserva->loadMissing(['codigoReserva', 'usuario', 'grupoFrota', 'modelos', 'estacaoRetirada', 'estacaoDevolucao', 'opcionais', 'cupomDesconto', 'transacoes']);

        $codigo = $reserva->codigoReserva?->codigo ?? ('RES-'.str_pad((string) $reserva->id, 6, '0', STR_PAD_LEFT));
        $data = $this->exibirFormularioPagamentoAction->handle($reserva);

        if (($data['saldoPendente'] ?? 0) <= 0.009) {
            return redirect()
                ->route('reserva.minhas.show', $codigo)
                ->with('success', 'Esta reserva já se encontra com o pagamento totalmente quitado.');
        }

        $titulo = 'Pagamento da reserva';
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumb([
            new BreadCrumbItem('Home', route('home')),
            new BreadCrumbItem('Minhas Reservas', route('reserva.minhas')),
            new BreadCrumbItem('Reserva Nº '.$codigo, route('reserva.minhas.show', $codigo)),
            new BreadCrumbItem('Efetuar Pagamento'),
        ]);

        return view('reserva.efetuar-pagamento.efetuar-pagamento-minhas', compact(
            'reserva', 'breadcrumb', 'titulo'
        ) + $data);
    }
}
