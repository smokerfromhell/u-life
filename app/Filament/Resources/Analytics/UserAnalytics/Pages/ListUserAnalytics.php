<?php

namespace App\Filament\Resources\Analytics\UserAnalytics\Pages;

use App\Filament\Resources\Analytics\UserAnalyticsResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListUserAnalytics extends ListRecords
{
    protected static string $resource = UserAnalyticsResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->size('md'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_guest')
                    ->label('Guest')
                    ->boolean(),

                TextColumn::make('characters_count')
                    ->label('Characters')
                    ->counts('characters')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M j, Y')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
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
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => UserAnalyticsResource::getUrl('view', ['record' => $record->id])),
            ])
            ->paginated([10, 25, 50, 100])
            ->striped();
    }
}
