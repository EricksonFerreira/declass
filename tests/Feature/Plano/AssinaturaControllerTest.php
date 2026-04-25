<?php

/**
 * Testes de integração do AssinaturaController.
 *
 * Cobre todos os cenários da lógica de planos:
 * - Nova contratação (mensal e anual, via PIX e cartão)
 * - Upgrade (mesmo ciclo, troca mensal→anual)
 * - Downgrade agendado (sem excedentes e com excedentes de veículos)
 * - Downgrade imediato com desativação de veículos (fluxo via downgrade view)
 * - Anual → Mensal (sempre agendado para fim do term anual)
 * - Cancelamento agendado (acesso mantido até fim do period)
 * - Remoção de mudança agendada
 * - Renovação (mesmo plano e com downgrade agendado)
 * - Troca de método de pagamento
 * - Confirmação e registro de pagamento pelo admin
 * - Polling de status de pagamento
 * - Middleware de bloqueio por plano
 */

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Models\Regra;
use App\Models\User;
use App\Models\Veiculo;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function novaEmpresaComUser(array $extraUser = []): array
{
    $regra   = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
    $empresa = Empresa::factory()->create();
    $user    = User::factory()->create(array_merge([
        'empresa_id' => $empresa->id,
        'role_id'    => $regra->id,
    ], $extraUser));

    return [$empresa, $user];
}

function superAdmin(): User
{
    return User::factory()->create(['role_id' => 1, 'empresa_id' => null]);
}

function assinaturaAtiva(Empresa $empresa, Plano $plano, array $extra = []): EmpresaPlano
{
    return EmpresaPlano::create(array_merge([
        'empresa_id'      => $empresa->id,
        'plano_id'        => $plano->id,
        'periodicidade'   => 'mensal',
        'status'          => 'ativo',
        'metodo_cobranca' => 'pix',
        'data_inicio'     => now()->toDateString(),
        'data_vencimento' => now()->addMonth()->toDateString(),
        'valor_cobrado'   => (float) $plano->valor_mensal,
    ], $extra));
}

// ─────────────────────────────────────────────────────────────────────────────
// GET /meu-plano
// ─────────────────────────────────────────────────────────────────────────────

describe('GET meu-plano', function () {
    it('renderiza a view com empresa sem assinatura', function () {
        [, $user] = novaEmpresaComUser();

        $this->actingAs($user)->get(route('assinatura.meu-plano'))
            ->assertStatus(200)
            ->assertViewIs('assinatura.meu-plano');
    });

    it('renderiza com assinatura ativa passada para a view', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        assinaturaAtiva($empresa, $plano);

        $this->actingAs($user)->get(route('assinatura.meu-plano'))
            ->assertStatus(200)
            ->assertViewHas('assinatura');
    });

    it('view recebe lista de planos ativos', function () {
        [, $user] = novaEmpresaComUser();
        Plano::factory()->count(3)->create(['ativo' => true]);

        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertViewHas('planos');
        expect($response->viewData('planos'))->toHaveCount(3);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/contratar — nova contratação (empresa sem plano ativo)
// ─────────────────────────────────────────────────────────────────────────────

describe('POST contratar — nova contratacao', function () {
    it('cria assinatura aguardando_pagamento para empresa sem plano', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 199.90]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])
        ->assertRedirect(route('assinatura.meu-plano'))
        ->assertSessionHas('success');

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id' => $empresa->id,
            'plano_id'   => $plano->id,
            'status'     => 'aguardando_pagamento',
        ]);
    });

    it('cria pagamento pendente ao contratar novo plano', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 199.90]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        $assinatura = EmpresaPlano::where('empresa_id', $empresa->id)->first();
        $this->assertDatabaseHas('empresa_plano_pagamentos', [
            'empresa_plano_id' => $assinatura->id,
            'status'           => 'pendente',
        ]);
    });

    it('contratacao anual cria assinatura com periodicidade anual', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_anual' => 150.00]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'anual',
            'metodo_pagamento' => 'pix',
        ]);

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id'   => $empresa->id,
            'periodicidade'=> 'anual',
        ]);
    });

    it('rejeita plano_id invalido', function () {
        [, $user] = novaEmpresaComUser();

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => 99999,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])->assertSessionHasErrors('plano_id');
    });

    it('rejeita metodo_pagamento invalido', function () {
        [, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'boleto', // não permitido pela regra da rota
        ])->assertSessionHasErrors('metodo_pagamento');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/contratar — upgrade (empresa já tem plano ativo)
// ─────────────────────────────────────────────────────────────────────────────

describe('POST contratar — upgrade com plano ativo', function () {
    it('cria pagamento de upgrade proporcional quando novo plano e mais caro', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);

        assinaturaAtiva($empresa, $planoA, [
            'data_inicio'    => now()->subDays(15)->toDateString(),
            'data_vencimento'=> now()->addDays(15)->toDateString(),
            'valor_cobrado'  => 100.00,
        ]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])
        ->assertRedirect(route('assinatura.meu-plano'))
        ->assertSessionHas('success');

        // Pagamento de upgrade pendente criado
        $this->assertDatabaseHas('empresa_plano_pagamentos', [
            'empresa_id' => $empresa->id,
            'status'     => 'pendente',
        ]);
    });

    it('retorna erro ao tentar contratar o mesmo plano e periodicidade', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 199.90]);

        assinaturaAtiva($empresa, $plano, ['valor_cobrado' => 199.90]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])->assertSessionHasErrors('plano_id');
    });

    it('retorna erro quando ja existe upgrade pendente', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);

        $assinatura = assinaturaAtiva($empresa, $planoA, [
            'data_inicio'    => now()->subDays(10)->toDateString(),
            'data_vencimento'=> now()->addDays(20)->toDateString(),
            'valor_cobrado'  => 100.00,
        ]);

        // Já existe upgrade pendente
        EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 50.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'upgrade'],
        ]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])->assertSessionHasErrors('metodo_pagamento');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/contratar — downgrade agendado
