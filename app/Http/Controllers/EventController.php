<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of events for the public page.
     */
    public function index(Request $request): View
    {
        // Build category-aware cache keys
        $categoryKey = 'all';
        if ($request->filled('category') && $request->category !== 'Semua') {
            $categoryMap = [
                'Upacara Adat' => 'ceremony',
                'Festival' => 'cultural',
                'Workshop' => 'workshop',
                'Pameran' => 'cultural',
                'Pertunjukan Seni' => 'cultural',
                'Budaya' => 'cultural',
                'Kuliner' => 'culinary',
            ];
            $mappedCategory = $categoryMap[$request->category] ?? $request->category;
            $categoryKey = $mappedCategory;
        }

        // Cache upcoming events (1 hour)
        $upcomingEvents = Cache::remember('public_events_upcoming_'.$categoryKey, 3600, function () use ($categoryKey) {
            $query = Event::query();

            if ($categoryKey !== 'all') {
                $query->category($categoryKey);
            }

            return (clone $query)
                ->with('mapLocation')
                ->where('end_datetime', '>=', Carbon::now())
                ->orderBy('start_datetime', 'asc')
                ->get()
                ->map(function (Event $event) {
                    return [
                        'id' => $event->id,
                        'name' => $event->name,
                        'description' => $event->description,
                        'category' => $event->getCategoryLabel(),
                        'start_date' => $event->start_datetime->format('Y-m-d'),
                        'start_time' => $event->start_datetime->format('H:i'),
                        'end_date' => $event->end_datetime->format('Y-m-d'),
                        'end_time' => $event->end_datetime->format('H:i'),
                        'location_name' => $event->location_name,
                        'is_free' => $event->is_free,
                        'price' => $event->price,
                        'max_participants' => $event->max_participants,
                        'latitude' => $event->mapLocation->latitude ?? '',
                        'longitude' => $event->mapLocation->longitude ?? '',
                    ];
                })->all();
        });

        // Cache calendar events (1 hour)
        $calendarEvents = Cache::remember('public_events_calendar_'.$categoryKey, 3600, function () use ($categoryKey) {
            $query = Event::query();

            if ($categoryKey !== 'all') {
                $query->category($categoryKey);
            }

            return $query->with('mapLocation')->orderBy('start_datetime', 'desc')
                ->get()
                ->map(function (Event $event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->name,
                        'start' => $event->start_datetime->toIso8601String(),
                        'end' => $event->end_datetime->toIso8601String(),
                        'category' => $event->getCategoryLabel(),
                        'location' => $event->location_name,
                        'description' => $event->description,
                        'is_free' => $event->is_free,
                        'price' => $event->is_free ? 'Gratis' : 'Rp '.number_format($event->price, 0, ',', '.'),
                        'max_participants' => $event->max_participants ?? '-',
                        'color' => match ($event->category) {
                            'ceremony' => '#D4AF37',
                            'cultural' => '#1E5128',
                            'workshop' => '#1A365D',
                            'culinary' => '#C53030',
                            default => '#4A5568'
                        },
                        'raw' => [
                            'id' => $event->id,
                            'name' => $event->name,
                            'description' => $event->description,
                            'category' => $event->getCategoryLabel(),
                            'start_date' => $event->start_datetime->format('Y-m-d'),
                            'start_time' => $event->start_datetime->format('H:i'),
                            'end_date' => $event->end_datetime->format('Y-m-d'),
                            'end_time' => $event->end_datetime->format('H:i'),
                            'location_name' => $event->location_name,
                            'latitude' => $event->mapLocation->latitude ?? '',
                            'longitude' => $event->mapLocation->longitude ?? '',
                            'is_free' => $event->is_free,
                            'price' => $event->price,
                            'max_participants' => $event->max_participants,
                        ],
                    ];
                })->all();
        });

        return view('user.events.index', compact('upcomingEvents', 'calendarEvents'));
    }
}
