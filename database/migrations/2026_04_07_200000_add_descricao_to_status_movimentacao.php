<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('status_movimentacao', 'descricao')) {
            Schema::table('status_movimentacao', function (Blueprint $table) {
                $table->text('descricao')->nullable()->after('nome');
            });
        }

        $descricoes = [
            'Concluído'             => 'A movimentação foi finalizada com sucesso. O veículo chegou ao destino e todos os registros foram encerrados.',
            'Em Trânsito'           => 'O veículo está a caminho do destino. Ainda não chegou ao local final da movimentação.',
            'Cancelado'             => 'A movimentação foi cancelada antes de ser concluída. O veículo permanece no local de origem.',
            'Quebrado'              => 'O veículo sofreu uma pane ou avaria durante a movimentação e está imobilizado.',
            'Em Manutenção'         => 'O veículo está passando por manutenção preventiva ou corretiva e temporariamente indisponível.',
            'Em Espera'             => 'A movimentação está aguardando autorização, disponibilidade de recurso ou outro pré-requisito.',
            'Indisponível'          => 'O veículo não pode ser movimentado no momento por alguma restrição operacional ou administrativa.',
            'Aguardando Manutenção' => 'O veículo aguarda entrada na fila de manutenção. Ainda não iniciou o serviço.',
        ];

        foreach ($descricoes as $nome => $descricao) {
            DB::table('status_movimentacao')
                ->where('nome', $nome)
                ->whereNull('descricao')
                ->update(['descricao' => $descricao]);
        }
    }

    public function down(): void
    {
        Schema::table('status_movimentacao', function (Blueprint $table) {
            $table->dropColumn('descricao');
        });
    }
};
