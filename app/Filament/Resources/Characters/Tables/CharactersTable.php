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
                SelectFilter::make('relationship_status')->options([
                    'single' => 'Single',
                    'dating' => 'Dating',
                    'engaged' => 'Engaged',
                    'married' => 'Married',
                    'divorced' => 'Divorced',
                    'widowed' => 'Widowed',
                ])->label('Relationship'),
                SelectFilter::make('career_level')->options([
                    'unemployed' => 'Unemployed',
                    'entry' => 'Entry Level',
                    'junior' => 'Junior',
                    'senior' => 'Senior',
                    'manager' => 'Manager',
                    'executive' => 'Executive',
                    'retired' => 'Retired',
                ])->label('Career'),
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
