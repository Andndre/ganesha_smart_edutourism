<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hierarki konten objek budaya: satu induk (mis. Paon), banyak anak (tungku,
     * alat masak). Anak tetap objek penuh dengan slug, audio, AR, dan pin sendiri.
     *
     * Sengaja terpisah dari `is_detail`: `is_detail` mengatur cara render pin
     * (zoom-gated), `parent_id` mengatur hubungan konten. Paon adalah komponen yang
     * muncul di setiap rumah, jadi ia perlu `is_detail` sendiri meski tidak punya induk.
     *
     * `nullOnDelete` hanya jaring pengaman; controller menolak menghapus induk yang
     * masih punya anak supaya kontennya tidak hilang diam-diam.
     */
    public function up(): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('slug')
                ->constrained('cultural_objects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
