<?php
use App\Models\Classification;
use App\Models\SettlementArea;
use App\Models\AdministrativeArea;

foreach (Classification::all() as $c) { echo $c->id.' | '.$c->name.' | '.$c->legend_color.' | order='.$c->sort_order.PHP_EOL; }
echo '--- sample settlement ---'.PHP_EOL;
$s = SettlementArea::first();
if ($s) { $a = $s->toArray(); unset($a['geojson_data']); print_r($a); }
echo '--- distinct confidence ---'.PHP_EOL;
print_r(SettlementArea::distinct()->pluck('confidence')->toArray());
echo '--- distinct years ---'.PHP_EOL;
print_r(SettlementArea::distinct()->orderBy('year')->pluck('year')->toArray());
echo '--- province row ---'.PHP_EOL;
$p = AdministrativeArea::where('level','provinsi')->first();
echo $p->id.' | '.$p->name.' | '.$p->lat.' | '.$p->lng.' | geo='.(is_array($p->geojson)?'yes':'no').PHP_EOL;
echo '--- regencies ---'.PHP_EOL;
foreach (AdministrativeArea::where('level','kabupaten')->orderBy('name')->get() as $r) { echo $r->id.' | '.$r->name.' | parent='.$r->parent_id.' | geo='.(is_array($r->geojson)?'yes':'no').PHP_EOL; }
$lines = file(__DIR__.'/resources/views/livewire/public/metodologi.blade.php');
for ($i = 53; $i <= 58 && isset($lines[$i]); $i++) {
    echo ($i+1).':>>>'.str_replace("\t","<T>",$lines[$i])."<<<\n";
}
