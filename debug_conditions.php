<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$c = App\Models\Character::first();

echo "=== Simulating getSystemActions() ===\n";

// Get all daily actions
$dailyActions = App\Models\DailyAction::orderBy('display_order')->get();

// Filter by isAvailable and collect mini_game info
$actionsWithMiniGame = [];
foreach ($dailyActions as $action) {
    if ($action->isAvailable($c)) {
        $arr = $action->toActionArray();
        if (!empty($arr['mini_game'])) {
            $actionsWithMiniGame[] = [
                'id' => $arr['id'],
                'title' => $arr['title'],
                'mini_game' => $arr['mini_game'],
            ];
        }
    }
}

echo "Actions with mini_game returned by getSystemActions: " . count($actionsWithMiniGame) . "\n";
echo json_encode($actionsWithMiniGame, JSON_PRETTY_PRINT) . "\n\n";

// Check if there are duplicate titles in daily_actions
echo "=== Check for duplicate titles ===\n";
$titleCounts = App\Models\DailyAction::select('title', DB::raw('count(*) as count'))
    ->groupBy('title')
    ->having('count', '>', 1)
    ->get();
foreach ($titleCounts as $tc) {
    echo "Title: '{$tc->title}' appears {$tc->count} times\n";
}