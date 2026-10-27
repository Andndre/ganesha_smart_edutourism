<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\RegistersDayOfWeekFunction;
use Tests\TestCase;

class AdminCulturalObjectAudioTest extends TestCase
{
    use RefreshDatabase;
    use RegistersDayOfWeekFunction;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registerDayOfWeekFunction();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_edit_page_renders_audio_player_initialization_for_existing_audio(): void
    {
        $object = CulturalObject::factory()->create([
            'audio_narration_paths' => [
                'id' => 'audio/narration_id.mp3',
                'en' => 'audio/narration_en.3gp',
            ],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.cultural-objects.edit', $object->id));

        $response->assertOk();
        $response->assertSee("window.setMiniAudio('current-audio-id', \"audio\/narration_id.mp3\");", false);
        $response->assertSee("window.setMiniAudio('current-audio-en', \"audio\/narration_en.3gp\");", false);
        $response->assertSee('accept=".mp3,.ogg,.wav,.m4a,.3gp,.3gpp', false);
    }

    public function test_update_accepts_3gp_audio_file_and_stores_it(): void
    {
        Storage::fake('public');

        $object = CulturalObject::factory()->create();

        // 3GP audio file
        $audioFile = UploadedFile::fake()->create('narration.3gp', 500, 'audio/3gpp');

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.cultural-objects.update', $object->id), [
                'redirect_to' => 'cultural-objects',
                'name' => ['en' => 'Updated Object', 'id' => 'Objek Diperbarui'],
                'category' => 'pawongan',
                'cultural_audio_file' => [
                    'id' => $audioFile,
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.cultural-objects'));

        $fresh = $object->fresh();
        $this->assertNotNull($fresh->audio_narration_paths);
        $this->assertArrayHasKey('id', $fresh->audio_narration_paths);
        Storage::disk('public')->assertExists($fresh->audio_narration_paths['id']);
    }

    public function test_store_accepts_3gp_audio_file(): void
    {
        Storage::fake('public');

        $audioFile = UploadedFile::fake()->create('new_narration.3gp', 300, 'video/3gpp');

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.cultural-objects.store'), [
                'redirect_to' => 'cultural-objects',
                'name' => ['en' => 'New Cultural Site', 'id' => 'Situs Budaya Baru'],
                'category' => 'parahyangan',
                'cultural_audio_file' => [
                    'en' => $audioFile,
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.cultural-objects'));

        $created = CulturalObject::where('name->en', 'New Cultural Site')->first();
        $this->assertNotNull($created);
        $this->assertNotNull($created->audio_narration_paths);
        $this->assertArrayHasKey('en', $created->audio_narration_paths);
        Storage::disk('public')->assertExists($created->audio_narration_paths['en']);
    }

    public function test_update_succeeds_without_audio_files_keeping_existing_paths(): void
    {
        $object = CulturalObject::factory()->create([
            'audio_narration_paths' => [
                'id' => 'audio/keep_me.mp3',
            ],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.cultural-objects.update', $object->id), [
                'name' => ['en' => 'Just Name Update', 'id' => 'Update Nama Saja'],
                'category' => 'pawongan',
            ]);

        $response->assertSessionHasNoErrors();
        $fresh = $object->fresh();
        $this->assertEquals(['id' => 'audio/keep_me.mp3'], $fresh->audio_narration_paths);
    }
}
