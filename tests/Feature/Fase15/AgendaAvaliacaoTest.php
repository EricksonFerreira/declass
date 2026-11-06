<?php

use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupAgendaAvaliacao($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Agenda', 'empresa_id' => $empresa->id]);
    $anoLetivo = \App\Models\AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = \App\Models\Etapa::create([
        'nome' => 'Fundamental', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id,
        'nome' => '3º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'empresa_id' => $empresa->id]);
    $admin = usuarioTenant($empresa, 'administrador');

    Avaliacao::create([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'user_id' => $admin->id, 'tipo' => 'prova', 'nome' => 'Prova Bimestral',
        'data' => now()->format('Y-m-10'), 'nota_maxima' => 10,
        'empresa_id' => $empresa->id,
    ]);
    Avaliacao::create([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'user_id' => $admin->id, 'tipo' => 'trabalho', 'nome' => 'Trabalho de Casa',
        'data' => now()->addDays(5)->format('Y-m-d'), 'nota_maxima' => 5,
        'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'turma');
}

test('agenda lista avaliacoes do mes agrupadas por dia', function () {
    $empresa = tenant();
    setupAgendaAvaliacao($empresa);
    $diretor = usuarioTenant($empresa, 'diretor');

    $this->actingAs($diretor)
        ->get(route('avaliacao.agenda', ['mes' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee('Prova Bimestral')
        ->assertSee('Trabalho de Casa');
});

test('agenda filtra por tipo de avaliacao', function () {
    $empresa = tenant();
    setupAgendaAvaliacao($empresa);
    $diretor = usuarioTenant($empresa, 'diretor');

    $this->actingAs($diretor)
        ->get(route('avaliacao.agenda', [
            'mes' => now()->format('Y-m'), 'tipo' => 'prova',
        ]))
        ->assertOk()
        ->assertSee('Prova Bimestral')
        ->assertDontSee('Trabalho de Casa');
});

test('professor ve apenas avaliacoes das turmas que leciona', function () {
    $empresa = tenant();
    $ids = setupAgendaAvaliacao($empresa);
    $professor = usuarioTenant($empresa, 'professor');

    // sem atribuicao: agenda vazia
    $this->actingAs($professor)
        ->get(route('avaliacao.agenda', ['mes' => now()->format('Y-m')]))
        ->assertOk()
        ->assertDontSee('Prova Bimestral');

    // com atribuicao ativa na turma: passa a ver
    DB::table('atribuicoes')->insert([
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => Disciplina::first()->id,
        'user_id' => $professor->id,
        'ativo' => 'S',
        'empresa_id' => $empresa->id,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($professor)
        ->get(route('avaliacao.agenda', ['mes' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee('Prova Bimestral');
});

test('agenda tem escopo de tenant', function () {
    $empresa = tenant();
    setupAgendaAvaliacao($empresa);

    $outro = tenant(['nome' => 'Rede Agenda B', 'slug' => 'rede-agenda-b']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $diretorB = usuarioTenant($outro, 'diretor');

    $this->actingAs($diretorB)
        ->get(route('avaliacao.agenda', ['mes' => now()->format('Y-m')]))
        ->assertOk()
        ->assertDontSee('Prova Bimestral');
});
