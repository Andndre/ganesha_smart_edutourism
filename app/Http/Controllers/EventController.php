<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of events for the public page.
     */
    public function index(Request $request): View
    {
        $query = Event::query();

        // Optional filter by category
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
            $query->category($mappedCategory);
        }

        // Get future events for the public timeline / list view, pre-mapped for Alpine.js
        $upcomingEvents = (clone $query)
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
            });

        // Get all events for the interactive calendar
        $allEvents = $query->orderBy('start_datetime', 'desc')->get();

        $calendarEvents = $allEvents->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->start_datetime->toIso8601String(),
                'end' => $event->end_datetime->toIso8601String(),
                'category' => $event->getCategoryLabel(),
                'location' => $event->location_name,
                'description' => $event->description,
                'is_free' => $event->is_free,
                'price' => $event->is_free ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.'),
                'max_participants' => $event->max_participants ?? '-',
                'color' => match ($event->category) {
                    'ceremony' => '#D4AF37', // Gold / ceremony
                    'cultural' => '#1E5128', // Green / brand
                    'workshop' => '#1A365D', // Deep Blue
                    'culinary' => '#C53030', // Red
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
        });

        return view('pages.events.index', compact('upcomingEvents', 'calendarEvents'));
    }
}
