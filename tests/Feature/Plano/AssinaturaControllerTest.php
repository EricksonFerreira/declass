<?php

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\EmpresaPlanoPagamento;
use App\Models\Plano;
use App\Models\Regra;
use App\Models\User;
use App\Models\Veiculo;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function criarEmpresaComUser(array $empresaAtrs = [], array $userAttrs = []): array
{
    $empresa = Empresa::factory()->create($empresaAtrs);
    $regra   = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
    $user    = User::factory()->create(array_merge(['empresa_id' => $empresa->id, 'role_id' => $regra->id], $userAttrs));

    return [$empresa, $user];
}

function criarSuperAdmin(): User
{
    $regra = Regra::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

    return User::factory()->create(['role_id' => 1]);
}

// ---------------------------------------------------------------------------
// meuPlano
// ---------------------------------------------------------------------------

describe('GET /meu-plano', function () {
    it('empresa sem assinatura ve a pagina de planos disponiveis', function () {
        [$empresa, $user] = criarEmpresaComUser();

        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertStatus(200);
        $response->assertViewIs('assinatura.meu-plano');
    });

    it('empresa com assinatura ativa ve seu plano atual', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create();

        EmpresaPlano::factory()->ativo()->create([
            'empresa_id' => $empresa->id,
            'plano_id'   => $plano->id,
        ]);

        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertStatus(200);
        $response->assertViewHas('assinatura');
    });
});

// ---------------------------------------------------------------------------
// contratar (POST /meu-plano/contratar)
// ---------------------------------------------------------------------------

describe('POST /meu-plano/contratar', function () {
    it('cria assinatura com pagamento pendente para nova contratacao', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 199.90]);

        $response = $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        $response->assertRedirect(route('assinatura.meu-plano'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id' => $empresa->id,
            'plano_id'   => $plano->id,
            'status'     => 'aguardando_pagamento',
        ]);
    });

    it('retorna erro ao tentar contratar o mesmo plano e periodicidade ja ativa', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 199.90]);

        EmpresaPlano::factory()->ativo()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $plano->id,
            'periodicidade'=> 'mensal',
            'valor_cobrado'=> 199.90,
        ]);

        $response = $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        $response->assertSessionHasErrors('plano_id');
    });

    it('agenda downgrade quando valor novo e menor que valor atual', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $planoA = Plano::factory()->create(['valor_mensal' => 300.00, 'valor_anual' => 270.00]);
        $planoB = Plano::factory()->create(['valor_mensal' => 100.00, 'valor_anual' => 90.00]);

        EmpresaPlano::factory()->ativo()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $planoA->id,
            'periodicidade'=> 'mensal',
            'valor_cobrado'=> 300.00,
        ]);

        $response = $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        $response->assertRedirect(route('assinatura.meu-plano'));

        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id'      => $empresa->id,
            'plano_id'        => $planoA->id,
            'proximo_plano_id'=> $planoB->id,
            'mudanca_tipo'    => 'downgrade',
        ]);
    });

    it('redireciona para selecao de veiculos quando ha excedentes no downgrade', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $planoA = Plano::factory()->ilimitado()->create(['valor_mensal' => 500.00, 'valor_anual' => 450.00]);
        $planoB = Plano::factory()->comLimite(1)->create(['valor_mensal' => 99.00, 'valor_anual' => 80.00]);

        EmpresaPlano::factory()->ativo()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $planoA->id,
            'periodicidade'=> 'mensal',
            'valor_cobrado'=> 500.00,
        ]);

        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'A', 'placa' => 'TES0001']);
        Veiculo::create(['empresa_id' => $empresa->id, 'ativo' => true, 'vendido' => false, 'nome' => 'B', 'placa' => 'TES0002']);

        $response = $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $planoB->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        $response->assertRedirect(route('assinatura.downgrade'));
    });

    it('agenda mudanca para mensal quando assinatura atual e anual', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create(['valor_mensal' => 200.00, 'valor_anual' => 170.00]);

        EmpresaPlano::factory()->anual()->ativo()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $plano->id,
            'valor_cobrado'=> 170.00 * 12,
        ]);

        $response = $this->actingAs($user)->post(route('assinatura.contratar.confirmar'), [
            'plano_id'         => $plano->id,
            'periodicidade'    => 'mensal',
            'metodo_pagamento' => 'pix',
        ]);

        $response->assertRedirect(route('assinatura.meu-plano'));
        $this->assertDatabaseHas('empresa_planos', [
            'empresa_id'  => $empresa->id,
            'mudanca_tipo'=> 'downgrade',
        ]);
    });
});

