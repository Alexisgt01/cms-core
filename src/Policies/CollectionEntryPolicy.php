<?php

namespace Alexisgt01\CmsCore\Policies;

use Alexisgt01\CmsCore\Models\CollectionEntry;
use App\Models\User;

class CollectionEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view collection entries');
    }

    public function view(User $user, CollectionEntry $entry): bool
    {
        return $user->can('view collection entries');
    }

    public function create(User $user): bool
    {
        return $user->can('create collection entries');
    }

    public function update(User $user, CollectionEntry $entry): bool
    {
        return $user->can('edit collection entries');
    }

    public function delete(User $user, CollectionEntry $entry): bool
    {
        return $user->can('delete collection entries');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete collection entries');
    }

    public function restore(User $user, CollectionEntry $entry): bool
    {
        return $user->can('delete collection entries');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('delete collection entries');
    }

    public function forceDelete(User $user, CollectionEntry $entry): bool
    {
        return $user->can('delete collection entries');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('delete collection entries');
    }
}
