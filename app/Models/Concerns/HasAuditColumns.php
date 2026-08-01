<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasAuditColumns
{
    protected static function bootHasAuditColumns(): void
    {
        static::creating(function ($model): void {
            $now = now();
            $userId = Auth::id();

            if (empty($model->datainc)) {
                $model->datainc = $now;
            }

            if (empty($model->usuinc)) {
                $model->usuinc = $userId;
            }

            $model->datatu = $model->datainc;
            $model->usuatu = $model->usuinc;
        });

        static::updating(function ($model): void {
            $now = now();
            $userId = Auth::id();

            $model->datatu = $now;
            $model->usuatu = $userId;
        });
    }
}
