<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Perfis de acesso (roles) do sistema escolar.
 *
 * Os 19 perfis da especificacao + 'membro' (baseline self-service).
 * Perfis sao por tenant: cada empresa tem seus proprios roles
 * (roles.empresa_id). A matriz de cada perfil e feita de padroes:
 *
 *  - '*'               : todas as permissoes nao-internas
 *  - 'prefixo.*'       : permissoes cujo nome comeca com 'prefixo.'
 *  - 'prefixo'         : permissao exata
 *  - 'acao' generica   : nao usada — permissoes sao rotas nomeadas
 *
 * A matriz padrao so e aplicada na CRIACAO do perfil — re-syncs
 * nao sobrescrevem ajustes manuais feitos na tela de perfis.
 */
class PerfisEscolaresSeeder extends Seeder
{
    /** Baseline self-service: home, perfil e notificacoes sao sempre liberados. */
    private const BASE = ['home', 'dashboard'];

    /**
     * Matriz padrao por perfil (role.name => padroes de permissao).
     */
    public const PERFIS = [
        'administrador' => ['*'],

        'administrador_rede' => [
            ...self::BASE,
            'menu.*',
            'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.edit', 'usuario.update',
            'usuario.filtro', 'usuario.removerFiltro', 'usuario.setPerPage',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.*', 'organizacao.*', 'documentos.*',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*', 'matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*',
            'plano_mensalidade.*', 'mensalidade.*', 'aviso.*', 'livro.*', 'emprestimo.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*',
            'aluno.*', 'responsavel.*',
        ],

        'diretor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show', 'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.filtro', 'documentos.removerFiltro',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*', 'matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*',
            'plano_mensalidade.*', 'mensalidade.*', 'aviso.*', 'livro.*', 'emprestimo.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*',
            'aluno.*', 'responsavel.*',
        ],

