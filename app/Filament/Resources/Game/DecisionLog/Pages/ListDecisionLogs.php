<?php

namespace App\Filament\Resources\Game\DecisionLog\Pages;

use App\Filament\Resources\Game\DecisionLogResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ListDecisionLogs extends ListRecords
{
    protected static string $resource = DecisionLogResource::class;

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('character_id')->label('Character ID')->sortable(),
                TextColumn::make('user_name')->label('Player')->searchable(),
                TextColumn::make('day')->label('Day')->badge()->sortable(),
                TextColumn::make('age_group')->label('Age')->badge()->sortable(),
                TextColumn::make('event_type')->label('Event Type')->badge()->sortable(),
                TextColumn::make('event_title')->label('Event')->searchable(),
                TextColumn::make('choice_text')->label('Choice')->searchable()->limit(50),
                // Before state
                TextColumn::make('before_health')->label('Health Before')->badge()->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextColumn::make('before_happiness')->label('Happiness Before')->badge()->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextColumn::make('before_finance')->label('Finance Before')->badge()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                // Changes
                TextColumn::make('health_change')->label('Health Δ')->badge()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('happiness_change')->label('Happiness Δ')->badge()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('finance_change')->label('Finance Δ')->badge()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                // After state
                TextColumn::make('after_health')->label('Health After')->badge()->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextColumn::make('after_happiness')->label('Happiness After')->badge()->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextColumn::make('after_finance')->label('Finance After')->badge()->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('after_relationship_status')->label('Relationship')->badge(),
                TextColumn::make('after_career_level')->label('Career')->badge(),
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
                SelectFilter::make('age_group')
                    ->label('Age Group')
                    ->options([
                        'child' => 'Child',
                        'teenager' => 'Teenager',
                        'adult' => 'Adult',
                        'old' => 'Old',
                    ]),
                SelectFilter::make('after_relationship_status')
                    ->label('Relationship')
                    ->options([
                        'single' => 'Single',
                        'dating' => 'Dating',
                        'engaged' => 'Engaged',
                        'married' => 'Married',
                        'divorced' => 'Divorced',
                        'widowed' => 'Widowed',
                    ]),
                SelectFilter::make('after_career_level')
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
