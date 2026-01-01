<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\Response;

class InvoicePolicy
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
    public function view(User $user, Invoice $invoice): bool
    {
        Log::info('InvoicePolicy view', ['user_id' => $user->id, 'role' => optional($user->role)->name, 'invoice_id' => $invoice->id]);
        return in_array(optional($user->role)->name, ['accountant', 'ceo']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        Log::info('InvoicePolicy create', ['user_id' => $user->id, 'role' => optional($user->role)->name]);
        return optional($user->role)->name === 'accountant';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        Log::info('InvoicePolicy update', ['user_id' => $user->id, 'role' => optional($user->role)->name, 'invoice_id' => $invoice->id]);
        return optional($user->role)->name === 'accountant';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        Log::info('InvoicePolicy delete', ['user_id' => $user->id, 'role' => optional($user->role)->name, 'invoice_id' => $invoice->id]);
        return optional($user->role)->name === 'accountant';
    }

    /**
     * Determine whether the user can restore the model.
     */
    // public function restore(User $user, Invoice $invoice): bool
    // {
    //     //
    // }

    /**
     * Determine whether the user can permanently delete the model.
     */
    // public function forceDelete(User $user, Invoice $invoice): bool
    // {
    //     //
    // }
}
