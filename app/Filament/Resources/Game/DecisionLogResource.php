<?php

namespace App\Filament\Resources\Game;

use App\Models\DecisionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class DecisionLogResource extends Resource
{
    protected static ?string $model = DecisionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Game';

    protected static ?string $recordTitleAttribute = 'id';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Game\DecisionLog\Pages\ListDecisionLogs::route('/'),
        ];
    }
}
