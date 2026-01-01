<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Only accountant and ceo can view the accounts list
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Account $account
     * @return bool
     */
    public function view(User $user, Account $account): bool
    {
        // Only accountant and ceo can view a single account
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Account $account
     * @return bool
     */
    public function update(User $user, Account $account): bool
    {
        // Only accountants may update accounts
        return optional($user->role)->name === 'accountant';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Account $account): bool
    {
        // No one is allowed to delete accounts
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Account $account): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Account $account): bool
    {
        return false;
    }
}
