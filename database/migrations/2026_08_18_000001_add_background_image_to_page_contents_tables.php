<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // hero_contents sudah memiliki kolom background_image (2025_01_01_000011).
        // Peta & Statistik memakai tabel settings (key-value), tidak perlu kolom baru.

        Schema::table('about_contents', function (Blueprint $table) {
            $table->string('background_image')->nullable()->after('image');
        });

        Schema::table('methodology_contents', function (Blueprint $table) {
            $table->string('background_image')->nullable()->after('body_id');
        });
    }

    public function down(): void
    {
        Schema::table('about_contents', function (Blueprint $table) {
            $table->dropColumn('background_image');
        });

        Schema::table('methodology_contents', function (Blueprint $table) {
            $table->dropColumn('background_image');
        });
    }
};
