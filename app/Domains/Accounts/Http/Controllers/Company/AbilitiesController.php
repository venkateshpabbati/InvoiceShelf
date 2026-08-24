<?php

namespace App\Domains\Accounts\Http\Controllers\Company;

use App\Platform\Http\Controller;
use Illuminate\Http\Request;

/**
 * Publishes the catalog of abilities a role can be granted.
 *
 * The catalog is configuration rather than data: the same fixed list for every
 * company and every caller, which is why nothing here is scoped or filtered.
 * The role editor draws its checkboxes from it, so the entries travel exactly
 * as declared -- same order, subjects and dependencies included.
 */
class AbilitiesController extends Controller
{
    /**
     * The whole catalog, straight out of configuration.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'abilities' => config('abilities.abilities'),
        ]);
    }
}
