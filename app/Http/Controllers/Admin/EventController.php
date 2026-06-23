<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
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
            $categoryMap = [
            'Upacara Adat' => 'ceremony',
            'Festival' => 'cultural',
            'Workshop' => 'workshop',
            'Pameran' => 'cultural',
            'Pertunjukan Seni' => 'cultural',
            'Budaya' => 'cultural',
                __('Kuliner') => 'culinary',
            ];
            $mappedCategory = $categoryMap[$request->category] ?? $request->category;
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
                'price' => $event->is_free ? 'Gratis' : 'Rp '.number_format($event->price, 0, ',', '.'),
                'max_participants' => $event->max_participants ?? '-',
                'edit_url' => route('admin.events.edit', $event->id),
                'delete_action' => route('admin.events.destroy', $event->id),
                'color' => match ($event->category) {
                    'ceremony' => '#D4AF37', // Gold / ceremony
                    'cultural' => '#1E5128', // Green / brand
                    'workshop' => '#1A365D', // Deep Blue
                    'culinary' => '#C53030', // Red
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
        });

        return view('admin.events.index', compact('events', 'calendarEvents', 'upcomingCount', 'thisMonthCount', 'pastCount'));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(): View
    {
        $query = Event::query();
        $events = $query->orderBy('start_datetime', 'desc')->paginate(10);

        $now = Carbon::now();
        $upcomingCount = Event::where('start_datetime', '>', $now)->count() ?: 5;
        $thisMonthCount = Event::whereMonth('start_datetime', $now->month)->whereYear('start_datetime', $now->year)->count() ?: 8;
        $pastCount = Event::where('end_datetime', '<', $now)->count() ?: 23;

        $allEvents = Event::orderBy('start_datetime', 'desc')->get();
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
        });

        $openCreateOnLoad = true;

        return view('admin.events.index', compact('events', 'calendarEvents', 'upcomingCount', 'thisMonthCount', 'pastCount', 'openCreateOnLoad'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['nullable', 'string'],
            'location_name' => ['required', 'array'],
            'location_name.en' => ['required', 'string', 'max:255'],
            'location_name.id' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_free' => ['nullable', 'boolean'],
            'price' => ['required_if:is_free,0', 'nullable', 'numeric', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
        ]);

        // Combine date and time
        $startTimeStr = $request->input('start_time') ?: '00:00';
        $endTimeStr = $request->input('end_time') ?: '23:59';

        $startDatetime = Carbon::parse($validated['start_date'].' '.$startTimeStr);
        $endDatetime = Carbon::parse($validated['end_date'].' '.$endTimeStr);

        if ($endDatetime->lt($startDatetime)) {
            return back()->withErrors(['end_date' => __('Tanggal & waktu selesai harus setelah waktu mulai.')])->withInput();
        }

        $isFree = $request->has('is_free') || $request->input('is_free') == '1';

        $categoryMap = [
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

        $event = new Event;
        $event->name = $validated['name'];
        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $event->slug = Str::slug($slugValue).'-'.Str::random(5);
        $event->description = $validated['description'] ?? null;
        $event->category = $categoryMap[$validated['category']] ?? 'cultural';
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
            $event->mapLocation()->create([
                'name' => is_string($event->name) ? $event->name : ($event->name[config('app.fallback_locale')] ?? $event->name['en'] ?? ''),
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

        $query = Event::query();
        $events = $query->orderBy('start_datetime', 'desc')->paginate(10);

        $now = Carbon::now();
        $upcomingCount = Event::where('start_datetime', '>', $now)->count() ?: 5;
        $thisMonthCount = Event::whereMonth('start_datetime', $now->month)->whereYear('start_datetime', $now->year)->count() ?: 8;
        $pastCount = Event::where('end_datetime', '<', $now)->count() ?: 23;

        $allEvents = Event::orderBy('start_datetime', 'desc')->get();
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

        return view('admin.events.index', compact('events', 'calendarEvents', 'upcomingCount', 'thisMonthCount', 'pastCount', 'editEventRaw'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'array'],
            'name.en' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'array'],
            'description.en' => ['nullable', 'string'],
            'description.id' => ['nullable', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'end_time' => ['nullable', 'string'],
            'location_name' => ['required', 'array'],
            'location_name.en' => ['required', 'string', 'max:255'],
            'location_name.id' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_free' => ['nullable', 'boolean'],
            'price' => ['required_if:is_free,0', 'nullable', 'numeric', 'min:0'],
            'max_participants' => ['nullable', 'integer', 'min:1'],
        ]);

        $startTimeStr = $request->input('start_time') ?: '00:00';
        $endTimeStr = $request->input('end_time') ?: '23:59';

        $startDatetime = Carbon::parse($validated['start_date'].' '.$startTimeStr);
        $endDatetime = Carbon::parse($validated['end_date'].' '.$endTimeStr);

        if ($endDatetime->lt($startDatetime)) {
            return back()->withErrors(['end_date' => __('Tanggal & waktu selesai harus setelah waktu mulai.')])->withInput();
        }

        $isFree = $request->has('is_free') || $request->input('is_free') == '1';

        $categoryMap = [
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

        $event->name = $validated['name'];
        $defaultLocale = config('app.fallback_locale', 'en');
        $slugValue = $validated['name'][$defaultLocale] ?? $validated['name']['en'] ?? reset($validated['name']);
        $event->slug = Str::slug($slugValue).'-'.Str::random(5);
        $event->description = $validated['description'] ?? null;
        $event->category = $categoryMap[$validated['category']] ?? 'cultural';
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
            $event->mapLocation()->updateOrCreate(
                [],
                [
                    'name' => is_string($event->name) ? $event->name : ($event->name[config('app.fallback_locale')] ?? $event->name['en'] ?? ''),
                    'category' => 'cultural',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'is_accessible' => true,
                ]
            );
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
}
