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
    public function __construct()
    {
        $this->middleware('can:relatorios.index')->only('index');
        $this->middleware('can:relatorios.membros')->only('membros');
        $this->middleware('can:relatorios.membros.store')->only('storeMembro');
        $this->middleware('can:relatorios.membros.update')->only('updateMembro');
        $this->middleware('can:relatorios.membros.reset-password')->only('resetPassword');
        $this->middleware('can:relatorios.membros.destroy')->only('destroyMembro');
        $this->middleware('can:relatorios.horas-trabalhadas')->only('horasTrabalhadas');
    }

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
        ]);

        $user->update([
            'nome' => $validated['name'],
            'email' => $validated['email'],
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
        abort_unless(
            $request->user()->hasPermissao('relatorios.export') || $request->user()->hasPermissao('relatorios.exportar'),
            403
        );

        $visibilidade = app(\App\Services\VisibilidadeService::class);
        $idsVisiveis = auth()->user()->idsVisiveis();

        if ($request->isMethod('post')) {
            $userIds = collect($request->input('user_ids', []))->map(fn ($id) => (int) $id);

            // Nao permite exportar dados fora do escopo de visibilidade.
            if ($idsVisiveis !== null) {
                $userIds = $userIds->filter(fn ($id) => in_array($id, $idsVisiveis, true))->values();

                if ($userIds->isEmpty()) {
                    $userIds = collect($idsVisiveis);
                }
            }

            return $action->execute(
                $request->input('data_inicio'),
                $request->input('data_fim'),
                $userIds->all(),
                $request->input('report_type')
            );
        }
        $users = $visibilidade->usuariosVisiveis(auth()->user())
            ->filter(fn ($u) => (int) $u->getRawOriginal('tipo_usuario_id') !== 1)
            ->sortBy('nome')
            ->values();
        $breadcrumb = [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Análise'],
            ['name' => 'Relatórios', 'url' => route('relatorios.index')],
            ['name' => 'Exportar CSV', 'active' => true],
        ];

        return View::make('relatorios.export', ['users' => $users, 'breadcrumb' => $breadcrumb]);
    }
}
