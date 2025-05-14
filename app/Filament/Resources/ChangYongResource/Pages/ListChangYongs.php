<?php

namespace App\Filament\Resources\ChangYongResource\Pages;

use App\Filament\Resources\ChangYongResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChangYongs extends ListRecords
{
    protected static string $resource = ChangYongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
