<?php
// One-shot, non-destructive repair for translatable fields that have their
// Indonesian value misfiled under the `en` key with no `id` key (the seeder bug).
// For each such field: copy en -> id so the ID tab is correct. `en` is left as-is;
// translate it afterwards (manually or via the LibreTranslate feature).
//
// Run:  php artisan tinker storage/repair_locales.php
// Idempotent: re-running changes nothing once `id` keys exist.

use App\Models\CulturalObject;
use App\Models\Event;

$targets = [
    CulturalObject::class => ['name', 'short_description', 'description'],
    Event::class          => ['name', 'description', 'location_name'],
];

$fixed = 0;
foreach ($targets as $model => $fields) {
    foreach ($model::all() as $row) {
        $dirty = false;
        foreach ($fields as $field) {
            $tr = $row->getTranslations($field);          // ['en' => ...] or ['en'=>...,'id'=>...]
            if (isset($tr['en']) && empty($tr['id'])) {   // en present, id missing/blank
                $row->setTranslation($field, 'id', $tr['en']);
                $dirty = true;
            }
        }
        if ($dirty) {
            $row->save();
            $fixed++;
            echo "fixed {$model}#{$row->id}\n";
        }
    }
}
echo "Done. Rows repaired: {$fixed}\n";
