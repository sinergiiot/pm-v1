<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PnlCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class PnlCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PnlCategory');
    }

    public function view(AuthUser $authUser, PnlCategory $pnlCategory): bool
    {
        return $authUser->can('View:PnlCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PnlCategory');
    }

    public function update(AuthUser $authUser, PnlCategory $pnlCategory): bool
    {
        return $authUser->can('Update:PnlCategory');
    }

    public function delete(AuthUser $authUser, PnlCategory $pnlCategory): bool
    {
        return $authUser->can('Delete:PnlCategory');
    }

    public function restore(AuthUser $authUser, PnlCategory $pnlCategory): bool
    {
        return $authUser->can('Restore:PnlCategory');
    }

    public function forceDelete(AuthUser $authUser, PnlCategory $pnlCategory): bool
    {
        return $authUser->can('ForceDelete:PnlCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PnlCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PnlCategory');
    }

    public function replicate(AuthUser $authUser, PnlCategory $pnlCategory): bool
    {
        return $authUser->can('Replicate:PnlCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PnlCategory');
    }

}