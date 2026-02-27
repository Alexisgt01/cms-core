<?php

namespace Alexisgt01\CmsCore\Policies;

use Alexisgt01\CmsCore\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view pages');
    }

    public function view(User $user, Page $page): bool
    {
        return $user->can('view pages');
    }

    public function create(User $user): bool
    {
        return $user->can('create pages');
    }

    public function update(User $user, Page $page): bool
    {
        return $user->can('edit pages');
    }

    public function delete(User $user, Page $page): bool
    {
        return $user->can('delete pages');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete pages');
    }

    public function restore(User $user, Page $page): bool
    {
        return $user->can('delete pages');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('delete pages');
    }

    public function forceDelete(User $user, Page $page): bool
    {
        return $user->can('delete pages');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('delete pages');
    }
}
