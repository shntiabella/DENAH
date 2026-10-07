<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_contents', function (Blueprint $table) {
            $table->id();
            $table->string('badge_text')->default('Pemetaan & Klasifikasi Permukiman');
            $table->string('headline_1')->default('Jelajahi Persebaran');
            $table->string('headline_2')->default('Permukiman');
            $table->text('subheadline')->nullable();
            $table->string('cta_primary_text')->default('Jelajahi Peta');
            $table->string('cta_secondary_text')->default('Lihat Statistik');
            $table->string('coordinates')->default("7°56'15\"S | 112°37'35\"E");
            $table->string('background_image')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('hero_badges', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('about_contents', function (Blueprint $table) {
            $table->id();
            $table->longText('body')->nullable();
            $table->string('image')->nullable();
            $table->string('more_link_text')->default('Selengkapnya');
            $table->string('more_link_url')->default('#tentang');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->decimal('value', 14, 2)->default(0);
            $table->string('unit')->nullable();
            $table->string('icon')->default('area');
            $table->boolean('counter')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stats');
        Schema::dropIfExists('about_contents');
        Schema::dropIfExists('hero_badges');
        Schema::dropIfExists('hero_contents');
    }
};