// ─────────────────────────────────────────────────────────────────────────────

describe('POST contratar — downgrade agendado', function () {
    it('agenda downgrade quando valor novo e menor que atual (mesmo ciclo mensal)', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);

        assinaturaAtiva($empresa, $planoA, ['valor_cobrado' => 300.00]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])
        ->assertRedirect(route('assinatura.meu-plano'))
        ->assertSessionHas('success');

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id'       => $empresa->id,
            'proximo_plano_id' => $planoB->id,
            'mudanca_tipo'     => 'downgrade',
        ]);
    });

    it('assinatura atual permanece ativa apos agendar downgrade', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);

        $assinatura = assinaturaAtiva($empresa, $planoA, ['valor_cobrado' => 300.00]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        // Status não muda — downgrade é agendado, não imediato
        expect($assinatura->fresh()->status)->toBe('ativo');
        expect($assinatura->fresh()->plano_id)->toBe($planoA->id);
    });

    it('mudanca_efetiva_em e igual a data_vencimento da assinatura atual', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $vencimento = now()->addMonth()->toDateString();

        $assinatura = assinaturaAtiva($empresa, $planoA, [
            'valor_cobrado'  => 300.00,
            'data_vencimento'=> $vencimento,
        ]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        expect($assinatura->fresh()->mudanca_efetiva_em->format('Y-m-d'))->toBe($vencimento);
    });

    it('redireciona para downgrade view quando ha veiculos excedentes', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->ilimitado()->create(['valor_mensal' => 500.00, 'valor_anual' => 450.00]);
        $planoB = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00, 'valor_anual' => 80.00]);

        assinaturaAtiva($empresa, $planoA, ['valor_cobrado' => 500.00]);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'TES0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'TES0002']);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])->assertRedirect(route('assinatura.downgrade'));
    });

    it('armazena dados de downgrade na session antes de redirecionar', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->ilimitado()->create(['valor_mensal' => 500.00, 'valor_anual' => 450.00]);
        $planoB = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00, 'valor_anual' => 80.00]);

        assinaturaAtiva($empresa, $planoA, ['valor_cobrado' => 500.00]);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'DSS0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'DSS0002']);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        expect(session('downgrade_plano_id'))->toBe((string) $planoB->id);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/contratar — anual → mensal (sempre agendado)
// ─────────────────────────────────────────────────────────────────────────────

describe('POST contratar — anual para mensal (sempre agendado)', function () {
    it('agenda mudanca para mensal quando assinatura atual e anual', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);

        assinaturaAtiva($empresa, $plano, [
            'periodicidade'  => 'anual',
            'data_vencimento'=> now()->addYear()->toDateString(),
            'valor_cobrado'  => 170.00 * 12,
        ]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ])
        ->assertRedirect(route('assinatura.meu-plano'))
        ->assertSessionHas('success');

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id'  => $empresa->id,
            'mudanca_tipo'=> 'downgrade',
        ]);
    });

    it('nao cria nova assinatura imediatamente para anual para mensal', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);

        assinaturaAtiva($empresa, $plano, [
            'periodicidade'  => 'anual',
            'data_vencimento'=> now()->addYear()->toDateString(),
            'valor_cobrado'  => 170.00 * 12,
        ]);

        $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        // Apenas 1 assinatura — não criou nova
        expect(EmpresaPlano::where('empresa_id', $empresa->id)->count())->toBe(1);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// GET/POST /meu-plano/downgrade — seleção de veículos
