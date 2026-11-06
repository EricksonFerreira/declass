<?php

use App\Models\Escola;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Notification;

function setupMatriculaPublica($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create([
        'nome' => 'Escola Pública', 'slug' => 'escola-publica',
        'empresa_id' => $empresa->id, 'ativa' => 'S',
    ]);

    return compact('escola');
}

function urlMatricula($empresa, $escola): string
{
    return "/matricula/{$empresa->slug}/{$escola->slug}";
}

test('visitante ve formulario publico da escola', function () {
    $empresa = tenant(['slug' => 'rede-publica']);
    $ids = setupMatriculaPublica($empresa);

    $this->get(urlMatricula($empresa, $ids['escola']))
        ->assertOk()
        ->assertSee('Escola Pública')
        ->assertSee('nome_aluno');
});

test('visitante envia solicitacao que cai na fila como portal', function () {
    Notification::fake();
    $empresa = tenant(['slug' => 'rede-publica2']);
    $ids = setupMatriculaPublica($empresa);
    $secretaria = usuarioTenant($empresa, 'secretario');

    $this->post(urlMatricula($empresa, $ids['escola']), [
        'nome_aluno' => 'Criança Portal',
        'data_nascimento_aluno' => '2020-01-15',
        'sexo_aluno' => 'feminino',
        'nome_responsavel' => 'Mãe Portal',
        'parentesco' => 'mae',
        'telefone_responsavel' => '11988887777',
        'email_responsavel' => 'mae@portal.com',
    ])->assertOk()->assertSee('Solicitação enviada');

    $this->assertDatabaseHas('solicitacoes_matricula', [
        'nome_aluno' => 'Criança Portal',
        'origem' => 'portal',
        'status' => 'pendente',
        'escola_id' => $ids['escola']->id,
        'empresa_id' => $empresa->id,
    ]);

    Notification::assertSentTo($secretaria, \App\Notifications\AvisoSistemaNotification::class);
});

test('formulario publico valida campos obrigatorios', function () {
    $empresa = tenant(['slug' => 'rede-publica3']);
    $ids = setupMatriculaPublica($empresa);

    $this->post(urlMatricula($empresa, $ids['escola']), [])
        ->assertSessionHasErrors(['nome_aluno', 'nome_responsavel']);

    $this->assertDatabaseMissing('solicitacoes_matricula', ['nome_aluno' => '']);
});

test('slug invalido ou escola inativa retorna 404', function () {
    $empresa = tenant(['slug' => 'rede-publica4']);
    $ids = setupMatriculaPublica($empresa);

    $this->get("/matricula/{$empresa->slug}/escola-que-nao-existe")->assertNotFound();
    $this->get("/matricula/slug-inexistente/{$ids['escola']->slug}")->assertNotFound();
});
