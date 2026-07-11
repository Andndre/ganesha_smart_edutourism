<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedback and reviews.
     */
    public function index(): View
    {
        $feedbacks = Feedback::with(['user', 'umkmProfile'])->orderBy('created_at', 'desc')->paginate(10);

        $avgRating = round(Feedback::avg('rating') ?? 0, 1);

        $totalReviews = Feedback::count();

        $now = Carbon::now();
        $thisMonthReviews = Feedback::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Star distribution
        $starsDistribution = [];
        $starRatings = [5, 4, 3, 2, 1];
        foreach ($starRatings as $star) {
            $count = Feedback::where('rating', $star)->count();
            $percentage = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
            $starsDistribution[] = [$star, $percentage];
        }

        return view('admin.feedback.index', compact('feedbacks', 'avgRating', 'totalReviews', 'thisMonthReviews', 'starsDistribution'));
    }

    /**
     * Reply to the specified feedback.
     */
    public function reply(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'admin_response' => ['required', 'string'],
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'admin_response' => $validated['admin_response'],
        ]);

        return redirect()->route('admin.feedback')->with('success', __('Balasan ulasan berhasil disimpan.'));
    }

    /**
     * Remove the specified feedback from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return redirect()->route('admin.feedback')->with('success', __('Ulasan berhasil dihapus.'));
    }
}
