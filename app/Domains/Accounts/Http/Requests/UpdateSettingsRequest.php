<?php

namespace App\Domains\Accounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The payload behind writing preferences: one map of option names to values.
 *
 * Only its presence is checked. Neither the option names nor the values are
 * constrained in any way, so anything the caller sends is upserted as-is —
 * bar the currency, which the controller guards once the books are open.
 */
class UpdateSettingsRequest extends FormRequest
{
    /**
     * Owner-only, but the gate that says so runs in the controller.
     */
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
            'settings' => [
                'required',
            ],
        ];
    }
}
