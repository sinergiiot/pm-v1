<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\RekeningBank;
use Illuminate\Auth\Access\HandlesAuthorization;

class RekeningBankPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RekeningBank');
    }

    public function view(AuthUser $authUser, RekeningBank $rekeningBank): bool
    {
        return $authUser->can('View:RekeningBank');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RekeningBank');
    }

    public function update(AuthUser $authUser, RekeningBank $rekeningBank): bool
    {
        return $authUser->can('Update:RekeningBank');
    }

    public function delete(AuthUser $authUser, RekeningBank $rekeningBank): bool
    {
        return $authUser->can('Delete:RekeningBank');
    }

    public function restore(AuthUser $authUser, RekeningBank $rekeningBank): bool
    {
        return $authUser->can('Restore:RekeningBank');
    }

    public function forceDelete(AuthUser $authUser, RekeningBank $rekeningBank): bool
    {
        return $authUser->can('ForceDelete:RekeningBank');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RekeningBank');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RekeningBank');
    }

    public function replicate(AuthUser $authUser, RekeningBank $rekeningBank): bool
    {
        return $authUser->can('Replicate:RekeningBank');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RekeningBank');
    }

}