<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administrative_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable()->comment('Kode wilayah (mis. 17.01.02.001)');
            $table->string('level', 20)->comment('provinsi | kabupaten | kecamatan | desa');
            $table->string('name', 150);
            $table->foreignId('parent_id')->nullable()->constrained('administrative_areas')->nullOnDelete();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->json('geojson')->nullable()->comment('GeoJSON polygon batas wilayah');
            $table->timestamps();

            $table->index(['level', 'name']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrative_areas');
    }
};
