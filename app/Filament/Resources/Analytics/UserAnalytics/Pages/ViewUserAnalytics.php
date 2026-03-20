<?php

namespace App\Filament\Resources\Analytics\UserAnalytics\Pages;

use App\Filament\Resources\Analytics\UserAnalyticsResource;
use App\Filament\Resources\Characters\CharacterResource;
use App\Models\Character;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ViewUserAnalytics extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = UserAnalyticsResource::class;

    protected static ?string $title = 'User Details';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to List')
                ->url(fn () => UserAnalyticsResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return Character::query()->where('user_id', $this->record->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Character Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('age_group')
                    ->label('Age Group')
                    ->badge()
                    ->sortable(),
                TextColumn::make('profession')
                    ->label('Profession')
                    ->placeholder('—')
                    ->badge()
                    ->sortable(),
                TextColumn::make('current_day')
                    ->label('Day')
                    ->sortable(),
                TextColumn::make('health')
                    ->label('Health')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),
                TextColumn::make('happiness')
                    ->label('Happiness')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger'))
                    ->sortable(),
                TextColumn::make('finance')
                    ->label('Finance')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('relationship_status')
                    ->label('Relationship')
                    ->badge()
                    ->sortable(),
                TextColumn::make('career_level')
                    ->label('Career')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('view_character')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => CharacterResource::getUrl('view', ['record' => $record->id])),
            ])
            ->emptyStateHeading('No characters')
            ->emptyStateDescription('This user has not created any characters yet.')
            ->paginated([10, 25, 50]);
    }
}
