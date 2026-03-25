<?php

namespace App\Filament\Resources\Analytics\Pages;

use App\Filament\Resources\Analytics\SharedDecisionLogResource;
use App\Models\SharedDecisionLog;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListSharedDecisionLogs extends ListRecords
{
    protected static string $resource = SharedDecisionLogResource::class;

    /**
     * Get header widgets - showing summary stats above the table
     */
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\Analytics\SharedDecisionLogsStatsWidget::class,
        ];
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('anon_character_id')
                    ->label('Character ID')
                    ->copyable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('user_name')
                    ->label('Player Name')
                    ->searchable()
                    ->sortable(),
                
                IconColumn::make('is_guest')
                    ->label('Type')
                    ->boolean()
                    ->trueIcon('heroicon-o-user')
                    ->falseIcon('heroicon-o-user-group')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn ($record) => $record->is_guest ? 'Guest Player' : 'Registered User'),
                
                TextColumn::make('day')
                    ->label('Day')
                    ->badge('primary')
                    ->sortable(),
                
                TextColumn::make('event_type')
                    ->label('Event Type')
                    ->badge(fn (string $state): string => match ($state) {
                        'daily' => 'info',
                        'cultural' => 'success',
                        'ageSpecific' => 'warning',
                        'profession' => 'purple',
                        'health' => 'danger',
                        'family' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'daily' => '📅 Daily Event',
                        'cultural' => '🎭 Cultural Event',
                        'ageSpecific' => '📖 Story Event',
                        'profession' => '💼 Career Event',
                        'health' => '❤️ Health Event',
                        'family' => '👨‍👩‍👧 Family Event',
                        default => ucfirst($state),
                    })
                    ->sortable(),
                
                TextColumn::make('event_title')
                    ->label('Event Title')
                    ->searchable()
                    ->wrap()
                    ->limit(40),
                
                TextColumn::make('choice_text')
                    ->label('Player Choice')
                    ->searchable()
                    ->wrap()
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->choice_text),
                
                TextColumn::make('effects')
                    ->label('Stat Effects')
                    ->wrap()
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($record) => $record->effects),
                
                TextColumn::make('mbti')
                    ->label('Personality')
                    ->badge('gray')
                    ->formatStateUsing(fn ($state) => $state ?: 'Analyzing...')
                    ->tooltip(fn ($record) => $this->getMbtiTooltip($record)),
                
                TextColumn::make('profession')
                    ->label('Career')
                    ->badge('info')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst($state) : 'Unemployed'),
                
                TextColumn::make('created_at')
                    ->label('Played At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Event Type')
                    ->options([
                        'daily' => '📅 Daily Events',
                        'cultural' => '🎭 Cultural Events',
                        'ageSpecific' => '📖 Story Events',
                        'profession' => '💼 Career Events',
                        'health' => '❤️ Health Events',
                        'family' => '👨‍👩‍👧 Family Events',
                    ]),
                
                SelectFilter::make('is_guest')
                    ->label('Player Type')
                    ->options([
                        '1' => '👤 Guest Players',
                        '0' => '👥 Registered Users',
                    ]),
                
                SelectFilter::make('profession')
                    ->label('Career')
                    ->options(fn () => \App\Models\SharedDecisionLog::distinct()
                        ->pluck('profession', 'profession')
                        ->filter()
                        ->mapWithKeys(fn ($p) => [ucfirst($p) => ucfirst($p)])
                        ->toArray()),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions(['10', '25', '50', '100'])
            ->persistColumnInSession();
    }

    /**
     * Get tooltip for MBTI type
     */
    protected function getMbtiTooltip($record): string
    {
        if (!$record->mbti) {
            return 'Personality being calculated...';
        }
        
        $descriptions = [
            'E' => 'Extraverted',
            'I' => 'Introverted',
            'N' => 'Intuitive',
            'S' => 'Sensing',
            'T' => 'Thinking',
            'F' => 'Feeling',
            'J' => 'Judging',
            'P' => 'Perceiving',
        ];
        
        $traits = str_split($record->mbti);
        $tooltip = [];
        
        foreach ($traits as $trait) {
            if (isset($descriptions[$trait])) {
                $tooltip[] = $descriptions[$trait];
            }
        }
        
        return implode(' / ', $tooltip);
    }
}
