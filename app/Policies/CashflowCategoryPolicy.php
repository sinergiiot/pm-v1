<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CashflowCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class CashflowCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashflowCategory');
    }

    public function view(AuthUser $authUser, CashflowCategory $cashflowCategory): bool
    {
        return $authUser->can('View:CashflowCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CashflowCategory');
    }

    public function update(AuthUser $authUser, CashflowCategory $cashflowCategory): bool
    {
        return $authUser->can('Update:CashflowCategory');
    }

    public function delete(AuthUser $authUser, CashflowCategory $cashflowCategory): bool
    {
        return $authUser->can('Delete:CashflowCategory');
    }

    public function restore(AuthUser $authUser, CashflowCategory $cashflowCategory): bool
    {
        return $authUser->can('Restore:CashflowCategory');
    }

    public function forceDelete(AuthUser $authUser, CashflowCategory $cashflowCategory): bool
    {
        return $authUser->can('ForceDelete:CashflowCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CashflowCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CashflowCategory');
    }

    public function replicate(AuthUser $authUser, CashflowCategory $cashflowCategory): bool
    {
        return $authUser->can('Replicate:CashflowCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CashflowCategory');
    }

}