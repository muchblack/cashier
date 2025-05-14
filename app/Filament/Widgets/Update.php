<?php

namespace App\Filament\Widgets;

use App\Models\SystemUpdate;
use Filament\Widgets\Widget;

class Update extends Widget
{
    protected static string $view = 'filament.widgets.update';

    public function getUpdate()
    {
        return SystemUpdate::query()
            ->latest('created_at')
            ->take(5)
            ->get();
    }
}
