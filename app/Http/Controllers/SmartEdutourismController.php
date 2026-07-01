<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UserVisit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SmartEdutourismController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $routes = Cache::tags(['edutourism'])->flexible("edutourism_routes_array_{$locale}", [86400, 172800], function () {
            $models = TourRoute::where('is_active', true)
                ->withCount('routePoints')
                ->get();

            return $models->map(function ($model) {
                $data = $model->toArray();
                $locale = app()->getLocale();
                foreach (['name', 'description'] as $field) {
                    if (isset($data[$field]) && \is_array($data[$field])) {
                        $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
                    }
                }

                return $data;
            })->values()->toArray();
        });

        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        $completedRouteIds = collect();

        if ($userId) {
            $completedRouteIds = RouteSession::where('user_id', $userId)
                ->where('status', 'completed')
                ->pluck('tour_route_id');
        } elseif ($guestToken) {
            $completedRouteIds = RouteSession::where('guest_token', $guestToken)
                ->where('status', 'completed')
                ->pluck('tour_route_id');
        }

        return view('user.edutourism.index', compact('routes', 'completedRouteIds'));
    }

    public function preview($id)
    {
        $locale = app()->getLocale();
        $route = TourRoute::with(['routePoints.locationable.mapLocation'])->findOrFail($id);

        $routeData = $route->toArray();
        foreach (['name', 'description'] as $field) {
            if (isset($routeData[$field]) && \is_array($routeData[$field])) {
                $routeData[$field] = $routeData[$field][$locale] ?? $routeData[$field][config('app.fallback_locale')] ?? reset($routeData[$field]) ?? '';
            }
        }

        $points = $route->routePoints->map(function ($point) use ($locale) {
            $loc = $point->locationable;
            $name = $loc->name ?? __('Titik Perhentian');
            if (\is_array($name)) {
                $name = $name[$locale] ?? $name[config('app.fallback_locale')] ?? reset($name) ?? '';
            }

            return [
                'id' => $point->id,
                'name' => $name,
                'lat' => $loc->mapLocation->latitude ?? null,
                'lng' => $loc->mapLocation->longitude ?? null,
            ];
        });

        return response()->json([
            'route' => $routeData,
            'points' => $points,
        ]);
    }

    public function start(Request $request, $id)
    {
        $route = TourRoute::with('routePoints')->findOrFail($id);
        $firstPoint = $route->routePoints->first();

        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        if (! $userId && ! $guestToken) {
            $guestToken = 'visitor_'.Str::random(32);
            session(['guest_token' => $guestToken, 'guest_name' => __('Wisatawan')]);
        }

        if ($userId) {
            RouteSession::where('user_id', $userId)->where('status', 'active')->update(['status' => 'abandoned']);
            $session = RouteSession::create([
                'user_id' => $userId,
                'tour_route_id' => $route->id,
                'current_point_id' => $firstPoint ? $firstPoint->id : null,
                'status' => 'active',
            ]);
        } else {
            RouteSession::where('guest_token', $guestToken)->where('status', 'active')->update(['status' => 'abandoned']);
            $session = RouteSession::create([
                'guest_token' => $guestToken,
                'tour_route_id' => $route->id,
                'current_point_id' => $firstPoint ? $firstPoint->id : null,
                'status' => 'active',
            ]);
        }

        $response = response()->json(['success' => true, 'redirect' => route('edutourism.active')]);

        if (! $userId) {
            $response->withCookie(cookie()->forever('visitor_token', $guestToken));
        }

        return $response;
    }

    public function active(Request $request)
    {
        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        if (! $userId && $guestToken && ! session()->has('guest_token')) {
            session(['guest_token' => $guestToken, 'guest_name' => __('Wisatawan')]);
        }

        $sessionQuery = RouteSession::with(['tourRoute', 'currentPoint.locationable.mapLocation', 'tourRoute.routePoints.locationable.mapLocation'])
            ->whereIn('status', ['active', 'completed'])
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc');

        if ($userId) {
            $sessionQuery->where('user_id', $userId);
        } elseif ($guestToken) {
            $sessionQuery->where('guest_token', $guestToken);
        } else {
            return redirect()->route('home');
        }

        $activeSession = $sessionQuery->first();

        if (! $activeSession) {
            return redirect()->route('home')->with('info', __('Anda tidak memiliki rute aktif saat ini.'));
        }

        return view('user.edutourism.active', compact('activeSession'));
    }

    public function arrive(Request $request, $pointId)
    {
        $point = TourRoutePoint::with('locationable')->findOrFail($pointId);
        /** @var Collection $quizzes */
        $quizzes = $point->locationable instanceof CulturalObject
            ? $point->locationable->quizzes
            : collect();

        $quizzesData = $quizzes->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'option_a' => $q->option_a,
            'option_b' => $q->option_b,
            'option_c' => $q->option_c,
            'option_d' => $q->option_d,
        ])->values();

        $sessionStatus = 'active';

        if ($quizzes->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'point' => $point,
                'quizzes' => $quizzesData,
                'session_status' => $sessionStatus,
            ]);
        }

        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        if (! $userId && $guestToken && ! session()->has('guest_token')) {
            session(['guest_token' => $guestToken, 'guest_name' => __('Wisatawan')]);
        }

        $session = $this->findActiveSession($userId, $guestToken);

        if (! $session || $session->current_point_id != $pointId) {
            return response()->json([
                'success' => true,
                'point' => $point,
                'quizzes' => $quizzesData,
                'session_status' => $sessionStatus,
            ]);
        }

        $session->points_completed += 1;

        $route = TourRoute::with('routePoints')->find($session->tour_route_id);
        $points = $route->routePoints;
        $currentIndex = $points->search(fn ($p) => $p->id == $session->current_point_id);

        if ($currentIndex !== false && isset($points[$currentIndex + 1])) {
            $session->current_point_id = $points[$currentIndex + 1]->id;
        } else {
            $session->status = 'completed';
            $session->current_point_id = null;
        }

        $session->save();
        $sessionStatus = $session->status;

        $this->recordVisit($userId, $point, $session);

        return response()->json([
            'success' => true,
            'point' => $point,
            'quizzes' => $quizzesData,
            'session_status' => $sessionStatus,
        ]);
    }

    private function findActiveSession(?int $userId, ?string $guestToken): ?RouteSession
    {
        if ($userId) {
            return RouteSession::where('status', 'active')
                ->where('user_id', $userId)
                ->first();
        }

        if ($guestToken) {
            return RouteSession::where('status', 'active')
                ->where('guest_token', $guestToken)
                ->first();
        }

        return null;
    }

    private function recordVisit(?int $userId, TourRoutePoint $point, RouteSession $session): void
    {
        if (! $userId || ! $point->locationable) {
            return;
        }

        $locationable = $point->locationable;
        $alreadyVisited = UserVisit::where('user_id', $userId)
            ->where('visitable_type', $locationable->getMorphClass())
            ->where('visitable_id', $locationable->id)
            ->where('route_session_id', $session->id)
            ->exists();

        if (! $alreadyVisited) {
            UserVisit::create([
                'user_id' => $userId,
                'visitable_type' => $locationable->getMorphClass(),
                'visitable_id' => $locationable->id,
                'route_session_id' => $session->id,
                'visited_at' => now(),
            ]);
        }
    }

    public function submitQuiz(Request $request, $quizId)
    {
        $request->validate([
            'answer' => 'required|string|size:1',
            'is_last_quiz' => 'nullable|boolean',
        ]);

        $quiz = CulturalObjectQuiz::findOrFail($quizId);
        $isCorrect = strtoupper($request->answer) === strtoupper($quiz->correct_option);

        // Update session
        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        if (! $userId && $guestToken && ! session()->has('guest_token')) {
            session(['guest_token' => $guestToken, 'guest_name' => __('Wisatawan')]);
        }

        $sessionQuery = RouteSession::where('status', 'active');
        if ($userId) {
            $sessionQuery->where('user_id', $userId);
        } elseif ($guestToken) {
            $sessionQuery->where('guest_token', $guestToken);
        } else {
            return response()->json(['success' => false, 'message' => __('Sesi tidak valid')], 403);
        }

        $session = $sessionQuery->first();
        if (! $session) {
            return response()->json(['success' => false, 'message' => __('Sesi tidak ditemukan')], 404);
        }

        if ($isCorrect) {
            $session->total_score += 100;

            if ($request->boolean('is_last_quiz', true)) {
                $session->points_completed += 1;

                // Move to next point
                $route = TourRoute::with('routePoints')->find($session->tour_route_id);
                $points = $route->routePoints;
                $currentIndex = $points->search(function ($p) use ($session) {
                    return $p->id === $session->current_point_id;
                });

                if ($currentIndex !== false && isset($points[$currentIndex + 1])) {
                    $session->current_point_id = $points[$currentIndex + 1]->id;
                } else {
                    $session->status = 'completed';
                    $session->current_point_id = null;
                }
            }

            $session->save();
        }

        return response()->json([
            'success' => true,
            'is_correct' => $isCorrect,
            'session' => $session,
        ]);
    }

    public function stop(Request $request)
    {
        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        $session = $this->findActiveSession($userId, $guestToken);

        if ($session) {
            $session->update(['status' => 'abandoned']);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('edutourism.index'),
        ]);
    }
}
