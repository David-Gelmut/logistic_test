<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function getUsersWithContacts(array $validated): Collection
    {
        return User::query()
            ->with(['primaryContact' => function ($q) use ($validated) {
                $q->select('id', 'user_id', 'type', 'value','priority')
                    ->where('type', $validated['channel'])
                    ->where('priority', true)
                    ->limit(1);
            }])
            ->whereIn('id', $validated['recipient_ids'])
            ->get();
    }
}
