<?php

namespace App\Filament\Resources\Analytics\SharedCharacterData\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SharedCharacterDataForm
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
                // Life Stats
                TextInput::make('health')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->label('Health (0-100)'),
                TextInput::make('happiness')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->label('Happiness (0-100)'),
                TextInput::make('finance')
                    ->numeric()
                    ->label('Finance'),
                Select::make('relationship_status')
                    ->options([
                        'single' => 'Single',
                        'dating' => 'Dating',
                        'engaged' => 'Engaged',
                        'married' => 'Married',
                        'divorced' => 'Divorced',
                        'widowed' => 'Widowed',
                    ])
                    ->label('Relationship Status'),
                Select::make('career_level')
                    ->options([
                        'unemployed' => 'Unemployed',
                        'entry' => 'Entry Level',
                        'junior' => 'Junior',
                        'senior' => 'Senior',
                        'manager' => 'Manager',
                        'executive' => 'Executive',
                        'retired' => 'Retired',
                    ])
                    ->label('Career Level'),
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
