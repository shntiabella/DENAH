<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settlement_areas', function (Blueprint $table) {
            $table->index(['status', 'year', 'regency', 'district', 'village'], 'idx_settlement_filter');
            $table->index(['status', 'year', 'regency'], 'idx_settlement_regency_year');
            $table->index(['status', 'year', 'district'], 'idx_settlement_district_year');
            $table->index(['status', 'year', 'village'], 'idx_settlement_village_year');
            $table->index(['status', 'regency', 'district', 'village'], 'idx_settlement_wilayah');
        });
    }

    public function down(): void
    {
        Schema::table('settlement_areas', function (Blueprint $table) {
            $table->dropIndex('idx_settlement_filter');
            $table->dropIndex('idx_settlement_regency_year');
            $table->dropIndex('idx_settlement_district_year');
            $table->dropIndex('idx_settlement_village_year');
            $table->dropIndex('idx_settlement_wilayah');
        });
    }
};
