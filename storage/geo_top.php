<?php
$data = json_decode(file_get_contents("public/data/data_2025.geojson"), true);
$features = $data["features"];
usort($features, fn($a,$b) => ($b["properties"]["count"]??0) <=> ($a["properties"]["count"]??0));
foreach (array_slice($features,0,5) as $f) {
    $ring = $f["geometry"]["coordinates"][0][0];
    $lons = array_column($ring,0); $lats = array_column($ring,1);
    echo "count=".$f["properties"]["count"]." classifica=".$f["properties"]["classifica"]." bbox: lon ".min($lons)."..".max($lons)." lat ".min($lats)."..".max($lats)." pts=".count($ring).PHP_EOL;
}
echo "--- distribution ---".PHP_EOL;
$buckets=[];
foreach($data["features"] as $f){ $c=$f["properties"]["count"]; if($c<10)$b="0-9"; elseif($c<100)$b="10-99"; elseif($c<1000)$b="100-999"; elseif($c<10000)$b="1k-10k"; else $b=">=10k"; $buckets[$b]=($buckets[$b]??0)+1; }
print_r($buckets);