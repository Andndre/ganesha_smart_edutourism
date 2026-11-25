<?php

namespace Tests\Unit;

use App\Models\ArModel;
use App\Models\CulturalObject;
use Tests\TestCase;

/**
 * Verifies the shared attributesToArray() override (used by CulturalObject)
 * returns translatable fields as full {en, id} arrays instead of raw JSON strings.
 */
class HasTranslatableArrayOutputTest extends TestCase
{
    public function test_ar_model_reads_localized_content_from_its_cultural_object(): void
    {
        app()->setLocale('id');
        $object = new CulturalObject(['name' => ['en' => 'Statue', 'id' => 'Patung']]);
        $model = new ArModel;
        $model->setRelation('culturalObject', $object);

        $this->assertSame('Patung', $model->name);
        $this->assertArrayNotHasKey('name', $model->attributesToArray());
    }

    public function test_cultural_object_serializes_translatable_fields_as_locale_array(): void
    {
        $model = new CulturalObject(['name' => ['en' => 'Temple', 'id' => 'Pura']]);

        $array = $model->attributesToArray();

        $this->assertSame(['en' => 'Temple', 'id' => 'Pura'], $array['name']);
    }
}
