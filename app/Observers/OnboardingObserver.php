<?php

namespace App\Observers;

use App\Models\ClockifyProjeto;
use App\Models\ClockifyTimeTracker;
use App\Models\OnboardingStep;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Observer genérico que marca steps de onboarding quando modelos são criados.
 * Registrado para ClockifyProjeto, ClockifyTime e User.
 */
class OnboardingObserver
{
    public function created(Model $model): void
    {
        $empresaId = $this->resolverEmpresaId($model);

        if (! $empresaId) {
            return;
        }

        $step = OnboardingStep::where('empresa_id', $empresaId)
            ->where('concluido', false)
            ->first();

        if (! $step) {
            return;
        }

        $classe = get_class($model);

        match (true) {
            str_ends_with($classe, 'ClockifyProjeto') => $step->update(['step_frota_cadastrada' => true]),
            str_ends_with($classe, 'ClockifyTimeTracker') => $step->update(['step_primeiro_registro' => true]),
            str_ends_with($classe, 'User') => $step->update(['step_usuario_criado' => true]),
            default => null,
        };

        $this->verificarConclusao($step->fresh());
    }

    private function resolverEmpresaId(Model $model): ?int
    {
        if (isset($model->empresa_id)) {
            return (int) $model->empresa_id;
        }

        if (Auth::check() && Auth::user()->empresa_id) {
            return (int) Auth::user()->empresa_id;
        }

        return null;
    }

    private function verificarConclusao(OnboardingStep $step): void
    {
        if (! $step) {
            return;
        }

        $todosConcluidos = $step->step_empresa_configurada
            && $step->step_plano_contratado
            && ($step->step_frota_cadastrada
                || ClockifyProjeto::where('empresa_id', $step->empresa_id)->exists())
            && ($step->step_primeiro_registro
                || ClockifyTimeTracker::where('empresa_id', $step->empresa_id)->exists());

        if ($todosConcluidos && ! $step->concluido) {
            $step->update(['concluido' => true]);
        }
    }
}
