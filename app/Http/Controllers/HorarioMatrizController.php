<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorarioMatriz\StoreHorarioMatrizRequest;
use App\Models\Escola;
use App\Models\HorarioSlot;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\HorarioMatrizService;
use Illuminate\Http\Request;

/**
 * Matriz de horarios: define as faixas de horario (aulas por
 * dia/turno) que servem de grade vazia para as turmas.
 */
class HorarioMatrizController extends BaseController
{
    public function __construct(
        protected HorarioMatrizService $matrizService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Matriz de Horários';

        $this->middleware('can:horario_matriz.index')->only('index');
        $this->middleware('can:horario_matriz.edit')->only('edit');
        $this->middleware('can:horario_matriz.update')->only('update');
    }

    public function index()
    {
        $escolas = Escola::query()->orderBy('nome')->get(['id', 'nome', 'empresa_id']);
        $matriz = [];
        foreach ($escolas as $escola) {
            $matriz[$escola->id] = $this->matrizService->matrizDaEscola($escola);
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Matriz de Horários', 'Estrutura Acadêmica');

        return view('horario_matriz.index', [
            'escolas' => $escolas,
            'matriz' => $matriz,
            'turnos' => Turma::TURNOS,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function edit(Request $request, Escola $escola, string $turno)
    {
        abort_unless(array_key_exists($turno, Turma::TURNOS), 404);

        $grade = $this->matrizService->gradeDoTurno($escola, $turno);

        // Os slots sao compartilhados entre os dias: usa o primeiro dia
        // configurado como modelo do formulario.
        $primeiroDia = collect($grade)->keys()->sort()->first();
        $slotsAtuais = $primeiroDia !== null ? $grade[$primeiroDia] : collect();
        $diasAtuais = $primeiroDia !== null ? array_keys($grade) : [];

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Matriz de Horários', 'horario_matriz.index', "{$escola->nome} — ".Turma::TURNOS[$turno]
        );

        return view('horario_matriz.edit', [
            'escola' => $escola,
            'turno' => $turno,
            'turnoLabel' => Turma::TURNOS[$turno],
            'dias' => HorarioSlot::DIAS,
            'diasAtuais' => $diasAtuais,
            'slotsAtuais' => $slotsAtuais->sortKeys()->values(),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function update(StoreHorarioMatrizRequest $request, Escola $escola, string $turno)
    {
        abort_unless(array_key_exists($turno, Turma::TURNOS), 404);

        $data = $request->validated();
        $grade = [];
        foreach ($data['dias'] as $dia) {
            $grade[(int) $dia] = array_map(
                fn ($a) => ['inicio' => $a['inicio'], 'fim' => $a['fim']],
                $data['aulas']
            );
        }

        $this->matrizService->salvarMatriz($escola, $turno, $grade);

        return redirect()->route('horario_matriz.index')
            ->with('success', "Matriz {$turno} de {$escola->nome} salva: ".count($data['dias']).' dia(s) × '.count($data['aulas']).' aula(s).');
    }
}
