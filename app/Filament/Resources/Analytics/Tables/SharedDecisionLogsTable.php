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
                // MBTI Type - styled as a personality badge
                TextColumn::make('mbti_type')
                    ->label('Personality')
                    ->badge()
                    ->size('sm')
                    ->fontFamily('mono')
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
                        'Analyzing...' => 'warning',
                        'Pending' => 'gray',
                        default => 'gray',
                    }),
                
                // User Name - Who made the decision
                TextColumn::make('user_name')
                    ->label('Player')
                    ->searchable()
                    ->size('sm'),
                
                // Event Type - styled as badge
                TextColumn::make('event_type')
                    ->label('Event Type')
                    ->badge()
                    ->color('info')
                    ->size('sm'),
                
                // Event Title - What happened
                TextColumn::make('event_title')
                    ->label('Event')
                    ->searchable()
                    ->size('sm')
                    ->wrap(),
                
                // Choice Text - What they chose
                TextColumn::make('choice_text')
                    ->label('Choice')
                    ->searchable()
                    ->size('sm')
                    ->wrap()
                    ->limit(50),
                
                // Day - when it happened
                TextColumn::make('day')
                    ->label('Day')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                
                // Guest indicator
                IconColumn::make('is_guest')
                    ->boolean()
                    ->label('Guest')
                    ->sortable()
                    ->size('sm'),
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
                TernaryFilter::make('is_guest')
                    ->label('Account Type')
                    ->trueLabel('Guest')
                    ->falseLabel('Registered'),
                SelectFilter::make('mbti_type')
                    ->label('Personality Type')
                    ->options([
                        'INTP' => 'INTP', 'INTJ' => 'INTJ', 'ENTP' => 'ENTP', 'ENTJ' => 'ENTJ',
                        'INFP' => 'INFP', 'INFJ' => 'INFJ', 'ENFP' => 'ENFP', 'ENFJ' => 'ENFJ',
                        'ISTJ' => 'ISTJ', 'ISTP' => 'ISTP', 'ESTJ' => 'ESTJ', 'ESTP' => 'ESTP',
                        'ISFJ' => 'ISFJ', 'ISFP' => 'ISFP', 'ESFJ' => 'ESFJ', 'ESFP' => 'ESFP',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->visible(fn() => (bool) Auth::user()?->hasRole('Super Admin')),
            ]);
    }
}
