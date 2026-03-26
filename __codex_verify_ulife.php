<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Character::where('name', 'ULIFE')->first();
Illuminate\Support\Facades\Auth::loginUsingId($c->user_id);
$response = app(App\Http\Controllers\EventController::class)->getAvailableEvents($c);
$data = $response->getData(true);
echo json_encode([
    'ageSpecific_count' => count($data['ageSpecific'] ?? []),
    'ageSpecific_titles' => collect($data['ageSpecific'] ?? [])->pluck('title')->values()->all(),
    'current_narrative' => $data['current_narrative'] ?? null,
], JSON_PRETTY_PRINT);
