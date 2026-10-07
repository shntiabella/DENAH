<?php
$html = file_get_contents("http://127.0.0.1:8899/peta");
$pos = strpos($html, "const CFG");
echo "pos=$pos\n";
$seg = substr($html, $pos, 200);
echo "RAW: [$seg]\n";
echo "hex: ", bin2hex(substr($html, $pos, 80)), "\n";
// cari juga tag script pembungkus di sekitar
$before = substr($html, max(0, $pos-400), 400);
echo "BEFORE: [", $before, "]\n";