<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var array<int, string> */
    protected array $permissions = [
        'view pages',
        'create pages',
        'edit pages',
        'delete pages',
        'publish pages',
    ];

    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect($this->permissions)
            ->mapWithKeys(fn (string $name) => [$name => Permission::create(['name' => $name])]);

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $superAdmin->givePermissionTo($permissions->values());

        $editor->givePermissionTo([
            $permissions['view pages'],
            $permissions['create pages'],
            $permissions['edit pages'],
            $permissions['publish pages'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $pagePermissions = Permission::whereIn('name', $this->permissions)->get();

        $superAdmin->revokePermissionTo($pagePermissions);
        $editor->revokePermissionTo($pagePermissions);

        $pagePermissions->each->delete();
    }
};
