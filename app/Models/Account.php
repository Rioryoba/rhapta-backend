<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes for quick seeding / API creation.
     */
    protected $fillable = [
        'account_number',
        'account_name',
        'account_description',
        'account_type',
        'bank_name',
        'balance',
        'code',
        'category',
        'type',
        'parent_id',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function debitJournalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'debit_account_id');
    }

    public function creditJournalEntries()
    {
        return $this->hasMany(JournalEntry::class, 'credit_account_id');
    }
}