// ---------------------------------------------------------------------------
// cancelar
// ---------------------------------------------------------------------------

describe('POST /meu-plano/cancelar', function () {
    it('agenda cancelamento para fim do periodo vigente', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create();

        $assinatura = EmpresaPlano::factory()->ativo()->create([
            'empresa_id'   => $empresa->id,
            'plano_id'     => $plano->id,
            'data_vencimento' => now()->addMonth()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('assinatura.cancelar'));

        $response->assertRedirect(route('assinatura.meu-plano'));

        $assinatura->refresh();
        expect($assinatura->mudanca_tipo)->toBe('cancelamento');
        expect($assinatura->mudanca_efetiva_em->format('Y-m-d'))->toBe($assinatura->data_vencimento->format('Y-m-d'));
    });

    it('nao cancela imediatamente - assinatura permanece ativa', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create();

        $assinatura = EmpresaPlano::factory()->ativo()->create([
            'empresa_id' => $empresa->id,
            'plano_id'   => $plano->id,
        ]);

        $this->actingAs($user)->post(route('assinatura.cancelar'));

        expect($assinatura->fresh()->status)->toBe('ativo');
    });
});

// ---------------------------------------------------------------------------
// removerMudancaAgendada
// ---------------------------------------------------------------------------

describe('POST /meu-plano/mudanca/remover', function () {
    it('remove mudanca agendada com sucesso', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create();

        $assinatura = EmpresaPlano::factory()->ativo()->create([
            'empresa_id'            => $empresa->id,
            'plano_id'              => $plano->id,
            'mudanca_tipo'          => 'cancelamento',
            'mudanca_efetiva_em'    => now()->addMonth()->toDateString(),
            'mudanca_solicitada_em' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('assinatura.mudanca.remover'));

        $response->assertRedirect(route('assinatura.meu-plano'));

        $assinatura->refresh();
        expect($assinatura->mudanca_tipo)->toBeNull();
        expect($assinatura->mudanca_efetiva_em)->toBeNull();
    });
});

// ---------------------------------------------------------------------------
// alterarStatus (admin)
// ---------------------------------------------------------------------------

describe('PATCH /assinatura/{assinatura}/status', function () {
    it('superadmin pode alterar status da assinatura', function () {
        $admin = criarSuperAdmin();
        $assinatura = EmpresaPlano::factory()->aguardandoPagamento()->create();

        $response = $this->actingAs($admin)->patch(route('assinatura.status', $assinatura), [
            'status' => 'ativo',
        ]);

        $response->assertRedirect();
        expect($assinatura->fresh()->status)->toBe('ativo');
    });

    it('retorna erro com status invalido', function () {
        $admin = criarSuperAdmin();
        $assinatura = EmpresaPlano::factory()->ativo()->create();

        $response = $this->actingAs($admin)->patch(route('assinatura.status', $assinatura), [
            'status' => 'status_invalido',
        ]);

        $response->assertSessionHasErrors('status');
    });
});

// ---------------------------------------------------------------------------
// confirmarPagamento (admin)
// ---------------------------------------------------------------------------

describe('PATCH /assinatura/pagamento/{pagamento}/confirmar', function () {
    it('admin confirma pagamento e ativa assinatura', function () {
        $admin   = criarSuperAdmin();
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

        $response = $this->actingAs($admin)->patch(route('assinatura.pagamento.confirmar', $pagamento), [
            'pago_em'=> now()->format('Y-m-d'),
            'metodo' => 'pix',
        ]);

        $response->assertRedirect();
        expect($assinatura->fresh()->status)->toBe('ativo');
        expect($pagamento->fresh()->status)->toBe('pago');
    });
});

// ---------------------------------------------------------------------------
// statusPagamento (JSON polling)
// ---------------------------------------------------------------------------

describe('GET /meu-plano/pagamento/{pagamento}/status', function () {
    it('retorna status do pagamento em JSON', function () {
        [$empresa, $user] = criarEmpresaComUser();
        $plano = Plano::factory()->create();

        $assinatura = EmpresaPlano::factory()->ativo()->create([
            'empresa_id' => $empresa->id,
            'plano_id'   => $plano->id,
        ]);

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

        $response = $this->actingAs($user)->getJson(route('assinatura.pagamento.status', $pagamento));

        $response->assertOk();
        $response->assertJsonStructure(['status', 'pago', 'pago_em']);
        $response->assertJsonPath('status', 'pendente');
        $response->assertJsonPath('pago', false);
    });
});
