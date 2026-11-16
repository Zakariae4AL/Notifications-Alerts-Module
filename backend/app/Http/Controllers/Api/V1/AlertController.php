<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendAlertRequest;
use App\Models\User;
use App\Services\AlertDispatcher;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    /**
     * List the alert types the API knows about (useful for building UIs).
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'data' => array_map(fn (NotificationType $type) => [
                'type' => $type->value,
                'label' => $type->label(),
                'severity' => $type->severity()->value,
                'required_data' => $type->requiredData(),
            ], NotificationType::cases()),
        ]);
    }

    /**
     * Raise an alert for one or more users (admins / dispatchers only).
     */
    public function store(SendAlertRequest $request, AlertDispatcher $dispatcher): JsonResponse
    {
        $recipients = User::whereIn('id', $request->validated('user_ids'))->get();

        $count = $dispatcher->dispatch(
            $request->notificationType(),
            $recipients,
            $request->validated('data') ?? [],
            $request->validated('message'),
        );

        return response()->json([
            'message' => __('notifications.alert_sent', ['count' => $count]),
            'recipients' => $count,
        ], 202);
    }
}
