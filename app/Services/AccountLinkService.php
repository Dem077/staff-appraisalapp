<?php

namespace App\Services;

use App\Models\User;

class AccountLinkService
{
    public static function linkUserToStaff(User $user, ?int $staffId): void
    {
        if ($staffId) {
            User::query()
                ->where('staff_id', $staffId)
                ->where('id', '!=', $user->id)
                ->update(['staff_id' => null]);
        }

        $user->update(['staff_id' => $staffId]);
    }
}
