<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get character
$c = App\Models\Character::first();

// Simulate what getSystemActions returns
$dailyActions = App\Models\DailyAction::orderBy('display_order')->get();

$miniGames = [];
foreach ($dailyActions as $action) {
    if ($action->isAvailable($c)) {
        $arr = $action->toActionArray();
        if (!empty($arr['mini_game'])) {
            $miniGames[] = [
                'id' => $arr['id'],
                'title' => $arr['title'],
                'mini_game' => $arr['mini_game'],
                'choices_count' => count($arr['choices']),
            ];
        }
    }
}

echo "=== Events with mini_game returned by API ===\n";
echo "Total: " . count($miniGames) . "\n\n";
foreach ($miniGames as $mg) {
    echo "- ID: {$mg['id']} | {$mg['title']} | mini_game: {$mg['mini_game']} | choices: {$mg['choices_count']}\n";
}