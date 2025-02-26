<?php

namespace App\Http\Controllers;

use App\Models\ChangYong;
use App\Models\Events;
use App\Models\Items;
use App\Models\Payment;
use Inertia\Inertia;
use Illuminate\Http\Request;

class CashierController
{

    public function index()
    {
        if(!auth()->check()){
            return redirect()->to(route('filament.admin.auth.login'));
        }
        //常用面額
        $quickAmounts = ChangYong::where('owner_id', auth()->id())->get()->pluck('price')->toArray();
        //年紀判別
        $date = now()->subYears(18);
        $r18Date = ($date->year - 1911).'/'.$date->month.'/'.$date->day;
        //支付方式
        $payment = Payment::where('owner_id', auth()->id())->get()->toArray();
        //場次
        $event = Events::where('owner_id', auth()->id())->get()->toArray();

        return Inertia::render('Cashier', [
                'quickAmounts' => $quickAmounts,
                'r18Date' => $r18Date,
                'payment' => $payment,
                'events' => $event,
                'user' => auth()->id(),
            ]
        );
    }
    public function show()
    {
        return Inertia::render('Show');
    }

    public function preorder()
    {
        //場次
        $event = Events::where('owner_id', auth()->id())->get()->toArray();

        return Inertia::render('PreOrder',[
            'events' => $event,
            'user' => auth()->id(),
        ]);
    }

    public function getItems($userId, $eventId)
    {
        $eventId=[0, $eventId];

        return response()->json(
            Items::where('owner_id', $userId)
                ->whereIn('event_id', $eventId)
                ->get()
                ->toArray()
        );
    }
}
