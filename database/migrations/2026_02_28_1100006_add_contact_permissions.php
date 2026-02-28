<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var array<int, string> */
    protected array $permissions = [
        'view contacts',
        'create contacts',
        'edit contacts',
        'delete contacts',
        'view contact requests',
        'create contact requests',
        'edit contact requests',
        'delete contact requests',
        'view contact hooks',
        'create contact hooks',
        'edit contact hooks',
        'delete contact hooks',
        'manage contact settings',
        'replay contact hooks',
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
            $permissions['view contacts'],
            $permissions['view contact requests'],
            $permissions['view contact hooks'],
            $permissions['replay contact hooks'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $contactPermissions = Permission::whereIn('name', $this->permissions)->get();

        $superAdmin->revokePermissionTo($contactPermissions);
        $editor->revokePermissionTo($contactPermissions);

        $contactPermissions->each->delete();
    }
};
