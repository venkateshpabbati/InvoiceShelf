<?php

namespace App\Domains\Money\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload for the one-shot historical exchange-rate backfill: a list of
 * {id, exchange_rate} pairs. Access is gated by the company setting, not here.
 */
class BulkExchangeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'currencies' => ['required'],
            'currencies.*.id' => ['required', 'numeric'],
            'currencies.*.exchange_rate' => ['required'],
        ];
    }
}
