<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Characters\CharacterResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CharactersRelationManager extends RelationManager
{
    protected static string $relationship = 'characters';

    protected static ?string $title = 'Characters';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('age_group')->badge()->sortable(),
                TextColumn::make('profession')->placeholder('—')->sortable(),
                TextColumn::make('current_day')->label('Day')->sortable(),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn($record) => CharacterResource::getUrl('view', ['record' => $record])),
            ]);
    }
}

