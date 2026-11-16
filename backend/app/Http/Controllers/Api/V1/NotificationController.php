<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * The authenticated user's inbox. Every query goes through
 * $request->user()->notifications(), so a user can never
 * read or modify somebody else's notifications.
 */
class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'status' => ['sometimes', Rule::in(['all', 'read', 'unread'])],
            'type' => ['sometimes', Rule::enum(NotificationType::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $notifications = $request->user()->notifications()
            ->when(($filters['status'] ?? 'all') === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when(($filters['status'] ?? 'all') === 'read', fn ($q) => $q->whereNotNull('read_at'))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();

        return NotificationResource::collection($notifications)->additional([
            'meta' => ['unread_count' => $request->user()->unreadNotifications()->count()],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id): NotificationResource
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    public function markAsUnread(Request $request, string $id): NotificationResource
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsUnread();

        return new NotificationResource($notification);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $updated = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['updated' => $updated]);
    }

    public function destroy(Request $request, string $id): Response
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return response()->noContent();
    }
}
