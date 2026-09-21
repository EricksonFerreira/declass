<?php

namespace App\Http\Controllers;

use App\Actions\Tutorial\DeleteTutorialAction;
use App\Actions\Tutorial\DeleteTutorialCategoriaAction;
use App\Actions\Tutorial\SaveTutorialAction;
use App\Actions\Tutorial\SaveTutorialCategoriaAction;
use App\Http\Requests\Tutorial\SaveTutorialCategoriaRequest;
use App\Http\Requests\Tutorial\SaveTutorialRequest;
use App\Models\Tutorial;
use App\Models\TutorialCategoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TutorialController extends Controller
{
    /**
     * Pagina publica (autenticada) de tutoriais — cards por categoria,
     * no estilo do XpandCars. Superadmin ve tambem os internos.
     */
    public function index(): View
    {
        $tutoriais = Tutorial::with('categoria')
            ->where('ativo', true)
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->where('visibilidade', Tutorial::VISIBILIDADE_TODOS))
            ->orderBy('ordem')
            ->orderBy('titulo')
            ->get();

        $categorias = TutorialCategoria::where('ativo', true)
            ->orderBy('ordem')
            ->orderBy('nome')
            ->get()
            ->filter(fn ($c) => $tutoriais->contains('tutorial_categoria_id', $c->id))
            ->values();

        return view('tutoriais.index', [
            'titulo' => 'Tutoriais',
            'tutoriais' => $tutoriais,
            'categorias' => $categorias,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Tutoriais', 'active' => true],
            ],
        ]);
    }

    /**
     * Configuracao de tutoriais (somente superadmin): categorias + lista
     * de tutoriais com atalhos de edicao.
     */
    public function gerenciar(): View
    {
        return view('tutoriais.gerenciar', [
            'titulo' => 'Configuração de Tutoriais',
            'categorias' => TutorialCategoria::withCount('tutoriais')
                ->orderBy('ordem')->orderBy('nome')->get(),
            'tutoriais' => Tutorial::with('categoria')
                ->orderBy('ordem')->orderBy('titulo')->get(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Configurações'],
                ['name' => 'Tutoriais', 'active' => true],
            ],
        ]);
    }

    public function create(): View
    {
        return view('tutoriais.form', [
            'titulo' => 'Novo Tutorial',
            'tutorial' => null,
            'categorias' => TutorialCategoria::where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(),
            'breadcrumb' => $this->breadcrumbForm('Novo Tutorial'),
        ]);
    }

    public function store(SaveTutorialRequest $request, SaveTutorialAction $action): RedirectResponse
    {
        $action->handle(
            $request->validated(),
            video: $request->file('video'),
            capa: $request->file('capa')
        );

        return redirect()->route('tutoriais.gerenciar')->with('success', 'Tutorial cadastrado.');
    }

    public function edit(Tutorial $tutorial): View
    {
        return view('tutoriais.form', [
            'titulo' => 'Editar Tutorial',
            'tutorial' => $tutorial,
            'categorias' => TutorialCategoria::where('ativo', true)->orderBy('ordem')->orderBy('nome')->get(),
            'breadcrumb' => $this->breadcrumbForm('Editar Tutorial'),
        ]);
    }

    public function update(SaveTutorialRequest $request, Tutorial $tutorial, SaveTutorialAction $action): RedirectResponse
    {
        $action->handle(
            $request->validated(),
            $tutorial,
            $request->file('video'),
            $request->file('capa')
        );

        return redirect()->route('tutoriais.gerenciar')->with('success', 'Tutorial atualizado.');
    }

    public function destroy(Tutorial $tutorial, DeleteTutorialAction $action): RedirectResponse
    {
        $action->handle($tutorial);

        return redirect()->route('tutoriais.gerenciar')->with('success', 'Tutorial excluído.');
    }

    public function storeCategoria(SaveTutorialCategoriaRequest $request, SaveTutorialCategoriaAction $action): RedirectResponse
    {
        $action->handle($request->validated());

        return redirect()->route('tutoriais.gerenciar')->with('success', 'Categoria criada.');
    }

    public function updateCategoria(SaveTutorialCategoriaRequest $request, TutorialCategoria $categoria, SaveTutorialCategoriaAction $action): RedirectResponse
    {
        $action->handle($request->validated(), $categoria);

        return redirect()->route('tutoriais.gerenciar')->with('success', 'Categoria atualizada.');
    }

    public function destroyCategoria(TutorialCategoria $categoria, DeleteTutorialCategoriaAction $action): RedirectResponse
    {
        $action->handle($categoria);

        return redirect()->route('tutoriais.gerenciar')->with('success', 'Categoria excluída. Os tutoriais foram mantidos sem categoria.');
    }

    private function breadcrumbForm(string $atual): array
    {
        return [
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Configurações'],
            ['name' => 'Tutoriais', 'url' => route('tutoriais.gerenciar')],
            ['name' => $atual, 'active' => true],
        ];
    }
}
