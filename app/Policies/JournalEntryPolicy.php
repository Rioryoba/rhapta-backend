<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JournalEntryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JournalEntry $journalEntry): bool
    {
        // Journal entries should not be updated, create a reversing entry instead
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        // Journal entries should not be deleted, create a reversing entry instead
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JournalEntry $journalEntry): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JournalEntry $journalEntry): bool
    {
        //
    }
}
