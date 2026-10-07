<?php
use App\Models\AdministrativeArea;
$bu = AdministrativeArea::where("level","kabupaten")->where("name","Bengkulu Utara")->first();
$geo = $bu->geojson;
echo "type: ".$geo["type"]."\n";
$polys = $geo["type"] === "MultiPolygon" ? $geo["coordinates"] : [$geo["coordinates"]];
echo "sub-poligon: ".count($polys)."\n";
function ringArea2($ring) { $a=0; $n=count($ring); for($i=0;$i<$n-1;$i++){ $a += ($ring[$i][0]*$ring[$i+1][1] - $ring[$i+1][0]*$ring[$i][1]); } return abs($a)/2; }
foreach ($polys as $i => $poly) {
    $ring = $poly[0];
    $lons = array_column($ring, 0); $lats = array_column($ring, 1);
    printf("poly %d: pts=%d areaDeg=%.4f bbox lon %.3f..%.3f lat %.3f..%.3f\n", $i, count($ring), ringArea2($ring), min($lons), max($lons), min($lats), max($lats));
}