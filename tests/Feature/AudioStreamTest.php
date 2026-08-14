<?php

namespace Tests\Feature;

use Tests\TestCase;

class AudioStreamTest extends TestCase
{
    public function test_it_streams_a_file_inside_public_storage(): void
    {
        $path = storage_path('app/public/audio-stream-test.mp3');
        @mkdir(\dirname($path), 0777, true);
        file_put_contents($path, 'fake-audio');

        try {
            $this->get('/audio-stream/audio-stream-test.mp3')->assertOk();
        } finally {
            @unlink($path);
        }
    }

    public function test_it_rejects_traversal_outside_public_storage(): void
    {
        // The route pattern is `.*`, so the controller is the only thing standing between an
        // unauthenticated request and any readable file on the box.
        $this->get('/audio-stream/../../../.env')->assertNotFound();
    }
}
