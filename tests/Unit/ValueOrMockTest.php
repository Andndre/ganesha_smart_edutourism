<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Verifies the shared valueOrMock() helper (app/helpers.php), now used by
 * DashboardController, ReportController, and FeedbackController instead of
 * repeating "if ($x === 0) { $x = <mock>; }" 14+ times.
 */
class ValueOrMockTest extends TestCase
{
    public function test_returns_real_value_when_truthy(): void
    {
        $this->assertSame(42, valueOrMock(42, 617));
    }

    public function test_returns_mock_when_zero(): void
    {
        $this->assertSame(617, valueOrMock(0, 617));
    }

    public function test_returns_mock_when_null(): void
    {
        $this->assertSame(4.7, valueOrMock(null, 4.7));
    }
}
