<?php

namespace App\Filament\Resources\Analytics\LifeStatsSnapshots\Pages;

use App\Filament\Resources\Analytics\LifeStatsSnapshotResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ListLifeStatsSnapshots extends ListRecords
{
    protected static string $resource = LifeStatsSnapshotResource::class;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user_name')->label('Player')->searchable(),
                TextColumn::make('day')->label('Day')->badge()->sortable(),
                TextColumn::make('event_type')->label('Event Type')->badge()->sortable(),
                TextColumn::make('event_title')->label('Event')->searchable(),
                TextColumn::make('choice_text')->label('Choice')->searchable()->limit(50),
                // Life Stats
                TextColumn::make('health')
                    ->label('Health')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),
                TextColumn::make('happiness')
                    ->label('Happiness')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),
                TextColumn::make('finance')
                    ->label('Finance')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('relationship_status')
                    ->label('Relationship')
                    ->badge()
                    ->sortable(),
                TextColumn::make('career_level')
                    ->label('Career')
                    ->badge()
                    ->sortable(),
                TextColumn::make('mbti')->label('MBTI')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options([
                        'daily' => 'Daily',
                        'cultural' => 'Cultural',
                        'ageSpecific' => 'Story',
                        'trigger' => 'Trigger',
                        'profession' => 'Profession',
                    ]),
                SelectFilter::make('relationship_status')
                    ->label('Relationship')
                    ->options([
                        'single' => 'Single',
                        'dating' => 'Dating',
                        'engaged' => 'Engaged',
                        'married' => 'Married',
                        'divorced' => 'Divorced',
                        'widowed' => 'Widowed',
                    ]),
                SelectFilter::make('career_level')
                    ->label('Career')
                    ->options([
                        'unemployed' => 'Unemployed',
                        'entry' => 'Entry Level',
                        'junior' => 'Junior',
                        'senior' => 'Senior',
                        'manager' => 'Manager',
                        'executive' => 'Executive',
                        'retired' => 'Retired',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