        'vice_diretor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show', 'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.filtro', 'documentos.removerFiltro',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*', 'matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*',
            'plano_mensalidade.*', 'mensalidade.*', 'aviso.*', 'livro.*', 'emprestimo.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*',
            'aluno.*', 'responsavel.*',
        ],

        'coordenador_pedagogico' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.filtro', 'usuario.removerFiltro',
            'usuario.setPerPage', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index',
            'ano_letivo.*', 'periodo_letivo.*', 'etapa.*', 'disciplina.*', 'turma.*',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.index', 'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.filtro',
            'aluno.documento.arquivo',
            'aluno.removerFiltro', 'aluno.setPerPage',
            'responsavel.index', 'responsavel.show', 'responsavel.filtro', 'responsavel.removerFiltro', 'responsavel.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'ocorrencia.index', 'ocorrencia.create', 'ocorrencia.store', 'ocorrencia.edit', 'ocorrencia.update',
            'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'plano_atendimento.index', 'plano_atendimento.filtro', 'plano_atendimento.removerFiltro', 'plano_atendimento.setPerPage',
            'tarefa.index', 'tarefa.create', 'tarefa.store', 'tarefa.edit', 'tarefa.update',
            'tarefa.entregas', 'tarefa.entregas.salvar',
            'tarefa.filtro', 'tarefa.removerFiltro', 'tarefa.setPerPage',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'livro.index', 'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'veiculo.index', 'veiculo.filtro', 'veiculo.removerFiltro', 'veiculo.setPerPage',
            'rota_transporte.index', 'rota_transporte.filtro', 'rota_transporte.removerFiltro', 'rota_transporte.setPerPage',
            'transporte_aluno.index', 'transporte_aluno.filtro', 'transporte_aluno.removerFiltro', 'transporte_aluno.setPerPage',
            'cardapio.index', 'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.create', 'evento.store', 'evento.edit', 'evento.update',
            'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.create', 'pesquisa.store', 'pesquisa.edit', 'pesquisa.update',
            'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
            'relatorio_frequencia.index', 'relatorio_transporte.index',
        ],

        'secretario' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.*',
            'escola.index', 'escola.show', 'escola.create', 'escola.store', 'escola.edit', 'escola.update',
            'escola.filtro', 'escola.removerFiltro', 'escola.setPerPage',
            'organizacao.index', 'organizacao.show',
            'documentos.*',
            'ano_letivo.index', 'ano_letivo.filtro', 'ano_letivo.removerFiltro', 'ano_letivo.setPerPage',
            'periodo_letivo.index', 'periodo_letivo.filtro', 'periodo_letivo.removerFiltro', 'periodo_letivo.setPerPage',
            'etapa.index', 'etapa.filtro', 'etapa.removerFiltro', 'etapa.setPerPage',
            'disciplina.index', 'disciplina.filtro', 'disciplina.removerFiltro', 'disciplina.setPerPage',
            'turma.index', 'turma.show', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage',
            'matricula.*', 'diario.*', 'avaliacao.*', 'boletim.*', 'ocorrencia.*', 'plano_atendimento.*', 'tarefa.*', 'aviso.*', 'livro.*', 'emprestimo.*', 'relatorio_frequencia.*', 'relatorio_inadimplencia.*', 'relatorio_transporte.*',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*', 'cardapio.*', 'evento.*', 'pesquisa.*',
            'plano_mensalidade.index', 'mensalidade.index',
            'aluno.*', 'responsavel.*',
        ],

        'auxiliar_secretaria' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.create', 'usuario.store',
            'usuario.filtro', 'usuario.removerFiltro', 'usuario.setPerPage',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'periodo_letivo.index', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage',
            'matricula.index', 'matricula.create', 'matricula.store', 'matricula.edit', 'matricula.update',
            'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.create', 'diario.store', 'diario.edit', 'diario.update',
            'avaliacao.index', 'avaliacao.create', 'avaliacao.store', 'avaliacao.edit', 'avaliacao.update',
            'diario.frequencia', 'diario.frequencia.salvar',
            'avaliacao.notas', 'avaliacao.notas.salvar',
            'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.create', 'aluno.store', 'aluno.edit', 'aluno.update',
            'aluno.filtro', 'aluno.removerFiltro', 'aluno.setPerPage',
            'aluno.responsavel.store', 'aluno.responsavel.update', 'aluno.responsavel.destroy',
            'aluno.documento.store', 'aluno.documento.update', 'aluno.documento.destroy', 'aluno.documento.arquivo',
            'responsavel.index', 'responsavel.show', 'responsavel.create', 'responsavel.store',
            'responsavel.filtro', 'responsavel.removerFiltro', 'responsavel.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'plano_mensalidade.index', 'mensalidade.index',
            'aviso.index', 'aviso.create', 'aviso.store', 'aviso.edit', 'aviso.update',
            'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'livro.index', 'livro.create', 'livro.store', 'livro.edit', 'livro.update',
            'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.create', 'emprestimo.store', 'emprestimo.devolver',
            'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'veiculo.index', 'veiculo.create', 'veiculo.store', 'veiculo.edit', 'veiculo.update',
            'veiculo.filtro', 'veiculo.removerFiltro', 'veiculo.setPerPage',
            'rota_transporte.index', 'rota_transporte.create', 'rota_transporte.store', 'rota_transporte.edit', 'rota_transporte.update',
            'rota_transporte.filtro', 'rota_transporte.removerFiltro', 'rota_transporte.setPerPage',
            'transporte_aluno.index', 'transporte_aluno.create', 'transporte_aluno.store', 'transporte_aluno.edit', 'transporte_aluno.update',
            'transporte_aluno.filtro', 'transporte_aluno.removerFiltro', 'transporte_aluno.setPerPage',
            'cardapio.index', 'cardapio.create', 'cardapio.store', 'cardapio.edit', 'cardapio.update',
            'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.create', 'evento.store', 'evento.edit', 'evento.update',
            'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.create', 'pesquisa.store', 'pesquisa.edit', 'pesquisa.update',
            'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
            'ocorrencia.index', 'ocorrencia.create', 'ocorrencia.store', 'ocorrencia.edit', 'ocorrencia.update',
            'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'relatorio_frequencia.index', 'relatorio_inadimplencia.index', 'relatorio_transporte.index',
        ],

        'professor' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'ano_letivo.index', 'periodo_letivo.index', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.create', 'diario.store', 'diario.edit', 'diario.update',
            'avaliacao.index', 'avaliacao.create', 'avaliacao.store', 'avaliacao.edit', 'avaliacao.update',
            'diario.frequencia', 'diario.frequencia.salvar',
            'avaliacao.notas', 'avaliacao.notas.salvar',
            'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.filtro',
            'aluno.documento.arquivo',
            'aluno.removerFiltro', 'aluno.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'ocorrencia.index', 'ocorrencia.create', 'ocorrencia.store',
            'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'tarefa.index', 'tarefa.create', 'tarefa.store', 'tarefa.edit', 'tarefa.update',
            'tarefa.entregas', 'tarefa.entregas.salvar',
            'tarefa.filtro', 'tarefa.removerFiltro', 'tarefa.setPerPage',
            'livro.index', 'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'veiculo.index', 'veiculo.filtro', 'veiculo.removerFiltro', 'veiculo.setPerPage',
            'rota_transporte.index', 'rota_transporte.filtro', 'rota_transporte.removerFiltro', 'rota_transporte.setPerPage',
            'transporte_aluno.index', 'transporte_aluno.filtro', 'transporte_aluno.removerFiltro', 'transporte_aluno.setPerPage',
            'cardapio.index', 'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
        ],

        'professor_substituto' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'ano_letivo.index', 'periodo_letivo.index', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show', 'turma.filtro', 'turma.removerFiltro', 'turma.setPerPage',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.create', 'diario.store', 'diario.edit', 'diario.update',
            'avaliacao.index', 'avaliacao.create', 'avaliacao.store', 'avaliacao.edit', 'avaliacao.update',
            'diario.frequencia', 'diario.frequencia.salvar',
            'avaliacao.notas', 'avaliacao.notas.salvar',
            'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.filtro',
            'aluno.documento.arquivo',
            'aluno.removerFiltro', 'aluno.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'ocorrencia.index', 'ocorrencia.create', 'ocorrencia.store',
            'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'tarefa.index', 'tarefa.create', 'tarefa.store', 'tarefa.edit', 'tarefa.update',
            'tarefa.entregas', 'tarefa.entregas.salvar',
            'tarefa.filtro', 'tarefa.removerFiltro', 'tarefa.setPerPage',
            'livro.index', 'livro.filtro', 'livro.removerFiltro', 'livro.setPerPage',
            'emprestimo.index', 'emprestimo.filtro', 'emprestimo.removerFiltro', 'emprestimo.setPerPage',
            'veiculo.index', 'veiculo.filtro', 'veiculo.removerFiltro', 'veiculo.setPerPage',
            'rota_transporte.index', 'rota_transporte.filtro', 'rota_transporte.removerFiltro', 'rota_transporte.setPerPage',
            'transporte_aluno.index', 'transporte_aluno.filtro', 'transporte_aluno.removerFiltro', 'transporte_aluno.setPerPage',
            'cardapio.index', 'cardapio.filtro', 'cardapio.removerFiltro', 'cardapio.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
        ],

        'funcionario_administrativo' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'periodo_letivo.index', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.index', 'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao',
            'aluno.documento.arquivo',
            'responsavel.index', 'responsavel.show',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
            'pesquisa.index', 'pesquisa.resultados', 'pesquisa.filtro', 'pesquisa.removerFiltro', 'pesquisa.setPerPage',
        ],

        'financeiro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show',
            'escola.index', 'escola.show',
            'organizacao.index', 'organizacao.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'periodo_letivo.index', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show',
            'matricula.index', 'matricula.filtro', 'matricula.removerFiltro', 'matricula.setPerPage',
            'diario.index', 'diario.filtro', 'diario.removerFiltro', 'diario.setPerPage',
            'avaliacao.index', 'avaliacao.filtro', 'avaliacao.removerFiltro', 'avaliacao.setPerPage',
            'aluno.index', 'aluno.show', 'aluno.declaracao',
            'responsavel.index', 'responsavel.show', 'responsavel.filtro', 'responsavel.removerFiltro', 'responsavel.setPerPage',
            'boletim.index', 'boletim.turma', 'boletim.aluno',
            'plano_mensalidade.*', 'mensalidade.*',
            'aviso.index', 'aviso.filtro', 'aviso.removerFiltro', 'aviso.setPerPage',
            'relatorio_inadimplencia.index',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'bibliotecario' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao',
            'livro.*', 'emprestimo.*',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'responsavel_transporte' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao',
            'veiculo.*', 'rota_transporte.*', 'transporte_aluno.*',
            'relatorio_transporte.index',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'responsavel_alimentacao' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'escola.index', 'escola.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao',
            'cardapio.*',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'atendimento_especializado' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'usuario.index', 'usuario.show', 'usuario.buscar', 'usuario.selectOptions',
            'escola.index', 'escola.show',
            'documentos.index', 'documentos.show',
            'ano_letivo.index', 'periodo_letivo.index', 'etapa.index', 'disciplina.index',
            'turma.index', 'turma.show',
            'aluno.index', 'aluno.show', 'aluno.declaracao', 'aluno.edit',
            'aluno.documento.arquivo',
            'aluno.update',
            'aluno.filtro', 'aluno.removerFiltro', 'aluno.setPerPage',
            'ocorrencia.index', 'ocorrencia.filtro', 'ocorrencia.removerFiltro', 'ocorrencia.setPerPage',
            'plano_atendimento.index', 'plano_atendimento.create', 'plano_atendimento.store',
            'plano_atendimento.edit', 'plano_atendimento.update', 'plano_atendimento.destroy',
            'plano_atendimento.filtro', 'plano_atendimento.removerFiltro', 'plano_atendimento.setPerPage',
            'evento.index', 'evento.filtro', 'evento.removerFiltro', 'evento.setPerPage',
        ],

        'aluno' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'portal.*',
        ],

        'responsavel_legal' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'portal.*',
        ],

        'responsavel_financeiro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
            'portal.*',
        ],

        // Baseline interno: usuario generico com acesso apenas as
        // telas self-service. Usado como fallback de remapeamento.
        'membro' => [
            ...self::BASE,
            'menu.*', 'perfil.*',
        ],
    ];

    /**
     * Resolve os padroes de um perfil contra as permissoes existentes.
     *
     * @param  array<int, string>  $padroes
     * @param  \Illuminate\Support\Collection<int, string>  $nomesPermissoes
     * @return array<int, string>
     */
    public static function resolverPadroes(array $padroes, $nomesPermissoes): array
    {
        $resolvidas = [];

        foreach ($padroes as $padrao) {
            if ($padrao === '*') {
                // Tudo que nao e permissao interna de plataforma.
                foreach ($nomesPermissoes as $nome) {
                    if (! \App\Models\Permissao::isNomeInterno($nome)) {
                        $resolvidas[] = $nome;
                    }
                }

                continue;
            }

            if (str_ends_with($padrao, '.*')) {
                $prefixo = substr($padrao, 0, -1); // mantem o ponto: 'escola.'
                foreach ($nomesPermissoes as $nome) {
                    if (str_starts_with($nome, $prefixo)) {
                        $resolvidas[] = $nome;
                    }
                }

                continue;
            }

            if (in_array($padrao, $nomesPermissoes->all(), true)) {
                $resolvidas[] = $padrao;
            }
        }

        return array_values(array_unique($resolvidas));
    }

    /**
     * Garante que todos os perfis existam para uma empresa
     * (usado no cadastro de novo tenant).
     */
    public function garantirPerfisParaEmpresa(int $empresaId): void
    {
        foreach (array_keys(self::PERFIS) as $nome) {
            DB::table('roles')->updateOrInsert(
                ['name' => $nome, 'guard_name' => 'web', 'empresa_id' => $empresaId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Sincroniza permissoes (rotas nomeadas) e cria os perfis com a
     * matriz padrao para todas as empresas existentes.
     */
    public function run(): void
    {
        app(\App\Services\PermissaoSyncService::class)->sync();
    }
}
