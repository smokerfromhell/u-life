<?php
/**
 * Web-based Migration Runner for Railway
 * 
 * WARNING: Delete this file after use!
 * 
 * Upload to your Railway public folder and visit /migrate.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "<h1>Database Migration Runner</h1>";

try {
    // Check if tables exist
    $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTableNames();
    echo "<p>Current tables: " . count($tables) . "</p>";
    
    // Run migrations
    echo "<p>Running migrations...</p>";
    $output = new Symfony\Component\Console\Output\BufferedOutput();
    $result = \Illuminate\Support\Facades\Artisan::call('migrate --force', [], $output);
    
    echo "<pre>" . $output->fetch() . "</pre>";
    
    if ($result === 0) {
        echo "<p style='color:green'>✓ Migrations completed successfully!</p>";
    } else {
        echo "<p style='color:red'>✗ Migration failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><strong>DELETE THIS FILE AFTER USE!</strong></p>";
