<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\ReportController;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\Support\RegistersDayOfWeekFunction;
use Tests\TestCase;

class BusyDaysReportTest extends TestCase
{
    use RefreshDatabase;
    use RegistersDayOfWeekFunction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerDayOfWeekFunction();
    }

    private function callGetBusyDays(Carbon $startDate, Carbon $endDate): array
    {
        $controller = app(ReportController::class);
        $method = new ReflectionMethod($controller, 'getBusyDays');
        $method->setAccessible(true);

        return $method->invoke($controller, $startDate, $endDate);
    }

    public function test_returns_correct_data_structure(): void
    {
        $date = Carbon::parse('2026-05-04');
        for ($i = 0; $i < 10; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $result = $this->callGetBusyDays(
            Carbon::parse('2026-05-01'),
            Carbon::parse('2026-05-31')
        );

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('day', $result[0]);
        $this->assertArrayHasKey('visitors', $result[0]);
        $this->assertArrayHasKey('pct', $result[0]);
        $this->assertIsInt($result[0]['visitors']);
    }

    public function test_fallback_to_dummy_when_empty(): void
    {
        $result = $this->callGetBusyDays(
            Carbon::parse('2026-05-01'),
            Carbon::parse('2026-05-31')
        );

        $expected = [
            ['day' => 'Sabtu',  'visitors' => 730, 'pct' => 100],
            ['day' => 'Minggu', 'visitors' => 680, 'pct' => 93],
            ['day' => "Jum'at", 'visitors' => 510, 'pct' => 70],
            ['day' => 'Kamis',  'visitors' => 490, 'pct' => 67],
            ['day' => 'Rabu',   'visitors' => 380, 'pct' => 52],
        ];

        $this->assertCount(5, $result);
        $this->assertSame($expected, $result);
    }

    public function test_day_of_week_mapping(): void
    {
        for ($i = 0; $i < 10; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => Carbon::parse('2026-05-03'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 5; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => Carbon::parse('2026-05-04'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 3; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => Carbon::parse('2026-05-05'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $result = $this->callGetBusyDays(
            Carbon::parse('2026-05-01'),
            Carbon::parse('2026-05-31')
        );

        $this->assertCount(3, $result);
        $this->assertEquals('Minggu', $result[0]['day']);
        $this->assertEquals('Senin', $result[1]['day']);
        $this->assertEquals('Selasa', $result[2]['day']);
    }

    public function test_percentage_calculation(): void
    {
        for ($i = 0; $i < 100; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => Carbon::parse('2026-05-03'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        for ($i = 0; $i < 50; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => Carbon::parse('2026-05-04'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $result = $this->callGetBusyDays(
            Carbon::parse('2026-05-01'),
            Carbon::parse('2026-05-31')
        );

        $this->assertCount(2, $result);
        $this->assertEquals('Minggu', $result[0]['day']);
        $this->assertEquals(100, $result[0]['pct']);
        $this->assertEquals('Senin', $result[1]['day']);
        $this->assertEquals(50, $result[1]['pct']);
    }

    public function test_top_5_busiest_days(): void
    {
        $dates = ['2026-05-03', '2026-05-04', '2026-05-05', '2026-05-06', '2026-05-07', '2026-05-08', '2026-05-09'];
        $counts = [10, 30, 20, 50, 40, 60, 5];

        foreach ($dates as $idx => $dateStr) {
            for ($i = 0; $i < $counts[$idx]; $i++) {
                VisitorLog::create([
                    'session_id' => Str::random(16),
                    'event_type' => 'page_view',
                    'logged_at' => Carbon::parse($dateStr),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $result = $this->callGetBusyDays(
            Carbon::parse('2026-05-01'),
            Carbon::parse('2026-05-31')
        );

        $this->assertCount(5, $result);

        $visitors = array_column($result, 'visitors');
        for ($i = 0; $i < count($visitors) - 1; $i++) {
            $this->assertGreaterThanOrEqual($visitors[$i + 1], $visitors[$i]);
        }
    }
}
