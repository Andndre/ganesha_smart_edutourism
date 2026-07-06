<?php

namespace App\Http\Controllers;

use App\Models\RouteMission;
use App\Models\RouteSession;
use App\Models\TourRoute;
use App\Models\TourRoutePoint;
use App\Models\UserVisit;
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
            'avatar_options' => $this->avatarOptionsForRoute($route),
        ]);
    }

    /**
     * Single source of truth for route-type detection, reused by the avatar picker,
     * badge finalization, and per-point collectible logic. Reads the stable
     * gamification_key column (set by seeder / admin form), NOT the editable name —
     * so renaming a route in admin can't silently break its rewards.
     */
    private function routeTypeKey(TourRoute $route): ?string
    {
        return $route->gamification_key;
    }

    /**
     * Avatar picker shown in the route preview modal before starting (replaces the PDF's
     * "Spin the Wheel" with a tap-picker — a RouteMission on point 1 would disable the
     * quiz flow there, see arrive()). Purely cosmetic: score/badges never depend on it.
     */
    private function avatarOptionsForRoute(TourRoute $route): array
    {
        return match ($this->routeTypeKey($route)) {
            'cultural_adventure' => [
                ['key' => 'explorer', 'label' => __('Penjelajah'), 'icon' => '🧭'],
                ['key' => 'archaeologist', 'label' => __('Arkeolog'), 'icon' => '🏺'],
                ['key' => 'elder', 'label' => __('Tetua Desa'), 'icon' => '🧓'],
                ['key' => 'forest_keeper', 'label' => __('Penjaga Hutan'), 'icon' => '🌲'],
            ],
            'eco_quest' => [
                ['key' => 'eco_ranger', 'label' => __('Eco Ranger'), 'icon' => '🌿'],
                ['key' => 'forest_guardian', 'label' => __('Forest Guardian'), 'icon' => '🌳'],
                ['key' => 'bamboo_scientist', 'label' => __('Bamboo Scientist'), 'icon' => '🔬'],
                ['key' => 'village_explorer', 'label' => __('Village Explorer'), 'icon' => '🧭'],
            ],
            default => [],
        };
    }

    public function start(Request $request, $id)
    {
        $route = TourRoute::with('routePoints')->findOrFail($id);
        $firstPoint = $route->routePoints->first();

        $request->validate(['avatar' => 'nullable|string|max:32']);
        $avatarKeys = collect($this->avatarOptionsForRoute($route))->pluck('key');
        $avatar = $avatarKeys->contains($request->string('avatar')) ? $request->string('avatar')->toString() : null;

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
                'selected_avatar' => $avatar,
                'status' => 'active',
            ]);
        } else {
            RouteSession::where('guest_token', $guestToken)->where('status', 'active')->update(['status' => 'abandoned']);
            $session = RouteSession::create([
                'guest_token' => $guestToken,
                'tour_route_id' => $route->id,
                'current_point_id' => $firstPoint ? $firstPoint->id : null,
                'selected_avatar' => $avatar,
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

        $sessionQuery = RouteSession::with(['tourRoute', 'currentPoint.locationable.mapLocation', 'currentPoint.missions', 'tourRoute.routePoints.locationable.mapLocation', 'tourRoute.routePoints.missions'])
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
        $point = TourRoutePoint::with(['locationable', 'missions'])->findOrFail($pointId);

        $sessionStatus = 'active';

        // Points with missions: unlock only — advancement happens in completeMission().
        if ($point->missions->isNotEmpty()) {
            return response()->json([
                'success' => true,
                'point' => $point,
                'has_missions' => true,
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
                'session_status' => $sessionStatus,
            ]);
        }

        $session->points_completed += 1;
        $this->advanceToNextPoint($session);

        $session->save();
        $sessionStatus = $session->status;

        $this->recordVisit($userId, $point, $session);

        return response()->json([
            'success' => true,
            'point' => $point,
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

    /**
     * Advance the session to the next route point, or mark it completed
     * when the current point is the last one. Does not save.
     */
    private function advanceToNextPoint(RouteSession $session): void
    {
        $route = TourRoute::with('routePoints')->find($session->tour_route_id);
        $points = $route->routePoints;
        $currentIndex = $points->search(fn ($p) => $p->id == $session->current_point_id);

        if ($currentIndex !== false && isset($points[$currentIndex + 1])) {
            $session->current_point_id = $points[$currentIndex + 1]->id;
        } else {
            $session->status = 'completed';
            $session->current_point_id = null;
            $this->finalizeSession($session, $route);
        }
    }

    /**
     * Award the final badge when a session completes. Only 3 fixed routes exist,
     * so this is a simple per-route match — no rules engine until a 4th route appears.
     */
    private function finalizeSession(RouteSession $session, TourRoute $route): void
    {
        $routeType = $this->routeTypeKey($route);

        if ($routeType === 'heritage_quest') {
            // Final team decision: relative rule — badge goes to the highest
            // completed score recorded so far for this route (ties win).
            $bestSoFar = RouteSession::where('tour_route_id', $session->tour_route_id)
                ->where('status', 'completed')
                ->where('id', '!=', $session->id)
                ->max('total_score') ?? 0;

            if ($session->total_score >= $bestSoFar) {
                $session->badge_awarded = 'Penglipuran Heritage Explorer';
            }

            return;
        }

        if ($routeType === 'cultural_adventure') {
            // PDF lists 3 tiers ("Cultural Guardian" / "Tradition Master" / "Heritage
            // Champion") without a rule to distinguish them — assumption: % of the
            // route's max possible score (quiz + all mission points).
            $max = $this->maxPossibleScore($route);
            $ratio = $max > 0 ? $session->total_score / $max : 0;

            $session->badge_awarded = match (true) {
                $ratio >= 0.9 => 'Heritage Champion',
                $ratio >= 0.7 => 'Tradition Master',
                default => 'Cultural Guardian',
            };

            return;
        }

        if ($routeType === 'eco_quest') {
            // Single predicate: PDF requires collecting "all" Eco Crystals. We seed 5
            // real points (PDF says "enam" but only lists 5 — treated as a PDF typo).
            $collected = $session->collectibles_earned ?? [];
            $hasAllCrystals = collect(range(1, 5))->every(fn ($n) => \in_array("eco_crystal_{$n}", $collected));

            if ($hasAllCrystals) {
                $session->badge_awarded = 'Eco Guardian of Penglipuran';
            }
        }
    }

    /**
     * Sum of every point's RouteMission point cap (the quiz at point 1 is now just
     * another RouteMission, so its 500-point cap is already included here).
     */
    private function maxPossibleScore(TourRoute $route): int
    {
        $route->loadMissing(['routePoints.missions']);

        return $route->routePoints->flatMap->missions->sum('points');
    }

    /**
     * The point's quiz mission, if any — used to gate the point-1 collectibles.
     */
    private function quizMission(TourRoutePoint $point): ?RouteMission
    {
        return $point->missions->firstWhere('type', 'quiz');
    }

    /**
     * Shared gate for both "digital_passport" and the sequential Route 2/3 point-1
     * collectibles: true when there's no quiz mission to grade, or the session's score
     * on it reaches >= $ratio of its point cap. Does not block progression on its own.
     *
     * ponytail: relies on the quiz mission being scored before this is called (only
     * invoked from completeMission()'s last-mission branch), so total_score already
     * reflects it — no separate per-answer tally needed like the old quiz_answers table.
     */
    private function firstPointQuizThresholdMet(RouteSession $session, TourRoutePoint $point, float $ratio = 0.8): bool
    {
        $quizMission = $this->quizMission($point);

        if (! $quizMission) {
            return false;
        }

        return $session->total_score >= (int) ceil($ratio * $quizMission->points);
    }

    /**
     * "Digital Passport" collectible (Route 1 Mission 1): awarded when >= 80% of the
     * first point's quizzes are answered correctly. Does not block progression
     * (existing behavior advances regardless of correctness). Route 1 only — Route
     * 2/3 get their own point-1 collectible from awardSequentialCollectible().
     */
    private function maybeAwardFirstPointCollectible(RouteSession $session, TourRoutePoint $point): void
    {
        $route = TourRoute::find($session->tour_route_id);

        if (! $route || $this->routeTypeKey($route) !== 'heritage_quest') {
            return;
        }

        $firstPoint = TourRoutePoint::where('tour_route_id', $session->tour_route_id)
            ->orderBy('order')
            ->first();

        if (! $firstPoint || $firstPoint->id !== $point->id) {
            return;
        }

        if ($this->firstPointQuizThresholdMet($session, $point)) {
            $session->awardCollectible('digital_passport');
        }
    }

    /**
     * Route 2/3 per-point collectible ("heritage_key_N" / "eco_crystal_N"), awarded when
     * a point completes. No-op for Route 1. Point 1's quiz mission keeps the same
     * 80%-correct gate as "digital_passport"; mission-based points 2-5 award
     * unconditionally on completion, matching existing progression behavior.
     */
    private function awardSequentialCollectible(RouteSession $session, TourRoutePoint $point): void
    {
        $route = TourRoute::with('routePoints')->find($session->tour_route_id);

        $prefix = match ($this->routeTypeKey($route)) {
            'cultural_adventure' => 'heritage_key',
            'eco_quest' => 'eco_crystal',
            default => null,
        };

        if (! $prefix) {
            return;
        }

        $order = $route->routePoints->search(fn ($p) => $p->id === $point->id);

        if ($order === false) {
            return;
        }

        $order++;

        if ($order === 1 && $this->quizMission($point) && ! $this->firstPointQuizThresholdMet($session, $point)) {
            return;
        }

        $session->awardCollectible("{$prefix}_{$order}");
    }

    public function completeMission(Request $request, $missionId)
    {
        $request->validate([
            'earned' => 'required|integer|min:0',
        ]);

        $mission = RouteMission::findOrFail($missionId);

        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');

        if (! $userId && $guestToken && ! session()->has('guest_token')) {
            session(['guest_token' => $guestToken, 'guest_name' => __('Wisatawan')]);
        }

        $session = $this->findActiveSession($userId, $guestToken);

        if (! $session) {
            return response()->json(['success' => false, 'message' => __('Sesi tidak ditemukan')], 404);
        }

        if ($session->current_point_id != $mission->tour_route_point_id) {
            return response()->json(['success' => false, 'message' => __('Misi ini bukan bagian dari titik saat ini.')], 422);
        }

        $completed = $session->missions_completed ?? [];
        $alreadyCompleted = \in_array($mission->id, $completed);

        if (! $alreadyCompleted) {
            // Score is client-reported (same trust level as GPS proximity), clamped to the mission cap.
            $session->total_score += min((int) $request->integer('earned'), $mission->points);
            $completed[] = $mission->id;
            $session->missions_completed = $completed;
        }

        $point = TourRoutePoint::with(['locationable', 'missions'])->find($mission->tour_route_point_id);
        $isLastMission = $point->missions->last()?->id === $mission->id;

        if ($isLastMission) {
            $session->points_completed += 1;
            $this->recordVisit($userId, $point, $session);
            $this->maybeAwardFirstPointCollectible($session, $point);
            $this->awardSequentialCollectible($session, $point);
            $this->advanceToNextPoint($session);
        }

        $session->save();

        return response()->json([
            'success' => true,
            'is_last_mission' => $isLastMission,
            'session' => $session,
            'session_status' => $session->status,
        ]);
    }

    /**
     * Resolve a scanned QR payload to the active session's current point.
     * Accepts raw tokens (EDU-...), AR marker ids (MARKER_...), or full URLs
     * (/ar/scan/{id}, ?marker=, — same formats ar-scanner.js understands),
     * so one physical QR sticker serves both AR scan and route unlock.
     */
    public function resolveQr(Request $request)
    {
        $request->validate(['code' => 'required|string|max:2048']);

        $userId = auth()->id();
        $guestToken = session('guest_token') ?? $request->cookie('visitor_token');
        $session = $this->findActiveSession($userId, $guestToken);

        if (! $session || ! $session->current_point_id) {
            return response()->json(['success' => false, 'message' => __('Tidak ada rute aktif.')], 404);
        }

        $code = $this->extractQrIdentifier($request->string('code')->toString());

        $points = TourRoutePoint::with('locationable.mapLocation.arModel')
            ->where('tour_route_id', $session->tour_route_id)
            ->orderBy('order')
            ->get();

        $matched = $points->first(function ($point) use ($code) {
            $markerFromAr = $point->locationable?->mapLocation?->arModel?->ar_marker_id;

            return ($point->qr_code_token && strcasecmp($point->qr_code_token, $code) === 0)
                || ($markerFromAr && strcasecmp($markerFromAr, $code) === 0);
        });

        if (! $matched) {
            return response()->json(['success' => false, 'message' => __('QR ini tidak dikenali sebagai titik rute.')], 422);
        }

        if ($matched->id !== $session->current_point_id) {
            return response()->json(['success' => false, 'message' => __('QR valid, tapi bukan titik tujuanmu saat ini. Selesaikan titik saat ini dulu.')], 422);
        }

        return response()->json(['success' => true, 'point_id' => $matched->id]);
    }

    private function extractQrIdentifier(string $raw): string
    {
        $raw = trim($raw);

        if (preg_match('/[?&]marker=([^&#]+)/', $raw, $m)) {
            return urldecode($m[1]);
        }

        if (preg_match('~/ar/scan/([^/?#]+)~', $raw, $m)) {
            return urldecode($m[1]);
        }

        if (preg_match('~/edutourism/qr/([^/?#]+)~', $raw, $m)) {
            return urldecode($m[1]);
        }

        return $raw;
    }

    private function recordVisit(?int $userId, TourRoutePoint $point, RouteSession $session): void
    {
        if (! $userId || ! $point->locationable) {
            return;
        }

        $locationable = $point->locationable;

        UserVisit::updateOrCreate(
            [
                'user_id' => $userId,
                'visitable_type' => $locationable->getMorphClass(),
                'visitable_id' => $locationable->id,
            ],
            [
                'route_session_id' => $session->id,
                'visited_at' => now(),
            ]
        );
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
