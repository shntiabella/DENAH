<?php
use Illuminate\Support\Js;
$out = Js::from(["province" => ["id" => 1, "name" => "Bengkulu"]])->toHtml();
echo $out, "\n";
echo "hex 40: ", bin2hex(substr($out, 0, 40)), "\n";
file_put_contents("storage/js_out.txt", $out);