<?php
// Simple script to run migration and seed
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Run migrate:fresh --seed
echo "Running migrations...\n";
Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);

echo "Done!\n";
echo "Output: " . Illuminate\Support\Facades\Artisan::output();
