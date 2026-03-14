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
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('anon_user_id')->label('Anon User'),
                TextEntry::make('anon_character_id')->label('Anon Character'),
                TextEntry::make('is_guest')->badge(),
                TextEntry::make('day')->badge(),
                TextEntry::make('event_type')->badge(),
                TextEntry::make('event_id'),
                TextEntry::make('choice_index'),
                KeyValueEntry::make('data')->label('Encrypted Payload (Decrypted)'),
            ]);
    }
}
