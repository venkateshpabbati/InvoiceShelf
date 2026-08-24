<?php

namespace App\Adapters\Contacts;

use App\Domains\Accounts\Models\CompanySetting;
use App\Domains\Contacts\Contracts\CustomerStatsProvider;
use App\Domains\Contacts\Models\Customer;
use App\Domains\Purchases\Models\Expense;
use App\Domains\Receivables\Models\Payment;
use App\Domains\Sales\Models\Invoice;
use Carbon\Carbon;

/**
 * One customer's fiscal year in numbers: twelve monthly buckets of invoiced,
 * spent and received money, plus the totals for the window as a whole.
 *
 * The window opens on the month named by the first dash-separated part of the
 * company's `fiscal_year` preference. Anything that part cannot be read as a
 * number, and the shipped default, the word "calendar_year", is exactly that,
 * intval()s to zero; month zero rolls Carbon back into December of the year
 * before, so those companies get a window opening the previous December. It is
 * a real, user-visible defect and it is reproduced here on purpose: the
 * dashboard walks the same window the same way, and the two have to keep
 * agreeing until they are fixed together.
 *
 * KNOWN QUIRK: only the fiscal-year lookup uses the $companyId that is passed
 * in. Every figure below is scoped by whereCompany(), which reads the company
 * header off the current request instead.
 */
class EloquentCustomerStatsProvider implements CustomerStatsProvider
{
    public function get(Customer $customer, int $companyId, bool $previousYear = false): array
    {
        $openingMonth = intval(explode('-', CompanySetting::getSetting('fiscal_year', $companyId))[0]);

        // Three cursors set to the same instant: the fixed left edge of the
        // whole window, and the pair that walks it one month at a time.
        $windowStart = Carbon::now();
        $monthStart = Carbon::now();
        $monthEnd = Carbon::now();

        // An opening month still ahead of us in the calendar year belongs to
        // the fiscal year that opened twelve months ago.
        $openedLastYear = $openingMonth > $monthStart->month;

        foreach ([$windowStart, $monthStart, $monthEnd] as $cursor) {
            if ($openedLastYear) {
                $cursor->subYear();
            }

            $cursor->month($openingMonth);
        }

        $windowStart->startOfMonth();
        $monthStart->startOfMonth();
        $monthEnd->endOfMonth();

        if ($previousYear) {
            $windowStart->subYear()->startOfMonth();
            $monthStart->subYear()->startOfMonth();
            $monthEnd->subYear()->endOfMonth();
        }

        $months = [];
        $invoiceTotals = [];
        $expenseTotals = [];
        $receiptTotals = [];
        $netProfits = [];

        for ($bucket = 0; $bucket < 12; $bucket++) {
            $bucketSpan = [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')];

            $invoiceTotals[] = Invoice::query()
                ->whereBetween('invoice_date', $bucketSpan)
                ->whereCompany()
                ->whereCustomer($customer->id)
                ->sum('base_total');

            $expenseTotals[] = Expense::query()
                ->whereBetween('expense_date', $bucketSpan)
                ->whereCompany()
                ->whereUser($customer->id)
                ->sum('base_amount');

            $receiptTotals[] = Payment::query()
                ->whereBetween('payment_date', $bucketSpan)
                ->whereCompany()
                ->whereCustomer($customer->id)
                ->sum('base_amount');

            // What was received less what was spent. Invoiced money is not in
            // it: a bill that has not been paid is not profit.
            $netProfits[] = $receiptTotals[$bucket] - $expenseTotals[$bucket];

            $months[] = $monthStart->translatedFormat('M');

            // Both cursors step off the first of their month, so a short month
            // can never drag the walk backwards.
            $monthEnd->startOfMonth()->addMonth()->endOfMonth();
            $monthStart->addMonth()->startOfMonth();
        }

        // Twelve steps left the walking cursor on the month after the window.
        // Back it on to the last month of the window and take that month's
        // final day as the right edge of the whole-window figures.
        $monthStart->subMonth()->endOfMonth();

        $windowSpan = [$windowStart->format('Y-m-d'), $monthStart->format('Y-m-d')];

        $salesTotal = Invoice::query()
            ->whereBetween('invoice_date', $windowSpan)
            ->whereCompany()
            ->whereCustomer($customer->id)
            ->sum('base_total');

        $totalReceipts = Payment::query()
            ->whereBetween('payment_date', $windowSpan)
            ->whereCompany()
            ->whereCustomer($customer->id)
            ->sum('base_amount');

        $totalExpenses = Expense::query()
            ->whereBetween('expense_date', $windowSpan)
            ->whereCompany()
            ->whereUser($customer->id)
            ->sum('base_amount');

        return [
            'months' => $months,
            'invoiceTotals' => $invoiceTotals,
            'expenseTotals' => $expenseTotals,
            'receiptTotals' => $receiptTotals,
            // KNOWN QUIRK: both sides are cut to whole units before the
            // subtraction, so the headline figure loses the cents that the
            // three totals beside it keep.
            'netProfit' => (int) $totalReceipts - (int) $totalExpenses,
            'netProfits' => $netProfits,
            'salesTotal' => $salesTotal,
            'totalReceipts' => $totalReceipts,
            'totalExpenses' => $totalExpenses,
        ];
    }
}
