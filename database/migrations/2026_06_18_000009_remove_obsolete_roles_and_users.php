<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    private const OBSOLETE_ROLES = ['brilink', 'service', 'upah_kerja'];

    public function up(): void
    {
        $userIds = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('roles.name', self::OBSOLETE_ROLES)
            ->pluck('model_has_roles.model_id')
            ->unique();

        if ($userIds->isNotEmpty()) {
            User::query()->whereIn('id', $userIds)->delete();
        }

        Role::query()
            ->whereIn('name', self::OBSOLETE_ROLES)
            ->each(fn (Role $role) => $role->delete());
    }

    public function down(): void
    {
        foreach (self::OBSOLETE_ROLES as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }
};
