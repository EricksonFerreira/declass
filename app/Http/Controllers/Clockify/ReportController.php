<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Report\ExportReportAction;
use App\Actions\Clockify\Report\HorasTrabalhadasAction;
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
        $search = request('search');
        $users = User::where('empresa_id', auth()->user()->empresa_id)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        $breadcrumb = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Análise'],
            ['name' => 'Relatórios', 'url' => route('relatorios.index')],
            ['name' => 'Membros', 'active' => true],
        ];

        return View::make('relatorios.membros', [
            'users' => $users,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    // New method to store a new member (user)
    public function storeMembro(Request $request, StoreMembroAction $action)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'cargo' => 'required|string|max:255',
        ]);
        $validated['password'] = 'Senh@123';
        $action->handle($validated);

        return redirect()->route('relatorios.membros')->with('success', 'Membro criado com sucesso.');
    }

    public function updateMembro(Request $request, $id)
    {
        $user = User::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'cargo' => 'required|string|max:255',
        ]);

        $user->update([
            'nome' => $validated['name'],
            'email' => $validated['email'],
            'cargo' => $validated['cargo'],
        ]);

        return redirect()->route('relatorios.membros')->with('success', 'Membro atualizado com sucesso.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make('Senh@123'),
            'forcar_troca_senha' => true,
        ]);

        return redirect()->route('relatorios.membros')->with('success', 'Senha do membro resetada com sucesso para "Senh@123". Ele será forçado a mudá-la no primeiro acesso.');
    }

    public function destroyMembro($id)
    {
        $user = User::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $user->delete();

        return redirect()->route('relatorios.membros')->with('success', 'Membro excluído com sucesso.');
    }

    public function horasTrabalhadas(HorasTrabalhadasAction $action)
    {
        $dados = $action->handle();

        $breadcrumb = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Análise'],
            ['name' => 'Relatórios', 'url' => route('relatorios.index')],
            ['name' => 'Horas Trabalhadas', 'active' => true],
        ];

        return View::make('relatorios.horas-trabalhadas', [
            'breadcrumb' => $breadcrumb,
            'linhas' => $dados['linhas'],
            'totais' => $dados['totais'],
            'chart' => $dados['chart'],
        ]);
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
        $users = User::where('empresa_id', auth()->user()->empresa_id)
            ->where(function ($q) {
                $q->whereNull('tipo_usuario_id')->orWhere('tipo_usuario_id', '!=', 1);
            })
            ->orderBy('nome')
            ->get();
        $breadcrumb = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Análise'],
            ['name' => 'Relatórios', 'url' => route('relatorios.index')],
            ['name' => 'Exportar CSV', 'active' => true],
        ];

        return View::make('relatorios.export', ['users' => $users, 'breadcrumb' => $breadcrumb]);
    }
}
