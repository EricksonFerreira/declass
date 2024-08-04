<?php

namespace App\Http\Controllers;

use App\Services\VidaService;
use Illuminate\Http\Request;
use Controller;

class VidaController extends Controller
{
    protected $vidaService;

    public function __construct(VidaService $vidaService)
    {
        $this->vidaService = $vidaService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['campo1', 'campo2']); // Substitua 'campo1', 'campo2' pelos nomes dos campos que deseja filtrar
        $vidas = $this->vidaService->getAllWithFiltersAndPagination($filters);
        return view('vidas.index', compact('vidas'));
    }

    public function create()
    {
        return view('vidas.create');
    }

    public function store(Request $request)
    {
        $this->vidaService->create($request->all());
        return redirect()->route('vidas.index')->with('success', 'Adicionado com sucesso!');
    }

    public function show($id)
    {
        $vida = $this->vidaService->find($id); // Supondo que o método find() retorne o registro pelo ID
        return view('vidas.show', compact('vida'));
    }

    public function edit($id)
    {
        $vida = $this->vidaService->find($id);
        return view('vidas.edit', compact('vida'));
    }

    public function update(Request $request, $id)
    {
        $this->vidaService->update($request->all(), $id);
        return redirect()->route('vidas.index')->with('success', 'Modificado com sucesso!');
    }

    public function destroy($id)
    {
        $this->vidaService->delete($id); // Supondo que o método delete() remova o registro pelo ID
        return redirect()->route('vidas.index')->with('success', 'Apagado com sucesso!');
    }


}
