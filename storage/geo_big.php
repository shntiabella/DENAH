<?php
$data = json_decode(file_get_contents("public/data/data_2025.geojson"), true);
$features = $data["features"];
// find the big one
$big = null;
foreach ($features as $f) { if (($f["properties"]["count"] ?? 0) > 100000000) { $big = $f; break; } }
$coords = $big["geometry"]["coordinates"];
echo "big feature: polygons=".count($coords).PHP_EOL;
$ringCount = 0; $ptsTotal = 0; $sizes = [];
foreach ($coords as $poly) { foreach ($poly as $ring) { $ringCount++; $n = count($ring); $ptsTotal += $n; $sizes[] = $n; } }
echo "rings=$ringCount ptsTotal=$ptsTotal".PHP_EOL;
sort($sizes);
echo "ring size min=".$sizes[0]." median=".$sizes[intdiv(count($sizes),2)]." max=".end($sizes).PHP_EOL;
// area via shoelace (deg^2 approx, then km2 w/ cos lat)
function ringArea($ring) { $a=0; $n=count($ring); for($i=0;$i<$n-1;$i++){ $a += ($ring[$i][0]*$ring[$i+1][1] - $ring[$i+1][0]*$ring[$i][1]); } return abs($a)/2; }
$areaDeg = 0;
foreach ($coords as $poly) { $outer = ringArea($poly[0]); for($i=1;$i<count($poly);$i++){ $outer -= ringArea($poly[$i]); } $areaDeg += $outer; }
$km2 = $areaDeg * 111.32 * 111.32 * cos(deg2rad(-3.6));
echo "big feature area ~ ".round($km2,1)." km2".PHP_EOL;
// typical small feature
$small = $features[10];
$sc = $small["geometry"]["coordinates"];
$sd = 0; foreach ($sc as $poly) { $o = ringArea($poly[0]); for($i=1;$i<count($poly);$i++){ $o -= ringArea($poly[$i]); } $sd += $o; }
echo "sample small feature: polys=".count($sc)." count=".$small["properties"]["count"]." area ~ ".round($sd*111.32*111.32*cos(deg2rad(-3.6)),6)." km2".PHP_EOL;