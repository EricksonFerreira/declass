<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\Mensalidade;
use App\Models\User;
use App\Notifications\AvisoSistemaNotification;
use App\Support\EmpresaContext;
use Illuminate\Console\Command;

/**
 * Lembretes automaticos de mensalidade: roda diariamente e notifica
 * os usuarios do portal (responsaveis/alunos vinculados) sobre
 * cobrancas que vencem em N dias ou que ja venceram. Cada tipo de
 * lembrete e disparado uma unica vez por mensalidade (colunas
 * lembrete_a_vencer_em / lembrete_vencida_em).
 */
class LembrarVencimentosCommand extends Command
{
    protected $signature = 'financeiro:lembrar-vencimentos {--dias=3 : Dias antes do vencimento para o primeiro lembrete}';

    protected $description = 'Notifica famílias sobre mensalidades a vencer e vencidas';

    public function handle(EmpresaContext $context): int
    {
        $dias = max(1, (int) $this->option('dias'));
        $enviados = 0;

        Empresa::query()->where('ativo', 'S')->pluck('id')->each(function (int $empresaId) use ($context, $dias, &$enviados) {
            $context->setEmpresaId($empresaId);
            $enviados += $this->notificarEmpresa($dias);
        });

        $this->info("Lembretes processados: {$enviados} notificacao(oes).");

        return self::SUCCESS;
    }

    private function notificarEmpresa(int $dias): int
    {
        $enviados = 0;
        $alvo = now()->addDays($dias)->toDateString();
        $hoje = now()->toDateString();

        // 1) vencem em N dias — lembrete unico
        $aVencer = Mensalidade::query()
            ->where('status', 'pendente')
            ->whereDate('vencimento', $alvo)
            ->whereNull('lembrete_a_vencer_em')
            ->with(['matricula.aluno.pessoa', 'matricula.aluno.responsaveis', 'matricula.aluno.user'])
            ->get();

        foreach ($aVencer as $m) {
            $enviados += $this->notificar($m, 'a_vencer', "vence em {$dias} dia(s)") ? 1 : 0;
            $m->forceFill(['lembrete_a_vencer_em' => now()])->save();
        }

        // 2) ja vencidas — lembrete unico
        $vencidas = Mensalidade::query()
            ->where('status', 'pendente')
            ->whereDate('vencimento', '<', $hoje)
            ->whereNull('lembrete_vencida_em')
            ->with(['matricula.aluno.pessoa', 'matricula.aluno.responsaveis', 'matricula.aluno.user'])
            ->get();

        foreach ($vencidas as $m) {
            $enviados += $this->notificar($m, 'vencida', 'esta vencida') ? 1 : 0;
            $m->forceFill(['lembrete_vencida_em' => now()])->save();
        }

        return $enviados;
    }

    /** Retorna true se ao menos um usuario foi notificado. */
    private function notificar(Mensalidade $m, string $tipo, string $situacao): bool
    {
        $aluno = $m->matricula?->aluno;
        if (! $aluno) {
            return false;
        }

        // responsaveis com conta + conta do proprio aluno
        $userIds = $aluno->responsaveis
            ->pluck('user_id')
            ->push($aluno->user_id)
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return false;
        }

        $nomeAluno = $aluno->pessoa?->nome ?? 'aluno';
        $competencia = \Carbon\Carbon::createFromFormat('Y-m', $m->competencia)->format('m/Y');
        $valor = 'R$ '.number_format((float) $m->valor, 2, ',', '.');

        $titulo = $tipo === 'vencida' ? 'Mensalidade vencida' : 'Mensalidade a vencer';
        $mensagem = "A mensalidade de {$nomeAluno} ({$competencia}, {$valor}, vencimento {$m->vencimento->format('d/m/Y')}) {$situacao}.";

        $enviou = false;
        User::query()->whereIn('id', $userIds)->get()->each(function (User $u) use ($titulo, $mensagem, $m, $tipo, &$enviou) {
            $u->notify(new AvisoSistemaNotification(
                $titulo,
                $mensagem,
                route('portal.financeiro'),
                'fas fa-file-invoice-dollar',
                $tipo === 'vencida' ? 'danger' : 'warning',
                'mensalidade-'.$m->id.'-'.$tipo,
                'financeiro'
            ));
            $enviou = true;
        });

        return $enviou;
    }
}
