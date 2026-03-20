<?php

namespace App\Filament\Widgets\CharacterStats;

use App\Models\Character;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CharacterTraumaTable extends BaseWidget
{
    public ?Character $record = null;

    protected function getStats(): array
    {
        $traumas = $this->record?->getTraumaTypes() ?? [];
        $traumaFlags = $this->record?->trauma_flags ?? [];
        
        $traumaTypes = array_unique($traumas);
        $count = count($traumaFlags);
        
        return [
            Stat::make('Total Traumas', $count)
                ->description('Unique types: ' . count($traumaTypes))
                ->color($count > 0 ? 'danger' : 'success'),
            Stat::make('Trauma Types', implode(', ', $traumaTypes) ?: 'None')
                ->description('Emotional challenges')
                ->color($count > 0 ? 'warning' : 'gray'),
        ];
    }
}
