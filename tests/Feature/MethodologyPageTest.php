<?php

namespace Tests\Feature;

use App\Models\MethodologyContent;
use App\Models\MethodologyStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MethodologyPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedMethodology(): void
    {
        $bodyId = '<p>Klasifikasi permukiman berbasis <strong>Google Earth Engine (GEE)</strong> memanfaatkan citra <strong>Sentinel-2 Surface Reflectance (SR) Harmonized</strong>. Proses memfilter citra, menghapus awan lewat <strong>Cloud Score+</strong>, menyusun <strong>median composite</strong>, membangun <strong>band spektral</strong>, indeks <strong>NBR</strong>, hingga fitur <strong>Satellite Embedding</strong>. Klasifikasi memakai <strong>training sample</strong> dan model <strong>Random Forest (100 trees)</strong>, lalu hasilnya diekspor sebagai <strong>GeoJSON</strong>.</p>';
        $bodyEn = '<p>Settlement classification is based on <strong>Google Earth Engine (GEE)</strong> using <strong>Sentinel-2 Surface Reflectance (SR) Harmonized</strong> imagery with <strong>Cloud Score+</strong>, <strong>median composite</strong>, <strong>NBR</strong>, and <strong>Satellite Embedding</strong> features. <strong>training sample</strong> and a <strong>Random Forest (100 trees)</strong> model are used, and results are exported as <strong>GeoJSON</strong>.</p>';

        MethodologyContent::query()->delete();
        MethodologyContent::create([
            'title' => 'Bagaimana Permukiman Dipetakan?',
            'title_id' => 'Bagaimana Permukiman Dipetakan?',
            'title_en' => 'How Are Settlements Mapped?',
            'body' => $bodyId,
            'body_id' => $bodyId,
            'body_en' => $bodyEn,
        ]);

        MethodologyStep::query()->delete();
        $steps = [
            ['Citra Satelit', 'Satellite Imagery'],
            ['Pra-pemrosesan', 'Pre-processing'],
            ['Penentuan Sampel', 'Sample Selection'],
            ['Klasifikasi Random Forest', 'Random Forest Classification'],
            ['Hasil Klasifikasi', 'Classification Result'],
            ['Ekspor & Pengolahan Data', 'Data Export & Processing'],
            ['WebGIS & Statistik', 'WebGIS & Statistics'],
        ];

        foreach ($steps as $i => [$title, $titleEn]) {
            MethodologyStep::create([
                'title' => $title,
                'title_id' => $title,
                'title_en' => $titleEn,
                'description' => "Deskripsi $title",
                'description_id' => "Deskripsi $title",
                'description_en' => "Description of $titleEn",
                'icon' => 'step',
                'sort_order' => $i,
            ]);
        }
    }

    public function test_home_renders_methodology_summary_and_link_to_metodologi(): void
    {
        $this->seedMethodology();

        $response = $this->get('/');
        $response->assertOk();
        $html = $response->getContent();

        // Tombol menuju halaman metodologi lengkap
        $this->assertMatchesRegularExpression('/href="[^"]*\/metodologi[^"]*"/', $html);
        $this->assertStringContainsString('Lihat Metodologi', $html);

        // Ringkasan 7 langkah garis besar muncul di Home
        foreach (['Citra Satelit', 'Pra-pemrosesan', 'Penentuan Sampel', 'Klasifikasi Random Forest', 'Hasil Klasifikasi', 'Ekspor &amp; Pengolahan Data', 'WebGIS &amp; Statistik'] as $step) {
            $this->assertStringContainsString($step, $html, 'Home method step missing: '.$step);
        }
    }

    public function test_methodology_page_renders_cms_steps_and_body(): void
    {
        $this->seedMethodology();

        $response = $this->get('/metodologi');
        $response->assertOk();
        $html = $response->getContent();

        // Aliran 7 tahap dari CMS
        foreach (['Citra Satelit', 'Pra-pemrosesan', 'Penentuan Sampel', 'Klasifikasi Random Forest', 'Hasil Klasifikasi', 'Ekspor &amp; Pengolahan Data', 'WebGIS &amp; Statistik'] as $title) {
            $this->assertStringContainsString($title, $html, 'Step title missing: '.$title);
        }

        // Penjelasan (rich text dari CMS)
        $this->assertStringContainsString('Google Earth Engine (GEE)', $html);
        $this->assertStringContainsString('Sentinel-2 Surface Reflectance (SR) Harmonized', $html);
        $this->assertStringContainsString('Cloud Score+', $html);
        $this->assertStringContainsString('median composite', $html);
        $this->assertStringContainsString('band spektral', $html);
        $this->assertStringContainsString('NBR', $html);
        $this->assertStringContainsString('Satellite Embedding', $html);
        $this->assertStringContainsString('training sample', $html);
        $this->assertStringContainsString('Random Forest (100 trees)', $html);
        $this->assertStringContainsString('GeoJSON', $html);

        // Tidak ada lagi langkah lama (9 tahap)
        $this->assertStringNotContainsString('Pemeriksaan', $html);
        $this->assertStringNotContainsString('Ekspor GeoJSON', $html);
        $this->assertStringNotContainsString('Database DENAH', $html);
    }

    public function test_methodology_page_translates_to_english(): void
    {
        $this->seedMethodology();

        $this->get('/locale/en');
        $response = $this->get('/metodologi');
        $html = $response->getContent();

        $this->assertStringContainsString('How Are Settlements Mapped?', $html);
        $this->assertStringContainsString('Random Forest Classification', $html);
        $this->assertStringContainsString('Satellite Imagery', $html);
        $this->assertStringNotContainsString('Klasifikasi Random Forest', $html);
        $this->assertStringNotContainsString('Citra Satelit', $html);
    }
}
