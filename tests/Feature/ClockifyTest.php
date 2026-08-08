<?php

use App\Models\Empresa;
use App\Models\User;
use App\Models\ClockifyCliente;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTag;
use App\Models\ClockifyTime;
use App\Models\ClockifyTimeTracker;
use App\Models\ClockifyTimeOff;
use App\Models\ClockifyCalendario;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Auth;

it('verifies clockify models multi-tenancy and scoping', function () {
    // 1. Create two companies (empresas)
    $empresaA = Empresa::create([
        'nome' => 'Empresa A',
        'slug' => 'empresa-a',
        'cnpj' => '11111111000111',
        'ativo' => true,
    ]);

    $empresaB = Empresa::create([
        'nome' => 'Empresa B',
        'slug' => 'empresa-b',
        'cnpj' => '22222222000122',
        'ativo' => true,
    ]);

    // 2. Set active company context to Empresa A
    app(EmpresaContext::class)->setEmpresaId($empresaA->id);

    // Create a client - should automatically set empresa_id to Empresa A
    $clientA = ClockifyCliente::create(['nome' => 'Cliente A']);
    expect($clientA->empresa_id)->toBe($empresaA->id);

    // Create a project
    $projectA = ClockifyProjeto::create([
        'nome' => 'Projeto A',
        'clockify_cliente_id' => $clientA->id,
        'cor' => '#ffffff',
    ]);
    expect($projectA->empresa_id)->toBe($empresaA->id);

    // Create tag
    $tagA = ClockifyTag::create(['nome' => 'Tag A']);
    expect($tagA->empresa_id)->toBe($empresaA->id);

    // Create time/team
    $timeA = ClockifyTime::create(['nome' => 'Time A']);
    expect($timeA->empresa_id)->toBe($empresaA->id);

    // Create user in Empresa A
    $userA = User::create([
        'nome' => 'John Doe',
        'email' => 'john.doe@empresa-a.com',
        'password' => bcrypt('password'),
        'empresa_id' => $empresaA->id,
        'role_id' => 1,
    ]);

    // Create tracker
    $trackerA = ClockifyTimeTracker::create([
        'user_id' => $userA->id,
        'clockify_projeto_id' => $projectA->id,
        'descricao' => 'Trabalhando no Projeto A',
        'inicio' => now(),
    ]);
    expect($trackerA->empresa_id)->toBe($empresaA->id);

    // Create time off
    $timeoffA = ClockifyTimeOff::create([
        'user_id' => $userA->id,
        'tipo' => 'ferias',
        'data_inicio' => now()->toDateString(),
        'data_fim' => now()->addDays(5)->toDateString(),
    ]);
    expect($timeoffA->empresa_id)->toBe($empresaA->id);

    // Create calendar alocation
    $calendarA = ClockifyCalendario::create([
        'user_id' => $userA->id,
        'clockify_projeto_id' => $projectA->id,
        'data_inicio' => now()->toDateString(),
        'data_fim' => now()->addDays(1)->toDateString(),
        'horas_por_dia' => 8.0,
    ]);
    expect($calendarA->empresa_id)->toBe($empresaA->id);

    // 3. Switch context to Empresa B and verify we don't see Empresa A's data
    app(EmpresaContext::class)->setEmpresaId($empresaB->id);

    expect(ClockifyCliente::count())->toBe(0);
    expect(ClockifyProjeto::count())->toBe(0);
    expect(ClockifyTag::count())->toBe(0);
    expect(ClockifyTime::count())->toBe(0);
    expect(ClockifyTimeTracker::count())->toBe(0);
    expect(ClockifyTimeOff::count())->toBe(0);
    expect(ClockifyCalendario::count())->toBe(0);
});
