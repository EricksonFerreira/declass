<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Atribuicao;
use App\Models\Avaliacao;
use App\Models\Aviso;
use App\Models\Cardapio;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Empresa;
use App\Models\EntregaTarefa;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Models\Ocorrencia;
use App\Models\ParadaTransporte;
use App\Models\PeriodoLetivo;
use App\Models\Pessoa;
use App\Models\PlanoAtendimento;
use App\Models\Responsavel;
use App\Models\RotaTransporte;
use App\Models\Tarefa;
use App\Models\TransporteAluno;
use App\Models\Turma;
use App\Models\User;
use App\Models\Veiculo;
use App\Support\EmpresaContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Massa de demonstracao da REDE escolar completa.
 *
 * Pressupoe a empresa 'escola-demo' (criada pelo DatabaseSeeder).
 * Popula 3 escolas, 5 anos letivos (2022-2026), todas as etapas
 * com turmas A/B (~20-30 alunos por turma, coortes que progridem
 * de ano), periodos letivos configuraveis (4 bimestres na Central
 * e Norte; 2 semestres na Sul — ex. modelo de faculdade), diario,
 * frequencia, avaliacoes, notas, financeiro, avisos e biblioteca.
 * Tudo idempotente: se o tenant ja tiver alunos, a massa academica
 * nao e duplicada.
 *
 * Execute: php artisan db:seed --class=DemoEscolarSeeder
 *
 * ─────────────────────────────────────────────────────────────
 * COMO ACESSAR (tela /login — 3 campos)
 * ─────────────────────────────────────────────────────────────
 *
 * Campo "Slug da empresa":  escola-demo
 * Campo "E-mail ou usuário": um dos e-mails abaixo
 * Campo "Senha":             password   (igual para todos)
 *
 * Contas de teste por perfil:
 *
 *  diretor@escolademo.test        administrador  — acesso total a tudo
 *  prof1@escolademo.test          professor      — diario, chamada,
 *                                                 avaliacoes e boletim
 *                                                 apenas das turmas
 *                                                 em que leciona
 *  prof2@escolademo.test          professor      — idem prof1
 *  secretaria@escolademo.test     secretario    — cadastro e gestao
 *                                                 Academica/Administrativa
 *  coordenador@escolademo.test    coordenador    — visao academica
 *                                                 (sem escrita em
 *                                                 diarios/notas)
 *  responsavel@escolademo.test    responsavel    — PORTAL: ve o
 *                                                 boletim e o
 *                                                 financeiro dos
 *                                                 filhos vinculados
 *  aluno@escolademo.test          aluno          — PORTAL: ve o
 *                                                 proprio boletim
 *  financeiro@escolademo.test     financeiro     — mensalidades,
 *                                                 planos, baixas e
 *                                                 inadimplencia
 *  bibliotecario@escolademo.test   bibliotecario  — acervo e
 *                                                 emprestimos de
 *                                                 livros
 *
 * Superadmin da plataforma (slug diferente):
 *  slug:   declass
 *  e-mail: admin@declass.test
 *  senha:  password
 *
 * O que conferir apos o login:
 *  - Menu lateral + cards do dashboard mudam conforme o perfil
 *  - responsavel/aluno caem direto no "Portal Familiar"
 *  - professor so ve as proprias turmas no diario/avaliacoes
 *  - boletim mostra medias, frequencia e notas por disciplina
 *  - periodo-letivo mostra 4 bimestres (Central/Norte) e
 *    2 semestres (Unidade Sul)
 *  - escolas, anos letivos e turmas das 3 unidades em
 *    Vida Academica > Escolas / Anos Letivos / Turmas
 * ─────────────────────────────────────────────────────────────
 */
class DemoEscolarSeeder extends Seeder
{
    private const SENHA = 'password';

    private const ANO_INICIAL = 2022;

    private const ANO_ATUAL = 2026;

    /**
     * Trilhas por segmento: posicoes = "anos" que viram nome de
     * turma ("1º Ano A"). A coorte que entra na posicao 0 avanca
     * uma posicao por ano letivo.
     */
    private const TRILHAS = [
        'infantil' => [
            'etapa' => 'Educação Infantil',
            'segmento' => 'infantil',
            'ordem' => 1,
            'posicoes' => ['Pré I', 'Pré II'],
        ],
        'fundamental1' => [
            'etapa' => 'Fundamental I',
            'segmento' => 'fundamental',
            'ordem' => 2,
            'posicoes' => ['1º Ano', '2º Ano', '3º Ano', '4º Ano', '5º Ano'],
        ],
        'fundamental2' => [
            'etapa' => 'Fundamental II',
            'segmento' => 'fundamental',
            'ordem' => 3,
            'posicoes' => ['6º Ano', '7º Ano', '8º Ano', '9º Ano'],
        ],
        'medio' => [
            'etapa' => 'Ensino Médio',
            'segmento' => 'medio',
            'ordem' => 4,
            'posicoes' => ['1º Médio', '2º Médio', '3º Médio'],
        ],
    ];

    private const LETRAS = ['A', 'B'];

    /** Cache de etapa_id por trilha (o tenant e unico neste seeder). */
    private array $etapaCache = [];

    public function run(): void
    {
        // A massa e grande (~15 mil registros); garante tempo de execucao.
        @set_time_limit(0);

        $empresa = Empresa::query()->where('slug', 'escola-demo')->first();

        if (! $empresa) {
            $this->command->warn('Empresa escola-demo nao encontrada. Rode db:seed primeiro.');

            return;
        }

        app(EmpresaContext::class)->setEmpresaId($empresa->id);

        $this->call(PerfisEscolaresSeeder::class);

        $usuarios = $this->usuarios($empresa);
        $escolas = $this->escolas($empresa);

        // Massa academica: processada por escola — se a execucao
        // for interrompida, uma nova rodada retoma so o que falta.
        $this->massaAcademica($empresa, $escolas, $usuarios);
        $this->vincularPortalContas($empresa, $usuarios);

        // Modulos complementares: idempotentes, reexecutaveis.
        $this->financeiro($empresa);
        $this->comunicacao($empresa);
        $this->biblioteca($empresa);
        $this->transporte($empresa, array_values($escolas));
        $this->alimentacao($empresa, array_values($escolas));
        $this->ocorrencias($empresa);
        $this->planosAtendimento($empresa);
        $this->tarefas($empresa, $usuarios);
        $this->agenda($empresa, array_values($escolas), $usuarios);
        $this->pesquisas($empresa, array_values($escolas), $usuarios);
        $this->saude($empresa);
        $this->estoque($empresa, array_values($escolas));
        $this->solicitacoesMatricula($empresa, array_values($escolas), $usuarios);

        $this->command->info('Massa demo criada com sucesso.');
    }

    /** Unidades da rede: reaproveita a Unidade Central do DatabaseSeeder. */
    private function escolas(Empresa $empresa): array
    {
        $defs = [
            'unidade-central' => ['nome' => 'Unidade Central', 'cidade' => 'São Paulo', 'capacidade' => 1200],
            'unidade-norte' => ['nome' => 'Unidade Norte', 'cidade' => 'São Paulo', 'capacidade' => 900],
            'unidade-sul' => ['nome' => 'Campus Sul', 'cidade' => 'São Paulo', 'capacidade' => 800],
        ];

        $escolas = [];
        foreach ($defs as $slug => $d) {
            $escolas[$slug] = Escola::query()->firstOrCreate(
                ['empresa_id' => $empresa->id, 'slug' => $slug],
                [
                    'nome' => $d['nome'],
                    'email' => $slug.'@escolademo.test',
                    'cidade' => $d['cidade'],
                    'estado' => 'SP',
                    'capacidade_alunos' => $d['capacidade'],
                    'turno_padrao' => 'integral',
                    'ativa' => 'S',
                ]
            );
        }

        return $escolas;
    }

