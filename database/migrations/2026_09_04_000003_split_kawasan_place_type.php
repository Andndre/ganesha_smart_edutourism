<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `kawasan` menggabungkan dua hal yang metaforanya berlawanan: Koridor Desa adalah
     * jalan yang memanjang dan terbuka, sedangkan Karang Memadu adalah pekarangan
     * bertembok yang justru tertutup. Satu glyph tidak bisa mewakili keduanya — glyph
     * jalan yang dipakai malah terbaca sebagai tangga di ukuran pin.
     */
    private const OLD = ['gerbang', 'pura', 'patung', 'bale', 'monumen', 'kawasan', 'alam', 'rumah'];

    private const NEW = ['gerbang', 'pura', 'patung', 'bale', 'monumen', 'koridor', 'pekarangan', 'alam', 'rumah'];

    public function up(): void
    {
        $this->setEnum(array_unique([...self::OLD, ...self::NEW]));

        // "karang" berarti pekarangan, jadi yang sudah tertandai kawasan adalah pekarangan.
        DB::table('cultural_objects')->where('place_type', 'kawasan')->update(['place_type' => 'pekarangan']);

        $this->setEnum(self::NEW);
    }

    public function down(): void
    {
        $this->setEnum(array_unique([...self::OLD, ...self::NEW]));

        DB::table('cultural_objects')
            ->whereIn('place_type', ['koridor', 'pekarangan'])
            ->update(['place_type' => 'kawasan']);

        $this->setEnum(self::OLD);
    }

    /**
     * Enum diperlebar dulu sebelum data dipindah, baru dipersempit — kalau langsung
     * diganti, baris yang masih memakai nilai lama ditolak.
     */
    private function setEnum(array $values): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) use ($values) {
            $table->enum('place_type', $values)->nullable()->change();
        });
    }
};
