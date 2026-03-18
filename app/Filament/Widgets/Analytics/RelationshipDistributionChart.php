<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RelationshipDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Relationship Status Distribution';

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $relationshipCounts = DecisionLog::select('after_relationship_status', DB::raw('count(*) as aggregate'))
            ->whereNotNull('after_relationship_status')
            ->where('after_relationship_status', '!=', '')
            ->groupBy('after_relationship_status')
            ->orderByDesc('aggregate')
            ->get();

        $labels = $relationshipCounts->pluck('after_relationship_status')->map(fn($label) => ucfirst($label))->all();
        $data = $relationshipCounts->pluck('aggregate')->map(fn($v) => (int) $v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Players',
                    'data' => $data,
                    'backgroundColor' => [
                        '#ec4899', '#8b5cf6', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444',
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }
}
