<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate: user can access contract module
        Gate::define('access-contracts', function (User $user) {
            return $user->hasAnyRole([
                User::ROLE_CONTRACT,
                User::ROLE_RECEIVABLE,
                User::ROLE_ADMIN_FINANCE,
            ]);
        });

        // Gate: user can view financial fields in contracts
        Gate::define('view-contract-financials', function (User $user) {
            return $user->hasAnyRole([
                User::ROLE_RECEIVABLE,
                User::ROLE_ADMIN_FINANCE,
            ]);
        });

        // Gate: user can manage receivables (parametrizacao de parcelas, valores extras, baixas)
        Gate::define('manage-receivables', function (User $user) {
            return $user->hasAnyRole([
                User::ROLE_RECEIVABLE,
                User::ROLE_ADMIN_FINANCE,
            ]);
        });

        // Gate: user can manage payables (despesas operacionais vinculadas aos eventos)
        Gate::define('manage-payables', function (User $user) {
            return $user->hasAnyRole([
                User::ROLE_PAYABLE,
                User::ROLE_ADMIN_FINANCE,
            ]);
        });

        // Gate: user can access international/multicurrency features
        Gate::define('access-international', function (User $user) {
            return $user->hasAnyRole([
                User::ROLE_INTERNATIONAL,
                User::ROLE_ADMIN_FINANCE,
            ]);
        });

        // Gate: user can manage admin finances (folhas de pagamento, despesas operacionais da empresa)
        Gate::define('manage-admin-finance', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN_FINANCE);
        });

        // Gate: user can approve releases and exchange (approvacao final de baixas e cambio)
        Gate::define('approve-releases', function (User $user) {
            return $user->hasRole(User::ROLE_ADMIN_FINANCE);
        });
    }
}
