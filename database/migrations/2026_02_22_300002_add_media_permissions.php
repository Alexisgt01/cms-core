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
            'view media',
            'create media',
            'edit media',
            'delete media',
        ])->mapWithKeys(fn (string $name) => [$name => Permission::create(['name' => $name])]);

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $superAdmin->givePermissionTo($permissions->values());

        $editor->givePermissionTo([
            $permissions['view media'],
            $permissions['create media'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $mediaPermissions = Permission::whereIn('name', [
            'view media', 'create media', 'edit media', 'delete media',
        ])->get();

        $superAdmin->revokePermissionTo($mediaPermissions);
        $editor->revokePermissionTo($mediaPermissions);

        $mediaPermissions->each->delete();
    }
};
