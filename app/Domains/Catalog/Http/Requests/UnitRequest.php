<?php

namespace App\Domains\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Incoming payload for creating or renaming a measurement unit.
 *
 * Besides the name rule it builds the attribute array to persist, pinned to
 * the company the request was addressed to rather than to anything submitted.
 */
class UnitRequest extends FormRequest
{
    /**
     * Access is settled by the unit policy in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * A name has to be unused inside the acting company; a different company
     * may hold the very same one.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $free = Rule::unique('units')->where('company_id', $this->header('company'));

        // Quirk kept as is: only PUT excuses the edited row from the name
        // check. A PATCH would collide with its own stored name.
        if ($this->isMethod('PUT')) {
            $free->ignore($this->route('unit'), 'id');
        }

        return [
            'name' => ['required', $free],
        ];
    }

    /**
     * The validated attributes with the acting company folded in, ready to be
     * written to a unit.
     *
     * @return array<string, mixed>
     */
    public function getUnitPayload()
    {
        return array_merge($this->validated(), [
            'company_id' => $this->header('company'),
        ]);
    }
}
