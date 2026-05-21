<?php

namespace App\Http\Controllers;

use App\Models\UserLearningProgress;
use App\Models\LearningModule;
use App\Models\LearningContent;
use App\Models\LearningQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LearningProgressController extends Controller
{
    /**
     * Record quiz answer submission via AJAX.
     */
    public function submitQuiz(Request $request, string $moduleSlug, string $contentSlug)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $validator = Validator::make($request->all(), [
            'selected_option' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $module = LearningModule::where('slug', $moduleSlug)->firstOrFail();
        $content = LearningContent::where('slug', $contentSlug)
            ->where('learning_module_id', $module->id)
            ->firstOrFail();
        $quiz = $content->quiz;
        if (! $quiz) {
            return response()->json(['error' => 'No quiz for this content'], Response::HTTP_BAD_REQUEST);
        }

        $isCorrect = $quiz->correct_option === $request->input('selected_option');

        // Create or update progress record
        $progress = UserLearningProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'learning_module_id' => $module->id,
                'learning_content_id' => $content->id,
            ],
            [
                'completed' => true,
                'score' => $isCorrect ? 1 : 0,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'correct' => $isCorrect,
            'message' => $isCorrect ? 'Correct answer!' : 'Incorrect answer.',
        ]);
    }

    /**
     * Retrieve the authenticated user's learning progress.
     */
    public function index()
    {
        $user = Auth::user();
        $progress = UserLearningProgress::where('user_id', $user->id)
            ->with(['learningModule', 'learningContent'])
            ->get();
        return response()->json($progress);
    }
}
