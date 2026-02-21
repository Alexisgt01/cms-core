<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var array<int, string> */
    protected array $permissions = [
        'view blog categories',
        'create blog categories',
        'edit blog categories',
        'delete blog categories',
        'view blog tags',
        'create blog tags',
        'edit blog tags',
        'delete blog tags',
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
            $permissions['view blog categories'],
            $permissions['create blog categories'],
            $permissions['edit blog categories'],
            $permissions['view blog tags'],
            $permissions['create blog tags'],
            $permissions['edit blog tags'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $blogPermissions = Permission::whereIn('name', $this->permissions)->get();

        $superAdmin->revokePermissionTo($blogPermissions);
        $editor->revokePermissionTo($blogPermissions);

        $blogPermissions->each->delete();
    }
};
