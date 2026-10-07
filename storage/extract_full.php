<?php
$html = file_get_contents("http://127.0.0.1:8899/peta");
preg_match('/wire:effects="([^"]+)"/', $html, $m);
$effects = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, "UTF-8"), true);
$script = $effects["scripts"][array_key_first($effects["scripts"])];
// buang tag <script> pembungkus agar murni JS
$script = preg_replace('#^\s*<script>|</script>\s*$#', '', $script);
file_put_contents("storage/full_script.js", $script);
echo "script bytes=" . strlen($script) . "\n";