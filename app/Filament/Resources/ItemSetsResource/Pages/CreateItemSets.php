<?php

namespace App\Filament\Resources\ItemSetsResource\Pages;

use App\Filament\Resources\ItemSetsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItemSets extends CreateRecord
{
    protected static string $resource = ItemSetsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->id();
        return $data;
    }
}
