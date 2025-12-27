<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Request::class => \App\Policies\RequestPolicy::class,
        \App\Models\Attendence::class => \App\Policies\AttendencePolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
        \App\Models\InvoiceItem::class => \App\Policies\InvoiceItemPolicy::class,
        \App\Models\Customer::class => \App\Policies\CustomerPolicy::class,
            \App\Models\Expense::class => \App\Policies\ExpensePolicy::class,
            \App\Models\Account::class => \App\Policies\AccountPolicy::class,
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
