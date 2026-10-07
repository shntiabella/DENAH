<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus relasi klasifikasi & field confidence dari data permukiman,
        // lalu hapus tabel classifications (konsep label kepadatan dihapus).
        Schema::table('settlement_areas', function (Blueprint $table) {
            $table->dropForeign(['classification_id']);
            $table->dropColumn(['classification_id', 'confidence']);
        });

        Schema::dropIfExists('classifications');
    }

    public function down(): void
    {
        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legend_color', 20)->default('#C17F52');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('settlement_areas', function (Blueprint $table) {
            $table->foreignId('classification_id')->nullable()->after('name')->constrained()->nullOnDelete();
            $table->string('confidence', 20)->nullable()->after('settlement_count');
        });
    }
};