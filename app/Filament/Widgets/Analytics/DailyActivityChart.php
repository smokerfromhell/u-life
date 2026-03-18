<?php

namespace App\Filament\Widgets\Analytics;

use App\Models\DecisionLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DailyActivityChart extends ChartWidget
{
    protected ?string $heading = 'Daily Activity (Last 14 Days)';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $since = Carbon::now()->subDays(14);

        // Get daily decision counts
        $dailyCounts = DecisionLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as aggregate')
        )
            ->where('created_at', '>=', $since)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Fill in missing dates with 0
        $dateRange = Carbon::now()->subDays(14)->toPeriod(Carbon::now());
        $dataMap = $dailyCounts->pluck('aggregate', 'date')->toArray();
        
        $labels = [];
        $data = [];
        
        foreach ($dateRange as $date) {
            $dateStr = $date->toDateString();
            $labels[] = $date->format('M j');
            $data[] = $dataMap[$dateStr] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Decisions',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
