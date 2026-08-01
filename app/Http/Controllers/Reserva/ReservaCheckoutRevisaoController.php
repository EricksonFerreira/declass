<?php

namespace App\Http\Controllers\Reserva;

use App\Actions\Reserva\Checkout\Dano\Adicionar\AdicionarDanoReservaComumAction;
use App\Actions\Reserva\Checkout\Revisao\Concluir\ConcluirRevisaoCheckoutReservaComumAction;
use App\Actions\Reserva\Checkout\Revisao\Exibir\ExibirRevisaoCheckoutReservaComumAction;
use App\Actions\Reserva\Checkout\Revisao\Invalidar\InvalidarCheckoutRevisaoAction;
use App\Actions\Reserva\Checkout\Revisao\Ocorrencia\RegistrarOcorrenciaCheckoutReservaComumAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reserva\AdicionarDanoCheckoutRevisaoRequest;
use App\Http\Requests\Reserva\ConcluirRevisaoCheckoutRequest;
use App\Http\Requests\Reserva\InvalidarCheckoutRevisaoRequest;
use App\Http\Requests\Reserva\RegistrarOcorrenciaCheckoutRequest;
use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class ReservaCheckoutRevisaoController extends Controller
{
    public function revisar(Request $request, $codigo, ReservaResolver $reservaResolver, ExibirRevisaoCheckoutReservaComumAction $action)
    {
        return $action->handle($reservaResolver->resolver($codigo), $request);
    }

    public function concluir(ConcluirRevisaoCheckoutRequest $request, $codigo, ReservaResolver $reservaResolver, ConcluirRevisaoCheckoutReservaComumAction $action)
    {
        $resolvida = $reservaResolver->resolver($codigo);
        $action->handle($resolvida->registro, $request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Revisão concluída com sucesso.']);
        }

        return redirect()
            ->route('reserva.show', $resolvida->codigo)
            ->with('success', 'Revisão concluída com sucesso.');
    }

    public function ocorrencia(
        RegistrarOcorrenciaCheckoutRequest $request,
        $codigo,
        ReservaResolver $reservaResolver,
        RegistrarOcorrenciaCheckoutReservaComumAction $action
    ) {
        $resolvida = $reservaResolver->resolver($codigo);
        $dados = $request->validated();

        $action->handle($resolvida->registro, $dados);

        if (in_array(($dados['modo_cobranca'] ?? ''), ['cobrar', 'nao_cobrar', 'remover_cobranca'], true)) {
            $mensagem = ($dados['modo_cobranca'] ?? '') === 'cobrar'
                ? 'Cobrança salva com sucesso.'
                : 'Cobrança removida com sucesso.';

            return redirect()
                ->route('reserva.checkout.revisao', $resolvida->codigo)
                ->with('success', $mensagem);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Ocorrência processada com sucesso.']);
        }

        return redirect()
            ->route('reserva.show', $resolvida->codigo)
            ->with('success', 'Ocorrência processada com sucesso.');
    }

    public function invalidar(InvalidarCheckoutRevisaoRequest $request, $codigo, ReservaResolver $reservaResolver, InvalidarCheckoutRevisaoAction $action)
    {
        $resolvida = $reservaResolver->resolver($codigo);
        $action->handle($resolvida->registro, $request->validated()['justificativa']);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Checkout invalidado com sucesso.']);
        }

        return redirect()
            ->route('reserva.show', $resolvida->codigo)
            ->with('warning', 'Checkout invalidado com sucesso.');
    }

    public function adicionarDano(
        AdicionarDanoCheckoutRevisaoRequest $request,
        $codigo,
        ReservaResolver $reservaResolver,
        AdicionarDanoReservaComumAction $action
    ) {
        $resolvida = $reservaResolver->resolver($codigo);
        $reserva = $resolvida->registro;
        $reservaCodigoId = (int) ($reserva->codigoReserva?->id ?? 0);

        $dano = $action->handle($request->validated(), (int) $reserva->id, $reservaCodigoId, $request);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'dano' => $dano]);
        }

        return redirect()
            ->route('reserva.checkout.revisao', $resolvida->codigo)
            ->with('success', 'Dano adicionado com sucesso.');
    }
}