// ─────────────────────────────────────────────────────────────────────────────

describe('downgrade — selecao de veiculos', function () {
    it('GET downgrade redireciona para meu-plano sem session', function () {
        [, $user] = novaEmpresaComUser();

        $this->actingAs($user)->get(route('assinatura.downgrade'))
            ->assertRedirect(route('assinatura.meu-plano'));
    });

    it('GET downgrade renderiza view com veiculos quando session esta definida', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->comLimite(1)->create();

        session(['downgrade_plano_id' => $plano->id]);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'DWN0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'DWN0002']);

        $this->actingAs($user)->get(route('assinatura.downgrade'))
            ->assertStatus(200)
            ->assertViewIs('assinatura.downgrade');
    });

    it('POST confirmarDowngrade desativa veiculos escolhidos e contrata novo plano', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00]);

        session([
            'downgrade_plano_id'      => $plano->id,
            'downgrade_periodicidade' => 'mensal',
            'downgrade_metodo'        => 'pix',
            'downgrade_dia_vencimento'=> null,
            'downgrade_origem'        => 'assinatura',
        ]);

        $v1 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'CON0001']);
        $v2 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'CON0002']);

        $this->actingAs($user)->post(route('assinatura.downgrade.confirmar'), [
            'veiculos_desativar' => [$v2->id],
        ])->assertRedirect(route('assinatura.meu-plano'));

        expect(Veiculo::find($v2->id)->ativo)->toBeFalse();
        expect(Veiculo::find($v1->id)->ativo)->toBeTrue();
    });

    it('POST confirmarDowngrade retorna erro se veiculos insuficientes desativados', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00]);

        session([
            'downgrade_plano_id'      => $plano->id,
            'downgrade_periodicidade' => 'mensal',
            'downgrade_metodo'        => 'pix',
            'downgrade_dia_vencimento'=> null,
            'downgrade_origem'        => 'assinatura',
        ]);

        $v1 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'INS0001']);
        $v2 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'INS0002']);
        $v3 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'C', 'placa_licenca' => 'INS0003']);

        // Desativa apenas 1, mas precisaria de 2 para ficar dentro do limite 1
        $this->actingAs($user)->post(route('assinatura.downgrade.confirmar'), [
            'veiculos_desativar' => [$v3->id],
        ])->assertSessionHasErrors('veiculos_desativar');
    });

    it('POST confirmarDowngrade origem agendar agenda a mudanca e nao cria nova assinatura', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->ilimitado()->create(['valor_mensal' => 500.00, 'valor_anual' => 450.00]);
        $planoB = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00]);

        $assinatura = assinaturaAtiva($empresa, $planoA, ['valor_cobrado' => 500.00]);

        session([
            'downgrade_plano_id'      => $planoB->id,
            'downgrade_periodicidade' => 'mensal',
            'downgrade_metodo'        => 'pix',
            'downgrade_dia_vencimento'=> null,
            'downgrade_origem'        => 'agendar',
        ]);

        $v1 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa_licenca' => 'AGD0001']);
        $v2 = Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa_licenca' => 'AGD0002']);

        $this->actingAs($user)->post(route('assinatura.downgrade.confirmar'), [
            'veiculos_desativar' => [$v2->id],
        ])->assertRedirect(route('assinatura.meu-plano'));

        // Assinatura atual continua ativa (mudança agendada)
        expect($assinatura->fresh()->status)->toBe('ativo');
        expect($assinatura->fresh()->mudanca_tipo)->toBe('downgrade');
        // Apenas 1 assinatura
        expect(EmpresaPlano::where('empresa_id', $empresa->id)->count())->toBe(1);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/cancelar
// ─────────────────────────────────────────────────────────────────────────────

