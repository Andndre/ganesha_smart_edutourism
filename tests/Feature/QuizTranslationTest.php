<?php

namespace Tests\Feature;

use App\Models\CulturalObject;
use App\Models\CulturalObjectQuiz;
use App\Models\MapLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTranslationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /**
     * Test storing a cultural object with multi-language quiz options via admin form.
     */
    public function test_store_cultural_object_with_translatable_quiz_options(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.cultural-objects.store'), [
            'name' => ['en' => 'Quiz Temple', 'id' => 'Candi Kuis'],
            'short_description' => ['en' => 'A temple', 'id' => 'Sebuah candi'],
            'description' => ['en' => 'Temple desc', 'id' => 'Deskripsi candi'],
            'category' => 'temple',
            'latitude' => '-8.4450000',
            'longitude' => '115.3500000',
            'has_quiz' => '1',
            'quiz_question' => [
                0 => ['en' => 'What is this?', 'id' => 'Apa ini?'],
            ],
            'quiz_option_a' => [0 => ['en' => 'Temple', 'id' => 'Candi']],
            'quiz_option_b' => [0 => ['en' => 'House', 'id' => 'Rumah']],
            'quiz_option_c' => [0 => ['en' => 'River', 'id' => 'Sungai']],
            'quiz_option_d' => [0 => ['en' => 'Tree', 'id' => 'Pohon']],
            'quiz_correct_option' => [0 => 'A'],
        ]);

        $response->assertRedirect();

        $object = CulturalObject::where('slug', 'quiz-temple')->firstOrFail();
        $this->assertCount(1, $object->quizzes);

        $quiz = $object->quizzes->first();
        $this->assertEquals(['en' => 'What is this?', 'id' => 'Apa ini?'], $quiz->getTranslations('question'));
        $this->assertEquals(['en' => 'Temple', 'id' => 'Candi'], $quiz->getTranslations('option_a'));
        $this->assertEquals(['en' => 'House', 'id' => 'Rumah'], $quiz->getTranslations('option_b'));
        $this->assertEquals(['en' => 'River', 'id' => 'Sungai'], $quiz->getTranslations('option_c'));
        $this->assertEquals(['en' => 'Tree', 'id' => 'Pohon'], $quiz->getTranslations('option_d'));
        $this->assertEquals('A', $quiz->correct_option);
    }

    /**
     * Test updating a cultural object replaces quiz options with translatable data.
     */
    public function test_update_cultural_object_with_translatable_quiz_options(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Test Place', 'id' => 'Tempat Uji'],
            'slug' => 'test-place',
            'short_description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'category' => 'temple',
        ]);
        MapLocation::create([
            'locationable_type' => CulturalObject::class,
            'locationable_id' => $object->id,
            'name' => 'Test Place',
            'latitude' => -8.445,
            'longitude' => 115.35,
            'category' => 'cultural',
        ]);

        // Add initial quiz
        CulturalObjectQuiz::create([
            'cultural_object_id' => $object->id,
            'question' => ['en' => 'Old question', 'id' => 'Pertanyaan lama'],
            'option_a' => ['en' => 'A1', 'id' => 'A1-id'],
            'option_b' => ['en' => 'B1', 'id' => 'B1-id'],
            'option_c' => ['en' => 'C1', 'id' => 'C1-id'],
            'option_d' => ['en' => 'D1', 'id' => 'D1-id'],
            'correct_option' => 'A',
        ]);

        // Update with new quiz
        $response = $this->actingAs($this->admin)->put(route('admin.cultural-objects.update', $object), [
            'name' => ['en' => 'Test Place', 'id' => 'Tempat Uji'],
            'short_description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'category' => 'temple',
            'latitude' => '-8.4450000',
            'longitude' => '115.3500000',
            'has_quiz' => '1',
            'quiz_question' => [
                0 => ['en' => 'New question?', 'id' => 'Pertanyaan baru?'],
            ],
            'quiz_option_a' => [0 => ['en' => 'Yes', 'id' => 'Ya']],
            'quiz_option_b' => [0 => ['en' => 'No', 'id' => 'Tidak']],
            'quiz_option_c' => [0 => ['en' => 'Maybe', 'id' => 'Mungkin']],
            'quiz_option_d' => [0 => ['en' => 'Unknown', 'id' => 'Tidak diketahui']],
            'quiz_correct_option' => [0 => 'B'],
        ]);

        $response->assertRedirect();

        $object->refresh();
        $this->assertCount(1, $object->quizzes);

        $quiz = $object->quizzes->first();
        $this->assertEquals(['en' => 'New question?', 'id' => 'Pertanyaan baru?'], $quiz->getTranslations('question'));
        $this->assertEquals(['en' => 'Yes', 'id' => 'Ya'], $quiz->getTranslations('option_a'));
        $this->assertEquals('B', $quiz->correct_option);
    }

    /**
     * Test quiz options are serialized as translation objects in JSON response.
     */
    public function test_quiz_options_returned_as_translations_in_json(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'JSON Test', 'id' => 'Uji JSON'],
            'slug' => 'json-test',
            'short_description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'category' => 'tradition',
        ]);
        CulturalObjectQuiz::create([
            'cultural_object_id' => $object->id,
            'question' => ['en' => 'What culture?', 'id' => 'Budaya apa?'],
            'option_a' => ['en' => 'Balinese', 'id' => 'Bali'],
            'option_b' => ['en' => 'Javanese', 'id' => 'Jawa'],
            'option_c' => ['en' => 'Sundanese', 'id' => 'Sunda'],
            'option_d' => ['en' => 'Other', 'id' => 'Lainnya'],
            'correct_option' => 'A',
        ]);

        $quiz = CulturalObjectQuiz::first();

        // When serialized to JSON, option fields should be objects with locale keys
        $json = $quiz->toArray();
        $this->assertIsArray($json['option_a']);
        $this->assertArrayHasKey('en', $json['option_a']);
        $this->assertArrayHasKey('id', $json['option_a']);
        $this->assertEquals('Balinese', $json['option_a']['en']);
        $this->assertEquals('Bali', $json['option_a']['id']);
    }

    /**
     * Test multiple quizzes with different translations per option.
     */
    public function test_multiple_quizzes_each_with_unique_translations(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.cultural-objects.store'), [
            'name' => ['en' => 'Multi Quiz', 'id' => 'Multi Kuis'],
            'short_description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'category' => 'craft',
            'latitude' => '-8.4450000',
            'longitude' => '115.3500000',
            'has_quiz' => '1',
            'quiz_question' => [
                0 => ['en' => 'Question A?', 'id' => 'Pertanyaan A?'],
                1 => ['en' => 'Question B?', 'id' => 'Pertanyaan B?'],
            ],
            'quiz_option_a' => [
                0 => ['en' => 'Q1 Opt A', 'id' => 'Q1 Opsi A'],
                1 => ['en' => 'Q2 Opt A', 'id' => 'Q2 Opsi A'],
            ],
            'quiz_option_b' => [
                0 => ['en' => 'Q1 Opt B', 'id' => 'Q1 Opsi B'],
                1 => ['en' => 'Q2 Opt B', 'id' => 'Q2 Opsi B'],
            ],
            'quiz_option_c' => [
                0 => ['en' => 'Q1 Opt C', 'id' => 'Q1 Opsi C'],
                1 => ['en' => 'Q2 Opt C', 'id' => 'Q2 Opsi C'],
            ],
            'quiz_option_d' => [
                0 => ['en' => 'Q1 Opt D', 'id' => 'Q1 Opsi D'],
                1 => ['en' => 'Q2 Opt D', 'id' => 'Q2 Opsi D'],
            ],
            'quiz_correct_option' => [0 => 'A', 1 => 'C'],
        ]);

        $response->assertRedirect();

        $object = CulturalObject::where('slug', 'multi-quiz')->firstOrFail();
        $this->assertCount(2, $object->quizzes);

        $quiz1 = $object->quizzes->first();
        $this->assertEquals(['en' => 'Question A?', 'id' => 'Pertanyaan A?'], $quiz1->getTranslations('question'));
        $this->assertEquals(['en' => 'Q1 Opt A', 'id' => 'Q1 Opsi A'], $quiz1->getTranslations('option_a'));
        $this->assertEquals('A', $quiz1->correct_option);

        $quiz2 = $object->quizzes->last();
        $this->assertEquals(['en' => 'Question B?', 'id' => 'Pertanyaan B?'], $quiz2->getTranslations('question'));
        $this->assertEquals(['en' => 'Q2 Opt D', 'id' => 'Q2 Opsi D'], $quiz2->getTranslations('option_d'));
        $this->assertEquals('C', $quiz2->correct_option);
    }

    /**
     * Test backward compatibility: creating quiz with plain string options (legacy).
     */
    public function test_quiz_model_accepts_plain_string_options_for_backward_compat(): void
    {
        $object = CulturalObject::create([
            'name' => ['en' => 'Legacy Quiz', 'id' => 'Kuis Lama'],
            'slug' => 'legacy-quiz',
            'short_description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'description' => ['en' => 'Desc', 'id' => 'Deskripsi'],
            'category' => 'house',
        ]);

        // Simulate legacy creation with plain strings
        $quiz = CulturalObjectQuiz::create([
            'cultural_object_id' => $object->id,
            'question' => ['en' => 'Question?', 'id' => 'Pertanyaan?'],
            'option_a' => 'Plain A',
            'option_b' => 'Plain B',
            'option_c' => 'Plain C',
            'option_d' => 'Plain D',
            'correct_option' => 'A',
        ]);

        // Should still store and retrieve correctly
        $quiz->refresh();
        $this->assertNotEmpty($quiz->option_a);
        $this->assertEquals('A', $quiz->correct_option);
    }
}
