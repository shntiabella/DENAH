<?php
$html = file_get_contents("http://127.0.0.1:8899/peta");
preg_match("/const CFG = JSON\.parse\(&#039;(.+?)&#039;\);/s", $html, $m);
$inner = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, "UTF-8");
echo "RAW 220 chars: ".substr($inner, 0, 220)."\n";
echo "hexdump 40 chars: ".bin2hex(substr($inner, 0, 40))."\n";