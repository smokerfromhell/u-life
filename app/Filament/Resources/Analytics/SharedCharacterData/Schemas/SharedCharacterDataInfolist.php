<?php

namespace App\Filament\Resources\Analytics\SharedCharacterData\Schemas;

use App\Models\PersonalityProfile;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SharedCharacterDataInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')->label('ID'),
                TextEntry::make('user.email')->label('User Email'),
                TextEntry::make('name'),
                TextEntry::make('age_group')->badge(),
                TextEntry::make('gender')->badge(),
                TextEntry::make('profession')->placeholder('—'),
                TextEntry::make('current_day')->label('Day'),
                TextEntry::make('health')
                    ->label('Health')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextEntry::make('happiness')
                    ->label('Happiness')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 70 ? 'success' : ($state >= 40 ? 'warning' : 'danger')),
                TextEntry::make('finance')
                    ->label('Finance')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextEntry::make('relationship_status')->label('Relationship')->badge(),
                TextEntry::make('career_level')->label('Career')->badge(),
                
                // MBTI Personality
                TextEntry::make('mbti_type')
                    ->label('MBTI Type')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        $mbti = $personality?->current_mbti ?? 'N/A';
                        $confidence = $personality?->mbti_confidence ?? 0;
                        return $mbti . ' (' . $confidence . '% confidence)';
                    }),
                TextEntry::make('energy_orientation')
                    ->label('Extraversion vs Introversion')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        $val = $personality?->energy_orientation ?? 50;
                        return $val . '% E / ' . (100 - $val) . '% I';
                    }),
                TextEntry::make('information_gathering')
                    ->label('Intuition vs Sensing')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        $val = $personality?->information_gathering ?? 50;
                        return $val . '% N / ' . (100 - $val) . '% S';
                    }),
                TextEntry::make('decision_forming')
                    ->label('Thinking vs Feeling')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        $val = $personality?->decision_forming ?? 50;
                        return $val . '% T / ' . (100 - $val) . '% F';
                    }),
                TextEntry::make('lifestyle_approach')
                    ->label('Judging vs Perceiving')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        $val = $personality?->lifestyle_approach ?? 50;
                        return $val . '% J / ' . (100 - $val) . '% P';
                    }),
                
                // Big Five
                TextEntry::make('bigfive_openness')
                    ->label('Openness (O)')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        return ($personality?->openness ?? 50) . '/100';
                    }),
                TextEntry::make('bigfive_conscientiousness')
                    ->label('Conscientiousness (C)')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        return ($personality?->conscientiousness ?? 50) . '/100';
                    }),
                TextEntry::make('bigfive_extraversion')
                    ->label('Extraversion (E)')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        return ($personality?->extraversion ?? 50) . '/100';
                    }),
                TextEntry::make('bigfive_agreeableness')
                    ->label('Agreeableness (A)')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        return ($personality?->agreeableness ?? 50) . '/100';
                    }),
                TextEntry::make('bigfive_neuroticism')
                    ->label('Neuroticism (N)')
                    ->default(function ($record) {
                        $personality = PersonalityProfile::where('character_id', $record->id)->first();
                        return ($personality?->neuroticism ?? 50) . '/100';
                    }),
                
                TextEntry::make('effective_stats')->label('Effective Stats'),
                TextEntry::make('stats')->label('Base Stats'),
                TextEntry::make('hidden_stats')->label('Hidden Stats'),
                TextEntry::make('active_event_paths')->label('Active Paths'),
                TextEntry::make('completed_event_chains')->label('Completed Chains'),
                TextEntry::make('shown_event_ids')->label('Shown Event IDs'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]);
    }
}
