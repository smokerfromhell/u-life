<?php

namespace App\Filament\Resources\Feedback;

use App\Filament\Resources\Feedback\Pages;
use App\Models\Feedback;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Feedback';

    protected static ?int $navigationSort = 100;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                \Filament\Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options([
                        'Bug Report' => 'Bug Report',
                        'Feature Suggestion' => 'Feature Suggestion',
                        'General Feedback' => 'General Feedback',
                        'Other' => 'Other',
                    ]),
                \Filament\Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->rows(4)
                    ->maxLength(5000),
                \Filament\Forms\Components\Toggle::make('is_read')
                    ->label('Read'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                \Filament\Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'danger' => 'Bug Report',
                        'info' => 'Feature Suggestion',
                        'success' => 'General Feedback',
                        'warning' => 'Other',
                    ]),
                \Filament\Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(50)
                    ->searchable(),
                \Filament\Tables\Columns\BooleanColumn::make('is_read')
                    ->label('Read'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'Bug Report' => 'Bug Report',
                        'Feature Suggestion' => 'Feature Suggestion',
                        'General Feedback' => 'General Feedback',
                        'Other' => 'Other',
                    ]),
                \Filament\Tables\Filters\TernaryFilter::make('is_read')
                    ->label('Read Status'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
        ];
    }

    public static function canAccess(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        if (!$user) {
            return false;
        }
        
        return $user->hasRole('Super Admin');
    }
}
