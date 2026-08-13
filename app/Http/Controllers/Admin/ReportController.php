<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\TicketScan;
use App\Models\VisitorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Get the busiest days within a date range.
     *
     * @return array<int, array{day: string, visitors: int, pct: int}>
     */
    private function getBusyDays(Carbon $startDate, Carbon $endDate): array
    {
        $driver = DB::getDriverName();
        $dayExpr = match ($driver) {
            'sqlite' => "(strftime('%w', logged_at) + 1)",
            'pgsql' => '(EXTRACT(DOW FROM logged_at)::int + 1)',
            default => 'DAYOFWEEK(logged_at)',
        };
        $busyDays = VisitorLog::selectRaw("{$dayExpr} as day_num, COUNT(DISTINCT session_id) as total")
            ->whereBetween('logged_at', [$startDate, $endDate])
            ->groupBy('day_num')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($busyDays->isEmpty()) {
            return [];
        }

        $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
        $maxTotal = $busyDays->max('total');

        return $busyDays->map(fn (object $row) => [
            'day' => $dayNames[$row->day_num - 1],
            'visitors' => (int) $row->total,
            'pct' => (int) round(($row->total / $maxTotal) * 100),
        ])->toArray();
    }

    /**
     * Display the reports and analytics dashboard.
     */
    public function index(Request $request): View
    {
        $now = Carbon::now();
        $selectedPeriod = $request->query('period', $now->locale('id')->isoFormat('MMMM YYYY'));

        return view('admin.reports.index', $this->buildReportData($selectedPeriod));
    }

    /**
     * Display a printable/downloadable report page.
     */
    public function downloadPdf(Request $request): View
    {
        $now = Carbon::now();
        $selectedPeriod = $request->query('period', $now->locale('id')->isoFormat('MMMM YYYY'));

        return view('admin.reports.print', array_merge(
            $this->buildReportData($selectedPeriod),
            ['generatedAt' => Carbon::now()->isoFormat('dddd, D MMMM Y [•] HH:mm')],
        ));
    }

    private function buildReportData(string $selectedPeriod): array
    {
        $now = Carbon::now();
        $defaultPeriod = $now->locale('id')->isoFormat('MMMM YYYY');

        $monthNames = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
            'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
            'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
        ];
        $parts = explode(' ', $selectedPeriod);
        $month = $monthNames[$parts[0]] ?? $now->month;
        $year = (int) ($parts[1] ?? $now->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $prevStartDate = $startDate->copy()->subMonth()->startOfMonth();
        $prevEndDate = $prevStartDate->copy()->endOfMonth();

        // Visitors
        $visitorCount = VisitorLog::whereBetween('logged_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')
            ->count();
        $prevVisitorCount = VisitorLog::whereBetween('logged_at', [$prevStartDate, $prevEndDate])
            ->where('event_type', 'page_view')
            ->count();
        $visitorDelta = $prevVisitorCount > 0 ? round((($visitorCount - $prevVisitorCount) / $prevVisitorCount) * 100) : 0;

        // Kunjungan tercatat dari scan tiket
        $scannedVisitors = (int) TicketScan::whereBetween('scanned_at', [$startDate, $endDate])->sum('party_size');
        $prevScannedVisitors = (int) TicketScan::whereBetween('scanned_at', [$prevStartDate, $prevEndDate])->sum('party_size');
        $scannedDelta = $prevScannedVisitors > 0
            ? round((($scannedVisitors - $prevScannedVisitors) / $prevScannedVisitors) * 100)
            : 0;

        // Jumlah tiket yang dipindai
        $ticketsScanned = TicketScan::whereBetween('scanned_at', [$startDate, $endDate])->count();
        $prevTicketsScanned = TicketScan::whereBetween('scanned_at', [$prevStartDate, $prevEndDate])->count();
        $ticketsDelta = $prevTicketsScanned > 0
            ? round((($ticketsScanned - $prevTicketsScanned) / $prevTicketsScanned) * 100)
            : 0;

        // Rating
        $rating = round(Feedback::whereBetween('created_at', [$startDate, $endDate])->avg('rating') ?? 0, 1);

        $prevRating = round(Feedback::whereBetween('created_at', [$prevStartDate, $prevEndDate])->avg('rating') ?? 0, 1);
        $ratingDelta = round($rating - $prevRating, 1);

        // Chart Data (21 days)
        $chartData = [];
        for ($day = 1; $day <= 21; $day++) {
            $date = Carbon::createFromDate($year, $month, $day);
            $chartData[] = VisitorLog::whereDate('logged_at', $date)
                ->where('event_type', 'page_view')
                ->count();
        }

        // Komposisi asal pengunjung
        $domestic = (int) TicketScan::whereBetween('scanned_at', [$startDate, $endDate])
            ->where('origin', 'domestic')->sum('party_size');
        $foreign = (int) TicketScan::whereBetween('scanned_at', [$startDate, $endDate])
            ->where('origin', 'foreign')->sum('party_size');
        $originTotal = $domestic + $foreign;

        $originBreakdown = [];
        foreach (['Domestik' => $domestic, 'Mancanegara' => $foreign] as $label => $count) {
            if ($count > 0) {
                $originBreakdown[] = [
                    'label' => $label,
                    'amount' => number_format($count).' orang',
                    'pct' => $originTotal > 0 ? (int) round(($count / $originTotal) * 100) : 0,
                ];
            }
        }

        $busyDays = $this->getBusyDays($startDate, $endDate);

        return compact(
            'selectedPeriod',
            'visitorCount', 'visitorDelta',
            'scannedVisitors', 'scannedDelta',
            'ticketsScanned', 'ticketsDelta',
            'rating', 'ratingDelta',
            'chartData', 'originBreakdown',
            'busyDays'
        );
    }
}
