<?php

namespace App\Jobs;

use App\Enums\NotificationStatusEnum;
use App\Exceptions\CancelledProviderException;
use App\Models\Notification;
use App\Services\Notifications\NotificationProviderFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(public Notification $notification)
    {
        $this->onQueue($this->notification->priority);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            $this->notification->refresh();

            if ($this->notification->status === NotificationStatusEnum::DELIVERED->value) return;

            $this->notification->increment('retry_count');

            $provider = NotificationProviderFactory::create($this->notification->channel);

            $this->notification->update(['status' => NotificationStatusEnum::SENT->value]);

            if ($provider->send($this->notification)) {

                $this->notification->update(['status' => NotificationStatusEnum::DELIVERED->value]);
            }
        } catch (CancelledProviderException $e) {

            $this->notification->update([
                'error_details' => $e->getMessage(),
                'status' => NotificationStatusEnum::CANCELED->value
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        if ($exception instanceof ModelNotFoundException) {
            \Log::error("Уведомление {$this->notification->id} было удалено до обработки.");
        }
        $this->notification->update([
            'status' => NotificationStatusEnum::FAILED->value,
            'error_details' => $exception->getMessage()
        ]);
    }
}
