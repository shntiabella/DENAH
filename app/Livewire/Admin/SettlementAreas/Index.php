<?php

namespace App\Livewire\Admin\SettlementAreas;

use App\Livewire\Admin\AdminComponent;
use App\Livewire\Admin\Concerns\WithConfirmDelete;
use App\Models\ActivityLog;
use App\Models\SettlementArea;
use App\Support\GeoAreaCalculator;
use App\Support\GeoJsonAttributeMapper;
use App\Support\GeoJsonHelper;
use App\Support\GeoJsonStreamReader;
use App\Support\SettlementStatistics;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
#[Title('Data Permukiman — DENAH CMS')]
class Index extends AdminComponent
{
    use WithConfirmDelete;
    use WithFileUploads;
    use WithPagination;

    public string $pageTitle = 'Kelola Data Permukiman';

    public $geojsonFile;

    /** @var array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string}|null */
    public ?array $parsedGeojson = null;

    public ?float $previewArea = null;

    public ?string $previewName = null;

    public ?int $previewCount = null;

    public ?string $geojsonWarning = null;

    public ?string $geojsonError = null;

    public string $status = 'published';

    public string $search = '';

    /** @var array<int, int> */
    public array $selected = [];

    public bool $showClearAllModal = false;

    public string $clearAllTyped = '';

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Data Permukiman'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGeojsonFile(): void
    {
        $this->reset(['parsedGeojson', 'previewArea', 'previewName', 'previewCount', 'geojsonWarning', 'geojsonError']);

        if (! $this->geojsonFile) {
            return;
        }

        $this->validate(
            ['geojsonFile' => 'file|mimes:json,geojson,text/plain,application/json,application/octet-stream|max:102400'],
            ['geojsonFile.max' => 'File melebihi batas upload maksimal 100 MB.'],
        );

        $path = $this->uploadedRealPath();
        if ($path === null) {
            $this->geojsonError = 'File sementara tidak ditemukan. Silakan unggah ulang file.';

            return;
        }

        try {
            $this->ensureProcessingCapacity();
            $summary = $this->summarizeGeoJsonFile($path);
        } catch (RuntimeException $e) {
            $this->geojsonError = $e->getMessage();

            return;
        } catch (\Throwable $e) {
            $this->geojsonError = $this->friendlyFailureMessage($e);

            return;
        }

        $this->parsedGeojson = $summary;
        $this->previewName = $this->geojsonFile->getClientOriginalName();
        $this->previewCount = $summary['count'];
        $this->previewArea = $summary['area_ha'];

        $warnings = [];
        if ($summary['warning'] !== null) {
            $warnings[] = $summary['warning'];
        }
        $range = GeoAreaCalculator::rangeCheck($summary['min_lat'], $summary['max_lat'], $summary['min_lon'], $summary['max_lon']);
        if (! $range['ok']) {
            $warnings[] = $range['message'];
        }
        $this->geojsonWarning = $warnings !== [] ? implode(' ', $warnings) : null;
    }

