<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        Schema::create('settlement_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('classification_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('area_size', 12, 4)->default(0)->comment('Luas dalam hektar (Ha)');
            $table->string('geojson_file')->nullable()->comment('Path file di storage');
            $table->json('geojson_data')->nullable()->comment('Isi GeoJSON untuk query cepat');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_areas');
        Schema::dropIfExists('classifications');
    }
};