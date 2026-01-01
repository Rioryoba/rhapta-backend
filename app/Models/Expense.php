<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id', 'created_by', 'requested_by', 'received_by', 'description', 'expense_date', 'reference', 'amount', 'subtotal', 'tax', 'discount', 'total', 'status', 'currency', 'site_id', 'department_id', 'category'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function items()
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function receiver()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope a query to apply common filters from request input.
     * Supports camelCase and snake_case keys.
     *
     * Examples: accountId / account_id, fromDate / from_date, toDate / to_date, status, search
     */
    public function scopeFilter($query, array $filters = [])
    {
        $get = function ($keys, $default = null) use ($filters) {
            foreach ((array) $keys as $k) {
                if (array_key_exists($k, $filters) && $filters[$k] !== null && $filters[$k] !== '') {
                    return $filters[$k];
                }
            }
            return $default;
        };

        if ($account = $get(['accountId', 'account_id'])) {
            $query->where('account_id', $account);
        }

        if ($status = $get(['status'])) {
            $query->where('status', $status);
        }

        if ($from = $get(['fromDate', 'from_date'])) {
            $query->whereDate('expense_date', '>=', $from);
        }

        if ($to = $get(['toDate', 'to_date'])) {
            $query->whereDate('expense_date', '<=', $to);
        }

        if ($search = $get(['search', 'q'])) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
