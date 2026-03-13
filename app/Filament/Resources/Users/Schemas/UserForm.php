<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(fn (callable $get): bool => !$get('is_guest'))
                    ->email(),
                DateTimePicker::make('email_verified_at'),
                Toggle::make('is_guest')
                    ->label('Guest Account')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $guestCount = \App\Models\User::where('name', 'like', 'Guest Player%')->count();
                            $guestName = $guestCount > 0 ? 'Guest Player ' . ($guestCount + 1) : 'Guest Player';
                            $set('name', $guestName);
                        } else {
                            $set('name', $get('name') || '');
                        }
                        $set('email', null);
                        $set('password', '');
                        $set('password_confirmation', '');
                    }),
                Toggle::make('share_consent')
                    ->label('Data sharing consent'),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (callable $get): bool => !$get('is_guest'))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                TextInput::make('password_confirmation')
                    ->label('Confirm Password')
                    ->password()
                    ->required(fn (callable $get): bool => !$get('is_guest'))
                    ->dehydrated(false)
                    ->same('password')
                    ->rules(['confirmed']),
                Select::make('roles.name')
                    ->label('Roles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->required(),
            ]);
    }
}

