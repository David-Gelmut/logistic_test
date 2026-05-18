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

class NotificationWorkerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест всей цепочки: Job -> Provider Mock -> Database Status Update
     */
    public function test_full_worker_flow_updates_status_to_delivered()
    {
        $notification = Notification::create([
            'request_id'   => Str::uuid(),
            'user_id' => 'user_1',
            'contact'      => '79990001122',
            'channel'      => 'sms',
            'message'      => 'Test worker message',
            'priority'     => 'high',
            'status'       => 'queued',
        ]);


        $smsProviderMock = Mockery::mock(SmsProviderService::class);
        $smsProviderMock->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function ($argument) use ($notification) {
                return $argument->id === $notification->id;
            }))
            ->andReturn(true);

        $this->app->instance(SmsProviderService::class, $smsProviderMock);

        $job = new SendNotificationJob($notification);
        $job->handle();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'delivered',
        ]);


        $this->assertNull($notification->fresh()->error_details);
    }

    /**
     * Тест сценария ошибки: Job -> Failed Provider -> Retry Logic
     */
    public function test_worker_flow_records_error_on_failure()
    {
        $notification = Notification::query()
            ->create([
            'request_id'   => Str::uuid()->toString(),
            'user_id' => 'user_2',
            'contact'      => 'error@test.com',
            'channel'      => 'email',
            'message'      => 'Test error message',
            'priority'     => 'low',
            'status'       => 'queued',
        ]);


        $emailProviderMock = Mockery::mock(EmailProviderService::class);
        $emailProviderMock->shouldReceive('send')
            ->andThrow(app(CancelledProviderException::class,  [ 'message' => 'Gateway Timeout']));

        $this->app->instance(EmailProviderService::class, $emailProviderMock);


        $job = new SendNotificationJob($notification);

        try {
            $job->handle();
        } catch (CancelledProviderException $e) {

            $this->assertEquals("Gateway Timeout", $e->getMessage());
        }

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'error_details' => 'Gateway Timeout',
        ]);
    }

}
