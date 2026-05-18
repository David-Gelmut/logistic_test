<?php

namespace App\Services;

use App\Exceptions\DublicateKeyException;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    /**
     * @throws DublicateKeyException
     */
    public function notificationsJobsDispatch(array $validated, Collection $users): void
    {
        $notifications = $this->insertNotifications($validated, $users);

        foreach ($notifications as $notification) {
            SendNotificationJob::dispatch($notification);
        }
    }

    /**
     * @throws DublicateKeyException
     */
    public function insertNotifications(array $validated, Collection $users): Collection
    {
        $cacheKey = "notification_{$validated['request_id']}";
        if (!Cache::add($cacheKey, 'processing', now()->addHour())) {
            throw new DublicateKeyException('Duplicate request', 409);
        }

        return DB::transaction(function () use ($validated, $users) {

            $dataNotification = [];

            foreach ($users as $user) {

                $dataNotification[] =
                    [
                        'request_id' => $validated['request_id'],
                        'user_id' => $user->id,
                        'channel' => $validated['channel'],
                        'contact' => $user->primaryContact->value,
                        'message' => $validated['message'],
                        'priority' => $validated['priority'],
                        'status' => 'queued',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
            }

            Notification::query()->insert($dataNotification);
            return Notification::query()->where('request_id', $validated['request_id'])->get();
        });
    }
}
