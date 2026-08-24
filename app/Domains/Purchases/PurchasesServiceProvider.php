<?php

namespace App\Domains\Purchases;

use App\Adapters\Purchases\MediaLibraryExpenseReceiptManager;
use App\Adapters\Purchases\MoneyExpenseExchangeRateRecorder;
use App\Adapters\Purchases\TaxationExpenseTaxManager;
use App\Domains\Purchases\Application\ClearExpenseTaxes;
use App\Domains\Purchases\Contracts\ExpenseExchangeRateRecorder;
use App\Domains\Purchases\Contracts\ExpenseReceiptManager;
use App\Domains\Purchases\Contracts\ExpenseTaxManager;
use App\Domains\Purchases\Models\Expense;
use App\Domains\Purchases\Models\ExpenseCategory;
use App\Domains\Purchases\Policies\ExpenseCategoryPolicy;
use App\Domains\Purchases\Policies\ExpensePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PurchasesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExpenseTaxManager::class, TaxationExpenseTaxManager::class);
        $this->app->bind(ExpenseExchangeRateRecorder::class, MoneyExpenseExchangeRateRecorder::class);
        $this->app->bind(ExpenseReceiptManager::class, MediaLibraryExpenseReceiptManager::class);
    }

    public function boot(): void
    {
        Expense::observe(ClearExpenseTaxes::class);

        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(ExpenseCategory::class, ExpenseCategoryPolicy::class);
        $bulkExpenseDelete = [ExpensePolicy::class, 'deleteMultiple'];
        Gate::define('delete multiple expenses', $bulkExpenseDelete);
    }
}
