<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Mensalidade;
use App\Models\Nota;
use App\Models\Ocorrencia;
use App\Models\PeriodoLetivo;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;

function historicoFixture($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Unidade Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026,
        'data_inicio' => '2026-02-01', 'data_fim' => '2026-12-15',
        'status' => 'letivo', 'periodicidade' => 'semestral',
        'empresa_id' => $empresa->id,
    ]);
    PeriodoLetivo::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'nome' => '1º Semestre', 'ordem' => 1, 'ativo' => 'S',
        'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create(['nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano EF A', 'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'empresa_id' => $empresa->id]);

    $pessoa = Pessoa::create(['empresa_id' => $empresa->id, 'nome' => 'Aluno Historico']);
    $aluno = Aluno::create([
        'empresa_id' => $empresa->id, 'pessoa_id' => $pessoa->id,
        'escola_id' => $escola->id, 'registro_academico' => 'RA-TESTE',
    ]);
    $matricula = Matricula::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'data_matricula' => '2026-02-01', 'numero_chamada' => 1,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'pessoa', 'aluno', 'matricula');
}

test('historico exibe secoes consolidadas do aluno', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['aluno' => $aluno] = historicoFixture($empresa);

    $this->actingAs($user)->get("/admin/aluno/{$aluno->id}/historico")
        ->assertOk()
        ->assertSee('<a href="'.route('aluno.show', $aluno->id).'">'.$aluno->nome.'</a>', false)
        ->assertSee('Aluno Historico')
        ->assertSee('Histórico escolar por ano letivo')
        ->assertSee('Linha do tempo')
        ->assertSee('Financeiro')
        ->assertSee('1º Ano EF A')
        ->assertSee('2026')
        ->assertSee('Exportar PDF');
});

test('historico consolida desempenho, financeiro e eventos', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $prof = usuarioTenant($empresa, 'professor');
    ['aluno' => $aluno, 'matricula' => $matricula, 'turma' => $turma, 'disciplina' => $disciplina, 'escola' => $escola] = historicoFixture($empresa);

    $diario = Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $prof->id,
        'data' => '2026-03-10', 'conteudo' => 'Equações',
    ]);
    Frequencia::create(['empresa_id' => $empresa->id, 'diario_id' => $diario->id, 'matricula_id' => $matricula->id, 'status' => 'presente']);
    $diario2 = Diario::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $prof->id,
        'data' => '2026-03-11', 'conteudo' => 'Equações 2',
    ]);
    Frequencia::create(['empresa_id' => $empresa->id, 'diario_id' => $diario2->id, 'matricula_id' => $matricula->id, 'status' => 'falta']);

    $avaliacao = Avaliacao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
        'disciplina_id' => $disciplina->id, 'user_id' => $prof->id,
        'tipo' => 'prova', 'nome' => 'P1', 'nota_maxima' => 10,
    ]);
    Nota::create(['empresa_id' => $empresa->id, 'avaliacao_id' => $avaliacao->id, 'matricula_id' => $matricula->id, 'nota' => 8]);

    Mensalidade::create([
        'empresa_id' => $empresa->id, 'matricula_id' => $matricula->id,
        'competencia' => '2026-03', 'valor' => 500, 'vencimento' => '2020-01-10', 'status' => 'pendente',
    ]);
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'matricula_id' => $matricula->id,
        'competencia' => '2026-04', 'valor' => 500, 'vencimento' => '2099-01-10', 'status' => 'pendente',
    ]);
    Mensalidade::create([
        'empresa_id' => $empresa->id, 'matricula_id' => $matricula->id,
        'competencia' => '2026-02', 'valor' => 500, 'vencimento' => '2026-02-10', 'status' => 'paga',
    ]);

    Ocorrencia::create([
        'empresa_id' => $empresa->id, 'escola_id' => $escola->id,
        'aluno_id' => $aluno->id, 'data' => '2026-03-15',
        'tipo' => 'elogio', 'gravidade' => 'leve', 'descricao' => 'Participação em aula',
    ]);

    $this->actingAs($admin)->get("/admin/aluno/{$aluno->id}/historico")
        ->assertOk()
        ->assertSee('50%')          // frequencia: 1 presente de 2
        ->assertSee('80%')          // media: nota 8 de 10
        ->assertSee('R$ 1.000,00')  // em aberto (2 pendentes)
        ->assertSee('Atrasadas')
        ->assertSee('Elogio')
        ->assertSee('Participação em aula');
});

test('historico pdf baixa documento do aluno', function () {
    $empresa = tenant();
    $user = usuarioTenant($empresa, 'administrador');
    ['aluno' => $aluno] = historicoFixture($empresa);

    $response = $this->actingAs($user)->get("/admin/aluno/{$aluno->id}/historico/pdf");

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('pdf');
});

test('aluno de outro tenant retorna 404 no historico', function () {
    $empresa = tenant();
    $empresaB = tenant();
    $user = usuarioTenant($empresa, 'administrador');

    app(EmpresaContext::class)->setEmpresaId($empresaB->id);
    $escolaB = Escola::create(['nome' => 'Outra', 'empresa_id' => $empresaB->id]);
    $pessoaB = Pessoa::create(['empresa_id' => $empresaB->id, 'nome' => 'Aluno B']);
    $alunoB = Aluno::create([
        'empresa_id' => $empresaB->id, 'pessoa_id' => $pessoaB->id,
        'escola_id' => $escolaB->id,
    ]);

    $this->actingAs($user)->get("/admin/aluno/{$alunoB->id}/historico")
        ->assertNotFound();
});
