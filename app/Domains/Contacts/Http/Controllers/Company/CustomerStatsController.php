<?php

namespace App\Domains\Contacts\Http\Controllers\Company;

use App\Domains\Contacts\Contracts\CustomerStatsProvider;
use App\Domains\Contacts\Http\Resources\CustomerResource;
use App\Domains\Contacts\Models\Customer;
use App\Domains\Reporting\Queries\CustomerStatementQuery;
use App\Platform\Http\Controller;
use Illuminate\Http\Request;

/**
 * The twelve-month chart shown on one contact's page.
 *
 * The window is anchored on the company's fiscal-year setting. A
 * `previous_year` key anywhere in the query string — whatever its value, the
 * presence alone counts — pushes the whole window back one year.
 *
 * The company comes off the request header rather than the resolved company,
 * exactly as it reaches the provider's integer parameter.
 */
class CustomerStatsController extends Controller
{
    public function __construct(
        private readonly CustomerStatsProvider $customerStatsProvider,
        private readonly CustomerStatementQuery $customerStatementQuery,
    ) {}

    public function __invoke(Request $request, Customer $customer)
    {
        $this->authorize('view', $customer);

        $companyId = $request->header('company');
        $previousYear = $request->has('previous_year');

        $chartData = $this->customerStatsProvider->get($customer, $companyId, $previousYear);

        // The row is read a second time instead of reusing the bound instance.
        // Nothing the provider does requires it, but the reload is what the
        // resource ends up rendering, so it stays.
        $fresh = Customer::query()->find($customer->id);

        $this->customerStatementQuery->hydrateAccountSummaries([$fresh]);

        return (new CustomerResource($fresh))
            ->additional(['meta' => ['chartData' => $chartData]]);
    }
}