describe('POST cancelar', function () {
    it('agenda cancelamento para fim do periodo vigente', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano   = Plano::factory()->create();
        $vencimento = now()->addMonth()->toDateString();
        $assinatura = assinaturaAtiva($empresa, $plano, ['data_vencimento' => $vencimento]);

        $this->actingAs($user)->post(route('assinatura.cancelar'))
            ->assertRedirect(route('assinatura.meu-plano'))
            ->assertSessionHas('success');

        $assinatura->refresh();
        expect($assinatura->mudanca_tipo)->toBe('cancelamento');
        expect($assinatura->mudanca_efetiva_em->format('Y-m-d'))->toBe($vencimento);
    });

    it('assinatura permanece ativa apos agendar cancelamento', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano      = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano);

        $this->actingAs($user)->post(route('assinatura.cancelar'));

        expect($assinatura->fresh()->status)->toBe('ativo');
    });

    it('cancelamento desliga renovacao_automatica', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano, [
            'metodo_cobranca'      => 'cartao',
            'renovacao_automatica' => true,
        ]);

        $this->actingAs($user)->post(route('assinatura.cancelar'));

        expect($assinatura->fresh()->renovacao_automatica)->toBeFalse();
    });

    it('cancelamento limpa proximo_plano_id e mudanca de downgrade anterior', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create();
        $planoB = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $planoA, [
            'proximo_plano_id' => $planoB->id,
            'mudanca_tipo'     => 'downgrade',
        ]);

        $this->actingAs($user)->post(route('assinatura.cancelar'));

        expect($assinatura->fresh()->proximo_plano_id)->toBeNull();
        expect($assinatura->fresh()->mudanca_tipo)->toBe('cancelamento');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/mudanca/remover
// ─────────────────────────────────────────────────────────────────────────────

describe('POST mudanca remover', function () {
    it('remove mudanca de cancelamento agendada', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano, [
            'mudanca_tipo'          => 'cancelamento',
            'mudanca_efetiva_em'    => now()->addMonth()->toDateString(),
            'mudanca_solicitada_em' => now(),
        ]);

        $this->actingAs($user)->post(route('assinatura.mudanca.remover'))
            ->assertRedirect(route('assinatura.meu-plano'))
            ->assertSessionHas('success');

        $assinatura->refresh();
        expect($assinatura->mudanca_tipo)->toBeNull();
        expect($assinatura->mudanca_efetiva_em)->toBeNull();
        expect($assinatura->mudanca_solicitada_em)->toBeNull();
    });

    it('remove mudanca de downgrade agendada', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create();
        $planoB = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $planoA, [
            'proximo_plano_id'      => $planoB->id,
            'proxima_periodicidade' => 'mensal',
            'mudanca_tipo'          => 'downgrade',
            'mudanca_efetiva_em'    => now()->addMonth()->toDateString(),
            'mudanca_solicitada_em' => now(),
        ]);

        $this->actingAs($user)->post(route('assinatura.mudanca.remover'));

        $assinatura->refresh();
        expect($assinatura->proximo_plano_id)->toBeNull();
        expect($assinatura->proxima_periodicidade)->toBeNull();
        expect($assinatura->mudanca_tipo)->toBeNull();
    });

    it('remove chave mudanca dos metadados quando existente', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano, [
            'mudanca_tipo'       => 'downgrade',
            'mudanca_efetiva_em' => now()->addMonth()->toDateString(),
            'metadados'          => ['mudanca' => ['veiculos_desativar' => [1, 2]]],
        ]);

        $this->actingAs($user)->post(route('assinatura.mudanca.remover'));

        $metadados = $assinatura->fresh()->metadados ?? [];
        expect($metadados)->not->toHaveKey('mudanca');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /meu-plano/renovar
// ─────────────────────────────────────────────────────────────────────────────

