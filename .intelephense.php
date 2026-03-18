<?php

/**
 * Intelephense Configuration
 * 
 * This configuration file tells Intelephense about Laravel stubs
 * to resolve undefined method/type errors for facades like Auth and Log
 */

$composer = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

// Check if laravel-intelephense stubs are installed
$hasLaravelStubs = isset($composer['require']['dev']['laravel-intelephense/enum']) || 
                   isset($composer['require-dev']['laravel-intelephense/enum']);

return [
    /**
     * Intelephense PHP Environment
     */
    'phpVersion' => '8.1.0',
    
    /**
     * Stub files for Laravel framework
     * This helps Intelephense recognize Laravel facades and Filament
     */
    'stubs' => [
        'laravel',
        'Illuminate\Support\Facades\Auth',
        'Illuminate\Support\Facades\Log',
        'Illuminate\Support\Facades\Request',
        'Illuminate\Support\Facades\Route',
        'Illuminate\Support\Facades\DB',
        'Illuminate\Support\Facades\Storage',
        'Illuminate\Support\Facades\Cache',
        'filament',
        'filament\actions',
        'filament\forms',
        'filament\tables',
        'filament\widgets',
    ],
    
    /**
     * Exclude unnecessary directories from analysis
     */
    'exclude' => [
        'vendor',
        'node_modules',
        'bootstrap/cache',
        'storage',
        'database/migrations',
    ],
    
    /**
     * Enable intelligent PHP built-in function completion
     */
    'completions' => [
        'phpdoc' => true,
        'builtin' => true,
    ],
    
    /**
     * Max memory usage in MB
     */
    'maxMemory' => 512,
];
