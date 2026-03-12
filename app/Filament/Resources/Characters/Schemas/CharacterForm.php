<?php

namespace App\Filament\Resources\Characters\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CharacterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('age_group')
                    ->options([
                        'child' => 'Child',
                        'teenager' => 'Teenager',
                        'adult' => 'Adult',
                        'old' => 'Old',
                    ])
                    ->required(),
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'non-binary' => 'Non-Binary',
                        'transgender' => 'Transgender',
                    ])
                    ->required(),
                TextInput::make('profession')
                    ->nullable(),
                TextInput::make('current_day')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                KeyValue::make('effective_stats')
                    ->keyLabel('Stat')
                    ->valueLabel('Value')
                    ->addable(false)
                    ->deletable(false)
                    ->editableKeys(false),
                KeyValue::make('stats')
                    ->keyLabel('Base Stat')
                    ->valueLabel('Value')
                    ->addable(false)
                    ->deletable(false)
                    ->editableKeys(false),
                KeyValue::make('hidden_stats')
                    ->keyLabel('Hidden Stat')
                    ->valueLabel('Value')
                    ->addable(false)
                    ->deletable(false)
                    ->editableKeys(false),
            ]);
    }
}

