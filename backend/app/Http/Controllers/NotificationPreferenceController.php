<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function update(Request $request)
    {
        $preferences = $request->user()->notificationPreferences;
        $preferences->update($request->all());

        return response()->json(['message' => 'Preferences updated']);
    }
}
