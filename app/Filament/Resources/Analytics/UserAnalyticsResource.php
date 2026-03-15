<?php

namespace App\Filament\Resources\Analytics;

use App\Filament\Resources\Analytics\UserAnalytics\Pages\ListUserAnalytics;
use App\Models\SharedDecisionLog;
use App\Models\LifeStatsSnapshot;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\UnionScope;

class UserAnalyticsResource extends Resource
{
    protected static ?string $model = ListUserAnalytics::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?string $navigationLabel = 'User Analytics';

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
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin') || (bool) Auth::user()?->hasRole('Professional');
    }

    public static function canViewAny(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin') || (bool) Auth::user()?->hasRole('Professional');
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Analytics\UserAnalytics\Pages\ListUserAnalytics::route('/'),
        ];
    }
}
