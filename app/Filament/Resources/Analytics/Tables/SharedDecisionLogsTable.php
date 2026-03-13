<?php

namespace App\Filament\Resources\Analytics\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SharedDecisionLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('event_type')->badge()->sortable(),
                TextColumn::make('day')->badge()->sortable(),
                IconColumn::make('is_guest')->boolean()->label('Guest')->sortable(),
                TextColumn::make('event_id')->sortable(),
                TextColumn::make('choice_index')->sortable(),
                
                // MBTI Personality Trait
                TextColumn::make('data.mbti')
                    ->label('MBTI Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'INTP' => 'gray',
                        'INTJ' => 'indigo',
                        'ENTP' => 'amber',
                        'ENTJ' => 'blue',
                        'INFP' => 'rose',
                        'INFJ' => 'purple',
                        'ENFP' => 'emerald',
                        'ENFJ' => 'green',
                        'ISTJ' => 'slate',
                        'ISTP' => 'cyan',
                        'ESTJ' => 'zinc',
                        'ESTP' => 'orange',
                        'ISFJ' => 'pink',
                        'ISFP' => 'violet',
                        'ESFJ' => 'yellow',
                        'ESFP' => 'red',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ?? 'Analyzing...'),
                
                TextColumn::make('anon_user_id')
                    ->label('Anon User')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => (bool) Auth::user()?->hasRole('Super Admin')),
                TextColumn::make('anon_character_id')
                    ->label('Anon Character')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn() => (bool) Auth::user()?->hasRole('Super Admin')),
            ])
            ->filters([
                SelectFilter::make('event_type')->options([
                    'daily' => 'Daily',
                    'cultural' => 'Cultural',
                    'ageSpecific' => 'Story',
                    'trigger' => 'Trigger',
                    'profession' => 'Profession',
                ]),
                TernaryFilter::make('is_guest')
                    ->label('Guest sessions')
                    ->trueLabel('Guest')
                    ->falseLabel('Registered'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn() => (bool) Auth::user()?->hasRole('Super Admin')),
            ]);
    }
}
