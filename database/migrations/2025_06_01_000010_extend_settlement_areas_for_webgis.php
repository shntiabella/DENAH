<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_areas', function (Blueprint $table) {
            $table->unsignedInteger('year')->default(now()->year)->after('name')->index();
            $table->string('province', 100)->nullable()->after('year');
            $table->string('regency', 100)->nullable()->after('province')->index();
            $table->string('district', 100)->nullable()->after('regency')->index();
            $table->string('village', 100)->nullable()->after('district')->index();
            $table->unsignedInteger('settlement_count')->default(0)->after('village')->comment('Jumlah lokasi permukiman');
            $table->string('confidence', 20)->nullable()->after('settlement_count')->comment('Status/keyakinan data: Tinggi/Sedang/Rendah');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_areas', function (Blueprint $table) {
            $table->dropColumn(['year', 'province', 'regency', 'district', 'village', 'settlement_count', 'confidence']);
        });
    }
};
