<?php

namespace App\Filament\Resources\Analytics\Pages;

use App\Filament\Resources\Analytics\SharedDecisionLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ListSharedDecisionLogs extends ListRecords
{
    protected static string $resource = SharedDecisionLogResource::class;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('anon_character_id')->label('Character ID')->sortable(),
                TextColumn::make('user_name')->label('Player')->searchable(),
                IconColumn::make('is_guest')->label('Guest')->boolean(),
                TextColumn::make('day')->label('Day')->badge()->sortable(),
                TextColumn::make('event_type')->label('Event Type')->badge()->sortable(),
                TextColumn::make('event_title')->label('Event')->searchable(),
                TextColumn::make('choice_text')->label('Choice')->searchable()->limit(50),
                TextColumn::make('mbti')->label('MBTI')->badge(),
                TextColumn::make('profession')->label('Profession')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options([
                        'daily' => 'Daily',
                        'cultural' => 'Cultural',
                        'ageSpecific' => 'Story',
                        'profession' => 'Profession',
                    ]),
                SelectFilter::make('is_guest')
                    ->label('User Type')
                    ->options([
                        '1' => 'Guest',
                        '0' => 'Registered User',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
