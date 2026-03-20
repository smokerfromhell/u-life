<?php

namespace App\Filament\Resources\Analytics\SharedCharacterData\Pages;

use App\Filament\Resources\Analytics\SharedCharacterDataResource;
use App\Filament\Widgets\CharacterPersonality\CharacterBigFiveChart;
use App\Filament\Widgets\CharacterPersonality\CharacterMbtiChart;
use App\Filament\Widgets\CharacterStats\CharacterReputationTable;
use App\Filament\Widgets\CharacterStats\CharacterTraumaTable;
use App\Filament\Widgets\CharacterStats\CharacterAchievementsTable;
use App\Filament\Widgets\CharacterStats\CharacterLuckStatsWidget;
use App\Filament\Widgets\CharacterStats\CharacterDecisionHistoryTable;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSharedCharacterData extends ViewRecord
{
    protected static string $resource = SharedCharacterDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CharacterMbtiChart::class,
            CharacterBigFiveChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CharacterReputationTable::class,
            CharacterTraumaTable::class,
            CharacterAchievementsTable::class,
            CharacterLuckStatsWidget::class,
            CharacterDecisionHistoryTable::class,
        ];
    }
}
