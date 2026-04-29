<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * IDs fixos e globais — nunca alterar a ordem.
 * Mapeamento usado em StatusReservaEnum e em todo o código da aplicação:
 *   1 = Pendente | 2 = Confirmada | 3 = Check-in | 4 = Check-out | 5 = Cancelada | 6 = No-show
 */
return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            ['id' => 1, 'nome' => 'Pendente',   'descricao' => 'Reserva criada, aguardando confirmação', 'cor' => '#f59e0b'],
            ['id' => 2, 'nome' => 'Confirmada', 'descricao' => 'Reserva confirmada pela estação',          'cor' => '#3b82f6'],
            ['id' => 3, 'nome' => 'Check-in',   'descricao' => 'Veículo entregue ao cliente',              'cor' => '#8b5cf6'],
            ['id' => 4, 'nome' => 'Check-out',  'descricao' => 'Veículo devolvido',                        'cor' => '#10b981'],
            ['id' => 5, 'nome' => 'Cancelada',  'descricao' => 'Reserva cancelada',                        'cor' => '#ef4444'],
            ['id' => 6, 'nome' => 'No-show',    'descricao' => 'Cliente não compareceu',                   'cor' => '#6b7280'],
        ];

        foreach ($statuses as $s) {
            $existe = DB::table('status_reserva')->where('id', $s['id'])->exists();
            if ($existe) {
                DB::table('status_reserva')->where('id', $s['id'])->update([
                    'nome'       => $s['nome'],
                    'descricao'  => $s['descricao'],
                    'cor'        => $s['cor'],
                    'ativo'      => 'S',
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('status_reserva')->insert([
                    'id'         => $s['id'],
                    'nome'       => $s['nome'],
                    'descricao'  => $s['descricao'],
                    'ativo'      => 'S',
                    'cor'        => $s['cor'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void {}
};
