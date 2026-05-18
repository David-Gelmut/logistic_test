<?php

namespace App\Console\Commands;

use App\Enums\NotificationStatusEnum;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use Illuminate\Console\Command;

class NotificationRetryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification-retry-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Notification::query()
            ->where('status', NotificationStatusEnum::FAILED)
            ->where('retry_count', '<', 100)
            ->get()
            ->each(function ($notification) {
                SendNotificationJob::dispatch($notification);
            });
    }
}
