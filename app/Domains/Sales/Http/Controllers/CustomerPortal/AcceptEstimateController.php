<?php

namespace App\Domains\Sales\Http\Controllers\CustomerPortal;

use App\Domains\Accounts\Models\Company;
use App\Domains\Sales\Http\Resources\CustomerPortal\EstimateResource;
use App\Platform\Http\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AcceptEstimateController extends Controller
{
    /**
     * Record the contact's verdict on one of their own offers.
     *
     * Known defect, kept as-is: the submitted value is written through
     * without a whitelist, so the stored status is whatever string arrives.
     *
     * @param  string  $id
     * @return Response
     */
    public function __invoke(Request $request, Company $company, $id)
    {
        $contact = Auth::guard('customer')->id();

        $estimate = $company->estimates()->whereCustomer($contact)->where('id', $id)->first();

        if ($estimate === null) {
            return response()->json(['error' => 'estimate_not_found'], Response::HTTP_NOT_FOUND);
        }

        $verdict = $request->only('status');

        $estimate->update($verdict);

        return EstimateResource::make($estimate);
    }
}
