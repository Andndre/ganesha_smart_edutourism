<?php

namespace Tests\Feature;

use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackVisitorLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_one_page_view_per_session_per_day(): void
    {
        $this->get('/')->assertOk();
        $this->get('/explore')->assertOk();

        $this->assertSame(1, VisitorLog::where('event_type', 'page_view')->count());
    }
}