    public function importFile(): void
    {
        $this->validate(
            ['geojsonFile' => 'required|file|max:102400', 'status' => 'required|in:draft,published'],
            ['geojsonFile.max' => 'File melebihi batas upload maksimal 100 MB.'],
        );

        if (! $this->parsedGeojson) {
            $this->updatedGeojsonFile();
            if ($this->geojsonError) {
                return;
            }
        }

        $summary = $this->parsedGeojson;
        if ($summary === null) {
            $this->geojsonError = 'Tidak ada data GeoJSON untuk diimpor. Pilih file terlebih dahulu.';

            return;
        }

        $path = $this->uploadedRealPath();
        if ($path === null) {
            $this->geojsonError = 'File sementara tidak ditemukan. Silakan unggah ulang file.';

            return;
        }

        try {
            $this->ensureProcessingCapacity();
            $raw = $this->readRawGeoJson($path);
        } catch (RuntimeException $e) {
            $this->geojsonError = $e->getMessage();

            return;
        } catch (\Throwable $e) {
            $this->geojsonError = $this->friendlyFailureMessage($e);

            return;
        }

        $storedPath = $this->geojsonFile->store('settlement-areas', 'public');

        $name = $summary['name'];
        $name = $name ? mb_substr($name, 0, 120) : mb_substr($this->getBaseName($this->geojsonFile->getClientOriginalName()), 0, 120);

        DB::table('settlement_areas')->insert([
            'name' => $name,
            'year' => now()->year,
            'province' => 'Bengkulu',
            'regency' => $summary['regency'],
            'district' => $summary['district'],
            'village' => $summary['village'],
            'settlement_count' => $summary['settlement_count'],
            'area_size' => $summary['area_ha'],
            'geojson_file' => $storedPath,
            'geojson_data' => $raw,
            'status' => $this->status,
            'description' => 'Diimpor via file GeoJSON ('.$this->previewName.').',
            'updated_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLog::record('Settlement Area', 'import', 'Mengimpor data permukiman dari "'.$this->previewName.'"');
        SettlementStatistics::flushCache();
        $this->dispatch('notify', type: 'success', message: 'Data GeoJSON berhasil diimpor.');

        $this->reset(['geojsonFile', 'parsedGeojson', 'previewArea', 'previewName', 'previewCount', 'geojsonWarning', 'geojsonError']);
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $sa = SettlementArea::select(['id', 'status', 'name'])->findOrFail($id);
        $sa->update([
            'status' => $sa->status === 'published' ? 'draft' : 'published',
            'updated_by' => auth()->id(),
        ]);
        ActivityLog::record('Settlement Area', 'toggle', 'Toggle status wilayah "'.$sa->name.'"');
        $this->dispatch('notify', type: 'success', message: 'Status wilayah diubah.');
    }

    /** @return array<int, int> */
    public function bulkDeleteIds(): array
    {
        return array_values(array_map('intval', $this->selected));
    }

    public function deleteSelected(): void
    {
        $ids = $this->bulkDeleteIds();
        if ($ids === []) {
            $this->dispatch('notify', type: 'error', message: 'Tidak ada data terpilih.');

            return;
        }

        $count = $this->deleteAreas($ids);
        if ($count > 0) {
            ActivityLog::record('Settlement Area', 'bulk-delete', 'Menghapus '.$count.' data permukiman terpilih');
            SettlementStatistics::flushCache();
            $this->dispatch('notify', type: 'success', message: $count.' data permukiman dihapus.');
        }
        $this->selected = [];
        $this->resetPage();
    }

    public function clearAll(): void
    {
        $ids = SettlementArea::pluck('id')->all();
        $count = $this->deleteAreas($ids);
        if ($count > 0) {
            ActivityLog::record('Settlement Area', 'clear', 'Mengosongkan seluruh '.$count.' data permukiman');
            SettlementStatistics::flushCache();
            $this->dispatch('notify', type: 'success', message: 'Seluruh '.$count.' data permukiman telah dihapus.');
        }
        $this->closeClearAll();
        $this->resetPage();
    }

    public function openClearAll(): void
    {
        $this->clearAllTyped = '';
        $this->showClearAllModal = true;
    }

    public function closeClearAll(): void
    {
        $this->clearAllTyped = '';
        $this->showClearAllModal = false;
    }

    public function performDelete(string $model, int $id): void
    {
        if ($model === 'area') {
            $sa = SettlementArea::select(['id', 'name'])->find($id);
            if (! $sa) {
                return;
            }
            $this->deleteAreas([$sa->id]);
            ActivityLog::record('Settlement Area', 'delete', 'Menghapus wilayah "'.$sa->name.'"');
            $this->dispatch('notify', type: 'success', message: 'Wilayah dihapus.');
        } elseif ($model === 'bulk') {
            $this->deleteSelected();
        }
    }

    /**
     * @param  array<int, int>  $ids
     */
    protected function deleteAreas(array $ids): int
    {
        $areas = SettlementArea::select(['id', 'geojson_file'])->whereIn('id', $ids)->get();
        foreach ($areas as $sa) {
            if ($sa->geojson_file && Storage::disk('public')->exists($sa->geojson_file)) {
                Storage::disk('public')->delete($sa->geojson_file);
            }
        }

        return SettlementArea::whereIn('id', $ids)->delete();
    }

    public function render()
    {
        $areas = SettlementArea::query()
            ->select(['id', 'name', 'year', 'settlement_count', 'area_size', 'status', 'created_at'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()->paginate(10);

        return view('livewire.admin.settlement-areas.index', [
            'areas' => $areas,
            'totalCount' => SettlementArea::count(),
            'allIds' => SettlementArea::pluck('id')->all(),
            'selectedCount' => count($this->bulkDeleteIds()),
        ]);
    }

    /**
     * Meringkas file GeoJSON secara streaming agar file besar tidak
     * dimuat seluruhnya ke memori.
     *
     * @return array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string}
     */
    protected function summarizeGeoJsonFile(string $path): array
    {
        $reader = new GeoJsonStreamReader($path);

        if (! $reader->isFeatureCollection()) {
            return $this->summarizeWholeFile($path);
        }

        $summary = $this->emptySummary('FeatureCollection');
        foreach ($reader->features() as $feature) {
            if (! is_array($feature)) {
                throw new RuntimeException('File GeoJSON mengandung fitur yang bukan objek Feature.');
            }
            $this->accumulateFeature($feature, $summary);
        }

        if ($summary['count'] === 0) {
            throw new RuntimeException('FeatureCollection tidak memiliki array "features".');
        }

        return $this->finalizeSummary($summary);
    }

    /**
     * Jalur fallback untuk Feature tunggal / dokumen non-FeatureCollection:
     * dibaca dan divalidasi utuh (hanya diperlukan bila dokumen satu fitur).
     *
     * @return array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string}
     */
    protected function summarizeWholeFile(string $path): array
    {
        $raw = $this->readRawGeoJson($path);
        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('File bukan JSON/GeoJSON yang valid.');
        }

        $check = GeoJsonHelper::validate($decoded);
        if (! $check['valid']) {
            throw new RuntimeException($check['message']);
        }

        if (($decoded['type'] ?? null) === 'FeatureCollection') {
            $summary = $this->emptySummary('FeatureCollection');
            foreach ($decoded['features'] ?? [] as $feature) {
                $this->accumulateFeature($feature, $summary);
            }
            if ($summary['count'] === 0) {
                throw new RuntimeException('FeatureCollection tidak memiliki array "features".');
            }
        } else {
            $summary = $this->emptySummary('Feature');
            $this->accumulateFeature($decoded, $summary);
        }

        return $this->finalizeSummary($summary);
    }

    /**
     * @param  array<string, mixed>  $feature
     * @param  array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string, lokasi: array<string, string>}  $summary
     */
    protected function accumulateFeature(array $feature, array &$summary): void
    {
        $check = GeoJsonHelper::validateFeature($feature);
        if (! $check['valid']) {
            throw new RuntimeException($check['message']);
        }

        $summary['count']++;

        $lokasi = GeoJsonAttributeMapper::lokasiName($feature);
        if ($lokasi !== null) {
            $summary['lokasi'][mb_strtolower($lokasi)] = $lokasi;
        }

        if ($summary['name'] === null) {
            $summary['name'] = $lokasi ?? GeoJsonHelper::extractName($feature);
        }
        if ($summary['regency'] === null) {
            $summary['regency'] = GeoJsonAttributeMapper::regency($feature);
        }
        if ($summary['district'] === null) {
            $summary['district'] = GeoJsonAttributeMapper::district($feature);
        }
        if ($summary['village'] === null) {
            $summary['village'] = GeoJsonAttributeMapper::village($feature);
        }

        $summary['area_ha'] += GeoJsonAttributeMapper::areaHa($feature);

        $bounds = GeoAreaCalculator::featureBounds($feature);
        if ($bounds !== null) {
            $summary['min_lat'] = min($summary['min_lat'], $bounds['min_lat']);
            $summary['max_lat'] = max($summary['max_lat'], $bounds['max_lat']);
            $summary['min_lon'] = min($summary['min_lon'], $bounds['min_lon']);
            $summary['max_lon'] = max($summary['max_lon'], $bounds['max_lon']);
        }
    }

    /**
     * @return array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string, lokasi: array<string, string>}
     */
    protected function emptySummary(string $type): array
    {
        return [
            'type' => $type,
            'count' => 0,
            'settlement_count' => 0,
            'area_ha' => 0.0,
            'min_lat' => INF,
            'max_lat' => -INF,
            'min_lon' => INF,
            'max_lon' => -INF,
            'name' => null,
            'regency' => null,
            'district' => null,
            'village' => null,
            'warning' => null,
            'lokasi' => [],
        ];
    }

    /**
     * @param  array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string, lokasi: array<string, string>}  $summary
     * @return array{type: string, count: int, settlement_count: int, area_ha: float, min_lat: float, max_lat: float, min_lon: float, max_lon: float, name: ?string, regency: ?string, district: ?string, village: ?string, warning: ?string}
     */
    protected function finalizeSummary(array $summary): array
    {
        if ($summary['min_lat'] === INF) {
            $summary['min_lat'] = $summary['max_lat'] = $summary['min_lon'] = $summary['max_lon'] = 0.0;
        }

        $uniqueLokasi = count($summary['lokasi']);
        if ($uniqueLokasi > 0) {
            $summary['settlement_count'] = $uniqueLokasi;
        } else {
            $summary['settlement_count'] = $summary['count'];
            if ($summary['count'] > 0) {
                $summary['warning'] = 'File tidak memiliki atribut nama lokasi (NAMOBJ/LOKASI/NAMA_KAWASAN/dll). Jumlah lokasi memakai jumlah fitur: '.$summary['count'].'.';
            }
        }

        $summary['area_ha'] = round($summary['area_ha'], 4);
        unset($summary['lokasi']);

        return $summary;
    }

    protected function readRawGeoJson(string $path): string
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Tidak dapat membaca file GeoJSON untuk diimpor.');
        }
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        return $raw;
    }

    protected function uploadedRealPath(): ?string
    {
        if (! $this->geojsonFile) {
            return null;
        }
        $path = $this->geojsonFile->getRealPath();
        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Beri ruang memori dan waktu secukupnya hanya untuk pemrosesan file
     * besar, tanpa menaikkan batas server secara berlebihan.
     */
    protected function ensureProcessingCapacity(): void
    {
        $currentBytes = $this->memoryLimitBytes();
        if ($currentBytes > 0 && $currentBytes < 256 * 1024 * 1024) {
            @ini_set('memory_limit', '256M');
        }
        @set_time_limit(300);
    }

    protected function memoryLimitBytes(): int
    {
        $value = trim((string) ini_get('memory_limit'));
        if ($value === '' || $value === '-1') {
            return -1;
        }
        $unit = strtolower(substr($value, -1));
        $amount = (float) $value;

        return match ($unit) {
            'g' => (int) ($amount * 1024 * 1024 * 1024),
            'm' => (int) ($amount * 1024 * 1024),
            'k' => (int) ($amount * 1024),
            default => (int) $amount,
        };
    }

    protected function friendlyFailureMessage(\Throwable $e): string
    {
        $message = strtolower((string) $e->getMessage());

        if (str_contains($message, 'memory')) {
            return 'Gagal memproses file: file terlalu besar untuk batas memori server saat ini. Unggah file dengan jumlah fitur lebih sedikit, atau naikkan batas memory_limit di pengaturan PHP server.';
        }
        if (str_contains($message, 'execution time') || str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
            return 'Gagal memproses file: proses melebihi batas waktu server. Coba unggah file yang lebih kecil.';
        }
        if (str_contains($message, 'json')) {
            return 'File bukan JSON/GeoJSON yang valid.';
        }

        return 'Gagal memproses file: '.$e->getMessage();
    }

    protected function getBaseName(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);

        return $name !== '' ? $name : 'Data Permukiman';
    }
}
