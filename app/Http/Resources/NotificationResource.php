<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'channel' => $this->channel,
            'contact' => $this->contact,
            'message' => $this->message,
            'status' => $this->status,
            'priority' => $this->priority,
        ];
    }
}
