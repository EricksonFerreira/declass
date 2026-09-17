<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Actions\Clockify\Tag\ListarTagsAction;
use App\Actions\Clockify\Tag\SaveTagAction;
use App\Actions\Clockify\Tag\UpdateTagAction;
use App\Actions\Clockify\Tag\DeleteTagAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyTagController extends Controller
{
    public function index(Request $request, ListarTagsAction $action): View
    {
        $tags = $action->handle($request->search);

        return view('clockify.tags', [
            'titulo' => 'Tags',
            'tags' => $tags,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Tags', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveTagAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.tags.index')
            ->with('success', 'Tag criada com sucesso!');
    }

    public function update(Request $request, $id, UpdateTagAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.tags.index')
            ->with('success', 'Tag atualizada com sucesso!');
    }

    public function destroy($id, DeleteTagAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.tags.index')
            ->with('success', 'Tag removida com sucesso!');
    }
}
