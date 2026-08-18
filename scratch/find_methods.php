<?php
$content = file_get_contents('app/Http/Controllers/Api/OrderController.php');
preg_match_all('/public function \w+\([^)]*\)/', $content, $m);
print_r($m[0]);
