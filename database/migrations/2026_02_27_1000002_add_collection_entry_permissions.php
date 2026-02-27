<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /** @var array<int, string> */
    protected array $permissions = [
        'view collection entries',
        'create collection entries',
        'edit collection entries',
        'delete collection entries',
        'publish collection entries',
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
            $permissions['view collection entries'],
            $permissions['create collection entries'],
            $permissions['edit collection entries'],
            $permissions['publish collection entries'],
        ]);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::findByName('super_admin');
        $editor = Role::findByName('editor');

        $entryPermissions = Permission::whereIn('name', $this->permissions)->get();

        $superAdmin->revokePermissionTo($entryPermissions);
        $editor->revokePermissionTo($entryPermissions);

        $entryPermissions->each->delete();
    }
};
