<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect([
            'view users',
            'create users',
            'edit users',
            'delete users',
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'edit profile',
            'delete profile',
        ])->mapWithKeys(fn (string $name) => [$name => Permission::create(['name' => $name])]);

        $superAdmin = Role::create(['name' => 'super_admin']);
        $editor = Role::create(['name' => 'editor']);
        $viewer = Role::create(['name' => 'viewer']);

        $superAdmin->givePermissionTo($permissions->values());

        $editor->givePermissionTo([
            $permissions['view users'],
            $permissions['create users'],
            $permissions['edit users'],
            $permissions['view roles'],
            $permissions['edit profile'],
        ]);

        $viewer->givePermissionTo([
            $permissions['view users'],
            $permissions['view roles'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::whereIn('name', ['super_admin', 'editor', 'viewer'])->delete();
        Permission::whereIn('name', [
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'edit profile', 'delete profile',
        ])->delete();
    }
};
