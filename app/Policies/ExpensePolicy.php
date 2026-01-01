<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user;
    }

    public function view(User $user, Expense $expense): bool
    {
        return in_array($user->role?->name, ['admin', 'accountant']) || $user->id === $expense->created_by;
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'accountant']);
    }

    public function update(User $user, Expense $expense): bool
    {
        return in_array($user->role?->name, ['admin', 'accountant']) && ($expense->status ?? 'pending') === 'pending';
    }

    public function delete(User $user, Expense $expense): bool
    {
        return in_array($user->role?->name, ['admin']) && ($expense->status ?? 'pending') === 'pending';
    }
}
