<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CapacityZone;
use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\VisitorLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        // 1. Visitor stats
        $todayVisitorCount = Cache::tags(['dashboard'])->flexible('dashboard_today_visitor_count', [300, 600], function () {
            return VisitorLog::whereDate('logged_at', Carbon::today())
                ->where('event_type', 'page_view')
                ->count();
        });
        $todayVisitorCount = valueOrMock($todayVisitorCount, 617);

        $yesterdayVisitorCount = Cache::tags(['dashboard'])->flexible('dashboard_yesterday_visitor_count', [3600, 7200], function () {
            return VisitorLog::whereDate('logged_at', Carbon::yesterday())
                ->where('event_type', 'page_view')
                ->count();
        });
        $yesterdayVisitorCount = valueOrMock($yesterdayVisitorCount, 550);
        $visitorDelta = $yesterdayVisitorCount > 0
            ? round((($todayVisitorCount - $yesterdayVisitorCount) / $yesterdayVisitorCount) * 100)
            : 0;

        // 2. Revenue stats
        $todayRevenue = Cache::tags(['dashboard'])->flexible('dashboard_today_revenue', [300, 600], function () {
            return Reservation::whereIn('status', ['confirmed', 'completed'])
                ->whereDate('created_at', Carbon::today())
                ->sum('total_amount');
        });
        $todayRevenue = valueOrMock($todayRevenue, 4200000);

        $yesterdayRevenue = Cache::tags(['dashboard'])->flexible('dashboard_yesterday_revenue', [3600, 7200], function () {
            return Reservation::whereIn('status', ['confirmed', 'completed'])
                ->whereDate('created_at', Carbon::yesterday())
                ->sum('total_amount');
        });
        $yesterdayRevenue = valueOrMock($yesterdayRevenue, 3880000);
        $revenueDelta = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100)
            : 0;

        // 3. Active tickets count
        $activeTicketsCount = Reservation::where('status', 'confirmed')
            ->whereDate('scheduled_date', Carbon::today())
            ->count();
        $activeTicketsCount = valueOrMock($activeTicketsCount, 89);

        $yesterdayActiveTickets = Reservation::where('status', 'confirmed')
            ->whereDate('scheduled_date', Carbon::yesterday())
            ->count();
        $yesterdayActiveTickets = valueOrMock($yesterdayActiveTickets, 92);
        $ticketsDelta = $yesterdayActiveTickets > 0
            ? round((($activeTicketsCount - $yesterdayActiveTickets) / $yesterdayActiveTickets) * 100)
            : 0;

        // 4. Avg satisfaction rating
        $avgRating = valueOrMock(Feedback::avg('rating'), 4.7);

        $prevAvgRating = valueOrMock(Feedback::whereDate('created_at', '<', Carbon::today())->avg('rating'), 4.5);
        $ratingDelta = round($avgRating - $prevAvgRating, 1);

        // 5. Capacity Zones
        $zones = Cache::tags(['capacity'])->flexible('capacity_zones_active_array', [60, 300], function () {
            return CapacityZone::where('is_active', true)->get()->append('occupancy_percentage')->toArray();
        });
        if (empty($zones)) {
            $zones = [
                ['name' => 'Zona Utama', 'current_count' => 312, 'max_capacity' => 400, 'warning_threshold' => 70, 'critical_threshold' => 90, 'occupancy_percentage' => 78],
                ['name' => 'Area UMKM', 'current_count' => 178, 'max_capacity' => 300, 'warning_threshold' => 70, 'critical_threshold' => 90, 'occupancy_percentage' => 59],
                ['name' => 'Pura Penataran', 'current_count' => 85, 'max_capacity' => 150, 'warning_threshold' => 70, 'critical_threshold' => 90, 'occupancy_percentage' => 56],
                ['name' => 'Kebun Bambu', 'current_count' => 42, 'max_capacity' => 200, 'warning_threshold' => 70, 'critical_threshold' => 90, 'occupancy_percentage' => 21],
            ];
        }

        // 7. Chart data (7 days)
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('D');
            $count = VisitorLog::whereDate('logged_at', $date)
                ->where('event_type', 'page_view')
                ->count();
            $mockData = [412, 380, 520, 490, 610, 730, 617];
            $chartValues[] = valueOrMock($count, $mockData[6 - $i]);
        }

        return view('admin.dashboard', compact(
            'todayVisitorCount', 'visitorDelta',
            'todayRevenue', 'revenueDelta',
            'activeTicketsCount', 'ticketsDelta',
            'avgRating', 'ratingDelta',
            'zones',
            'chartLabels', 'chartValues'
        ));
    }
}
