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
    Schema::table('users', function (Blueprint $table) {
      $table->enum('role', ['tourist', 'umkm_owner', 'admin'])->default('tourist')->after('password');
      $table->string('phone')->nullable()->after('role');
      $table->string('nationality')->nullable()->after('phone');
      $table->string('preferred_language', 10)->default('id')->after('nationality');
      $table->string('avatar_path')->nullable()->after('preferred_language');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn(['role', 'phone', 'nationality', 'preferred_language', 'avatar_path']);
    });
  }
};
