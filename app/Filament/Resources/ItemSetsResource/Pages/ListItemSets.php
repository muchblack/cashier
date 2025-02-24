<?php

namespace App\Filament\Resources\ItemSetsResource\Pages;

use App\Filament\Resources\ItemSetsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemSets extends ListRecords
{
    protected static string $resource = ItemSetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
