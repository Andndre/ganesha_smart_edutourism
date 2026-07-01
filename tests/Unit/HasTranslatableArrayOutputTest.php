<?php

namespace Tests\Unit;

use App\Models\ArModel;
use App\Models\CulturalObject;
use Tests\TestCase;

/**
 * Verifies the shared attributesToArray() override (used by ArModel and CulturalObject)
 * returns translatable fields as full {en, id} arrays instead of raw JSON strings.
 */
class HasTranslatableArrayOutputTest extends TestCase
{
    public function test_ar_model_serializes_translatable_fields_as_locale_array(): void
    {
        $model = new ArModel(['name' => ['en' => 'Statue', 'id' => 'Patung']]);

        $array = $model->attributesToArray();

        $this->assertSame(['en' => 'Statue', 'id' => 'Patung'], $array['name']);
    }

    public function test_cultural_object_serializes_translatable_fields_as_locale_array(): void
    {
        $model = new CulturalObject(['name' => ['en' => 'Temple', 'id' => 'Pura']]);

        $array = $model->attributesToArray();

        $this->assertSame(['en' => 'Temple', 'id' => 'Pura'], $array['name']);
    }
}
