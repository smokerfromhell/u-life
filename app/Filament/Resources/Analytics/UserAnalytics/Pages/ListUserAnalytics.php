<?php

namespace App\Filament\Resources\Analytics\UserAnalytics\Pages;

use App\Filament\Resources\Analytics\UserAnalyticsResource;
use App\Models\SharedDecisionLog;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListUserAnalytics extends ListRecords
{
    protected static string $resource = UserAnalyticsResource::class;

    protected function getTableQuery(): Builder
    {
return SharedDecisionLog::query()
            ->selectRaw('
                user_name,  
                anon_user_id, 
                is_guest, 
                COUNT(*) as decision_count, 
                MAX(created_at) as last_active, 
                MIN(created_at) as first_active, 
                MAX(id) as sort_id
            ')
            ->groupBy('user_name', 'anon_user_id', 'is_guest')
            ->orderByDesc('decision_count')
            ->orderByDesc('sort_id');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_name')
                    ->label('Player Name')
                    ->searchable()
                    ->sortable()
                    ->size('md'),

                BadgeColumn::make('is_guest')
                    ->label('Account Type')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Guest' : 'Registered'),

                TextColumn::make('decision_count')
                    ->label('Total Decisions')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('first_active')
                    ->label('First Activity')
                    ->dateTime('M j, Y')
                    ->sortable(),

                TextColumn::make('last_active')
                    ->label('Last Active')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_guest')
                    ->label('Account Type')
                    ->trueLabel('Guest')
                    ->falseLabel('Registered'),
            ])
            ->actions([
                Action::make('view')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => '/admin/decision-logs?tableFilters[user_name][value]=' . urlencode($record->user_name)),
            ])
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}

