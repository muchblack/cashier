<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ReadMe;
use App\Filament\Widgets\Update;
use Filament\Pages\Page;

class SaleAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = '銷售分析';
    protected static ?string $title = '銷售分析儀表板';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.SaleAnalytics';
    protected function getHeaderWidgets(): array
    {
        return [

        ];
    }

    protected function getFooterWidgets(): array
    {
        return [

        ];
    }
}
