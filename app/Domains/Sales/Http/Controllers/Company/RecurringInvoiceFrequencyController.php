<?php

namespace App\Domains\Sales\Http\Controllers\Company;

use App\Domains\Sales\Models\RecurringInvoice;
use App\Platform\Http\Controller;
use Illuminate\Http\Request;

/**
 * Previews the moment a schedule would fire for the first time.
 */
class RecurringInvoiceFrequencyController extends Controller
{
    /**
     * Read a cron expression and a start date, and answer with the first
     * firing they produce.
     *
     * The schedule form asks for this while it is still being filled in, so
     * nothing here is gated or written down — the date is worked out, handed
     * back and forgotten. A start date the parser cannot read, or an
     * expression it cannot parse, comes back as a server error rather than a
     * validation message.
     */
    public function __invoke(Request $request)
    {
        $nextRun = RecurringInvoice::getNextInvoiceDate(
            $request->input('frequency'),
            $request->input('starts_at'),
        );

        return response()->json([
            'success' => true,
            'next_invoice_at' => $nextRun,
        ]);
    }
}
