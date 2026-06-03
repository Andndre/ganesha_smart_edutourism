<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SmartEdutourismController extends Controller
{
    public function index(): View
    {
        $routes = TourRoute::where('is_active', true)
            ->withCount('routePoints')
            ->get();

        return view('user.edutourism.index', compact('routes'));
    }

    public function preview($id)
    {
        $route = TourRoute::with(['routePoints.locationable.mapLocation'])->findOrFail($id);

        $points = $route->routePoints->map(function ($point) {
            return [
                'id' => $point->id,
                'name' => $point->locationable->name ?? 'Titik Perhentian',
                'lat' => $point->locationable->mapLocation->latitude ?? null,
                'lng' => $point->locationable->mapLocation->longitude ?? null,
            ];
        });

        return response()->json([
            'route' => $route,
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
            session(['guest_token' => $guestToken, 'guest_name' => 'Wisatawan']);
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
            session(['guest_token' => $guestToken, 'guest_name' => 'Wisatawan']);
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
            return redirect()->route('home')->with('info', 'Anda tidak memiliki rute aktif saat ini.');
        }

        return view('user.edutourism.active', compact('activeSession'));
    }

    public function arrive(Request $request, $pointId)
    {
        $point = TourRoutePoint::with('locationable')->findOrFail($pointId);
        // Check for quizzes (now global on CulturalObject)
        $quizzes = [];
        if ($point->locationable instanceof CulturalObject) {
            $quizzes = $point->locationable->quizzes;
        }

        if (count($quizzes) === 0) {
            $userId = auth()->id();
            $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

            if (! $userId && $guestToken && ! session()->has('guest_token')) {
                session(['guest_token' => $guestToken, 'guest_name' => 'Wisatawan']);
            }

            $sessionQuery = RouteSession::where('status', 'active');
            if ($userId) {
                $sessionQuery->where('user_id', $userId);
            } elseif ($guestToken) {
                $sessionQuery->where('guest_token', $guestToken);
            } else {
                $sessionQuery = null;
            }

            if ($sessionQuery) {
                $session = $sessionQuery->first();
                if ($session && $session->current_point_id == $pointId) {
                    $session->points_completed += 1;

                    $route = TourRoute::with('routePoints')->find($session->tour_route_id);
                    $points = $route->routePoints;
                    $currentIndex = $points->search(function ($p) use ($session) {
                        return $p->id == $session->current_point_id;
                    });

                    if ($currentIndex !== false && isset($points[$currentIndex + 1])) {
                        $session->current_point_id = $points[$currentIndex + 1]->id;
                    } else {
                        $session->status = 'completed';
                        $session->current_point_id = null;
                    }

                    $session->save();
                    $sessionStatus = $session->status;
                }
            }
        }

        return response()->json([
            'success' => true,
            'point' => $point,
            'quizzes' => $quizzes,
            'session_status' => $sessionStatus ?? 'active',
        ]);
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
            session(['guest_token' => $guestToken, 'guest_name' => 'Wisatawan']);
        }

        $sessionQuery = RouteSession::where('status', 'active');
        if ($userId) {
            $sessionQuery->where('user_id', $userId);
        } elseif ($guestToken) {
            $sessionQuery->where('guest_token', $guestToken);
        } else {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid'], 403);
        }

        $session = $sessionQuery->first();
        if (! $session) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak ditemukan'], 404);
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
}
