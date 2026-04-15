<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

echo "Starting Migration...\n";

// 1. Move Files
$storagePath = storage_path('app/public');
$publicUploadsPath = public_path('uploads');

if (!File::exists($publicUploadsPath)) {
    File::makeDirectory($publicUploadsPath, 0755, true);
}

$directories = File::directories($storagePath);

foreach ($directories as $dir) {
    $dirName = basename($dir);
    $destPath = $publicUploadsPath . DIRECTORY_SEPARATOR . $dirName;
    
    echo "Processing directory: $dirName\n";
    
    if (File::exists($destPath)) {
        echo "  Destination already exists, merging contents...\n";
        File::copyDirectory($dir, $destPath);
        // We might want to delete afterwards, but copy is safer for now.
    } else {
        echo "  Moving directory to $destPath...\n";
        File::copyDirectory($dir, $destPath);
    }
}

echo "Files copied to public/uploads/.\n";

// 2. Update Database
$tablesToUpdate = [
    'users' => ['avatar'],
    'technician_requests' => ['photo', 'iqama_photo'],
    'technicians' => ['image', 'national_id_image'],
    'services' => ['image', 'icon'],
    'content_items' => ['image'],
    'contract_payment_receipts' => ['receipt_file'],
    'order_attachments' => ['file_path'],
    'maintenance_companies' => ['commercial_record_file'],
    'orders' => ['client_signature'],
];

foreach ($tablesToUpdate as $table => $columns) {
    if (!Schema::hasTable($table)) {
        echo "Table $table does not exist, skipping.\n";
        continue;
    }

    foreach ($columns as $column) {
        if (!Schema::hasColumn($table, $column)) {
            echo "  Column $column in $table does not exist, skipping.\n";
            continue;
        }

        echo "  Updating $table.$column...\n";
        
        // This query prepends 'uploads/' if it doesn't already start with 'uploads/' 
        // and if the column is NOT NULL/empty.
        // We use a regex or LIKE to be safe.
        
        $count = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, 'NOT LIKE', 'uploads/%')
            ->where($column, 'NOT LIKE', 'http%') // Skip full URLs
            ->update([
                $column => DB::raw("CONCAT('uploads/', $column)")
            ]);
            
        echo "    Updated $count records.\n";
    }
}

echo "Migration Complete!\n";
