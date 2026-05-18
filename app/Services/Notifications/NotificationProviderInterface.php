<?php

namespace App\Services\Notifications;

use App\Models\Notification;

interface NotificationProviderInterface
{
    public function send(Notification $notification): bool;
}
