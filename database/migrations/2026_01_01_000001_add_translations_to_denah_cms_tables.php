<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan kolom multi-bahasa (`_id` / `_en`) untuk tabel konten CMS.
 *
 * Kolom dasar (mis. `title`) tetap dipertahankan sebagai fallback agar data
 * lama tidak hilang. Versi `_id` memuat teks Bahasa Indonesia, `_en` English.
 */
return new class extends Migration
{
    public function up(): void
    {
        // hero_contents
        Schema::table('hero_contents', function (Blueprint $table) {
            foreach (['badge_text', 'headline_1', 'headline_2', 'cta_primary_text', 'cta_secondary_text'] as $col) {
                $table->string($col.'_id', 120)->nullable()->after($col);
                $table->string($col.'_en', 120)->nullable()->after($col.'_id');
            }
            $table->text('subheadline_id')->nullable()->after('subheadline');
            $table->text('subheadline_en')->nullable()->after('subheadline_id');
        });

        // about_contents
        Schema::table('about_contents', function (Blueprint $table) {
            $table->longText('body_id')->nullable()->after('body');
            $table->longText('body_en')->nullable()->after('body_id');
            $table->string('more_link_text_id', 40)->nullable()->after('more_link_text');
            $table->string('more_link_text_en', 40)->nullable()->after('more_link_text_id');
        });

        // features
        Schema::table('features', function (Blueprint $table) {
            $table->string('title_id', 80)->nullable()->after('title');
            $table->string('title_en', 80)->nullable()->after('title_id');
            $table->text('description_id')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_id');
        });

        // methodology_steps
        Schema::table('methodology_steps', function (Blueprint $table) {
            $table->string('title_id', 80)->nullable()->after('title');
            $table->string('title_en', 80)->nullable()->after('title_id');
            $table->text('description_id')->nullable()->after('description');
            $table->text('description_en')->nullable()->after('description_id');
        });

        // methodology_contents
        Schema::table('methodology_contents', function (Blueprint $table) {
            $table->longText('body_id')->nullable()->after('body');
            $table->longText('body_en')->nullable()->after('body_id');
        });

        // stats
        Schema::table('stats', function (Blueprint $table) {
            $table->string('label_id', 60)->nullable()->after('label');
            $table->string('label_en', 60)->nullable()->after('label_id');
            $table->string('unit_id', 20)->nullable()->after('unit');
            $table->string('unit_en', 20)->nullable()->after('unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('hero_contents', function (Blueprint $table) {
            $table->dropColumn([
                'badge_text_id', 'badge_text_en',
                'headline_1_id', 'headline_1_en',
                'headline_2_id', 'headline_2_en',
                'subheadline_id', 'subheadline_en',
                'cta_primary_text_id', 'cta_primary_text_en',
                'cta_secondary_text_id', 'cta_secondary_text_en',
            ]);
        });

        Schema::table('about_contents', function (Blueprint $table) {
            $table->dropColumn(['body_id', 'body_en', 'more_link_text_id', 'more_link_text_en']);
        });

        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn(['title_id', 'title_en', 'description_id', 'description_en']);
        });

        Schema::table('methodology_steps', function (Blueprint $table) {
            $table->dropColumn(['title_id', 'title_en', 'description_id', 'description_en']);
        });

        Schema::table('methodology_contents', function (Blueprint $table) {
            $table->dropColumn(['body_id', 'body_en']);
        });

        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn(['label_id', 'label_en', 'unit_id', 'unit_en']);
        });
    }
};
