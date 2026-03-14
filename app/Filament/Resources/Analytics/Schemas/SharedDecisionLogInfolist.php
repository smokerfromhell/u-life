<?php

namespace App\Filament\Resources\Analytics\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SharedDecisionLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('user_name')->label('User'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('anon_user_id')->label('Anon User'),
                TextEntry::make('anon_character_id')->label('Anon Character'),
                TextEntry::make('is_guest')->badge(),
                TextEntry::make('day')->badge(),
                TextEntry::make('event_type')->badge(),
                TextEntry::make('event_id'),
                TextEntry::make('choice_index'),
                TextEntry::make('mbti_type')->label('MBTI Type')->badge(),
                // Life Stats at time of decision
                TextEntry::make('data.life_stats.health')
                    ->label('Health')
                    ->badge()
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextEntry::make('data.life_stats.happiness')
                    ->label('Happiness')
                    ->badge()
                    ->color(fn ($state) => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextEntry::make('data.life_stats.finance')
                    ->label('Finance')
                    ->badge()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextEntry::make('data.life_stats.relationship_status')
                    ->label('Relationship')
                    ->badge(),
                TextEntry::make('data.life_stats.career_level')
                    ->label('Career')
                    ->badge(),
                KeyValueEntry::make('data')->label('Decision Data'),
            ]);
    }
}
