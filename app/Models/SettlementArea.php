<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $name
 * @property int $year
 * @property string|null $province
 * @property string|null $regency
 * @property string|null $district
 * @property string|null $village
 * @property int $settlement_count
 * @property float $area_size
 * @property string|null $geojson_file
 * @property array|null $geojson_data
 * @property string $status
 * @property string|null $description
 */
class SettlementArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'year', 'province', 'regency', 'district', 'village',
        'settlement_count', 'area_size',
        'geojson_file', 'geojson_data', 'status', 'description', 'updated_by',
    ];

    protected $casts = [
        'geojson_data' => 'array',
        'area_size' => 'float',
        'year' => 'int',
        'settlement_count' => 'int',
    ];

    /** @return BelongsTo<User, self> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Filter data permukiman agar sesuai dengan satu wilayah administrasi.
     * Pencocokan pakai UPPER(TRIM(...)) agar nama dari AdministrativeArea
     * tetap cocok walau penulisan huruf/spasi di settlement_areas berbeda.
     */
    public function scopeForAdministrativeArea($query, AdministrativeArea $area)
    {
        return match ($area->level) {
            AdministrativeArea::LEVEL_PROVINCE => $query->whereRaw("UPPER(TRIM(province)) = ?", [\Illuminate\Support\Str::upper(trim((string) $area->name))]),
            AdministrativeArea::LEVEL_REGENCIES => $query->whereRaw("UPPER(TRIM(regency)) = ?", [\Illuminate\Support\Str::upper(trim((string) $area->name))]),
            AdministrativeArea::LEVEL_DISTRICTS => $query->whereRaw("UPPER(TRIM(district)) = ?", [\Illuminate\Support\Str::upper(trim((string) $area->name))]),
            AdministrativeArea::LEVEL_VILLAGES => $query
                ->whereRaw("UPPER(TRIM(village)) = ?", [\Illuminate\Support\Str::upper(trim((string) $area->name))])
                ->whereRaw("UPPER(TRIM(district)) = ?", [\Illuminate\Support\Str::upper(trim((string) $area->parent?->name ?? ''))]),
            default => $query,
        };
    }
}
