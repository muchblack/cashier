<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewWebsite')
                ->label('前往收銀計算頁面')
                ->icon('heroicon-o-globe-alt')
                ->url('/', shouldOpenInNewTab: true)
                ->button(),
        ];
    }
}
