<?php

namespace App\Filament\Resources\ProfessionalAccountRequests;

use App\Filament\Resources\ProfessionalAccountRequests\Pages\ListProfessionalAccountRequests;
use App\Filament\Resources\ProfessionalAccountRequests\Pages\ViewProfessionalAccountRequest;
use App\Filament\Resources\ProfessionalAccountRequests\Schemas\ProfessionalAccountRequestInfolist;
use App\Filament\Resources\ProfessionalAccountRequests\Tables\ProfessionalAccountRequestsTable;
use App\Models\ProfessionalAccountRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProfessionalAccountRequestResource extends Resource
{
    protected static ?string $model = ProfessionalAccountRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Access Control';

    protected static ?string $recordTitleAttribute = 'email';

    public static function table(Table $table): Table
    {
        return ProfessionalAccountRequestsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ProfessionalAccountRequestInfolist::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfessionalAccountRequests::route('/'),
            'view' => ViewProfessionalAccountRequest::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin');
    }

    public static function canViewAny(): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin');
    }

    public static function canView(Model $record): bool
    {
        return (bool) Auth::user()?->hasRole('Super Admin') || (bool) Auth::user()?->hasRole('Admin');
    }
}

