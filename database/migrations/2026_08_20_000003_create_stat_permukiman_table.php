<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stat_permukiman', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->string('regency', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('village', 100)->nullable();
            $table->decimal('luas_ha', 14, 4)->default(0);
            $table->unsignedInteger('jumlah_lokasi')->default(0);
            $table->unsignedInteger('jumlah_records')->default(0);
            $table->timestamps();

            $table->unique(['year', 'regency', 'district', 'village'], 'stat_permukiman_unique');
            $table->index(['year', 'regency'], 'stat_permukiman_year_regency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stat_permukiman');
    }
};
