<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'nullable|string',
        ]);

        // Find latest completed reservation without existing feedback
        $latestCompleted = Reservation::where('user_id', auth()->id())
            ->where('status', 'completed')
            ->whereDoesntHave('feedbacks')
            ->latest('scheduled_date')
            ->first();

        // Check if already has feedback for this reservation
        if ($latestCompleted && $latestCompleted->feedbacks()->exists()) {
            $existingFeedback = $latestCompleted->feedbacks()->first();

            return redirect()->route('feedback.edit', $existingFeedback);
        }

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'reservation_id' => $latestCompleted?->id,
            'feedback_type' => 'general',
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'photos' => $validated['photos'] ?? [],
            'is_public' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas ulasannya!',
                'data' => $feedback,
            ]);
        }

        return redirect()->route('feedback.thank-you', $feedback);
    }

    public function update(Request $request, Feedback $feedback): JsonResponse|RedirectResponse
    {
        if ($feedback->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'nullable|string',
        ]);

        $feedback->update([
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'photos' => $validated['photos'] ?? $feedback->photos,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ulasan berhasil diperbarui!',
                'data' => $feedback,
            ]);
        }

        return redirect()->route('feedback.thank-you', $feedback);
    }

    public function show(Feedback $feedback): View
    {
        if ($feedback->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.feedback.show', compact('feedback'));
    }

    public function edit(Feedback $feedback): View
    {
        if ($feedback->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.feedback.edit', compact('feedback'));
    }

    public function thankYou(Feedback $feedback): View
    {
        if ($feedback->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.feedback.thank-you', compact('feedback'));
    }
}
