<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class TestController
{
    public function test()
    {
        return Inertia::render('PreOrder', []);
    }
}
