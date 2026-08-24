<?php

namespace App\Domains\Accounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Payload rules for defining or renaming a company role.
 *
 * Role names are unique per company rather than per install, so the uniqueness
 * check is narrowed by hand to the scope named in the `company` header. A
 * rename excuses the role from its own name, but only when the verb is PUT --
 * kept as is, an otherwise identical PATCH collides with the stored name.
 */
class RoleRequest extends FormRequest
{
    /**
     * Access is settled by the role policy in the controller, not here.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * What a role submission has to satisfy before it is written.
     */
    public function rules(): array
    {
        $name = Rule::unique('roles')->where('scope', $this->header('company'));

        if ($this->getMethod() === 'PUT') {
            $name->ignore($this->route('role')->id, 'id');
        }

        return [
            'name' => ['required', 'string', $name],
            'abilities' => ['required'],
            'abilities.*' => ['required'],
        ];
    }

    /**
     * The submitted attributes, minus the abilities, stamped with the scope.
     *
     * Everything the caller sent survives the trip; the role model's own
     * fillable list decides what is actually written.
     */
    public function getRolePayload()
    {
        $attributes = $this->except('abilities');

        $attributes['scope'] = $this->header('company');

        return $attributes;
    }
}
