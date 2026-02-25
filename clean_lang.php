<?php
$files = ['lang/ar.json', 'lang/en.json'];
foreach ($files as $file) {
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        ksort($data);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Cleaned $file\n";
    }
}
