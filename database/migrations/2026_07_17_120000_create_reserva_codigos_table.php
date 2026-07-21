<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reserva_codigos')) {
            Schema::create('reserva_codigos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->nullable()->constrained('empresas')->cascadeOnDelete();
                $table->string('codigo', 40)->unique();
                $table->string('tipo_origem', 20)->index();
                $table->unsignedBigInteger('reserva_id')->nullable()->unique();
                $table->unsignedBigInteger('reserva_motorista_app_id')->nullable()->unique();
                $table->string('status_label', 80)->nullable();
                $table->dateTime('data_inicio')->nullable();
                $table->dateTime('data_fim')->nullable();
                $table->decimal('valor_total', 10, 2)->default(0);
                $table->timestamps();

                $table->index(['empresa_id', 'codigo'], 'reserva_codigos_empresa_codigo_idx');
            });
        }

        $this->backfillCodigos();
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_codigos');
    }

    private function backfillCodigos(): void
    {
        if (!Schema::hasTable('reserva_codigos')) {
            return;
        }

        if (Schema::hasTable('reservas')) {
            $campos = ['id'];
            foreach (['empresa_id', 'data_inicio', 'data_fim', 'valor_total_final', 'valor_total_com_desconto', 'valor_total_tarifa_base', 'status_reserva_id'] as $campo) {
                if (Schema::hasColumn('reservas', $campo)) {
                    $campos[] = $campo;
                }
            }

            DB::table('reservas')
                ->select($campos)
                ->orderBy('id')
                ->chunkById(200, function ($reservas): void {
                    foreach ($reservas as $reserva) {
                        DB::table('reserva_codigos')->updateOrInsert(
                            ['reserva_id' => (int) $reserva->id],
                            [
                                'empresa_id' => $reserva->empresa_id ?? null,
                                'codigo' => $this->gerarCodigoExistente('RES', (int) $reserva->id),
                                'tipo_origem' => 'reserva',
                                'reserva_motorista_app_id' => null,
                                'status_label' => $this->statusReservaLabel((int) ($reserva->status_reserva_id ?? 0)),
                                'data_inicio' => $reserva->data_inicio,
                                'data_fim' => $reserva->data_fim,
                                'valor_total' => $this->valorReserva((float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? $reserva->valor_total_tarifa_base ?? 0)),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                });
        }

        if (Schema::hasTable('reservas_motorista_app')) {
            $campos = ['id'];
            foreach (['empresa_id', 'data_inicio', 'data_fim', 'valor_total_final', 'status'] as $campo) {
                if (Schema::hasColumn('reservas_motorista_app', $campo)) {
                    $campos[] = $campo;
                }
            }

            DB::table('reservas_motorista_app')
                ->select($campos)
                ->orderBy('id')
                ->chunkById(200, function ($reservas): void {
                    foreach ($reservas as $reserva) {
                        DB::table('reserva_codigos')->updateOrInsert(
                            ['reserva_motorista_app_id' => (int) $reserva->id],
                            [
                                'empresa_id' => $reserva->empresa_id ?? null,
                                'codigo' => $this->gerarCodigoExistente('MAP', (int) $reserva->id),
                                'tipo_origem' => 'motorista_app',
                                'reserva_id' => null,
                                'status_label' => $reserva->status ?? null,
                                'data_inicio' => $reserva->data_inicio,
                                'data_fim' => $reserva->data_fim,
                                'valor_total' => $this->valorReserva((float) ($reserva->valor_total_final ?? 0)),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                });
        }
    }

    private function gerarCodigoExistente(string $prefixo, int $id): string
    {
        $codigo = sprintf('%s-%s-%06d', $prefixo, now()->format('ymd'), $id);

        if (!DB::table('reserva_codigos')->where('codigo', $codigo)->exists()) {
            return $codigo;
        }

        do {
            $codigo = sprintf('%s-%s-%s', $prefixo, now()->format('ymd'), strtoupper(Str::random(5)));
        } while (DB::table('reserva_codigos')->where('codigo', $codigo)->exists());

        return $codigo;
    }

    private function statusReservaLabel(int $statusId): ?string
    {
        return match ($statusId) {
            1 => 'Pendente',
            2 => 'Confirmada',
            3 => 'Check-in',
            4 => 'Check-out',
            5 => 'Cancelada',
            6 => 'No-show',
            default => null,
        };
    }

    private function valorReserva(float $valor): float
    {
        return round(max(0, $valor), 2);
    }
};
