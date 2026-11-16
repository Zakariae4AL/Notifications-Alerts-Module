<?php

namespace App\Http\Resources;

use App\Enums\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;

/**
 * @mixin DatabaseNotification
 */
class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = NotificationType::tryFrom($this->type);
        $payload = $this->data;

        return [
            'id' => $this->id,
            'type' => $this->type,
            'label' => $type?->label() ?? $this->type,
            'severity' => $payload['severity'] ?? $type?->severity()->value,
            // Rendered at read time so it follows the reader's language.
            'message' => $payload['custom_message'] ?? $type?->message($payload['data'] ?? []) ?? '',
            'payload' => (object) ($payload['data'] ?? []),
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
