<?php

namespace Tests\Unit;

use App\Exceptions\DublicateKeyException;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService();
    }

    public function it_successfully_creates_notifications_and_dispatches_jobs(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $user->contacts()->create(['type' => 'sms', 'value' => '79991112233', 'priority' => true]);

        $validated = [
            'request_id' => Str::uuid()->toString(),
            'channel' => 'sms',
            'message' => 'Hello!',
            'priority' => 'high',
        ];


        $this->service->notificationsJobsDispatch($validated, User::all());


        $this->assertDatabaseHas('notifications', [
            'request_id' => $validated['request_id'],
            'contact' => '79991112233',
            'status' => 'queued'
        ]);

        Queue::assertPushed(SendNotificationJob::class, 1);
    }

    public function it_throws_exception_on_duplicate_request_id_via_cache(): void
    {
        $requestId = Str::uuid()->toString();
        $cacheKey = "request_lock:" . $requestId;

        Cache::put($cacheKey, 'processing', 60);

        $validated = ['request_id' => $requestId];

        $this->expectException(DublicateKeyException::class);
        $this->service->insertNotifications($validated, User::all());
    }
}
