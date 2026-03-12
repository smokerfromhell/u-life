<?php

namespace App\Filament\Resources\Analytics;

use App\Filament\Resources\Analytics\Pages\ListSharedDecisionLogs;
use App\Filament\Resources\Analytics\Pages\ViewSharedDecisionLog;
use App\Filament\Resources\Analytics\Schemas\SharedDecisionLogInfolist;
use App\Filament\Resources\Analytics\Tables\SharedDecisionLogsTable;
use App\Models\SharedDecisionLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class SharedDecisionLogResource extends Resource
{
    protected static ?string $model = SharedDecisionLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Analytics';

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

    public static function infolist(Schema $schema): Schema
    {
        return SharedDecisionLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SharedDecisionLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSharedDecisionLogs::route('/'),
            'view' => ViewSharedDecisionLog::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Professional');
    }

    public static function canViewAny(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Professional');
    }

    public static function canView(Model $record): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin');
    }
}
