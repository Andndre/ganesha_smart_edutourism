<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'reservation_id' => null,
            'feedback_type' => 'general',
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_public' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasannya!',
            'data' => $feedback,
        ]);
    }
}
