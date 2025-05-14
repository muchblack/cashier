<?php

namespace App\Filament\Resources\ItemSetsResource\Pages;

use App\Filament\Resources\ItemSetsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditItemSets extends EditRecord
{
    protected static string $resource = ItemSetsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
