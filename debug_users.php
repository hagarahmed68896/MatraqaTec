<?php
use App\Models\User;

$users = User::select('id', 'email', 'type')->get();
foreach ($users as $u) {
    echo "ID: {$u->id}, Email: {$u->email}, Type: {$u->type}" . PHP_EOL;
}
