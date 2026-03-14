<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CharacterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('user.email')->label('User Email'),
                TextEntry::make('name'),
                TextEntry::make('age_group')->badge(),
                TextEntry::make('gender')->badge(),
                TextEntry::make('profession')->placeholder('—'),
                TextEntry::make('current_day')->label('Day'),
                TextEntry::make('current_narrative')->placeholder('—'),
                // Life Stats
                TextEntry::make('health')->label('Health')->badge()->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextEntry::make('happiness')->label('Happiness')->badge()->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextEntry::make('finance')->label('Finance')->badge()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextEntry::make('relationship_status')->label('Relationship')->badge(),
                TextEntry::make('career_level')->label('Career')->badge(),
                KeyValueEntry::make('effective_stats')->label('Effective Stats'),
                KeyValueEntry::make('stats')->label('Base Stats'),
                KeyValueEntry::make('hidden_stats')->label('Hidden Stats'),
                KeyValueEntry::make('active_event_paths')->label('Active Paths'),
                KeyValueEntry::make('completed_event_chains')->label('Completed Chains'),
                KeyValueEntry::make('shown_event_ids')->label('Shown Event IDs'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}

