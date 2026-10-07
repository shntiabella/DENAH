<?php

namespace Database\Seeders;

use App\Models\AboutContent;
use App\Models\Feature;
use App\Models\HeroBadge;
use App\Models\HeroContent;
use App\Models\MethodologyContent;
use App\Models\MethodologyStep;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Stat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            GeoJsonMetaDataSeeder::class,
            AdministrativeAreaSeeder::class,
        ]);

        // Hero
        HeroContent::firstOrCreate([], [
            'badge_text' => 'Pemetaan & Klasifikasi Permukiman',
            'headline_1' => 'Jelajahi Persebaran',
            'headline_2' => 'Permukiman',
            'subheadline' => 'Eksplorasi informasi spasial mengenai persebaran dan klasifikasi permukiman melalui peta interaktif.',
            'cta_primary_text' => 'Jelajahi Peta',
            'cta_secondary_text' => 'Lihat Statistik',
            'coordinates' => "7°56'15\"S | 112°37'35\"E",
        ]);
        foreach (['Spasial Data', 'Klasifikasi', 'Open Access'] as $i => $label) {
            HeroBadge::firstOrCreate(['label' => $label], ['sort_order' => $i]);
        }

        // About
        AboutContent::firstOrCreate([], [
            'body' => '<p>DENAH adalah platform pemetaan dan klasifikasi permukiman. Eksplorasi informasi spasial mengenai persebaran dan klasifikasi permukiman melalui peta interaktif.</p>',
            'more_link_text' => 'Selengkapnya',
            'more_link_url' => '#tentang',
        ]);

        // Stats
        $stats = [
            ['label' => 'Luas Permukiman', 'value' => 12800, 'unit' => 'Ha', 'icon' => 'area', 'counter' => true],
            ['label' => 'Wilayah Terpetakan', 'value' => 36, 'unit' => 'wilayah', 'icon' => 'pins', 'counter' => true],
            ['label' => 'Data Spasial', 'value' => 54, 'unit' => 'dataset', 'icon' => 'layers', 'counter' => true],
            ['label' => 'Tahun Pemetaan', 'value' => 2026, 'unit' => '', 'icon' => 'calendar', 'counter' => false],
        ];
        foreach ($stats as $i => $s) {
            Stat::firstOrCreate(['label' => $s['label']], array_merge($s, ['sort_order' => $i]));
        }

        // Features
        $features = [
            ['title' => 'Peta Interaktif', 'description' => 'Navigasi peta dengan legenda klasifikasi permukiman.', 'icon' => 'map'],
            ['title' => 'Statistik', 'description' => 'Ringkasan tren persebaran permukiman dalam bentuk angka.', 'icon' => 'chart'],
            ['title' => 'Metodologi', 'description' => 'Penjelasan proses pemetaan dan klasifikasi secara transparan.', 'icon' => 'layers'],
        ];
        foreach ($features as $i => $f) {
            Feature::firstOrCreate(['title' => $f['title']], array_merge($f, ['sort_order' => $i]));
        }

        // Methodology — pipeline klasifikasi permukiman berbasis Google Earth Engine (GEE)
        $steps = [
            [
                'title' => 'Citra Satelit',
                'title_en' => 'Satellite Imagery',
                'icon' => 'satellite',
                'description' => 'Menggunakan citra Sentinel-2 Surface Reflectance (SR) Harmonized (COPERNICUS/S2_SR_HARMONIZED) sebagai sumber data utama pemetaan permukiman.',
                'description_en' => 'Uses Sentinel-2 Surface Reflectance (SR) Harmonized (COPERNICUS/S2_SR_HARMONIZED) imagery as the primary source for settlement mapping.',
            ],
            [
                'title' => 'Pra-pemrosesan',
                'title_en' => 'Pre-processing',
                'icon' => 'preprocess',
                'description' => 'Penghapusan tutupan awan otomatis dengan Cloud Score+ (ambang > 0,5) dan pembuatan komposit median temporal untuk citra bebas awan yang konsisten.',
                'description_en' => 'Automatic cloud masking using Cloud Score+ (threshold > 0.5) and temporal median composite to produce consistent, cloud-free imagery.',
            ],
            [
                'title' => 'Penentuan Sampel',
                'title_en' => 'Sample Collection',
                'icon' => 'sample',
                'description' => 'Membuat training sample kelas biner (0 = non-permukiman, 1 = permukiman) serta fitur yang sesuai (Satellite Embedding) sebagai input klasifikasi.',
                'description_en' => 'Defines binary training samples (0 = non-settlement, 1 = settlement) and aligned features (Satellite Embedding) as classification input.',
            ],
            [
                'title' => 'Klasifikasi Random Forest',
                'title_en' => 'Random Forest Classification',
                'icon' => 'forest',
                'description' => 'Melatih model klasifikasi Random Forest (100 trees) menggunakan band spektral (biru, hijau, merah, NIR) dan indeks NBR pada Google Earth Engine.',
                'description_en' => 'Trains a Random Forest classification model (100 trees) using spectral bands (blue, green, red, NIR) and the NBR index in Google Earth Engine.',
            ],
            [
                'title' => 'Hasil Klasifikasi',
                'title_en' => 'Classification Result',
                'icon' => 'result',
                'description' => 'Raster hasil klasifikasi menghasilkan peta biner permukiman/non-permukiman dengan resolusi 10 m per piksel.',
                'description_en' => 'The classified raster produces a settlement/non-settlement binary map at 10-meter pixel resolution.',
            ],
        ];

        // Step 6–7 (brief 7 tahap): hapus "Pemeriksaan" & "Database DENAH", gabung
        // "Ekspor GeoJSON" + "Database DENAH" menjadi satu langkah "Ekspor & Pengolahan Data".
        $extra = [
            [
                'title' => 'Ekspor & Pengolahan Data',
                'title_en' => 'Data Export & Processing',
                'icon' => 'export',
                'description' => 'Hasil klasifikasi diekspor dari Google Earth Engine dan diintegrasikan ke database DENAH beserta atribut luasan dan klasifikasi.',
                'description_en' => 'Classification results are exported from Google Earth Engine and integrated into the DENAH database along with area and classification attributes.',
            ],
            [
                'title' => 'WebGIS & Statistik',
                'title_en' => 'WebGIS & Statistics',
                'icon' => 'webgis',
                'description' => 'Peta interaktif WebGIS dan panel statistik menyajikan hasil klasifikasi secara real-time langsung dari database.',
                'description_en' => 'Interactive WebGIS map and statistics panel present classification results in real time directly from the database.',
            ],
        ];

        // Pastikan hanya 7 langkah yang tersimpan (hapus data lama lalu seed ulang).
        MethodologyStep::query()->delete();
        foreach (array_merge($steps, $extra) as $i => $s) {
            MethodologyStep::create($s + ['sort_order' => $i]);
        }

        $bodyId = '<p>Klasifikasi permukiman pada pemetaan ini berbasis pada <strong>Google Earth Engine (GEE)</strong> dengan memanfaatkan citra <strong>Sentinel-2 Surface Reflectance (SR) Harmonized</strong>. Tahap dimulai dengan memfilter citra berdasarkan rentang waktu dan area kajian, menghapus tutupan awan melalui <strong>Cloud Score+</strong> (ambang &gt; 0,5), lalu menyusun <strong>median composite</strong>. Data tersebut menjadi dasar penyusunan <strong>band spektral</strong> (biru, hijau, merah, NIR), indeks <strong>NBR</strong>, serta fitur turunan berupa <strong>Satellite Embedding</strong>.</p>';
        $bodyEn = '<p>Settlement classification in this mapping is based on <strong>Google Earth Engine (GEE)</strong> using <strong>Sentinel-2 Surface Reflectance (SR) Harmonized</strong> imagery. The process starts by filtering images by date range and study area, removing clouds with <strong>Cloud Score+</strong> (threshold &gt; 0.5), then building a <strong>median composite</strong>. This data becomes the basis for deriving <strong>spectral bands</strong> (blue, green, red, NIR), the <strong>NBR</strong> index, and auxiliary features such as <strong>Satellite Embedding</strong>.</p>';

        $mc = MethodologyContent::firstOrCreate([], ['body' => $bodyId]);
        $mc->update([
            'title' => 'Bagaimana Permukiman Dipetakan?',
            'title_id' => 'Bagaimana Permukiman Dipetakan?',
            'title_en' => 'How Are Settlements Mapped?',
            'body' => $bodyId,
            'body_id' => $bodyId,
            'body_en' => $bodyEn,
            'updated_by' => null,
        ]);

        // Settings
        Setting::set('site_name', 'DENAH');
        Setting::set('tagline', 'Digital Ecosystem & Navigation for Area Housing');
        Setting::set('meta_title', 'DENAH — Jelajahi Persebaran Permukiman');
        Setting::set('meta_description', 'Platform pemetaan dan klasifikasi permukiman.');
        Setting::set('footer_description', 'Platform pemetaan dan klasifikasi permukiman. Eksplorasi informasi spasial mengenai persebaran dan klasifikasi permukiman melalui peta interaktif.');
        Setting::set('contact_email', 'admin@denah.id');
        Setting::set('contact_phone', '+62 000 0000 0000');
        Setting::set('contact_address', 'Indonesia');

        // Social links
        foreach ([['Facebook', 'facebook', '#'], ['Instagram', 'instagram', '#'], ['X', 'x', '#'], ['YouTube', 'youtube', '#']] as $i => [$platform, $icon, $url]) {
            SocialLink::firstOrCreate(['platform' => $platform], ['icon' => $icon, 'url' => $url, 'sort_order' => $i]);
        }
    }
}
