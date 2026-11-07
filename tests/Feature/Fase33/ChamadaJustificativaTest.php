<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Justificativa;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupChamada33($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id, 'ativo' => 'S',
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);
    $outraDisciplina = Disciplina::create(['nome' => 'História', 'sigla' => 'HIS', 'empresa_id' => $empresa->id]);

    foreach ([$disciplina, $outraDisciplina] as $d) {
        DB::table('turma_disciplina')->insert([
            'turma_id' => $turma->id, 'disciplina_id' => $d->id,
            'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $pessoa = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'outraDisciplina', 'pessoa', 'aluno', 'matricula');
}

function atribuirProfessor33($empresa, $turma, $disciplina, $professor): void
{
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $professor->id,
        'papel' => 'titular', 'ativo' => 'S',
    ]);
}

function diarioDoProfessor33($empresa, $turma, $disciplina, $professor, string $data): Diario
{
    return Diario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $turma->escola_id,
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'user_id' => $professor->id, 'data' => $data,
        'conteudo' => 'Aula de teste',
    ]);
}

test('fazer chamada leva direto a unificada e resolve disciplina unica', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);

    // Sem disciplina: vai direto para a chamada unificada.
    $this->actingAs($professor)
        ->get(route('turma.chamada', $ids['turma']->id))
        ->assertRedirect(route('diario.chamada', [
            'turma_id' => $ids['turma']->id,
            'data' => now()->toDateString(),
        ]));

    // Com uma unica disciplina visivel ela resolve sozinha:
    // aparece como texto (sem select) e a data fica editavel.
    $this->actingAs($professor)
        ->get(route('diario.chamada', ['turma_id' => $ids['turma']->id]))
        ->assertOk()
        ->assertSee('Matemática')
        ->assertDontSee('História')
        ->assertSee('type="date"', false)
        ->assertSee('Salvar chamada');
});

test('chamada com varias disciplinas pede a escolha antes de listar alunos', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);
    atribuirProfessor33($empresa, $ids['turma'], $ids['outraDisciplina'], $professor);

    // Sem disciplina_id e com mais de uma opcao: mostra o seletor
    // e nao renderiza a lista de alunos nem o formulario de salvar.
    $this->actingAs($professor)
        ->get(route('diario.chamada', ['turma_id' => $ids['turma']->id]))
        ->assertOk()
        ->assertSee('name="disciplina_id"', false)
        ->assertSee('Matemática')
        ->assertSee('História')
        ->assertSee('type="date"', false)
        ->assertDontSee('Salvar chamada');

    // Escolhida a disciplina, a tela completa aparece.
    $this->actingAs($professor)
        ->get(route('diario.chamada', [
            'turma_id' => $ids['turma']->id,
            'disciplina_id' => $ids['outraDisciplina']->id,
        ]))
        ->assertOk()
        ->assertSee('História')
        ->assertSee('Salvar chamada');
});

test('chamada com disciplina e data abre a chamada unificada do diario existente', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);
    diarioDoProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor, now()->toDateString());

    $this->actingAs($professor)
        ->get(route('turma.chamada', $ids['turma']->id).'?disciplina_id='.$ids['disciplina']->id.'&data='.now()->toDateString())
        ->assertRedirect(route('diario.chamada', [
            'turma_id' => $ids['turma']->id,
            'disciplina_id' => $ids['disciplina']->id,
            'data' => now()->toDateString(),
        ]));
});

test('chamada sem aula registrada leva a chamada unificada que cria a aula', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);

    $this->actingAs($professor)
        ->get(route('turma.chamada', $ids['turma']->id).'?disciplina_id='.$ids['disciplina']->id.'&data=2026-03-10')
        ->assertRedirect(route('diario.chamada', [
            'turma_id' => $ids['turma']->id,
            'disciplina_id' => $ids['disciplina']->id,
            'data' => '2026-03-10',
        ]));
});

test('professor nao faz chamada em disciplina nao atribuida', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);

    $this->actingAs($professor)
        ->get(route('turma.chamada', $ids['turma']->id).'?disciplina_id='.$ids['outraDisciplina']->id)
        ->assertForbidden();
});

test('tela de chamada tem checkbox mestre para marcar todos', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);
    $diario = diarioDoProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor, now()->toDateString());

    $this->actingAs($professor)
        ->get(route('diario.frequencia', $diario->id))
        ->assertOk()
        ->assertSee('freq-marcar-todos')
        ->assertSee('freq-check');
});

test('justificativa da secretaria converte faltas do periodo', function () {
    $empresa = tenant();
    $secretario = usuarioTenant($empresa, 'secretario');
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);

    $dentro = diarioDoProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor, '2026-03-10');
    $fora = diarioDoProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor, '2026-03-20');
    $outraDisciplinaDiario = diarioDoProfessor33($empresa, $ids['turma'], $ids['outraDisciplina'], $professor, '2026-03-11');

    $freqDentro = Frequencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'diario_id' => $dentro->id, 'matricula_id' => $ids['matricula']->id, 'status' => 'falta',
    ]);
    $freqFora = Frequencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'diario_id' => $fora->id, 'matricula_id' => $ids['matricula']->id, 'status' => 'falta',
    ]);
    $freqOutra = Frequencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'diario_id' => $outraDisciplinaDiario->id, 'matricula_id' => $ids['matricula']->id, 'status' => 'falta',
    ]);

    $this->actingAs($secretario)->post(route('justificativa.store'), [
        'matricula_id' => $ids['matricula']->id,
        'data_inicio' => '2026-03-10',
        'data_fim' => '2026-03-12',
        'tipo' => 'atestado_medico',
        'efeito' => 'justificada',
        'motivo' => 'Consulta médica',
    ])->assertRedirect(route('justificativa.index'));

    $this->assertDatabaseHas('justificativas', [
        'matricula_id' => $ids['matricula']->id, 'tipo' => 'atestado_medico', 'efeito' => 'justificada',
    ]);
    expect($freqDentro->fresh()->status)->toBe('justificada');
    expect($freqDentro->fresh()->justificativa_id)->not->toBeNull();
    expect($freqOutra->fresh()->status)->toBe('justificada'); // disciplina vazia = todas
    expect($freqFora->fresh()->status)->toBe('falta'); // fora do periodo
});

test('efeito abonada marca faltas como abonadas e exclusao reverte', function () {
    $empresa = tenant();
    $secretario = usuarioTenant($empresa, 'secretario');
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupChamada33($empresa);
    atribuirProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor);

    $diario = diarioDoProfessor33($empresa, $ids['turma'], $ids['disciplina'], $professor, '2026-04-02');
    $freq = Frequencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'diario_id' => $diario->id, 'matricula_id' => $ids['matricula']->id, 'status' => 'falta',
    ]);

    $this->actingAs($secretario)->post(route('justificativa.store'), [
        'matricula_id' => $ids['matricula']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'data_inicio' => '2026-04-01',
        'data_fim' => '2026-04-05',
        'tipo' => 'declaracao',
        'efeito' => 'abonada',
    ])->assertRedirect(route('justificativa.index'));

    expect($freq->fresh()->status)->toBe('abonada');

    $justificativa = Justificativa::where('matricula_id', $ids['matricula']->id)->firstOrFail();
    $this->actingAs($secretario)
        ->delete(route('justificativa.destroy', $justificativa->id))
        ->assertRedirect(route('justificativa.index'));

    expect($freq->fresh()->status)->toBe('falta');
    expect($freq->fresh()->justificativa_id)->toBeNull();
});

test('professor nao acessa modulo de justificativas', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');

    $this->actingAs($professor)->get(route('justificativa.index'))->assertForbidden();
});
