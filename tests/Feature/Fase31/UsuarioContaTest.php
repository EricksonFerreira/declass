<?php

use App\Models\Aluno;
use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function novoAluno($empresa, string $nome = 'Aluno Conta'): Aluno
{
    $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id, 'email' => strtolower(str_replace(' ', '.', $nome)).'@test.com']);

    return Aluno::create([
        'empresa_id' => $empresa->id,
        'pessoa_id' => $pessoa->id,
        'registro_academico' => 'RA-'.strtoupper(\Illuminate\Support\Str::random(6)),
        'ativo' => 'S',
    ]);
}

function novoResponsavel($empresa, string $nome = 'Responsável Conta'): Responsavel
{
    $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id]);

    return Responsavel::create([
        'empresa_id' => $empresa->id,
        'pessoa_id' => $pessoa->id,
        'ativo' => 'S',
    ]);
}

test('admin cria conta de aluno vinculada pela tela de usuarios', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = novoAluno($empresa);

    // Configura a sessao de redirecionamento (feito pelo index no uso real).
    $this->actingAs($admin)->get(route('usuario.index'));

    $this->actingAs($admin)->post(route('usuario.store'), [
        'nome' => 'Conta Aluno',
        'email' => 'aluno.conta@test.com',
        'tipo_conta' => 'aluno',
        'vinculo_id' => $aluno->id,
        'ativo' => '1',
    ])->assertRedirect(route('usuario.index'));

    $user = User::where('email', 'aluno.conta@test.com')->first();

    expect($user)->not->toBeNull();
    expect($aluno->fresh()->user_id)->toBe($user->id);
    expect($user->regra?->name)->toBe('aluno');
    expect($user->tipoConta())->toBe('aluno');
    expect($user->isPortalAccount())->toBeTrue();
});

test('admin cria conta de responsavel vinculada pela tela de usuarios', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $responsavel = novoResponsavel($empresa);

    $this->actingAs($admin)->get(route('usuario.index'));

    $this->actingAs($admin)->post(route('usuario.store'), [
        'nome' => 'Conta Responsavel',
        'email' => 'resp.conta@test.com',
        'tipo_conta' => 'responsavel',
        'vinculo_id' => $responsavel->id,
        'ativo' => '1',
    ])->assertRedirect(route('usuario.index'));

    $user = User::where('email', 'resp.conta@test.com')->first();

    expect($user)->not->toBeNull();
    expect($responsavel->fresh()->user_id)->toBe($user->id);
    expect($user->regra?->name)->toBe('responsavel_legal');
    expect($user->tipoConta())->toBe('responsavel');
});

test('conta de portal exige vinculo valido do mesmo tenant', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $alunoDeOutroTenant = novoAluno($empresaB, 'Aluno B');

    $this->actingAs($admin)->post(route('usuario.store'), [
        'nome' => 'Conta Invalida',
        'email' => 'invalida@test.com',
        'tipo_conta' => 'aluno',
        'vinculo_id' => $alunoDeOutroTenant->id,
    ])->assertSessionHasErrors('vinculo_id');

    $this->actingAs($admin)->post(route('usuario.store'), [
        'nome' => 'Conta Sem Vinculo',
        'email' => 'semvinculo@test.com',
        'tipo_conta' => 'aluno',
    ])->assertSessionHasErrors('vinculo_id');

    expect(User::where('email', 'invalida@test.com')->exists())->toBeFalse();
    expect(User::where('email', 'semvinculo@test.com')->exists())->toBeFalse();
    expect($alunoDeOutroTenant->fresh()->user_id)->toBeNull();
});

test('nao permite vincular aluno que ja possui conta', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = novoAluno($empresa);
    $donoDaConta = usuarioTenant($empresa, 'aluno');
    $aluno->update(['user_id' => $donoDaConta->id]);

    $this->actingAs($admin)->post(route('usuario.store'), [
        'nome' => 'Conta Duplicada',
        'email' => 'duplicada@test.com',
        'tipo_conta' => 'aluno',
        'vinculo_id' => $aluno->id,
    ])->assertSessionHasErrors('vinculo_id');
});

test('editar usuario troca o vinculo e o tipo de conta', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $alunoA = novoAluno($empresa, 'Aluno A');
    $alunoB = novoAluno($empresa, 'Aluno B');
    $responsavel = novoResponsavel($empresa);

    $user = usuarioTenant($empresa, 'aluno', ['email' => 'conta@test.com']);
    $alunoA->update(['user_id' => $user->id]);

    $this->actingAs($admin)->get(route('usuario.index'));

    // Troca o aluno vinculado
    $this->actingAs($admin)->put(route('usuario.update', $user->id), [
        'nome' => 'Conta',
        'email' => 'conta@test.com',
        'tipo_conta' => 'aluno',
        'vinculo_id' => $alunoB->id,
        'ativo' => '1',
    ])->assertRedirect(route('usuario.index'));

    expect($alunoA->fresh()->user_id)->toBeNull();
    expect($alunoB->fresh()->user_id)->toBe($user->id);

    // Muda o tipo para responsavel
    $this->actingAs($admin)->put(route('usuario.update', $user->id), [
        'nome' => 'Conta',
        'email' => 'conta@test.com',
        'tipo_conta' => 'responsavel',
        'vinculo_id' => $responsavel->id,
        'ativo' => '1',
    ])->assertRedirect(route('usuario.index'));

    expect($alunoB->fresh()->user_id)->toBeNull();
    expect($responsavel->fresh()->user_id)->toBe($user->id);
    expect($user->fresh()->regra?->name)->toBe('responsavel_legal');

    // Volta para colaborador: remove o vinculo
    $roleMembro = DB::table('roles')->where('empresa_id', $empresa->id)->where('name', 'membro')->value('id');
    $this->actingAs($admin)->put(route('usuario.update', $user->id), [
        'nome' => 'Conta',
        'email' => 'conta@test.com',
        'tipo_conta' => 'colaborador',
        'role_id' => $roleMembro,
        'ativo' => '1',
    ])->assertRedirect(route('usuario.index'));

    expect($responsavel->fresh()->user_id)->toBeNull();
    expect($user->fresh()->tipoConta())->toBe('colaborador');
});

test('index lista tipos e filtra por tipo de conta', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $aluno = novoAluno($empresa);
    $contaAluno = usuarioTenant($empresa, 'aluno', ['nome' => 'Conta Aluno', 'email' => 'aluno.idx@test.com']);
    $aluno->update(['user_id' => $contaAluno->id]);

    $this->actingAs($admin)->get(route('usuario.index'))
        ->assertOk()
        ->assertSee('Conta Aluno')
        ->assertSee('badge-primary');

    // Filtro por tipo aluno: nao deve listar o admin (colaborador)
    $this->actingAs($admin)->get(route('usuario.filtro', ['tipo_conta' => 'aluno']));
    $this->actingAs($admin)->get(route('usuario.index'))
        ->assertOk()
        ->assertSee('Conta Aluno')
        ->assertDontSee($admin->email);
});

test('conta de aluno entra direto no portal apos login', function () {
    $empresa = tenant();
    $aluno = novoAluno($empresa);
    $user = usuarioTenant($empresa, 'aluno', [
        'email' => 'login.portal@test.com',
        'password' => 'password',
    ]);
    $aluno->update(['user_id' => $user->id]);

    $response = $this->post(route('login'), [
        'slug' => $empresa->slug,
        'email' => 'login.portal@test.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('portal.index'));
});
