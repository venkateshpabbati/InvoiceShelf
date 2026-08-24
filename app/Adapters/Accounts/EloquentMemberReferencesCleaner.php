<?php

namespace App\Adapters\Accounts;

use App\Domains\Accounts\Contracts\MemberReferencesCleaner;
use App\Domains\Accounts\Models\User;

class EloquentMemberReferencesCleaner implements MemberReferencesCleaner
{
    public function clear(User $user): void
    {
        $authored = ['invoices', 'estimates', 'customers', 'recurringInvoices', 'expenses', 'payments', 'items'];
        foreach ($authored as $relation) {
            $user->{$relation}()->update(['creator_id' => null]);
        }
    }
}
