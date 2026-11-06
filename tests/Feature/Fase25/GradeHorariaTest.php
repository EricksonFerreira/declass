<?php

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\HorarioSlot;
use App\Models\Turma;
use App\Models\User;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Storage;

function gradeFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Unidade Grade', 'empresa_id' => $empresa->id]);
    $etapa = Etapa::create(['nome' => '5º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'status' => 'letivo',
        'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id,
        'nome' => '5º Ano A', 'turno' => 'matutino', 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $mat = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);
    $por = Disciplina::create(['nome' => 'Português', 'sigla' => 'POR', 'empresa_id' => $empresa->id]);
    $turma->disciplinas()->attach($mat->id, ['carga_horaria_semanal' => 2]);
    $turma->disciplinas()->attach($por->id, ['carga_horaria_semanal' => 5]);

    $prof = User::factory()->create(['empresa_id' => $empresa->id, 'nome' => 'Prof Grade']);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'turma_id' => $turma->id, 'disciplina_id' => $mat->id,
        'user_id' => $prof->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    // Matriz matutina: seg-sex, 3 aulas/dia.
    foreach ([1, 2, 3, 4, 5] as $dia) {
        foreach ([['07:00', '07:50'], ['07:50', '08:40'], ['08:40', '09:30']] as $i => $horario) {
            HorarioSlot::create([
                'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
                'turno' => 'matutino', 'dia_semana' => $dia, 'ordem' => $i + 1,
                'hora_inicio' => $horario[0], 'hora_fim' => $horario[1],
            ]);
        }
    }

    return compact('escola', 'anoLetivo', 'turma', 'mat', 'por', 'prof');
}

test('matriz de horarios salva slots para todos os dias', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['escola' => $escola] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->put("/admin/horario-matriz/{$escola->id}/vespertino", [
        'dias' => [1, 2, 3, 4, 5],
        'aulas' => [
            ['inicio' => '13:00', 'fim' => '13:50'],
            ['inicio' => '13:50', 'fim' => '14:40'],
        ],
    ])->assertRedirect(route('horario_matriz.index'));

    expect(HorarioSlot::where('escola_id', $escola->id)->where('turno', 'vespertino')->count())->toBe(10);
});

test('matriz rejeita horarios sobrepostos', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['escola' => $escola] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->put("/admin/horario-matriz/{$escola->id}/noturno", [
        'dias' => [1],
        'aulas' => [
            ['inicio' => '19:00', 'fim' => '20:00'],
            ['inicio' => '19:30', 'fim' => '20:30'],
        ],
    ])->assertSessionHasErrors('aulas');
});

test('grade da turma renderiza a semana com celulas', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->get("/admin/turma/{$turma->id}/grade")
        ->assertOk()
        ->assertSee('5º Ano A')
        ->assertSee('Matemática')
        ->assertSee('grade-tabela');
});

test('salvar celula cria aula e respeita carga horaria', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma, 'mat' => $mat] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->postJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 1, 'ordem' => 1,
        'disciplina_id' => $mat->id, 'user_id' => null, 'sala' => '12',
    ])->assertOk()->assertJson(['ok' => true]);

    $this->assertDatabaseHas('horarios_turma', [
        'turma_id' => $turma->id, 'dia_semana' => 1, 'ordem' => 1,
        'disciplina_id' => $mat->id, 'sala' => '12',
    ]);

    // MAT tem carga 2: preenche 2 celulas e a 3a deve falhar.
    $this->actingAs($admin)->postJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 2, 'ordem' => 1, 'disciplina_id' => $mat->id,
    ])->assertOk();

    $this->actingAs($admin)->postJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 3, 'ordem' => 1, 'disciplina_id' => $mat->id,
    ])->assertStatus(422);
});

test('professor nao pode ficar em duas aulas no mesmo horario', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma, 'turma2' => $turma2, 'mat' => $mat, 'prof' => $prof] = gradeFixture($empresa) + ['turma2' => null];

    // Segunda turma no mesmo turno/escola para gerar conflito real.
    app(EmpresaContext::class)->setEmpresaId($empresa->id);
    $turmaB = Turma::create([
        'escola_id' => $turma->escola_id, 'ano_letivo_id' => $turma->ano_letivo_id,
        'etapa_id' => $turma->etapa_id,
        'nome' => '5º Ano B', 'turno' => 'matutino', 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $turmaB->disciplinas()->attach($mat->id, ['carga_horaria_semanal' => 5]);
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'escola_id' => $turma->escola_id,
        'turma_id' => $turmaB->id, 'disciplina_id' => $mat->id,
        'user_id' => $prof->id, 'papel' => 'titular', 'ativo' => 'S',
    ]);

    $this->actingAs($admin)->postJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 1, 'ordem' => 1, 'disciplina_id' => $mat->id, 'user_id' => $prof->id,
    ])->assertOk();

    $this->actingAs($admin)->postJson("/admin/turma/{$turmaB->id}/grade/celula", [
        'dia_semana' => 1, 'ordem' => 1, 'disciplina_id' => $mat->id, 'user_id' => $prof->id,
    ])->assertStatus(422);
});

test('limpar celula remove a aula', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma, 'mat' => $mat] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->postJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 1, 'ordem' => 1, 'disciplina_id' => $mat->id,
    ])->assertOk();

    $this->actingAs($admin)->deleteJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 1, 'ordem' => 1,
    ])->assertOk();

    $this->assertDatabaseMissing('horarios_turma', [
        'turma_id' => $turma->id, 'dia_semana' => 1, 'ordem' => 1,
    ]);
});

test('exporta xlsx com uma aba por turma e pdf consolidado', function () {
    Storage::fake('public');
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    ['turma' => $turma, 'mat' => $mat] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($admin)->postJson("/admin/turma/{$turma->id}/grade/celula", [
        'dia_semana' => 1, 'ordem' => 1, 'disciplina_id' => $mat->id,
    ])->assertOk();

    $this->actingAs($admin)->get('/admin/horarios/exportar?formato=xlsx&exibir=disciplina')
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $this->actingAs($admin)->get('/admin/horarios/exportar?formato=pdf&exibir=professor')
        ->assertOk();

    $this->actingAs($admin)->get("/admin/turma/{$turma->id}/grade/pdf?exibir=professor")
        ->assertOk();
});

test('perfil sem permissao nao acessa grade nem matriz', function () {
    $empresa = tenant();
    $prof = usuarioTenant($empresa, 'professor');
    ['turma' => $turma, 'escola' => $escola] = gradeFixture($empresa);
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $this->actingAs($prof)->get("/admin/turma/{$turma->id}/grade")->assertForbidden();
    $this->actingAs($prof)->get('/admin/horarios')->assertForbidden();
    $this->actingAs($prof)->get('/admin/horario-matriz')->assertForbidden();
});
