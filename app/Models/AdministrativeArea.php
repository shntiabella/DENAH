<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string|null $code
 * @property string $level
 * @property string $name
 * @property int|null $parent_id
 * @property float|null $lat
 * @property float|null $lng
 * @property array|null $geojson
 */
class AdministrativeArea extends Model
{
    use HasFactory;

    public const LEVEL_PROVINCE = 'provinsi';
    public const LEVEL_REGENCIES = 'kabupaten';
    public const LEVEL_DISTRICTS = 'kecamatan';
    public const LEVEL_VILLAGES = 'desa';

    protected $fillable = ['code', 'level', 'name', 'parent_id', 'lat', 'lng', 'geojson'];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'geojson' => 'array',
    ];

    public const LEVELS = [
        self::LEVEL_PROVINCE => 'Provinsi',
        self::LEVEL_REGENCIES => 'Kabupaten/Kota',
        self::LEVEL_DISTRICTS => 'Kecamatan',
        self::LEVEL_VILLAGES => 'Desa/Kelurahan',
    ];

    /** @return BelongsTo<self, self> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<self> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return BelongsTo<self, self> */
    public function province(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->where('level', self::LEVEL_PROVINCE);
    }

    /** @return BelongsTo<self, self> */
    public function regency(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->where('level', self::LEVEL_REGENCIES);
    }

    /** @return BelongsTo<self, self> */
    public function district(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->where('level', self::LEVEL_DISTRICTS);
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->level] ?? ucfirst($this->level);
    }

    public function fullName(): string
    {
        $parts = [$this->name];
        $parent = $this->parent;
        $guard = 0;
        while ($parent && $guard < 4) {
            array_unshift($parts, $parent->name);
            $parent = $parent->parent;
            $guard++;
        }

        return implode(', ', $parts);
    }

    /**
     * GeoJSON Feature untuk layer Leaflet, dengan properties wilayah.
     *
     * @return array{type: string, properties: array<string, mixed>, geometry: array|null}
     */
    public function toGeoJsonFeature(): array
    {
        $geo = $this->geojson;

        return [
            'type' => 'Feature',
            'properties' => [
                'id' => $this->id,
                'code' => $this->code,
                'name' => $this->name,
                'level' => $this->level,
                'parent_id' => $this->parent_id,
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'geometry' => is_array($geo) && isset($geo['type'], $geo['coordinates']) ? $geo : null,
        ];
    }
}
