<?php

use App\Menu\Filters\RoutePermissionVisibilityFilter;
use App\Services\MenuSecoesService;
use JeroenNoten\LaravelAdminLte\AdminLte;

function sidebarTexts(): array
{
    $menu = app(AdminLte::class)->menu('sidebar');

    return collect($menu)->pluck('text')->filter()->values()->all();
}

test('menu lateral do professor mostra apenas a rotina pedagogica', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $this->actingAs($professor);

    $menu = app(AdminLte::class)->menu('sidebar');
    $textos = collect($menu)->pluck('text')->filter()->values()->all();

    expect($textos)->toBe([
        'Início',
        'Minhas Turmas',
        'Meus Horários',
        'Calendário',
        'Comunicação',
        'Biblioteca',
    ]);

    // Grupos administrativos e modulos nao pertinentes ficam fora.
    expect($textos)->not->toContain('Organização Administrativa')
        ->and($textos)->not->toContain('Operação Pedagógica')
        ->and($textos)->not->toContain('Operações')
        ->and($textos)->not->toContain('Relatórios')
        ->and($textos)->not->toContain('Financeiro')
        ->and($textos)->not->toContain('Diário e Avaliações')
        ->and($textos)->not->toContain('Alimentação');

    $folhasDe = fn (string $grupo) => collect(collect($menu)->firstWhere('text', $grupo)['submenu'])
        ->pluck('text')->values()->all();

    expect($folhasDe('Calendário'))->toBe(['Calendário Letivo', 'Feriados']);
    expect($folhasDe('Comunicação'))->toBe(['Agenda', 'Avisos', 'Pesquisas']);
    expect($folhasDe('Biblioteca'))->toBe(['Meus Empréstimos']);
});

test('menu lateral do admin mantem diario e avaliacoes', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $this->actingAs($admin);

    $menu = app(AdminLte::class)->menu('sidebar');
    $operacao = collect($menu)->firstWhere('text', 'Operação Pedagógica');
    $avaliacao = collect($operacao['submenu'])->firstWhere('text', 'Diário e Avaliações');
    $folhas = collect($avaliacao['submenu'])->pluck('text')->values()->all();

    expect($folhas)->toContain('Diário de Classe')
        ->and($folhas)->toContain('Avaliações')
        ->and($folhas)->toContain('Agenda de Avaliações');
});

test('menu lateral do admin nao mostra minhas turmas e mantem organizacao administrativa', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $this->actingAs($admin);

    $textos = sidebarTexts();

    expect($textos)->toContain('Organização Administrativa')
        ->and($textos)->toContain('Operação Pedagógica')
        ->and($textos)->toContain('Operações')
        ->and($textos)->not->toContain('Minhas Turmas')
        ->and($textos)->not->toContain('Vida Acadêmica')
        ->and($textos)->not->toContain('Institucional')
        ->and($textos)->not->toContain('Pessoas');
});

test('hub de cards esconde diario, avaliacoes, agenda e alimentacao do professor', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $admin = usuarioTenant($empresa, 'administrador');
    $service = app(MenuSecoesService::class);

    $rotasProf = collect($service->secoes($professor))
        ->flatMap(fn ($s) => $s['cards'])->map(fn ($c) => $c[2]);

    expect($rotasProf)
        ->not->toContain('diario.index')
        ->not->toContain('avaliacao.index')
        ->not->toContain('avaliacao.agenda')
        ->not->toContain('cardapio.index')
        ->not->toContain('emprestimo.index')
        ->not->toContain('livro.index')
        ->toContain('portal.emprestimos') // biblioteca do professor: so os proprios emprestimos
        ->toContain('turma.index'); // continua entrando pela turma

    $rotasAdmin = collect($service->secoes($admin))
        ->flatMap(fn ($s) => $s['cards'])->map(fn ($c) => $c[2]);

    expect($rotasAdmin)
        ->toContain('diario.index')
        ->toContain('avaliacao.index')
        ->toContain('avaliacao.agenda')
        ->toContain('cardapio.index')
        ->toContain('emprestimo.index')
        ->toContain('livro.index');
});

test('sidebar restringe itens de diario e avaliacoes so para professor', function () {
    $empresa = tenant();
    $professor = usuarioTenant($empresa, 'professor');
    $admin = usuarioTenant($empresa, 'administrador');
    $filter = app(RoutePermissionVisibilityFilter::class);

    $this->actingAs($professor);
    expect($filter->transform(['route' => 'diario.index'])['restricted'] ?? false)->toBeTrue()
        ->and($filter->transform(['route' => 'avaliacao.index'])['restricted'] ?? false)->toBeTrue()
        ->and($filter->transform(['route' => 'avaliacao.agenda'])['restricted'] ?? false)->toBeTrue()
        ->and($filter->transform(['route' => 'cardapio.index'])['restricted'] ?? false)->toBeTrue()
        // rotas que ele usa seguem visiveis
        ->and($filter->transform(['route' => 'turma.index'])['restricted'] ?? false)->toBeFalse()
        ->and($filter->transform(['route' => 'meus_horarios.index'])['restricted'] ?? false)->toBeFalse();

    $this->actingAs($admin);
    expect($filter->transform(['route' => 'diario.index'])['restricted'] ?? false)->toBeFalse()
        ->and($filter->transform(['route' => 'avaliacao.index'])['restricted'] ?? false)->toBeFalse()
        ->and($filter->transform(['route' => 'avaliacao.agenda'])['restricted'] ?? false)->toBeFalse()
        ->and($filter->transform(['route' => 'cardapio.index'])['restricted'] ?? false)->toBeFalse();
});
