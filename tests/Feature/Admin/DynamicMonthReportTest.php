<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DynamicMonthReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = DB::connection()->getPdo();
        $pdo->sqliteCreateFunction('DAYOFWEEK', function (string $datetime) {
            return (int) date('w', strtotime($datetime)) + 1;
        });
    }

    public function test_default_period_is_current_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $expectedMonth = Carbon::now()->locale('id')->isoFormat('MMMM YYYY');

        $response = $this->actingAs($admin)->get(route('admin.reports'));

        $response->assertStatus(200);
        $response->assertSee($expectedMonth);
    }

    public function test_custom_period_overrides_default(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lastMonth = Carbon::now()->subMonth()->locale('id')->isoFormat('MMMM YYYY');

        $response = $this->actingAs($admin)->get(route('admin.reports', ['period' => $lastMonth]));

        $response->assertStatus(200);
        $response->assertSee($lastMonth);
    }

    public function test_month_year_parsing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $testDate = Carbon::parse('2026-05-15');
        $period = 'Mei 2026';

        for ($i = 0; $i < 5; $i++) {
            VisitorLog::create([
                'session_id' => Str::random(16),
                'event_type' => 'page_view',
                'logged_at' => $testDate,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.reports', ['period' => $period]));

        $response->assertStatus(200);
        $response->assertSee($period);
    }

    public function test_download_pdf_also_dynamic(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.reports.download', ['period' => 'Mei 2026']));

        $response->assertStatus(200);
        $response->assertSee('Laporan Mei 2026');
    }
}
