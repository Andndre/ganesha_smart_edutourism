<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\TicketScan;
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
        $yesterdayVisitorCount = Cache::tags(['dashboard'])->flexible('dashboard_yesterday_visitor_count', [3600, 7200], function () {
            return VisitorLog::whereDate('logged_at', Carbon::yesterday())
                ->where('event_type', 'page_view')
                ->count();
        });
        $visitorDelta = $yesterdayVisitorCount > 0
            ? round((($todayVisitorCount - $yesterdayVisitorCount) / $yesterdayVisitorCount) * 100)
            : 0;

        // 2. Kunjungan tercatat dari scan tiket
        $todayScannedVisitors = Cache::tags(['dashboard'])->flexible('dashboard_today_scanned_visitors', [300, 600], function () {
            return (int) TicketScan::whereDate('scanned_at', Carbon::today())->sum('party_size');
        });
        $yesterdayScannedVisitors = Cache::tags(['dashboard'])->flexible('dashboard_yesterday_scanned_visitors', [3600, 7200], function () {
            return (int) TicketScan::whereDate('scanned_at', Carbon::yesterday())->sum('party_size');
        });
        $scannedDelta = $yesterdayScannedVisitors > 0
            ? round((($todayScannedVisitors - $yesterdayScannedVisitors) / $yesterdayScannedVisitors) * 100)
            : 0;

        // 3. Jumlah tiket yang dipindai
        $todayTicketCount = TicketScan::whereDate('scanned_at', Carbon::today())->count();

        $yesterdayTicketCount = TicketScan::whereDate('scanned_at', Carbon::yesterday())->count();
        $ticketsDelta = $yesterdayTicketCount > 0
            ? round((($todayTicketCount - $yesterdayTicketCount) / $yesterdayTicketCount) * 100)
            : 0;

        // 4. Avg satisfaction rating
        $avgRating = round(Feedback::avg('rating') ?? 0, 1);

        $prevAvgRating = round(Feedback::whereDate('created_at', '<', Carbon::today())->avg('rating') ?? 0, 1);
        $ratingDelta = round($avgRating - $prevAvgRating, 1);

        // 7. Chart data (7 days)
        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('D');
            $chartValues[] = VisitorLog::whereDate('logged_at', $date)
                ->where('event_type', 'page_view')
                ->count();
        }

        return view('admin.dashboard', compact(
            'todayVisitorCount', 'visitorDelta',
            'todayScannedVisitors', 'scannedDelta',
            'todayTicketCount', 'ticketsDelta',
            'avgRating', 'ratingDelta',
            'chartLabels', 'chartValues'
        ));
    }
}
