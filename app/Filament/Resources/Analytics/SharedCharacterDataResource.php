<?php

namespace App\Filament\Resources\Analytics;

use App\Filament\Resources\Analytics\SharedCharacterData\Pages\ListSharedCharacterData;
use App\Filament\Resources\Analytics\SharedCharacterData\Pages\ViewSharedCharacterData;
use App\Filament\Resources\Analytics\SharedCharacterData\Schemas\SharedCharacterDataForm;
use App\Filament\Resources\Analytics\SharedCharacterData\Schemas\SharedCharacterDataInfolist;
use App\Filament\Widgets\CharacterPersonality\CharacterBigFiveChart;
use App\Filament\Widgets\CharacterPersonality\CharacterMbtiChart;
use App\Filament\Widgets\CharacterStats\CharacterReputationTable;
use App\Filament\Widgets\CharacterStats\CharacterTraumaTable;
use App\Filament\Widgets\CharacterStats\CharacterAchievementsTable;
use App\Filament\Widgets\CharacterStats\CharacterLuckStatsWidget;
use App\Filament\Widgets\CharacterStats\CharacterDecisionHistoryTable;
use App\Models\Character;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SharedCharacterDataResource extends Resource
{
    protected static ?string $model = Character::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Analytics';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SharedCharacterDataForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SharedCharacterDataInfolist::configure($schema);
    }

    public static function getWidgets(): array
    {
        return [
            CharacterMbtiChart::class,
            CharacterBigFiveChart::class,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return self::getWidgets();
    }

    protected function getFooterWidgets(): array
    {
        return [
            CharacterReputationTable::class,
            CharacterTraumaTable::class,
            CharacterAchievementsTable::class,
            CharacterLuckStatsWidget::class,
            CharacterDecisionHistoryTable::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('age_group')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('profession')
                    ->placeholder('—')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('current_day')
                    ->label('Day')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('health')
                    ->label('Health')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('happiness')
                    ->label('Happiness')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('finance')
                    ->label('Finance')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('relationship_status')
                    ->label('Relationship')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('career_level')
                    ->label('Career')
                    ->badge()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('age_group')->options([
                    'child' => 'Child',
                    'teenager' => 'Teenager',
                    'adult' => 'Adult',
                    'old' => 'Old',
                ]),
                \Filament\Tables\Filters\SelectFilter::make('relationship_status')->options([
                    'single' => 'Single',
                    'dating' => 'Dating',
                    'engaged' => 'Engaged',
                    'married' => 'Married',
                    'divorced' => 'Divorced',
                    'widowed' => 'Widowed',
                ])->label('Relationship'),
                \Filament\Tables\Filters\SelectFilter::make('career_level')->options([
                    'unemployed' => 'Unemployed',
                    'entry' => 'Entry Level',
                    'junior' => 'Junior',
                    'senior' => 'Senior',
                    'manager' => 'Manager',
                    'executive' => 'Executive',
                    'retired' => 'Retired',
                ])->label('Career'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50, 100])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSharedCharacterData::route('/'),
            'view' => ViewSharedCharacterData::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canViewAny(): bool
    {
        return true;
    }
}
