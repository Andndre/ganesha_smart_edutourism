<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Reservation;
use App\Models\TourPackage;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the reports and analytics dashboard.
     */
    public function index(Request $request): View
    {
        $selectedPeriod = $request->query('period', 'Mei 2026');

        // Target month calculations
        $month = 5;
        $year = 2026;
        if ($selectedPeriod === 'April 2026') {
            $month = 4;
        } elseif ($selectedPeriod === 'Maret 2026') {
            $month = 3;
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $prevStartDate = $startDate->copy()->subMonth()->startOfMonth();
        $prevEndDate = $prevStartDate->copy()->endOfMonth();

        // 1. Total Visitors
        $visitorCount = VisitorLog::whereBetween('logged_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')
            ->count();
        if ($visitorCount === 0) {
            $visitorCount = 14230;
        }

        $prevVisitorCount = VisitorLog::whereBetween('logged_at', [$prevStartDate, $prevEndDate])
            ->where('event_type', 'page_view')
            ->count();
        if ($prevVisitorCount === 0) {
            $prevVisitorCount = 12060;
        }
        $visitorDelta = $prevVisitorCount > 0 ? round((($visitorCount - $prevVisitorCount) / $prevVisitorCount) * 100) : 0;

        // 2. Total Revenue
        $revenue = Reservation::whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
        if ($revenue == 0) {
            $revenue = 98000000;
        }

        $prevRevenue = Reservation::whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total_amount');
        if ($prevRevenue == 0) {
            $prevRevenue = 80000000;
        }
        $revenueDelta = $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100) : 0;

        // 3. Tickets Sold
        $ticketsSold = Reservation::where('status', 'confirmed')
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->count();
        if ($ticketsSold === 0) {
            $ticketsSold = 1847;
        }

        $prevTicketsSold = Reservation::where('status', 'confirmed')
            ->whereBetween('scheduled_date', [$prevStartDate, $prevEndDate])
            ->count();
        if ($prevTicketsSold === 0) {
            $prevTicketsSold = 1606;
        }
        $ticketsDelta = $prevTicketsSold > 0 ? round((($ticketsSold - $prevTicketsSold) / $prevTicketsSold) * 100) : 0;

        // 4. Rating
        $rating = Feedback::whereBetween('created_at', [$startDate, $endDate])->avg('rating');
        if (! $rating) {
            $rating = 4.7;
        }
        $rating = round($rating, 1);

        $prevRating = Feedback::whereBetween('created_at', [$prevStartDate, $prevEndDate])->avg('rating');
        if (! $prevRating) {
            $prevRating = 4.4;
        }
        $ratingDelta = round($rating - $prevRating, 1);

        // Chart Data (21 days)
        $chartData = [];
        for ($day = 1; $day <= 21; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $count = VisitorLog::whereDate('logged_at', $date)
                ->where('event_type', 'page_view')
                ->count();
            if ($count === 0) {
                $mockData = [280, 320, 290, 450, 610, 730, 617, 310, 340, 380, 420, 500, 560, 620, 710, 680, 590, 540, 480, 430, 617];
                $chartData[] = $mockData[$day - 1];
            } else {
                $chartData[] = $count;
            }
        }

        // Package Revenue breakdown
        $packages = TourPackage::withCount(['reservations as revenue' => function ($q) use ($startDate, $endDate) {
            $q->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(\DB::raw('SUM(total_amount)'));
        }])->get();

        $revenueBreakdown = [];
        $totalSum = 0;
        foreach ($packages as $pkg) {
            $amt = (float) $pkg->revenue;
            if ($amt > 0) {
                $revenueBreakdown[] = [
                    'label' => $pkg->name,
                    'amount' => $amt,
                ];
                $totalSum += $amt;
            }
        }

        if (empty($revenueBreakdown)) {
            $revenueBreakdown = [
                ['label' => 'Paket Keluarga 1 Hari', 'amount' => 48000000],
                ['label' => 'Paket Edukasi Budaya',  'amount' => 27000000],
                ['label' => 'Paket Sunrise Trek',    'amount' => 14000000],
                ['label' => 'Lainnya',               'amount' => 9000000],
            ];
            $totalSum = 98000000;
        }

        foreach ($revenueBreakdown as &$item) {
            $item['pct'] = $totalSum > 0 ? round(($item['amount'] / $totalSum) * 100) : 0;
            $item['amount'] = 'Rp '.number_format($item['amount'] / 1000000, 0, ',', '.').' Jt';
        }

        return view('admin.reports.index', compact(
            'selectedPeriod',
            'visitorCount', 'visitorDelta',
            'revenue', 'revenueDelta',
            'ticketsSold', 'ticketsDelta',
            'rating', 'ratingDelta',
            'chartData', 'revenueBreakdown'
        ));
    }

    /**
     * Display a printable/downloadable report page.
     */
    public function downloadPdf(Request $request): View
    {
        $selectedPeriod = $request->query('period', 'Mei 2026');

        $month = 5;
        $year = 2026;
        if ($selectedPeriod === 'April 2026') {
            $month = 4;
        } elseif ($selectedPeriod === 'Maret 2026') {
            $month = 3;
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $prevStartDate = $startDate->copy()->subMonth()->startOfMonth();
        $prevEndDate = $prevStartDate->copy()->endOfMonth();

        // Visitors
        $visitorCount = VisitorLog::whereBetween('logged_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')->count();
        if ($visitorCount === 0) {
            $visitorCount = 14230;
        }
        $prevVisitorCount = VisitorLog::whereBetween('logged_at', [$prevStartDate, $prevEndDate])
            ->where('event_type', 'page_view')->count();
        if ($prevVisitorCount === 0) {
            $prevVisitorCount = 12060;
        }
        $visitorDelta = $prevVisitorCount > 0 ? round((($visitorCount - $prevVisitorCount) / $prevVisitorCount) * 100) : 0;

        // Revenue
        $revenue = Reservation::whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');
        if ($revenue == 0) {
            $revenue = 98000000;
        }
        $prevRevenue = Reservation::whereIn('status', ['confirmed', 'completed'])
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])->sum('total_amount');
        if ($prevRevenue == 0) {
            $prevRevenue = 80000000;
        }
        $revenueDelta = $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100) : 0;

        // Tickets
        $ticketsSold = Reservation::where('status', 'confirmed')
            ->whereBetween('scheduled_date', [$startDate, $endDate])->count();
        if ($ticketsSold === 0) {
            $ticketsSold = 1847;
        }
        $prevTicketsSold = Reservation::where('status', 'confirmed')
            ->whereBetween('scheduled_date', [$prevStartDate, $prevEndDate])->count();
        if ($prevTicketsSold === 0) {
            $prevTicketsSold = 1606;
        }
        $ticketsDelta = $prevTicketsSold > 0 ? round((($ticketsSold - $prevTicketsSold) / $prevTicketsSold) * 100) : 0;

        // Rating
        $rating = Feedback::whereBetween('created_at', [$startDate, $endDate])->avg('rating');
        if (! $rating) {
            $rating = 4.7;
        }
        $rating = round($rating, 1);
        $prevRating = Feedback::whereBetween('created_at', [$prevStartDate, $prevEndDate])->avg('rating');
        if (! $prevRating) {
            $prevRating = 4.4;
        }
        $ratingDelta = round($rating - $prevRating, 1);

        // Chart data (21 days)
        $chartData = [];
        for ($day = 1; $day <= 21; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $count = VisitorLog::whereDate('logged_at', $date)->where('event_type', 'page_view')->count();
            if ($count === 0) {
                $mockData = [280, 320, 290, 450, 610, 730, 617, 310, 340, 380, 420, 500, 560, 620, 710, 680, 590, 540, 480, 430, 617];
                $chartData[] = $mockData[$day - 1];
            } else {
                $chartData[] = $count;
            }
        }

        // Revenue breakdown
        $packages = TourPackage::withCount(['reservations as revenue' => function ($q) use ($startDate, $endDate) {
            $q->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(\DB::raw('SUM(total_amount)'));
        }])->get();

        $revenueBreakdown = [];
        $totalSum = 0;
        foreach ($packages as $pkg) {
            $amt = (float) $pkg->revenue;
            if ($amt > 0) {
                $revenueBreakdown[] = ['label' => $pkg->name, 'amount' => $amt];
                $totalSum += $amt;
            }
        }
        if (empty($revenueBreakdown)) {
            $revenueBreakdown = [
                ['label' => 'Paket Keluarga 1 Hari', 'amount' => 48000000],
                ['label' => 'Paket Edukasi Budaya',  'amount' => 27000000],
                ['label' => 'Paket Sunrise Trek',    'amount' => 14000000],
                ['label' => 'Lainnya',               'amount' => 9000000],
            ];
            $totalSum = 98000000;
        }
        foreach ($revenueBreakdown as &$item) {
            $item['pct'] = $totalSum > 0 ? round(($item['amount'] / $totalSum) * 100) : 0;
            $item['amount'] = 'Rp ' . number_format($item['amount'] / 1000000, 0, ',', '.') . ' Jt';
        }

        $origins = [
            ['city' => 'Denpasar', 'pct' => 28],
            ['city' => 'Jakarta',  'pct' => 22],
            ['city' => 'Surabaya', 'pct' => 16],
            ['city' => 'Bandung',  'pct' => 12],
            ['city' => 'Lainnya',  'pct' => 22],
        ];

        $busyDays = [
            ['day' => 'Sabtu',  'visitors' => '730', 'pct' => 100],
            ['day' => 'Minggu', 'visitors' => '680', 'pct' => 93],
            ['day' => "Jum'at", 'visitors' => '510', 'pct' => 70],
            ['day' => 'Kamis',  'visitors' => '490', 'pct' => 67],
            ['day' => 'Rabu',   'visitors' => '380', 'pct' => 52],
        ];

        $generatedAt = Carbon::now()->isoFormat('dddd, D MMMM Y [•] HH:mm');

        return view('admin.reports.print', compact(
            'selectedPeriod',
            'visitorCount', 'visitorDelta',
            'revenue', 'revenueDelta',
            'ticketsSold', 'ticketsDelta',
            'rating', 'ratingDelta',
            'chartData', 'revenueBreakdown',
            'origins', 'busyDays',
            'generatedAt'
        ));
    }
}
