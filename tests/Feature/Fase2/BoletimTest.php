<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Nota;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupBoletim($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Central', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '1º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '1º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'sigla' => 'MAT', 'empresa_id' => $empresa->id]);

    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $pessoa = Pessoa::create(['nome' => 'Maria Silva', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create([
        'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
        'registro_academico' => 'RA-0001', 'empresa_id' => $empresa->id,
    ]);
    $matricula = Matricula::create([
        'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
        'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
        'numero_chamada' => 1, 'empresa_id' => $empresa->id,
    ]);

    return compact('escola', 'anoLetivo', 'etapa', 'turma', 'disciplina', 'aluno', 'matricula');
}

function avaliarComNota($ids, $admin, float $nota): void
{
    $av = Avaliacao::create([
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'user_id' => $admin->id,
        'tipo' => 'prova',
        'nome' => 'Prova '.fake()->unique()->numberBetween(1, 9999),
        'nota_maxima' => 10,
        'empresa_id' => $ids['turma']->empresa_id,
    ]);

    Nota::create([
        'avaliacao_id' => $av->id,
        'matricula_id' => $ids['matricula']->id,
        'nota' => $nota,
        'empresa_id' => $ids['turma']->empresa_id,
    ]);
}

test('boletim da turma exibe medias e frequencia', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupBoletim($empresa);

    // duas avaliacoes: media = (8+6)/2 = 7
    avaliarComNota($ids, $admin, 8);
    avaliarComNota($ids, $admin, 6);

    // frequencia: 1 aula, 1 presente => 100%
    $prof = $admin;
    $this->actingAs($prof)->post(route('diario.store'), [
        'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id,
        'data' => '2026-03-10',
        'conteudo' => 'Aula teste',
    ]);
    $diario = Diario::first();
    $this->actingAs($prof)->put(route('diario.frequencia.salvar', $diario->id), [
        'frequencias' => [['matricula_id' => $ids['matricula']->id, 'status' => 'presente']],
    ]);

    $response = $this->actingAs($admin)->get(route('boletim.turma', $ids['turma']->id));
    $response->assertOk();

    $dados = $response->viewData('medias');
    expect($dados["{$ids['matricula']->id}:{$ids['disciplina']->id}"])->toBe(7.0);

    $freqs = $response->viewData('frequencias');
    expect($freqs->get($ids['matricula']->id)['pct'])->toBe(100.0);
});

test('boletim individual exibe avaliacoes e notas do aluno', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupBoletim($empresa);

    avaliarComNota($ids, $admin, 9);

    $response = $this->actingAs($admin)->get(route('boletim.aluno', $ids['aluno']->id));
    $response->assertOk()
        ->assertSee('Maria Silva')
        ->assertSee('9,0 / 10,0');

    $medias = $response->viewData('medias');
    expect($medias[$ids['disciplina']->id])->toBe(9.0);
});

test('aluno sem matricula ativa retorna 404 no boletim', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $pessoa = Pessoa::create(['nome' => 'Sem Matricula', 'empresa_id' => $empresa->id]);
    $aluno = Aluno::create(['pessoa_id' => $pessoa->id, 'empresa_id' => $empresa->id]);

    $this->actingAs($admin)->get(route('boletim.aluno', $aluno->id))->assertNotFound();
});

test('professor so ve boletim de turmas que leciona', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $professor = usuarioTenant($empresa, 'professor');
    $ids = setupBoletim($empresa);

    // professor SEM atribuicao -> 403 na turma
    $this->actingAs($professor)->get(route('boletim.turma', $ids['turma']->id))->assertForbidden();
    $this->actingAs($professor)->get(route('boletim.aluno', $ids['aluno']->id))->assertForbidden();

    // com atribuicao -> acesso liberado
    Atribuicao::create([
        'empresa_id' => $empresa->id, 'turma_id' => $ids['turma']->id,
        'disciplina_id' => $ids['disciplina']->id, 'user_id' => $professor->id,
        'papel' => 'titular',
    ]);

    $this->actingAs($professor)->get(route('boletim.turma', $ids['turma']->id))->assertOk();
    $this->actingAs($professor)->get(route('boletim.aluno', $ids['aluno']->id))->assertOk();

    // seletor so lista a turma do professor
    $response = $this->actingAs($professor)->get(route('boletim.index'));
    $turmasVisiveis = $response->viewData('turmas');
    expect($turmasVisiveis)->toHaveCount(1)
        ->and($turmasVisiveis->first()->id)->toBe($ids['turma']->id);
});

test('boletim tem escopo de tenant', function () {
    $empresa = tenant();
    $admin = usuarioTenant($empresa, 'administrador');
    $ids = setupBoletim($empresa);

    avaliarComNota($ids, $admin, 7);
    expect(Avaliacao::count())->toBe(1);

    // sob outro tenant, nada e visivel
    app(EmpresaContext::class)->setEmpresaId(tenant()->id);
    expect(Avaliacao::count())->toBe(0)
        ->and(Nota::count())->toBe(0)
        ->and(Matricula::count())->toBe(0);
});
