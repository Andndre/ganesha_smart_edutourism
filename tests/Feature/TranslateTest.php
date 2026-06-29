<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TranslateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_proxies_text_to_libretranslate(): void
    {
        Http::fake([
            '*/translate' => Http::response(['translatedText' => 'Public Toilet'], 200),
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/translate', [
                'q' => 'Toilet Umum',
                'source' => 'id',
                'target' => 'en',
                'format' => 'text',
            ])
            ->assertOk()
            ->assertJson(['translatedText' => 'Public Toilet']);

        Http::assertSent(fn ($req) => $req['source'] === 'id' && $req['target'] === 'en' && $req['format'] === 'text');
    }

    public function test_it_rejects_same_source_and_target(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/translate', ['q' => 'x', 'source' => 'en', 'target' => 'en'])
            ->assertStatus(422);
    }

    public function test_it_returns_502_when_service_down(): void
    {
        Http::fake(['*/translate' => Http::response('', 500)]);

        $this->actingAs(User::factory()->create())
            ->postJson('/translate', ['q' => 'x', 'source' => 'id', 'target' => 'en'])
            ->assertStatus(502);
    }

    public function test_it_requires_authentication(): void
    {
        $this->postJson('/translate', ['q' => 'x', 'source' => 'id', 'target' => 'en'])
            ->assertStatus(401);
    }
}
