<?php

namespace Vendor\CmsCore\Policies;

use App\Models\User;
use Vendor\CmsCore\Models\CmsMedia;

class CmsMediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view media');
    }

    public function view(User $user, CmsMedia $media): bool
    {
        return $user->can('view media');
    }

    public function create(User $user): bool
    {
        return $user->can('create media');
    }

    public function update(User $user, CmsMedia $media): bool
    {
        return $user->can('edit media');
    }

    public function delete(User $user, CmsMedia $media): bool
    {
        return $user->can('delete media');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete media');
    }
}
