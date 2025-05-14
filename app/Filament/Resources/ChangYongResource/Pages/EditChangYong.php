<?php

namespace App\Filament\Resources\ChangYongResource\Pages;

use App\Filament\Resources\ChangYongResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChangYong extends EditRecord
{
    protected static string $resource = ChangYongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
