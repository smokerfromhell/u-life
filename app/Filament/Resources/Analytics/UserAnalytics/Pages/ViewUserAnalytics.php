<?php

namespace App\Filament\Resources\Analytics\UserAnalytics\Pages;

use App\Filament\Resources\Analytics\UserAnalyticsResource;
use App\Models\SharedDecisionLog;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as StatsOverview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ViewUserAnalytics extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = UserAnalyticsResource::class;

    protected static ?string $title = 'User Analytics';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Back to List')
                ->url(fn () => UserAnalyticsResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    public function table(Table $table): Table
    {
        $userName = $this->record->user_name;

        return $table
            ->query(SharedDecisionLog::query()->where('user_name', $userName))
            ->columns([
                TextColumn::make('day')
                    ->label('Day')
                    ->sortable(),
                BadgeColumn::make('event_type')
                    ->label('Event Type')
                    ->colors([
                        'warning' => 'daily',
                        'primary' => 'cultural',
                        'success' => 'profession',
                        'danger' => 'age_specific',
                    ]),
                TextColumn::make('choice_text')
                    ->label('Choice')
                    ->limit(30),
                TextColumn::make('profession')
                    ->label('Profession')
                    ->badge(),
                TextColumn::make('mbti')
                    ->label('MBTI')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('details')
                    ->label('Details')
                    ->url(fn ($record) => '#'),
            ])
            ->emptyStateHeading('No shared data')
            ->emptyStateDescription('This user has not shared any decision data yet.')
            ->paginated([10, 25, 50]);
    }

    protected function getHeaderWidgets(): array
    {
        $userName = $this->record->user_name;

        $totalDecisions = SharedDecisionLog::where('user_name', $userName)->count();
        $uniqueEvents = SharedDecisionLog::where('user_name', $userName)->distinct('event_id')->count('event_id');
        $avgDay = SharedDecisionLog::where('user_name', $userName)->avg('day');
        $mbti = SharedDecisionLog::where('user_name', $userName)->first()?->mbti ?? 'N/A';

        return [
            StatsOverview::make([
                Stat::make('Total Shared Decisions', $totalDecisions)
                    ->description('Decisions shared by this user')
                    ->color('success'),
                Stat::make('Unique Events', $uniqueEvents)
                    ->description('Different events experienced')
                    ->color('primary'),
                Stat::make('Average Day', round($avgDay))
                    ->description('Avg game day of shared data')
                    ->color('warning'),
                Stat::make('MBTI', $mbti)
                    ->description('User\'s personality type')
                    ->color('info'),
            ]),
        ];
    }
}

