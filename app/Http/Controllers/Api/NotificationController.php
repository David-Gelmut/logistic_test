<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\DublicateKeyException;
use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\Users\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationController extends Controller
{
    use ApiResponse;

    public function send(NotificationRequest $request, UserService $userService, NotificationService $notificationService): JsonResponse
    {
        try {
            $validated = $request->validated();

            $notificationService->notificationsJobsDispatch($validated, $userService->getUsersWithContacts($validated));

            return $this->createResponse('accepted', 'Create notifications with request: ' . $validated['request_id'], 201);
        } catch (DublicateKeyException $exception) {

            return $this->createResponse('error', $exception->getMessage(), 409);

        } catch (\Exception $exception) {

            return $this->createResponse('error', $exception->getMessage(), 500);

        }
    }

    public function history(User $user): JsonResource
    {
        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return NotificationResource::collection($notifications);
    }
}
