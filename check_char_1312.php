<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find character by name
$c = App\Models\Character::where('name', '1312')->first();

if (!$c) {
    // Try finding by partial name
    $c = App\Models\Character::where('name', 'like', '%1312%')->first();
}

if (!$c) {
    echo "Character not found. Available characters:\n";
    $chars = App\Models\Character::pluck('name', 'id');
    foreach ($chars as $id => $name) {
        echo "- ID $id: $name\n";
    }
    exit;
}

echo "=== Character 1312 Info ===\n";
echo "ID: {$c->id}\n";
echo "Name: {$c->name}\n";
echo "Age: '{$c->age}'\n";
echo "Age group: {$c->age_group}\n";
echo "Skills: " . json_encode($c->skills->pluck('name')->toArray()) . "\n";
echo "Life stats: " . json_encode($c->life_stats) . "\n\n";

// Check what events with mini_game are available for this character
$dailyActions = App\Models\DailyAction::orderBy('display_order')->get();

$miniGames = [];
$allAvailable = [];
foreach ($dailyActions as $action) {
    if ($action->isAvailable($c)) {
        $arr = $action->toActionArray();
        $allAvailable[] = $arr['title'];
        if (!empty($arr['mini_game'])) {
            $miniGames[] = [
                'id' => $arr['id'],
                'title' => $arr['title'],
                'mini_game' => $arr['mini_game'],
                'conditions' => $action->conditions,
            ];
        }
    }
}

echo "=== Available events: " . count($allAvailable) . " total ===\n";
echo "First 20: " . implode(', ', array_slice($allAvailable, 0, 20)) . "...\n\n";

echo "=== Events with mini_game: " . count($miniGames) . " ===\n";
foreach ($miniGames as $mg) {
    echo "- ID: {$mg['id']} | {$mg['title']} | mini_game: {$mg['mini_game']}\n";
    echo "  Conditions: " . json_encode($mg['conditions']) . "\n\n";
}