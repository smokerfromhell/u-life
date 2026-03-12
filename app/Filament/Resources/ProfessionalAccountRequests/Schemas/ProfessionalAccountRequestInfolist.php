<?php

namespace App\Filament\Resources\ProfessionalAccountRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProfessionalAccountRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id')->label('ID'),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('status')->badge(),
            TextEntry::make('name'),
            TextEntry::make('email'),
            TextEntry::make('organization')->placeholder('—'),
            TextEntry::make('message')->markdown()->placeholder('—'),
            TextEntry::make('createdUser.email')->label('Created User')->placeholder('—'),
            TextEntry::make('approver.email')->label('Approved By')->placeholder('—'),
            TextEntry::make('approved_at')->dateTime()->placeholder('—'),
            TextEntry::make('rejecter.email')->label('Rejected By')->placeholder('—'),
            TextEntry::make('rejected_at')->dateTime()->placeholder('—'),
            TextEntry::make('rejection_reason')->placeholder('—'),
        ]);
    }
}

