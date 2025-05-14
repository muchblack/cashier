<?php

namespace App\Filament\Resources\ChangYongResource\Pages;

use App\Filament\Resources\ChangYongResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChangYong extends CreateRecord
{
    protected static string $resource = ChangYongResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['owner_id'] = auth()->id();
        return $data;
    }
}
