<?php

namespace App\Filament\Resources\ItemTypeResource\Pages;

use App\Filament\Resources\ItemTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItemType extends CreateRecord
{
    protected static string $resource = ItemTypeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->id();
        return $data;
    }
}
