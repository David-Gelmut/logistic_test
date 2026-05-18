<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Providers\EmailProviderService;
use App\Services\Notifications\Providers\SmsProviderService;

class NotificationProviderFactory
{
    /**
     * @throws \Exception
     */
    public static function create(string $channel): NotificationProviderInterface
    {
        return match ($channel) {
            'sms' => app(SmsProviderService::class),
            'email' => app(EmailProviderService::class),
            default => throw new \Exception("Unsupported channel"),
        };
    }
}
