<?php

use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Horario;
use App\Models\HorarioSlot;
use App\Models\HorarioTurma;
use App\Models\Turma;
use App\Support\EmpresaContext;

function setupHomeProf($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola HP', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-11-30',
    ]);
    $etapa = Etapa::create(['nome' => '1º Ano', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);
    $turma = Turma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'ano_letivo_id' => $anoLetivo->id, 'etapa_id' => $etapa->id,
        'nome' => '1º Ano A', 'turno' => 'matutino', 'ativo' => 'S',
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'disciplina', 'turma');
}

function aulaHojeHomeProf($empresa, array $ids, $professor): void
{
    $horario = Horario::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turno' => 'matutino', 'nome' => 'Matutino', 'ativo' => 'S',
    ]);
    foreach (range(1, 7) as $dia) {
        HorarioSlot::create([
            'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
            'horario_id' => $horario->id, 'turno' => 'matutino',
            'dia_semana' => $dia, 'ordem' => 1,
            'hora_inicio' => '07:30', 'hora_fim' => '08:20',
        ]);
    }
    $ids['turma']->update(['horario_id' => $horario->id]);

    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id, 'papel' => 'titular',
    ]);

    $dow = (int) now()->dayOfWeek; // 0=dom..6=sab
    HorarioTurma::create([
        'empresa_id' => $empresa->id, 'escola_id' => $ids['escola']->id,
        'turma_id' => $ids['turma']->id,
        'dia_semana' => $dow === 0 ? 7 : $dow, 'ordem' => 1,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id, 'sala' => '101',
    ]);
}

test('home do professor mostra resumo do dia e aulas de hoje', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupHomeProf($empresa);
    aulaHojeHomeProf($empresa, $ids, $professor);

    $this->actingAs($professor)->get(route('home'))
        ->assertOk()
        ->assertSee('Aulas hoje')
        ->assertSee('Chamadas feitas')
        ->assertSee('Minhas turmas')
        ->assertSee('Avaliações')
        ->assertSee('Matemática')
        ->assertSee('1º Ano A')
        ->assertSee('07:30')
        ->assertSee('Sala 101')
        ->assertSee('Chamada')
        // Popup de acao rapida da aula
        ->assertSee('aula-acao-modal')
        ->assertSee('Ir para turma')
        ->assertSee('Fazer chamada')
        ->assertSee(route('turma.show', $ids['turma']->id), false)
        ->assertSee('diarios/chamada', false);
});

test('home do professor sem aulas hoje mostra estado vazio', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    setupHomeProf($empresa);

    $this->actingAs($professor)->get(route('home'))
        ->assertOk()
        ->assertSee('Sem aulas hoje');
});

test('home de nao-professor segue sendo a padrao', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($admin)->get(route('home'))
        ->assertOk()
        ->assertSee('principais áreas')
        ->assertDontSee('Chamadas feitas');
});

test('nav inferior do professor traz as funcoes do dia a dia', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $admin = usuarioTenant($empresa, 'administrador');

    $this->actingAs($professor);
    $html = view('partials.mobile-bottom-nav')->render();
    expect($html)
        ->toContain('Turmas')
        ->toContain('Horários')
        ->toContain('Calendário')
        ->toContain('Menu')
        ->and(substr_count($html, 'class="mbn-item'))->toBe(5);

    $this->actingAs($admin);
    $htmlAdmin = view('partials.mobile-bottom-nav')->render();
    expect($htmlAdmin)
        ->toContain('Atendimento')
        ->toContain('Docs')
        ->not->toContain('Horários');
});

test('nav inferior marca apenas o item da tela atual', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');

    $html = $this->actingAs($professor)->get(route('home'))
        ->assertOk()
        ->getContent();

    preg_match('/<nav class="mobile-bottom-nav.*?<\/nav>/s', $html, $m);
    $nav = $m[0] ?? '';

    // Antes do fix, itens com slug null casavam secao null e todos
    // ficavam ativos em qualquer tela (Início + Horários + Calendário).
    expect($nav)->not->toBe('')
        ->and(substr_count($nav, 'mbn-item active'))->toBe(1);
});
