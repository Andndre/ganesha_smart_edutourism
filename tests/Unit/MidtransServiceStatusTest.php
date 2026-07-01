<?php

namespace Tests\Unit;

use App\Services\MidtransService;
use Tests\TestCase;

/**
 * Verifies the paid/cancelled status classification now shared by
 * TicketingController and BookingController (was duplicated inline 4x).
 */
class MidtransServiceStatusTest extends TestCase
{
    public function test_paid_statuses(): void
    {
        $this->assertTrue(MidtransService::isPaidStatus('capture'));
        $this->assertTrue(MidtransService::isPaidStatus('settlement'));
        $this->assertFalse(MidtransService::isPaidStatus('pending'));
        $this->assertFalse(MidtransService::isPaidStatus(null));
    }

    public function test_cancelled_statuses(): void
    {
        $this->assertTrue(MidtransService::isCancelledStatus('cancel'));
        $this->assertTrue(MidtransService::isCancelledStatus('deny'));
        $this->assertTrue(MidtransService::isCancelledStatus('expire'));
        $this->assertFalse(MidtransService::isCancelledStatus('settlement'));
        $this->assertFalse(MidtransService::isCancelledStatus(null));
    }
}