    /**
     * Estrutura academica completa por escola: anos letivos,
     * periodos, etapas, turmas, coortes de alunos, matriculas e
     * a operacao completa do ano atual. Idempotente por escola.
     */
    private function massaAcademica(Empresa $empresa, array $escolas, array $usuarios): void
    {
        $this->etapas($empresa);
        $this->disciplinas($empresa);

        foreach ($escolas as $escola) {
            // Considera populada so quando ha matriculas ativas no
            // ano atual — coortes criadas sem operacao nao contam,
            // entao uma execucao interrompida pode ser retomada.
            $populada = Matricula::query()
                ->where('empresa_id', $empresa->id)
                ->where('status', 'ativa')
                ->whereHas('anoLetivo', fn ($q) => $q->where('ano', self::ANO_ATUAL))
                ->whereHas('turma', fn ($q) => $q->where('escola_id', $escola->id))
                ->exists();

            if ($populada) {
                $this->command->info("Escola {$escola->nome} ja populada — pulando.");

                continue;
            }

            $this->command->info("Populando {$escola->nome}...");

            // Transacao por escola: se o processo morrer no meio
            // (ex.: timeout), o rollback deixa a escola intacta
            // para a proxima execucao recomecar do zero.
            DB::transaction(function () use ($empresa, $escola, $usuarios) {
                $semestres = $escola->slug === 'unidade-sul';
                $coortes = $this->criarCoortes($empresa, $escola);
                $rows = $this->matriculasDaEscola($empresa, $escola, $semestres, $coortes);

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('matriculas')->insert($chunk);
                }

                $this->operacaoEscola($empresa, $escola, $usuarios);
            });

            $this->command->info("  {$escola->nome}: populada.");
        }
    }

    /** Etapas (segmentos) compartilhadas pelo tenant. */
    private function etapas(Empresa $empresa): array
    {
        $etapas = [];
        foreach (self::TRILHAS as $key => $trilha) {
            $etapas[$key] = Etapa::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $trilha['etapa']],
                [
                    'segmento' => $trilha['segmento'],
                    'ordem' => $trilha['ordem'],
                    'ativo' => 'S',
                ]
            );
        }

        return $etapas;
    }

    /** Disciplinas base compartilhadas pelo tenant. */
    private function disciplinas(Empresa $empresa)
    {
        $disciplinas = collect();
        foreach ([
            ['Matemática', 'MAT'], ['Português', 'POR'], ['Ciências', 'CIE'],
            ['História', 'HIS'], ['Geografia', 'GEO'], ['Inglês', 'ING'],
            ['Educação Física', 'EDF'], ['Artes', 'ART'],
        ] as [$nome, $sigla]) {
            $disciplinas->push(Disciplina::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome' => $nome],
                ['sigla' => $sigla]
            ));
        }

        return $disciplinas;
    }

    /**
     * Coortes de alunos por trilha: entram no inicio da trilha e
     * avancam uma posicao por ano. Entradas anteriores a 2022
     * garantem que TODA posicao esteja preenchida em todos os
     * anos da janela — ex.: Fundamental I entra desde 2018.
     *
     * @return array<string, array<int, array{entrada:int,letra:string,alunos:array<int,int>}>>
     */
    private function criarCoortes(Empresa $empresa, Escola $escola): array
    {
        $coortes = [];
        $contador = 0;

        foreach (self::TRILHAS as $key => $trilha) {
            $tamanhoTrilha = count($trilha['posicoes']);
            $primeiraEntrada = self::ANO_INICIAL - ($tamanhoTrilha - 1);

            foreach (range($primeiraEntrada, self::ANO_ATUAL) as $entrada) {
                foreach (self::LETRAS as $letra) {
                    $alunosIds = [];
                    $total = random_int(20, 30);

                    for ($i = 0; $i < $total; $i++) {
                        $contador++;
                        [$aluno] = $this->criarAlunoComResponsavel(
                            $empresa, $escola, $contador, $entrada
                        );
                        $alunosIds[] = $aluno->id;
                    }

                    $coortes[$key][] = [
                        'entrada' => $entrada,
                        'letra' => $letra,
                        'alunos' => $alunosIds,
                    ];
                }
            }
        }

        return $coortes;
    }

    /** Pessoa + aluno + responsavel legal + vinculo (1 coorte). */
    private function criarAlunoComResponsavel(Empresa $empresa, Escola $escola, int $seq, int $anoEntrada): array
    {
        $pessoa = Pessoa::create([
            'empresa_id' => $empresa->id,
            'nome' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => fake()->dateTimeBetween('-17 years', '-6 years')->format('Y-m-d'),
            'sexo' => fake()->randomElement(['M', 'F']),
            'email' => fake()->unique()->safeEmail(),
            'telefone' => fake()->numerify('119########'),
        ]);

        $aluno = Aluno::create([
            'empresa_id' => $empresa->id,
            'pessoa_id' => $pessoa->id,
            'escola_id' => $escola->id,
            'registro_academico' => 'RA-'.$escola->id.'-'.str_pad($seq, 5, '0', STR_PAD_LEFT),
            'data_ingresso' => $anoEntrada.'-02-05',
            'turno_preferencial' => fake()->randomElement(['matutino', 'vespertino', 'integral']),
            'ativo' => 'S',
        ]);

        $respPessoa = Pessoa::create([
            'empresa_id' => $empresa->id,
            'nome' => fake()->name(),
            'cpf' => fake()->unique()->numerify('###.###.###-##'),
            'data_nascimento' => fake()->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
            'sexo' => fake()->randomElement(['M', 'F']),
            'email' => fake()->unique()->safeEmail(),
            'celular' => fake()->numerify('119########'),
        ]);

        $responsavel = Responsavel::create([
            'empresa_id' => $empresa->id,
            'pessoa_id' => $respPessoa->id,
            'profissao' => fake()->randomElement(['Comerciante', 'Professor', 'Enfermeiro', 'Advogado', 'Autônomo', 'Funcionário público']),
            'local_trabalho' => fake()->company(),
            'ativo' => 'S',
        ]);

        $aluno->responsaveis()->attach($responsavel->id, [
            'tipo' => 'legal',
            'principal' => true,
            'pode_retirar' => true,
            'autoriza_contato' => true,
        ]);

        return [$aluno, $responsavel];
    }

    /**
     * Anos letivos, periodos, turmas e matriculas de uma escola.
     * Retorna as linhas de matricula para insert em lote.
     */
    private function matriculasDaEscola(
        Empresa $empresa,
        Escola $escola,
        bool $semestres,
        array $coortes
    ): array {
        $rows = [];
        $agora = now();
        $anos = range(self::ANO_INICIAL, self::ANO_ATUAL);

        foreach ($anos as $ano) {
            $anoLetivo = \App\Models\AnoLetivo::firstOrCreate(
                ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'ano' => $ano],
                [
                    'data_inicio' => $ano.'-02-01',
                    'data_fim' => $ano.'-12-15',
                    'status' => $ano === self::ANO_ATUAL ? 'letivo' : 'encerrado',
                    'ativo' => 'S',
                ]
            );

            $this->criarPeriodos($empresa, $escola, $anoLetivo, $semestres);

            // Turmas do ano: toda posicao A/B de cada trilha.
            $turmasAno = [];
            foreach (self::TRILHAS as $key => $trilha) {
                $etapaId = $this->etapaId($empresa, $key);
                foreach ($trilha['posicoes'] as $pos => $nomePosicao) {
                    foreach (self::LETRAS as $letra) {
                        $nome = $nomePosicao.' '.$letra;
                        $turma = Turma::firstOrCreate(
                            ['empresa_id' => $empresa->id, 'nome' => $nome, 'ano_letivo_id' => $anoLetivo->id],
                            [
                                'escola_id' => $escola->id,
                                'etapa_id' => $etapaId,
                                'turno' => fake()->randomElement(['matutino', 'vespertino']),
                                'sala' => 'Sala '.fake()->numberBetween(1, 30),
                                'capacidade_maxima' => 35,
                                'ativo' => 'S',
                            ]
                        );
                        $turmasAno["$key|$pos|$letra"] = $turma;
                    }
                }
            }

            // Disciplinas ofertadas em todas as turmas do ano.
            $disciplinas = Disciplina::query()->where('empresa_id', $empresa->id)->get();
            foreach ($turmasAno as $turma) {
                foreach ($disciplinas as $disciplina) {
                    DB::table('turma_disciplina')->updateOrInsert(
                        ['turma_id' => $turma->id, 'disciplina_id' => $disciplina->id],
                        ['carga_horaria_semanal' => fake()->numberBetween(3, 6), 'created_at' => $agora, 'updated_at' => $agora]
                    );
                }
            }

            // Matriculas: cada coorte ocupa a posicao (ano - entrada).
            foreach (self::TRILHAS as $key => $trilha) {
                $tamanho = count($trilha['posicoes']);
                foreach ($coortes[$key] as $coorte) {
                    $pos = $ano - $coorte['entrada'];
                    if ($pos < 0 || $pos >= $tamanho) {
                        continue;
                    }

                    $turma = $turmasAno["$key|$pos|{$coorte['letra']}"];
                    $status = $ano === self::ANO_ATUAL ? 'ativa' : 'concluida';

                    foreach ($coorte['alunos'] as $i => $alunoId) {
                        $rows[] = [
                            'empresa_id' => $empresa->id,
                            'aluno_id' => $alunoId,
                            'turma_id' => $turma->id,
                            'ano_letivo_id' => $anoLetivo->id,
                            'status' => $status,
                            'numero_chamada' => $i + 1,
                            'data_matricula' => $ano.'-02-05',
                            'data_saida' => $status === 'concluida' ? $ano.'-12-10' : null,
                            'created_at' => $agora,
                            'updated_at' => $agora,
                        ];
                    }
                }
            }

        }

        return $rows;
    }

    /** Cache simples de etapa_id por trilha dentro do tenant. */
    private function etapaId(Empresa $empresa, string $key): int
    {
        if (! isset($this->etapaCache[$key])) {
            $this->etapaCache[$key] = Etapa::query()
                ->where('empresa_id', $empresa->id)
                ->where('nome', self::TRILHAS[$key]['etapa'])
                ->value('id');
        }

        return $this->etapaCache[$key];
    }

    /**
     * Periodos letivos do ano: 4 bimestres nas unidades regulares
     * e 2 semestres de ~6 meses no Campus Sul (modelo faculdade) —
     * demonstrando que a quantidade/nome sao configuraveis.
     */
    private function criarPeriodos(Empresa $empresa, Escola $escola, \App\Models\AnoLetivo $anoLetivo, bool $semestres): void
    {
        $periodos = $semestres
            ? [
                [1, '1º Semestre', '02-01', '07-15'],
                [2, '2º Semestre', '07-20', '12-10'],
            ]
            : [
                [1, '1º Bimestre', '02-01', '04-15'],
                [2, '2º Bimestre', '04-20', '06-30'],
                [3, '3º Bimestre', '07-01', '09-15'],
                [4, '4º Bimestre', '09-20', '12-10'],
            ];

        foreach ($periodos as [$ordem, $nome, $ini, $fim]) {
            PeriodoLetivo::firstOrCreate(
                ['empresa_id' => $empresa->id, 'ano_letivo_id' => $anoLetivo->id, 'ordem' => $ordem],
                [
                    'escola_id' => $escola->id,
                    'nome' => $nome,
                    'data_inicio' => $anoLetivo->ano.'-'.$ini,
                    'data_fim' => $anoLetivo->ano.'-'.$fim,
                    'ativo' => 'S',
                ]
            );
        }
    }

    /**
     * Operacao completa do ano atual de UMA escola: atribuicoes
     * de professor, diarios, frequencias, avaliacoes e notas.
     * Anos anteriores ficam com estrutura + matriculas.
     */
    private function operacaoEscola(Empresa $empresa, Escola $escola, array $usuarios): void
    {
        $anoLetivo = \App\Models\AnoLetivo::query()
            ->where('empresa_id', $empresa->id)
            ->where('escola_id', $escola->id)
            ->where('ano', self::ANO_ATUAL)
            ->first();
        if (! $anoLetivo) {
            return;
        }

        $professores = collect([$usuarios['prof1'], $usuarios['prof2']])->filter();

        $turmas = Turma::query()
            ->where('empresa_id', $empresa->id)
            ->where('escola_id', $escola->id)
            ->where('ano_letivo_id', $anoLetivo->id)
            ->with('disciplinas')
            ->get();

        $periodosPorAno = PeriodoLetivo::query()
            ->where('ano_letivo_id', $anoLetivo->id)
            ->where('ativo', 'S')
            ->orderBy('ordem')
            ->get()
            ->keyBy('ordem');

        $matriculasPorTurma = Matricula::query()
            ->whereIn('turma_id', $turmas->pluck('id'))
            ->where('status', 'ativa')
            ->get()
            ->groupBy('turma_id');

        $periodo1 = $periodosPorAno->get(1);
        $periodo2 = $periodosPorAno->get(2) ?? $periodosPorAno->get(1);
        $i = 0;

        foreach ($turmas as $turma) {
            $matriculas = $matriculasPorTurma->get($turma->id, collect());
            if ($matriculas->isEmpty()) {
                continue;
            }

            foreach ($turma->disciplinas as $disciplina) {
                // Atribuicao titular professor (so ano atual).
                $prof = $professores->isNotEmpty()
                    ? $professores[$i++ % $professores->count()]
                    : null;

                Atribuicao::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'turma_id' => $turma->id,
                        'disciplina_id' => $disciplina->id,
                    ],
                    [
                        'user_id' => $prof?->id,
                        'papel' => 'titular',
                        'ativo' => 'S',
                    ]
                );

                // Diarios + frequencias: 3 aulas recentes do ano.
                foreach (['2026-03-10', '2026-03-12', '2026-03-17'] as $data) {
                    $diario = Diario::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'turma_id' => $turma->id,
                            'disciplina_id' => $disciplina->id,
                            'data' => $data,
                        ],
                        [
                            'user_id' => $prof?->id ?? $usuarios['prof1']->id,
                            'conteudo' => fake()->randomElement([
                                'Revisão de conteúdo', 'Exercícios práticos', 'Leitura e interpretação',
                                'Apresentação de projeto', 'Dinâmica em grupo', 'Correção de atividade',
                            ]),
                        ]
                    );

                    $freqRows = [];
                    foreach ($matriculas as $matricula) {
                        $freqRows[] = [
                            'empresa_id' => $empresa->id,
                            'diario_id' => $diario->id,
                            'matricula_id' => $matricula->id,
                            'status' => fake()->randomElement(
                                ['presente', 'presente', 'presente', 'presente', 'presente', 'falta', 'justificada']
                            ),
                            'justificativa' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    foreach ($matriculas as $j => $matricula) {
                        if ($freqRows[$j]['status'] === 'justificada') {
                            $freqRows[$j]['justificativa'] = 'Atestado médico';
                        }
                    }
                    foreach (array_chunk($freqRows, 400) as $chunk) {
                        DB::table('frequencias')->insert($chunk);
                    }
                }

                // Avaliacoes ligadas aos periodos letivos + notas.
                foreach ([
                    ['prova', 'Avaliação 1', $periodo1],
                    ['trabalho', 'Trabalho Prático', $periodo2],
                ] as [$tipo, $nome, $periodo]) {
                    $avaliacao = Avaliacao::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'turma_id' => $turma->id,
                            'disciplina_id' => $disciplina->id,
                            'nome' => $nome,
                        ],
                        [
                            'user_id' => $prof?->id ?? $usuarios['prof1']->id,
                            'tipo' => $tipo,
                            'periodo' => $periodo?->nome ?? '1º Período',
                            'periodo_letivo_id' => $periodo?->id,
                            'data' => '2026-04-08',
                            'nota_maxima' => 10,
                        ]
                    );

                    $notaRows = [];
                    foreach ($matriculas as $matricula) {
                        $nota = fake()->boolean(75)
                            ? fake()->randomFloat(1, 5, 10)
                            : fake()->randomFloat(1, 0, 4.9);
                        $notaRows[] = [
                            'empresa_id' => $empresa->id,
                            'avaliacao_id' => $avaliacao->id,
                            'matricula_id' => $matricula->id,
                            'nota' => $nota,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    foreach (array_chunk($notaRows, 400) as $chunk) {
                        DB::table('notas')->insert($chunk);
                    }
                }
            }
        }
    }

    /**
     * Vincula as contas de portal demo: o responsavel passa a ver
     * o boletim/financeiro dos primeiros filhos e o aluno demo ve
     * o proprio boletim. Idempotente — so preenche user_id vazio.
     */
    private function vincularPortalContas(Empresa $empresa, array $usuarios): void
    {
        $alunos = Aluno::query()->where('empresa_id', $empresa->id)
            ->orderBy('id')->limit(10)->get();
        if ($alunos->isEmpty()) {
            return;
        }

        // responsavel@ ve os primeiros 5 alunos vinculados
        $vinculados = 0;
        foreach ($alunos as $aluno) {
            foreach ($aluno->responsaveis as $resp) {
                if ($vinculados >= 5) {
                    break 2;
                }
                if (! $resp->user_id) {
                    $resp->update(['user_id' => $usuarios['responsavel']->id]);
                    $vinculados++;
                }
            }
        }

        // aluno@ ve a si mesmo
        $primeiro = $alunos->first();
        if ($primeiro && ! $primeiro->user_id) {
            $primeiro->update(['user_id' => $usuarios['aluno']->id]);
        }
    }

    /** Usuarios de acesso para testar os perfis. */
    private function usuarios(Empresa $empresa): array
    {
        $criar = function (string $email, string $nome, string $perfil) use ($empresa) {
            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $nome,
                    'nome' => $nome,
                    'password' => Hash::make(self::SENHA),
                    'empresa_id' => $empresa->id,
                    'ativo' => true,
                ]
            );

            $roleId = DB::table('roles')
                ->where('empresa_id', $empresa->id)
                ->where('name', $perfil)
                ->where('guard_name', 'web')
                ->value('id');

            DB::table('users')->where('id', $user->id)->update([
                'role_id' => $roleId,
                'email_verified_at' => now(),
            ]);

            return $user->fresh();
        };

        return [
            'prof1' => $criar('prof1@escolademo.test', 'Professora Ana Souza', 'professor'),
            'prof2' => $criar('prof2@escolademo.test', 'Professor Carlos Lima', 'professor'),
            'secretaria' => $criar('secretaria@escolademo.test', 'Secretaria Beatriz', 'secretario'),
            'coordenador' => $criar('coordenador@escolademo.test', 'Coordenadora Paula Reis', 'coordenador_pedagogico'),
            'responsavel' => $criar('responsavel@escolademo.test', 'Responsável Demo', 'responsavel_legal'),
            'aluno' => $criar('aluno@escolademo.test', 'Aluno Demo Portal', 'aluno'),
            'financeiro' => $criar('financeiro@escolademo.test', 'Financeiro Demo', 'financeiro'),
            'bibliotecario' => $criar('bibliotecario@escolademo.test', 'Bibliotecário Demo', 'bibliotecario'),
        ];
    }

    /**
     * Avisos de comunicacao por unidade: gerais + um por turma.
     * Idempotente: firstOrCreate por (empresa, escola, titulo).
     */
    private function comunicacao(Empresa $empresa): void
    {
        $escolas = Escola::query()->where('empresa_id', $empresa->id)->get();
        if ($escolas->isEmpty()) {
            return;
        }

        $genericos = [
            ['titulo' => 'Reunião de pais e mestres', 'tipo' => 'evento',
                'mensagem' => 'A reunião de pais e mestres acontecerá no dia '.now()->addDays(10)->format('d/m/Y').' às 19h, no auditório da escola. A presença de todos os responsáveis é muito importante.'],
            ['titulo' => 'Calendário de avaliações', 'tipo' => 'info',
                'mensagem' => 'O calendário de avaliações foi publicado no portal. Fiquem atentos aos horários de cada disciplina e tragam os materiais solicitados.'],
            ['titulo' => 'Atualize os dados cadastrais', 'tipo' => 'urgente',
                'mensagem' => 'Responsáveis, verifiquem se seus dados de contato e dos alunos estão atualizados na secretaria. Contato desatualizado dificulta a comunicação em emergências.'],
        ];

        foreach ($escolas as $escola) {
            foreach ($genericos as $g) {
                Aviso::firstOrCreate(
                    ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'titulo' => $g['titulo']],
                    $g + [
                        'turma_id' => null,
                        'data_publicacao' => now()->subDays(random_int(1, 5))->toDateString(),
                        'ativo' => 'S',
                    ]
                );
            }

            $turma = Turma::query()->where('escola_id', $escola->id)
                ->whereHas('anoLetivo', fn ($q) => $q->where('ano', self::ANO_ATUAL))
                ->first();
            if ($turma) {
                Aviso::firstOrCreate(
                    ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'titulo' => 'Passeio educativo da turma '.$turma->nome],
                    [
                        'turma_id' => $turma->id,
                        'mensagem' => "A turma {$turma->nome} participará de um passeio educativo. O termo de autorização deve ser entregue na secretaria até ".now()->addDays(5)->format('d/m/Y').'.',
                        'tipo' => 'evento',
                        'data_publicacao' => now()->subDay()->toDateString(),
                        'ativo' => 'S',
                    ]
                );
            }

            // Mural interno: avisos para a equipe (nao vao ao portal)
            $internos = [
                ['titulo' => 'Reunião pedagógica interna', 'tipo' => 'info',
                    'mensagem' => 'Reunião pedagógica na sala dos professores em '.now()->addDays(4)->format('d/m/Y').' às 14h. Trazer planejamentos do bimestre.'],
                ['titulo' => 'Reciclagem em avaliação diagnóstica', 'tipo' => 'evento',
                    'mensagem' => 'Oficina de formação em correção de avaliações diagnósticas. Inscrições com a coordenação pedagógica.'],
            ];
            foreach ($internos as $avisoInterno) {
                Aviso::firstOrCreate(
                    ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'titulo' => $avisoInterno['titulo']],
                    $avisoInterno + [
                        'turma_id' => null,
                        'destino' => 'staff',
                        'data_publicacao' => now()->subDays(random_int(1, 4))->toDateString(),
                        'ativo' => 'S',
                    ]
                );
            }
        }
    }

    /** Acervo e circulacao de emprestimos da biblioteca (Unidade Central). */
    private function biblioteca(Empresa $empresa): void
    {
        $escola = Escola::query()->where('empresa_id', $empresa->id)
            ->where('slug', 'unidade-central')->first()
            ?? Escola::query()->where('empresa_id', $empresa->id)->first();
        if (! $escola) {
            return;
        }

        $catalogo = [
            ['titulo' => 'Dom Casmurro', 'autor' => 'Machado de Assis', 'categoria' => 'literatura', 'exemplares' => 3],
            ['titulo' => 'O Pequeno Príncipe', 'autor' => 'Antoine de Saint-Exupéry', 'categoria' => 'infantil', 'exemplares' => 5],
            ['titulo' => 'Memórias Póstumas de Brás Cubas', 'autor' => 'Machado de Assis', 'categoria' => 'literatura', 'exemplares' => 2],
            ['titulo' => 'A Revolução dos Bichos', 'autor' => 'George Orwell', 'categoria' => 'juvenil', 'exemplares' => 4],
            ['titulo' => 'O Alquimista', 'autor' => 'Paulo Coelho', 'categoria' => 'literatura', 'exemplares' => 3],
            ['titulo' => 'Cem Anos de Solidão', 'autor' => 'Gabriel García Márquez', 'categoria' => 'literatura', 'exemplares' => 2],
            ['titulo' => 'Matemática para o Ensino Fundamental', 'autor' => 'Equipe Declass', 'categoria' => 'didatico', 'exemplares' => 10],
            ['titulo' => 'Gramática Reflexiva', 'autor' => 'Celso Cunha & Lindley Cintra', 'categoria' => 'didatico', 'exemplares' => 8],
            ['titulo' => 'O Problema das Pessoas', 'autor' => 'Millor Fernandes', 'categoria' => 'literatura', 'exemplares' => 2],
            ['titulo' => 'História Geral', 'autor' => 'Boris Fausto', 'categoria' => 'referencia', 'exemplares' => 4],
            ['titulo' => 'Atlas Geográfico Escolar', 'autor' => 'IBGE', 'categoria' => 'referencia', 'exemplares' => 6],
            ['titulo' => 'Diário de um Banana', 'autor' => 'Jeff Kinney', 'categoria' => 'juvenil', 'exemplares' => 5],
        ];

        $livros = [];
        foreach ($catalogo as $dados) {
            $livros[] = \App\Models\Livro::firstOrCreate(
                ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'titulo' => $dados['titulo']],
                $dados + ['ativo' => 'S']
            );
        }

        $alunos = Aluno::query()->where('empresa_id', $empresa->id)
            ->where('escola_id', $escola->id)->orderBy('id')->limit(8)->get();
        if ($alunos->isEmpty() || $livros === []) {
            return;
        }

        if (\App\Models\EmprestimoLivro::query()->where('empresa_id', $empresa->id)->exists()) {
            return;
        }

        foreach (range(0, 9) as $i) {
            $aluno = $alunos[$i % $alunos->count()];
            $livro = $livros[$i % count($livros)];

            $dados = [
                'empresa_id' => $empresa->id,
                'livro_id' => $livro->id,
                'aluno_id' => $aluno->id,
                'data_emprestimo' => now()->subDays(20)->toDateString(),
                'data_devolucao_prevista' => now()->subDays(5)->toDateString(),
                'status' => 'ativo',
            ];

            if ($i % 3 === 0) {
                $dados['data_devolucao'] = now()->subDays(6)->toDateString();
                $dados['status'] = 'devolvido';
            } elseif ($i % 3 === 1) {
                $dados['observacoes'] = 'Aluno avisado para regularizar.';
            } else {
                $dados['data_emprestimo'] = now()->subDays(2)->toDateString();
                $dados['data_devolucao_prevista'] = now()->addDays(13)->toDateString();
            }

            \App\Models\EmprestimoLivro::create($dados);
        }
    }

    /**
     * Planos de mensalidade + cobrancas das competencias recentes.
     * Idempotente: compara pares (matricula, competencia) ja
     * existentes antes do insert em lote.
     */
    private function financeiro(Empresa $empresa): void
    {
        $integral = \App\Models\PlanoMensalidade::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nome' => 'Mensalidade Integral'],
            ['valor' => 850.00, 'descricao' => 'Turno integral, materiais inclusos', 'ativo' => 'S']
        );
        $parcial = \App\Models\PlanoMensalidade::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nome' => 'Mensalidade Parcial'],
            ['valor' => 550.00, 'descricao' => 'Meio periodo', 'ativo' => 'S']
        );

        // Apenas matriculas ativas (ano atual) recebem plano/cobranca.
        $matriculas = Matricula::query()
            ->where('empresa_id', $empresa->id)
            ->where('status', 'ativa')
            ->get();

        $agora = now();
        foreach ($matriculas as $i => $matricula) {
            if (! $matricula->plano_mensalidade_id) {
                $matricula->update(['plano_mensalidade_id' => ($i % 2 === 0 ? $integral : $parcial)->id]);
                $matricula->load('planoMensalidade');
            }
        }

        $competencias = [
            now()->subMonth()->format('Y-m') => now()->subMonth()->startOfMonth()->addDays(9)->toDateString(),
            now()->format('Y-m') => now()->addDays(5)->toDateString(),
        ];

        $existentes = DB::table('mensalidades')
            ->where('empresa_id', $empresa->id)
            ->get(['matricula_id', 'competencia'])
            ->mapWithKeys(fn ($m) => ["$m->matricula_id|$m->competencia" => true])
            ->all();

        $ehMesAnterior = now()->subMonth()->format('Y-m');
        $rows = [];

        foreach ($matriculas as $i => $matricula) {
            $plano = $matricula->planoMensalidade;
            if (! $plano) {
                continue;
            }

            foreach ($competencias as $competencia => $vencimento) {
                if (isset($existentes["$matricula->id|$competencia"])) {
                    continue;
                }

                $paga = $competencia === $ehMesAnterior && $i % 2 === 0;

                $rows[] = [
                    'empresa_id' => $empresa->id,
                    'matricula_id' => $matricula->id,
                    'plano_mensalidade_id' => $plano->id,
                    'competencia' => $competencia,
                    'valor' => $plano->valor,
                    'vencimento' => $vencimento,
                    'status' => $paga ? 'paga' : 'pendente',
                    'data_pagamento' => $paga ? \Illuminate\Support\Carbon::parse($vencimento)->addDays(2)->toDateString() : null,
                    'forma_pagamento' => $paga ? fake()->randomElement(['pix', 'boleto', 'cartao_credito']) : null,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('mensalidades')->insert($chunk);
        }
    }

    /**
     * Transporte escolar por unidade: frota, rotas com paradas
     * e ~12% dos alunos ativos vinculados como passageiros.
     * Idempotente — firstOrCreate + guarda por escola.
     */
    private function transporte(Empresa $empresa, array $escolas): void
    {
        foreach ($escolas as $escola) {
            $veiculos = Veiculo::query()
                ->where('empresa_id', $empresa->id)
                ->where('escola_id', $escola->id)
                ->get();

            if ($veiculos->isEmpty()) {
                $modelos = [
                    ['ABC1D23', 'Mercedes Sprinter 2020', 2020, 20, 'Carlos Souza', '(11) 98811-2233'],
                    ['XYZ9K87', 'Volare W8 2019', 2019, 32, 'José Lima', '(11) 97722-4455'],
                    ['QWE4R56', 'Fiat Ducato 2021', 2021, 16, 'Ana Pereira', '(11) 96633-7788'],
                ];
                foreach ($modelos as [$placa, $modelo, $ano, $cap, $motorista, $tel]) {
                    $veiculos->push(Veiculo::firstOrCreate(
                        ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'placa' => $placa],
                        [
                            'modelo' => $modelo,
                            'ano' => $ano,
                            'capacidade' => $cap,
                            'motorista_nome' => $motorista,
                            'motorista_telefone' => $tel,
                            'ativo' => 'S',
                        ]
                    ));
                }
            }

            $rotas = [];
            $rotasDef = [
                ['Rota Centro — Manhã', '06:30', '17:00'],
                ['Rota Norte — Tarde', '12:30', '18:30'],
            ];
            foreach ($rotasDef as $i => [$nome, $ida, $volta]) {
                $rota = RotaTransporte::firstOrCreate(
                    ['empresa_id' => $empresa->id, 'escola_id' => $escola->id, 'nome' => $nome],
                    [
                        'veiculo_id' => $veiculos[$i % $veiculos->count()]->id,
                        'horario_ida' => $ida,
                        'horario_volta' => $volta,
                        'ativo' => 'S',
                    ]
                );

                if ($rota->paradas()->count() === 0) {
                    $bairros = ['Jardim Paulista', 'Vila Mariana', 'Moema', 'Pinheiros', 'Santana'];
                    foreach ($bairros as $ordem => $bairro) {
                        ParadaTransporte::create([
                            'empresa_id' => $empresa->id,
                            'rota_id' => $rota->id,
                            'nome' => 'Parada '.$bairro,
                            'ordem' => $ordem + 1,
                            'horario_previsto' => $ida,
                        ]);
                    }
                }

                $rotas[] = $rota->load('paradas');
            }

            // ~12% dos alunos ativos do ano atual viram passageiros.
            $alunosIds = Matricula::query()
                ->where('empresa_id', $empresa->id)
                ->where('status', 'ativa')
                ->whereHas('turma', fn ($q) => $q->where('escola_id', $escola->id))
                ->inRandomOrder()
                ->limit(350)
                ->pluck('aluno_id');

            $jaVinculados = TransporteAluno::query()
                ->where('empresa_id', $empresa->id)
                ->where('ativo', 'S')
                ->pluck('aluno_id')
                ->all();

            $count = 0;
            foreach ($alunosIds as $alunoId) {
                if (in_array($alunoId, $jaVinculados, true)) {
                    continue;
                }
                $rota = $rotas[$count % count($rotas)];
                $parada = $rota->paradas->random();

                TransporteAluno::create([
                    'empresa_id' => $empresa->id,
                    'aluno_id' => $alunoId,
                    'rota_id' => $rota->id,
                    'parada_id' => $parada->id,
                    'ativo' => 'S',
                ]);

                if (++$count >= 180) {
                    break;
                }
            }
        }
    }

    /**
     * Cardapios das proximas 2 semanas letivas por unidade:
     * cafe da manha, almoco e lanche da tarde em dias uteis.
     * Idempotente via unique (escola, data, tipo).
     */
    private function alimentacao(Empresa $empresa, array $escolas): void
    {
        $cardapios = [
            'cafe_manha' => [
                'Leite, pão francês com manteiga e fruta da estação',
                'Achocolatado, tapioca com queijo coalho e banana',
                'Iogurte natural, bolo caseiro e maçã',
            ],
            'almoco' => [
                'Arroz, feijão, frango grelhado, salada de alface e tomate, suco de laranja',
                'Macarrão ao sugo, almôndegas, brócolis no vapor e gelatina',
                'Arroz, feijão, iscas de carne, purê de batata e salada de rúcula',
                'Strogonoff de frango com arroz, legumes salteados e fruta',
                'Feijoada completa (versão escolar), laranja e couve refogada',
            ],
            'lanche_tarde' => [
                'Biscoito maisena e suco de uva',
                'Pão de queijo (3 un.) e suco de maracujá',
                'Bolacha recheada e leite',
                'Fruta da estação e bolo de cenoura',
            ],
        ];

        $restricoes = [
            'Opção vegetariana disponível',
            'Versão sem glúten para alérgicos',
            'Opção sem lactose disponível',
            null,
        ];

        foreach ($escolas as $escola) {
            $dias = 0;
            $data = now()->startOfDay();
            while ($dias < 10) {
                if ($data->isWeekend()) {
                    $data->addDay();

                    continue;
                }

                foreach ($cardapios as $tipo => $opcoes) {
                    Cardapio::firstOrCreate(
                        [
                            'empresa_id' => $empresa->id,
                            'escola_id' => $escola->id,
                            'data' => $data->toDateString(),
                            'tipo' => $tipo,
                        ],
                        [
                            'descricao' => fake()->randomElement($opcoes),
                            'restricoes_alimentares' => fake()->randomElement($restricoes),
                            'ativo' => 'S',
                        ]
                    );
                }

                $dias++;
                $data->addDay();
            }
        }
    }

    /**
     * Ocorrencias disciplinares/pedagogicas de alunos do ano
     * atual: mistura de elogios, advertencias e ocorrencias.
     * Idempotente — pula se a escola ja tem registros.
     */
    private function ocorrencias(Empresa $empresa): void
    {
        $tipos = [
            'elogio' => ['Participou ativamente da aula', 'Desempenho excelente na avaliação', 'Ajuda colegas em atividades em grupo'],
            'advertencia' => ['Conversas durante a explicação', 'Atraso reiterado', 'Não trouxe material escolar'],
            'ocorrencia' => ['Uso de celular em sala', 'Desrespeito a colega', 'Recusa em realizar atividade'],
        ];
        $locais = ['Sala de aula', 'Pátio', 'Corredor', 'Laboratório', 'Refeitório'];

        foreach (Escola::query()->where('empresa_id', $empresa->id)->get() as $escola) {
            if (Ocorrencia::query()->where('escola_id', $escola->id)->exists()) {
                continue;
            }

            $alunosIds = Matricula::query()
                ->where('empresa_id', $empresa->id)
                ->where('status', 'ativa')
                ->whereHas('turma', fn ($q) => $q->where('escola_id', $escola->id))
                ->inRandomOrder()
                ->limit(60)
                ->pluck('aluno_id');

            foreach ($alunosIds as $i => $alunoId) {
                $tipo = $i % 5 === 0 ? 'elogio' : ($i % 3 === 0 ? 'advertencia' : 'ocorrencia');
                $gravidade = $tipo === 'elogio' ? 'leve' : fake()->randomElement(['leve', 'media', 'grave']);

                Ocorrencia::create([
                    'empresa_id' => $empresa->id,
                    'aluno_id' => $alunoId,
                    'escola_id' => $escola->id,
                    'data' => now()->subDays(fake()->numberBetween(1, 60))->toDateString(),
                    'tipo' => $tipo,
                    'gravidade' => $gravidade,
                    'local' => fake()->randomElement($locais),
                    'descricao' => fake()->randomElement($tipos[$tipo]),
                    'medida_adotada' => $tipo === 'elogio' ? null : fake()->randomElement([
                        'Advertência verbal registrada', 'Convocação dos pais', 'Orientação individual',
                    ]),
                    'notifica_responsavel' => $gravidade !== 'leve',
                    'ativo' => 'S',
                ]);
            }
        }
    }

    /**
     * Planos de atendimento especializado (PEI/PDI) para uma
     * fracao dos alunos ativos do ano atual. Idempotente por
     * escola — pula se ja existem planos.
     */
    private function planosAtendimento(Empresa $empresa): void
    {
        $necessidades = [
            'TEA — suporte em rotina e comunicacao',
            'Deficiencia visual — material em braille/ampliado',
            'TDAH — tempo estendido e reforco pedagogico',
            'Altas habilidades — atividades de aprofundamento',
            'Deficiencia auditiva — recursos visuais e intérprete',
        ];
        $objetivos = [
            'Desenvolver autonomia nas atividades cotidianas',
            'Garantir acesso ao curriculo com adaptacoes',
            'Melhorar comunicacao e interacao social',
            'Manter frequencia e participacao nas avaliacoes',
        ];

        foreach (Escola::query()->where('empresa_id', $empresa->id)->get() as $escola) {
            if (PlanoAtendimento::query()->where('escola_id', $escola->id)->exists()) {
                continue;
            }

            $alunosIds = Matricula::query()
                ->where('empresa_id', $empresa->id)
                ->where('status', 'ativa')
                ->whereHas('turma', fn ($q) => $q->where('escola_id', $escola->id))
                ->inRandomOrder()
                ->limit(30)
                ->pluck('aluno_id');

            foreach ($alunosIds as $alunoId) {
                PlanoAtendimento::create([
                    'empresa_id' => $empresa->id,
                    'aluno_id' => $alunoId,
                    'escola_id' => $escola->id,
                    'tipo' => fake()->randomElement(['pei', 'pei', 'pdi', 'paee']),
                    'status' => fake()->randomElement(['ativo', 'ativo', 'ativo', 'elaboracao', 'suspenso']),
                    'data_elaboracao' => now()->subMonths(fake()->numberBetween(1, 10))->toDateString(),
                    'proxima_revisao' => now()->addMonths(fake()->numberBetween(1, 6))->toDateString(),
                    'diagnostico' => 'Avaliacao pedagogica e laudos anexados aos documentos do aluno.',
                    'necessidades' => fake()->randomElement($necessidades),
                    'objetivos' => fake()->randomElement($objetivos),
                    'estrategias' => 'Adaptações curriculares, tempo estendido e avaliacoes alternativas.',
                    'observacoes' => 'Reuniao de acompanhamento com a familia a cada bimestre.',
                    'ativo' => 'S',
                ]);
            }
        }
    }

    /**
     * Dever de casa: tarefas por turma do ano atual com controle
     * de entregas por aluno (idempotente por escola).
     */
    private function tarefas(Empresa $empresa, array $usuarios): void
    {
        $titulos = [
            'Exercicios de fixacao da aula',
            'Leitura do capitulo indicado',
            'Pesquisa sobre o tema da semana',
            'Resolucao de problemas',
            'Redacao de texto dissertativo',
            'Atividade pratica em grupo',
            'Revisao para a prova',
            'Interpretacao de texto',
        ];
        $descricoes = [
            'Fazer os exercicios indicados no caderno.',
            'Ler o capitulo e responder as questoes.',
            'Pesquisar na internet e entregar um resumo de uma pagina.',
            'Resolver os problemas da lista de exercicios.',
        ];

        foreach (Escola::query()->where('empresa_id', $empresa->id)->get() as $escola) {
            $anoLetivo = \App\Models\AnoLetivo::query()
                ->where('empresa_id', $empresa->id)
                ->where('escola_id', $escola->id)
                ->where('ano', self::ANO_ATUAL)
                ->first();
            if (! $anoLetivo) {
                continue;
            }
            if (Tarefa::query()->where('empresa_id', $empresa->id)
                ->whereHas('turma', fn ($q) => $q->where('escola_id', $escola->id))->exists()) {
                continue;
            }

            $turmas = Turma::query()
                ->where('empresa_id', $empresa->id)
                ->where('escola_id', $escola->id)
                ->where('ano_letivo_id', $anoLetivo->id)
                ->with(['disciplinas', 'matriculas' => fn ($q) => $q->where('status', 'ativa')])
                ->inRandomOrder()
                ->limit(12)
                ->get();

            $autorId = $usuarios['prof1']->id ?? null;

            foreach ($turmas as $turma) {
                $disciplinas = $turma->disciplinas;
                foreach (range(1, 2) as $n) {
                    $dataEntrega = now()->addDays(fake()->numberBetween(-8, 12));
                    $tarefa = Tarefa::create([
                        'empresa_id' => $empresa->id,
                        'turma_id' => $turma->id,
                        'disciplina_id' => $disciplinas->isNotEmpty() ? $disciplinas->random()->id : null,
                        'user_id' => $autorId,
                        'titulo' => fake()->randomElement($titulos).' '.fake()->numberBetween(1, 30),
                        'descricao' => fake()->randomElement($descricoes),
                        'instrucoes' => fake()->optional(0.4)->sentence(),
                        'data_entrega' => $dataEntrega->toDateString(),
                        'nota_maxima' => fake()->randomElement([5, 10, 10, 10]),
                        'ativo' => 'S',
                    ]);

                    foreach ($turma->matriculas->take(28) as $matricula) {
                        $vencida = $dataEntrega->isPast();
                        $status = $vencida
                            ? fake()->randomElement(['entregue', 'entregue', 'avaliada', 'pendente', 'pendente'])
                            : 'pendente';
                        EntregaTarefa::create([
                            'empresa_id' => $empresa->id,
                            'tarefa_id' => $tarefa->id,
                            'matricula_id' => $matricula->id,
                            'status' => $status,
                            'nota' => $status === 'avaliada' ? fake()->randomFloat(1, 0, $tarefa->nota_maxima ?? 10) : null,
                            'data_entrega' => in_array($status, ['entregue', 'avaliada']) ? $dataEntrega->copy()->subDays(fake()->numberBetween(0, 3))->toDateString() : null,
                            'observacoes' => $status === 'pendente' && $vencida ? 'Entrega em atraso' : null,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Agenda escolar: eventos das proximas semanas por unidade +
     * alguns eventos da rede toda. Idempotente por
     * empresa+escola+titulo+data_inicio.
     */
    private function agenda(Empresa $empresa, array $escolas, array $usuarios): void
    {
        $autorId = $usuarios['secretaria']->id ?? null;

        $modelos = [
            ['tipo' => 'reuniao', 'titulo' => 'Reunião de pais e mestres', 'publico' => 'todos', 'hora' => '19:00'],
            ['tipo' => 'evento', 'titulo' => 'Semana da Leitura', 'publico' => 'todos', 'hora' => '08:00'],
            ['tipo' => 'evento', 'titulo' => 'Feira de Ciências', 'publico' => 'todos', 'hora' => '09:00'],
            ['tipo' => 'avaliacao', 'titulo' => 'Provas do bimestre', 'publico' => 'todos', 'hora' => null],
            ['tipo' => 'feriado', 'titulo' => 'Ponto facultativo', 'publico' => 'todos', 'hora' => null],
            ['tipo' => 'prazo', 'titulo' => 'Entrega de documentos de matrícula', 'publico' => 'interno', 'hora' => null],
            ['tipo' => 'evento', 'titulo' => 'Reunião pedagógica interna', 'publico' => 'interno', 'hora' => '14:00'],
            ['tipo' => 'formatura', 'titulo' => 'Ensaios de formatura', 'publico' => 'todos', 'hora' => '18:00'],
        ];

        foreach ($escolas as $escola) {
            foreach (range(0, 11) as $i) {
                $modelo = $modelos[$i % count($modelos)];
                // Datas deterministicos (sem random) para idempotencia
                $data = now()->addDays($i * 4);
                if ($data->isWeekend()) {
                    $data->addDays(2);
                }

                \App\Models\Evento::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'escola_id' => $escola->id,
                        'titulo' => $modelo['titulo'],
                        'data_inicio' => $data->toDateString(),
                    ],
                    [
                        'user_id' => $autorId,
                        'descricao' => fake()->optional(0.5)->sentence(12),
                        'tipo' => $modelo['tipo'],
                        'data_fim' => $modelo['tipo'] === 'formatura' ? $data->copy()->addDay()->toDateString() : null,
                        'hora_inicio' => $modelo['hora'],
                        'hora_fim' => $modelo['hora'] ? \Carbon\Carbon::parse($modelo['hora'])->addHours(2)->format('H:i') : null,
                        'local' => fake()->randomElement(['Auditório', 'Quadra poliesportiva', 'Sala dos Professores', null]),
                        'publico' => $modelo['publico'],
                        'status' => 'agendado',
                    ]
                );
            }
        }

        // Eventos da rede toda (sem escola)
        foreach ([
            ['Congresso de Inovação Educacional', 'evento', 'todos', 20],
            ['Início do ano letivo', 'evento', 'todos', 5],
            ['Reunião de diretores', 'reuniao', 'interno', 12],
        ] as [$titulo, $tipo, $publico, $dias]) {
            \App\Models\Evento::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'escola_id' => null,
                    'titulo' => $titulo,
                    'data_inicio' => now()->addDays($dias)->toDateString(),
                ],
                [
                    'user_id' => $autorId,
                    'tipo' => $tipo,
                    'publico' => $publico,
                    'status' => 'agendado',
                ]
            );
        }
    }

    /**
     * Pesquisas de satisfacao: uma aberta por unidade (visivel no
     * portal das familias) + uma encerrada da rede com respostas
     * dos usuarios demo. Idempotente via firstOrCreate.
     */
    private function pesquisas(Empresa $empresa, array $escolas, array $usuarios): void
    {
        $perguntas = [
            ['Como você avalia o atendimento da secretaria?', 'nota', 'S'],
            ['Como você avalia a organização e a limpeza da escola?', 'nota', 'S'],
            ['Recomendaria a escola para outras famílias?', 'sim_nao', 'S'],
            ['Você sente falta de alguma informação da escola?', 'sim_nao', 'N'],
            ['Como você avalia o desempenho do seu(a) filho(a) neste semestre?', 'nota', 'S'],
            ['Sugestões ou elogios para a gestão', 'texto', 'N'],
        ];

        $criarPerguntas = function (\App\Models\Pesquisa $pesquisa) use ($perguntas) {
            foreach ($perguntas as $i => [$texto, $tipo, $obrigatoria]) {
                \App\Models\PesquisaPergunta::firstOrCreate(
                    ['pesquisa_id' => $pesquisa->id, 'pergunta' => $texto],
                    ['ordem' => $i + 1, 'tipo' => $tipo, 'obrigatoria' => $obrigatoria]
                );
            }
        };

        foreach ($escolas as $escola) {
            $pesquisa = \App\Models\Pesquisa::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'escola_id' => $escola->id,
                    'titulo' => 'Pesquisa de satisfação — '.$escola->nome,
                ],
                [
                    'descricao' => 'Queremos ouvir as famílias! Responda o questionário rápido sobre a escola.',
                    'data_inicio' => now()->subDays(3)->toDateString(),
                    'data_fim' => now()->addDays(12)->toDateString(),
                    'status' => 'aberta',
                ]
            );
            $criarPerguntas($pesquisa);
        }

        // Pesquisa encerrada da rede, com respostas dos usuarios demo
        $encerrada = \App\Models\Pesquisa::firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'escola_id' => null,
                'titulo' => 'Pesquisa de satisfação — Rede (encerrada)',
            ],
            [
                'descricao' => 'Avaliação geral da rede no semestre anterior.',
                'data_inicio' => now()->subDays(40)->toDateString(),
                'data_fim' => now()->subDays(25)->toDateString(),
                'status' => 'encerrada',
            ]
        );
        $criarPerguntas($encerrada);

        // Respostas demo na pesquisa encerrada (usuarios portal)
        $perguntasIds = $encerrada->perguntas()->pluck('id')->all();
        foreach (['responsavel', 'aluno'] as $chave) {
            $user = $usuarios[$chave] ?? null;
            if (! $user || count($perguntasIds) < 6) {
                continue;
            }
            \App\Models\PesquisaResposta::firstOrCreate(
                ['pesquisa_id' => $encerrada->id, 'user_id' => $user->id],
                [
                    'empresa_id' => $empresa->id,
                    'aluno_id' => null,
                    'respostas' => [
                        (string) $perguntasIds[0] => (string) random_int(3, 5),
                        (string) $perguntasIds[1] => (string) random_int(3, 5),
                        (string) $perguntasIds[2] => random_int(0, 1) ? 'S' : 'N',
                        (string) $perguntasIds[3] => random_int(0, 1) ? 'S' : 'N',
                        (string) $perguntasIds[4] => (string) random_int(3, 5),
                    ],
                ]
            );
        }
    }

    /**
     * Fichas de saude para ~40% dos alunos ativos de cada
     * unidade: tipo sanguineo, alertas e contato de
     * emergencia. Idempotente por aluno_id (unique).
     */
    private function saude(Empresa $empresa): void
    {
        $sanguineos = array_keys(\App\Models\SaudeAluno::TIPOS_SANGUINEOS);
        $alergiasOpcoes = [
            'Amendoim', 'Lactose', 'Poeria', 'Picada de inseto',
            'Dipirona', 'Corante alimentar', null,
        ];
        $condicoesOpcoes = [
            'Asma', 'Diabetes tipo 1', 'Epilepsia controlada', 'TEA', null,
        ];
        $medicamentosOpcoes = [
            'Bombinha de asma', 'Insulina', 'Antialérgico diário', null,
        ];
        $parentescos = ['Mãe', 'Pai', 'Avó', 'Tio(a)', 'Irmão(ã) maior'];

        foreach (Escola::query()->where('empresa_id', $empresa->id)->get() as $escola) {
            $alunosIds = Matricula::query()
                ->where('empresa_id', $empresa->id)
                ->where('status', 'ativa')
                ->whereHas('turma', fn ($q) => $q->where('escola_id', $escola->id))
                ->inRandomOrder()
                ->limit(120)
                ->pluck('aluno_id');

            foreach ($alunosIds->take((int) ceil($alunosIds->count() * 0.4)) as $i => $alunoId) {
                \App\Models\SaudeAluno::firstOrCreate(
                    ['aluno_id' => $alunoId],
                    [
                        'empresa_id' => $empresa->id,
                        'tipo_sanguineo' => fake()->randomElement($sanguineos),
                        'alergias' => fake()->randomElement($alergiasOpcoes),
                        'condicoes_cronicas' => fake()->randomElement($condicoesOpcoes),
                        'medicamentos' => fake()->randomElement($medicamentosOpcoes),
                        'contato_emergencia_nome' => fake()->name(),
                        'contato_emergencia_parentesco' => fake()->randomElement($parentescos),
                        'contato_emergencia_telefone' => fake()->numerify('(11) 9####-####'),
                        'observacoes' => fake()->optional(0.3)->sentence(10),
                        'ativo' => 'S',
                    ]
                );
            }
        }
    }

    /**
     * Materiais de estoque por unidade: catalogo fixo com
     * estoque inicial e algumas movimentacoes de historico.
     * Idempotente por (empresa, escola, nome).
     */
    private function estoque(Empresa $empresa, array $escolas): void
    {
        $catalogo = [
            ['Papel sulfite A4', 'papelaria', 'pct', 240, 60, 'Almoxarifado'],
            ['Caderno universitário 96fl', 'papelaria', 'un', 500, 100, 'Almoxarifado'],
            ['Caneta esferográfica azul', 'papelaria', 'cx', 80, 20, 'Secretaria'],
            ['Lousa branca piloto', 'didatico', 'un', 12, 3, 'Sala de aula'],
            ['Jogo de xadrez', 'didatico', 'un', 15, 5, 'Biblioteca'],
            ['Detergente neutro 5L', 'limpeza', 'un', 30, 8, 'Depósito limpeza'],
            ['Papel toalha', 'limpeza', 'pct', 200, 50, 'Banheiros'],
            ['Álcool 70% 1L', 'limpeza', 'un', 40, 10, 'Laboratório'],
            ['Arroz tipo 1 5kg', 'cantina', 'un', 60, 15, 'Cantina'],
            ['Feijão carioca 1kg', 'cantina', 'pct', 90, 20, 'Cantina'],
            ['Leite integral 1L', 'cantina', 'un', 120, 30, 'Câmara fria'],
            ['Uniforme camiseta P', 'uniforme', 'un', 45, 10, 'Guarda-roupa'],
        ];

        foreach ($escolas as $escola) {
            $temMovimentacoes = \App\Models\MovimentacaoEstoque::query()
                ->whereHas('material', fn ($q) => $q->where('escola_id', $escola->id))
                ->exists();

            foreach ($catalogo as [$nome, $categoria, $unidade, $estoque, $minimo, $local]) {
                $material = \App\Models\Material::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'escola_id' => $escola->id,
                        'nome' => $nome,
                    ],
                    [
                        'categoria' => $categoria,
                        'unidade_medida' => $unidade,
                        'estoque_atual' => $estoque,
                        'estoque_minimo' => $minimo,
                        'localizacao' => $local,
                        'ativo' => 'S',
                    ]
                );

                // Historico inicial apenas na primeira execucao da unidade
                if ($temMovimentacoes) {
                    continue;
                }

                \App\Models\MovimentacaoEstoque::firstOrCreate(
                    [
                        'material_id' => $material->id,
                        'tipo' => 'entrada',
                        'data' => now()->subDays(20)->toDateString(),
                    ],
                    [
                        'empresa_id' => $empresa->id,
                        'quantidade' => $estoque,
                        'saldo_anterior' => 0,
                        'saldo_novo' => $estoque,
                        'motivo' => 'Compra inicial / reposição de estoque',
                        'user_id' => null,
                    ]
                );

                // Uma saida recente em parte dos materiais
                if ($estoque > $minimo && crc32($nome) % 2 === 0) {
                    $saida = (int) ceil(($estoque - $minimo) * 0.3);
                    \App\Models\MovimentacaoEstoque::firstOrCreate(
                        [
                            'material_id' => $material->id,
                            'tipo' => 'saida',
                            'data' => now()->subDays(3)->toDateString(),
                        ],
                        [
                            'empresa_id' => $empresa->id,
                            'quantidade' => $saida,
                            'saldo_anterior' => $estoque,
                            'saldo_novo' => $estoque - $saida,
                            'motivo' => 'Consumo interno / distribuição',
                            'user_id' => null,
                        ]
                    );
                    $material->update(['estoque_atual' => $estoque - $saida]);
                }
            }
        }
    }

    /**
     * Fila de solicitacoes de matricula: 3 por unidade (pendentes
     * e em analise) + 1 aprovada de verdade na primeira escola,
     * gerando aluno/matricula/responsavel reais. Idempotente por
     * (empresa, escola, nome_aluno).
     */
    private function solicitacoesMatricula(Empresa $empresa, array $escolas, array $usuarios): void
    {
        $secretaria = $usuarios['secretaria'] ?? null;
        $statusOpcoes = ['pendente', 'pendente', 'em_analise', 'pendente'];

        foreach ($escolas as $escola) {
            $anoLetivo = \App\Models\AnoLetivo::query()
                ->where('escola_id', $escola->id)->orderByDesc('ano')->first();
            $etapas = \App\Models\Etapa::query()->where('empresa_id', $empresa->id)->get();
            $turmas = \App\Models\Turma::query()->where('escola_id', $escola->id)->get();

            foreach ($statusOpcoes as $i => $status) {
                $nome = "Solicitante Demo {$escola->id}-{$i}";
                $existe = \App\Models\SolicitacaoMatricula::query()
                    ->where('empresa_id', $empresa->id)
                    ->where('escola_id', $escola->id)
                    ->where('nome_aluno', $nome)
                    ->exists();
                if ($existe) {
                    continue;
                }

                \App\Models\SolicitacaoMatricula::create([
                    'empresa_id' => $empresa->id,
                    'escola_id' => $escola->id,
                    'ano_letivo_id' => $anoLetivo?->id,
                    'etapa_id' => $etapas->isNotEmpty() ? $etapas->random()->id : null,
                    'nome_aluno' => $nome,
                    'data_nascimento_aluno' => fake()->dateTimeBetween('-12 years', '-5 years')->format('Y-m-d'),
                    'sexo_aluno' => fake()->randomElement(['masculino', 'feminino']),
                    'nome_responsavel' => fake()->name().' (Responsável)',
                    'parentesco' => fake()->randomElement(array_keys(\App\Models\SolicitacaoMatricula::PARENTESCOS)),
                    'telefone_responsavel' => fake()->numerify('(11) 9####-####'),
                    'email_responsavel' => 'resp.'.$escola->id.'.'.$i.'@escolademo.test',
                    'origem' => 'interno',
                    'status' => $status,
                    'observacoes' => fake()->optional(0.4)->sentence(8),
                ]);
            }
        }

        // Uma solicitacao aprovada de verdade na primeira unidade:
        // gera aluno, matricula e responsavel reais via service.
        $escola = $escolas[0] ?? null;
        $turma = $escola ? \App\Models\Turma::query()->where('escola_id', $escola->id)->first() : null;
        if (! $escola || ! $turma || ! $secretaria) {
            return;
        }

        $nome = "Aprovado Demo {$escola->id}";
        $existe = \App\Models\SolicitacaoMatricula::query()
            ->where('empresa_id', $empresa->id)
            ->where('escola_id', $escola->id)
            ->where('nome_aluno', $nome)
            ->exists();
        if ($existe) {
            return;
        }

        $solicitacao = \App\Models\SolicitacaoMatricula::create([
            'empresa_id' => $empresa->id,
            'escola_id' => $escola->id,
            'ano_letivo_id' => $turma->ano_letivo_id,
            'etapa_id' => $turma->etapa_id,
            'nome_aluno' => $nome,
            'data_nascimento_aluno' => fake()->dateTimeBetween('-10 years', '-6 years')->format('Y-m-d'),
            'sexo_aluno' => fake()->randomElement(['masculino', 'feminino']),
            'nome_responsavel' => 'Responsável do Aprovado Demo',
            'parentesco' => 'mae',
            'telefone_responsavel' => '(11) 98888-0000',
            'email_responsavel' => 'aprovado.demo@escolademo.test',
            'origem' => 'interno',
            'status' => 'pendente',
        ]);

        app(\App\Services\SolicitacaoMatriculaService::class)
            ->aprovar($solicitacao, ['turma_id' => $turma->id], $secretaria);
    }
}
