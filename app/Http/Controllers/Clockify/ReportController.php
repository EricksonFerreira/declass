<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Report\ExportReportAction;
use App\Actions\Clockify\Report\IndexAction;
use App\Actions\Clockify\Report\StoreMembroAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ReportController extends Controller
{
    public function index(IndexAction $action)
    {
        return $action->handle();
    }

    public function membros()
    {
        $users = User::paginate(15);

        return View::make('relatorios.membros', ['users' => $users, 'breadcrumb' => []]);
    }

    // New method to store a new member (user)
    public function storeMembro(Request $request, StoreMembroAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        $action->handle($validated);

        return redirect()->route('relatorios.membros')->with('success', 'Membro criado com sucesso.');
    }

    public function destroyMembro($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('relatorios.membros')->with('success', 'Membro excluído com sucesso.');
    }

    public function horasTrabalhadas()
    {
        return View::make('relatorios.horas-trabalhadas');
    }

    public function export(Request $request, ExportReportAction $action)
    {
        if ($request->isMethod('post')) {
            return $action->execute(
                $request->input('data_inicio'),
                $request->input('data_fim'),
                $request->input('user_ids', []),
                $request->input('report_type')
            );
        }
        $users = User::all();

        return View::make('relatorios.export', ['users' => $users]);
    }
}
