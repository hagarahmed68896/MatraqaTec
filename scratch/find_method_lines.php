<?php
$content = file_get_contents('app/Http/Controllers/Api/OrderController.php');
$lines = explode("\n", $content);
$methods = ['accept', 'technicianAccept', 'technicianRefuse', 'refuse', 'reschedule', 'respondToReschedule'];
foreach ($methods as $method) {
    foreach ($lines as $i => $line) {
        if (preg_match('/public function ' . $method . '\b/', $line)) {
            echo "$method starts at line " . ($i + 1) . "\n";
            break;
        }
    }
}
