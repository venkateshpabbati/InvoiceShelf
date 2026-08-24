<?php

namespace App\Domains\Accounts\Http\Requests;

use App\Rules\IdnEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the staff-account form, which serves both filing a new member and
 * editing one that already exists.
 *
 * The two verbs part company in exactly two places: on an edit the uniqueness
 * check steps over the row being edited, and the password turns optional so a
 * form saved with the field left alone keeps the hash already on file.
 *
 * The address is unique across the whole installation rather than within the
 * company, so a collision tells one tenant that an address is already spoken
 * for somewhere else entirely. Kept as it stands.
 */
class MemberRequest extends FormRequest
{
    /** The columns copied out of the payload onto the account row. */
    private const ACCOUNT_FIELDS = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * Nothing is decided at this layer; the member policy runs in the
     * controller, so every caller that got this far is let through.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The membership list is only checked for shape — each entry has to name a
     * company and a role, but neither is looked up here.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $editing = $this->getMethod() == 'PUT';

        $address = Rule::unique('users');

        if ($editing) {
            $address->ignore($this->member);
        }

        return [
            'name' => ['required'],
            'email' => ['required', new IdnEmail, $address],
            'phone' => ['nullable'],
            'password' => $editing ? ['nullable', 'min:8'] : ['required', 'min:8'],
            'companies' => ['required'],
            'companies.*.id' => ['required'],
            'companies.*.role' => ['required'],
        ];
    }

    /**
     * The account row on its own, stamped with whoever is filing it.
     *
     * On an edit the stamp is written again, so the column records the last
     * person to save the form rather than the one who opened the account.
     *
     * @return array<string, mixed>
     */
    public function getUserPayload()
    {
        return collect($this->validated())
            ->only(self::ACCOUNT_FIELDS)
            ->merge(['creator_id' => $this->user()->id])
            ->toArray();
    }
}
