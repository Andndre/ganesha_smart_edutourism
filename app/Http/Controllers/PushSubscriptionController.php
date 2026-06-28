<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'       => 'required|url',
            'keys.auth'      => 'required|string',
            'keys.p256dh'    => 'required|string',
        ]);

        auth()->user()->updatePushSubscription(
            $request->endpoint,
            $request->input('keys.p256dh'),
            $request->input('keys.auth'),
        );

        return response()->json(['success' => true]);
    }
}
