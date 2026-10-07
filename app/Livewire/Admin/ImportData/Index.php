<?php

namespace App\Livewire\Admin\ImportData;

use App\Livewire\Admin\AdminComponent;
use App\Models\ActivityLog;
use App\Models\SettlementArea;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Import Data — DENAH CMS')]
class Index extends AdminComponent
{
    use WithFileUploads;

    public string $pageTitle = 'Import Data Permukiman';

    public $file;

    /** @var array<int, array<int, string>> */
    public array $preview = [];

    /** @var array<int, string> */
    public array $headers = [];

    public int $imported = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function breadcrumbsProperty(): array
    {
        return [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Import Data'],
        ];
    }

    public function updatedFile(): void
    {
        $this->reset(['preview', 'headers', 'errors', 'imported']);

        if (! $this->file) {
            return;
        }

        $rows = $this->parseCsv($this->file->getRealPath());
        $this->headers = $rows[0] ?? [];
        $this->preview = array_slice($rows, 1, 10);
    }

    public function import(): void
    {
        $this->errors = [];
        $this->imported = 0;

        if (! $this->file) {
            $this->errors[] = 'Pilih file CSV terlebih dahulu.';

            return;
        }

        $rows = $this->parseCsv($this->file->getRealPath());
        if (count($rows) < 2) {
            $this->errors[] = 'File tidak memiliki baris data.';

            return;
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
        $count = 0;

        foreach (array_slice($rows, 1) as $i => $row) {
            $row = array_pad($row, count($headers), '');
            $data = array_combine($headers, $row);
            if (! $data) {
                continue;
            }

            $name = trim((string) ($data['name'] ?? ''));
            if ($name === '') {
                $this->errors[] = 'Baris '.($i + 2).': kolom "name" kosong, dilewati.';
                continue;
            }

            SettlementArea::create([
                'name' => $name,
                'year' => (int) ($data['year'] ?? now()->year) ?: now()->year,
                'province' => trim((string) ($data['province'] ?? '')) ?: 'Bengkulu',
                'regency' => trim((string) ($data['regency'] ?? '')) ?: null,
                'district' => trim((string) ($data['district'] ?? '')) ?: null,
                'village' => trim((string) ($data['village'] ?? '')) ?: null,
                'settlement_count' => (int) ($data['settlement_count'] ?? 0),
                'area_size' => (float) ($data['area_size'] ?? 0),
                'status' => in_array($data['status'] ?? 'draft', ['draft', 'published'], true) ? $data['status'] : 'draft',
                'description' => trim((string) ($data['description'] ?? '')) ?: null,
                'updated_by' => auth()->id(),
            ]);
            $count++;
        }

        $this->imported = $count;
        ActivityLog::record('Import', 'import', 'Import '.$count.' data permukiman dari CSV');
        $this->dispatch('notify', type: 'success', message: $count.' data berhasil diimport.');
        $this->reset(['file', 'preview', 'headers']);
    }

    public function downloadTemplate()
    {
        $headers = ['name', 'year', 'province', 'regency', 'district', 'village', 'settlement_count', 'area_size', 'status', 'description'];
        $csv = implode(',', $headers)."\n";
        $csv .= 'Contoh Permukiman,2024,Bengkulu,Bengkulu Selatan,Manna,Desa Contoh,120,0.5,published,Contoh data'."\n";

        return response()->streamDownload(fn () => print($csv), 'template-permukiman.csv');
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }
}