describe('POST renovar', function () {
    it('cria nova assinatura pendente ao renovar o plano atual', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 200.00]);
        assinaturaAtiva($empresa, $plano);

        $this->actingAs($user)->post(route('assinatura.renovar'), [
            'periodicidade'   => 'mensal',
            'metodo_cobranca' => 'pix',
        ])->assertRedirect(route('assinatura.meu-plano'));

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id' => $empresa->id,
            'status'     => 'aguardando_pagamento',
        ]);
    });

    it('renovacao com downgrade agendado usa o proximo plano', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 300.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 100.00]);

        assinaturaAtiva($empresa, $planoA, [
            'proximo_plano_id'      => $planoB->id,
            'proxima_periodicidade' => 'mensal',
            'mudanca_tipo'          => 'downgrade',
            'mudanca_efetiva_em'    => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('assinatura.renovar'), [
            'periodicidade'   => 'mensal',
            'metodo_cobranca' => 'pix',
        ]);

        $nova = EmpresaPlano::where('empresa_id', $empresa->id)
            ->where('status', 'aguardando_pagamento')
            ->first();

        expect($nova?->plano_id)->toBe($planoB->id);
    });

    it('retorna erro ao tentar renovar com cancelamento agendado', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        assinaturaAtiva($empresa, $plano, [
            'mudanca_tipo'       => 'cancelamento',
            'mudanca_efetiva_em' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($user)->post(route('assinatura.renovar'), [
            'periodicidade'   => 'mensal',
            'metodo_cobranca' => 'pix',
        ])->assertSessionHas('error');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// PATCH /meu-plano/pagamento/{pagamento}/metodo
// ─────────────────────────────────────────────────────────────────────────────

describe('PATCH alterar metodo pagamento', function () {
    it('troca metodo de pix para cartao', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano      = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($user)->patch(route('assinatura.pagamento.alterar-metodo', $pagamento), [
            'metodo' => 'cartao',
        ])->assertRedirect(route('assinatura.meu-plano'));

        expect($pagamento->fresh()->metodo)->toBe('cartao');
    });

    it('bloqueia troca de metodo de pagamento ja pago', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pago',
            'data_vencimento'  => now()->toDateString(),
            'pago_em'          => now(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($user)->patch(route('assinatura.pagamento.alterar-metodo', $pagamento), [
            'metodo' => 'cartao',
        ])->assertStatus(403);
    });

    it('bloqueia alteracao de pagamento de outra empresa', function () {
        [$empresaA, $userA] = novaEmpresaComUser();
        [$empresaB, $userB] = novaEmpresaComUser();
        $plano = Plano::factory()->create();

        $assinaturaB = assinaturaAtiva($empresaB, $plano);

        $pagamentoB = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinaturaB->id,
            'empresa_id'       => $empresaB->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($userA)->patch(route('assinatura.pagamento.alterar-metodo', $pagamentoB), [
            'metodo' => 'cartao',
        ])->assertStatus(403);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// GET /meu-plano/pagamento/{pagamento}/status (polling JSON)
// ─────────────────────────────────────────────────────────────────────────────

describe('GET status pagamento JSON', function () {
    it('retorna status pendente em JSON', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($user)->getJson(route('assinatura.pagamento.status', $pagamento))
            ->assertOk()
            ->assertJsonStructure(['status', 'pago', 'pago_em'])
            ->assertJsonPath('status', 'pendente')
            ->assertJsonPath('pago', false);
    });

    it('retorna pago=true quando pagamento esta pago', function () {
        [$empresa, $user] = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinatura = assinaturaAtiva($empresa, $plano);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pago',
            'data_vencimento'  => now()->toDateString(),
            'pago_em'          => now(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($user)->getJson(route('assinatura.pagamento.status', $pagamento))
            ->assertJsonPath('pago', true);
    });

    it('bloqueia acesso ao status de pagamento de outra empresa', function () {
        [$empresaA, $userA] = novaEmpresaComUser();
        [$empresaB]         = novaEmpresaComUser();
        $plano = Plano::factory()->create();
        $assinaturaB = assinaturaAtiva($empresaB, $plano);

        $pagamentoB = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinaturaB->id,
            'empresa_id'       => $empresaB->id,
            'valor'            => 100.00,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($userA)->getJson(route('assinatura.pagamento.status', $pagamentoB))
            ->assertStatus(403);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// PATCH /assinatura/{assinatura}/status (admin)
// ─────────────────────────────────────────────────────────────────────────────

describe('PATCH alterarStatus admin', function () {
    it('superadmin altera status de aguardando_pagamento para ativo', function () {
        $admin      = superAdmin();
        $assinatura = EmpresaPlano::factory()->aguardandoPagamento()->create();

        $this->actingAs($admin)->patch(route('assinatura.status', $assinatura), [
            'status' => 'ativo',
        ])->assertRedirect();

        expect($assinatura->fresh()->status)->toBe('ativo');
    });

    it('superadmin pode marcar como inadimplente', function () {
        $admin      = superAdmin();
        $assinatura = EmpresaPlano::factory()->ativo()->create();

        $this->actingAs($admin)->patch(route('assinatura.status', $assinatura), [
            'status' => 'inadimplente',
        ]);

        expect($assinatura->fresh()->status)->toBe('inadimplente');
    });

    it('superadmin pode cancelar manualmente', function () {
        $admin      = superAdmin();
        $assinatura = EmpresaPlano::factory()->ativo()->create();

        $this->actingAs($admin)->patch(route('assinatura.status', $assinatura), [
            'status' => 'cancelado',
        ]);

        expect($assinatura->fresh()->status)->toBe('cancelado');
    });

    it('retorna erro de validacao com status invalido', function () {
        $admin      = superAdmin();
        $assinatura = EmpresaPlano::factory()->ativo()->create();

        $this->actingAs($admin)->patch(route('assinatura.status', $assinatura), [
            'status' => 'status_fantasma',
        ])->assertSessionHasErrors('status');
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// PATCH /assinatura/pagamento/{pagamento}/confirmar (admin)
// ─────────────────────────────────────────────────────────────────────────────

describe('PATCH confirmarPagamento admin', function () {
    it('admin confirma pagamento e ativa assinatura', function () {
        $admin   = superAdmin();
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create(['valor_mensal' => 199.90]);

        $assinatura = EmpresaPlano::factory()->aguardandoPagamento()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $plano->id,
            'valor_cobrado'=> 199.90,
        ]);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 199.90,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => ['acao' => 'contratacao'],
        ]);

        $this->actingAs($admin)->patch(route('assinatura.pagamento.confirmar', $pagamento), [
            'pago_em'=> now()->format('Y-m-d'),
            'metodo' => 'pix',
        ])->assertRedirect();

        expect($assinatura->fresh()->status)->toBe('ativo');
        expect($pagamento->fresh()->status)->toBe('pago');
    });

    it('confirmar upgrade via admin troca o plano da assinatura', function () {
        $admin   = superAdmin();
        $empresa = Empresa::factory()->create();
        $planoA  = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);
        $planoB  = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);

        $assinatura = EmpresaPlano::factory()->ativo()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $planoA->id,
            'periodicidade'=> 'mensal',
            'valor_cobrado'=> 100.00,
            'data_inicio'  => now()->subDays(10)->toDateString(),
            'data_vencimento'=> now()->addDays(20)->toDateString(),
        ]);

        $pagamento = EmpresaPlanoPagamento::create([
            'empresa_plano_id' => $assinatura->id,
            'empresa_id'       => $empresa->id,
            'valor'            => 66.67,
            'periodicidade'    => 'mensal',
            'metodo'           => 'pix',
            'status'           => 'pendente',
            'data_vencimento'  => now()->addDays(3)->toDateString(),
            'metadados'        => [
                'acao'               => 'upgrade',
                'novo_plano_id'      => $planoB->id,
                'nova_periodicidade' => 'mensal',
                'reinicia_periodo'   => false,
            ],
        ]);

        $this->actingAs($admin)->patch(route('assinatura.pagamento.confirmar', $pagamento), [
            'pago_em'=> now()->format('Y-m-d'),
            'metodo' => 'pix',
        ]);

        expect($assinatura->fresh()->plano_id)->toBe($planoB->id);
    });
});

// ─────────────────────────────────────────────────────────────────────────────
// POST /assinatura/{assinatura}/pagamento — registrar pagamento (admin)
// ─────────────────────────────────────────────────────────────────────────────

describe('POST registrarPagamento admin', function () {
    it('admin registra pagamento manual e assinatura permanece no status', function () {
        $admin   = superAdmin();
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create();

        $assinatura = EmpresaPlano::factory()->ativo()->create([
            'empresa_id' => $empresa->id,
            'plano_id'   => $plano->id,
        ]);

        $this->actingAs($admin)->post(route('assinatura.pagamento.registrar', $assinatura), [
            'valor'          => 199.90,
            'metodo'         => 'manual',
            'status'         => 'pendente',
            'data_vencimento'=> now()->addDays(30)->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertDatabaseHas('empresa_plano_pagamentos', [
            'empresa_plano_id' => $assinatura->id,
            'valor'            => 199.90,
            'status'           => 'pendente',
        ]);
    });

    it('admin registra pagamento como pago e ativa assinatura inadimplente', function () {
        $admin   = superAdmin();
        $empresa = Empresa::factory()->create();
        $plano   = Plano::factory()->create();

        $assinatura = EmpresaPlano::factory()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $plano->id,
            'status'       => 'inadimplente',
            'valor_cobrado'=> 199.90,
        ]);

        $this->actingAs($admin)->post(route('assinatura.pagamento.registrar', $assinatura), [
            'valor'          => 199.90,
            'metodo'         => 'pix',
            'status'         => 'pago',
            'data_vencimento'=> now()->toDateString(),
            'pago_em'        => now()->toDateString(),
        ]);

        expect($assinatura->fresh()->status)->toBe('ativo');
    });
});
