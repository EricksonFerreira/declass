<?php

use App\Models\Aluno;
use App\Models\AnoLetivo;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Pessoa;
use App\Models\Turma;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

function setupRelatorioFrequencia($empresa): array
{
    app(EmpresaContext::class)->setEmpresaId($empresa->id);

    $escola = Escola::create(['nome' => 'Escola Rel', 'empresa_id' => $empresa->id]);
    $anoLetivo = AnoLetivo::create([
        'escola_id' => $escola->id, 'ano' => 2026, 'empresa_id' => $empresa->id,
    ]);
    $etapa = Etapa::create([
        'nome' => '3º Ano EF', 'segmento' => 'fundamental', 'empresa_id' => $empresa->id,
    ]);
    $turma = Turma::create([
        'escola_id' => $escola->id, 'ano_letivo_id' => $anoLetivo->id,
        'etapa_id' => $etapa->id, 'nome' => '3º Ano A', 'turno' => 'matutino',
        'empresa_id' => $empresa->id,
    ]);
    $disciplina = Disciplina::create(['nome' => 'Matemática', 'empresa_id' => $empresa->id]);
    DB::table('turma_disciplina')->insert([
        'turma_id' => $turma->id, 'disciplina_id' => $disciplina->id,
        'carga_horaria_semanal' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $matriculas = collect();
    foreach (['Ana Souza', 'Bruno Lima', 'Carla Reis'] as $i => $nome) {
        $pessoa = Pessoa::create(['nome' => $nome, 'empresa_id' => $empresa->id]);
        $aluno = Aluno::create([
            'pessoa_id' => $pessoa->id, 'escola_id' => $escola->id,
            'registro_academico' => 'RA-R'.($i + 1), 'empresa_id' => $empresa->id,
        ]);
        $matriculas->push(Matricula::create([
            'aluno_id' => $aluno->id, 'turma_id' => $turma->id,
            'ano_letivo_id' => $anoLetivo->id, 'status' => 'ativa',
            'numero_chamada' => $i + 1, 'empresa_id' => $empresa->id,
        ]));
    }

    $professor = usuarioTenant($empresa, 'professor');

    // 4 aulas: Ana falta 3 (25%), Bruno falta 4 (0% -> risco), Carla falta 0 (100%)
    $diarios = collect();
    foreach (range(1, 4) as $d) {
        $diarios->push(Diario::create([
            'empresa_id' => $empresa->id, 'turma_id' => $turma->id,
            'disciplina_id' => $disciplina->id, 'user_id' => $professor->id,
            'data' => "2026-03-0{$d}",
            'conteudo' => 'Aula',
        ]));
    }

    foreach ($diarios as $diario) {
        // Ana: falta nas aulas 1,2,3 e presente na 4 -> 25%
        Frequencia::create([
            'empresa_id' => $empresa->id, 'diario_id' => $diario->id,
            'matricula_id' => $matriculas[0]->id,
            'status' => $diario->id === $diarios->last()->id ? 'presente' : 'falta',
        ]);
        // Bruno: falta em todas -> 0%
        Frequencia::create([
            'empresa_id' => $empresa->id, 'diario_id' => $diario->id,
            'matricula_id' => $matriculas[1]->id,
            'status' => 'falta',
        ]);
        // Carla: presente em todas -> 100%
        Frequencia::create([
            'empresa_id' => $empresa->id, 'diario_id' => $diario->id,
            'matricula_id' => $matriculas[2]->id,
            'status' => 'presente',
        ]);
    }

    return compact('escola', 'anoLetivo', 'turma', 'disciplina', 'matriculas');
}

test('coordenador acessa relatorio de frequencia com ranking de faltas', function () {
    $empresa = tenant();
    $ids = setupRelatorioFrequencia($empresa);
    $user = usuarioTenant($empresa, 'coordenador_pedagogico');

    $response = $this->actingAs($user)->get(route('relatorio_frequencia.index'));
    $response->assertOk()
        ->assertSee('Ana Souza')
        ->assertSee('Bruno Lima')
        ->assertSee('Carla Reis');

    $resumo = $response->viewData('resumo');
    expect($resumo['alunos'])->toBe(3)
        ->and($resumo['com_frequencia'])->toBe(3)
        ->and($resumo['em_risco'])->toBe(2); // Ana 25% e Bruno 0%

    // ordenado por faltas desc: Bruno (4) primeiro, depois Ana (3), Carla (0)
    $linhas = $response->viewData('matriculas')->items();
    expect($linhas[0]->aluno)->toBe('Bruno Lima')
        ->and($linhas[2]->aluno)->toBe('Carla Reis');
});

test('filtro apenas risco retorna somente alunos abaixo de 75%', function () {
    $empresa = tenant();
    setupRelatorioFrequencia($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($user)
        ->get(route('relatorio_frequencia.index', ['apenas_risco' => 1]));

    $response->assertOk()
        ->assertSee('Bruno Lima')
        ->assertSee('Ana Souza')
        ->assertDontSee('Carla Reis');
});

test('filtro por turma restringe o relatorio', function () {
    $empresa = tenant();
    $ids = setupRelatorioFrequencia($empresa);
    $user = usuarioTenant($empresa, 'administrador');

    $response = $this->actingAs($user)
        ->get(route('relatorio_frequencia.index', ['turma_id' => $ids['turma']->id]));
    $response->assertOk();
    expect($response->viewData('resumo')['alunos'])->toBe(3);
});

test('professor nao acessa relatorio consolidado', function () {
    $empresa = tenant();
    setupRelatorioFrequencia($empresa);
    $user = usuarioTenant($empresa, 'professor');

    $this->actingAs($user)
        ->get(route('relatorio_frequencia.index'))
        ->assertForbidden();
});

test('relatorio tem escopo de tenant', function () {
    $empresa = tenant();
    setupRelatorioFrequencia($empresa);

    $outro = tenant(['nome' => 'Rede R', 'slug' => 'rede-r']);
    app(EmpresaContext::class)->setEmpresaId($outro->id);

    $user = usuarioTenant($outro, 'administrador');
    $response = $this->actingAs($user)->get(route('relatorio_frequencia.index'));
    $response->assertOk();
    expect($response->viewData('resumo')['alunos'])->toBe(0);
});
