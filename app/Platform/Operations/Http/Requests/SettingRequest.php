<?php

namespace App\Platform\Operations\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload for a bulk write to the settings store.
 */
class SettingRequest extends FormRequest
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
     * The map of options to write must be present; individual option names
     * are free-form, so nothing below `settings` is constrained here.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'settings' => 'required',
        ];
    }
}
