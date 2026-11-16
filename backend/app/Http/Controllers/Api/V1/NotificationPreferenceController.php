<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferencesRequest;
use App\Services\PreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function __construct(private readonly PreferenceService $preferences) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->preferences->matrix($request->user())]);
    }

    public function update(UpdatePreferencesRequest $request): JsonResponse
    {
        $this->preferences->update($request->user(), $request->validated('preferences'));

        return response()->json([
            'message' => __('notifications.preferences_saved'),
            'data' => $this->preferences->matrix($request->user()),
        ]);
    }
}
