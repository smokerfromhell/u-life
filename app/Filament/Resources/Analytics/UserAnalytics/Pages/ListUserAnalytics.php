<?php

namespace App\Filament\Resources\Analytics\UserAnalytics\Pages;

use App\Filament\Resources\Analytics\UserAnalyticsResource;
use App\Models\DecisionLog;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class ListUserAnalytics extends ListRecords
{
    protected static string $resource = UserAnalyticsResource::class;

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Query DecisionLog which has both decision and stats data
        return DecisionLog::query()->orderBy('created_at', 'desc');
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                // User Info
                TextColumn::make('user_name')
                    ->label('Player')
                    ->searchable()
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('character_id')
                    ->label('Character ID')
                    ->searchable()
                    ->sortable()
                    ->size('sm'),
                
                // Account Type
                TextColumn::make('is_guest')
                    ->label('Account')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Guest' : 'Registered')
                    ->sortable(),
                
                // Personality
                TextColumn::make('mbti')
                    ->label('MBTI')
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
                    ->size('sm'),
                
                // Timeline
                TextColumn::make('day')
                    ->label('Day')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                TextColumn::make('age_group')
                    ->label('Age')
                    ->badge()
                    ->color('teal')
                    ->sortable(),
                
                // Event Info
                TextColumn::make('event_type')
                    ->label('Event Type')
                    ->badge()
                    ->color('primary')
                    ->size('sm'),
                
                TextColumn::make('event_title')
                    ->label('Event')
                    ->searchable()
                    ->wrap()
                    ->size('sm'),
                
                TextColumn::make('choice_text')
                    ->label('Choice')
                    ->searchable()
                    ->wrap()
                    ->limit(60)
                    ->size('sm'),
                
                // Before State
                TextColumn::make('before_health')
                    ->label('Health Before')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('before_happiness')
                    ->label('Happiness Before')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('before_finance')
                    ->label('Finance Before')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable()
                    ->size('sm'),
                
                // Changes (Delta)
                TextColumn::make('health_change')
                    ->label('Health Δ')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('happiness_change')
                    ->label('Happiness Δ')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('finance_change')
                    ->label('Finance Δ')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable()
                    ->size('sm'),
                
                // After State
                TextColumn::make('after_health')
                    ->label('Health After')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('after_happiness')
                    ->label('Happiness After')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('after_finance')
                    ->label('Finance After')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable()
                    ->size('sm'),
                
                // Relationship & Career
                TextColumn::make('after_relationship_status')
                    ->label('Relationship')
                    ->badge()
                    ->color('purple')
                    ->sortable()
                    ->size('sm'),
                
                TextColumn::make('after_career_level')
                    ->label('Career')
                    ->badge()
                    ->color('blue')
                    ->sortable()
                    ->size('sm'),
                
                // Timestamp
                TextColumn::make('created_at')
                    ->label('Recorded')
                    ->dateTime()
                    ->sortable()
                    ->size('sm'),
            ])
            ->filters([
                // Event Type Filter
                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options([
                        'daily' => 'Daily',
                        'cultural' => 'Cultural',
                        'ageSpecific' => 'Life Story',
                        'profession' => 'Career',
                        'trigger' => 'Trigger',
                    ]),
                
                // Age Group Filter
                SelectFilter::make('age_group')
                    ->label('Age Group')
                    ->options([
                        'child' => 'Child (0-12)',
                        'teenager' => 'Teenager (13-19)',
                        'adult' => 'Adult (20-64)',
                        'old' => 'Senior (65+)',
                    ]),
                
                // Relationship Filter
                SelectFilter::make('after_relationship_status')
                    ->label('Relationship Status')
                    ->options([
                        'single' => 'Single',
                        'dating' => 'Dating',
                        'engaged' => 'Engaged',
                        'married' => 'Married',
                        'divorced' => 'Divorced',
                        'widowed' => 'Widowed',
                    ]),
                
                // Career Filter
                SelectFilter::make('after_career_level')
                    ->label('Career Level')
                    ->options([
                        'unemployed' => 'Unemployed',
                        'entry' => 'Entry Level',
                        'junior' => 'Junior',
                        'senior' => 'Senior',
                        'manager' => 'Manager',
                        'executive' => 'Executive',
                        'retired' => 'Retired',
                    ]),
                
                // MBTI Filter
                SelectFilter::make('mbti')
                    ->label('Personality Type')
                    ->options([
                        'INTP' => 'INTP', 'INTJ' => 'INTJ', 'ENTP' => 'ENTP', 'ENTJ' => 'ENTJ',
                        'INFP' => 'INFP', 'INFJ' => 'INFJ', 'ENFP' => 'ENFP', 'ENFJ' => 'ENFJ',
                        'ISTJ' => 'ISTJ', 'ISTP' => 'ISTP', 'ESTJ' => 'ESTJ', 'ESTP' => 'ESTP',
                        'ISFJ' => 'ISFJ', 'ISFP' => 'ISFP', 'ESFJ' => 'ESFJ', 'ESFP' => 'ESFP',
                    ]),
                
                // Account Type Filter
                TernaryFilter::make('is_guest')
                    ->label('Account Type')
                    ->trueLabel('Guest')
                    ->falseLabel('Registered'),
                
                // Health Change Filter
                SelectFilter::make('health_change')
                    ->label('Health Trend')
                    ->options([
                        'improved' => 'Improved',
                        'declined' => 'Declined',
                        'stable' => 'No Change',
                    ])
                    ->query(fn ($query, $data) => match($data['value']) {
                        'improved' => $query->where('health_change', '>', 0),
                        'declined' => $query->where('health_change', '<', 0),
                        'stable' => $query->where('health_change', '=', 0),
                        default => $query,
                    }),
                
                // Happiness Change Filter
                SelectFilter::make('happiness_change')
                    ->label('Happiness Trend')
                    ->options([
                        'improved' => 'Improved',
                        'declined' => 'Declined',
                        'stable' => 'No Change',
                    ])
                    ->query(fn ($query, $data) => match($data['value']) {
                        'improved' => $query->where('happiness_change', '>', 0),
                        'declined' => $query->where('happiness_change', '<', 0),
                        'stable' => $query->where('happiness_change', '=', 0),
                        default => $query,
                    }),
                
                // Finance Change Filter
                SelectFilter::make('finance_change')
                    ->label('Finance Trend')
                    ->options([
                        'improved' => 'Improved',
                        'declined' => 'Declined',
                        'stable' => 'No Change',
                    ])
                    ->query(fn ($query, $data) => match($data['value']) {
                        'improved' => $query->where('finance_change', '>', 0),
                        'declined' => $query->where('finance_change', '<', 0),
                        'stable' => $query->where('finance_change', '=', 0),
                        default => $query,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25, 50, 100])
            ->striped();
    }
}
