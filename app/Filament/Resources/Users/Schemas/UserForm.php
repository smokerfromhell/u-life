<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                Toggle::make('is_guest')
                    ->label('Guest account')
                    ->disabled(),
                Toggle::make('share_consent')
                    ->label('Data sharing consent'),
                Select::make('role')
                    ->relationship('roles', 'name')
                    ->required(),
            ]);
    }
}
