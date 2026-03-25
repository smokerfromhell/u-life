<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$c = App\Models\Character::first();
if ($c) {
    echo "Character ID: " . $c->id . "\n";
    echo "Name: " . $c->name . "\n";
    
    // Simulate what getSystemActions does
    $actions = App\Models\DailyAction::where('type', 'system')->get();
    
    $availableActions = [];
    foreach ($actions as $action) {
        if ($action->isAvailable($c)) {
            $actionData = $action->toActionArray();
            if (!empty($actionData['mini_game'])) {
                $availableActions[] = $actionData;
            }
        }
    }
    
    echo "\n=== Actions with mini_game from toActionArray() ===\n";
    echo json_encode($availableActions, JSON_PRETTY_PRINT);
    
    // Now check directly the mini_game field
    echo "\n\n=== Direct mini_game_type from database ===\n";
    $learnEvents = App\Models\DailyAction::where('title', 'like', '%Learn%')->whereNotNull('mini_game_type')->get(['id', 'title', 'mini_game_type', 'type']);
    foreach ($learnEvents as $event) {
        echo "- ID: " . $event->id . " | " . $event->title . " | mini_game_type: " . $event->mini_game_type . "\n";
    }
} else {
    echo "No character found\n";
}