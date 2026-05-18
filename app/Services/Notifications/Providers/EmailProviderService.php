<?php

namespace App\Services\Notifications\Providers;

use App\Exceptions\CancelledProviderException;
use App\Models\Notification;
use App\Services\Notifications\NotificationProviderInterface;

class EmailProviderService implements NotificationProviderInterface
{

    public function send(Notification $notification): bool
    {
        if (rand(1, 10) === 1) throw new CancelledProviderException("Gateway Timeout");

        \Log::info("Email sent to {$notification->contact}: {$notification->message}");
        return true;
    }
}
