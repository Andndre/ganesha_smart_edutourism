<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\UmkmProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function create(Request $request): View
    {
        $umkm = null;
        if ($request->has('umkm_profile_id')) {
            $umkm = UmkmProfile::find($request->umkm_profile_id);
        }

        return view('user.feedback.create', compact('umkm'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'nullable|string',
            'umkm_profile_id' => 'nullable|exists:umkm_profiles,id',
            'feedback_type' => 'nullable|string|in:general,cultural,service,facility,umkm',
        ]);

        $feedbackType = $validated['feedback_type'] ?? 'general';
        $umkmProfileId = $validated['umkm_profile_id'] ?? null;

        $latestCompleted = null;
        if ($feedbackType === 'general') {
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
        }

        $feedback = Feedback::create([
            'user_id' => auth()->id(),
            'reservation_id' => $latestCompleted?->id,
            'umkm_profile_id' => $umkmProfileId,
            'feedback_type' => $feedbackType,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'photos' => $validated['photos'] ?? [],
        ]);

        // Dynamically update the cached/static rating on UmkmProfile if it exists
        if ($feedbackType === 'umkm' && $umkmProfileId) {
            $umkm = UmkmProfile::find($umkmProfileId);
            if ($umkm) {
                $avgRating = Feedback::where('umkm_profile_id', $umkmProfileId)
                    ->where('feedback_type', 'umkm')
                    ->avg('rating');
                if ($avgRating) {
                    $umkm->update(['rating' => round($avgRating, 1)]);
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Terima kasih atas ulasannya!'),
                'data' => $feedback,
            ]);
        }

        return redirect()->route('feedback.thank-you', $feedback);
    }

    public function update(Request $request, Feedback $feedback): JsonResponse|RedirectResponse
    {
        $this->authorizeOwner($feedback);

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
                'message' => __('Ulasan berhasil diperbarui!'),
                'data' => $feedback,
            ]);
        }

        return redirect()->route('feedback.thank-you', $feedback);
    }

    public function show(Feedback $feedback): View
    {
        $this->authorizeOwner($feedback);

        return view('user.feedback.show', compact('feedback'));
    }

    public function edit(Feedback $feedback): View
    {
        $this->authorizeOwner($feedback);

        return view('user.feedback.edit', compact('feedback'));
    }

    public function thankYou(Feedback $feedback): View
    {
        $this->authorizeOwner($feedback);

        return view('user.feedback.thank-you', compact('feedback'));
    }

    public function index(): View
    {
        $feedbacks = Feedback::where('user_id', auth()->id())
            ->with('reservation')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.feedback.index', compact('feedbacks'));
    }

    private function authorizeOwner(Feedback $feedback): void
    {
        abort_if($feedback->user_id !== auth()->id(), 403);
    }
}
