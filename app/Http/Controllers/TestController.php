<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class TestController
{
    public function test()
    {
        dump(auth()->user()->id);
        return Inertia::render('Welcome', []);
    }
}
