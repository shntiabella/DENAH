<?php

use App\Models\Classification;
use App\Models\SettlementArea;
use App\Models\User;
use App\Support\GeoAreaCalculator;
use App\Support\GeoJsonHelper;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->validGeojson = [
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [[[112.5, -7.0], [112.6, -7.0], [112.6, -7.1], [112.5, -7.1], [112.5, -7.0]]],
                ],
                'properties' => ['name' => 'Wilayah Uji'],
            ],
        ],
    ];
});

it('validates a proper GeoJSON FeatureCollection', function () {
    expect(GeoJsonHelper::validate($this->validGeojson)['valid'])->toBeTrue();
});

it('rejects invalid GeoJSON type', function () {
    $res = GeoJsonHelper::validate(['type' => 'NotAType']);
    expect($res['valid'])->toBeFalse();
});

it('extracts name from GeoJSON properties', function () {
    expect(GeoJsonHelper::extractName($this->validGeojson))->toBe('Wilayah Uji');
});

it('calculates a positive area for a polygon', function () {
    expect(GeoAreaCalculator::areaKm2($this->validGeojson))->toBeGreaterThan(0);
});

it('flags coordinates outside Indonesia range', function () {
    $out = $this->validGeojson;
    $out['features'][0]['geometry']['coordinates'] = [[[10, 50], [11, 50], [11, 49], [10, 49], [10, 50]]];
    expect(GeoAreaCalculator::checkIndonesiaRange($out)['ok'])->toBeFalse();
});

it('computes lat/lon bounds of a feature', function () {
    $bounds = GeoAreaCalculator::featureBounds($this->validGeojson['features'][0]);
    expect($bounds['min_lat'])->toBe(-7.1)
        ->and($bounds['max_lat'])->toBe(-7.0)
        ->and($bounds['min_lon'])->toBe(112.5)
        ->and($bounds['max_lon'])->toBe(112.6);
});

it('enriches published settlement areas in the public API', function () {
    $class = Classification::create(['name' => 'Padat', 'slug' => 'padat', 'legend_color' => '#C17F52']);
    SettlementArea::create([
        'name' => 'Wilayah Uji',
        'classification_id' => $class->id,
        'status' => 'published',
        'area_size' => 12.5,
        'geojson_data' => $this->validGeojson,
    ]);

    $res = get('/api/settlement-areas')->assertOk()->json();
    expect($res['type'])->toBe('FeatureCollection')
        ->and($res['features'])->toHaveCount(1)
        ->and($res['features'][0]['properties'])->toHaveKeys(['denah_name', 'denah_classification', 'denah_area_ha', 'denah_legend_color'])
        ->and($res['features'][0]['properties']['denah_name'])->toBe('Wilayah Uji');
});

it('hides draft settlement areas from the public API', function () {
    SettlementArea::create([
        'name' => 'Draft Area', 'status' => 'draft', 'geojson_data' => $this->validGeojson,
    ]);
    $res = get('/api/settlement-areas')->assertOk()->json();
    expect($res['features'])->toBeEmpty();
});

it('redirects unauthenticated admin to login', function () {
    get('/admin/dashboard')->assertRedirect('/admin/login');
});

it('renders admin content pages when authenticated', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    actingAs($user);

    foreach ([
        '/admin/dashboard' => 'Dashboard',
        '/admin/hero' => 'Kelola Hero Beranda',
        '/admin/about' => 'Tentang DENAH',
        '/admin/stats' => 'Data dalam Angka',
        '/admin/features' => 'Fitur Utama',
        '/admin/classifications' => 'Klasifikasi',
        '/admin/settlement-areas' => 'Data Spasial',
    ] as $uri => $label) {
        get($uri)->assertOk()->assertSee($label);
    }
});

it('renders admin management pages when authenticated', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    actingAs($user);

    foreach ([
        '/admin/methodology' => 'Metodologi',
        '/admin/downloads' => 'Unduh Data',
        '/admin/footer' => 'Footer',
        '/admin/users' => 'Manajemen User',
        '/admin/media' => 'Media Library',
        '/admin/settings' => 'Pengaturan',
    ] as $uri => $label) {
        get($uri)->assertOk()->assertSee($label);
    }
})->group('slow');

it('returns a FeatureCollection from the public settlement-areas API', function () {
    get('/api/settlement-areas')->assertOk()->assertJson(['type' => 'FeatureCollection']);
});
