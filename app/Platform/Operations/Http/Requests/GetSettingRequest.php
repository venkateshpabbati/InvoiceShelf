<?php

namespace App\Platform\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters for a single-option read from the settings store.
 */
class GetSettingRequest extends FormRequest
{
    /**
     * Access is decided by the `manage settings` ability in the controller,
     * so the request itself lets everything through.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The option name to look up. It is echoed back as the response key, so
     * it has to be a scalar string.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'key' => 'required|string',
        ];
    }
}
