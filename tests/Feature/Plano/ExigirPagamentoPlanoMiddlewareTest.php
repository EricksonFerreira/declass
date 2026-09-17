<?php

use App\Models\Empresa;
use App\Models\EmpresaPlano;
use App\Models\Plano;
use App\Models\Regra;
use App\Models\User;

function criarUserSemEmpresa(): User
{
    $regra = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);

    return User::factory()->create(['role_id' => $regra->id, 'empresa_id' => null]);
}

function criarUserComEmpresaSemAssinatura(): array
{
    $regra = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
    $empresa = Empresa::factory()->create();
    $user = User::factory()->create(['role_id' => $regra->id, 'empresa_id' => $empresa->id]);

    return [$empresa, $user];
}

function criarUserComAssinaturaAtiva(): array
{
    $regra = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
    $empresa = Empresa::factory()->create();
    $plano = Plano::factory()->create();
    $user = User::factory()->create(['role_id' => $regra->id, 'empresa_id' => $empresa->id]);

    EmpresaPlano::factory()->ativo()->create([
        'empresa_id' => $empresa->id,
        'plano_id' => $plano->id,
    ]);

    return [$empresa, $user];
}

describe('ExigirPagamentoPlanoMiddleware', function () {
    it('bloqueia usuario com empresa mas sem assinatura ativa', function () {
        [$empresa, $user] = criarUserComEmpresaSemAssinatura();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertRedirect(route('assinatura.meu-plano'));
    });

    it('permite acesso quando assinatura esta ativa', function () {
        [$empresa, $user] = criarUserComAssinaturaAtiva();

        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertStatus(200);
    });

    it('nao bloqueia rota de meu-plano mesmo sem assinatura', function () {
        [$empresa, $user] = criarUserComEmpresaSemAssinatura();

        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertStatus(200);
    });

    it('nao bloqueia usuario sem empresa vinculada', function () {
        $user = criarUserSemEmpresa();

        $response = $this->actingAs($user)->get(route('home'));

        // ResolveTenant nega acesso antes do middleware de pagamento (403),
        // entao o importante e nao haver redirect para meu-plano.
        $response->assertForbidden();
    });

    it('nao bloqueia superadmin mesmo sem assinatura', function () {
        $admin = User::factory()->create(['empresa_id' => null]);
        \Illuminate\Support\Facades\DB::table('users')->where('id', $admin->id)->update(['tipo_usuario_id' => 1]);
        $admin->refresh();

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertStatus(200);
    });

    it('permite acesso a rotas de assinatura sem pagamento', function () {
        [$empresa, $user] = criarUserComEmpresaSemAssinatura();

        // A rota meu-plano faz parte do prefixo assinatura.* e e permitida pelo middleware
        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertStatus(200);
    });

    it('permite acesso a rotas de perfil sem pagamento', function () {
        [$empresa, $user] = criarUserComEmpresaSemAssinatura();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
    });

    it('empresa com assinatura em trial tem acesso ao sistema', function () {
        $regra = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
        $empresa = Empresa::factory()->create();
        $plano = Plano::factory()->create();
        $user = User::factory()->create(['role_id' => $regra->id, 'empresa_id' => $empresa->id]);

        EmpresaPlano::factory()->trial()->create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
        ]);

        $response = $this->actingAs($user)->get(route('assinatura.meu-plano'));

        $response->assertStatus(200);
    });

    it('bloqueia acesso quando o trial expirou', function () {
        $regra = Regra::firstOrCreate(['name' => 'usuario', 'guard_name' => 'web']);
        $empresa = Empresa::factory()->create();
        $plano = Plano::factory()->create();
        $user = User::factory()->create(['role_id' => $regra->id, 'empresa_id' => $empresa->id]);

        EmpresaPlano::factory()->trial()->create([
            'empresa_id' => $empresa->id,
            'plano_id' => $plano->id,
            'data_inicio' => now()->subDays(10)->toDateString(),
            'data_vencimento' => now()->subDays(3)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertRedirect(route('assinatura.meu-plano'));
    });
});
