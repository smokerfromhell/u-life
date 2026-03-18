<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ChoicesPopularityChart extends ChartWidget
{
    protected ?string $heading = 'Top 10 Popular Choices';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $choiceCounts = DecisionLog::select('choice_text', DB::raw('count(*) as aggregate'))
            ->whereNotNull('choice_text')
            ->where('choice_text', '!=', '')
            ->groupBy('choice_text')
            ->orderByDesc('aggregate')
            ->limit(10)
            ->get();

        $labels = $choiceCounts->pluck('choice_text')->map(fn($text) => substr($text, 0, 30) . (strlen($text) > 30 ? '...' : ''))->all();
        $data = $choiceCounts->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Times Selected',
                    'data' => $data,
                    'backgroundColor' => '#8b5cf6',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
