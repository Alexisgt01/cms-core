<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var array<int, string> */
    protected array $permissions = [
        'manage blog settings',
        'view blog authors',
        'create blog authors',
        'edit blog authors',
        'delete blog authors',
        'view blog posts',
        'create blog posts',
        'edit blog posts',
        'delete blog posts',
        'publish blog posts',
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
            $permissions['view blog authors'],
            $permissions['create blog authors'],
            $permissions['edit blog authors'],
            $permissions['view blog posts'],
            $permissions['create blog posts'],
            $permissions['edit blog posts'],
            $permissions['publish blog posts'],
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
