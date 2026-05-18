<?php

namespace Tests\Feature;

use App\Exceptions\CancelledProviderException;
use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Providers\EmailProviderService;
use App\Services\Notifications\Providers\SmsProviderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class FullApplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест сценария (Route -> API -> DB -> Worker -> Provider)
     */
    public function test_full_application_flow_from_route_to_provider()
    {
        Queue::fake();

        $user = User::factory()->create();
        $user->contacts()->create([
            'type' => 'sms',
            'value' => '79991112233',
            'priority' => true
        ]);

        $payload = [
            'request_id' => Str::uuid()->toString(),
            'channel' => 'sms',
            'priority' => 'high',
            'message' => 'Full flow test message',
            'recipient_ids' => [$user->id],
        ];

        $smsMock = Mockery::mock(SmsProviderService::class);
        $smsMock->shouldReceive('send')->once()->andReturn(true);
        $this->app->instance(SmsProviderService::class, $smsMock);

        ///Route -> API
        $response = $this->postJson('/api/v1/notifications', $payload);

        //API
        $response->assertStatus(201);

        //DB
        $this->assertDatabaseHas('notifications', [
            'request_id' => $payload['request_id'],
            'status' => 'queued'
        ]);

        // Проверяем, что задача попала в очередь
        Queue::assertPushed(SendNotificationJob::class, function ($job) use ($payload) {
            return $job->notification->request_id === $payload['request_id'];
        });

        // Извлекаем задачу из очереди и запускаем её вручную (имитация воркера)
        $notification = Notification::where('request_id', $payload['request_id'])->first();

        $job = new SendNotificationJob($notification);
        $job->handle();

        // Проверяем финальный результат в БД
        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'delivered'
        ]);
    }
}
