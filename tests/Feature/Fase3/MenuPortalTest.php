<?php

use JeroenNoten\LaravelAdminLte\AdminLte;

test('menu lateral do aluno mostra apenas inicio e os itens do portal', function () {
    $empresa = tenant();
    $aluno = usuarioTenant($empresa, 'aluno');
    $this->actingAs($aluno);

    $menu = app(AdminLte::class)->menu('sidebar');
    $textos = collect($menu)->pluck('text')->filter()->values()->all();

    expect($textos)->toBe([
        'Início',
        'Meus Horários',
        'Minhas Disciplinas',
        'Turmas',
        'Frequência',
        'Notas',
        'Alertas',
        'Avisos',
        'Agenda',
        'Solicitações',
        'Biblioteca',
        'Pagamentos',
        'Documentos',
    ]);
});

test('menu lateral do responsavel mostra apenas inicio e os itens do portal', function () {
    $empresa = tenant();
    $responsavel = usuarioTenant($empresa, 'responsavel_legal');
    $this->actingAs($responsavel);

    $textos = collect(app(AdminLte::class)->menu('sidebar'))
        ->pluck('text')->filter()->values()->all();

    expect($textos)->toBe([
        'Início',
        'Meus Horários',
        'Minhas Disciplinas',
        'Turmas',
        'Frequência',
        'Notas',
        'Alertas',
        'Avisos',
        'Agenda',
        'Solicitações',
        'Biblioteca',
        'Pagamentos',
        'Documentos',
    ]);
});

test('menu lateral do admin nao mostra os itens soltos do portal', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $this->actingAs($admin);

    $textos = collect(app(AdminLte::class)->menu('sidebar'))
        ->pluck('text')->filter()->values()->all();

    expect($textos)->not->toContain('Minhas Disciplinas')
        ->and($textos)->not->toContain('Pagamentos')
        ->and($textos)->toContain('Organização Administrativa');
});
