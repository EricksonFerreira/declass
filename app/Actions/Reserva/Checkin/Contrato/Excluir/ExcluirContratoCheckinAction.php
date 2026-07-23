<?php

namespace App\Actions\Reserva\Checkin\Contrato\Excluir;

use App\Models\ContratoCheckin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExcluirContratoCheckinAction
{
    public function handle(Request $request, $id)
    {
        // A rota passa o código ou ID da reserva. Vamos resolver o contrato associado à reserva.
        $contrato = ContratoCheckin::where('id', $id)
            ->orWhere('reserva_id', $id)
            ->orWhereHas('reserva.codigoReserva', function ($q) use ($id) {
                $q->where('codigo', $id);
            })
            ->first();

        if (!$contrato) {
            return response()->json(['error' => 'Contrato nao encontrado.'], 404);
        }

        if ($contrato->pdf_path && Storage::disk('public')->exists($contrato->pdf_path)) {
            Storage::disk('public')->delete($contrato->pdf_path);
        }

        $contrato->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Contrato excluido com sucesso.']);
        }

        return redirect()->route('reserva.show', $id)
            ->with('success', 'Contrato de check-in excluído com sucesso.');
    }
}
