<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom judul multi-bahasa (`title`, `title_id`, `title_en`)
 * pada tabel `methodology_contents` agar judul section Metodologi di halaman
 * Home & halaman `/metodologi` dapat dikelola sepenuhnya melalui CMS dan
 * mendukung pergantian bahasa ID/EN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('methodology_contents', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->string('title_id')->nullable()->after('title');
            $table->string('title_en')->nullable()->after('title_id');
        });
    }

    public function down(): void
    {
        Schema::table('methodology_contents', function (Blueprint $table) {
            $table->dropColumn(['title', 'title_id', 'title_en']);
        });
    }
};