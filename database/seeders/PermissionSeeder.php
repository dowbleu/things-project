<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Просмотр всех вещей', 'slug' => 'view-all-things', 'description' => 'Просмотр всех вещей в системе'],
            ['name' => 'Управление местами', 'slug' => 'manage-places', 'description' => 'Полный доступ к управлению местами хранения'],
            ['name' => 'Управление вещами', 'slug' => 'manage-things', 'description' => 'Полный доступ к управлению вещами'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Назначаем все права администраторам
        $admins = User::where('role', 'admin')->get();
        $allPermissions = Permission::all();

        foreach ($admins as $admin) {
            $admin->permissions()->syncWithoutDetaching($allPermissions->pluck('id'));
        }
    }
}
