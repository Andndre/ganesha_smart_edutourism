<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate existing plain-string option values to JSON {en: ..., id: ...}
        $rows = DB::table('cultural_object_quizzes')->select('id', 'option_a', 'option_b', 'option_c', 'option_d')->get();
        foreach ($rows as $row) {
            $wrap = fn ($val) => json_decode($val, true) !== null ? $val : json_encode(['en' => $val, 'id' => $val]);
            DB::table('cultural_object_quizzes')->where('id', $row->id)->update([
                'option_a' => $wrap($row->option_a),
                'option_b' => $wrap($row->option_b),
                'option_c' => $wrap($row->option_c),
                'option_d' => $wrap($row->option_d),
            ]);
        }
    }

    public function down(): void
    {
        // Flatten JSON back to plain strings (take 'en' value)
        $rows = DB::table('cultural_object_quizzes')->select('id', 'option_a', 'option_b', 'option_c', 'option_d')->get();
        foreach ($rows as $row) {
            $flatten = function ($val) {
                $decoded = json_decode($val, true);

                return \is_array($decoded) ? ($decoded['en'] ?? '') : $val;
            };
            DB::table('cultural_object_quizzes')->where('id', $row->id)->update([
                'option_a' => $flatten($row->option_a),
                'option_b' => $flatten($row->option_b),
                'option_c' => $flatten($row->option_c),
                'option_d' => $flatten($row->option_d),
            ]);
        }
    }
};
