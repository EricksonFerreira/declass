<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Evento;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupAgenda($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Agenda', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '2º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '2º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);

    $pessoaAluno = Pessoa::create(['nome' => 'Pedro Alves', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoaAluno->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-AG1', 'empresa_id' => $empresa->id,
    ]);
    Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    $pessoaResp = Pessoa::create(['nome' => 'Mãe do Pedro', 'empresa_id' => $empresa->id]);
    $responsavel = Responsavel::create([
        'pessoa_id' => $pessoaResp->id, 'empresa_id' => $empresa->id,
    ]);
    $aluno->responsaveis()->attach($responsavel->id, [
        'tipo' => 'legal', 'principal' => true,
        'pode_retirar' => true, 'autoriza_contato' => true,
    ]);

    return compact('escola', 'turma', 'aluno', 'responsavel');
}

function eventoDemo($empresa, $escola, array $over = []): Evento
{
    return Evento::create(array_merge([
        'empresa_id' => $empresa->id,
        'escola_id' => $escola->id,
        'titulo' => 'Evento Teste',
        'tipo' => 'evento',
        'data_inicio' => now()->addDays(5)->toDateString(),
        'publico' => 'todos',
        'status' => 'agendado',
    ], $over));
}

test('diretor cadastra evento e ele aparece na agenda do mes', function () {
    $empresa = tenant();
    $ids = setupAgenda($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->get(route('evento.index'))->assertOk();

    $response = $this->actingAs($user)->post(route('evento.store'), [
        'titulo' => 'Reunião de pais',
        'tipo' => 'reuniao',
        'escola_id' => $ids['escola']->id,
        'data_inicio' => now()->addDays(3)->toDateString(),
        'hora_inicio' => '19:00',
        'hora_fim' => '21:00',
        'local' => 'Auditório',
        'publico' => 'todos',
        'status' => 'agendado',
        'descricao' => 'Conversa sobre o desempenho dos alunos',
    ]);

    $response->assertRedirect(route('evento.index'));
    expect(Evento::where('titulo', 'Reunião de pais')->exists())->toBeTrue();

    $this->actingAs($user)
        ->get(route('evento.index', ['mes' => now()->format('Y-m')]))
        ->assertOk()
        ->assertSee('Reunião de pais');
});

test('validacao exige titulo tipo e data de inicio', function () {
    $empresa = tenant();
    setupAgenda($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->post(route('evento.store'), [])
        ->assertSessionHasErrors(['titulo', 'tipo', 'data_inicio', 'publico', 'status']);
});

test('data fim nao pode ser anterior ao inicio', function () {
    $empresa = tenant();
    $ids = setupAgenda($empresa);
    $user = usuarioTenant($empresa, 'diretor');

    $this->actingAs($user)->post(route('evento.store'), [
        'titulo' => 'Evento inválido',
        'tipo' => 'evento',
        'escola_id' => $ids['escola']->id,
        'data_inicio' => now()->addDays(10)->toDateString(),
        'data_fim' => now()->addDays(5)->toDateString(),
        'publico' => 'todos',
        'status' => 'agendado',
    ])->assertSessionHasErrors('data_fim');
});

test('evento de outro tenant nao aparece na agenda', function () {
    $empresa = tenant();
    $ids = setupAgenda($empresa);
    eventoDemo($empresa, $ids['escola'], ['titulo' => 'Evento Secreto A']);

    $outro = tenant(['nome' => 'Rede B', 'slug' => 'rede-b-agenda']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);
    $userB = usuarioTenant($outro, 'diretor');

    $this->actingAs($userB)
        ->get(route('evento.index', ['mes' => now()->format('Y-m')]))
        ->assertOk()
        ->assertDontSee('Evento Secreto A');
});

test('professor le agenda mas nao cria evento', function () {
    $empresa = tenant();
    setupAgenda($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)
        ->get(route('evento.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('evento.create'))
        ->assertForbidden();
});

test('portal mostra apenas eventos publicos e nao cancelados', function () {
    $empresa = tenant();
    $ids = setupAgenda($empresa);

    eventoDemo($empresa, $ids['escola'], ['titulo' => 'Reunião Pública', 'publico' => 'todos', 'status' => 'agendado']);
    eventoDemo($empresa, $ids['escola'], ['titulo' => 'Reunião Interna', 'publico' => 'interno', 'status' => 'agendado']);
    eventoDemo($empresa, $ids['escola'], ['titulo' => 'Evento Cancelado', 'publico' => 'todos', 'status' => 'cancelado']);

    $user = usuarioTenant($empresa, 'responsavel_legal');
    $ids['responsavel']->update(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('portal.agenda'))
        ->assertOk()
        ->assertSee('Reunião Pública')
        ->assertDontSee('Reunião Interna')
        ->assertDontSee('Evento Cancelado');
});

test('portal bloqueia conta sem aluno vinculado', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'responsavel_legal');

    $this->actingAs($user)
        ->get(route('portal.agenda'))
        ->assertForbidden();
});
