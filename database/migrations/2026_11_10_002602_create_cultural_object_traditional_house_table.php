<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cultural_object_traditional_house', function (Blueprint $table) {
            $table->foreignId('traditional_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cultural_object_id')->constrained()->cascadeOnDelete();
            $table->primary(['traditional_house_id', 'cultural_object_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_object_traditional_house');
    }
};
