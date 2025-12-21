<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /notifications
    public function index(Request $request)
    {
        // Dummy notifications for testing
        return response()->json([
            ['id' => 1, 'message' => 'Trip started'],
            ['id' => 2, 'message' => 'Passenger boarded'],
            ['id' => 3, 'message' => 'Trip delayed'],
        ]);
    }
}
