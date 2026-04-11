<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionName = 'reserva.minhas';
        $guardName      = 'web';

        // Create the permission if it doesn't exist yet.
        $permissionId = DB::table('permissions')
            ->where('name', $permissionName)
            ->where('guard_name', $guardName)
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name'       => $permissionName,
                'guard_name' => $guardName,
                'grupo'      => 'reserva',
                'descricao'  => 'Visualizar as próprias reservas (cliente)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Attach this permission to every existing role that doesn't have it.
        $roleIds = DB::table('roles')->pluck('id');

        foreach ($roleIds as $roleId) {
            $alreadyLinked = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (! $alreadyLinked) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id'       => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')
            ->where('name', 'reserva.minhas')
            ->where('guard_name', 'web')
            ->first();

        if ($permission) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();

            DB::table('permissions')
                ->where('id', $permission->id)
                ->delete();
        }
    }
};
