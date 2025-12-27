<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class InvoiceItemPolicy
{
    public function viewAny(User $user): bool
    {
    return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    public function view(User $user, InvoiceItem $item): bool
    {
    return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    public function create(User $user): bool
    {
    return optional($user->role)->name === 'accountant';
    }

    public function update(User $user, InvoiceItem $item): bool
    {
    return optional($user->role)->name === 'accountant';
    }

    public function delete(User $user, InvoiceItem $item): bool
    {
    return optional($user->role)->name === 'accountant';
    }
}
