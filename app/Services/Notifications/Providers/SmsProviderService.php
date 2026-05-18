<?php

namespace App\Services\Notifications\Providers;

use App\Exceptions\CancelledProviderException;
use App\Models\Notification;
use App\Services\Notifications\NotificationProviderInterface;

class SmsProviderService implements NotificationProviderInterface
{

    /**
     * @throws \Exception
     */
    public function send(Notification $notification): bool
    {
        //throw new CancelledProviderException("Gateway Timeout");
        if (rand(1, 10) === 1) throw new CancelledProviderException("Gateway Timeout");

        \Log::info("SMS sent to {$notification->contact}: {$notification->message}");
        return true;
    }
}
