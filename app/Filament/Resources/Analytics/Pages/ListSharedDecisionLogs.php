<?php

namespace App\Filament\Resources\Analytics\Pages;

use App\Filament\Resources\Analytics\SharedDecisionLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSharedDecisionLogs extends ListRecords
{
    protected static string $resource = SharedDecisionLogResource::class;
}

