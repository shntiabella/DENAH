<?php
$html = file_get_contents("http://127.0.0.1:8899/peta");
if (!preg_match('/wire:effects="([^"]+)"/', $html, $m)) { echo "wire:effects tidak ada\n"; exit(1); }
$effects = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, "UTF-8"), true);
$script = $effects["scripts"][array_key_first($effects["scripts"])] ?? null;
if (!$script) { echo "script kosong\n"; exit(1); }
if (!preg_match("/const CFG = (JSON\.parse\('.+?'\));/s", $script, $mm)) { echo "CFG line tidak ada\n"; exit(1); }
file_put_contents("storage/cfg_eval.cjs", "const CFG = " . $mm[1] . ";\nconsole.log(\"OK:\", JSON.stringify(CFG.endpoints), \"| kelas:\", CFG.classifications.length, \"| fileYears:\", CFG.fileYears.join(\",\"));\n");
echo "tersimpan, panjang script=" . strlen($script) . "\n";