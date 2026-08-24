<?php

namespace App\Domains\Accounts\Policies;

use App\Domains\Accounts\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Silber\Bouncer\Database\Role;

/**
 * Who may work with per-company roles.
 *
 * One question answers all seven entries: does the actor own the company named
 * in the `company` header? There is no second half here. Where a role is
 * handed in it is never looked at, so the role's own scope plays no part in
 * the decision — confining a role to its company is left to the scoping that
 * Bouncer applies while the query runs, not to this class.
 */
class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Browsing the roles of the active company.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOwner();
    }

    /**
     * Reading one role. The role itself is not examined.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->isOwner();
    }

    /**
     * Defining a role.
     */
    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    /**
     * Renaming a role or resyncing its abilities.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->isOwner();
    }

    /**
     * Dropping a role. Whether anybody still holds it is settled downstream,
     * not here.
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->isOwner();
    }

    /**
     * Bringing a role back — unreachable, as roles are not soft-deleted.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->isOwner();
    }

    /**
     * Erasing a role for good — unreachable for the same reason.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->isOwner();
    }
}
