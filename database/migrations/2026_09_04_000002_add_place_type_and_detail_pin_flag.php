<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dua sumbu baru untuk pin objek budaya:
     *
     * - place_type memilih glyph pin. Nullable supaya objek lama jatuh balik ke
     *   candi bentar yang selama ini dipakai, tanpa perlu backfill.
     * - is_detail menandai objek tingkat 2, yaitu komponen di dalam pekarangan rumah
     *   adat (angkul-angkul, paon, merajan, saka enam). Satu objek seperti itu dipin
     *   di setiap rumah, sehingga di zoom jauh puluhan pin kembar menutupi peta.
     *   Pin milik objek bertanda ini hanya dirender saat zoom sudah cukup dekat.
     *
     *   Sengaja di objeknya, bukan di map_locations: seluruh pin sebuah komponen
     *   berperilaku sama, jadi menandainya per pin berarti admin mencentang puluhan
     *   kali untuk hasil yang identik.
     *
     *   Geofence edutourism membaca map_locations langsung tanpa menyaring kolom ini,
     *   jadi radius "sudah tiba" tidak berubah sama sekali.
     */
    public function up(): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->enum('place_type', [
                'gerbang',
                'pura',
                'patung',
                'bale',
                'monumen',
                'kawasan',
                'alam',
                'rumah',
            ])->nullable()->after('category');

            $table->boolean('is_detail')->default(false)->after('place_type')->index();
        });
    }

    public function down(): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->dropIndex(['is_detail']);
            $table->dropColumn(['place_type', 'is_detail']);
        });
    }
};
