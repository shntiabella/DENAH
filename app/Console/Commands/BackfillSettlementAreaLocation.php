<?php

namespace App\Console\Commands;

use App\Support\GeoJsonAttributeMapper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillSettlementAreaLocation extends Command
{
    protected $signature = 'denah:backfill-settlement-location
        {--dry-run : Tampilkan rencana perbaikan tanpa menulis ke database}';

    protected $description = 'Perbaiki kolom regency, district, dan village yang NULL pada settlement_areas berdasarkan isi geojson_data, tanpa mengubah geometri.';

    public function handle(): int
    {
        $total = 0;
        $updated = 0;
        $unchanged = 0;

        DB::table('settlement_areas')
            ->whereNull('regency')
            ->orWhereNull('district')
            ->orWhereNull('village')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$total, &$updated, &$unchanged) {
                foreach ($rows as $row) {
                    $total++;
                    $values = $this->extract($row->geojson_data);
                    if ($values === []) {
                        $this->warn("Row #{$row->id}: tidak ada properti yang bisa dipetakan.");

                        continue;
                    }

                    $data = [];
                    foreach ($values as $column => $value) {
                        if (($row->{$column} ?? null) === null || trim((string) $row->{$column}) === '') {
                            $data[$column] = $value;
                        }
                    }

                    if ($data === []) {
                        $unchanged++;

                        continue;
                    }

                    $this->line(sprintf(
                        "Row #%d: regency=%s district=%s village=%s",
                        $row->id,
                        $data['regency'] ?? '-',
                        $data['district'] ?? '-',
                        $data['village'] ?? '-',
                    ));

                    if (! $this->option('dry-run')) {
                        DB::table('settlement_areas')->where('id', $row->id)->update($data);
                    }
                    $updated++;
                }
            });

        $this->newLine();
        if ($this->option('dry-run')) {
            $this->info("[dry-run] {$updated} baris akan diperbarui (dari {$total} baris bermasalah, {$unchanged} sudah terisi).");

            return self::SUCCESS;
        }
        $this->info("Selesai. {$updated} baris diperbarui (dari {$total} baris bermasalah, {$unchanged} sudah terisi).");

        return self::SUCCESS;
    }

    /**
     * Ambil nilai regency/district/village dari seluruh feature pada
     * geojson_data (FeatureCollection atau Feature tunggal).
     *
     * @return array{regency?: string, district?: string, village?: string}
     */
    protected function extract(?string $json): array
    {
        $data = $json !== null && $json !== '' ? json_decode($json, true) : null;
        if (! is_array($data)) {
            return [];
        }

        if (($data['type'] ?? null) === 'Feature') {
            $data = ['type' => 'FeatureCollection', 'features' => [$data]];
        }

        $features = is_array($data['features'] ?? null) ? $data['features'] : [];

        $result = [];
        foreach ($features as $feature) {
            if (! is_array($feature)) {
                continue;
            }
            foreach ([
                'regency' => GeoJsonAttributeMapper::regency($feature),
                'district' => GeoJsonAttributeMapper::district($feature),
                'village' => GeoJsonAttributeMapper::village($feature),
            ] as $column => $value) {
                if ($value !== null && trim($value) !== '' && ! isset($result[$column])) {
                    $result[$column] = $value;
                }
            }
            if (count($result) === 3) {
                break;
            }
        }

        return $result;
    }
}
