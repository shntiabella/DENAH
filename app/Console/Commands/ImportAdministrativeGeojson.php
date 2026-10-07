<?php

namespace App\Console\Commands;

use App\Models\AdministrativeArea;
use App\Support\GeoJsonStreamReader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Import polygon boundary geometries from a local .geojson file into
 * administrative_areas.geojson, matching each feature by name against the
 * administrative_areas.name column for the given level.
 *
 * Large files are streamed via GeoJsonStreamReader. Only Polygon/MultiPolygon
 * geometries are extracted. Every DB write is a qualified query()
 * (where level+name, or primary key) — never a bare update() without a WHERE.
 *
 * Examples:
 *   php artisan denah:import-admin-geojson database/boundaries.geojson --level=kecamatan
 *   php artisan denah:import-admin-geojson --file=public/data/x.geojson --level=desa --key=NAME
 */
class ImportAdministrativeGeojson extends Command
{
    protected $signature = 'denah:import-admin-geojson
        {path? : Optional path to the local .geojson file}
        {--file= : Alternative path to the local .geojson file}
        {--level=kecamatan : Target admin level: kabupaten|kecamatan|desa}
        {--key= : Property name holding the matching name (default: auto-detect)}
        {--no-centroid : Do not backfill lat/lng from the polygon centroid}
        {--dry-run : Only analyze, do not mutate the database}';

    protected $description = 'Import boundary polygon geometries from a .geojson file into administrative_areas.geojson by name matching.';

    private const POLYGON_TYPES = ['Polygon', 'MultiPolygon'];

    public function handle(): int
    {
        $path = $this->option('file') ?? $this->argument('path') ?? 'database/data/boundaries.geojson';
        $level = $this->option('level');
        $key = $this->option('key');
        $dryRun = (bool) $this->option('dry-run');
        $fillCentroid = ! (bool) $this->option('no-centroid');

        if (! in_array($level, [AdministrativeArea::LEVEL_REGENCIES, AdministrativeArea::LEVEL_DISTRICTS, AdministrativeArea::LEVEL_VILLAGES], true)) {
            $this->error("Unsupported --level '{$level}'. Use kabupaten, kecamatan or desa.");

            return self::FAILURE;
        }

        if (! is_file($path)) {
            $this->error("GeoJSON file not found: {$path}");

            return self::FAILURE;
        }

        $this->info("Importing: {$path}");
        $this->info("Target level: {$level}");

        $counters = ['matched' => 0, 'no_geometry' => 0, 'unmatched' => 0];
        $unmatchedSamples = [];

        DB::transaction(function () use ($path, $level, $key, $dryRun, $fillCentroid, &$counters, &$unmatchedSamples) {
            $reader = new GeoJsonStreamReader($path);

            foreach ($reader->features() as $feature) {
                $geometry = $feature['geometry'] ?? null;
                if (! $this->isBoundary($geometry)) {
                    $counters['no_geometry']++;

                    continue;
                }

                $name = $this->featureName($feature, $key);
                if ($name === null || $name === '') {
                    $counters['unmatched']++;

                    continue;
                }

                $row = AdministrativeArea::query()
                    ->where('level', $level)
                    ->where('name', $name)
                    ->first(['id', 'name', 'lat', 'lng']);

                if ($row === null) {
                    $counters['unmatched']++;
                    if (count($unmatchedSamples) < 25) {
                        $unmatchedSamples[] = $name;
                    }

                    continue;
                }

                if (! $dryRun) {
                    $this->applyGeometry($row, $geometry, $fillCentroid);
                }
                $counters['matched']++;
            }
        });
$this->line("Matched: {$counters['matched']}");
        $this->line("Skipped (no boundary geometry): {$counters['no_geometry']}");
        $this->line("Unmatched (name not found): {$counters['unmatched']}");

        foreach (array_slice(array_unique(array_values($unmatchedSamples)), 0, 5) as $sample) {
            $this->warn("Unmatched name: {$sample}");
        }

        if ($dryRun) {
            $this->warn('--dry-run: no rows were modified.');
        }

        return self::SUCCESS;
    }

    /**
     * Apply the geometry to one matched row using a qualified primary-key update.
     *
     * @param  \App\Models\AdministrativeArea  $row
     * @param  array<string, mixed>  $geometry
     */
    private function applyGeometry(object $row, array $geometry, bool $fillCentroid): void
    {
        $payload = [
            'geojson' => $geometry,
            'updated_at' => now(),
        ];

        if ($fillCentroid) {
            $centroid = $this->centroid($geometry);
            if ($centroid !== null && ($row->lat === null || $row->lng === null)) {
                $payload['lat'] = $centroid[0];
                $payload['lng'] = $centroid[1];
            }
        }

        AdministrativeArea::query()
            ->whereKey($row->id)
            ->update($payload);
    }

    private function isBoundary(mixed $geometry): bool
    {
        if (! is_array($geometry)) {
            return false;
        }

        return in_array($geometry['type'] ?? null, self::POLYGON_TYPES, true)
            && is_array($geometry['coordinates'] ?? null);
    }

    private function featureName(array $feature, ?string $key = null): ?string
    {
        $props = $feature['properties'] ?? [];
        if (! is_array($props)) {
            $props = [];
        }

        if ($key !== null && $key !== '') {
            $value = $props[$key] ?? null;

            return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
        }

        foreach (['name', 'Name', 'NAME', 'nama', 'wilayah', 'WADMKK', 'WADMKC', 'WADMD', 'NAMOBJ'] as $candidate) {
            $value = $props[$candidate] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    /**
     * Simple centroid (average lon/lat of outer rings) of a Polygon/MultiPolygon.
     *
     * @param  array<string, mixed>  $geometry
     * @return array{0: float, 1: float}|null lon/lat
     */
    private function centroid(array $geometry): ?array
    {
        $outer = [];
        $type = $geometry['type'] ?? null;
        $coords = $geometry['coordinates'] ?? [];

        if ($type === 'Polygon') {
            $outer = [$coords[0] ?? []];
        } elseif ($type === 'MultiPolygon') {
            foreach ($coords as $polygon) {
                if (is_array($polygon) && is_array($polygon[0] ?? null)) {
                    $outer[] = $polygon[0];
                }
            }
        }

        $count = 0;
        $lonSum = 0.0;
        $latSum = 0.0;

        foreach ($outer as $ring) {
            foreach ($ring as $point) {
                if (! is_array($point)) {
                    continue;
                }
                $lonSum += (float) ($point[0] ?? 0);
                $latSum += (float) ($point[1] ?? 0);
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        return [round($lonSum / $count, 6), round($latSum / $count, 6)];
    }
}