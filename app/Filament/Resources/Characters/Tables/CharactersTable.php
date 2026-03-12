<?php

namespace App\Filament\Resources\Characters\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CharactersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('age_group')
                    ->badge()
                    ->sortable(),
                TextColumn::make('profession')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('current_day')
                    ->label('Day')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('age_group')->options([
                    'child' => 'Child',
                    'teenager' => 'Teenager',
                    'adult' => 'Adult',
                    'old' => 'Old',
                ]),
                TernaryFilter::make('profession')
                    ->label('Has profession')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('profession')->where('profession', '!=', ''),
                        false: fn($query) => $query->where(function ($q) {
                            $q->whereNull('profession')->orWhere('profession', '');
                        }),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
