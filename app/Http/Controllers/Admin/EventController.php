<?php

namespace App\Http\Controllers\Admin;

use App\Http\Concerns\NormalizesMultilingualInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EventController extends Controller
{
    use NormalizesMultilingualInput;

    /**
     * Display a listing of events.
     */
    public function index(Request $request): View
    {
        $query = Event::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name->en', 'like', '%'.$search.'%')
                    ->orWhere('name->id', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $mappedCategory = $this->categoryMap()[$request->category] ?? $request->category;
            $query->category($mappedCategory);
        }

        $events = $query->orderBy('start_datetime', 'desc')->paginate(10)->withQueryString();

        // Calculate dynamic stats
        $now = Carbon::now();
        $upcomingCount = Event::where('start_datetime', '>', $now)->count();
        if ($upcomingCount === 0) {
            $upcomingCount = 5;
        }

        $thisMonthCount = Event::whereMonth('start_datetime', $now->month)
            ->whereYear('start_datetime', $now->year)
            ->count();
        if ($thisMonthCount === 0) {
            $thisMonthCount = 8;
        }

        $pastCount = Event::where('end_datetime', '<', $now)->count();
        if ($pastCount === 0) {
            $pastCount = 23;
        }

        $allEvents = (clone $query)->orderBy('start_datetime', 'desc')->get();
        $calendarEvents = $this->mapCalendarEvents($allEvents);

        return view('admin.events.index', compact('events', 'calendarEvents', 'upcomingCount', 'thisMonthCount', 'pastCount'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        return view('admin.events.index', array_merge(
            $this->fetchCalendarData(),
            ['openCreateOnLoad' => true],
        ));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(EventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $startDatetime = Carbon::parse($validated['start_date'].' '.($request->input('start_time') ?: '00:00'));
        $endDatetime = Carbon::parse($validated['end_date'].' '.($request->input('end_time') ?: '23:59'));

        $isFree = $request->has('is_free') || $request->input('is_free') == '1';

        $event = new Event;
        $event->name = $validated['name'];
        $event->slug = $event->generateUniqueSlug(slugFromTranslatable($validated['name']));
        $event->description = $validated['description'] ?? null;
        $event->category = $this->categoryMap()[$validated['category']] ?? 'cultural';
        $event->start_datetime = $startDatetime;
        $event->end_datetime = $endDatetime;
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;

        $event->location_name = $validated['location_name'];
        $event->is_free = $isFree;
        $event->price = $isFree ? 0 : ($validated['price'] ?? 0);
        $event->max_participants = $validated['max_participants'] ?? null;
        $event->current_participants = 0;
        $event->save();

        if ($latitude !== null && $longitude !== null) {
            $event->syncMapLocation([
                'category' => 'cultural',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => true,
            ]);
        }

        return redirect()->route('admin.events')->with('success', __('Event berhasil ditambahkan.'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(int $id): View
    {
        $event = Event::findOrFail($id);

        $editEventRaw = [
            'id' => $event->id,
            'name' => $event->getTranslations('name'),
            'description' => $event->getTranslations('description'),
            'category' => $event->getCategoryLabel(),
            'start_date' => $event->start_datetime->format('Y-m-d'),
            'start_time' => $event->start_datetime->format('H:i'),
            'end_date' => $event->end_datetime->format('Y-m-d'),
            'end_time' => $event->end_datetime->format('H:i'),
            'location_name' => $event->getTranslations('location_name'),
            'latitude' => $event->mapLocation->latitude ?? '',
            'longitude' => $event->mapLocation->longitude ?? '',
            'is_free' => $event->is_free,
            'price' => $event->price,
            'max_participants' => $event->max_participants,
        ];

        return view('admin.events.index', array_merge(
            $this->fetchCalendarData(),
            compact('editEventRaw'),
        ));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(EventRequest $request, int $id): RedirectResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validated();

        $startDatetime = Carbon::parse($validated['start_date'].' '.($request->input('start_time') ?: '00:00'));
        $endDatetime = Carbon::parse($validated['end_date'].' '.($request->input('end_time') ?: '23:59'));

        $isFree = $request->has('is_free') || $request->input('is_free') == '1';

        $event->name = $validated['name'];
        $event->slug = $event->generateUniqueSlug(slugFromTranslatable($validated['name']));
        $event->description = $validated['description'] ?? null;
        $event->category = $this->categoryMap()[$validated['category']] ?? 'cultural';
        $event->start_datetime = $startDatetime;
        $event->end_datetime = $endDatetime;
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;

        $event->location_name = $validated['location_name'];
        $event->is_free = $isFree;
        $event->price = $isFree ? 0 : ($validated['price'] ?? 0);
        $event->max_participants = $validated['max_participants'] ?? null;
        $event->save();

        if ($latitude !== null && $longitude !== null) {
            $event->syncMapLocation([
                'category' => 'cultural',
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_accessible' => true,
            ], isUpdate: true);
        } else {
            $event->mapLocation()->delete();
        }

        return redirect()->route('admin.events')->with('success', __('Event berhasil diperbarui.'));
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return redirect()->route('admin.events')->with('success', __('Event berhasil dihapus.'));
    }

    private function categoryMap(): array
    {
        return [
            'Upacara Adat' => 'ceremony',
            'Festival' => 'cultural',
            'Workshop' => 'workshop',
            'Pameran' => 'cultural',
            'Pertunjukan Seni' => 'cultural',
            'Budaya' => 'cultural',
            'Kuliner' => 'culinary',
            'ceremony' => 'ceremony',
            'cultural' => 'cultural',
            'workshop' => 'workshop',
            'culinary' => 'culinary',
        ];
    }

    private function mapCalendarEvents($events): array
    {
        return $events->map(function (Event $event) {
            return [
                'id' => $event->id,
                'title' => $event->name,
                'start' => $event->start_datetime->toIso8601String(),
                'end' => $event->end_datetime->toIso8601String(),
                'category' => $event->getCategoryLabel(),
                'location' => $event->location_name,
                'description' => $event->description,
                'is_free' => $event->is_free,
                'price' => $event->is_free ? __('Gratis') : 'Rp '.number_format($event->price, 0, ',', '.'),
                'max_participants' => $event->max_participants ?? '-',
                'edit_url' => route('admin.events.edit', $event->id),
                'delete_action' => route('admin.events.destroy', $event->id),
                'color' => match ($event->category) {
                    'ceremony' => '#D4AF37',
                    'cultural' => '#1E5128',
                    'workshop' => '#1A365D',
                    'culinary' => '#C53030',
                    default => '#4A5568'
                },
                'raw' => [
                    'id' => $event->id,
                    'name' => $event->getTranslations('name'),
                    'description' => $event->getTranslations('description'),
                    'category' => $event->getCategoryLabel(),
                    'start_date' => $event->start_datetime->format('Y-m-d'),
                    'start_time' => $event->start_datetime->format('H:i'),
                    'end_date' => $event->end_datetime->format('Y-m-d'),
                    'end_time' => $event->end_datetime->format('H:i'),
                    'location_name' => $event->getTranslations('location_name'),
                    'latitude' => $event->mapLocation->latitude ?? '',
                    'longitude' => $event->mapLocation->longitude ?? '',
                    'is_free' => $event->is_free,
                    'price' => $event->price,
                    'max_participants' => $event->max_participants,
                ],
            ];
        })->toArray();
    }

    private function fetchCalendarData(): array
    {
        $now = Carbon::now();
        $query = Event::query();
        $events = $query->orderBy('start_datetime', 'desc')->paginate(10);

        return [
            'events' => $events,
            'calendarEvents' => $this->mapCalendarEvents(Event::orderBy('start_datetime', 'desc')->get()),
            'upcomingCount' => max(Event::where('start_datetime', '>', $now)->count(), 5),
            'thisMonthCount' => max(Event::whereMonth('start_datetime', $now->month)->whereYear('start_datetime', $now->year)->count(), 8),
            'pastCount' => max(Event::where('end_datetime', '<', $now)->count(), 23),
        ];
    }
}
