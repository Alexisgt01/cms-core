<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var array<int, string> */
    protected array $permissions = [
        'view redirects',
        'create redirects',
        'edit redirects',
        'delete redirects',
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
            $permissions['view redirects'],
            $permissions['create redirects'],
            $permissions['edit redirects'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $redirectPermissions = Permission::whereIn('name', $this->permissions)->get();

        $superAdmin->revokePermissionTo($redirectPermissions);
        $editor->revokePermissionTo($redirectPermissions);

        $redirectPermissions->each->delete();
    }
};
