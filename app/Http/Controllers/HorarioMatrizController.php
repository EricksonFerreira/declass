<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorarioMatriz\StoreHorarioMatrizRequest;
use App\Models\Escola;
use App\Models\Horario;
use App\Models\HorarioSlot;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\HorarioMatrizService;

/**
 * Matriz de horarios: gerencia os Horarios nomeados de cada
 * escola/turno (a "grade vazia" que as turmas preenchem).
 */
class HorarioMatrizController extends BaseController
{
    public function __construct(
        protected HorarioMatrizService $matrizService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Matriz de Horários';

        $this->middleware('can:horario_matriz.index')->only('index');
        $this->middleware('can:horario_matriz.create')->only(['create', 'store']);
        $this->middleware('can:horario_matriz.edit')->only('edit');
        $this->middleware('can:horario_matriz.update')->only('update');
        $this->middleware('can:horario_matriz.destroy')->only('destroy');
    }

    public function index()
    {
        $escolas = Escola::query()->orderBy('nome')->get(['id', 'nome', 'empresa_id']);
        $horarios = [];
        foreach ($escolas as $escola) {
            $horarios[$escola->id] = $this->matrizService->horariosDaEscola($escola);
        }

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Matriz de Horários', 'Estrutura Acadêmica');

        return view('horario_matriz.index', [
            'escolas' => $escolas,
            'horarios' => $horarios,
            'turnos' => Turma::TURNOS,
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function create(Escola $escola)
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Matriz de Horários', 'horario_matriz.index', "{$escola->nome} — novo horário"
        );

        return view('horario_matriz.edit', [
            'escola' => $escola,
            'horario' => null,
            'turnos' => Turma::TURNOS,
            'dias' => HorarioSlot::DIAS,
            'diasAtuais' => [],
            'slotsAtuais' => collect(),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function store(StoreHorarioMatrizRequest $request, Escola $escola)
    {
        $data = $request->validated();
        $grade = $this->montarGrade($data);

        $horario = $this->matrizService->criarHorario($escola, $data, $grade);

        return redirect()->route('horario_matriz.index')
            ->with('success', "Horário \"{$horario->nome}\" criado em {$escola->nome}: ".count($data['dias']).' dia(s) × '.count($data['aulas']).' aula(s).');
    }

    public function edit(Horario $horario)
    {
        $horario->load('escola');
        $grade = $this->matrizService->gradeDoHorario($horario);

        // Os slots sao compartilhados entre os dias: usa o primeiro dia
        // configurado como modelo do formulario.
        $primeiroDia = collect($grade)->keys()->sort()->first();
        $slotsAtuais = $primeiroDia !== null ? $grade[$primeiroDia] : collect();
        $diasAtuais = $primeiroDia !== null ? array_keys($grade) : [];

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Matriz de Horários', 'horario_matriz.index', "{$horario->escola->nome} — {$horario->nome}"
        );

        return view('horario_matriz.edit', [
            'escola' => $horario->escola,
            'horario' => $horario,
            'turnos' => Turma::TURNOS,
            'dias' => HorarioSlot::DIAS,
            'diasAtuais' => $diasAtuais,
            'slotsAtuais' => $slotsAtuais->sortKeys()->values(),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function update(StoreHorarioMatrizRequest $request, Horario $horario)
    {
        $data = $request->validated();
        $grade = $this->montarGrade($data);

        $this->matrizService->salvarHorario($horario, $data, $grade);

        return redirect()->route('horario_matriz.index')
            ->with('success', "Horário \"{$horario->nome}\" salvo: ".count($data['dias']).' dia(s) × '.count($data['aulas']).' aula(s).');
    }

    public function destroy(Horario $horario)
    {
        $nome = $horario->nome;
        $this->matrizService->excluirHorario($horario);

        return redirect()->route('horario_matriz.index')
            ->with('success', "Horário \"{$nome}\" excluído.");
    }

    private function montarGrade(array $data): array
    {
        $grade = [];
        foreach ($data['dias'] as $dia) {
            $grade[(int) $dia] = array_map(
                fn ($a) => ['inicio' => $a['inicio'], 'fim' => $a['fim']],
                $data['aulas']
            );
        }

        return $grade;
    }
}
