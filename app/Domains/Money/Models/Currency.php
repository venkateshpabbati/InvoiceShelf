<?php

namespace App\Domains\Money\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Global currency reference data.
 *
 * Rows are seeded at install time and are shared by every company — there is
 * no CRUD surface for them. An `exchange_rate` is not stored here; callers
 * that resolve one hang it on the instance before it is serialised.
 */
class Currency extends Model
{
    use HasFactory;

    /**
     * Codes pinned to the front of the currency listing, in this order.
     */
    public const COMMON_CURRENCY_CODES = [
        'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'BRL',
    ];

    protected $table = 'currencies';

    protected $guarded = [
        'id',
    ];
}
