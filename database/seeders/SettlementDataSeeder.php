<?php

namespace Database\Seeders;

use App\Models\AdministrativeArea;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettlementDataSeeder extends Seeder
{
    use WithoutModelEvents;

    private const YEARS = [2021, 2022, 2023, 2024, 2025];

    public function run(): void
    {
        DB::table('settlement_areas')->truncate();

        $updatedBy = User::value('id');

        $chunk = [];
        $total = 0;

        AdministrativeArea::where('level', AdministrativeArea::LEVEL_VILLAGES)
            ->with(['parent.parent'])
            ->orderBy('id')
            ->each(function (AdministrativeArea $village) use (&$chunk, &$total, $updatedBy) {
                $district = $village->parent;
                $regency = $district?->parent;

                foreach (self::YEARS as $year) {
                    $chunk[] = $this->buildRow($village, $district?->name, $regency?->name, $year, $updatedBy);
                    $total++;

                    if (count($chunk) >= 1000) {
                        DB::table('settlement_areas')->insert($chunk);
                        $chunk = [];
                        $this->command?->getOutput()->write('.');
                    }
                }
            });

        if ($chunk !== []) {
            DB::table('settlement_areas')->insert($chunk);
            $this->command?->getOutput()->write('.');
        }

        $this->command?->getOutput()->writeln('');
        $this->command?->info("Seeded {$total} settlement records (village x year).");
    }

    /** @return array<string, mixed> */
    private function buildRow(
        AdministrativeArea $village,
        ?string $district,
        ?string $regency,
        int $year,
        ?int $updatedBy,
    ): array {
        $seed = crc32($village->id.$year);
        mt_srand($seed);

        // Jumlah kawasan/lokasi permukiman di desa ini (deterministik per desa & tahun).
        $lokasiCount = mt_rand(3, 6);

        $features = [];
        $lokasiNames = [];
        $totalHa = 0.0;

        for ($i = 1; $i <= $lokasiCount; $i++) {
            $luasHa = round(mt_rand(50, 900) / 100, 4);
            $totalHa += $luasHa;
            $name = 'Permukiman '.$village->name.' '.$i;
            $lokasiNames[mb_strtolower($name)] = $name;

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'WADMKK' => $regency,
                    'WADMKC' => $district,
                    'NAMOBJ' => $name,
                    'LUAS_HA' => $luasHa,
                    'count' => (int) round($luasHa * 250),
                    'classifica' => 1,
                ],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [$this->smallPolygon($village, $i)],
                ],
            ];
        }

        return [
            'name' => $village->name,
            'year' => $year,
            'province' => 'Bengkulu',
            'regency' => $regency,
            'district' => $district,
            'village' => $village->name,
            'settlement_count' => count($lokasiNames),
            'area_size' => round($totalHa, 4),
            'geojson_data' => json_encode([
                'type' => 'FeatureCollection',
                'features' => $features,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => 'published',
            'description' => 'Pemetaan permukiman tahun '.$year.' di desa '.$village->name.'.',
            'updated_by' => $updatedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Poligon kecil (rectangle) di sekitar titik desa sebagai geometri
     * sintetis satu kawasan permukiman.
     *
     * @return array<int, array{0: float, 1: float}>
     */
    private function smallPolygon(AdministrativeArea $village, int $index): array
    {
        mt_srand(crc32($village->id.'poly'.$index));

        $lat = (float) $village->lat;
        $lng = (float) $village->lng;
        $span = 0.001 + mt_rand(1, 1000) / 500_000.0;
        $dLat = $span;
        $dLng = $span / max(1.0, cos(deg2rad($lat)));

        return [
            [$lng - $dLng, $lat - $dLat],
            [$lng + $dLng, $lat - $dLat],
            [$lng + $dLng, $lat + $dLat],
            [$lng - $dLng, $lat + $dLat],
            [$lng - $dLng, $lat - $dLat],
        ];
    }
}
